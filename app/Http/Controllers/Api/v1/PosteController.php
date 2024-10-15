<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Tapp\Airtable\Facades\AirtableFacade;
use Carbon\Carbon;
use App\Helpers\TokenHelper; // Assurez-vous que TokenHelper est bien importé

class PosteController extends Controller
{
    public function createPoste(Request $request)
    {
        try {
            $userId = TokenHelper::getUserIdFromToken($request)[0]['id']; // Récupération de l'ID utilisateur
            $currentDate = Carbon::now()->format('n/j/Y');

            $data = [
                'Name' => $request->input('name'),
                'isArchived' => 0,
                'create_at' => $currentDate,
                'created_by' => $userId,
                'Employee' => $request->input('employee_ids')
            ];

            $result = AirtableFacade::table('Poste')->create($data);

            return response()->json(['message' => 'Poste créé avec succès', 'data' => $result], 201);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la création du poste: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la création du poste'], 500);
        }
    }

    public function updatePoste($id, Request $request)
    {
        try {
            $userId = TokenHelper::getUserIdFromToken($request)[0]['id']; // Récupération de l'ID utilisateur

            $updatedFields = [
                'Name' => $request->input('name'),
                'Employee' => $request->input('employee_ids'),
            ];

            $result = AirtableFacade::table('Poste')->patch($id, $updatedFields);

            return response()->json(['message' => 'Poste mis à jour avec succès', 'data' => $result], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour du poste: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la mise à jour du poste'], 500);
        }
    }

    public function archivePoste($id, Request $request)
    {
        try {
            $userId = TokenHelper::getUserIdFromToken($request)[0]['id']; // Récupération de l'ID utilisateur

            $updatedFields = ['isArchived' => 1];
            $result = AirtableFacade::table('Poste')->patch($id, $updatedFields);

            return response()->json(['message' => 'Poste archivé avec succès', 'data' => $result], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'archivage du poste: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de l\'archivage du poste'], 500);
        }
    }

    public function unarchivePoste($id, Request $request)
    {
        try {
            $userId = TokenHelper::getUserIdFromToken($request)[0]['id']; // Récupération de l'ID utilisateur

            $updatedFields = ['isArchived' => 0];
            $result = AirtableFacade::table('Poste')->patch($id, $updatedFields);

            return response()->json(['message' => 'Poste désarchivé avec succès', 'data' => $result], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors du désarchivage du poste: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors du désarchivage du poste'], 500);
        }
    }

    public function getPostes(Request $request)
    {
        try {
            $postes = AirtableFacade::table('Poste')->get();

            $formattedPostes = collect($postes)->map(function ($poste) {
                return [
                    'id' => $poste['id'],
                    'name' => $poste['fields']['Name'],
                    'isArchived' => $poste['fields']['isArchived'] ?? 0,
                    'employees' => $poste['fields']['Employee'] ?? [],
                ];
            })->values()->all();

            return response()->json($formattedPostes, 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des postes: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la récupération des postes'], 500);
        }
    }
}
