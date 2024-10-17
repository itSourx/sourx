<?php

use function Ramsey\Uuid\v1;
use Illuminate\Http\Request;
use App\Http\Middleware\auth;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\v1\AuthController;
use App\Http\Controllers\Api\v1\UserController;
use App\Http\Controllers\Api\v1\DemandeController;
use App\Http\Controllers\Api\v1\DocumentController;
use App\Http\Controllers\Api\v1\FolderController;
use App\Http\Controllers\Api\v1\PasswordController;
use App\Http\Controllers\Api\v1\TeamController;
use App\Http\Controllers\Api\v1\PosteController;
use App\Http\Controllers\ActivityLogController;


Route::group(['prefix' => 'v1'], function () {

    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login'])->name('login');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::put('/updateProfile', [AuthController::class, 'updateProfile'])->name('updateProfile');
    });

    // Password reset routes
    Route::prefix('password')->group(function () {
        Route::post('/reset', [PasswordController::class, "sendResetLinkEmail"])->name('sendResetLinkEmail');
        Route::post('/verify', [PasswordController::class, 'verifyResetCode'])->name('verifyResetCode');
        Route::post('/confirm', [PasswordController::class, 'resetPassword'])->name('resetPassword');
        Route::post('/first-login-change', [PasswordController::class, 'firstLoginChange'])->name('firstLoginChange');
    });

    // Actions de connexions
    Route::post('/createUser', [AuthController::class, 'createUser'])->name('createUser');
    Route::post('/bulkCreateUsers', [AuthController::class, 'bulkCreateUsers'])->name('bulkCreateUsers');
    Route::put('/updateUser/{id}', [AuthController::class, 'updateUser'])->name('updateUser');
    Route::put('/toggleArchiveStatus/{id}', [AuthController::class, 'toggleArchiveStatus'])->name('toggleArchiveStatus');

    Route::delete('/deleteUser/{id}', [AuthController::class, 'deleteUser'])->name('deleteUser');

    Route::get('/teams', [AuthController::class, 'getTeams'])->name('teams');
    Route::delete('/removeUserFromTeam/{userId}', [AuthController::class, 'removeUserFromTeam'])->name('removeUserFromTeam');

    Route::get('/isUserAuth', [AuthController::class, 'isUserAuth'])->name('isUserAuth');
    Route::post('/allemployees', [AuthController::class, 'getAllEmployees'])->name('allemployees');
    Route::post('/getTeamMembers', [AuthController::class, 'getTeamMembers'])->name('getTeamMembers');
    Route::post('/getAuthUser', [AuthController::class, 'getAuthUser'])->name('getAuthUser');

    // Actions sur les Équipes

    Route::post('/createTeam', [TeamController::class, 'createTeam'])->name('createTeam');
    Route::put('/updateTeam/{id}', [TeamController::class, 'updateTeam'])->name('updateTeam');
    Route::delete('/deleteTeam/{name}', [TeamController::class, 'deleteTeam'])->name('deleteTeam');
    Route::get('/getTeams', [TeamController::class, 'getTeams'])->name('getTeams');
    Route::prefix('team')->group(function () {
        Route::put('/toggleTeamArchiveStatus/{teamName}', [TeamController::class, 'toggleTeamArchiveStatus'])->name('toggleTeamArchiveStatus');
    });


    // Action sur les documents

    Route::post('/sendDocumentByUser', [DocumentController::class, 'sendDocumentByUser'])->name('sendDocumentByUser');
    Route::post('/sendDocumentByAdmin', [DocumentController::class, 'sendDocumentByAdmin'])->name('sendDocumentByAdmin');
    Route::post('/getDocumentByUser', [DocumentController::class, 'getDocumentByUser'])->name('getDocumentByUser');
    Route::get('/documents', [DocumentController::class, 'getAllDocuments'])->name('getAllDocuments');
    Route::post('/document/move/{documentId}/{folderName}', [DocumentController::class, 'moveDocument'])
        ->where('folderName', '.*')
        ->name('moveDocument');

    // Routes sur les dossiers 

    Route::prefix('folders')->group(function () {
        Route::post("/create", [FolderController::class, "createFolder"])->name("createFolder");
        Route::delete('/delete/{name}', [FolderController::class, 'deleteFolder'])->where('name', '.*')->name("deleteFolder");
        Route::put('/rename/{name}', [FolderController::class, 'renameFolder'])->where('name', '.*')->name("renameFolder");
    });

    Route::prefix('postes')->group(function () {
        Route::post('/', [PosteController::class, 'createPoste'])->name('createPoste'); 
        Route::patch('/{id}', [PosteController::class, 'updatePoste'])->name('updatePoste'); 
        Route::patch('/{id}/archive', [PosteController::class, 'archivePoste'])->name('archivePoste');
        Route::patch('/{id}/unarchive', [PosteController::class, 'unarchivePoste'])->name('unarchivePoste');
        Route::get('/', [PosteController::class, 'getPostes'])->name('getPostes');
    });

    Route::get('/motifs', [DemandeController::class, 'getMotifs'])->name('getMotifs');
    Route::post('/motifs', [DemandeController::class, 'createMotif'])->name('createMotif');
    Route::patch('/motifs/{id}', [DemandeController::class, 'updateMotif'])->name('updateMotif');
    Route::patch('/motifs/{id}/archive', [DemandeController::class, 'archiveMotif'])->name('archiveMotif');
    Route::patch('/motifs/{id}/unarchive', [DemandeController::class, 'unarchiveMotif'])->name('unarchiveMotif');

    Route::post('/createDemand', [DemandeController::class, 'createDemand'])->name('createDemand');
    Route::post('/getDemandsByUser', [DemandeController::class, 'getDemandsByUser'])->name('getDemandsByUser');
    Route::post('/getDemandsToValidate', [DemandeController::class, 'getDemandsToValidate'])->name('getDemandsToValidate');
    Route::post('/requests/{requestId}/approve', [DemandeController::class, 'accepter'])->name('accepter');
    Route::post('/requests/{requestId}/reject', [DemandeController::class, 'refuser'])->name('refuser');
    Route::patch('/requests/{requestId}/takeCharge', [DemandeController::class, 'prendreEnCharge'])->name('prendreEnCharge');
    Route::patch('/requests/{requestId}/update', [DemandeController::class, 'updateRequest'])->name('updateRequest');

    // Actions sur les informations de l'utilisateur

    Route::post('/user/informations', [AuthController::class, 'getUserInformations'])->name('getUserInformations');
    Route::post('/user/update', [UserController::class, 'updateUser'])->name('updateUser');

    // Auth request
    Route::middleware("auth")->group(function () {

        Route::get('/recentActivities', [ActivityLogController::class, 'getRecentActivities'])->name('getRecentActivities');

        // delete documents
        Route::post("/document/delete", [DocumentController::class, "deleteDocument"])->name("deleteDocument");
    });
});

