<?php

namespace App\Http\Controllers\Api\v1;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Tapp\Airtable\Facades\AirtableFacade;

class TeamController extends Controller
{

    public function createTeam(Request $request)
    {
        try {

            info($request);

            $teamName = $request->name;
            $members = collect($request->members)->pluck('id')->toArray();

            // Vérifier si une équipe avec le même nom existe déjà
            $existingTeams = AirtableFacade::table('Equipe')->where('Nom', $teamName)->get();

            if (count($existingTeams) > 0) {
                return response()->json(['message' => 'Une équipe avec ce nom existe déjà.'], 409);
            }

            // Créer l'équipe
            $result = AirtableFacade::table('Equipe')->create([
                'Nom' => $teamName,
                'Employee' => $members, // IDs des utilisateurs
            ]);

            return response()->json(['message' => 'Équipe créée avec succès', 'team' => $result], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la création de l\'équipe', ['exception' => $e]);
            return response()->json(['error' => 'Erreur lors de la création de l\'équipe'], 500);
        }
    }

    public function updateTeam(Request $request, $name)
    {
        try {
            // Récupérer l'équipe existante par l'ancien nom
            $team = AirtableFacade::table('Equipe')->where('Nom', $name)->get();

            if (count($team) === 0) {
                return response()->json(['message' => 'Équipe non trouvée.'], 404);
            }

            $teamId = $team[0]['id'];

            // Nouveau nom de l'équipe
            $newTeamName = $request->name;
            $members = collect($request->members)->pluck('id')->toArray();

            // Mettre à jour l'équipe dans Airtable
            $result = AirtableFacade::table('Equipe')->update($teamId, [
                'Nom' => $newTeamName,
                'Employee' => $members,
            ]);

            return response()->json(['message' => 'Équipe mise à jour avec succès', 'team' => $result], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour de l\'équipe', ['exception' => $e]);
            return response()->json(['error' => 'Erreur lors de la mise à jour de l\'équipe'], 500);
        }
    }



    public function deleteTeam($name)
    {
        try {
            $team = (AirtableFacade::table('Equipe')->where('Nom', $name)->get())[0];

            if (!$team['id']) {
                return response()->json(['error' => 'Équipe non trouvée.'], 404);
            }

            $result = AirtableFacade::table('Equipe')->destroy($team['id']);

            info($team);

            if ($result) {
                return response()->json(['message' => 'Équipe supprimée avec succès.'], 200);
            } else {
                return response()->json(['error' => 'Échec de la suppression de l\'équipe.'], 500);
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression de l\'équipe', ['exception' => $e]);
            return response()->json(['error' => 'Erreur lors de la suppression de l\'équipe'], 500);
        }
    }

    public function getTeams()
    {
        try {
            $teams = AirtableFacade::table('Equipe')->get();

            $formattedTeams = [];

            foreach ($teams as $team) {
                $formattedTeams[] = [
                    'id' => $team['id'],
                    'name' => $team['fields']['Nom'],
                    'members' => $team['fields']['Employee'] ?? [], // IDs des utilisateurs
                ];
            }

            return response()->json(['teams' => $formattedTeams], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des équipes', ['exception' => $e]);
            return response()->json(['error' => 'Erreur lors de la récupération des équipes'], 500);
        }
    }


    public function getTeamMembers(Request $request)
    {
        try {
            // Récupérer les informations de l'utilisateur actuel à partir du token
            $tokenHash = hash('sha256', $request->token);
            $tokenInfo = AirtableFacade::table('token_table')->where('token', $tokenHash)->get();

            if (!$tokenInfo[0]) {
                return response()->json([
                    'message' => 'Token not found',
                ], 404);
            }

            $user_id = ($tokenInfo[0]['fields'])['userid'];
            $reponse = AirtableFacade::table('Employee')->where('id', $user_id)->get();
            $currentUser = isset($reponse[0]['fields']) ? $reponse[0]['fields'] : null;

            if (!$currentUser) {
                return response()->json([
                    'message' => 'User not found',
                ], 404);
            }

            // Vérifier le isAdmin de l'utilisateur
            $isAdmin = $currentUser['isAdmin'];
            $teamId = $currentUser['Equipe'][0];


            if ($isAdmin == 0) {
                // Si l'utilisateur est un salarié, récupérer les membres de son équipe
                $equipe = AirtableFacade::table('Equipe')
                    ->filterByFormula("RECORD_ID() = '{$teamId}'")
                    ->get();

                if (!$equipe || !isset($equipe[0]['fields']['Employee'])) {
                    return response()->json([
                        'message' => 'No members found for this team',
                    ], 404);
                }
                $employeeIds = $equipe[0]['fields']['Employee'];
                $teamMembers = [];
                foreach ($employeeIds as $employeeId) {
                    $employee = AirtableFacade::table('Employee')->find($employeeId);
                    if ($employee && $employee['fields']['isAdmin'] == 0) {
                        $teamMembers[] = $employee;
                    }
                }

                info($employeeIds);
            } else if ($isAdmin == 1) {
                // Si l'utilisateur est un admin, récupérer tous les salariés
                $teamMembers = AirtableFacade::table('Employee')
                    ->filterByFormula("isAdmin = 0")
                    ->get();
            } else {
                return response()->json([
                    'message' => 'Invalid user level priority',
                ], 400);
            }

            return response()->json([
                'teamMembers' => $teamMembers
            ], 200);
        } catch (\Exception $e) {
            info('Error fetching team members:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'An error occurred while fetching team members',
            ], 500);
        }
    }


    
    public function toggleTeamArchiveStatus(Request $request, $teamName)
    {
        try {
            $team = (AirtableFacade::table('Equipe')->where('Nom', $teamName)->get())[0];

            if (!$team['id']) {
                return response()->json(['error' => 'Équipe non trouvée.'], 404);
            }

            $updatedData = [
                'isArchived' => $request->isArchived,
            ];

            $result = AirtableFacade::table('Employee')->patch($team['id'], $updatedData);

            return response()->json(['message' => 'Statut archivé mis à jour avec succès', 'user' => $result], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour du statut', ['exception' => $e]);
            return response()->json(['error' => 'Erreur lors de la mise à jour du statut archivé'], 500);
        }
    }
}
