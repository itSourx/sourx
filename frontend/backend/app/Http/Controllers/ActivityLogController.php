<?php

namespace App\Http\Controllers;

use Tapp\Airtable\Facades\AirtableFacade;
use Illuminate\Support\Facades\Log;
use App\Helpers\TokenHelper;
use Illuminate\Http\Request;
use Carbon\Carbon;


class ActivityLogController extends Controller
{
    public static function logActivity($userId, $activityType, $description)
    {
        try {
            $result = AirtableFacade::table('ActivityLog')->create([
                'Description' => $description,
                'User_id' => [$userId],
                'Activity_type' => $activityType
            ]);

            Log::info('Activity logged successfully', ['result' => $result]);
        } catch (\Exception $e) {
            Log::error('Error logging activity: ' . $e->getMessage());
        }
    }

    public function getRecentActivities(Request $request)
    {
        try {

            $userId = TokenHelper::getUserIdFromToken($request);
            $employeeId = $userId[0]["id"];

            $employee = AirtableFacade::table('Employee')->find($employeeId);
            $activityIds = $employee['fields']['ActivityLog'] ?? [];

            if (empty($activityIds)) {
                // Retourner une réponse vide si aucune activité n'est trouvée
                return response()->json([
                    'activities' => []
                ]);
            }

            // Initialiser un tableau pour stocker les détails des activités
            $activitiesDetails = [];
            // Limiter les IDs aux 6 derniers
            $recentActivityIds = array_slice($activityIds, -10);

            // Récupérer les détails de chaque activité en utilisant les IDs
            foreach ($recentActivityIds as $activityId) {
                try {
                    $activity = AirtableFacade::table('ActivityLog')->find($activityId);

                    if ($activity && isset($activity['fields'])) {
                        $activitiesDetails[] = [
                            'description' => $activity['fields']['Description'] ?? 'N/A',
                            'activity_type' => $activity['fields']['Activity_type'] ?? 'N/A',
                            'activity_date' => $activity['fields']['Activity_date'] ?? 'N/A'
                        ];
                    }
                } catch (\Exception $e) {
                    // Log or handle errors for individual activity fetches if necessary
                    info('Failed to fetch activity details for ID: ' . $activityId . '. Error: ' . $e->getMessage());
                }
            }

            // Retourner les détails des activités
            return response()->json($activitiesDetails);
        } catch (\Exception $e) {
            // Gérer les exceptions éventuelles
            return response()->json([
                'error' => 'Failed to retrieve recent activities',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
