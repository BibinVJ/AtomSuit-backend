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

        $payload = $request->all();
        $eventType = $payload['type'] ?? null;
        $eventId = $payload['id'] ?? null;

        \Log::info('Incoming Webhook Request', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'event_type' => $eventType,
            'event_id' => $eventId,
            'headers' => $request->headers->all(),
            'payload' => $payload,
        ]);

        // Store Webhook Log in Database
        WebhookLog::create([
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'event_type' => $eventType,
            'event_id' => $eventId,
            'headers' => $request->headers->all(),
            'payload' => $payload,
            'response_status' => $response->getStatusCode(),
            'response_body' => $response->getContent(),
        ]);

        return $response;
    }
}
