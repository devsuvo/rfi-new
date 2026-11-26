<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Access;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Admin registers and manages every user: admins, teachers and students. */
class StudentController extends Controller
{
    public function index(Request $request)
    {
        $role = $request->query('role', 'student');
        $q = $this->filtered($request, $role)->withCount('results')->with('batches')->orderBy('name')->orderBy('id');

        return view('admin.students.index', [
            'users' => $q->paginate(20)->withQueryString(),
            'role' => $role,
            'counts' => User::selectRaw('role, COUNT(*) n')->groupBy('role')->pluck('n', 'role'),
            'batches' => Access::allBatches(),
        ]);
    }

    /** Plain-text login list for every user matching the current filters (all pages), numbered. */
    public function logins(Request $request)
    {
        $users = $this->filtered($request, $request->query('role', 'student'))->orderBy('name')->get(['name', 'email', 'auto_password']);
        $lines = [];
        foreach ($users as $i => $u) {
            $local = strstr($u->email, '@', true);
            $pass = $u->auto_password ? $local : null;
            $lines[] = ($i + 1).'. '.$u->name."\n   Email: ".$u->email.($pass ? "\n   Password: ".$pass : '');
        }

        return response(implode("\n\n", $lines), 200, ['Content-Type' => 'text/plain; charset=utf-8']);
    }

