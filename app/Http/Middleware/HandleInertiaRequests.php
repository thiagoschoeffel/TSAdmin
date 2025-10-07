<?php

namespace App\Http\Middleware;

use Inertia\Middleware;
use Illuminate\Http\Request;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'app' => [
                'name' => config('app.name', 'Example App'),
            ],
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'role' => $request->user()->role,
                    'permissions' => $request->user()->permissions,
                ] : null,
            ],
            'flash' => [
                'status' => fn() => session('status'),
                'success' => fn() => session('success'),
                'error' => fn() => session('error'),
                'flash_id' => fn() => session('flash_id'),
            ],
        ]);
    }
}
