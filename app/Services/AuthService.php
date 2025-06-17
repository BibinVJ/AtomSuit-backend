<?php

namespace App\Services;

use App\DataTransferObjects\AuthenticatedUserDTO;
use App\Enums\UserStatus;
use App\Jobs\SendPasswordResetOtpMailJob;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
}
