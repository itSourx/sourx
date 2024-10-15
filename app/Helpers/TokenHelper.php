<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use Tapp\Airtable\Facades\AirtableFacade;

class TokenHelper
{
    public static function getUserIdFromToken(Request $request)
    {
        $authorizationHeader = $request->header('Authorization');

        if (!$authorizationHeader) {
            return null;
        }

        $token = str_replace('Bearer ', '', $authorizationHeader);
        $tokenHash = hash('sha256', $token);
        $tokenInfo = AirtableFacade::table('token_table')->where('token', $tokenHash)->get();
        if ($tokenInfo->isEmpty()) {
            return null;
        }

        $employeeAirtableId = AirtableFacade::table('Employee')->where('id', $tokenInfo[0]["fields"]['userid'])->get();

        return $employeeAirtableId;
    }

    public static function getSimpleUserIdFromToken(Request $request)
    {
        $authorizationHeader = $request->header('Authorization');

        if (!$authorizationHeader) {
            return null;
        }

        $token = str_replace('Bearer ', '', $authorizationHeader);
        $tokenHash = hash('sha256', $token);
        $tokenInfo = AirtableFacade::table('token_table')->where('token', $tokenHash)->get();

        if ($tokenInfo->isEmpty()) {
            return null;
        }

        return $tokenInfo[0]["fields"]["userid"];
    }

}
