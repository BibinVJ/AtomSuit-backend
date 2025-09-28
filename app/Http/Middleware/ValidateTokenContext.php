<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ApiResponse;
use App\Models\Passport\ContextAwareToken;

class ValidateTokenContext
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Skip validation if no authentication is required
        if (!Auth::check()) {
            return $next($request);
        }

        // Get the current context
        $currentContext = tenant() ? 'tenant:' . tenant()->id : 'central';
        
        // Get token from the request
        $token = $this->getTokenFromRequest($request);
        if (!$token) {
            return $next($request);
        }

        // Get the token context from token name
        $tokenContext = $this->extractContextFromTokenName($token->name);
        
        \Log::info('Token context validation', [
            'token_name' => $token->name,
            'current_context' => $currentContext,
            'token_context' => $tokenContext,
        ]);

        // Check if the token can be used in the current context
        if ($currentContext !== $tokenContext) {
            \Log::warning('Token context mismatch - blocking access', [
                'token_context' => $tokenContext,
                'current_context' => $currentContext
            ]);
            
            return ApiResponse::error(
                'Token not valid for this context',
                [
                    'message' => 'This token cannot be used in the current context',
                    'token_context' => $tokenContext,
                    'current_context' => $currentContext
                ],
                403
            );
        }

        return $next($request);
    }

    /**
     * Get the token from the request by parsing the JWT and finding in DB
     */
    private function getTokenFromRequest(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return null;
            }

            // Get the current token from the user's token relationship
            $tokenValue = $request->bearerToken();
            if (!$tokenValue) {
                return null;
            }

            // Parse the JWT to get token ID
            $tokenId = $this->getTokenIdFromJWT($tokenValue);
            if (!$tokenId) {
                return null;
            }

            return ContextAwareToken::find($tokenId);
        } catch (\Exception $e) {
            \Log::warning('Error retrieving token', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Extract token ID from JWT
     */
    private function getTokenIdFromJWT(string $tokenValue)
    {
        try {
            // JWT tokens have 3 parts separated by dots
            $parts = explode('.', $tokenValue);
            if (count($parts) !== 3) {
                return null;
            }

            // Decode the payload (second part)
            $payload = json_decode(base64_decode($parts[1]), true);
            return $payload['jti'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Extract context information from token name
     */
    private function extractContextFromTokenName(string $tokenName): string
    {
        // Enhanced token name format: "PAT-central-1-1234567890" or "PAT-tenant:uuid-1-1234567890"
        if (preg_match('/^PAT-(.+?)-\d+-\d+$/', $tokenName, $matches)) {
            return $matches[1];
        }
        
        // Fallback for simpler format: "PAT-central" or "PAT-tenant:uuid"
        if (preg_match('/^PAT-(.+)$/', $tokenName, $matches)) {
            return $matches[1];
        }
        
        // Fallback for tokens without context info
        return 'unknown';
    }
}