<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\MessageResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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
            'company_id' => 'integer|nullable',
            'per_page'  => 'integer|required',
            "search"    => 'string|nullable',
            'where'     => 'array|nullable'
        ]);

        if ($validate->fails()) {
            return $this->showValidateError($validate->errors());
        }

        $per_page = $request->input('per_page', 10);

        try {

            DB::beginTransaction();

            $user = $this->user->query();

            if ($request->has('where')) {
                $user->where($request->where);
            }

            if ($request->has('search')) {
                $user->where('name', 'like', '%' . $request->search . '%');
            }

            if ($request->has('company_id')) {
                $user->whereHas('assignPic.project.company', function ($q) use ($request) {
                    $q->where('id', $request->company_id);
                });
            }

            $data = $user->paginate($per_page);

            DB::commit();

            return $this->showIndexOrFail($data);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->showFail($e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'name'      => 'string|required',
            'email'     => 'email|required|unique:users,email',
            'password'  => 'string|required|min:8',
            'role_id'   => 'integer|required|exists:roles,id',
            'status'    => 'required|in:0,1'
        ]);

        if ($validate->fails()) {
            return $this->showValidateError($validate->errors());
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
            return $this->showFail($e->getMessage());
        }
    }

    public function show(Request $request, $id)
    {
        $validate = Validator::make($request->all(), [
            'id' => 'id|integer',
        ]);

        if ($validate->fails()) {
            return $this->showNotFound($validate->errors());
        }

        try {
            DB::beginTransaction();
            $user = $this->user->where('id', $id)->first();

            DB::commit();
            return $this->showViewOrFail($user);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->showFail($e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $validate = Validator::make($request->all(), [
            'name'      => 'string|required',
            'email'     => [
                'email',
                'required',
                Rule::unique('users', 'email')->ignore($id),
            ],
            'password'  => 'string|nullable|min:8',
            'role_id'   => 'integer|required|exists:roles,id',
            'status'    => 'required|in:0,1'
        ]);


        if ($validate->fails()) {
            return $this->showValidateError($validate->errors());
        }

        try {
            DB::beginTransaction();
            $user = $this->user->where('id', $id)->first();

            if (!$user) {
                return $this->showNotFound($user);
            }

            $data = [
                'name'      => $request->name ?? $user->name,
                'email'     => $request->email ?? $user->email,
                'role_id'   => $request->role_id ?? $user->role_id,
                'status'    => $request->status ?? $user->status
            ];

            if ($request->password) {
                $data['password'] = Hash::make($request->password);
            }
            $user->update($data);

            DB::commit();
            return $this->showUpdateOrFail($user);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->showFail($e->getMessage());
        }
    }

    public function destroy(Request $request, $id)
    {
        $validate = Validator::make($request->all(), [
            'id' => 'id|integer',
        ]);

        if ($validate->fails()) {
            return $this->showNotFound($validate->errors());
        }

        try {
            DB::beginTransaction();
            $user = $this->user->where('id', $id)->first();

            if (!$user) {
                return $this->showNotFound($user);
            }

            $user->delete();

            DB::commit();
            return $this->showDestroyOrFail($user);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->showFail($e->getMessage());
        }
    }


    public function activeUser(Request $request, $id)
    {
        $validate = Validator::make($request->all(), [
            'status'    => 'required|in:0,1',
        ]);

        if ($validate->fails()) {
            return $this->showNotFound($validate->errors());
        }

        try {

            DB::beginTransaction();
            $user = $this->user->findOrFail($id);

            if (!$user) {
                return $this->showNotFound($user);
            }

            $user->update([
                'status' => $request->status
            ]);

            DB::commit();
            return $this->showUpdateOrFail($user);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->showFail($e->getMessage());
        }
    }
}
