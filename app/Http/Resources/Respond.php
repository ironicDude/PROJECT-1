<?php
namespace App\Http\Resources;
trait Respond
{
    public function respond($message = null, $data = null, $status = 200):array
    {
          return [
            'message' => $message,
            'data' => $data,
            'status' => $status,
          ];
    } // end of respond
}
