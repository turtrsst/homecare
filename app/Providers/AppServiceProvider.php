<?php

namespace App\Providers;

use App\Models\HomecareService;
use App\Observers\HomecareServiceObserver;
use App\Support\Permissions;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->registerPermissionGates();
        $this->registerObservers();
        $this->respectForwardedHost();
        $this->guardWasmSleepBug();
    }

    /**
     * Runtime php-wasm (Emscripten) mengalami trap stack bila objek
     * Illuminate\Support\Sleep didekstruksi saat RETURN — terjadi pada
     * timebox login yang gagal. Di lingkungan itu kita fake sleep; di
     * server produksi normal, timebox tetap berjalan sebagaimana mestinya.
     */
    private function guardWasmSleepBug(): void
    {
        if (str_contains(php_uname(), 'Emscripten')) {
            \Illuminate\Support\Sleep::fake();
        }
    }

    /**
     * Gate didefinisikan terpusat dari peta role→permission.
     * Tidak ada pengecekan role manual yang tersebar.
     */
    private function registerPermissionGates(): void
    {
        foreach (Permissions::map() as $ability => $roles) {
            Gate::define($ability, function ($user) use ($roles) {
                return $user->is_active && in_array($user->role, $roles, true);
            });
        }
    }

    private function registerObservers(): void
    {
        HomecareService::observe(HomecareServiceObserver::class);
    }

    /**
     * Dukung reverse proxy / preview: URL absolut (asset, action, notifikasi)
     * mengikuti host yang benar-benar diminta browser.
     */
	private function respectForwardedHost(): void
	{
		if (! $this->app->runningInConsole() && Request::getHost()) {
			$scheme = Request::secure() ? 'https' : 'http';
			$host = Request::getHttpHost();

			$appUrl = parse_url(config('app.url'));
			$basePath = rtrim($appUrl['path'] ?? '', '/');

			if ($host && $basePath) {
				URL::forceRootUrl($scheme . '://' . $host . $basePath);
			} elseif ($host) {
				URL::forceRootUrl($scheme . '://' . $host);
			}

			if ($scheme === 'https') {
				URL::forceScheme('https');
			}
		}
	}
}
