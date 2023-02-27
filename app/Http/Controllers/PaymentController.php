<?php

namespace App\Http\Controllers;

use App\Libraries\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Spatie\FlareClient\Api;

class PaymentController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
    }

    public static function checkout($data)
    {
        $key = config('midtrans.server_key');

        $transaction_detail = [
            "order_id" => $data["order_id"],
            "gross_amount" => $data["gross_amount"]
        ];

        $response = Http::withBasicAuth($key, " ")->post("https://api.sandbox.midtrans.com/v2/charge", [
            "payment_type" => "bank_transfer",
            "transaction_details" => $transaction_detail,
            "bank_transfer" => [
                "bank" => "bca"
            ]
        ]);

        if ($response->failed()) {
            return ApiResponse::error("Transaction Failed");
        }

        if ($response['status_code'] != 201) {
            return ApiResponse::error($response['status_message']);
        }

        $data = [
            'message' => "Transfer to VA Number",
            'data' => $response['va_numbers']
        ];

        return ApiResponse::success($data);
    }
}
