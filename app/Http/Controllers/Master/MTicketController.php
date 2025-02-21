<?php

namespace App\Http\Controllers\Master;

use App\Events\Ticket\CreateTicketEvent;
use App\Events\Ticket\DeleteTicketEvent;
use App\Events\Ticket\UpdateStatusTicketEvent;
use App\Events\Ticket\UpdateTicketEvent;
use App\Http\Controllers\Controller;
use App\Models\Attachment;
use App\Models\LogTicket;
use App\Models\Project;
use App\Models\Severity;
use App\Models\Ticket;
use App\Models\TicketAssign;
use App\Models\TicketStatus;
use App\Models\User;
use App\Traits\MessageResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class MTicketController extends Controller
{
    use MessageResponse;
    protected $ticket, $project, $attachment, $logTicket, $ticketAssign;

    public function __construct()
    {
        $this->ticket       = new Ticket();
        $this->project      = new Project();
        $this->attachment   = new Attachment();
        $this->logTicket    = new LogTicket();
        $this->ticketAssign = new TicketAssign();
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

            $ticket = $this->ticket->with(['attachment', 'project.company', 'ticketAssign' => function ($query) {
                $query->latest()->first();
            }, 'ticketAssign.user']);

            if ($request->has('where')) {
                $ticket->where($request->where);
            }

            if ($request->has('search')) {
                $ticket->where('subject', 'like', '%' . $request->search . '%');
            }
            if ($request->has('sort_by')) {
                $ticket->orderByRaw($request->sort_by);
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
            'file'              => 'nullable|file|mimes:jpg,jpeg,png,pdf,docx,mp4,avi,mkv,mov|max:20480',
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

            if ($request->has('file')) {
                $path = $request->file('file')->store('attachments', 'public');

                $ticket->attachment()->create([
                    'name'      => $request->file('file')->getClientOriginalName(),
                    'path'      => url('storage/' . $path),
                ]);
            }

            CreateTicketEvent::dispatch($ticket);

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
            $ticket = $this->ticket->with(['attachment', 'user', 'project.company', 'ticketAssign' => function ($query) {
                $query->latest()->first();
            }, 'ticketAssign.user'])->findOrFail($id);

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
            'file'              => 'nullable|file|mimes:jpg,jpeg,png,pdf,docx,mp4,avi,mkv,mov|max:20480',
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

            if ($request->has('user_id')) {
                $user = User::find($request->user_id);
                if ($user) {
                    $userName = $user->name;
                } else {
                    $userName = 'User tidak ditemukan';
                }

                $userExist = $this->ticketAssign
                    ->where('user_id', $request->user_id)
                    ->where('ticket_id', $ticket->id)->first();
                if (!$userExist) {
                    $ticket->ticketAssign()->create([
                        'user_id'           => $request->user_id,
                        'ticket_id'         => $ticket->id
                    ]);

                    $this->logTicket->create([
                        'user_id'           => Auth::user()->id,
                        'ticket_id'         => $ticket->id,
                        'ticket_status_id'  => $ticket->ticket_status_id,
                        'role_id'           => Auth::user()->role_id,
                        'description'       => "Penugasan user_id baru: {$userName}",
                    ]);
                } else {
                    $this->logTicket->create([
                        'user_id'           => Auth::user()->id,
                        'ticket_id'         => $ticket->id,
                        'ticket_status_id'  => $ticket->ticket_status_id,
                        'role_id'           => Auth::user()->role_id,
                        'description'       => "User_id {$userName} sudah ditugaskan kembali ke tiket.",
                    ]);
                }
            }

            $changes = $ticket->getChanges();

            foreach ($changes as $field => $newValue) {
                switch ($field) {
                    case 'severity_id':
                        $newValue = Severity::find($newValue)->name ?? 'Severity Tidak Ditemukan';
                        break;
                    case 'ticket_status_id':
                        $newValue = TicketStatus::find($newValue)->name ?? 'Status Tidak Ditemukan';
                        break;
                    case 'project_id':
                        $newValue = Project::find($newValue)->name ?? 'Proyek Tidak Ditemukan';
                        break;
                    default:
                        break;
                }

                $this->logTicket->create([
                    'user_id'           => Auth::user()->id,
                    'ticket_id'         => $ticket->id,
                    'ticket_status_id'  => $ticket->ticket_status_id,
                    'role_id'           => Auth::user()->role_id,
                    'description'       => "Perubahan pada {$field}: {$newValue}",
                ]);
            }

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

            UpdateTicketEvent::dispatch($ticket, $ticket->project_id);

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

            broadcast(new DeleteTicketEvent($ticket, $ticket->project_id))->toOthers();

            if ($ticket->attachment) {
                $path = str_replace(url('storage'), '', $ticket->attachment->path);
                Storage::disk('public')->delete($path);

                $ticket->attachment()->delete();
            }

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

            $oldStatusName = TicketStatus::find($oldStatus)->name ?? 'Status Tidak Ditemukan';
            $newStatusName = TicketStatus::find($newStatus)->name ?? 'Status Tidak Ditemukan';

            if ($oldStatus == $newStatus) {
                return response()->json([
                    'status' => 'success',
                    'messages' => 'Status tidak Berubah',
                ], 200);
            }

            $ticket->update([
                'ticket_status_id' => $newStatus,
            ]);

            broadcast(new UpdateStatusTicketEvent($ticket, $ticket->project_id))->toOthers();

            $this->logTicket->create([
                'user_id'          => Auth::id(),
                'ticket_id'        => $ticket->id,
                'ticket_status_id' => $newStatus,
                'role_id'          => Auth::user()->role_id,
                'description'      => "Status ticket diubah dari {$oldStatusName} ke {$newStatusName}"
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'messages' => 'Status Update From ' . $oldStatusName . ' To ' . $newStatusName,
                'data' => $ticket
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->showFail($e->getMessage());
        }
    }
}
