<?php

namespace App\Http\Controllers;

use App\Services\MidtransSnap;
use App\Services\RajaOngkir;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use RuntimeException;
use Throwable;

class ShippingController extends Controller
{
    public function options(MidtransSnap $midtrans, RajaOngkir $rajaOngkir): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'midtrans_enabled' => $midtrans->configured(),
                'shipping_enabled' => $rajaOngkir->configured(),
                'checkout_enabled' => ! app()->environment('production') || $rajaOngkir->configured(),
            ],
        ]);
    }

    public function destinations(Request $request, RajaOngkir $rajaOngkir): JsonResponse
    {
        $data = $request->validate(['q' => ['required', 'string', 'min:3', 'max:100']]);

        try {
            return response()->json(['success' => true, 'data' => $rajaOngkir->destinations($data['q'])]);
        } catch (RuntimeException|ConnectionException $exception) {
            return response()->json(['success' => false, 'message' => $exception->getMessage()], 503);
        }
    }

    public function quotes(Request $request, RajaOngkir $rajaOngkir): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'destination_id' => ['required', 'integer', 'min:1'],
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.book_id' => ['required', 'integer', 'distinct', 'exists:books,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data untuk menghitung ongkir tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        try {
            $quotes = $rajaOngkir->quotes((int) $data['destination_id'], $data['items']);
        } catch (RuntimeException|ConnectionException $exception) {
            return response()->json(['success' => false, 'message' => $exception->getMessage()], 503);
        } catch (Throwable) {
            return response()->json(['success' => false, 'message' => 'Gagal menghitung ongkos kirim. Coba lagi.'], 503);
        }

        if ($quotes === []) {
            return response()->json(['success' => false, 'message' => 'Tidak ada layanan kurir untuk alamat tersebut.'], 422);
        }

        return response()->json(['success' => true, 'data' => $quotes]);
    }
}
