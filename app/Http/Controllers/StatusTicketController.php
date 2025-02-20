<?php

namespace App\Http\Controllers;

use App\Models\TicketStatus;
use App\Traits\MessageResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StatusTicketController extends Controller
{
    use MessageResponse;
    protected $status;
    public function __construct()
    {
        $this->status = new TicketStatus();
    }
    public function index(Request $request)
    {
        try {
            DB::beginTransaction();

            $statusQuery = $this->status->with(['ticket' => function ($query) use ($request) {
                if ($request->has('project_id')) {
                    $query->where('project_id', $request->project_id);
                }
            }]);

            $statusData = $statusQuery->get();

            DB::commit();
            return $this->showViewOrFail($statusData);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->showFail($e->getMessage());
        }
    }
}
