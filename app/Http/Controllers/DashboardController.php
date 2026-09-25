<?php

namespace App\Http\Controllers;

use App\Models\Term;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $roleLabel = match (true) {
            $user->is_admin => 'مدير',
            $user->is_supervisor => 'إداري',
            $user->is_educational_supervisor => 'مشرف تربوي',
            default => 'أستاذ',
        };

        return view('dashboard.index', [
            'currentTerm' => Term::current(),
            'roleLabel' => $roleLabel,
        ]);
    }
}
