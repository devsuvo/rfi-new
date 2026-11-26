<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Result;
use App\Support\Access;
use Illuminate\Http\Request;

/** Admin and teachers check writing: band + feedback, saved as a draft until "Send to student". */
class WritingController extends Controller
{
    public function index(Request $request)
    {
        $q = Access::scopeResults(Result::with('user')->where('module', 'writing')->latest(), $request->user());
        match ($request->query('status')) {
            'pending' => $q->whereNull('feedback_sent_at'),
            'sent' => $q->whereNotNull('feedback_sent_at'),
            default => null,
        };
        if ($b = $request->query('batch')) {
            $q->whereHas('user', fn ($u) => $u->where('batch', $b));
        }

        return view('admin.writing.index', [
            'results' => $q->paginate(20)->withQueryString(),
            'batches' => Access::batchesFor($request->user()),
        ]);
    }

    public function show(Request $request, Result $result)
    {
        abort_unless($result->module === 'writing', 404);
        abort_unless(Access::canSeeResult($request->user(), $result), 403);

        return view('admin.writing.show', compact('result'));
    }

    public function mark(Request $request, Result $result)
    {
        abort_unless($result->module === 'writing' && Access::canSeeResult($request->user(), $result), 403);
        $data = $request->validate([
            'band' => 'required|numeric|min:0|max:9',
            'feedback' => 'nullable|string|max:5000',
        ]);
        $send = $request->boolean('send');
        $result->update($data + [
            'marked_by' => $request->user()->id,
            'feedback_sent_at' => $send ? now() : $result->feedback_sent_at,
        ]);

        return redirect()->route('admin.writing.index')
            ->with('ok', $send ? 'Band and feedback sent to the student.' : 'Saved as a draft. Press “Send to student” when it is ready.');
    }
}
