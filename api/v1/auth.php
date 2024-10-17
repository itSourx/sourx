<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            Log::info('Tentative de connexion', ['email' => $request->email]);

            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'mdp' => 'required|string|min:6',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            if (!Auth::attempt($request->only('email', 'mdp'))) {
                Log::warning('Échec de connexion', ['email' => $request->email]);
                return $this->errorResponse('Ces identifiants ne correspondent pas à nos enregistrements.', 401);
            }

            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;

            Log::info('Connexion réussie', ['user_id' => $user->id]);

            return $this->successResponse([
                'message' => 'Connexion réussie',
                'user' => $user,
                'token' => $token,
                'expires_in' => config('sanctum.expiration', 60 * 24 * 7), // 1 semaine par défaut
            ]);

        } catch (ValidationException $e) {
            Log::warning('Validation échouée', ['errors' => $e->errors()]);
            return $this->errorResponse($e->errors(), 422);
        } catch (\Exception $e) {
            Log::error('Erreur inattendue lors de la connexion', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->errorResponse('Une erreur inattendue est survenue lors de la connexion. Veuillez réessayer.', 500);
        }
    }

    protected function successResponse($data, $code = 200)
    {
        return response()->json($data, $code);
    }

    protected function errorResponse($message, $code)
    {
        return response()->json(['error' => $message], $code);
    }

    // ... autres méthodes
}
