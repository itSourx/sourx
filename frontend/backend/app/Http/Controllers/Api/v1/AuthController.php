<?php

namespace App\Http\Controllers\Api\v1;

use App\Mail\testMail;
use Illuminate\Support\Facades\Mail;
use App\Mail\ConnexionNotification;
use Jenssegers\Agent\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\AirTable\AirtableUser;
use Illuminate\Support\Facades\Log;
use Tapp\Airtable\Facades\AirtableFacade;
use Illuminate\Support\Str;
use Tapp\Airtable\Airtable;
use Google\Cloud\Storage\StorageClient;
use App\Helpers\TokenHelper;
use Maatwebsite\Excel\Facades\Excel;




class AuthController extends Controller
{

    protected $storageClient;
    protected $bucket;

    public function __construct()
    {
        // Initialise Google Cloud Storage client
        $this->storageClient = new StorageClient([
            'keyFilePath' => storage_path('/service-account.json'), // Assurez-vous que ce chemin est correct
        ]);

        $this->bucket = $this->storageClient->bucket(env('GOOGLE_CLOUD_STORAGE_BUCKET'));
    }

    private function calculateTotalBucketSize()
    {
        $totalSize = 0;
        foreach ($this->bucket->objects() as $object) {
            $totalSize += $object->info()['size'];
        }
        return $totalSize;
    }

    private function calculateUserUploadsSize($userId)
    {
        $totalSize = 0;
        $userFolderPath = "users/{$userId}/documents/";

        $objects = $this->bucket->objects(['prefix' => $userFolderPath]);

        foreach ($objects as $object) {
            $totalSize += $object->info()['size'];
        }

        return $totalSize;
    }

    public function createUser(Request $request)
    {
        try {
            $existingUsers = AirtableFacade::table('Employee')->where('email', $request->email)->get();

            if (count($existingUsers) > 0) {
                return response()->json(['message' => 'Un utilisateur avec cet email existe déjà.'], 409);
            }

            $mdp = $request->mdp ?: substr(str_shuffle('0123456789'), 0, 6);
            info("MDP = ");
            info($mdp);
            $equipes = $request->Equipe;
            $equipeIds = [];

            foreach ($equipes as $equipeName) {
                $equipe = AirtableFacade::table('Equipe')->where('Nom', $equipeName)->get();

                if (!empty($equipe) && isset($equipe[0]['id'])) {
                    $equipeIds[] = $equipe[0]['id']; // Ajoute l'ID de l'équipe dans l'array final
                } else {
                    Log::warning("Équipe non trouvée : $equipeName");
                }
            }
            
            info($request->poste);

            $result = AirtableFacade::table('Employee')->create([
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'email' => $request->email,
                'FirstLogin' => 'true',
                'telephone' => $request->telephone,
                'mdp' => Hash::make($mdp),
                'Photo' => $request->photo ?? null,
                'isAdmin' => 0,
                'role' => $request->role ?? null,
                'Poste_id' => [$request->poste],
                'Equipe' => $equipeIds
            ]);
            // info($result);

            // Envoyer un mail pour donner le mdp au user ... 
            $result_2 = AirtableFacade::table('Password_reset')->create([
                'Email' => $request->email,
                'Code' => intval($mdp),
            ]);   

            
            return response()->json(['message' => 'Nouvel utilisateur enregistré'], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'enregistrement du nouvel employé', ['exception' => $e]);
            return response()->json(['error' => 'Erreur lors de l\'enregistrement du nouvel employé'], 500);
        }
    }

