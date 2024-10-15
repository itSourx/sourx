<?php

namespace App\Models\AirTable;


use Illuminate\Database\Eloquent\Model;
use Serializable;
use Tapp\Airtable\Facades\AirtableFacade as Airtable;


class AirtableUserModel 
{
    protected $table = 'Employee'; // Remplacer par le nom de votre table Airtable
    protected $id ;
    protected $nom ;
    protected $prenom ;
    protected $role ;
    protected $email ;
    protected $telephone ;
    protected $airtable ;



    public function __construct()
    {
        $airtable = new Airtable([
            'base_id' => env('AIRTABLE_BASE_ID'),
            'api_key' => env('AIRTABLE_KEYAIRTABLE_KEY'),
        ]);

        $this->airtable = $airtable;
    }

    // public function all()
    // {
    //     $records = $this->airtable->getRecords('nom_de_la_table_Airtable');

    //     $airtableUsers = [];
    //     foreach ($records as $record) {
    //         $airtableUsers[] = new AirtableUser($record);
    //     }

    //     return $airtableUsers;
    // }

    public function find($id)
    {
        $record = $this->airtable->getRecord('nom_de_la_table_Airtable', $id);

        if (!$record) {
            return null;
        }

        return new AirtableUser($record);
    }

    public function create($data)
    
    {
        $record = $this->airtable->createRecord('nom_de_la_table_Airtable', $data);

        return new AirtableUser($record);
    }

    //public function update($id, $data)
    // {
    //     $this->airtable->updateRecord('nom_de_la_table_Airtable', $id, $data);
    // }

    // public function delete($id)
    // {
    //     $this->airtable->deleteRecord('nom_de_la_table_Airtable', $id);
    // }
}

