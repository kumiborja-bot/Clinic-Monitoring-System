<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        // Include the build even when ASSET_URL is configured. The default
        // implementation hashes only that fixed URL and misses new bundles.
        $manifest = public_path('build/manifest.json');

        if (is_file($manifest)) {
            return hash('xxh128', (string) config('app.asset_url').'|'.hash_file('xxh128', $manifest));
        }

        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'flash' => [
                'status' => fn () => $request->session()->get('status'),
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'import_report' => fn () => $request->session()->get('import_report'),
            ],
        ];
    }
}
