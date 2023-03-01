<?php

namespace App\Http\Controllers;

use App\Libraries\ApiResponse;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Spatie\FlareClient\Api;

class PaymentController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $response = $request->all();

        $signature = hash('sha512', $response['order_id'] . $response['status_code'] . $response['gross_amount'] . config('midtrans.server_key'));

        if ($response['signature_key'] != $signature) {
            return ApiResponse::error('invalid signature key', 400);
        }

        $order = Order::find($response['order_id']);

        if (!$order) {
            $order->status = "Not Found ";
            return ApiResponse::error('order not found', 400);
        }

        if ($response['transaction_status'] == "settlement") {
            $order->status = "PAID";
            $order->save();
        } else if ($response['transaction_status'] == "expire") {
            $order->status = "CANCELED";
            $order->save();
        }

        $data = [
            'message' => 'succesed'
        ];

        return ApiResponse::success($data, 200);
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
            return [
                'status' => "error",
                'data' => 'Transaction Failed'
            ];
        }

        if ($response['status_code'] != 201) {
            return [
                'status' => "error",
                'data' => 'Transaction Failed'
            ];
        }

        $data = [
            'message' => "Transfer to VA Number",
            'data' => $response['va_numbers']
        ];

        return [
            'status' => 'succes',
            'data' => $data
        ];
    }
}
