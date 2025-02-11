<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\MessageResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class LoginController extends Controller
{
    use MessageResponse;
    protected $user;

    public function __construct()
    {
        $this->user = new User();
    }


    public function login(Request $request)
    {
        $request->validate([
            'email'     => 'required|email',
            'password'  => 'required'
        ]);
        try {
            $user = $this->user->where('email', $request->email)->first();

            if ($user->status == 0) {
                throw new \Exception('User is not active');
            }

            if (!$user || !Hash::check($request->password, $user->password)) {
                throw new \Exception('Invalid credentials');
            }

            $token = $user->createToken('access_token')->plainTextToken;

            return $this->loginSuccess('Login successful', [
                'token' => $token,
                'user'  => $user
            ]);
        } catch (ValidationException $e) {
            return $this->showValidateError($e->getMessage());
        } catch (\Exception $e) {
            return $this->showFail($e->getMessage());
        }
    }

    public function authCheck(Request $request)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Token tidak ditemukan'], 401);
        }

        $tokenData = PersonalAccessToken::findToken($token);

        if (!$tokenData) {
            return response()->json(['message' => 'Token tidak valid'], 401);
        }

        if (!$tokenData->tokenable) {
            return response()->json(['message' => 'Token sudah expired atau pengguna tidak valid'], 401);
        }

        return response()->json([
            'message' => 'Token is valid',
            'user' => $tokenData->tokenable,
        ], 200);
    }
}
