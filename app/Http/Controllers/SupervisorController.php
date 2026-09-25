<?php

namespace App\Http\Controllers;

use App\Models\SupervisorAttendance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SupervisorController extends Controller
{
    public function index()
    {
        $supervisors = User::where('is_supervisor', true)->get();

        return view('supervisors.index', ['supervisors' => $supervisors]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_admin' => false,
            'is_supervisor' => true,
        ]);

        return redirect('/supervisors');
    }

    public function destroy(User $supervisor)
    {
        if (SupervisorAttendance::where('supervisor_id', $supervisor->id)->exists()) {
            return back()->withErrors(['name' => 'لا يمكن حذف هذا الإداري لأنه سجّل غيابًا من قبل، وحذفه سيؤدي إلى محو هذه السجلات معه. اتركه دون استخدام بدلًا من حذفه.']);
        }

        $supervisor->delete();

        return redirect('/supervisors');
    }
}
