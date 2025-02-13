<?php

namespace App\Http\Controllers;

use App\Models\TicketStatus;
use App\Traits\MessageResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketStatusController extends Controller
{
    use MessageResponse;
    protected $ticketStatus;

    public function __construct()
    {
        $this->ticketStatus = new TicketStatus();
    }

    public function index(Request $request)
    {
        try {
            DB::beginTransaction();

            $ticketStatus = $this->ticketStatus->get();

            DB::commit();

            return $this->showIndexOrFail($ticketStatus);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->showFail($e->getMessage());
        }
    }
}
