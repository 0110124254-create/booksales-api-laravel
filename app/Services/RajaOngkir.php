<?php

namespace App\Services;

use App\Models\Book;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class RajaOngkir
{
    private const BASE_URL = 'https://rajaongkir.komerce.id/api/v1';

    public function configured(): bool
    {
        return filled(config('services.rajaongkir.api_key'))
            && filled(config('services.rajaongkir.origin_id'));
    }

    /** @return array<int, array{id: int, label: string, zip_code: string}> */
    public function destinations(string $search): array
    {
        $this->ensureConfigured();
        $key = 'shipping-destinations:'.sha1(mb_strtolower($search));

        return Cache::remember($key, now()->addDay(), function () use ($search): array {
            $response = $this->request()
                ->get(self::BASE_URL.'/destination/domestic-destination', [
                    'search' => $search,
                    'limit' => 10,
                    'offset' => 0,
                ]);

            $data = $this->responseData($response->json(), $response->successful());

            return collect($data)->map(fn (array $destination): array => [
                'id' => (int) ($destination['id'] ?? 0),
                'label' => (string) ($destination['label'] ?? ''),
                'zip_code' => (string) ($destination['zip_code'] ?? ''),
            ])->filter(fn (array $destination): bool => $destination['id'] > 0)->values()->all();
        });
    }

    /** @param array<int, array{book_id: int, quantity: int}> $items
     * @return array<int, array{name: string, code: string, service: string, description: string, cost: int, etd: string}>
     */
    public function quotes(int $destinationId, array $items): array
    {
        $this->ensureConfigured();
        $weight = $this->packageWeight($items);
        $couriers = (string) config('services.rajaongkir.couriers');

        $response = $this->request()
            ->asForm()
            ->post(self::BASE_URL.'/calculate/domestic-cost', [
                'origin' => (int) config('services.rajaongkir.origin_id'),
                'destination' => $destinationId,
                'weight' => $weight,
                'courier' => $couriers,
                'price' => 'lowest',
            ]);

        $data = $this->responseData($response->json(), $response->successful());

        return collect($data)->map(fn (array $quote): array => [
            'name' => (string) ($quote['name'] ?? strtoupper((string) ($quote['code'] ?? ''))),
            'code' => (string) ($quote['code'] ?? ''),
            'service' => (string) ($quote['service'] ?? ''),
            'description' => (string) ($quote['description'] ?? ''),
            'cost' => (int) ($quote['cost'] ?? 0),
            'etd' => (string) ($quote['etd'] ?? ''),
        ])->filter(fn (array $quote): bool => $quote['code'] !== '' && $quote['service'] !== '' && $quote['cost'] >= 0)->values()->all();
    }

    /** @param array<int, array{book_id: int, quantity: int}> $items */
    public function packageWeight(array $items): int
    {
        $quantities = collect($items)->keyBy(fn (array $item): int => (int) $item['book_id']);
        $books = Book::query()
            ->whereIn('id', $quantities->keys())
            ->where('is_active', true)
            ->get(['id', 'weight_gram']);

        if ($books->count() !== $quantities->count()) {
            throw new RuntimeException('Salah satu buku tidak lagi tersedia. Perbarui keranjang Anda.');
        }

        return max(1, (int) $books->sum(fn (Book $book): int => $book->weight_gram * (int) $quantities[$book->id]['quantity']));
    }

    private function ensureConfigured(): void
    {
        if (! $this->configured()) {
            throw new RuntimeException('Cek ongkir belum dikonfigurasi. Isi RAJAONGKIR_API_KEY dan RAJAONGKIR_ORIGIN_ID di .env.');
        }
    }

    private function request(): PendingRequest
    {
        return Http::acceptJson()
            ->withHeader('key', (string) config('services.rajaongkir.api_key'))
            ->timeout(15)
            ->retry(2, 250);
    }

    /** @param array<string, mixed>|null $payload
     * @return array<int, array<string, mixed>>
     */
    private function responseData(?array $payload, bool $successful): array
    {
        if (! $successful || ($payload['meta']['status'] ?? null) !== 'success' || ! is_array($payload['data'] ?? null)) {
            throw new RuntimeException('Tarif pengiriman tidak dapat diambil. Periksa API key RajaOngkir, alamat tujuan, dan asal toko.');
        }

        return $payload['data'];
    }
}