    public function bulkCreateUsers(Request $request)
    {
        try {
            $users = $request->input('users', []);
            $newUsers = [];

            foreach ($users as $user) {
                // Vérification de l'existence de l'utilisateur par email
                $existingUsers = AirtableFacade::table('Employee')->where('email', $user['email'])->get();
                if (count($existingUsers) > 0) {
                    Log::warning('Un utilisateur avec cet email existe déjà: ' . $user['email']);
                    continue; // Skip existing users
                }

                // Génération du mot de passe si non fourni
                $mdp = $user['mdp'] ?? substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);

                // Conversion des noms d'équipes en IDs d'équipes
                $equipeIds = [];
                foreach ($user['Equipe'] as $equipeName) {
                    $equipe = AirtableFacade::table('Equipe')->where('Nom', $equipeName)->get();
                    if (!empty($equipe) && isset($equipe[0]['id'])) {
                        $equipeIds[] = $equipe[0]['id'];
                    } else {
                        Log::warning("Équipe non trouvée : $equipeName");
                    }
                }

                $newUsers[] = [
                    'nom' => $user['nom'],
                    'prenom' => $user['prenom'],
                    'email' => $user['email'],
                    'telephone' => $user['telephone'],
                    'mdp' => Hash::make($mdp),
                    'Photo' => $user['photo'] ?? null,
                    'isAdmin' => 0,
                    'role' => $user['role'] ?? null,
                    'Poste' => $user['poste'],
                    'Equipe' => $equipeIds
                ];
            }

            AirtableFacade::table('Employee')->insert($newUsers);

            return response()->json(['message' => 'Les utilisateurs ont été enregistrés avec succès.'], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'importation des utilisateurs en masse', ['exception' => $e]);
            return response()->json(['error' => 'Erreur lors de l\'importation des utilisateurs.'], 500);
        }
    }


    public function deleteUser($id)
    {
        try {
            $user = AirtableFacade::table('Employee')->find($id);

            if (!$user) {
                return response()->json(['error' => 'Utilisateur non trouvé.'], 404);
            }

            // Supprimer l'utilisateur
            $result = AirtableFacade::table('Employee')->destroy($id);

            if ($result) {
                return response()->json(['message' => 'Utilisateur supprimé avec succès.'], 200);
            } else {
                return response()->json(['error' => 'Échec de la suppression de l\'utilisateur.'], 500);
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression de l\'utilisateur', ['exception' => $e]);
            return response()->json(['error' => 'Erreur lors de la suppression de l\'utilisateur'], 500);
        }
    }

    public function updateUser(Request $request, $id)
    {
        try {
            $user = AirtableFacade::table('Employee')->find($id);

            if (!$user) {
                return response()->json(['error' => 'Utilisateur non trouvé.'], 404);
            }

            // Vérifier s'il existe un utilisateur avec le même email (hors l'utilisateur actuel)
            $existingUsers = AirtableFacade::table('Employee')->where('email', $request->email)->get();

            if (count($existingUsers) > 0 && $existingUsers[0]['id'] !== $id) {
                return response()->json(['message' => 'Un utilisateur avec cet email existe déjà.'], 409);
            }

            $equipes = $request->Equipe;
            $equipeIds = [];

            foreach ($equipes as $equipeName) {
                $equipe = AirtableFacade::table('Equipe')->where('Nom', $equipeName)->get();

                if (!empty($equipe) && isset($equipe[0]['id'])) {
                    $equipeIds[] = $equipe[0]['id']; // Ajoute l'ID de l'équipe dans l'array final
                } else {
                    Log::warning("Équipe non trouvée : $equipeName");
                }
            }

            info($equipeIds);

            // Mettre à jour les informations de l'utilisateur
            $updatedData = [
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'email' => $request->email,
                'telephone' => $request->telephone,
                'role' => $request->role,
                'Poste' => $request->poste,
                'Equipe' => $equipeIds
            ];

            info($updatedData);

            // Utilisation de la méthode `patch` pour ne pas effacer les champs non spécifiés
            $result = AirtableFacade::table('Employee')->patch($id, $updatedData);

            info($result);

            return response()->json(['message' => 'Utilisateur mis à jour avec succès', 'user' => $result], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour de l\'utilisateur', ['exception' => $e]);
            return response()->json(['error' => 'Erreur lors de la mise à jour de l\'utilisateur'], 500);
        }
    }

    public function toggleArchiveStatus(Request $request, $id)
    {
        try {
            $user = AirtableFacade::table('Employee')->find($id);

            if (!$user) {
                return response()->json(['error' => 'Utilisateur non trouvé.'], 404);
            }

            $updatedData = [
                'isArchived' => $request->isArchived,
                'login_attempts' => 0,
            ];

            $result = AirtableFacade::table('Employee')->patch($id, $updatedData);

            return response()->json(['message' => 'Statut archivé mis à jour avec succès', 'user' => $result], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour du statut archivé de l\'utilisateur', ['exception' => $e]);
            return response()->json(['error' => 'Erreur lors de la mise à jour du statut archivé'], 500);
        }
    }



    private function createOrUpdateToken($api_token, $airtable_id)
    {
        $existingToken = AirtableFacade::table('token_table')->where('userid', $airtable_id)->get();
        if ($existingToken->isNotEmpty()) {
            $firstToken = $existingToken[0];
            AirtableFacade::table('token_table')->update($firstToken['id'], [
                'userid' => $airtable_id,
                'token' => $api_token,
                'expires_in' => 3600,
            ]);
            info("Update token...");
        } else {
            AirtableFacade::table('token_table')->create([
                'userid' => $airtable_id,
                'token' => $api_token,
                'expires_in' => 3600,
            ]);
            info("Create new token...");
        }
    }


    public function login(Request $request)
    {
        $agent = new Agent();
        $ipAddress = $request->ip();
        $device = $agent->device();
        $os = $agent->platform();
        $browser = $agent->browser();

        try {

            $request->validate([
                'email' => 'required|string|email',
                'mdp' => 'required|string',
            ]);
            $credentials = $request->only('email', 'mdp');

            $response = AirtableFacade::table('Employee')->where('email', $credentials['email'])->get();

            $userRecord = $response[0]['fields'];

            // Vérifier si l'utilisateur est valide
            if ($userRecord['isArchived'] == 1) {
                return response()->json(['message' => 'Utilisateur non trouvé'], 404);
            }

            $equipes = [];
            if (isset($userRecord['isArchived (from Equipe)']) && isset($userRecord['Nom (from Equipe)'])) {
                $equipes = array_filter($userRecord['Nom (from Equipe)'], function ($index) use ($userRecord) {
                    return $userRecord['isArchived (from Equipe)'][$index] == 0;
                }, ARRAY_FILTER_USE_KEY);
            }

            info($userRecord['isArchived (from Equipe)']);
            info($userRecord['Nom (from Equipe)']);
            info($userRecord['Equipe']);

            if (!Hash::check($credentials['mdp'], $userRecord['mdp'])) {
                $currentAttempts = $userRecord['login_attempts'] ?? 0;
                $currentAttempts++;

                // Mettre à jour les tentatives de connexion dans Airtable
                AirtableFacade::table('Employee')->patch($response[0]['id'], [
                    'login_attempts' => $currentAttempts,
                ]);

                // Vérifier si les tentatives atteignent 5
                if ($currentAttempts >= 5) {
                    AirtableFacade::table('Employee')->patch($response[0]['id'], [
                        'isArchived' => 1,
                    ]);
                    return response()->json(['message' => 'Compte bloqué après trop de tentatives échouées'], 403);
                }

                return response()->json(['message' => 'Mauvais mot de passe'], 401);
            }

            AirtableFacade::table('Employee')->patch($response[0]['id'], [
                'login_attempts' => 0,
            ]);

            $token = Str::random(60);
            $api_token = hash('sha256', $token);

            info($userRecord);

            $signedUrl = null;
            if (!empty($userRecord['Photo'])) {
                $photoUrl = $userRecord['Photo'] ?? null;
                if ($photoUrl) {
                    $fileName = basename(parse_url($photoUrl, PHP_URL_PATH)); // Extraire le nom du fichier
                    $bucket = $this->bucket; // Accéder au bucket Google Cloud Storage

                    $object = $bucket->object('Images/' . $fileName); // Assurez-vous que le chemin est correct
                    if ($object->exists()) {
                        $signedUrl = $object->signedUrl(new \DateTime('+1 hour'));
                    }
                }
            }

            // Mail::to($userRecord['email'])->send(new ConnexionNotification($ipAddress, $device, $os, $browser));

            $this->createOrUpdateToken($api_token, $userRecord['id']);

            $userData = [
                'system_id' => $response[0]['id'],
                'id' => $userRecord['id'],
                'FirstLogin' => filter_var($userRecord['FirstLogin'], FILTER_VALIDATE_BOOLEAN),
                'email' => $userRecord['email'],
                'telephone' => $userRecord['telephone'],
                'nom' => $userRecord['nom'],
                'prenom' => $userRecord['prenom'],
                'role' => $userRecord['role'],
                'poste' => $userRecord['Poste'] ?? null,
                'isAdmin' => $userRecord['isAdmin'],
                'Equipe' => $equipes,
                'photo' => $signedUrl,
                'user_uploads_size' => $this->calculateUserUploadsSize($userRecord['id']),
                'total_storage_used' => $this->calculateTotalBucketSize(),
            ];

            return response()->json([
                'message' => 'Connexion réussie!',
                'user' => $userData,
                'token' => $token,
                'expires_in' => 3600
            ]);
        } catch (\Exception $e) {
            Log::error('Error during login:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Echec de la connexion'], 500);
        }
    }

    public static function getAuthUser(Request $request)
    {
        $token = $request->bearerToken();
        $tokenHash = hash('sha256', $token);
        $tokenInfo = AirtableFacade::table('token_table')->where('token', $tokenHash)->get();

        if (!$tokenInfo->isEmpty()) {
            $employeeId = $tokenInfo[0]['fields']['userid'];

            try {
                $response = AirtableFacade::table('Employee')->where('id', $employeeId)->get();
                $userRecord = $response[0]['fields'];

                $equipes = [];
                if (isset($userRecord['isArchived (from Equipe)']) && isset($userRecord['Nom (from Equipe)'])) {
                    $equipes = array_filter($userRecord['Nom (from Equipe)'], function ($key) use ($userRecord) {
                        return $userRecord['isArchived (from Equipe)'][$key] == 0; // Filtrer où 'isArchived' est 0 (non archivé)
                    }, ARRAY_FILTER_USE_KEY);
                }

                $signedUrl = null;
                if (!empty($userRecord['Photo'])) {
                    $photoUrl = $userRecord['Photo'] ?? null;
                    if ($photoUrl) {
                        $fileName = basename(parse_url($photoUrl, PHP_URL_PATH)); // Extraire le nom du fichier
                        $bucket = $this->bucket; // Accéder au bucket Google Cloud Storage

                        $object = $bucket->object('Images/' . $fileName); // Assurez-vous que le chemin est correct
                        if ($object->exists()) {
                            // Générer un lien signé valide pendant 1 heure
                            $signedUrl = $object->signedUrl(new \DateTime('+1 hour'));
                        }
                    }
                }

                $userData = [
                    'id' => $userRecord['id'],
                    'FirstLogin' => filter_var($userRecord['FirstLogin'], FILTER_VALIDATE_BOOLEAN),
                    'email' => $userRecord['email'],
                    'telephone' => $userRecord['telephone'],
                    'nom' => $userRecord['nom'],
                    'prenom' => $userRecord['prenom'],
                    'isAdmin' => $userRecord['isAdmin'],
                    'role' => $userRecord['role'],
                    'poste' => $userRecord['Poste'],
                    'Equipe' => $equipes,
                    'photo' => $signedUrl,
                    'user_uploads_size' => $this->calculateUserUploadsSize($userRecord['id']),
                ];

                return response()->json([
                    'message' => 'Login successful',
                    'user' => $userData,
                    'token' => $token,
                ]);
            } catch (\Exception $e) {
                Log::error('Error fetching documents by employee ID: ' . $e->getMessage());
                return response()->json(['message' => 'An error occurred while fetching documents'], 500);
            }
        } else {
            return response()->json(['message' => 'Token not found'], 404);
        }
    }

    public function updateProfile(Request $request)
    {
        try {
            // Récupération de l'utilisateur connecté
            $user = TokenHelper::getUserIdFromToken($request);

            info($user);
            if (!$user) {
                return response()->json(['error' => 'Utilisateur non trouvé.'], 404);
            }

            info($request);

            // Validation des données reçues
            /* $request->validate([
                'prenom' => 'required|string|max:255',
                'nom' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'telephone' => 'required|string|max:15',
                'photo' => 'nullable|string',
                'current_password' => 'nullable|string',
                'new_password' => 'nullable|string|min:8|different:current_password',
            ]); */

            // Vérification du mot de passe actuel si un nouveau mot de passe est fourni
            if ($request->filled('current_password') && !Hash::check($request->current_password, $user[0]['fields']['mdp'])) {
                return response()->json(['error' => 'Mot de passe actuel incorrect.'], 403);
            }
            info("---------------");
            // Mise à jour des données utilisateur
            $updatedData = [
                'prenom' => $request->prenom,
                'nom' => $request->nom,
                /* 'email' => $request->email, */
                'telephone' => $request->telephone,
            ];

            if ($request->has('photo')) {
                $photoData = $request->photo;

                if (preg_match('/^data:image\/(\w+);base64,/', $photoData, $type)) {
                    $photoData = substr($photoData, strpos($photoData, ',') + 1);
                    $type = strtolower($type[1]); // jpg, png, gif, etc.

                    if (!in_array($type, ['jpg', 'jpeg', 'png', 'gif'])) {
                        return response()->json(['error' => 'Type de fichier non supporté.'], 415);
                    }

                    $photoData = base64_decode($photoData);

                    if ($photoData === false) {
                        return response()->json(['error' => 'L\'image n\'a pas pu être décodée.'], 415);
                    }

                    $fileName = time() . '_image.' . $type;
                    $filePath = 'Images/' . $fileName;

                    // Créer un fichier temporaire
                    $tempFile = tmpfile();
                    fwrite($tempFile, $photoData);
                    $fileStream = stream_get_meta_data($tempFile)['uri'];

                    $object = $this->bucket->upload(fopen($fileStream, 'r'), [
                        'name' => $filePath,
                    ]);

                    $publicPath = sprintf('https://storage.googleapis.com/%s/%s', env('GOOGLE_CLOUD_STORAGE_BUCKET'), $filePath);

                    $updatedData['Photo'] = $publicPath;

                    fclose($tempFile);
                } else {
                    $updatedData['Photo'] = $user[0]['fields']['Photo'];
                }
            } else {
                $updatedData['Photo'] = $user[0]['fields']['Photo']; // Fais attention à [0]
            }


            // Met à jour le mot de passe uniquement si un nouveau est fourni
            if ($request->filled('new_password')) {
                $updatedData['mdp'] = Hash::make($request->new_password);
            }

            // Mettre à jour les données dans Airtable
            $result = AirtableFacade::table('Employee')->patch($user[0]['id'], $updatedData);

            info($result);

            // Retourner les données utilisateur mises à jour
            $userData = [
                'id' => $result['id'],
                'prenom' => $result['fields']['prenom'],
                'nom' => $result['fields']['nom'],
                'email' => $result['fields']['email'],
                'telephone' => $result['fields']['telephone'],
                'photo' => $result['fields']['Photo'] ?? null,
            ];

            return response()->json([
                'message' => 'Profil mis à jour avec succès',
                'user' => $userData
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour du profil', ['exception' => $e->getMessage()]);
            return response()->json(['error' => 'Une erreur est survenue lors de la mise à jour du profil.'], 500);
        }
    }



    public function removeUserFromTeam($userId, $teamId)
    {
        try {
            // Récupérer l'utilisateur par son ID
            $user = AirtableFacade::table('Employee')->find($userId);

            if (!$user) {
                return response()->json(['error' => 'Utilisateur non trouvé.'], 404);
            }
            info("USER-----------");
            info($user);

            // Récupérer les équipes de l'utilisateur
            $currentTeams = $user['fields']['Equipe'] ?? [];

            info("CURRENT TEAMS-----------");
            info($currentTeams);

            // Vérifier si l'utilisateur est dans au moins deux équipes
            if (count($currentTeams) <= 1) {
                return response()->json(['message' => 'Impossible de retirer l\'utilisateur de sa seule équipe.'], 400);
            }

            // Vérifier si l'utilisateur est bien dans l'équipe du manager
            if (!in_array($teamId, $currentTeams)) {
                return response()->json(['message' => 'L\'utilisateur n\'appartient pas à cette équipe.'], 400);
            }

            // Supprimer l'utilisateur de l'équipe spécifiée
            $team = AirtableFacade::table('Equipe')->find($teamId);

            info("DELETED FROM-----------");
            info($team);


            if ($team) {
                // Mettre à jour les membres de l'équipe en supprimant l'utilisateur
                $updatedMembers = array_filter(
                    $team['fields']['Employee'],
                    fn($memberId) => $memberId !== $userId
                );

                // S'assurer que l'équipe n'est pas vide après suppression de l'utilisateur
                if (!empty($updatedMembers)) {
                    AirtableFacade::table('Equipe')->patch($teamId, ['Employee' => $updatedMembers]);
                }
            }

            // Réponse réussie
            return response()->json(['message' => 'Utilisateur retiré de l\'équipe avec succès.'], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression de l\'utilisateur de l\'équipe', ['exception' => $e]);
            return response()->json(['error' => 'Erreur lors de la suppression de l\'utilisateur de l\'équipe.'], 500);
        }
    }




    public function getAllEmployees(Request $request)
    {

        try {
            $user = TokenHelper::getUserIdFromToken($request);

            $employees = AirtableFacade::table('Employee')->get();
            $filteredEmployees = [];

            info($user);

            if ($user[0]['fields']["role"] == "Manager") {
                $filteredEmployees = $employees->filter(function ($employee) {
                    return isset($employee['fields']['isArchived']) && $employee['fields']['isArchived'] == 0 &&
                        isset($employee['fields']['role']) && $employee['fields']['role'] == 'Salarie';
                })->values();
            } else if ($user[0]['fields']["role"] == "Administrateur") {
                $filteredEmployees = $employees;
            }

            info($filteredEmployees);


            $formattedEmployees = $filteredEmployees->map(function ($employee) {
                return [
                    'id' => $employee['id'],
                    'email' => $employee['fields']['email'] ?? null,
                    'nom' => $employee['fields']['nom'] ?? null,
                    'prenom' => $employee['fields']['prenom'] ?? null,
                    'telephone' => $employee['fields']['telephone'] ?? null,
                    'poste' => $employee['fields']['Poste'] ?? null,
                    'role' => $employee['fields']['role'] ?? null,
                    'Equipe' => $employee['fields']['Nom (from Equipe)'] ?? null,
                    'EquipeIsArchived' => $employee['fields']['isArchived (from Equipe)'] ?? 0,
                    'photo' => $employee['fields']['Photo'] ?? null,
                    'isArchived' => $employee['fields']['isArchived'] ?? 0,
                ];
            });

            info('Employees fetched successfully:', ['employees' => $formattedEmployees]);

            return response()->json([
                'employees' => $formattedEmployees
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while fetching employees'], 500);
        }
    }


    public function logout(Request $request)
    {
        $token = $request->bearerToken();

        // Vérifier si le jeton est fourni
        if (!$token) {
            return response()->json(['message' => 'Missing token'], 401);
        }

        $tokenRecord = AirtableFacade::table('token_table')->where('token', $token)->get();

        $tokenRecord =  isset($reponse[0]['fields']['token']) ? $reponse[0]['fields']['token'] : null;
        AirtableFacade::table('token_table')->delete($tokenRecord['id']);

        // Vérifier si le jeton existe
        // if (!$tokenRecord) {
        //     return response()->json(['message' => 'Invalid token'], 401);
        // }

        // // Invalider le jeton (marquer comme révoqué)
        // $tokenRecord->update(['revoked' => true]);

        // Retourner une réponse de réussite
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function getUserInformations(Request $request)
    {
        try {
            // Recherche du jeton dans la table token_table
            $tokenHash = hash('sha256', $request->token);
            $tokenInfo = AirtableFacade::table('token_table')->where('token', $tokenHash)->get();
            if (!$tokenInfo[0]) {
                return response()->json([
                    'message' => 'Token not found',
                ], 404);
            }

            $user_id = ($tokenInfo[0]['fields'])['userid'];
            $reponse = AirtableFacade::table('Employee')->where('id', $user_id)->get();
            $user =  isset($reponse[0]['fields']) ? $reponse[0]['fields'] : null;
            //info($user);
            $Auser = new AirtableUser($user);
            return response()->json([
                'user' => [
                    'nom' => $Auser->getNom(),
                    'prenom' => $Auser->getPreNom(),
                    'email' => $Auser->getEmail(),
                    'telephone' => $Auser->getTelephone(),
                    'photo' => $Auser->getPhoto(),
                    'isAdmin' => $Auser->getisAdmin(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error during login:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'An error occurred during login'
            ], 500);
        }
    }
}
