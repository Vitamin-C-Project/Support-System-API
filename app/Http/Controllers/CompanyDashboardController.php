<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\TicketStatus;
use App\Traits\MessageResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CompanyDashboardController extends Controller
{
    use MessageResponse;

    protected $project, $ticket, $ticketStatus;

    public function __construct()
    {
        $this->project = new Project();
        $this->ticket = new Ticket();
        $this->ticketStatus = new TicketStatus();
    }
    public function countTicketStatus(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'company_id' => 'integer|required',
            'project_id' => 'integer|nullable',
        ]);

        if ($validate->fails()) {
            return $this->showValidateError($validate->errors());
        }

        try {
            DB::beginTransaction();

            $projects = $this->project
                ->where('company_id', $request->company_id)
                ->when($request->filled('project_id'), function ($query) use ($request) {
                    return $query->where('id', $request->project_id);
                })
                ->pluck('id');

            $ticketStatusCounts = $this->ticket
                ->whereIn('project_id', $projects)
                ->whereIn('ticket_status_id', [1, 3, 4, 8])
                ->with('status')
                ->select('ticket_status_id', DB::raw('count(*) as count'))
                ->groupBy('ticket_status_id')
                ->get();

            $allStatuses = $this->ticketStatus
                ->whereIn('id', [1, 3, 4, 8])
                ->pluck('name', 'id');

            $response = collect([1, 3, 4, 8])->map(function ($statusId) use ($ticketStatusCounts, $allStatuses) {
                return [
                    'status_id' => $statusId,
                    'status_name' => $allStatuses[$statusId] ?? 'Unknown',
                    'count' => $ticketStatusCounts->firstWhere('ticket_status_id', $statusId)->count ?? 0
                ];
            });

            DB::commit();
            return $this->showIndexOrFail($response);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->showFail($e->getMessage());
        }
    }
}
