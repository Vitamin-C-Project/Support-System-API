<?php

namespace App\Http\Controllers;

use App\Models\LogTicket;
use App\Traits\MessageResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LogController extends Controller
{
    use MessageResponse;
    protected $logTicket;

    public function __construct()
    {
        $this->logTicket = new LogTicket();
    }
    public function index(Request $request)
    {
        try {
            DB::beginTransaction();

            $logTicketQuery = $this->logTicket->with(['ticket', 'ticket.user', 'ticket.project.company', 'role', 'user'])
                ->orderBy('created_at', 'desc');

            if ($request->has('ticket_id')) {
                $logTicketQuery->where('ticket_id', $request->ticket_id);
            }

            $logTicket = $logTicketQuery->get();

            DB::commit();
            return $this->showViewOrFail($logTicket);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->showFail($e->getMessage());
        }
    }
}
