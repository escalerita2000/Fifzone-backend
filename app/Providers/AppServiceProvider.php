<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

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
        Builder::macro('whereLike', function ($column, $value) {
            $likeOperator = DB::connection()->getDriverName() === 'sqlite' ? 'like' : 'ilike';
            return $this->where($column, $likeOperator, $value);
        });

        Builder::macro('orWhereLike', function ($column, $value) {
            $likeOperator = DB::connection()->getDriverName() === 'sqlite' ? 'like' : 'ilike';
            return $this->orWhere($column, $likeOperator, $value);
        });
    }
}
