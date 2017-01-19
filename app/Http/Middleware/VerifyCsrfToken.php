<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;
use Closure;

use App\Dao\{AccessModelDao};
use Lib\PublicClass\{S};

class VerifyCsrfToken extends BaseVerifier
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        //
    ];
    public function handle($request, Closure $next)
    {
        try
        {
            return parent::handle($request, $next);
        }
        catch ( \Exception $e )
        {
            if( AccessModelDao::isApiModel())
                S::error('60001');
            else
                abort(403);
        }
    }
}
