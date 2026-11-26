<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>{{ $batch }} — Student Logins</title>
<style>
    @page { margin: 28px 34px 40px; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10.5px; color: #1e2433; }
    .head { border-bottom: 3px solid #2563eb; padding-bottom: 10px; margin-bottom: 14px; }
    .head img { height: 40px; }
    .head td { vertical-align: middle; }
    h1 { font-size: 18px; margin: 0; color: #0f172a; }
    .sub { color: #64748b; font-size: 10px; margin-top: 2px; }
    .info { width: 100%; border-collapse: separate; border-spacing: 0; margin-bottom: 14px; }
    .info td { background: #f1f5fd; padding: 8px 12px; border-radius: 6px; vertical-align: top; }
    .lbl { font-size: 8.5px; text-transform: uppercase; letter-spacing: .6px; color: #64748b; }
    .val { font-size: 12px; font-weight: bold; color: #0f172a; margin-top: 2px; }
    table.list { width: 100%; border-collapse: collapse; }
    table.list th { background: #2563eb; color: #fff; text-align: left; font-size: 9.5px; text-transform: uppercase; letter-spacing: .5px; padding: 7px 9px; }
    table.list td { padding: 6px 9px; border-bottom: 1px solid #e5e9f2; }
    table.list tr:nth-child(even) td { background: #f8faff; }
    .sn { width: 34px; text-align: center; color: #64748b; }
    .mono { font-family: DejaVu Sans Mono, monospace; font-size: 10px; }
    .muted { color: #94a3b8; font-style: italic; }
    .wm { position: fixed; top: 300px; left: 90px; width: 360px; opacity: .07; z-index: -1; }
    .wm-text { position: fixed; top: 430px; left: -40px; width: 700px; text-align: center; font-size: 78px; font-weight: bold; color: #dc2626; opacity: .06; transform: rotate(-35deg); z-index: -1; letter-spacing: 8px; }
    .conf { display: inline-block; background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; font-size: 8.5px; font-weight: bold; letter-spacing: 1.5px; padding: 3px 8px; border-radius: 4px; margin-bottom: 4px; }
    .note { margin-top: 16px; padding: 10px 12px; background: #fff7ed; border-left: 3px solid #f97316; font-size: 9.5px; color: #7c2d12; line-height: 1.5; }
    .foot { position: fixed; bottom: -26px; left: 0; right: 0; font-size: 8.5px; color: #94a3b8; text-align: center; }
</style>
</head>
<body>
@if (is_file(public_path('images/new-logo.png')))<img class="wm" src="{{ public_path('images/new-logo.png') }}">@endif
<div class="wm-text">CONFIDENTIAL</div>
<div class="foot"><b style="color:#b91c1c">CONFIDENTIAL</b> · Hexa's Zindabazar · For authorised staff only · Generated {{ now()->format('d M Y, h:i A') }}</div>

<table class="head" width="100%"><tr>
    <td><div class="conf">CONFIDENTIAL</div><h1>Student Login Details</h1><div class="sub">{{ $course ?? 'IELTS' }} · {{ $students->count() }} students</div></td>
    @if (is_file(public_path('images/new-logo.png')))<td style="text-align:right"><img src="{{ public_path('images/new-logo.png') }}"></td>@endif
</tr></table>

<table class="info"><tr>
    <td width="32%"><div class="lbl">Batch</div><div class="val">{{ $batch }}</div></td>
    <td width="2%" style="background:none"></td>
    <td width="32%"><div class="lbl">Teacher</div><div class="val">{{ $teacher ?? '—' }}</div></td>
    <td width="2%" style="background:none"></td>
    <td width="32%"><div class="lbl">Co-Teacher</div><div class="val">{{ $coTeachers->isNotEmpty() ? $coTeachers->implode(', ') : '—' }}</div></td>
</tr></table>

<table class="list">
    <thead><tr><th class="sn" style="color:#fff">SN</th><th>Student Name</th><th>Student Email</th><th>Password</th></tr></thead>
    <tbody>
    @forelse ($students as $i => $s)
        <tr>
            <td class="sn">{{ $i + 1 }}</td>
            <td><b>{{ $s->name }}</b></td>
            <td class="mono">{{ $s->email }}</td>
            <td class="mono">@if ($s->auto_password){{ strstr($s->email, '@', true) }}@else<span class="muted">set manually</span>@endif</td>
        </tr>
    @empty
        <tr><td colspan="4" style="text-align:center;padding:20px" class="muted">No students in this batch.</td></tr>
    @endforelse
    </tbody>
</table>

<div class="note">
    <b>Confidential — please handle with care.</b><br>
    This document contains private login details for the students of batch <b>{{ $batch }}</b>. Share each login only with the student it belongs to, do not post it in groups or forward the full list, and destroy printed copies after the logins have been handed out. Students can ask their teacher if they have trouble logging in.
</div>
</body>
</html>
