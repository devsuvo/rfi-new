<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeacherBatch;
use App\Models\User;
use App\Support\Access;
use Illuminate\Http\Request;

/** Admin gives each teacher the batches they look after. */
class AssignController extends Controller
{
    public function index()
    {
        return view('admin.assign', [
            'teachers' => User::where('role', 'teacher')->with(['batches' => fn ($q) => $q->orderBy('batch')])->orderBy('name')->get(),
            'batches' => Access::allBatches(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'teacher_id' => ['required', 'exists:users,id'],
            'batch' => 'required|string|max:60',
        ]);
        abort_unless(User::find($data['teacher_id'])->isTeacher(), 422);
        TeacherBatch::firstOrCreate(['teacher_id' => $data['teacher_id'], 'batch' => User::normalizeBatch($data['batch'])]);

        return back()->with('ok', 'Batch assigned.');
    }

    public function destroy(TeacherBatch $assignment)
    {
        $assignment->delete();

        return back()->with('ok', 'Batch removed from the teacher.');
    }
}
