<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FilesController;
use Illuminate\Http\Request;
use Tapp\Airtable\Facades\AirtableFacade;
use App\Models\AirTable\AirtableUser;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\ActivityLogController;
use Google\Cloud\Storage\StorageClient;
use App\Helpers\TokenHelper;
use PhpParser\Node\Stmt\TryCatch;

class DocumentController extends Controller
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

    // Méthode pour créer le dossier utilisateur s'il n'existe pas
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

    public function getFolderIdByName($folderName)
    {
        try {
            $folders = AirtableFacade::table('Folders')->where('Name', $folderName)->get();

            if ($folders->isEmpty()) {
                return response()->json(['message' => 'Folder not found'], 404);
            }

            $folderId = $folders[0]['id'];

            return $folderId;
        } catch (\Exception $e) {
            Log::error('Error fetching folder by name: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return;
        }
    }


    public function sendDocumentByUser(Request $request)
    {

        info($request);
        
        
        $creatorId = TokenHelper::getUserIdFromToken($request)[0]['id'];
        $data = $request->all();

        try {
            if (empty($data['destinationFolder'])) {
                $folderId = $this->getFolderIdByName('Autres documents');
                /* array_push($data['selectedUsers'], $creatorId); */
            } else {
                $folderId = $this->getFolderIdByName($data['destinationFolder']);
            }

            info("FOLDER ID");
            info($data['selectedUsers']);

            if (!$folderId) {
                return response()->json(['message' => 'Dossier non trouvé'], 404);
            }

            // Vérifier si des fichiers sont présents dans la requête
            $filesToUpload = $request->file('filesToUpload');

            // Si aucun fichier n'est fourni, créer un document avec une URI vide
            if (!$filesToUpload || !is_array($filesToUpload) || empty($filesToUpload)) {
                $documentData = [
                    'Titre' => 'Document sans fichier', // Ou utilisez un titre par défaut ou spécifique
                    'CreateBy' => [$creatorId],
                    'SharedWithPeople' => $data['selectedUsers'],
                    'Folder' => [$folderId],
                    'uri' => '', // URI vide
                ];

                AirtableFacade::table('Documents')->create($documentData);
                ActivityLogController::logActivity($creatorId, 'Ajout/Creation', 'Nouveau document sans fichier ajouté');
            } else {

                foreach ($request->file('filesToUpload') as $file) {
                    try {
                        $userFolderPath = "users/{$creatorId}/documents/";
                        $this->createUserFolderIfNotExists($userFolderPath);

                        $fileName = time() . '_' . $file->getClientOriginalName();
                        $filePath = $userFolderPath . $fileName;
                        $fileStream = fopen($file->getPathname(), 'r');

                        // Upload du fichier sur Google Cloud Storage
                        $object = $this->bucket->upload($fileStream, [
                            'name' => $filePath,
                        ]);

                        $publicPath = sprintf('https://storage.googleapis.com/%s/%s', env('GOOGLE_CLOUD_STORAGE_BUCKET'), $filePath);

                        $documentData = [
                            'Titre' => $file->getClientOriginalName(),
                            'CreateBy' => [$creatorId],
                            'SharedWithPeople' => $data['selectedUsers'],
                            'Folder' => [$folderId],
                            'uri' => $publicPath,
                        ];

                        $result = AirtableFacade::table('Documents')->create($documentData);
                        info($result);
                        ActivityLogController::logActivity($creatorId, 'Ajout/Creation', 'Nouveau document "' . $documentData['Titre'] . '" ajouté');
                    } catch (\Exception $e) {
                        Log::error('Erreur lors de la préparation ou de l\'insertion du document : ' . $e->getMessage());
                        return response()->json(['message' => 'Erreur lors de la préparation ou de l\'insertion du document.'], 500);
                    }
                }

                return response()->json(['message' => 'Téléchargement terminé'], 200);
            }
        } catch (\Exception $e) {
            info("Erreur générale : " . $e->getMessage());
            return response()->json(['message' => 'Échec de l\'envoi des documents.', 'details' => $e->getMessage()], 500);
        }
    }

    public function deleteFolder($folderName, Request $request)
    {
        // Récupérer le dossier par son nom
        $folder = (AirtableFacade::table('Folders')->where('Name', $folderName)->get())[0];
        if ($folder->isEmpty()) {
            return response()->json(['message' => 'Dossier non trouvé'], 404);
        }

        $updatedData = [
            'isArchived' => 1,
        ];

        // Utiliser la méthode patch pour mettre à jour le document
        AirtableFacade::table('Documents')->patch($folder['id'], $updatedData);

        $documents = $folder['fields']['Documents'] ?? [];

        // Si le créateur n'est pas un administrateur, supprimer tous les documents à l'intérieur
        foreach ($documents as $documentId) {
            try {
                AirtableFacade::table('Documents')->patch($documentId, $updatedData);
            } catch (\Exception $e) {
                Log::error("Erreur lors de l'archivage du document {$documentId} : " . $e->getMessage());
                return response()->json(['message' => 'Erreur lors de la suppression des documents.'], 500);
            }
        }

        return response()->json(['message' => 'Dossier supprimé avec succès'], 200);
    }
    //  delete document 
    public function deleteDocument(Request $request)
    {
        $data = $request->all();
        $updatedData = [
            'isArchived' => 1,
        ];
        try {
            $result = AirtableFacade::table('Documents')->patch($data['id'], $updatedData);
            info($result);
            return response()->json(['message' => 'Document archivé avec succès'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Echec de l\'archivage document. '], 500);
        }
    }
    /* public function deleteDocument(Request $request)
    {
        $data = $request->all();
        try {
            $result = AirtableFacade::table("Documents")->destroy($data['id']);
            info($result);
            return response()->json(['message' => 'Document supprimé avec succès'], 200);
        } catch (\Exception $e) {
            // Gérer les exceptions éventuelles
            return response()->json([
                'error' => 'Echec de suppressiondu document : ',
                'details' => $e->getMessage()
            ], 500);
        }
    } */

    private function createSharedPublicFolderIfNotExists($folderPath)
    {
        // Vérifier si le dossier existe
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


    // Send document by Admin or Manager

    public function sendDocumentByAdmin(Request $request)
    {
        $adminId = TokenHelper::getUserIdFromToken($request)[0]["id"];
        $data = $request->all();

        try {

            $folderId = $this->getFolderIdByName("Autres documents");
            if (!$folderId) {
                return response()->json(['message' => 'Dossier non trouvé'], 404);
            }

            $selectedUsers = json_decode($data['selectedUsers'], true);
            if (empty($selectedUsers)) {
                return response()->json(['message' => 'Aucun utilisateur sélectionné.'], 400);
            }

            array_push($selectedUsers, $adminId);

            foreach ($request->file('filesToUpload') as $file) {
                $fileName = time() . '_' . $file->getClientOriginalName();
                try {

                    $publicFolderPath = "shared/public/";
                    $this->createSharedPublicFolderIfNotExists($publicFolderPath);

                    $filePath = $publicFolderPath . $fileName;
                    $fileStream = fopen($file->getPathname(), 'r');

                    $object = $this->bucket->upload($fileStream, [
                        'name' => $filePath,
                    ]);

                    $publicPath = sprintf('https://storage.googleapis.com/%s/%s', env('GOOGLE_CLOUD_STORAGE_BUCKET'), $filePath);

                    $documentData = [
                        'Titre' => $file->getClientOriginalName(),
                        'CreateBy' => [$adminId],
                        'SharedWithPeople' => $selectedUsers,
                        'Folder' => [$folderId],
                        'uri' => $publicPath,
                    ];

                    AirtableFacade::table('Documents')->create($documentData);
                    ActivityLogController::logActivity($adminId, 'Ajout/Creation', 'Document "' . $documentData['Titre'] . '" ajouté par l\'administrateur');
                } catch (\Exception $e) {
                    Log::error('Erreur lors du téléchargement ou de l\'insertion du document : ' . $fileName, [
                        'error' => $e->getMessage()
                    ]);
                    return response()->json(['message' => 'Erreur lors de l\'envoi du fichier : ' . $fileName], 500);
                }
            }


            return response()->json(['message' => 'Téléchargement terminé'], 200);
        } catch (\Exception $e) {
            Log::error('Erreur générale lors de l\'envoi des documents par l\'administrateur : ' . $e->getMessage());
            return response()->json(['message' => 'Échec de l\'envoi des documents.', 'details' => $e->getMessage()], 500);
        }
    }


    public function getDocumentByUser(Request $request)
    {
        // Récupérer le token depuis l'en-tête Authorization
        $authorizationHeader = $request->header('Authorization');

        if (!$authorizationHeader) {
            return response()->json(['message' => 'Authorization header not found'], 401);
        }

        $token = str_replace('Bearer ', '', $authorizationHeader);
        $tokenHash = hash('sha256', $token);
        $tokenInfo = (AirtableFacade::table('token_table')->where('token', $tokenHash)->get())[0];

        $employeeId = $tokenInfo['fields']['userid'];
        $documentsOfUser = $this->fetchDocumentsOfUser($employeeId);

        $userFolders = AirtableFacade::table('Folders')->where('id (from Creator)', $employeeId)->get();
        $adminFolders = AirtableFacade::table('Folders')->where('role (from Creator)', 'Administrateur')->get();
        $allFolders = $userFolders->merge($adminFolders);

        info($allFolders);

        $filteredFolders = $allFolders->filter(function ($folder) {
            return isset($folder['fields']['isArchived']) && $folder['fields']['isArchived'] == 0;
        })->values();

        info($filteredFolders);

        $formattedFolders = $filteredFolders->map(function ($folder) {
            return [
                'id' => $folder['id'],
                'name' => $folder['fields']['Name'],
                'creatorId' => $folder['fields']['Creator'][0],
                'creatorName' => ($folder['fields']['nom (from Creator)'][0] . ' ' . $folder['fields']['prenom (from Creator)'][0]),
                'creatorRole' => $folder['fields']['role (from Creator)'][0],
            ];
        })->toArray();

        info("DOCUMENTS OF USER");
        info($documentsOfUser);
        info("FOLDERS OF USER");
        info($formattedFolders);

        return response()->json([
            'documents' => $documentsOfUser,
            'folders' => $formattedFolders,
        ], 200);
    }


    private function fetchDocumentsOfUser($employeeId)
    {
        try {
            $employeeData = (AirtableFacade::table('Employee')->where('id', $employeeId)->get())[0];

            $employeeFields = $employeeData['fields'];
            $airtable_id = $employeeData['id'];
            $allDocumentsId = $employeeFields['Documents'] ?? [];

            // $allDocumentsId = array_merge($documents);


            // Créer la formule pour Airtable filterByFormula
            $formula = 'OR(' . implode(',', array_map(fn($id) => "RECORD_ID() = '$id'", $allDocumentsId)) . ')';
            $documentsData = AirtableFacade::table('Documents')->filterByFormula($formula)->get();

            $filteredDocuments = $documentsData->filter(function ($document) {
                return isset($document['fields']['isArchived']) && $document['fields']['isArchived'] == 0 && $document['fields']['isArchived (from Folder)'][0] == 0;
            });
            info($documentsData);
            info("Fiktered");
            info($filteredDocuments);

            // Formater les données des documents
            $mappedDocuments = $filteredDocuments->map(function ($document) use ($airtable_id) {
                $uri = $document['fields']['uri'];
                $parsedUrl = parse_url($uri);
                $fileName = basename($parsedUrl['path']);
    
                $userFolderPath = "users/{$document['fields']['CreateBy'][0]}/documents/{$fileName}";
                $sharedFolderPath = "shared/public/{$fileName}";
    
                $object = $this->bucket->object($userFolderPath);
                if (!$object->exists()) {
                    $object = $this->bucket->object($sharedFolderPath);
                    if (!$object->exists()) {
                        Log::error("No such object found in both paths: $userFolderPath or $sharedFolderPath");
                        // Skip this document if not found in bucket
                        return null;
                    }
                }
    
                $signedUrl = $object->signedUrl(new \DateTime('+1 hour'));
                $fileSize = $object->info()['size'] ?? 'N/A'; // Récupérer la taille en octets
    
                return [
                    'id' => $document['id'],
                    'createdTime' => $document['createdTime'],
                    'creatorName' => $document['fields']['nom (from CreateBy)'][0] . ' ' . $document['fields']['prenom (from CreateBy)'][0],
                    'creatorPhoto' => null,
                    'title' => $document['fields']['Titre'],
                    'folder' => [
                        'name' => $document['fields']['Name (from Folder)'][0],
                    ],
                    'folderCreator' => $document['fields']['nom (from Creator) (from Folder)'][0] . ' ' . $document['fields']['prenom (from Creator) (from Folder)'][0],
                    'url' => $signedUrl,
                    'size' => round($fileSize / 1024 / 1024, 2) . ' MB',
                ];
            })->toArray();
    
            // Remove null values (documents not found in bucket)
            return array_values(array_filter($mappedDocuments));
            
        } catch (\Exception $e) {
            Log::error('Error fetching documents by user: ' . $e->getMessage());
            return [];
        }
    }


    public function moveDocument($documentId, $folderName, Request $request)
    {
        try {
            info("--1 -");
            // Récupérer l'utilisateur connecté et vérifier s'il est admin
            $userId = TokenHelper::getUserIdFromToken($request);
            info($userId);
            $employeeId = $userId[0]['id'];
            $employee = AirtableFacade::table('Employee')->find($employeeId);
            $isAdmin = $employee['fields']['isAdmin'] == 1;

            // Récupérer le dossier de destination par son nom
            $destinationFolder = AirtableFacade::table('Folders')->where('Name', $folderName)->get();

            // Vérifier si le dossier de destination existe
            if ($destinationFolder->isEmpty()) {
                return response()->json(['message' => 'Dossier de destination non trouvé.'], 404);
            }

            $destinationFolderId = $destinationFolder[0]['id'];
            $creatorId = $destinationFolder[0]['fields']['Creator'][0];
            $creator = AirtableFacade::table('Employee')->find($creatorId);
            $creatorIsAdmin = isset($creator['fields']['isAdmin']) && $creator['fields']['isAdmin'] == 1;

            info($creatorIsAdmin);

            // Vérifier les permissions pour déplacer vers un dossier créé par un admin
            if ($creatorIsAdmin && !$isAdmin) {
                info("PRIVILEGES");
                return response()->json(['message' => 'Privilèges insuffisants.'], 403);
            }

            // Récupérer le document par son ID
            $document = AirtableFacade::table('Documents')->find($documentId);

            info($document);

            // Vérifier si le document existe
            if (!$document) {
                return response()->json(['message' => 'Document non trouvé.'], 404);
            }

            // Mettre à jour le champ du dossier du document
            $updatedData = [
                'Folder' => [$destinationFolderId],
            ];

            // Utiliser la méthode patch pour mettre à jour le document
            $result = AirtableFacade::table('Documents')->patch($documentId, $updatedData);

            info($result);

            return response()->json(['message' => 'Document déplacé avec succès.', 'document' => $result], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors du déplacement du document : ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors du déplacement du document.'], 500);
        }
    }




    public function getAllDocuments()
    {
        try {

            $reponse = AirtableFacade::table('Documents')->get();
            return $reponse;
            info('Response from Airtable:', ['response' => $reponse]);

            return response()->json(['documents' => $reponse], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching all documents: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while fetching documents'], 500);
        }
    }

    // Employee Authentecated
    public static function getAuthUser(Request $request): AirtableUser
    {

        $token = $request->token;
        $tokenHash = hash('sha256', $token);
        $tokenInfo = AirtableFacade::table('token_table')->where('token', $tokenHash)->get();
        if (!$tokenInfo->isEmpty()) {
            $employeeId = $tokenInfo[0]['fields']['userid'];
            try {
                $data = AirtableFacade::table('Employee')->where('id', $employeeId)->get();
                $employee = new AirtableUser([
                    'id' => $data[0]['fields']['id'],
                    'nom' => $data[0]['fields']['nom'],
                    'prenom' => $data[0]['fields']['prenom'],
                    'email' => $data[0]['fields']['email'],
                    'telephone' => $data[0]['fields']['telephone']
                ]);
                return $employee;
            } catch (\Exception $e) {
                Log::error('Error fetching documents by employee ID: ' . $e->getMessage());
                return response()->json(['message' => 'An error occurred while fetching documents'], 500);
            }
        } else {
            return response()->json(['message' => 'Token not found'], 404);
        }
    }
}
