<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Response;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
    protected function responseSuccess($data = null,$message = "success",$statusCode = Response::HTTP_OK)
    {
        return response()->json([
            'status' => 'success',
            'data' => $data,
            'message' => $message
        ], $statusCode);
    }

    protected function responseError($message = 'Error', $statusCode = Response::HTTP_BAD_REQUEST)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
        ], $statusCode);
    }
}
