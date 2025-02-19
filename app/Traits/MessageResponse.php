<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait MessageResponse
{
    public function showIndexOrFail(mixed $data, int $status = JsonResponse::HTTP_OK,): JsonResponse
    {
        $isEmpty = is_null($data) || empty($data);

        return response()->json([
            "status"  => $isEmpty ? JsonResponse::HTTP_NOT_FOUND : $status,
            "messages" => $isEmpty ? "Data not found" : "Success Get Data",
            "data"    => $isEmpty ? [] : $data,
        ], $isEmpty ? JsonResponse::HTTP_NOT_FOUND : $status);
    }

    public function showCreateOrFail(mixed $data, int $status = JsonResponse::HTTP_CREATED): JsonResponse
    {
        $isEmpty = is_null($data) || empty($data);

        return response()->json([
            "status"  => $isEmpty ? JsonResponse::HTTP_NOT_FOUND : $status,
            "messages" => $isEmpty ? "Data not found" : "Success Create Data",
            "data"    => $isEmpty ? [] : $data,
        ], $isEmpty ? JsonResponse::HTTP_NOT_FOUND : $status);
    }

    public function showViewOrFail(mixed $data, int $status = JsonResponse::HTTP_OK): JsonResponse
    {
        $isEmpty = is_null($data) || empty($data);

        return response()->json([
            "status"  => $isEmpty ? JsonResponse::HTTP_NOT_FOUND : $status,
            "messages" => $isEmpty ? "Data not found" : "Success View Data",
            "data"    => $isEmpty ? [] : $data,
        ], $isEmpty ? JsonResponse::HTTP_NOT_FOUND : $status);
    }

    public function showUpdateOrFail(mixed $data, int $status = JsonResponse::HTTP_OK): JsonResponse
    {
        $isEmpty = is_null($data) || empty($data);

        return response()->json([
            "status"  => $isEmpty ? JsonResponse::HTTP_NOT_FOUND : $status,
            "messages" => $isEmpty ? "Data not found" : "Success Update Data",
            "data"    => $isEmpty ? [] : $data,
        ], $isEmpty ? JsonResponse::HTTP_NOT_FOUND : $status);
    }

    public function showDestroyOrFail(mixed $data, int $status = JsonResponse::HTTP_OK): JsonResponse
    {
        $isEmpty = is_null($data) || empty($data);

        return response()->json([
            "status"  => $isEmpty ? JsonResponse::HTTP_NOT_FOUND : $status,
            "messages" => $isEmpty ? "Data not found" : "Success Delete Data",
            "data"    => $isEmpty ? [] : $data,
        ], $isEmpty ? JsonResponse::HTTP_NOT_FOUND : $status);
    }

    public function showNotFound(mixed $data = []): JsonResponse
    {
        return response()->json([
            "status"  => JsonResponse::HTTP_NOT_FOUND,
            "messages" => "Data not found",
            "data"    => $data,
        ], JsonResponse::HTTP_NOT_FOUND);
    }

    public function showValidateError(mixed $data): JsonResponse
    {
        return response()->json([
            "status"  => JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
            "messages" => "Validation Error",
            "errors"  => $data, // Menggunakan `errors` karena ini error validasi
        ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function showFail(mixed $data, $status = JsonResponse::HTTP_INTERNAL_SERVER_ERROR): JsonResponse
    {
        return response()->json([
            "status"  => $status,
            "messages" => "Internal Server Error",
            "error"   => $data,
        ], $status);
    }

    public function showUnauthorized(string $message = "Unauthorized"): JsonResponse
    {
        return response()->json([
            "status"  => JsonResponse::HTTP_UNAUTHORIZED,
            "messages" => $message,
        ], JsonResponse::HTTP_UNAUTHORIZED);
    }

    public function showForbidden(string $message = "Forbidden Access"): JsonResponse
    {
        return response()->json([
            "status"  => JsonResponse::HTTP_FORBIDDEN,
            "messages" => $message,
        ], JsonResponse::HTTP_FORBIDDEN);
    }

    public function showConflict(string $message = "Conflict Detected"): JsonResponse
    {
        return response()->json([
            "status"  => JsonResponse::HTTP_CONFLICT,
            "messages" => $message,
        ], JsonResponse::HTTP_CONFLICT);
    }

    public function loginSuccess(string $message, array $data = []): JsonResponse
    {
        return response()->json([
            "status"  => JsonResponse::HTTP_OK,
            "messages" => $message,
            "data"    => $data,
        ], JsonResponse::HTTP_OK);
    }

    public function logoutSuccess(string $message, array $data = []): JsonResponse
    {
        return response()->json([
            "status"  => JsonResponse::HTTP_OK,
            "messages" => $message,
            "data"    => $data,
        ], JsonResponse::HTTP_OK);
    }
}
