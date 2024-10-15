<?php

namespace App\Models\AirTable;

use Tapp\Airtable\Facades\AirtableFacade;


class AirtableModel
{
    protected $record;
    protected $fields = "fields";

    public function __construct($record)
    {
        $this->record = $record;
    }

    public function __get($key)
    {
        // id
        if ($key == "id") {
            return $this->record['id'];
        }


        // fields
        if (array_key_exists($key, $this->record[$this->fields])) {
            return $this->record[$this->fields][$key];
        }
        

        
        return null;
    }

    
}
