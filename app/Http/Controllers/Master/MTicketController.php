<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Traits\MessageResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MTicketController extends Controller
{
    use MessageResponse;
    protected $ticket;

    public function __construct()
    {
        $this->ticket = new Ticket();
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

            $ticket = $this->ticket->query();

            if ($request->has('where')) {
                $ticket->where($request->where);
            }

            if ($request->has('search')) {
                $ticket->where('subject', 'like', '%' . $request->search . '%');
            }

            $data = $ticket->paginate($per_page);

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
            'project_id'        => 'integer|required',
            'ticket_status_id'  => 'integer|required',
            'severity_id'       => 'integer|required',
            'subject'           => 'string|required',
            'code'              => 'string|required',
            'type'              => 'array|required',
            'description'       => 'string|required'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validate->errors()
            ], 400);
        }

        try {
            DB::beginTransaction();
            $ticket = $this->ticket->create([
                'project_id'        => $request->project_id,
                'ticket_status_id'  => $request->ticket_status_id,
                'severity_id'       => $request->severity_id,
                'subject'           => $request->subject,
                'code'              => $request->code,
                'type'              => json_encode($request->type),
                'description'       => $request->description
            ]);

            DB::commit();

            return $this->showCreateOrFail($ticket);
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
            $ticket = $this->ticket->where('id', $id)->first();

            DB::commit();
            return $this->showViewOrFail($ticket);
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
            'project_id'        => 'integer|required',
            'ticket_status_id'  => 'integer|required',
            'severity_id'       => 'integer|required',
            'subject'           => 'string|required',
            'code'              => 'string|required',
            'type'              => 'array|required',
            'description'       => 'string|required'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validate->errors()
            ], 400);
        }

        try {
            DB::beginTransaction();
            $ticket = $this->ticket->where('id', $id)->first();

            if (!$ticket) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data not found'
                ], 404);
            }

            $ticket->update([
                'project_id'        => $request->project_id,
                'ticket_status_id'  => $request->ticket_status_id,
                'severity_id'       => $request->severity_id,
                'subject'           => $request->subject,
                'code'              => $request->code,
                'type'              => json_encode($request->type),
                'description'       => $request->description
            ]);

            DB::commit();
            return $this->showUpdateOrFail($ticket);
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
            $ticket = $this->ticket->where('id', $id)->first();

            if (!$ticket) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data not found'
                ], 404);
            }

            $ticket->delete();

            DB::commit();
            return $this->showDestroyOrFail($ticket);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
