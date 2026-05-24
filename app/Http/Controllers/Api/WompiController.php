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

        $integrity_secret = env('WOMPI_INTEGRITY_SECRET');

        if (!$integrity_secret) {
            return response()->json([
                'success' => false,
                'message' => 'WOMPI_INTEGRITY_SECRET no configurado en .env'
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
}