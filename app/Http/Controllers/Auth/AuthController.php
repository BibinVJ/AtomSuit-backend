<?php

namespace App\Http\Controllers\Auth;


use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;
use Laravel\Passport\Token;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function __construct(protected AuthService $authService) {}

    /**
     * Register a new user and return token.
     */
    public function register(RegisterRequest $request)
    {
        $registerdUser = $this->authService->register($request->validated());

        return ApiResponse::success('User registered successfully.', [
            'token' => [
                'access_token' => $registerdUser->authToken->accessToken,
                'expires_in'   => $registerdUser->authToken->expiresIn,
            ],
            'user' => new UserResource($registerdUser->user),
        ]);
    }

    /**
     * Login user and return token.
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();
        $authenticatedUser = $this->authService->login($credentials['email'],$credentials['password']);

        return ApiResponse::success('User authenticated successfully.', [
            'token' => [
                'access_token' => $authenticatedUser->authToken->accessToken,
                'expires_in'   => $authenticatedUser->authToken->expiresIn,
            ],
            'user' => new UserResource($authenticatedUser->user),
        ]);
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->each(function (Token $token) {
            $token->revoke();
            $token->refreshToken?->revoke();
        });

        return ApiResponse::success('Logout successful.');
    }

    // PHASE 3 - do not delete or uncomment 
    // /**
    //  * Redirect to the social provider.
    //  */
    // public function socialRedirect(string $provider)
    // {
    //     try {
    //         $url = Socialite::driver($provider)->stateless()->redirect()->getTargetUrl();

    //         return ApiResponse::success('Redirect URL generated.', [
    //             'url' => $url,
    //         ]);
    //     } catch (\Exception $e) {
    //         return ApiResponse::error("Unable to redirect to {$provider}.", [$e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    //     }
    // }

    // /**
    //  * Handle social callback and login/register user.
    //  */
    // public function socialCallback(Request $request, string $provider)
    // {
    //     try {
    //         $socialUser = Socialite::driver($provider)->stateless()->user();

    //         $user = User::firstOrCreate(
    //             ['email' => $socialUser->getEmail()],
    //             [
    //                 'name'        => $socialUser->getName() ?? $socialUser->getNickname(),
    //                 'provider'    => $provider,
    //                 'provider_id' => $socialUser->getId(),
    //                 'password'    => Str::password(12),
    //                 'email_verified_at' => now(),
    //             ]
    //         );

    //         $tokenResult = $user->createToken('Personal Access Token');
    //         $token = $tokenResult->accessToken;

    //         return redirect(config('app.frontend_url') . '/login?token=' . $token);
    //     } catch (\Exception $e) {
    //         return ApiResponse::error("Social login failed for {$provider}.", [$e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    //     }
    // }

}
