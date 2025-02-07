<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\MessageResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

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
            'email' => 'required|email',
            'password' => 'required'
        ]);
        try {

            $user = $this->user->where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                throw new \Exception('Invalid credentials');
            }

            $token = $user->createToken('access_token')->plainTextToken;

            return $this->loginSuccess('Login successful', [
                'token' => $token,
                'user'  => $user
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation Error',
                'messages' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'Login Error',
                'message' => 'An error occurred during login. Please try again later.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
