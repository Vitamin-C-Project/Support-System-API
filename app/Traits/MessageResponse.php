<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait MessageResponse
{
    function showIndexOrFail(mixed $data, $status = JsonResponse::HTTP_OK)
    {
        $responseStatus = $status;
        $format = [
            "status"    => $status,
            "message"   => "Success",
            "data"      => $data,
        ];

        if (empty($data)) {
            $responseStatus = JsonResponse::HTTP_NOT_FOUND;
            $format["status"] = JsonResponse::HTTP_NOT_FOUND;
            $format["message"] = "Data not found";
            $format["data"] = [];
        }

        return response()->json($format, $responseStatus);
    }

    function showCreateOrFail(mixed $data, int $status = JsonResponse::HTTP_CREATED)
    {
        $format = [
            "status"    => $status,
            'message'   => 'Success',
            'data'      => $data
        ];

        if (empty($data)) {
            $format["message"] = "Data not found";
            $format["data"] = [];
        }

        return response()->json($format, $status);
    }

    function showViewOrFail(mixed $data, int $status = JsonResponse::HTTP_OK, $statusNotFound = JsonResponse::HTTP_NOT_FOUND)
    {
        $responseStatus = $status;

        $format = [
            "status"    => $status,
            'message'   => 'Success',
            'data'      => $data
        ];

        if (empty($data)) {
            $responseStatus = $statusNotFound;
            $format['status'] = $statusNotFound;
            $format["message"] = "Data not found";
            $format["data"] = [];
        }

        return response()->json($format, $responseStatus);
    }

    function showUpdateOrFail(mixed $data, int $status = JsonResponse::HTTP_OK, $statusNotFound = JsonResponse::HTTP_NOT_FOUND)
    {
        $responseStatus = $status;
        $format = [
            "status"    => $status,
            'message'   => 'Success',
            'data'      => $data
        ];

        if (empty($data)) {
            $responseStatus = $statusNotFound;
            $format['status'] = $statusNotFound;
            $format["message"] = "Data not found";
            $format["data"] = [];
        }

        return response()->json($format, $responseStatus);
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
