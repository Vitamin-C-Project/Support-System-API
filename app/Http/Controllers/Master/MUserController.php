<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\MessageResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class MUserController extends Controller
{
    use MessageResponse;
    protected $user;

    public function __construct()
    {
        $this->user = new User();
    }


    public function index(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'per_page'  => 'integer|required',
            'status'    => 'integer|nullable',
            'role_id'   => 'integer|nullable',
            "search"    => 'string|nullable'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validate->errors()
            ], 400);
        }

        $per_page = $request->input('per_page', 10);

        try {

            DB::beginTransaction();

            $user = $this->user->query();

            if ($request->has('role_id')) {
                $user->where('role_id', $request->role_id);
            }

            if ($request->has('status')) {
                $user->where('status', $request->status);
            }

            if ($request->has('search')) {
                $user->where('name', 'like', '%' . $request->search . '%');
            }

            $data = $user->paginate($per_page);

            DB::commit();

            return $this->showIndexOrFail($data);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'name'      => 'string|required',
            'email'     => 'email|required',
            'password'  => 'string|required',
            'role_id'   => 'integer|required',
            'status'    => 'integer|required'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validate->errors()
            ], 400);
        }

        try {
            DB::beginTransaction();
            $user = $this->user->create([
                'name'      => $request->name,
                'email'     => $request->email,
                'password'  => Hash::make($request->password),
                'role_id'   => $request->role_id,
                'status'    => $request->status
            ]);

            DB::commit();

            return $this->showCreateOrFail($user);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $id)
    {
        $validate = Validator::make($request->all(), [
            'id' => 'id|integer',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => JsonResponse::HTTP_BAD_REQUEST,
                'message' => $validate->errors()
            ], 400);
        }

        try {
            DB::beginTransaction();
            $user = $this->user->where('id', $id)->first();

            DB::commit();
            return $this->showViewOrFail($user);
        } catch (\Exception) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Data not found'
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validate = Validator::make($request->all(), [
            'name'      => 'string|nullable',
            'email'     => 'email|nullable',
            'password'  => 'string|nullable',
            'role_id'   => 'integer|nullable',
            'status'    => 'integer|nullable'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validate->errors()
            ], 400);
        }

        try {
            DB::beginTransaction();
            $user = $this->user->where('id', $id)->first();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data not found'
                ], 404);
            }

            $user->update([
                'name'      => $request->name ?? $user->name,
                'email'     => $request->email ?? $user->email,
                'password'  => Hash::make($request->password) ?? $user->password,
                'role_id'   => $request->role_id ?? $user->role_id,
                'status'    => $request->status ?? $user->status
            ]);

            DB::commit();
            return $this->showUpdateOrFail($user);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
