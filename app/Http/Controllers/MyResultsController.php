<?php

namespace App\Http\Controllers;

use App\Models\Result;
use Illuminate\Http\Request;

/** A student's own results: reading scores right away, writing band + feedback once the teacher sends it. */
class MyResultsController extends Controller
{
    public function index(Request $request)
    {
        return view('front_end.my-results', [
            // only the results of the student's own course (Reading for IELTS or Writing for IELTS)
            'results' => Result::where('user_id', $request->user()->id)
                ->when($request->user()->courseKey(), fn ($q, $c) => $q->where('module', $c))
                ->latest()->get(),
            'course' => $request->user()->courseKey(),
        ]);
    }

    public function show(Request $request, Result $result)
    {
        abort_unless($result->user_id === $request->user()->id && $request->user()->canUseCourse($result->module), 403);
        // writing stays hidden until the teacher presses "Send to student"
        abort_if($result->module === 'writing' && ! $result->feedback_sent_at, 404);
        abort_if($result->module === 'reading' && ! $result->total, 404);

        return view('front_end.my-result', compact('result'));
    }
}
