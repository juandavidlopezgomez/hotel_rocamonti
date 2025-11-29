<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceJsonResponse
{
    public function handle(Request $request, Closure $next)
    {
        // Manejar peticiones OPTIONS (preflight)
        if ($request->isMethod('OPTIONS')) {
            $response = response('', 200);
        } else {
            $request->headers->set('Accept', 'application/json');
            $response = $next($request);
        }

        // Configurar headers CORS
        $origin = $request->headers->get('Origin');
        if ($origin && str_starts_with($origin, 'http://localhost')) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
        } else {
            $response->headers->set('Access-Control-Allow-Origin', '*');
        }

        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS, PATCH');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With, Origin');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        $response->headers->set('Access-Control-Max-Age', '86400');

        return $response;
    }
}
