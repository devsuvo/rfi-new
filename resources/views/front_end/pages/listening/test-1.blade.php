<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Listening Test 1 · IELTS Practice Portal</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
</head>
<body>
@include('front_end.layout.test-theme', ['tone' => '#1d5bff', 'soft' => '#e8efff', 'module' => 'Listening', 'minutes' => 30, 'audio' => true])

<div class="t-head"><span class="dot"></span><strong>Listening</strong><span>Form Completion · Multiple Choice</span></div>

<div class="t-tabs">
    <section class="t-pane" data-tab="Form Completion 1">
        <div class="t-passage">
            {{-- audio: add later --}}
            <audio controls src="{{ asset('audio/listening-1-part-1.mp3') }}"></audio>
            <h5 class="topic-title">Sports centre membership</h5>
            <p class="t-line">Name: Sarah <input class="t-input" name="q1" placeholder="1">.</p>
            <p class="t-line">Membership type: <input class="t-input" name="q2" placeholder="2">.</p>
            <p class="t-line">Preferred day for classes: <input class="t-input" name="q3" placeholder="3">.</p>
            <p class="t-line">Cost per month: £<input class="t-input" name="q4" placeholder="4">.</p>
        </div>
        <div class="t-questions">
            <h4>Questions 1–4</h4>
            <p class="t-instr">Complete the form. Write ONE WORD AND/OR A NUMBER for each answer.</p>
            <p class="t-instr">Type your answers in the boxes on the left.</p>
        </div>
    </section>

    <section class="t-pane" data-tab="Multiple Choice 1">
        <div class="t-passage">
            {{-- audio: add later --}}
            <audio controls src="{{ asset('audio/listening-1-part-2.mp3') }}"></audio>
            <h5 class="topic-title">Work experience for veterinary science students</h5>
            <p>Listen to the conversation between a tutor and two students, then answer the questions.</p>
        </div>
        <div class="t-questions">
            <h4>Questions 5–6</h4>
            <p class="t-instr">Choose the correct answer.</p>
            <div class="mc-tab">
                <div class="mc-q">
                    <div class="mc-stem"><span class="qn">5</span><span>Why does the student want work experience on a farm?</span></div>
                    <label><input type="radio" name="q5" value="A">it is required for the course</label>
                    <label><input type="radio" name="q5" value="B">he grew up on a farm</label>
                    <label><input type="radio" name="q5" value="C">he wants to work with large animals</label>
                </div>
                <div class="mc-q">
                    <div class="mc-stem"><span class="qn">6</span><span>The tutor suggests the students should first</span></div>
                    <label><input type="radio" name="q6" value="A">contact local clinics</label>
                    <label><input type="radio" name="q6" value="B">read the placement guide</label>
                    <label><input type="radio" name="q6" value="C">speak to last year's students</label>
                </div>
            </div>
        </div>
    </section>
</div>

@include('front_end.layout.audio-player')
</body>
</html>
