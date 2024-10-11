<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\AirTable\AirtableModel;
use App\Models\AirTable\AirtableReponse;
use App\Models\AirTable\AirtableTrait;
use Tapp\Airtable\Facades\AirtableFacade;
use Illuminate\Support\Facades\Log;
use App\Helpers\TokenHelper;

class FolderController extends Controller
{
    use  AirtableTrait, AirtableReponse;


    public function createFolder(Request $request)
    {
        $userId = TokenHelper::getUserIdFromToken($request);
        $employeeId = $userId[0]["id"];

        // info($request);

        // Créer un nouveau dossier
        try {

            $existingFolder = AirtableFacade::table('Folders')->where('Name', $request->Name)->get();

            info($existingFolder);
            info($employeeId);

            if (count($existingFolder) > 0 && $existingFolder[0]['fields']['Creator'][0] == $employeeId) {
                return response()->json(['message' => 'Erreur, Dossier existant'], 409);
            }

            $response = AirtableFacade::table('Folders')->create([
                'Name' => $request->Name,
                'Creator' => [$employeeId],
            ]);

            info($response);

            return response()->json(['message' => 'Le nouveau dossier a été créé avec succès.'], 200);
        } catch (\Exception $e) {
            return response()->json(
                ['message' => 'Erreur lors de la création du nouveau dossier.'],
                500
            );
        }
    }

    public function renameFolder($folderName, Request $request)
    {
        try {
            // Récupérer l'utilisateur connecté et vérifier s'il est admin
            $userId = TokenHelper::getUserIdFromToken($request);
            $employeeId = $userId[0]['id'];
            $employee = AirtableFacade::table('Employee')->find($employeeId);
            $isAdmin = $employee['fields']['isAdmin'] == 1;

            // Récupérer le dossier à renommer
            $folder = AirtableFacade::table('Folders')->where('Name', $folderName)->get();

            // Vérifier si le dossier existe
            if ($folder->isEmpty()) {
                return response()->json(['message' => 'Dossier non trouvé'], 404);
            }

            $folderId = $folder[0]['id'];
            $creatorId = $folder[0]['fields']['Creator'][0]; // ID du créateur du dossier

            // Récupérer les informations du créateur du dossier
            $creator = AirtableFacade::table('Employee')->find($creatorId);
            $creatorIsAdmin = isset($creator['fields']['isAdmin']) && $creator['fields']['isAdmin'] == 1;

            // Vérifier les permissions
            if (!$isAdmin && $creatorIsAdmin) {
                return response()->json(['message' => 'Vous ne pouvez pas renommer ce dossier créé par un administrateur.'], 403);
            }

            // Mise à jour du nom du dossier
            $newFolderName = $request->input('name');
            if (empty($newFolderName) || trim($newFolderName) === '') {
                return response()->json(['message' => 'Le nouveau nom de dossier ne peut pas être vide.'], 400);
            }

            $updatedData = [
                'Name' => $request->input('name')
            ];

            // Utilisation de la méthode `patch` pour mettre à jour le nom du dossier
            $result = AirtableFacade::table('Folders')->patch($folderId, $updatedData);

            return response()->json(['message' => 'Dossier renommé avec succès.', 'folder' => $result], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors du renommage du dossier : ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors du renommage du dossier.'], 500);
        }
    }


    //destroy Folder
    /* 
    public function deleteFolder($folderName, Request $request)
    {

        $userId = TokenHelper::getUserIdFromToken($request);
        $employeeId = $userId[0]['id'];
        $employee = AirtableFacade::table('Employee')->find($employeeId);
        $isAdmin = $employee['fields']['isAdmin'] == 1;

        // Récupérer le dossier par son nom
        $folder = AirtableFacade::table('Folders')->where('Name', $folderName)->get();

        // Vérifier si le dossier existe
        if ($folder->isEmpty()) {
            return response()->json(['message' => 'Dossier non trouvé'], 404);
        }

        $creatorId = $folder[0]['fields']['Creator'][0]; // ID du créateur du dossier
        $documents = $folder[0]['fields']['Documents'] ?? [];

        // Récupérer les informations du créateur du dossier
        $creator = AirtableFacade::table('Employee')->find($creatorId);
        $creatorIsAdmin = isset($creator['fields']['isAdmin']) && $creator['fields']['isAdmin'] == 1;

        // Vérifier les permissions pour la suppression
        if ($creatorIsAdmin && !$isAdmin) {
            return response()->json(['message' => 'Privilèges insuffisants.'], 403);
        }

        // Si le créateur n'est pas un administrateur, supprimer tous les documents à l'intérieur
        foreach ($documents as $documentId) {
            try {
                AirtableFacade::table('Documents')->destroy($documentId);
            } catch (\Exception $e) {
                Log::error("Erreur lors de la suppression du document {$documentId} : " . $e->getMessage());
                return response()->json(['message' => 'Erreur lors de la suppression des documents.'], 500);
            }
        }

        // Supprimer le dossier
        try {
            $response = AirtableFacade::table('Folders')->destroy($folder[0]['id']);
            info($response);
            return response()->json(['message' => 'Dossier supprimé avec succès.'], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression du dossier : ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la suppression du dossier.'], 500);
        }

        return response()->json(['message' => 'Dossier supprimé avec succès'], 200);
    } 
    */

    public function deleteFolder($folderName, Request $request)
    {
        // Récupérer le dossier par son nom
        $folder = (AirtableFacade::table('Folders')->where('Name', $folderName)->get())[0];
        info($folder);

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

    public function getFolder(Request $request)
    {
        $data = $request->all();
        $response = AirtableFacade::table('Folder')->where('id', $data['id'])->get();
        return response()->json(['message' => 'Folder retrieved', 'data' => $response], 200);
    }
}
