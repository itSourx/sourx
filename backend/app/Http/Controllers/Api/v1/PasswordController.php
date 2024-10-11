<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPasswordCodeMail;

use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Helpers\TokenHelper;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\AirTable\AirtableTrait;
use App\Models\AirTable\AirtableReponse;
use Tapp\Airtable\Facades\AirtableFacade;

class PasswordController extends Controller
{
    use AirtableTrait, AirtableReponse;

    public function firstLoginChange(Request $request)
    {
        try {
            $user = TokenHelper::getUserIdFromToken($request);

            if (!$user) {
                return response()->json(['error' => 'Utilisateur non trouvé.'], 404);
            }

            /* $request->validate([
                'newPassword' => 'required|string|min:8',
            ]); */

            $updatedData = [
                'mdp' => Hash::make($request->newPassword),
                'FirstLogin' => "false",
            ];

            $result = AirtableFacade::table('Employee')->patch($user[0]['id'], $updatedData);
            info($result);
            return response()->json([
                'message' => 'Mot de passe changé avec succès',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors du changement de mot de passe de la première connexion', ['exception' => $e->getMessage()]);
            return response()->json(['error' => 'Une erreur est survenue lors du changement du mot de passe.'], 500);
        }
    }


    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $existingResponse = AirtableFacade::table('Password_reset')->where('Email', $request->email)->get();
        $code = mt_rand(100000, 999999);

        if (count($existingResponse) > 0) {
            $resetId = $existingResponse[0]['id'];

            $response = AirtableFacade::table('Password_reset')->patch($resetId, [
                'Code' => $code,
            ]);
        } else {
            $employee = AirtableFacade::table('Employee')->where('email', $request->email)->get();
            $response = AirtableFacade::table('Password_reset')->create([
                'Email' => $request->email,
                'Employee' => [$employee[0]['id']],
                'Code' => $code,
            ]);
        }

        // Mail::to($request->email)->send(new ResetPasswordCodeMail($code));

        return response()->json(['message' => 'Reset link sent'], 200);
    }

    public function verifyResetCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|numeric'
        ]);

        $response = AirtableFacade::table('Password_reset')
            ->where('Email', $request->email)
            ->where('Code', $request->code)
            ->get();

        if (count($response) == 0) {
            return response()->json(['message' => 'Invalid code'], 400);
        }

        return response()->json(['message' => 'Code verified'], 200);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            // 'newPassword' => 'required|string|min:8',
        ]);

        // Récupérer l'utilisateur par son email
        $response = AirtableFacade::table('Employee')->where('email', $request->email)->get();
        if (count($response) == 0) {
            return response()->json(['message' => 'Email not found'], 404);
        }

        // Mettre à jour le mot de passe
        $userId = $response[0]['id'];

        // Mettre à jour le mot de passe tout en conservant les autres colonnes intactes
        $updatedData = [
            'mdp' => Hash::make($request->newPassword),
        ];

        try {
            $response = AirtableFacade::table('Employee')->patch($userId, $updatedData);
        } catch (\Exception $e) {
            Log::error('Error resetting password', ['exception' => $e->getMessage()]);
            return response()->json(['error' => 'An error occurred while resetting the password.'], 500);
        }

        return response()->json(['message' => 'Password reset successfully'], 200);
    }
}
