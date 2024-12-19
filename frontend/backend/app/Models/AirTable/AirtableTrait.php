<?php
namespace App\Models\AirTable;

use Tapp\Airtable\Facades\AirtableFacade;

trait AirtableTrait {

    public static function fields($jsonData, $fields) {
        $data = json_decode($jsonData, true);
        $table = [];

        if (empty($data) || empty($fields)) {
            return $table; // Handle invalid data or empty fields
        }

        foreach ($data as $row) {
            $rowData = [
                "recodid"=>$row["id"]
            ];

            foreach ($fields as $field) {
                if (isset($row['fields'][$field])) {
                    $rowData[$field] = $row['fields'][$field];

                } else {
                    $rowData[$field] = null; // Handle missing fields
                }
            }
            $table[] = $rowData;
        }

        return $table;
    }

    public static function db_find($id,$filable){

        
    }




}

trait traine {

   
    public static function findx($id){

         $reponse = AirtableFacade::table($table)->where("id",$id)->get();
        
    }




}

 trait AirtableReponse {

    public static function first($reponse)
    {
        return $reponse[0];
    }


}


?>



