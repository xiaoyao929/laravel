<?php

namespace App\Providers;

use Lib\PublicClass\{Log};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        $dbListen = env( 'DB_LISTEN', false );
        if( $dbListen )
        {
            DB::listen(function ($query) {
                Log::log_write( "sql：{$query->sql},数据：".json_encode( $query->bindings, JSON_UNESCAPED_UNICODE ).",时间：{$query->time}" );
            });
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
