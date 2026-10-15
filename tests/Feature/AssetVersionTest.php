<?php

namespace Tests\Feature;

use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Http\Request;
use Tests\TestCase;

class AssetVersionTest extends TestCase
{
    public function test_build_changes_invalidate_old_pages_with_a_fixed_asset_url(): void
    {
        $original = public_path();
        $fixture = sys_get_temp_dir().'/clinic-assets-'.bin2hex(random_bytes(6));
        mkdir($fixture.'/build', 0777, true);
        $this->app->usePublicPath($fixture);
        config(['app.asset_url' => 'https://clinic.example']);

        try {
            $middleware = new HandleInertiaRequests;
            $request = Request::create('/medicines');
            file_put_contents($fixture.'/build/manifest.json', '{"file":"old.js"}');
            $old = $middleware->version($request);
            file_put_contents($fixture.'/build/manifest.json', '{"file":"new.js"}');
            $new = $middleware->version($request);
            $this->assertNotSame($old, $new);
            $this->assertSame($new, $middleware->version($request));
        } finally {
            $this->app->usePublicPath($original);
            unlink($fixture.'/build/manifest.json');
            rmdir($fixture.'/build');
            rmdir($fixture);
        }
    }
}
