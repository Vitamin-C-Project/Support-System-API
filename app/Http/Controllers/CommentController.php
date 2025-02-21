<?php

namespace App\Http\Controllers;

use App\Events\Comment\CreateCommentEvent;
use App\Events\Comment\DeleteCommentEvent;
use App\Models\Comment;
use App\Traits\MessageResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    use MessageResponse;
    protected $comment;

    public function __construct()
    {
        $this->comment = new Comment();
    }

    public function index(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'per_page'          => 'integer|required',
            "search"            => 'string|nullable',
            'where'             => 'array|nullable',
            'sort_by'           => 'nullable',
        ]);

        if ($validate->fails()) {
            return $this->showValidateError($validate->errors());
        }

        $per_page = $request->input('per_page', 10);

        try {

            DB::beginTransaction();

            $comment = $this->comment->with('attachment', 'user');

            if ($request->has('where')) {
                $comment->where($request->where);
            }

            if ($request->has('sort_by')) {
                $comment->orderByRaw($request->sort_by);
            }

            $data = $comment->paginate($per_page);

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
            'ticket_id'     => 'integer|required',
            'description'   => 'string|required',
            'file'          => 'nullable|file|mimes:jpg,jpeg,png,pdf,docx,mp4,avi,mkv,mov|max:20480',
        ]);

        if ($validate->fails()) {
            return $this->showValidateError($validate->errors());
        }

        try {
            DB::beginTransaction();
            $comment = $this->comment->create([
                'user_id'       => Auth::id(),
                'ticket_id'     => $request->ticket_id,
                'description'   => $request->description
            ]);

            if ($request->hasFile('file')) {
                $path = $request->file('file')->store('comments', 'public');

                $comment->attachment()->create([
                    'name'      => $request->file('file')->getClientOriginalName(),
                    'path'      => url('storage/' . $path),
                ]);
            }

            CreateCommentEvent::dispatch($comment->load(['attachment', 'user']), $request->ticket_id);

            DB::commit();

            return $this->showCreateOrFail($comment);
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
            $comment = $this->comment->with('attachment')->findOrFail($id);

            if (!$comment) {
                return $this->showNotFound($comment);
            }

            DB::commit();
            return $this->showViewOrFail($comment);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->showFail($e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $validate = Validator::make($request->all(), [
            'ticket_id'     => 'integer|required',
            'description'   => 'string|required',
            'file'          => 'nullable|file|mimes:jpg,jpeg,png,pdf,docx,mp4,avi,mkv,mov|max:20480',
        ]);

        if ($validate->fails()) {
            return $this->showValidateError($validate->errors());
        }

        try {
            DB::beginTransaction();
            $comment = $this->comment->findOrFail($id);

            if (!$comment) {
                return $this->showNotFound($comment);
            }

            $comment->update([
                'user_id'       => Auth::id(),
                'ticket_id'     => $request->ticket_id,
                'description'   => $request->description
            ]);

            if ($request->hasFile('file')) {
                if ($comment->attachment) {
                    $path = str_replace(url('storage'), '', $comment->attachment->path);
                    Storage::disk('public')->delete($path);
                }

                $path = $request->file('file')->store('comments', 'public');

                $comment->attachment()->where('id', $comment->attachment->id)->update([
                    'name'      => $request->file('file')->getClientOriginalName(),
                    'path'      => url('storage/' . $path),
                ]);
            }

            DB::commit();
            return $this->showUpdateOrFail($comment);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->showFail($e->getMessage());
        }
    }

    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();

        $comment = $this->comment->where('id', $id)->first();

        if (!$comment) {
            return $this->showNotFound(["id" => $id]);
        }


        try {
            DeleteCommentEvent::dispatch($comment->load(['attachment', 'user']), $comment->ticket_id);

            if ($comment->attachment) {
                $path = str_replace(url('storage'), '', $comment->attachment->path);
                Storage::disk('public')->delete($path);

                $comment->attachment()->delete();
            }

            $comment->delete();

            DB::commit();
            return $this->showDestroyOrFail($comment);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->showFail($e->getMessage());
        }
    }
}
