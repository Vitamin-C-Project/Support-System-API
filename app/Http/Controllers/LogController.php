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

            $logTicket = $this->logTicket->with(['ticket', 'ticket.user', 'ticket.project.company', 'role'])
                // ->where('ticket_id', $request->id)
                ->orderBy('created_at', 'desc')
                ->get();

            DB::commit();
            return $this->showViewOrFail($logTicket);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->showFail($e->getMessage());
        }
    }
}
