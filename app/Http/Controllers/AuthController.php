<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

use App\Services\UserService;
use App\Services\AuthService;
//use App\Services\WebAuthnService;

// use Webauthn\AuthenticatorAssertionResponse;
// use Webauthn\AuthenticatorAssertionResponseValidator;
// use Webauthn\AuthenticatorAttestationResponseValidator;
// use Webauthn\AuthenticatorAttestationResponse;

use App\Models\Role;
use Illuminate\Validation\ValidationException;
class AuthController extends Controller
{
    protected $authService;
    protected $userService;
    protected $biometricService;

    public function __construct(Authservice $authservice, UserService $userService, ) //WebAuthnService $biometricService
    {
        $this->authService = $authservice;
        $this->userService = $userService;
        //$this->biometricService = $biometricService;
    }

    // public function getMicrosoftUrl()
    // {
    //     return response()->json($this->authService->getMicrosoftUrl());
    // }
    // public function microsoftCallback()
    // {
    //     $microsoftAccount = $this->authService->getMicrosoftUser();

    //     if (!$microsoftAccount) {
    //         Log::error('Missing Microsoft account in callback');
    //         return $this->redirectWithError('Kon Microsoft-accountgegevens niet ophalen.');
    //     }

    //     try {
    //         // findOrCreateUser gooit ValidationException bij bekende fouten
    //         $user = $this->userService->findOrCreateUser($microsoftAccount);

    //         $this->authService->authenticateUser($user);
    //         $this->authService->setAuthCookies($user);

    //         return redirect()->away(rtrim(config('app.url'), '/') . '/#/callback');

    //     } catch (ValidationException $e) {
    //         // Validatiefouten → toon netjes aan gebruiker
    //         $msg = collect($e->errors())->flatten()->implode(' ');
    //         return $this->redirectWithError($msg);

    //     } catch (\Throwable $e) {
    //         // Log full details for debugging
    //         Log::error('Microsoft callback unexpected error', [
    //             'message' => $e->getMessage(),
    //             'trace' => $e->getTraceAsString()
    //         ]);

    //         // Show a generic fallback message to the user
    //         $msg = 'Er is een onbekende fout opgetreden. Probeer het later opnieuw.';

    //         return $this->redirectWithError($msg);
    //     }

    // } 

    /**
     * Redirect naar SPA met flash message in cookie (leesbaar door JS).
     */
    protected function redirectWithError(string $message)
    {
        // korte levensduur (minuten)
        $minutes = 1;
        $secure = config('app.env') === 'production';
        $cookieDomain = env('COOKIE_SHARED_DOMAIN', null); // e.g. '.example.com' of null

        $cookie = cookie(
            'flashMessage',
            $message,
            $minutes,
            '/',                 // path
            $cookieDomain,       // domain
            $secure,             // secure
            false,               // httpOnly (false => readable door JS)
            false,               // raw
            'Lax'                // sameSite
        );

        return redirect()->away(env('APP_URL') . '/#/')->withCookie($cookie);
    }

    public function initLogin(Request $request)
    {
        $user = $this->userService->getUserByEmail($request['email']);

        if (!$user) {
            return response()->json(['messages' => ['Er is geen gebruiker gevonden met deze gegevens.']], 404);
        } 
        return response()->json($user);
    }

    public function finishLogin(Request $request)
    {
        $user = $this->userService->getUserByEmail($request['email']);

        if (!$user) {
            return response()->json(['messages' => ['Er is geen gebruiker gevonden met deze gegevens.']], 404);
        }

        if (!preg_match('/^\$2[ayb]\$.{56}$/', $user->password)) {
            return response()->json('', 201);
        }

        if (!$this->authService->checkPassword($user, trim($request['password']))) {
            return response()->json(['messages' => ['Wachtwoord incorrect.']], 401);
        } else {
            $this->authService->authenticateUser($user);
        }

        $token = $this->authService->generateToken($user);

        return response()->json(['token' => $token]);
    }


