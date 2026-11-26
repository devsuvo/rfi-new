<?php

// Module tones from the design system (02 · Colour).
return [
    'speaking' => [
        'name' => 'Speaking', 'tone' => '#5B4BFF', 'soft' => '#EFEDFF', 'icon' => 'record_voice_over',
        'classes' => [
            ['title' => 'Part 1 – Introduction and interview', 'meta' => '12 topics', 'url' => null],
            ['title' => 'Part 2 – Long turn (cue card)', 'meta' => '20 cue cards', 'url' => null],
            ['title' => 'Part 3 – Two-way discussion', 'meta' => '10 topics', 'url' => null],
        ],
    ],
    'listening' => [
        'name' => 'Listening', 'tone' => '#1D5BFF', 'soft' => '#E8EFFF', 'icon' => 'headphones',
        'classes' => [
            ['title' => 'Listening Test 1', 'meta' => 'Form Completion · Multiple Choice', 'url' => '/listening/test-1'],
        ],
    ],
    'writing' => [
        'name' => 'Writing', 'heading' => 'Writing for IELTS', 'tone' => '#E2263C', 'soft' => '#FDE9EC', 'icon' => 'edit_note',
        'classes' => [
            ['title' => 'Writing Test 1', 'meta' => 'Task 1 · Task 2', 'url' => '/writing/test-1'],
        ],
    ],
    'reading' => [
        'name' => 'Reading', 'heading' => 'Reading for IELTS', 'tone' => '#0AA3B5', 'soft' => '#E3F6F8', 'icon' => 'menu_book',
        'classes' => array_map(fn ($c) => $c + ['url' => '/reading/class/'.$c['slug']], [
            ['slug' => 'orientation', 'title' => 'Orientation', 'meta' => 'Full test · 3 passages · 40 questions'],
            ['slug' => 'class-1', 'title' => 'Class 1 – Notes & Table Completion', 'meta' => '3 exercises · 3 homework'],
            ['slug' => 'class-2', 'title' => 'Class 2 – True/False/Not Given & Yes/No/Not Given', 'meta' => 'Warm-up · 3 exercises · 4 homework'],
            ['slug' => 'class-3', 'title' => 'Class 3 – Matching Names', 'meta' => '3 exercises · 3 homework'],
            ['slug' => 'class-4', 'title' => 'Class 4 – MCQ', 'meta' => '3 exercises · 4 homework'],
            ['slug' => 'class-5', 'title' => 'Class 5 – Progress Test 1', 'meta' => '3 passages · 40 questions'],
            ['slug' => 'class-6', 'title' => 'Class 6 – List of Headings', 'meta' => '4 exercises · 4 homework'],
            ['slug' => 'class-7', 'title' => 'Class 7 – Matching Paragraphs (Which Paragraph Contains)', 'meta' => '2 exercises'],
            ['slug' => 'class-8', 'title' => 'Class 8 – Summary With & Without Clue', 'meta' => 'Warm-up · 3 exercises · 4 homework'],
            ['slug' => 'class-9', 'title' => 'Class 9 – Short Answer Question', 'meta' => '2 exercises · 2 homework'],
            ['slug' => 'class-10', 'title' => 'Class 10 – Progress Test 2', 'meta' => '3 passages · 40 questions'],
            ['slug' => 'class-11', 'title' => 'Class 11 – Diagram & Flowchart', 'meta' => '4 exercises · 4 homework'],
            ['slug' => 'class-12', 'title' => 'Class 12 – Sentence Endings', 'meta' => '2 exercises'],
            ['slug' => 'class-13', 'title' => 'Class 13 – Progress Test 3', 'meta' => '3 passages · 40 questions'],
            ['slug' => 'class-14', 'title' => 'Class 14 – Review', 'meta' => '1 review passage · 5 homework'],
        ]),
    ],
];
