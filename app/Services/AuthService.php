<?php
namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

use App\Models\Role;
use Exception;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPasswordMail;


class AuthService
{

    public function getMicrosoftUser()
    {
        return Socialite::driver('azure')->stateless()->user();
    }



    public function setAuthCookies(User $user): void
    {
        $user->load('role'); // zorg dat rol is geladen

        setcookie('bearerToken', $this->generateToken($user), 0, '/', env('SESSION_DOMAIN'), false, false);
        setcookie('csrfToken', csrf_token(), 0, '/', env('SESSION_DOMAIN'), false, false);
        setcookie('profile', json_encode($user), 0, '/', env('SESSION_DOMAIN'), false, false);
    }


    /**
     * Check if the provided password is valid for the given user.
     *
     * @param User $user
     * @param string $password
     * @return bool
     */
    public function checkPassword(User $user, string $password): bool
    {
        return Hash::check($password, $user->password);
    }

    public function authenticateUser(User $user)
    {
        Auth::login($user);
    }

    /**
     * Generate an authentication token for the given user.
     *
     * @param User $user
     * @return string
     */
    public function generateToken(User $user): string
    {
        return $user->createToken('auth_token')->plainTextToken;
    }

    public function getMicrosoftUrl()
    {
        return Socialite::driver('azure')->stateless()->redirect()->getTargetUrl();
    }

    public function resetPassword(Request $request)
    {
        try {
            // Valideer invoer
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email',
                'token' => 'required',
                'password' => 'required|min:8|confirmed',
            ]);

            // Retourneer validatiefouten
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user, $password) {
                    $user->forceFill([
                        'password' => Hash::make($password),
                    ])->save();
                }
            );

            return match ($status) {
                Password::INVALID_TOKEN => response()->json(['message' => 'De opgegeven token is ongeldig of verlopen.'], 422),
                Password::INVALID_USER => response()->json(['message' => 'Er is geen gebruiker gevonden met dit e-mailadres.'], 404),
                default => response()->json(['message' => 'Wachtwoord succesvol gereset.'], 200),
            };
        } catch (Exception $e) {
            return response()->json(['message' => ['Wachtwoord resetten mislukt']], 500);
        }
    }

public function sendResetLink(Request $request)
    { 
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.required' => 'Het e-mailadres is verplicht.',
            'email.email' => 'Voer een geldig e-mailadres in.',
            'email.exists' => 'Er is geen gebruiker met dit e-mailadres.',
        ]);

        $currentUser = Auth::user(); 
        $status = Password::broker()->sendResetLink(
            $request->only('email'),
            function ($user, $token) use ($currentUser) { 

                $url = config('app.frontend_url') . '/#/resetWachtwoord?token=' . $token . '&email=' . urlencode($user->email);

                try {
                    $mail = Mail::to($user->email); 

                    $mail->send(new ResetPasswordMail($url)); 
                } catch (\Throwable $e) {
                    Log::error('❌ Failed to send reset email', [
                        'to' => $user->email,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        ); 
        return match ($status) {
            Password::RESET_LINK_SENT => response()->json(['message' => 'E-mail met resetlink is verstuurd.'], 200),
            default => response()->json(['errors' => ['message' => __($status)]], 500),
        };
    }
}