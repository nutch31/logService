<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;

class checkSecretKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request The request form
     * @param  \Closure                 $next    The next request
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Returns header value with default as fallback
        $_SECRECT_KEY  = $request->header('secretKey', null);

        if ( $_SECRECT_KEY == null ) {
            return response()->json([
                'status'     => false,
                'statusCode' => 'HEADER_SECRET_KEY_NOT_PROVIDER',
                'data'       => [],
                'messages'   => 'Not valid Secret Key provider.',
                'errors'     => []
            ], 401);
        }

        if ( $_SECRECT_KEY != env('SECRECT_KEY')) {
            return response()->json([
                'status'     => false,
                'statusCode' => 'HEADER_SECRET_KEY_INVAID',
                'data'       => [],
                'messages'   => 'Secret Key information not match.',
                'errors'     => []
            ], 401);
        }

        return $next($request);
    }
}
