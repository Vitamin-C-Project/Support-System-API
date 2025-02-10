<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Traits\MessageResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MCompanyController extends Controller
{
    use MessageResponse;
    protected $company;

    public function __construct()
    {
        $this->company = new Company();
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

            $company = $this->company->query();

            if ($request->has('where')) {
                $company->where($request->where);
            }

            if ($request->has('search')) {
                $company->where('name', 'like', '%' . $request->search . '%');
            }

            $data = $company->paginate($per_page);

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
            'user_id'           => 'integer|required',
            'name'              => 'string|required',
            'type'              => 'array|required',
            'city'              => 'string|required',
            'zip_code'          => 'integer|required',
            'address'           => 'string|required',
            'status'            => 'integer|required',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validate->errors()
            ], 400);
        }

        try {
            DB::beginTransaction();
            $company = $this->company->create([
                'user_id'    => $request->user_id,
                'name'       => $request->name,
                'type'       => json_encode($request->type),
                'city'       => $request->city,
                'zip_code'   => $request->zip_code,
                'address'    => $request->address,
                'status'     => $request->status,
            ]);

            DB::commit();

            return $this->showCreateOrFail($company);
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
            $company = $this->company->where('id', $id)->first();

            DB::commit();
            return $this->showViewOrFail($company);
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
            'user_id'           => 'integer|required',
            'name'              => 'string|required',
            'type'              => 'array|required',
            'city'              => 'string|required',
            'zip_code'          => 'integer|required',
            'address'           => 'string|required',
            'status'            => 'integer|required',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validate->errors()
            ], 400);
        }

        try {
            DB::beginTransaction();
            $company = $this->company->where('id', $id)->first();

            if (!$company) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data not found'
                ], 404);
            }

            $company->update([
                'user_id'    => $request->user_id,
                'name'       => $request->name,
                'type'       => json_encode($request->type),
                'city'       => $request->city,
                'zip_code'   => $request->zip_code,
                'address'    => $request->address,
                'status'     => $request->status,
            ]);

            DB::commit();
            return $this->showUpdateOrFail($company);
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
            $company = $this->company->where('id', $id)->first();

            if (!$company) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data not found'
                ], 404);
            }

            $company->delete();

            DB::commit();
            return $this->showDestroyOrFail($company);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
