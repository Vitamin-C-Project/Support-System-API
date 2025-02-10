<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Traits\MessageResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    use MessageResponse;
    protected $role;

    public function __construct()
    {
        $this->role = new Role();
    }

    public function index(Request $request)
    {
        try {
            DB::beginTransaction();

            $role = $this->role->get();

            DB::commit();

            return $this->showIndexOrFail($role);
        } catch (\Exception) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Data not found'
            ], 500);
        }
    }
}
