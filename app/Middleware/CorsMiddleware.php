<?php

namespace App\Middleware;

use Closure;
use Core\Http\Request;
use Core\Http\Respond;
use Core\Middleware\MiddlewareInterface;

final class CorsMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, Closure $next)
    {
        // Handle preflight requests
        if ($request->method() === 'OPTIONS') {
            $header = respond()->getHeader();
            $header->set('Access-Control-Allow-Origin', '*'); // or specify your domain
            $header->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $header->set('Access-Control-Allow-Headers', 'X-CSRF-Token, X-Requested-With, Accept, Content-Type, Authorization');
            $header->set('Access-Control-Max-Age', '86400'); // Cache preflight response

            return respond()->setCode(Respond::HTTP_NO_CONTENT); // Return 204 No Content for preflight
        }

        // Handle normal requests
        $response = $next($request);

        // Set CORS headers on the response
        $header = $response->getHeader();
        $header->set('Access-Control-Allow-Origin', '*'); // or specify your domain
        $header->set('Access-Control-Allow-Credentials', 'true');
        $header->set('Access-Control-Expose-Headers', 'Authorization, Content-Type, Cache-Control, Content-Disposition');

        // Ensure proper handling of varying headers
        $vary = $header->has('Vary') ? explode(', ', $header->get('Vary')) : [];
        $vary = array_unique([...$vary, 'Accept', 'Origin', 'User-Agent', 'Access-Control-Request-Method', 'Access-Control-Request-Headers']);
        $header->set('Vary', join(', ', $vary));

        return $response;
    }
}
