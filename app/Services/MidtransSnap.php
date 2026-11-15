<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Sale;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class MidtransSnap
{
    public function configured(): bool
    {
        return filled(config('services.midtrans.server_key'));
    }

    /** @return array{token: string, redirect_url: string} */
    public function createTransaction(Sale $sale): array
    {
        if (! $this->configured()) {
            throw new RuntimeException('Midtrans belum dikonfigurasi. Isi MIDTRANS_SERVER_KEY di .env.');
        }

        $baseUrl = config('services.midtrans.is_production')
            ? 'https://app.midtrans.com'
            : 'https://app.sandbox.midtrans.com';

        $items = $sale->items->map(fn ($item): array => [
            'id' => (string) $item->book_id,
            'price' => (int) $item->unit_price,
            'quantity' => (int) $item->quantity,
            'name' => mb_substr($item->book_title, 0, 50),
        ])->all();
        if ($sale->shipping_cost > 0) {
            $items[] = [
                'id' => 'shipping',
                'price' => (int) $sale->shipping_cost,
                'quantity' => 1,
                'name' => mb_substr('Ongkir '.$sale->shipping_courier.' '.$sale->shipping_service, 0, 50),
            ];
        }

        try {
            $response = Http::acceptJson()
                ->asJson()
                ->withBasicAuth((string) config('services.midtrans.server_key'), '')
                ->timeout(15)
                ->post($baseUrl.'/snap/v1/transactions', [
                    'transaction_details' => [
                        'order_id' => $sale->reference,
                        'gross_amount' => (int) $sale->total,
                    ],
                    'item_details' => $items,
                    'customer_details' => [
                        'first_name' => $sale->customer_name,
                        'email' => $sale->customer_email,
                        'phone' => $sale->customer_phone,
                    ],
                    'callbacks' => ['finish' => url('/orders/'.$sale->reference)],
                ]);
        } catch (ConnectionException) {
            throw new RuntimeException('Tidak dapat terhubung ke Midtrans. Periksa koneksi internet/server lalu coba checkout kembali.');
        }

        if (! $response->successful() || ! $response->json('token') || ! $response->json('redirect_url')) {
            throw new RuntimeException('Midtrans tidak dapat membuat transaksi. Periksa konfigurasi dan coba lagi.');
        }

        return [
            'token' => $response->json('token'),
            'redirect_url' => $response->json('redirect_url'),
        ];
    }

    public function handleNotification(array $payload): bool
    {
        $serverKey = (string) config('services.midtrans.server_key');
        $orderId = (string) ($payload['order_id'] ?? '');
        $statusCode = (string) ($payload['status_code'] ?? '');
        $grossAmount = (string) ($payload['gross_amount'] ?? '');
        $signature = (string) ($payload['signature_key'] ?? '');

        if ($serverKey === '' || $orderId === '' || $statusCode === '' || $grossAmount === '' || $signature === '') {
            return false;
        }

        $expected = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);
        if (! hash_equals($expected, $signature)) {
            return false;
        }

        $sale = Sale::query()->where('reference', $orderId)->first();
        if ($sale === null || number_format((int) $sale->total, 2, '.', '') !== number_format((float) $grossAmount, 2, '.', '')) {
            return false;
        }

        $transactionStatus = (string) ($payload['transaction_status'] ?? '');
        $fraudStatus = (string) ($payload['fraud_status'] ?? '');

        if ($statusCode === '200' && ($transactionStatus === 'settlement' || ($transactionStatus === 'capture' && $fraudStatus === 'accept'))) {
            Sale::query()->whereKey($sale->id)->where('payment_status', 'pending')->update([
                'payment_status' => 'paid',
                'paid_at' => now(),
                'updated_at' => now(),
            ]);
        } elseif (in_array($transactionStatus, ['deny', 'cancel', 'expire'], true)) {
            $this->cancelAndRestoreStock($sale);
        }

        return true;
    }

    private function cancelAndRestoreStock(Sale $sale): void
    {
        DB::transaction(function () use ($sale): void {
            $lockedSale = Sale::with('items')->whereKey($sale->id)->lockForUpdate()->first();
            if ($lockedSale === null || $lockedSale->payment_status !== 'pending') {
                return;
            }

            $lockedSale->update(['payment_status' => 'cancelled']);
            foreach ($lockedSale->items as $item) {
                Book::query()->whereKey($item->book_id)->increment('stock', $item->quantity);
            }
        });
    }
}
