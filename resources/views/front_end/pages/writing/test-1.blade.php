<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Writing Test 1 · Writing for IELTS</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <style>
        .w-table { width: 100%; border-collapse: collapse; font-size: 14px; }
        .w-table th { background: #5A7394; color: #fff; font-weight: 600; padding: 8px; }
        .w-table td { padding: 7px 8px; border: 1px solid #D5DEEA; text-align: center; background: #EAF0F7; }
        .w-table td:first-child { text-align: left; font-weight: 600; }
    </style>
</head>
<body>
@include('front_end.layout.test-theme', ['tone' => '#e2263c', 'soft' => '#fde9ec', 'module' => 'Writing', 'minutes' => $minutes ?? 60, 'liveUrl' => route('tests.live', ['writing', 'test-1']), 'submit' => route('results.store', ['writing', 'test-1'])])

<div class="t-head"><span class="dot"></span><strong>Writing for IELTS</strong><span>Task 1 · Task 2</span></div>

<div class="t-tabs">
    <section class="t-pane" data-tab="Task 1">
        <div class="t-passage">
            <p class="w-prompt">1. The table below gives information on consumer spending on different items in five different countries in 2002.</p>
            <p class="w-instr">Summarise the information by selecting and reporting the main features, and make comparisons where relevant.</p>
            <div class="w-chart">
                <table class="w-table">
                    <tr><th>Country</th><th>Food/Drinks/Tobacco</th><th>Clothing/Footwear</th><th>Leisure/Education</th></tr>
                    <tr><td>Ireland</td><td>28.91%</td><td>6.43%</td><td>2.21%</td></tr>
                    <tr><td>Italy</td><td>16.36%</td><td>9.00%</td><td>3.20%</td></tr>
                    <tr><td>Spain</td><td>18.80%</td><td>6.51%</td><td>1.98%</td></tr>
                    <tr><td>Sweden</td><td>15.77%</td><td>5.40%</td><td>3.22%</td></tr>
                    <tr><td>Turkey</td><td>32.14%</td><td>6.63%</td><td>4.35%</td></tr>
                </table>
            </div>
            <p class="w-instr" style="margin-top:16px">Write at least 150 words.</p>
        </div>
        <div class="t-questions">
            <textarea class="w-area" name="task1" placeholder="Start writing here..."></textarea>
            <div class="w-count"></div>
        </div>
    </section>

    <section class="t-pane" data-tab="Task 2">
        <div class="t-passage">
            <p class="w-prompt">2. Some people think that students should study only the subjects they will need for their future career. Others believe a wide range of subjects is more useful.</p>
            <p class="w-instr">Discuss both views and give your own opinion. Write at least 250 words.</p>
        </div>
        <div class="t-questions">
            <textarea class="w-area" name="task2" placeholder="Start writing here..."></textarea>
            <div class="w-count"></div>
        </div>
    </section>
</div>
</body>
</html>
