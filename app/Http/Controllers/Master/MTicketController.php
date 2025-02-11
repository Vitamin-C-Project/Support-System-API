<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Ticket;
use App\Traits\MessageResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MTicketController extends Controller
{
    use MessageResponse;
    protected $ticket, $project;

    public function __construct()
    {
        $this->ticket = new Ticket();
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
            return $this->showFail($e->getMessage());
        }
    }


    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'project_id'        => 'integer|required',
            'ticket_status_id'  => 'integer|required',
            'severity_id'       => 'integer|required',
            'subject'           => 'string|required',
            'type'              => 'array|required',
            'description'       => 'string|required'
        ]);

        if ($validate->fails()) {
            return $this->showValidateError($validate->errors());
        }

        try {
            DB::beginTransaction();

            $project = $this->project::find($request->project_id);
            $initial = 'PCS';

            if ($project) {
                $words = explode(' ', $project->name);
                $initials = array_map(fn($word) => strtoupper(substr($word, 0, 1)), $words);
                $initial = implode('', array_slice($initials, 0, 2));
            }

            $lastTicket = $this->ticket->where('project_id', $request->project_id)
                ->whereNotNull('code')
                ->latest('id')
                ->value('code');

            if ($lastTicket) {
                $lastNumber = (int) substr($lastTicket, -3);
            } else {
                $lastNumber = 0;
            }

            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);

            $code = "{$initial}-{$newNumber}";

            $ticket = $this->ticket->create([
                'user_id'           => Auth::user()->id,
                'project_id'        => $request->project_id,
                'ticket_status_id'  => $request->ticket_status_id,
                'severity_id'       => $request->severity_id,
                'subject'           => $request->subject,
                'code'              => $code,
                'type'              => json_encode($request->type),
                'description'       => $request->description
            ]);

            DB::commit();

            return $this->showCreateOrFail($ticket);
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
            $ticket = $this->ticket->where('id', $id)->first();

            DB::commit();
            return $this->showViewOrFail($ticket);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->showNotFound($e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $validate = Validator::make($request->all(), [
            'project_id'        => 'integer|required',
            'ticket_status_id'  => 'integer|required',
            'severity_id'       => 'integer|required',
            'subject'           => 'string|required',
            'type'              => 'array|required',
            'description'       => 'string|required'
        ]);

        if ($validate->fails()) {
            return $this->showValidateError($validate->errors());
        }

        try {
            DB::beginTransaction();
            $ticket = $this->ticket->where('id', $id)->first();

            if (!$ticket) {
                return $this->showNotFound($ticket);
            }

            $ticket->update([
                'user_id'           => Auth::user()->id,
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
            return $this->showFail($e->getMessage());
        }
    }
}
