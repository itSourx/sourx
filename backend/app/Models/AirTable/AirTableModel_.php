<?php

namespace App\Models\AirTable;


use Illuminate\Database\Eloquent\Model;
use Serializable;
use Tapp\Airtable\Facades\AirtableFacade as Airtable;
use Tapp\Airtable\Facades\AirtableFacade;

class AirtableModel 
{
    use AirtableTrait ;
     // Remplacer par le nom de votre table Airtable
    protected $id ;
    protected $nom ;
    protected $prenom ;
    protected $role ;
    protected $email ;
    protected $telephone ;
    protected $airtable ;
    protected $table ;

    protected $fillable = [];
    static protected $ok = 55;




    public function __construct()
    {
        $airtable = new Airtable([
            'base_id' => env('AIRTABLE_BASE_ID'),
            'api_key' => env('AIRTABLE_KEYAIRTABLE_KEY'),
        ]);
        
        $this->airtable = $airtable;
        
    }

    static public function all()
    {
        
    }

    public static function find($id)
    {
        $cl = get_called_class();
        $instance = new $cl ;
        $table = $instance->table ;
        $filable =  $instance->filable;
        $reponse = AirtableFacade::table($table)->where("id",$id)->get();
        return  AirtableTrait::fields($reponse,$filable) ;
        

        
        
    }

    public function create($data)
    
    {
        
    }

    //public function update($id, $data)
    // {
    //     $this->airtable->updateRecord('nom_de_la_table_Airtable', $id, $data);
    // }

    // public function delete($id)
    // {
    //     $this->airtable->deleteRecord('nom_de_la_table_Airtable', $id);
    // }

    public function __get($name) {
        if (isset($this->data[0][$name])) {
            $values = [];
            foreach ($this->data as $row) {
                $values[] = $row[$name];
            }
            return $values;
        } else {
            throw new \Exception("Propriété '$name' inexistante");
        }
    }
}

