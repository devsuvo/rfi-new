<?php

namespace Database\Seeders;

use App\Models\AnswerKey;
use App\Models\ClassUnlock;
use App\Models\Result;
use App\Models\TeacherBatch;
use App\Models\User;
use App\Support\Grader;
use Illuminate\Database\Seeder;

/**
 * Example data to try every role:  php artisan db:seed --class=DemoSeeder
 * Safe to run again (it updates the same demo rows). Demo passwords: 12345678
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        $pw = '12345678';

        // ---- Teachers + their batches ----
        $jumon = User::firstOrCreate(['email' => 'jumon@hz.com'], ['name' => 'Jumon', 'password' => $pw, 'role' => 'teacher']);
        $rina = User::firstOrCreate(['email' => 'rina@hz.com'], ['name' => 'Rina', 'password' => $pw, 'role' => 'teacher']);
        TeacherBatch::firstOrCreate(['teacher_id' => $jumon->id, 'batch' => 'batch-1']);
        TeacherBatch::firstOrCreate(['teacher_id' => $rina->id, 'batch' => 'batch-2']);

        // ---- Students (a batch exists as soon as a student has it) ----
        $eva = User::where('email', 'eva@hz.com')->first()
            ?? User::create(['name' => 'Eva', 'email' => 'eva@hz.com', 'password' => '20262026', 'role' => 'student', 'batch' => 'batch-1']);
        $rafi = User::firstOrCreate(['email' => 'rafi@hz.com'], ['name' => 'Rafi', 'password' => $pw, 'role' => 'student', 'batch' => 'batch-1']);
        $nila = User::firstOrCreate(['email' => 'nila@hz.com'], ['name' => 'Nila', 'password' => $pw, 'role' => 'student', 'batch' => 'batch-2']);
        $tanvir = User::firstOrCreate(['email' => 'tanvir@hz.com'], ['name' => 'Tanvir', 'password' => $pw, 'role' => 'student', 'batch' => 'batch-2']);

        // ---- Class locks: batch-1 is further ahead than batch-2 ----
        $open = [
            'batch-1' => [[$jumon, 'reading', 'orientation'], [$jumon, 'reading', 'class-1'], [$jumon, 'reading', 'class-7'], [$jumon, 'writing', 'test-1']],
            'batch-2' => [[$rina, 'reading', 'orientation'], [$rina, 'reading', 'class-7']],
        ];
        // ---- Extra demo batches for Jumon: batch-3, batch-4 ----
        foreach (['batch-3' => [['Sumi', 'sumi'], ['Arif', 'arif'], ['Mitu', 'mitu']], 'batch-4' => [['Rakib', 'rakib'], ['Tania', 'tania']]] as $batch => $people) {
            TeacherBatch::firstOrCreate(['teacher_id' => $jumon->id, 'batch' => $batch]);
            foreach ($people as [$name, $slug]) {
                User::firstOrCreate(['email' => "$slug@hz.com"], ['name' => $name, 'password' => $pw, 'role' => 'student', 'batch' => $batch]);
            }
        }
        $open['batch-3'] = [[$jumon, 'reading', 'orientation'], [$jumon, 'reading', 'class-1']];
        $open['batch-4'] = [[$jumon, 'reading', 'orientation']];

        foreach ($open as $batch => $rows) {
            foreach ($rows as [$t, $module, $slug]) {
                ClassUnlock::firstOrCreate(['batch' => $batch, 'module' => $module, 'test_slug' => $slug], ['teacher_id' => $t->id]);
            }
        }

        // ---- Reading answer key (admin) — Class 7, Exercise 1, questions 1–11 ----
        $key = [
            'p1_q1' => 'D', 'p1_q2' => 'F', 'p1_q3' => 'B', 'p1_q4' => 'E', 'p1_q5' => 'A', 'p1_q6' => 'C',
            'p1_q7' => 'Jupiter and Saturn / Saturn and Jupiter',
            'p1_q8' => '(the) Solar System',
            'p1_q9' => 'sensors and circuits / circuits and sensors',
            'p1_q10' => 'spares / spare parts',
            'p1_q11' => '(NASA) radio dish',
        ];
        AnswerKey::updateOrCreate(['module' => 'reading', 'test_slug' => 'class-7'], ['test_name' => 'class7_reading', 'answers' => $key]);

        // ---- Reading submissions (marked automatically) ----
        $reading = [
            [$eva, ['p1_q1' => 'D', 'p1_q2' => 'F', 'p1_q3' => 'B', 'p1_q4' => 'E', 'p1_q5' => 'A', 'p1_q6' => 'C', 'p1_q7' => 'saturn and jupiter', 'p1_q8' => 'Solar System', 'p1_q9' => 'sensors and circuits', 'p1_q10' => 'spare parts', 'p1_q11' => 'radio dish'], 2],
            [$rafi, ['p1_q1' => 'D', 'p1_q2' => 'B', 'p1_q3' => 'B', 'p1_q4' => 'F', 'p1_q5' => 'A', 'p1_q6' => 'A', 'p1_q7' => 'Jupiter', 'p1_q8' => 'the solar system', 'p1_q9' => 'circuits', 'p1_q10' => 'spares', 'p1_q11' => 'satellite'], 1],
            [$nila, ['p1_q1' => 'D', 'p1_q2' => 'F', 'p1_q3' => 'C', 'p1_q4' => 'E', 'p1_q5' => 'A', 'p1_q6' => 'C', 'p1_q7' => 'Jupiter and Saturn', 'p1_q8' => 'solar system', 'p1_q9' => 'sensors', 'p1_q10' => 'spares', 'p1_q11' => 'NASA radio dish'], 0],
        ];
        foreach ($reading as [$u, $answers, $daysAgo]) {
            $g = Grader::grade($key, $answers);
            $this->result($u, 'reading', 'class-7', $answers, $daysAgo, ['score' => $g['score'], 'total' => $g['total'], 'band' => Grader::band($g['score'], $g['total'])]);
        }

        // ---- Writing submissions: one feedback sent, one draft, one not checked ----
        $t1 = 'The table compares how consumers in five countries spent their money in 2002. Overall, food, drinks and tobacco took the biggest share everywhere, while leisure and education took the smallest. Turkey spent the most on food at 32.14%, and Sweden the least at 15.77%. Clothing ranged from 5.40% in Sweden to 9.00% in Italy.';
        $t2 = 'Some people believe students should focus only on subjects useful for their career, while others prefer a broad education. In my opinion, a wide range of subjects is more valuable. Firstly, many young people change their career plans. Secondly, subjects such as art and history build creativity and critical thinking, which every job needs.';
        $this->result($eva, 'writing', 'test-1', ['task1' => $t1, 'task2' => $t2], 1, [
            'band' => 6.5, 'feedback' => "Task 1: clear overview, good comparisons. Add one more figure for leisure/education.\nTask 2: your position is clear. Develop each body paragraph with an example and write at least 250 words.",
            'marked_by' => $jumon->id, 'feedback_sent_at' => now()->subHours(5),
        ]);
        $this->result($rafi, 'writing', 'test-1', ['task1' => $t1, 'task2' => 'I think students need many subjects because it is good for them.'], 0, [
            'band' => 5.0, 'feedback' => 'Task 2 is far too short — aim for 250 words.', 'marked_by' => $jumon->id,
        ]);
        $this->result($tanvir, 'writing', 'test-1', ['task1' => $t1, 'task2' => $t2], 0, []);

        $this->command?->info('Demo data ready. Teachers/students password: '.$pw.' (Eva: 20262026)');
    }

    private function result(User $u, string $module, string $slug, array $answers, int $daysAgo, array $extra): void
    {
        $r = Result::firstOrNew(['user_id' => $u->id, 'module' => $module, 'test_slug' => $slug]);
        $r->fill(['answers' => $answers] + $extra + ['score' => null, 'total' => null, 'band' => null, 'feedback' => null, 'marked_by' => null, 'feedback_sent_at' => null]);
        $r->created_at = now()->subDays($daysAgo)->subHours(3);
        $r->save();
    }
}