    /** Printable PDF of one batch: batch name, teacher + co-teachers, then SN / name / email / password. */
    public function loginsPdf(Request $request)
    {
        $batch = (string) $request->query('batch');
        abort_if($batch === '', 404);
        $students = User::where('role', 'student')->where('batch', $batch)
            ->when(isset(User::COURSES[$request->query('course')]), fn ($q) => $q->where('course', $request->query('course')))
            ->orderBy('name')->orderBy('id')->get(['name', 'email', 'auto_password', 'course']);
        $teachers = \App\Models\TeacherBatch::with('teacher:id,name')->where('batch', $batch)->orderBy('id')->get()
            ->pluck('teacher.name')->filter()->values();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.students.logins-pdf', [
            'batch' => $batch,
            'teacher' => $teachers->first(),
            'coTeachers' => $teachers->slice(1)->values(),
            'students' => $students,
            'course' => User::COURSES[$students->first()?->course] ?? null,
        ])->setPaper('a4');

        $clean = fn ($v) => trim(preg_replace('/[^A-Za-z0-9 _.-]+/', '', (string) $v));
        $name = implode(' - ', array_filter([$clean($teachers->first()), $clean($batch)]));

        return $pdf->download(($name !== '' ? $name : 'batch').'.pdf');
    }

    private function filtered(Request $request, string $role)
    {
        $q = User::where('role', $role);
        if ($s = $request->query('q')) {
            $q->where(fn ($w) => $w->where('name', 'like', "%{$s}%")
                ->orWhere('email', 'like', "%{$s}%")
                ->orWhere('phone', 'like', "%{$s}%"));
        }
        if ($b = $request->query('batch')) {
            $q->where('batch', $b);
        }
        if (isset(User::COURSES[$c = $request->query('course')])) {
            $q->where('course', $c);
        }

        return $q;
    }

    public function create(Request $request)
    {
        return view('admin.students.create', [
            'role' => $request->query('role', 'student'),
            'batches' => Access::allBatches(),
        ]);
    }

    public function edit(User $user)
    {
        return view('admin.students.form', ['user' => $user, 'batches' => Access::allBatches()]);
    }

    /** Registers one or many users at once; empty passwords are auto-generated and shown once. */
    public function store(Request $request)
    {
        $people = collect($request->input('people', []))
            ->map(fn ($p) => array_map(fn ($v) => is_string($v) ? trim($v) : $v, (array) $p))
            ->filter(fn ($p) => ($p['name'] ?? '') !== '' || ($p['email'] ?? '') !== '')
            ->values();
        $request->merge(['people' => $people->all()]);

        $common = $request->validate([
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
            'course' => ['nullable', 'required_unless:role,admin', Rule::in(array_keys(User::COURSES))],
            'batch' => 'nullable|required_if:role,student|string|max:60',
            'access_until' => 'nullable|date',
            'people' => 'required|array|min:1|max:200',
            'people.*.name' => 'required|string|max:120',
            'people.*.email' => 'nullable|required_if:role,admin,teacher|email|distinct|unique:users,email',
            'people.*.phone' => 'nullable|string|max:30',
            'people.*.password' => 'nullable|string|min:6',
        ], [
            'batch.required_if' => 'A student needs a batch.',
            'course.required_unless' => 'Choose Reading for IELTS or Writing for IELTS.',
            'people.required' => 'Add at least one person (name + email).',
            'people.*.name.required' => 'Row :position: name is missing.',
            'people.*.email.required_if' => 'Row :position: email is missing (only students get an automatic email).',
            'people.*.email.email' => 'Row :position: email is not valid.',
            'people.*.email.distinct' => 'Row :position: this email is entered twice.',
            'people.*.email.unique' => 'Row :position: this email is already registered.',
        ]);

        $created = [];
        \DB::transaction(function () use ($common, &$created) {
            $taken = [];
            foreach ($common['people'] as $p) {
                $auto = self::autoLogin($p['name'], $common['batch'] ?? '', $taken);
                $taken[] = $auto;
                $p['email'] = ($p['email'] ?? '') !== '' ? $p['email'] : $auto.'@'.self::AUTO_DOMAIN;
                $password = ($p['password'] ?? '') !== '' ? $p['password'] : str_pad($auto, 6, '1');
                $isAuto = $password === strstr(strtolower($p['email']), '@', true) && str_ends_with(strtolower($p['email']), '@'.self::AUTO_DOMAIN);
                User::create([
                    'name' => $p['name'], 'email' => strtolower($p['email']), 'phone' => $p['phone'] ?? null,
                    'password' => $password, 'role' => $common['role'], 'auto_password' => $isAuto,
                    'course' => $common['role'] === 'admin' ? null : $common['course'],
                    'batch' => $common['role'] === 'student' ? $common['batch'] : null,
                    'access_until' => $common['role'] === 'student' ? ($common['access_until'] ?? null) : null,
                ]);
                $created[] = ['name' => $p['name'], 'email' => strtolower($p['email']), 'password' => $password];
            }
        });

        return redirect()->route('admin.students.create', ['role' => $common['role']])
            ->with('created', $created)
            ->with('ok', count($created).' '.strtolower(User::ROLES[$common['role']]).'(s) registered.');
    }

    public const AUTO_DOMAIN = 'hz.com';

    /** "Rahim Uddin" + "batch-12" => "rahim.hz12"; repeats become rahim2.hz12, rahim3.hz12…. */
    private static function autoLogin(string $name, string $batch, array $taken): string
    {
        $first = strtolower(preg_replace('/[^a-z0-9]/i', '', strtok(trim($name), ' ') ?: '')) ?: 'student';
        $tag = preg_match_all('/\d+/', $batch, $m) ? implode('', $m[0]) : strtolower(preg_replace('/[^a-z0-9]/i', '', $batch));
        $login = $first.'.hz'.$tag;
        for ($i = 2; in_array($login, $taken, true) || User::where('email', $login.'@'.self::AUTO_DOMAIN)->exists(); $i++) {
            $login = $first.$i.'.hz'.$tag;
        }

        return $login;
    }

    public function update(Request $request, User $user)
    {
        $data = $this->data($request, $user);
        $email = strtolower($data['email']);
        if (empty($data['password'])) {
            unset($data['password']);
            $data['auto_password'] = $user->auto_password && $email === strtolower($user->email);
        } else {
            $data['auto_password'] = str_ends_with($email, '@'.self::AUTO_DOMAIN) && $data['password'] === strstr($email, '@', true);
        }
        if ($user->is($request->user())) {
            $data['role'] = 'admin'; // never demote yourself
        }
        $user->update($data);

        return redirect()->route('admin.students.index', ['role' => $user->role])->with('ok', 'User updated.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->is($request->user())) {
            return back()->with('ok', 'You cannot delete your own account.');
        }
        $user->delete();

        return back()->with('ok', 'User deleted.');
    }

    private function data(Request $request, ?User $user): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user?->id)],
            'phone' => 'nullable|string|max:30',
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
            'course' => ['nullable', 'required_unless:role,admin', Rule::in(array_keys(User::COURSES))],
            'batch' => 'nullable|required_if:role,student|string|max:60',
            'access_until' => 'nullable|date',
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:6'],
        ], ['batch.required_if' => 'A student needs a batch.', 'course.required_unless' => 'Choose Reading for IELTS or Writing for IELTS.']);
        $data['access_until'] = $data['access_until'] ?? null;

        return $data;
    }
}
