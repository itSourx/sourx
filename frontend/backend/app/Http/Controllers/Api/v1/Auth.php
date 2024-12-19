<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\AirTable\AirtableModel;
use App\Models\AirTable\AirtableReponse;
use App\Models\AirTable\Employee;
use Tapp\Airtable\Facades\AirtableFacade;

class Auth extends Controller
{
    use AirtableReponse;

    
    public static function employee($token)
    {
       
            $tokenHash = hash('sha256', $token);
            $tokenInfo = AirtableFacade::table('token_table')->where('token', $tokenHash)->get();
            $employeeId = $tokenInfo[0]['fields']['userid'];

            $response = AirtableFacade::table('Employee')->where('id', $employeeId)->get();
            return new Employee(AirtableReponse::first($response));


        

    }

}
                                               