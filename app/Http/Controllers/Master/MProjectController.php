<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Traits\MessageResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MProjectController extends Controller
{
    use MessageResponse;
    protected $project;

    public function __construct()
    {
        $this->project = new Project();
    }

    public function index(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'per_page'          => 'integer|required',
            "search"            => 'string|nullable',
            'where'             => 'array|nullable',
        ]);

        if ($validate->fails()) {
            return $this->showValidateError($validate->errors());
        }

        $per_page = $request->input('per_page', 10);

        try {

            DB::beginTransaction();

            $project = $this->project->query();

            if ($request->has('where')) {
                $project->where($request->where);
            }

            if ($request->has('search')) {
                $project->where('name', 'like', '%' . $request->search . '%');
            }

            if ($request->has('project_id')) {
                $project->whereHas('assignPic.user', function ($q) use ($request) {
                    $q->where('id', 1);
                });
            }

            $data = $project->paginate($per_page);

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
            'company_id'        => 'integer|required|exists:companies,id',
            'name'              => 'string|required',
            'type'              => 'array|required',
            'server_address'    => 'string|required',
            'domain'            => 'string|required',
            'status'            => 'integer|required',
            'expired_at'        => 'date|required',
        ]);

        if ($validate->fails()) {
            return $this->showValidateError($validate->errors());
        }

        try {
            DB::beginTransaction();
            $project = $this->project->create([
                'company_id' => $request->company_id,
                'user_id'    => Auth::user()->id,
                'name'       => $request->name,
                'type'       => json_encode($request->type),
                'server'     => $request->server_address,
                'domain'     => $request->domain,
                'expired_at' => $request->expired_at,
                'status'     => $request->status,
            ]);

            DB::commit();

            return $this->showCreateOrFail($project);
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
            return $this->showValidateError($validate->errors());
        }

        try {
            DB::beginTransaction();
            $project = $this->project->where('id', $id)->first();

            if (!$project) {
                return $this->showNotFound($project);
            }

            DB::commit();
            return $this->showViewOrFail($project);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->showFail($e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $validate = Validator::make($request->all(), [
            'company_id'        => 'integer|required|exists:companies,id',
            'name'              => 'string|required',
            'type'              => 'array|required',
            'server_address'    => 'string|required',
            'domain'            => 'string|required',
            'status'            => 'integer|required',
            'expired_at'        => 'date|required',
        ]);

        if ($validate->fails()) {
            return $this->showValidateError($validate->errors());
        }

        try {
            DB::beginTransaction();
            $project = $this->project->where('id', $id)->first();

            if (!$project) {
                return $this->showNotFound($project);
            }

            $project->update([
                'company_id' => $request->company_id,
                'user_id'    => Auth::user()->id,
                'name'       => $request->name,
                'type'       => json_encode($request->type),
                'server'     => $request->server_address,
                'domain'     => $request->domain,
                'expired_at' => $request->expired_at,
                'status'     => $request->status,
            ]);

            DB::commit();
            return $this->showUpdateOrFail($project);
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
            return $this->showValidateError($validate->errors());
        }

        try {
            DB::beginTransaction();
            $project = $this->project->where('id', $id)->first();

            if (!$project) {
                return $this->showNotFound($project);
            }

            $project->delete();

            DB::commit();
            return $this->showDestroyOrFail($project);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->showFail($e->getMessage());
        }
    }
}
