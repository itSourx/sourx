<?php

namespace App\Http\Controllers\Api\v1;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Tapp\Airtable\Facades\AirtableFacade;



// Demande de permission pour un congé ou pour une autorisation de sortie
// moditf de demande de permission pour un congé ou pour une autorisation 
// supression de demande de permission pour un congé ou pour  autre autorisation 
// liste de demande de permission pour un congé ou pour une autorisation

class UserController extends Controller
{
   //   Update user from Airtable Employee
    public function updateUser(Request $request)
    {
        try {
            info('Update user ... :', [
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'email' => $request->email,
                'telephone' => $request->telephone,
            ]);

            $result = AirtableFacade::table('Employee')->update($request->id, [
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'email' => $request->email,
                'telephone' => $request->telephone,
                
            ]);
            return response()->json(['message' => 'User updated'], 200);
        } catch (\Exception $e) {
            info('Error updating user');
            return 'erreur';
        
        }
    }

}