    // public function initBiometricLogin(Request $request)
    // {
    //     $findUser = $this->userService->getUserByEmail($request['email']);


    //     if ($findUser && !empty($findUser->publicKeyCredential)) {

    //         $allowedCredential = $this->biometricService->getUserCredential($findUser)->getPublicKeyCredentialDescriptor();

    //         $data = [
    //             'publicKeyCredentials' => $this->biometricService->generateLoginOptions($allowedCredential, $findUser),
    //             'type' => 'login',
    //             'idUser' => $findUser->id
    //         ];

    //         return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    //     }

    //     return response()->json([
    //         'messages' => ['Gebruiker niet gevonden of heeft geen vingerprint gekoppeld'],
    //     ], 404);
    // }



    // public function handleBiometricsRegister(Request $request, $idUser)
    // {

    //     $user = $this->userService->getUser($idUser);

    //     if (!$user) {
    //         return response()->json(['messages' => ['Er is geen gebruiker gevonden met deze gegevens.']], 404);
    //     }


    //     return response()->json([
    //         'publicKeyCredentials' => $this->biometricService->generateRegistrationOptions($user),
    //         'type' => 'register',
    //         'idUser' => Auth::id()
    //     ]);
    // }

    // public function verifyBiometricRegistration(Request $request, $idUser)
    // {
    //     $publicKeyCredentialCreationOptions = $this->biometricService->getPublicKeyCredentialCreationOptions($idUser);
    //     $credentials = $this->biometricService->publicKeyCredentialLoader->load(json_encode($request->all()));

    //     if ($credentials->response instanceof AuthenticatorAttestationResponse) {
    //         $validator = new AuthenticatorAttestationResponseValidator();
    //         try {
    //             $publicKeyCredentialSource = $validator->check(
    //                 $credentials->response,
    //                 $publicKeyCredentialCreationOptions,
    //                 'localhost'
    //             );

    //             $user = User::find($idUser);
    //             $user->biometricsRegistered = 1;
    //             $user->publicKeyCredential = json_encode($publicKeyCredentialSource);

    //             if ($user->save()) {
    //                 return response()->json("Registratie succesvol", 200);
    //             }
    //         } catch (\Exception $e) {
    //             return response()->json($e->getMessage());
    //         }
    //     }
    // }

    // public function verifyBiometricAuthentication(Request $request, $idUser)
    // {
    //     $associatedUser = User::find($idUser);
    //     $publicKeyCredentialSource = $this->biometricService->getUserCredential($associatedUser);
    //     $credentials = $this->biometricService->publicKeyCredentialLoader->load(json_encode($request->all()));
    //     if ($credentials->response instanceof AuthenticatorAssertionResponse) {
    //         $validator = new AuthenticatorAssertionResponseValidator();
    //         try {
    //             $publicKeyCredentialSource = $validator->check(
    //                 $publicKeyCredentialSource,
    //                 $credentials->response,
    //                 $this->biometricService->getPublicKeyCredentialCreationOptions($associatedUser->id),
    //                 'hethouvast.pearl-it.nl',
    //                 null
    //             );

    //             $this->authService->authenticateUser($associatedUser->first());

    //             $token = $this->authService->generateToken($associatedUser->first());

    //             return response()->json(['token' => $token]);
    //         } catch (Exception $e) {
    //             return response()->json($e->getMessage());
    //         }
    //     }
    //     return response()->json('Data incorrect response type', 400);
    // }


    public static function changePsw(Request $request)
    {
        $user = User::where('email', '=', $request['email'])->first();

        if ($request->newPassword !== $request->password_confirm) {
            return response()->json(['messages' => ['Wachtwoorden komen niet overeen.']], 400);
        }
        $user->password = Hash::make($request->newPassword);
        if ($user->save()) {
            return response()->json(200);
        }
        return response()->json(500);
    }

    public function resetPassword(Request $request)
    {
        return $this->authService->resetPassword($request);
    }

    public function sendResetLink(Request $request)
    {
        return $this->authService->sendResetLink($request);
    }

}

