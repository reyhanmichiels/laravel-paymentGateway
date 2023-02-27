<?php

namespace App\Http\Controllers;

use App\Libraries\ApiResponse;
use App\Models\Course;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CourseController extends Controller
{
    public function buy(Course $course, Request $request)
    {
        $validate = Validator::make($request->all(), [
            'name' => 'string|required'
        ]);

        if ($validate->fails()) {
            return ApiResponse::error($validate->errors());
        }

        try {
            DB::beginTransaction();

            $order = new Order;
            $order->name = $request->name;
            $order->course_id = $course->id;
            $order->gross_amount = $course->price;
            $order->save();

            $data = [
                'order_id' => $order->id,
                'gross_amount' => $order->gross_amount
            ];

            $response = PaymentController::checkout($data);
            return $response;

            // if ($response->status() != 201) {
            //     return ApiResponse::error($response['status_message']);
            // } else {
            //     return ApiResponse::success($response['va_numbers']);
            // }

            // return response()->json($response);

            DB::commit();
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage());
            DB::rollBack();
        }
    }
}
