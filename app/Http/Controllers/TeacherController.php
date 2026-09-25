<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TeacherController extends Controller
{
    public function index()
    {
        $teachers = User::teachersOnly()->orderBy('name')->get();

        return view('teachers.index', ['teachers' => $teachers]);
    }

    // إضافة عدة معلمين مرة واحدة: كل صف يحتوي على اسم وبريد إلكتروني وكلمة مرور، ويتم تجاهل أي صف فارغ
    public function store(Request $request)
    {
        $names = $request->input('name', []);
        $emails = $request->input('email', []);
        $passwords = $request->input('password', []);

        $created = 0;
        $skippedEmails = [];

        foreach ($names as $i => $name) {
            $name = trim((string) $name);
            $email = trim((string) ($emails[$i] ?? ''));
            $password = (string) ($passwords[$i] ?? '');

            if ($name === '' && $email === '') {
                continue;
            }

            if ($name === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
                return back()->withErrors([
                    'name' => 'تأكد من أن كل صف يحتوي على اسم، وبريد إلكتروني صحيح، وكلمة مرور مكونة من 6 أحرف على الأقل (صف رقم '.($i + 1).').',
                ])->withInput();
            }

            if (User::where('email', $email)->exists()) {
                $skippedEmails[] = $email;
                continue;
            }

            User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'is_admin' => false,
            ]);
            $created++;
        }

        if ($created === 0 && empty($skippedEmails)) {
            return back()->withErrors(['name' => 'يجب إدخال معلم واحد على الأقل.'])->withInput();
        }

        $message = "تم إضافة $created معلم بنجاح";
        if ($skippedEmails) {
            $message .= '. تم تجاهل عناوين بريد إلكتروني مستخدمة سابقًا: '.implode('، ', $skippedEmails);
        }

        return redirect('/teachers')->with('success', $message);
    }

    public function destroy(User $teacher)
    {
        if (Assignment::where('teacher_id', $teacher->id)->exists()) {
            return back()->withErrors(['name' => 'لا يمكن حذف هذا المعلم لأن له توزيع فصول حاليًا. احذف توزيعه أولًا من صفحة "التوزيع".']);
        }

        $teacher->delete();

        return redirect('/teachers');
    }
}
