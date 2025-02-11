<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\MessageResponse;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    use MessageResponse;
    protected $user;

    public function __construct()
    {
        $this->user = new User();
    }

    public function logout(Request $request)
    {
        try {

            $request->user()->currentAccessToken()->delete();

            return $this->logoutSuccess('Logout successful', [
                'user'  => $request->user()
            ]);
        } catch (\Exception $e) {
            return $this->showFail($e->getMessage());
        }
    }
}
