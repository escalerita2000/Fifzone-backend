<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WompiController extends Controller
{
    public function generateSignature(Request $request)
    {
        $request->validate([
            'reference'       => 'required|string',
            'amount_in_cents' => 'required|integer',
            'currency'        => 'required|string',
        ]);

        $integrity_secret = env('WOMPI_INTEGRITY_KEY') ?: env('WOMPI_INTEGRITY_SECRET');

        if (!$integrity_secret) {
            return response()->json([
                'success' => false,
                'message' => 'WOMPI_INTEGRITY_SECRET / WOMPI_INTEGRITY_KEY no configurado en .env'
            ], 500);
        }

        // Fórmula exacta de Wompi
        $cadena    = $request->reference
                   . $request->amount_in_cents
                   . $request->currency
                   . $integrity_secret;

        $signature = hash('sha256', $cadena);

        return response()->json([
            'success'   => true,
            'signature' => $signature,
        ]);
    }

    public function checkoutConfig(Request $request)
    {
        $request->validate([
            'reference'       => 'required|string',
            'amount_in_cents' => 'required|integer',
            'currency'        => 'required|string',
        ]);

        $public_key = env('WOMPI_PUBLIC_KEY');
        $integrity_secret = env('WOMPI_INTEGRITY_KEY') ?: env('WOMPI_INTEGRITY_SECRET');

        if (!$integrity_secret || !$public_key) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciales de Wompi no configuradas en .env'
            ], 500);
        }

        $cadena = $request->reference
                . $request->amount_in_cents
                . $request->currency
                . $integrity_secret;

        $signature = hash('sha256', $cadena);

        return response()->json([
            'success' => true,
            'publicKey' => $public_key,
            'signature' => $signature,
            'amountInCents' => $request->amount_in_cents,
            'currency' => $request->currency,
            'reference' => $request->reference,
        ]);
    }

    public function webhook(Request $request)
    {
        $body = $request->json()->all();
        $signature = $body['signature'] ?? null;

        if (!$signature) {
            return response()->json(['error' => 'No signature provided'], 400);
        }

        $checksum = $signature['checksum'] ?? '';
        $properties = $signature['properties'] ?? [];
        $timestamp = $body['timestamp'] ?? '';

        // Reconstruct checksum string
        $concatString = '';
        foreach ($properties as $prop) {
            $value = data_get($body, 'data.' . $prop);
            $concatString .= $value;
        }
        $concatString .= $timestamp;

        // Use events secret if set, fallback to integrity secret/key
        $secret = env('WOMPI_EVENTS_SECRET') ?: (env('WOMPI_INTEGRITY_KEY') ?: env('WOMPI_INTEGRITY_SECRET'));
        $concatString .= $secret;

        $computedChecksum = hash('sha256', $concatString);

        if ($computedChecksum !== $checksum) {
            \Illuminate\Support\Facades\Log::warning('Wompi Webhook signature validation failed', [
                'computed' => $computedChecksum,
                'received' => $checksum
            ]);
            return response()->json(['error' => 'Invalid signature'], 403);
        }

        $transaction = $body['data']['transaction'] ?? null;
        if (!$transaction) {
            return response()->json(['error' => 'No transaction data'], 400);
        }

        $reference = $transaction['reference'] ?? null;
        $status = $transaction['status'] ?? null;

        $venta = \App\Models\Venta::where('reference', $reference)->first();
        if (!$venta) {
            return response()->json(['error' => 'Venta not found for reference: ' . $reference], 404);
        }

        if ($status === 'APPROVED') {
            $venta->estado = 'completada';
        } elseif (in_array($status, ['DECLINED', 'VOIDED', 'ERROR'])) {
            $venta->estado = 'fallida';
        } else {
            $venta->estado = 'pendiente';
        }

        $venta->notas = 'Pago Wompi ID: ' . ($transaction['id'] ?? '') . ' - Estado: ' . $status;
        $venta->save();

        return response()->json(['success' => true]);
    }
}