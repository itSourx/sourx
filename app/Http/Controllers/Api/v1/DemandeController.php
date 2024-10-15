<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Mail\DemandeAccepter;
use App\Models\AirTable\AirtableUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Tapp\Airtable\Facades\AirtableFacade;
use App\Http\Controllers\ActivityLogController;
use Carbon\Carbon;
use App\Helpers\TokenHelper;
use Google\Cloud\Storage\StorageClient;
use App\Mail\DemandeAcceptee;
use App\Mail\DemandeRefusee;

class DemandeController extends Controller
{

    protected $storageClient;
    protected $bucket;

    public function __construct()
    {
        // Initialiser le client Google Cloud Storage
        $this->storageClient = new StorageClient([
            'keyFilePath' => storage_path('/service-account.json'),
        ]);

        $this->bucket = $this->storageClient->bucket(env('GOOGLE_CLOUD_STORAGE_BUCKET'));
    }

    private function createUserFolderIfNotExists($folderPath)
    {
        $object = $this->bucket->object($folderPath);
        if (!$object->exists()) {
            $this->bucket->upload('', [
                'name' => $folderPath,
                'metadata' => [
                    'contentType' => 'application/x-directory'
                ]
            ]);
        }
    }

    public function createDemand(Request $request)
    {
        $userId = TokenHelper::getUserIdFromToken($request)[0]['id'];
        $currentDate = Carbon::now()->format('n/j/Y');

        $filenames = [];
        $documentLinks = [];

        if (is_array($request->file('filesToUpload'))) {
            foreach ($request->file('filesToUpload') as $file) {
                $filename = time() . '_' . $file->getClientOriginalName();
                $fileStream = fopen($file->getPathname(), 'r');

                // Générer un chemin de dossier basé sur l'utilisateur
                $userFolderPath = "users/{$userId}/demandes/";
                $this->createUserFolderIfNotExists($userFolderPath);

                $filePath = $userFolderPath . $filename;
                $object = $this->bucket->upload($fileStream, [
                    'name' => $filePath,
                ]);

                $publicPath = sprintf('https://storage.googleapis.com/%s/%s', env('GOOGLE_CLOUD_STORAGE_BUCKET'), $filePath);

                $filenames[] = $filename;
                $documentLinks[] = $publicPath;
            }

            $filenamesString = implode(',', $filenames);
            $documentLinksString = implode(',', $documentLinks);
        }

        try {
            $data = [
                'Raison' => [$request->Titre],
                'DateDemande' => $currentDate,
                'Description' => $request->Description,
                'Status' => $request->Status,
                'Employe' => [$userId],
            ];


            if (!empty($documentLinksString)) {
                $data['TitreJustificatif'] = $filenamesString;
                $data['Justificatifs'] = $documentLinksString;
            }
            info($data);

            $result = AirtableFacade::table('Demande')->create($data);

            ActivityLogController::logActivity($userId, 'Ajout/Creation', 'Nouvelle Demande "' . $request->Titre . '" effectuée');

            return response()->json(['message' => 'Nouvelle demande créée avec succès'], 200);
        } catch (\Exception $e) {
            Log::error('Error registering new demand: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur de création de la demande', 'error' => $e->getMessage()], 500);
        }
    }

    public function createMotif(Request $request)
    {
        try {
            $data = [
                'Titre' => $request->input('title'),
                'isArchived' => 0
            ];
            $result = AirtableFacade::table('MotifsDemande')->create($data);
            return response()->json(['message' => 'Motif créé avec succès', 'data' => $result], 201);
        } catch (\Exception $e) {
            Log::error('Error creating motif: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la création du motif'], 500);
        }
    }

    public function updateMotif($id, Request $request)
    {
        try {
            $updatedFields = [
                'Titre' => $request->input('title'),
            ];
            $result = AirtableFacade::table('MotifsDemande')->patch($id, $updatedFields);
            return response()->json(['message' => 'Motif mis à jour avec succès', 'data' => $result], 200);
        } catch (\Exception $e) {
            Log::error('Error updating motif: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la mise à jour du motif'], 500);
        }
    }

    public function archiveMotif($id)
    {
        try {
            $updatedFields = ['isArchived' => 1];
            $result = AirtableFacade::table('MotifsDemande')->patch($id, $updatedFields);
            return response()->json(['message' => 'Motif archivé avec succès', 'data' => $result], 200);
        } catch (\Exception $e) {
            Log::error('Error archiving motif: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de l\'archivage du motif'], 500);
        }
    }

    public function unarchiveMotif($id)
    {
        try {
            $motif = AirtableFacade::table('MotifsDemande')->find($id);

            if (!$motif || !isset($motif['fields'])) {
                return response()->json(['message' => 'Motif non trouvé'], 404);
            }

            $updatedFields = ['isArchived' => 0];
            AirtableFacade::table('MotifsDemande')->patch($id, $updatedFields);

            return response()->json(['message' => 'Motif désarchivé avec succès'], 200);
        } catch (\Exception $e) {
            Log::error('Error unarchiving motif: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors du désarchivage'], 500);
        }
    }


    public function getMotifs(Request $request)
    {
        try {
            // Récupérer les motifs non archivés
            $motifs = AirtableFacade::table('MotifsDemande')
                /* ->where('isArchived', 0) // Filtrer les motifs actifs */
                ->get();

            $formattedMotifs = collect($motifs)->map(function ($motif) {
                return [
                    'id' => $motif['id'],
                    'title' => $motif['fields']['Titre'],
                    'isArchived' => $motif['fields']['isArchived'] ?? 0,
                ];
            })->values()->all();

            return response()->json($formattedMotifs, 200);
        } catch (\Exception $e) {
            Log::error('Error fetching motifs: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la récupération des motifs'], 500);
        }
    }


    public function prendreEnCharge($requestId, Request $request)
    {
        try {
            $userId = TokenHelper::getUserIdFromToken($request)[0]['id'];

            $updatedFields = [
                'Status' => 'En cours',
            ];

            $result = AirtableFacade::table('Demande')->patch($requestId, $updatedFields);

            ActivityLogController::logActivity($userId, 'Prise en charge', 'Demande "' . $requestId . '" prise en charge');

            return response()->json([
                'message' => 'La demande a été prise en charge avec succès.',
                'data' => $result
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error taking over request: ' . $e->getMessage());
            return response()->json([
                'message' => 'Une erreur s\'est produite lors de la prise en charge de la demande.',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    public function getDemandsByUser(Request $request)
    {
        $userId = TokenHelper::getSimpleUserIdFromToken($request);
        try {
            $demandes = AirtableFacade::table('Demande')->where('Employe',  $userId)->get();
            $groupedDemandes = [];

            foreach ($demandes as $demande) {
                $id = $demande['id'] ?? null;

                if (!isset($groupedDemandes[$id])) {
                    $groupedDemandes[$id] = [
                        'Id' => $id,
                        'Titre' => $demande['fields']['Titre (from Raison)'][0] ?? null,
                        'Description' => $demande['fields']['Description'] ?? null,
                        'Status' => $demande['fields']['Status'] ?? null,
                        'Date' => $demande['fields']['DateDemande'] ?? null,
                        'DateReponse' => $demande['fields']['DateReponse'] ?? null,
                        'Motif' => $demande['fields']['Motif'] ?? null,
                        'TitreJustificatifs' => $demande['fields']['TitreJustificatif'] ?? null,
                        'Justificatifs' => $demande['fields']['Justificatifs'] ?? null,
                        'TitreAttachementResponses' => $demande['fields']['TitreAttachementResponse'] ?? null,
                        'AttachementResponses' => $demande['fields']['AttachementResponse'] ?? null,
                    ];
                }
            }

            $formattedDemandes = array_values($groupedDemandes);

            return response()->json($formattedDemandes, 200);
        } catch (\Exception $e) {
            Log::error('Error fetching demandes of the user: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while fetching demandes'], 500);
        }
    }

    public function updateRequest($requestId, Request $request)
    {
        try {
            $userId = TokenHelper::getUserIdFromToken($request)[0]['id'];
            $updatedFields = [];

            if ($request->has('title')) {
                $updatedFields['Raison'] = [$request->input('title')];
            }

            if ($request->has('description')) {
                $updatedFields['Description'] = $request->input('description');
            }

            $currentDate = Carbon::now()->format('n/j/Y');
            $updatedFields['DerniereModification'] = $currentDate;


            if ($request->hasFile('filesToUpload')) {
                $filenames = [];
                $documentLinks = [];
                foreach ($request->file('filesToUpload') as $file) {
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $fileStream = fopen($file->getPathname(), 'r');

                    // Générer un chemin de dossier basé sur l'utilisateur
                    $userFolderPath = "users/{$userId}/demandes/";
                    $this->createUserFolderIfNotExists($userFolderPath);

                    $filePath = $userFolderPath . $filename;
                    $this->bucket->upload($fileStream, [
                        'name' => $filePath,
                    ]);

                    $publicPath = sprintf('https://storage.googleapis.com/%s/%s', env('GOOGLE_CLOUD_STORAGE_BUCKET'), $filePath);

                    $filenames[] = $filename;
                    $documentLinks[] = $publicPath;
                }

                $updatedFields['TitreJustificatif'] = implode(',', $filenames);
                $updatedFields['Justificatifs'] = implode(',', $documentLinks);
            }

            $result = AirtableFacade::table('Demande')->patch($requestId, $updatedFields);

            info($result);

            return response()->json([
                'message' => 'Demande mise à jour avec succès.',
                'data' => $result
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error updating request: ' . $e->getMessage());
            return response()->json([
                'message' => 'Une erreur s\'est produite lors de la mise à jour de la demande.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function getDemandsToValidate(Request $request)
    {
        try {
            $userId = TokenHelper::getUserIdFromToken($request);
            $user = $userId[0]['fields'];
            $demandesIds = [];

            if ($user['role'] == 'Administrateur') {
                info("MANAGEEERRR");
                $managers = AirtableFacade::table('Employee')->where('role', 'Manager')->get();
                foreach ($managers as $manager) {
                    if (isset($manager['fields']['Demande']) && ($manager['fields']['role'] === "Manager")) {
                        $demandesIds[] = $manager['fields']['Demande'];
                    }
                }
                $demandesIds = array_unique(array_merge(...$demandesIds));
                info($demandesIds);
            } else {
                $userRole = $user['role'];
                $userEquipe = $user['Equipe'] ?? null;

                info($userEquipe);
                if ($userRole === 'Manager' && $userEquipe) {

                    foreach ($userEquipe as $equipe) {
                        $equipe = AirtableFacade::table('Equipe')->find($equipe);
                        $demandesIdsDuplicated[] = $equipe['fields']['Demande (from Employee)'] ?? [];
                    }
                    $demandesIds = array_unique(array_merge(...$demandesIdsDuplicated));
                } else {
                    return response()->json(['message' => 'Unauthorized access'], 403);
                }
            }

            // Récupérez les demandes en utilisant les IDs collectés
            if (!empty($demandesIds)) {
                $formula = "OR(" . $this->generateFormulaForIds($demandesIds) . ")";
                $demandes = AirtableFacade::table('Demande')
                    ->filterByFormula("AND(OR({Status} = 'En cours', {Status} = 'Soumis'), $formula)")
                    ->get();
            } else {
                $demandes = [];
            }

            info($demandes);
            info("#############");


            // Formatez les demandes pour la réponse
            if ($user['role'] == 'Administrateur') {
                $formattedDemandes = collect($demandes)->filter(function ($demande) {
                    return isset($demande['fields']['role (from Employe)']) &&
                        in_array('Manager', $demande['fields']['role (from Employe)']);
                })->map(function ($demande) {
                    $nom = $demande['fields']['nom (from Employe)'][0] ?? '';
                    $prenom = $demande['fields']['prenom (from Employe)'][0] ?? '';
                    $demandeur = trim($nom . ' ' . $prenom);

                    return [
                        'Id' => $demande['id'] ?? null,
                        'Titre' => $demande['fields']['Titre (from Raison)'][0] ?? null,
                        'Demandeur' => $demandeur,
                        'Description' => $demande['fields']['Description'] ?? null,
                        'Status' => $demande['fields']['Status'] ?? null,
                        'Date' => $demande['fields']['DateDemande'] ?? null,
                        'DateReponse' => $demande['fields']['DateReponse'] ?? null,
                        'Motif' => $demande['fields']['Motif'] ?? null,
                        'Justificatifs' => $demande['fields']['Justificatifs'] ?? null,
                        'TitreJustificatifs' => $demande['fields']['TitreJustificatif'] ?? null
                    ];
                })->values()->all();
            } else {
                $formattedDemandes = collect($demandes)->filter(function ($demande) {
                    return isset($demande['fields']['role (from Employe)']) &&
                        in_array('Salarie', $demande['fields']['role (from Employe)']);
                })->map(function ($demande) {
                    $nom = $demande['fields']['nom (from Employe)'][0] ?? '';
                    $prenom = $demande['fields']['prenom (from Employe)'][0] ?? '';
                    $demandeur = trim($nom . ' ' . $prenom);

                    return [
                        'Id' => $demande['id'] ?? null,
                        'Titre' => $demande['fields']['Titre (from Raison)'][0] ?? null,
                        'Demandeur' => $demandeur,
                        'Description' => $demande['fields']['Description'] ?? null,
                        'Status' => $demande['fields']['Status'] ?? null,
                        'Date' => $demande['fields']['DateDemande'] ?? null,
                        'DateReponse' => $demande['fields']['DateReponse'] ?? null,
                        'Motif' => $demande['fields']['Motif'] ?? null,
                        'Justificatifs' => $demande['fields']['Justificatifs'] ?? null,
                        'TitreJustificatifs' => $demande['fields']['TitreJustificatif'] ?? null
                    ];
                })->values()->all();
            }


            info($formattedDemandes);


            return response()->json($formattedDemandes, 200);
        } catch (\Exception $e) {
            Log::error('Error fetching demandes to be validated: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while fetching demandes to be validated'], 500);
        }
    }

    private function generateFormulaForIds(array $ids)
    {
        return implode(',', array_map(fn($id) => "RECORD_ID() = '$id'", $ids));
    }


    // accepter une demande et envoyer un mail
    public function accepter($requestId, Request $request)
    {
        try {
            $userId = TokenHelper::getUserIdFromToken($request)[0]['id'];
            $message = $request->input('message');
            $files = $request->file();
            $currentDate = Carbon::now()->format('n/j/Y');

            $publicPaths = [];
            $filenames = [];

            foreach ($files as $file) {
                $filename = time() . '_' . $file->getClientOriginalName();
                $fileStream = fopen($file->getPathname(), 'r');

                $userFolderPath = "users/{$userId}/responses/";
                $this->createUserFolderIfNotExists($userFolderPath);

                $filePath = $userFolderPath . $filename;
                $object = $this->bucket->upload($fileStream, [
                    'name' => $filePath,
                ]);

                $publicPaths[] = sprintf('https://storage.googleapis.com/%s/%s', env('GOOGLE_CLOUD_STORAGE_BUCKET'), $filePath);
                $filenames[] = $file->getClientOriginalName();
            }

            $updatedFields = [
                'Motif' => $message,
                'Status' => 'Approuvée',
                'DateReponse' => $currentDate,
                'AttachementResponse' => implode(',', $publicPaths),
                'TitreAttachementResponse' => implode(',', $filenames),
            ];

            $result = AirtableFacade::table('Demande')->patch($requestId, $updatedFields);

            ActivityLogController::logActivity($userId, 'Acceptation', 'Demande "' . $requestId . '" acceptée');

            // Récupérer les informations de l'utilisateur pour envoyer un e-mail
            $user = AirtableFacade::table('Employee')->find($result['fields']['Employe'][0]);

            $email = $user['fields']['email'] ?? null;

            if ($email) {
                Mail::to($email)->send(new DemandeAcceptee($user, $message, $publicPaths));
            }

            return response()->json([
                'message' => 'Demande approuvée avec succès.',
                'data' => $result
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error approving request: ' . $e->getMessage());
            return response()->json([
                'message' => 'Une erreur s\'est produite lors de l\'approbation de la demande.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // refuser une demande
    public function refuser($requestId, Request $request)
    {
        try {
            $userId = TokenHelper::getUserIdFromToken($request)[0]['id'];
            $message = $request->input('message');

            $currentDate = Carbon::now()->format('n/j/Y');


            $updatedFields = [
                'Motif' => $message,
                'Status' => 'Refusée',
                'DateReponse' => $currentDate,
            ];

            $result = AirtableFacade::table('Demande')->patch($requestId, $updatedFields);

            ActivityLogController::logActivity($userId, 'Refus', 'Demande "' . $requestId . '" refusée');

            // Récupérer les informations de l'utilisateur pour envoyer un e-mail
            $user = AirtableFacade::table('Employee')->find($result['fields']['Employe'][0]);

            $email = $user['fields']['email'] ?? null;


            if ($email) {
                // Mail::to($email)->send(new DemandeRefusee($user, $message));
            }


            return response()->json([
                'message' => 'Demande refusée avec succès.',
                'data' => $result
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error rejecting request: ' . $e->getMessage());
            return response()->json([
                'message' => 'Une erreur s\'est produite lors du rejet de la demande.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
