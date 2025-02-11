<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Severity;
use App\Traits\MessageResponse;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class MSeverityController extends Controller
{
    use MessageResponse;
    protected $severity;

    public function __construct()
    {
        $this->severity = new Severity();
    }


    public function index(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'per_page'  => 'integer|required',
            "search"    => 'string|nullable',
            "where"     => 'array|nullable'
        ]);

        if ($validate->fails()) {
            return $this->showValidateError($validate->errors());
        }

        $per_page = $request->input('per_page', 10);

        try {

            DB::beginTransaction();

            $severity = $this->severity->query();

            if ($request->has('where')) {
                $severity->where($request->where);
            }

            if ($request->has('search')) {
                $severity->where('name', 'like', '%' . $request->search . '%');
            }

            $data = $severity->paginate($per_page);

            DB::commit();

            return $this->showIndexOrFail($data);
        } catch (QueryException) {
            DB::rollback();
            return $this->showNotFound('Data not found');
        } catch (\Exception $e) {
            DB::rollback();
            return $this->showFail($e->getMessage());
        }
    }


    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'name'          => 'string|required',
            'description'   => 'string|required',
            'estimated_day'  => 'date|required',
            'status'        => 'integer|required'
        ]);

        if ($validate->fails()) {
            return $this->showValidateError($validate->errors());
        }

        try {
            DB::beginTransaction();
            $severity = $this->severity->create([
                'name'              => $request->name,
                'description'       => $request->description,
                'estimated_day'     => $request->estimated_day,
                'status'            => $request->status
            ]);

            DB::commit();

            return $this->showCreateOrFail($severity);
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
            $severity = $this->severity->where('id', $id)->first();

            DB::commit();
            return $this->showViewOrFail($severity);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->showFail($e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $validate = Validator::make($request->all(), [
            'name'          => 'string|required',
            'description'   => 'string|required',
            'estimated_day'  => 'date|required',
            'status'        => 'integer|required'
        ]);

        if ($validate->fails()) {
            return $this->showValidateError($validate->errors());
        }

        try {
            DB::beginTransaction();
            $severity = $this->severity->where('id', $id)->first();

            if (!$severity) {
                return $this->showNotFound($severity);
            }

            $severity->update([
                'name'              => $request->name,
                'description'       => $request->description,
                'estimated_day'     => $request->estimated_day,
                'status'            => $request->status
            ]);

            DB::commit();
            return $this->showUpdateOrFail($severity);
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
            $severity = $this->severity->where('id', $id)->first();

            if (!$severity) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data not found'
                ], 404);
            }

            $severity->delete();

            DB::commit();
            return $this->showDestroyOrFail($severity);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->showFail($e->getMessage());
        }
    }
}
