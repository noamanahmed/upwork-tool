<?php

namespace App\Providers;

use App\Client\GuzzleClient;
use App\Database\ConnectionPooler;
use App\Models\Language;
use App\Router\ResourceRegistrar;
use App\Router\Router as MyRouter;
use Illuminate\Contracts\Routing\BindingRegistrar;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\ResourceRegistrar as BaseResourceRegistrar;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if(config('app.debug')) $this->bootDebug();

        Schema::defaultStringLength(191);
        // Add custom methods to all resource groups in routes
        app()->bind(BaseResourceRegistrar::class, function () {
            return new ResourceRegistrar(app()->make(Router::class));
        });

        Route::macro('apiCrudResource', function ($name, $controller,$options = []) {
            $routes = app(BaseResourceRegistrar::class)->getResourceDefaults();
            return Route::resource($name, $controller, array_merge([
                'only' => $routes,
            ], $options))->middleware('authorize.api:'.$name);
        });

        app()->singleton('translations',function () {
            $header = request()->header('Accept-Language','en-USA');
            $header = explode(',',$header)[0];
            $header = explode('-',$header)[0];
            if($header === 'en') $header = 'en-USA';
            $language = Language::where('code',$header)->firstOrFail();
            return $language->translations;
        });

        \Laminas\Feed\Reader\Reader::setHttpClient(new GuzzleClient());

    }

    public function bootDebug()
    {


        if (app()->isLocal()) {
            app()->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }

        \Illuminate\Database\Query\Builder::macro('toRawSql', function(){
            return array_reduce($this->getBindings(), function($sql, $binding){
                return preg_replace('/\?/', is_numeric($binding) ? $binding : "'".$binding."'" , $sql, 1);
            }, $this->toSql());
        });

        \Illuminate\Database\Eloquent\Builder::macro('toRawSql', function(){
            return ($this->getQuery()->toRawSql());
        });

        // $this->app->singleton('db', function ($app) {
        //     $factory = $app->make('db.factory');

        //     return new ConnectionPooler($factory);
        // });
    }
}
