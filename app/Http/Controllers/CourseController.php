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
            'name' => 'string|required',
            'email' => 'email'
        ]);

        if ($validate->fails()) {
            return ApiResponse::error($validate->errors(), 409);
        }

        try {
            DB::beginTransaction();

            $order = new Order;
            $order->name = $request->name;
            $order->email = $request->email;
            $order->course_id = $course->id;
            $order->gross_amount = $course->price;
            $order->save();

            $data = [
                'order_id' => $order->id,
                'gross_amount' => $order->gross_amount
            ];

            $response = PaymentController::checkout($data);
            
            
            if ($response['status'] == 'error') {
                return ApiResponse::error($response['data']);
            } ;

            DB::commit();
            return ApiResponse::success($response['data'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error($e->getMessage(), 409);
        }
    }
}
