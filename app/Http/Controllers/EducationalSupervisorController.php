<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EducationalSupervisorController extends Controller
{
    public function index()
    {
        $educationalSupervisors = User::where('is_educational_supervisor', true)->get();

        return view('educational-supervisors.index', ['educationalSupervisors' => $educationalSupervisors]);
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
            'is_supervisor' => false,
            'is_educational_supervisor' => true,
        ]);

        return redirect('/educational-supervisors');
    }

    public function destroy(User $educationalSupervisor)
    {
        $educationalSupervisor->delete();

        return redirect('/educational-supervisors');
    }
}
