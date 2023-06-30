<?php
namespace App\Http\Resources;
trait CustomResponse
{
    public function customResponse($message = "", $data = [], $status = 200)
    {
        return response()->json([
            'message' => $message,
            'data' => $data
            ], $status);
    } // end of respond
}
