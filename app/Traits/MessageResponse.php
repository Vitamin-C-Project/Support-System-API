<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait MessageResponse
{
    function showIndexOrFail(mixed $data, string $table)
    {
        $format = [
            "status"    => "000",
            "message"   => "Success index table {$table}",
            "data"      => $data,
        ];

        if (empty($data)) {
            $format["message"] = "Data not found";
            $format["data"] = [];
        }

        return response()->json($format, 200);
    }

    public function loginSuccess(string $message, array $data = [], int $status = JsonResponse::HTTP_OK): JsonResponse
    {
        return response()->json([
            'status' => $status,
            'message' => $message,
            'data' => $data
        ], $status);
    }
    public function logoutSuccess(string $message, array $data = [], int $status = JsonResponse::HTTP_OK): JsonResponse
    {
        return response()->json([
            'status' => $status,
            'message' => $message,
            'data' => $data
        ], $status);
    }
}
