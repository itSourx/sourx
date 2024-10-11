<?php

namespace App\Providers;

use App\Models\AirTable\AirtableUserModel;
use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;

class AirtableUserProvider extends EloquentUserProvider
{
    protected AirtableUserModel  $models;
    public function __construct(AirtableUserModel $models)
    {
       // parent::__construct( $hasher ,$model);
        $this->model = $models;

    }

    // Méthode pour récupérer un utilisateur par son ID
    public function retrieveById($identifier)
    {
        $record = $this->models->airtable->getRecord('nom_de_la_table_Airtable', $identifier);

        if (!$record) {
            return null;
        }

        return new AirtableUserModel($record);
    }

    // Méthode pour récupérer un utilisateur par son email
    public function retrieveByCredentials($credentials)
    {
        $email = $credentials['email'];

        $records = $this->models->airtable->search('nom_de_la_table_Airtable', ['Email' => $email]);

        if (!$records) {
            return null;
        }

        $airtableUser = new AirtableUserModel($records[0]); // Assurez-vous que votre recherche renvoie un seul utilisateur

        return $airtableUser;
    }

    // Méthode pour vérifier si un utilisateur existe
    public function validateCredentials($user, $credentials)
    {
        $email = $credentials['email'];
        $password = $credentials['password'];

        // Vérifiez si l'utilisateur existe avec l'email fourni
        $airtableUser = $this->retrieveByCredentials(['email' => $email]);

        if (!$airtableUser) {
            return false;
        }

        // Vérifiez si le mot de passe correspond (implémentez la vérification du mot de passe)
        // if (!password_verify($password, $airtableUser->getPassword())) {
        //     return false;
        // }

        return true;
    }
}
