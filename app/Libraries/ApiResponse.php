<?php 

namespace App\Libraries;


class ApiResponse
{
    public static function success($response)
    {
        
        $data = isset($response['data']) ? $response['data'] : null;
        
        $message = isset($response['message']) ? $response['message'] : null;

        return response()->json([
            "status"    => "succes",
            "message"   => $message,
            "data"      => $data
        ]);
    }

    public static function error($response)
    {   
        $message = isset($response) ? $response : null;
        
        return response()->json([
            "status"    => "error",
            "message"   => $message
        ]);
    }
} 
