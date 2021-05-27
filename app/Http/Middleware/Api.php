<?php

namespace App\Http\Middleware;

use App\Models\ApiErrors;
use App\Models\Helper;
use Illuminate\Http\Request;
use PhpParser\Node\Expr\Closure;

class Api
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $status_code = $response->original instanceof ApiErrors ? $response->original->status : $response->getStatusCode();
        $response_type = Helper::isSuccessHTTPStatus($status_code) ? 'success' : 'error';

        return response(json_encode([$response_type => $response->original]), $status_code)
            ->header('content-type', 'application/json;charset=utf-8');
    }
}
