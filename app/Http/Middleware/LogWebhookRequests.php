<?php

namespace App\Http\Middleware;

use App\Models\WebhookLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogWebhookRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        \Log::info('Incoming Webhook Request', [
            'url'     => $request->fullUrl(),
            'method'  => $request->method(),
            'headers' => $request->headers->all(),
            'payload' => $request->all(),
        ]);

        // Store Webhook Log in Database
        WebhookLog::create([
            'url'            => $request->fullUrl(),
            'method'         => $request->method(),
            'headers'        => $request->headers->all(),
            'payload'        => $request->all(),
            'response_status' => $response->getStatusCode(),
            'response_body'  => $response->getContent(),
        ]);

        return $response;
    }
}
