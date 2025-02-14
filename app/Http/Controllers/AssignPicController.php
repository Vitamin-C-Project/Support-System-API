<?php

namespace App\Http\Controllers;

use App\Models\AssignPic;
use App\Models\Project;
use App\Models\User;
use App\Traits\MessageResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AssignPicController extends Controller
{
    use MessageResponse;
    protected $pic, $user, $project;

    public function __construct()
    {
        $this->pic = new AssignPic();
        $this->user = new User();
        $this->project = new Project();
    }

    public function UserExist(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'user_id'       => 'integer|required',
            'project_id'    => 'integer|required'
        ]);

        if ($validate->fails()) {
            return $this->showValidateError($validate->errors());
        }

        try {
            DB::beginTransaction();

            $exist = $this->pic
                ->where('user_id', $request->user_id)
                ->where('project_id', $request->project_id)->first();

            if ($exist) {
                $projectName = $this->project->where('id', $request->project_id)->value('name');
                return $this->showFail("User already exists in project $projectName");
            }

            $data = $this->pic->create([
                'user_id'       => $request->user_id,
                'project_id'    => $request->project_id
            ]);

            DB::commit();

            return $this->showCreateOrFail($data);
        } catch (\Exception $e) {
            return $this->showFail($e->getMessage());
        }
    }


    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'name'          => 'string|required',
            'email'         => 'email|required|unique:users,email',
            'password'      => 'string|required|min:8',
            'role_id'       => 'integer|required|exists:roles,id',
            'status'        => 'required|in:0,1',
            'project_id'    => 'integer|required|exists:projects,id'
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

            $pic = $this->pic->create([
                'user_id'       => $user->id,
                'project_id'    => $request->project_id
            ]);

            DB::commit();

            return $this->showCreateOrFail([
                'user' => $user,
                'pic'  => $pic
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->showFail($e->getMessage());
        }
    }

    public function destroy(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'user_id'    => 'integer|required',
            'company_id' => 'integer|required'
        ]);

        if ($validate->fails()) {
            return $this->showValidateError($validate->errors());
        }

        try {
            DB::beginTransaction();

            $pic = $this->pic->whereHas('project', function ($query) use ($request) {
                $query->where('company_id', $request->company_id);
            })->where('user_id', $request->user_id)->first();

            if (!$pic) {
                return $this->showFail("User tidak ditemukan dalam perusahaan ini.");
            }

            $pic->delete();

            DB::commit();
            return $this->showDestroyOrFail($pic);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->showFail($e->getMessage());
        }
    }
}
