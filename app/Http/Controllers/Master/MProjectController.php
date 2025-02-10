<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Traits\MessageResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
            return response()->json([
                'status' => 'error',
                'message' => $validate->errors()
            ], 400);
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

            $data = $project->paginate($per_page);

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
            'name'              => 'string|required',
            'user_id'           => 'string|required',
            'type'              => 'array|required',
            'server_address'    => 'string|required',
            'domain'            => 'string|required',
            'status'            => 'integer|required',
            'expired_at'        => 'date|required',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validate->errors()
            ], 400);
        }

        try {
            DB::beginTransaction();
            $project = $this->project->create([
                'name'       => $request->name,
                'user_id'    => $request->user_id,
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
            $project = $this->project->where('id', $id)->first();

            DB::commit();
            return $this->showViewOrFail($project);
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
            'name'              => 'string|required',
            'user_id'           => 'string|required',
            'type'              => 'array|required',
            'server_address'    => 'string|required',
            'domain'            => 'string|required',
            'status'            => 'integer|required',
            'expired_at'        => 'date|required',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validate->errors()
            ], 400);
        }

        try {
            DB::beginTransaction();
            $project = $this->project->where('id', $id)->first();

            if (!$project) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data not found'
                ], 404);
            }

            $project->update([
                'name'       => $request->name,
                'user_id'    => $request->user_id,
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
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
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
            $project = $this->project->where('id', $id)->first();

            if (!$project) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data not found'
                ], 404);
            }

            $project->delete();

            DB::commit();
            return $this->showDestroyOrFail($project);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
