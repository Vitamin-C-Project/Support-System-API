<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Attachment;
use App\Models\LogTicket;
use App\Models\Project;
use App\Models\Ticket;
use App\Traits\MessageResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class MTicketController extends Controller
{
    use MessageResponse;
    protected $ticket, $project, $attachment, $logTicket;

    public function __construct()
    {
        $this->ticket       = new Ticket();
        $this->project      = new Project();
        $this->attachment   = new Attachment();
        $this->logTicket    = new LogTicket();
    }

    public function index(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'per_page'      => 'integer|required',
            "search"        => 'string|nullable',
            'where'         => 'array|nullable',
        ]);

        if ($validate->fails()) {
            return $this->showValidateError($validate->errors());
        }

        $per_page = $request->input('per_page', 10);

        try {
            DB::beginTransaction();

            $ticket = $this->ticket->with(['attachment', 'project.company']);

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
            'description'       => 'string|required',
            'file'              => 'required|file|mimes:jpg,jpeg,png,pdf,docx|max:2048'
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
                'description'       => $request->description,
            ]);

            $path = $request->file('file')->store('attachments', 'public');

            $ticket->attachment()->create([
                'name'      => $request->file('file')->getClientOriginalName(),
                'path'      => url('storage/' . $path),
            ]);

            $this->logTicket->create([
                'user_id'           => Auth::user()->id,
                'ticket_id'         => $ticket->id,
                'ticket_status_id'  => $request->ticket_status_id,
                'role_id'           => Auth::user()->role_id,
                'description'       => 'Ticket Baru Dibuat',
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
            $ticket = $this->ticket->with(['attachment', 'user', 'project.company'])->findOrFail($id);

            DB::commit();
            return $this->showViewOrFail($ticket);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->showFail($e->getMessage());
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
            'description'       => 'string|required',
            'file'              => 'required|file|mimes:jpg,jpeg,png,pdf,docx|max:2048'
        ]);

        if ($validate->fails()) {
            return $this->showValidateError($validate->errors());
        }

        try {
            DB::beginTransaction();

            $ticket = $this->ticket->findOrFail($id);

            if (!$ticket) {
                return $this->showNotFound($ticket);
            }

            if ($ticket->project_id != $request->project_id) {
                $project = Project::find($request->project_id);
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
                $newCode = "{$initial}-{$newNumber}";
            } else {
                $newCode = $ticket->code;
            }

            $ticket->update([
                'user_id'           => Auth::user()->id,
                'project_id'        => $request->project_id,
                'ticket_status_id'  => $request->ticket_status_id,
                'severity_id'       => $request->severity_id,
                'subject'           => $request->subject,
                'code'              => $newCode,
                'type'              => json_encode($request->type),
                'description'       => $request->description,
            ]);

            $this->logTicket->create([
                'user_id'           => Auth::user()->id,
                'ticket_id'         => $ticket->id,
                'ticket_status_id'  => $request->ticket_status_id,
                'role_id'           => Auth::user()->role_id,
                'description'       => 'Ticket Baru Dibuat',
            ]);

            if ($request->hasFile('file')) {
                if ($ticket->attachment) {
                    $path = str_replace(url('storage'), '', $ticket->attachment->path);
                    Storage::disk('public')->delete($path);
                }

                $path = $request->file('file')->store('attachments', 'public');

                $ticket->attachment()->where('id', $ticket->attachment->id)->update([
                    'name'      => $request->file('file')->getClientOriginalName(),
                    'path'      => url('storage/' . $path),
                ]);
            }

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
            $ticket = $this->ticket->findOrFail($id);

            $ticket->delete();

            DB::commit();
            return $this->showDestroyOrFail($ticket);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->showFail($e->getMessage());
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $validate = Validator::make($request->all(), [
            'ticket_status_id'      => 'integer|required',
        ]);

        if ($validate->fails()) {
            return $this->showValidateError($validate->errors());
        }

        try {
            DB::beginTransaction();

            $ticket = $this->ticket->findOrFail($id);

            $oldStatus = $ticket->ticket_status_id;
            $newStatus = $request->ticket_status_id;

            if ($oldStatus == $newStatus) {
                return $this->showFail('Status tidak berubah');
            }

            $ticket->update([
                'ticket_status_id' => $newStatus,
            ]);

            $this->logTicket->create([
                'user_id'          => Auth::id(),
                'ticket_id'        => $ticket->id,
                'ticket_status_id' => $newStatus,
                'role_id'          => Auth::user()->role_id,
                'description'      => "Status ticket diubah dari {$oldStatus} ke {$newStatus}"
            ]);

            DB::commit();

            return $this->showUpdateOrFail($ticket);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->showFail($e->getMessage());
        }
    }
}
