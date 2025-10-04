<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View|\Inertia\Response
    {
        if (class_exists(\Inertia\Inertia::class)) {
            return \Inertia\Inertia::render('Admin/Dashboard');
        }

        return view('dashboard');
    }
}
