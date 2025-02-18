<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Traits\MessageResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            return $this->showValidateError($validate->errors());
        }

        $per_page = $request->input('per_page', 10);

        try {

            DB::beginTransaction();

            $company = $this->company->with('user');

            if ($request->has('where')) {
                $company->where($request->where);
            }

            if ($request->has('search')) {
                $company->where('name', 'like', '%' . $request->search . '%');
            }

            if ($request->has('project_id')) {
                $company->whereHas('project', function ($query) use ($request) {
                    $query->where('id', $request->project_id);
                });
            }

            $data = $company->paginate($per_page);

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
            'name'              => 'string|required',
            'type'              => 'array|required',
            'city'              => 'string|required',
            'zip_code'          => 'integer|required',
            'address'           => 'string|required',
            'status'            => 'integer|required',
        ]);

        if ($validate->fails()) {
            return $this->showValidateError($validate->errors());
        }

        try {
            DB::beginTransaction();
            $company = $this->company->create([
                'user_id'    => Auth::user()->id,
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
            $company = $this->company->where('id', $id)->first();

            if (!$company) {
                return $this->showNotFound($company);
            }

            DB::commit();
            return $this->showViewOrFail($company);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->showFail($e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $validate = Validator::make($request->all(), [
            'name'              => 'string|required',
            'type'              => 'array|required',
            'city'              => 'string|required',
            'zip_code'          => 'integer|required',
            'address'           => 'string|required',
            'status'            => 'integer|required',
        ]);

        if ($validate->fails()) {
            return $this->showValidateError($validate->errors());
        }

        try {
            DB::beginTransaction();
            $company = $this->company->where('id', $id)->first();

            if (!$company) {
                return $this->showNotFound($company);
            }

            $company->update([
                'user_id'    => Auth::user()->id,
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
            $company = $this->company->where('id', $id)->first();

            if (!$company) {
                return $this->showNotFound($company);
            }

            $company->delete();

            DB::commit();
            return $this->showDestroyOrFail($company);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->showFail($e->getMessage());
        }
    }
}
