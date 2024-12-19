<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Tapp\Airtable\Facades\AirtableFacade;

class auth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json(['message' => 'Token not found'], 401);
        }
        //check if token is valid
        $tokenInfo = AirtableFacade::table('token_table')->where('token', hash('sha256', $token))->get();
      info("tokenInfo", [$tokenInfo[0]]);

        if (count($tokenInfo) == 0) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        // check if token is expired
        $tokenInfo = $tokenInfo[0];
        $expirationDate = $tokenInfo['fields']['expires_in']; //
        $currentDate = date('Y-m-d H:i:s');
        if ($expirationDate < $currentDate) {
            return response()->json(['message' => 'Token expired'], 401);
        }




        
        return $next($request);
    }


}
