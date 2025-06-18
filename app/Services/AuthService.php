<?php

namespace App\Services;

use App\DataTransferObjects\AuthenticatedUserDTO;
use App\Enums\UserStatus;
use App\Jobs\SendPasswordResetOtpMailJob;
use App\Models\UserLoginDetail;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Token;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class AuthService
{
    public function __construct(protected UserRepository $userRepository) {}

    public function register(array $data)
    {
        $user = $this->userRepository->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'status' => UserStatus::Pending,
            'status_updated_at' => now(),
        ]);

        $user->assignRole($data['role']);

        $authToken = $user->createToken('Personal Access Token');

        return new AuthenticatedUserDTO($user, $authToken);
    }

    public function login(string $email, string $password)
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user || !Hash::check($password, $user->password)) {
            throw new UnauthorizedHttpException('', 'Invalid credentials');
        }

        if ($user->status !== UserStatus::Active) {
            throw new UnauthorizedHttpException('', 'User account is not active');
        }

        $authToken = $user->createToken('Personal Access Token');

        return new AuthenticatedUserDTO($user, $authToken);
    }

    public function sendResetOtp(string $email): void
    {
        $otp = rand(100000, 999999);
        $hashed = Hash::make($otp);

        DB::table('reset_tokens')->updateOrInsert(
            ['email' => $email],
            ['token' => $hashed, 'created_at' => now()]
        );

        dispatch(new SendPasswordResetOtpMailJob($email, $otp));
    }

    public function verifyOtp(string $email, string $otp): bool
    {
        $record = DB::table('reset_tokens')->where('email', $email)->first();
        if (!$record || !Hash::check($otp, $record->token)) return false;

        return !Carbon::parse($record->created_at)->addMinutes(10)->isPast();
    }

    public function resetPassword(string $email, string $newPassword, ?string $otp = null): bool
    {
        if ($otp && !$this->verifyOtp($email, $otp)) return false;

        $user = $this->userRepository->findByEmail($email);
        $this->userRepository->updatePassword($user, Hash::make($newPassword));

        DB::table('reset_tokens')->where('email', $email)->delete();
        return true;
    }

    public function logoutUser($user, $fromAlldevices = false): void
    {
        if ($fromAlldevices) {
            // revoke all tokens for the user
            $user->tokens()->each(function (Token $token) {
                $token->revoke();
                $token->refreshToken?->revoke();
            });

            // Update all active login sessions
            UserLoginDetail::where('user_id', $user->id)
                ->whereNull('logout_at')
                ->update(['logout_at' => now()]);
        } else {
            $user->token()?->revoke();
            $user->token()?->refreshToken?->revoke();

            $this->updateSessionByToken($user->token()->id);
        }
    }

    private function updateSessionByToken($tokenId)
    {
        // If you store token_id, you can update specific session
        UserLoginDetail::where('token_id', $tokenId)
            ->whereNull('logout_at')
            ->update([
                'logout_at' => now(),
            ]);
    }
}
