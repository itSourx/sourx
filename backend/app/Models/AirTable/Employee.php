<?php

namespace App\Models\AirTable;

use Tapp\Airtable\Facades\AirtableFacade;


class Employee extends AirTableModel
{
    //constructor
    public function __construct($record)
    {
        parent::__construct($record);
    }

    //reset password
    public function resetPassword($newPassword)
    {
        $newPassword = hash('sha256', $newPassword);
        $response = AirtableFacade::table('Employee')->update($this->id, ['password' => $newPassword]);
        return true;
    }
    

     
    

    
};
