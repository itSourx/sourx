<?php

namespace App\Models\AirTable;
use App\Models\AirTable\AirtableModel;
use Illuminate\Database\Eloquent\Model;

 class AirtableDocument extends Model  {
    /* use traine;

    public function __construct()
    {
        parent::find(0) ;
        
    } */

    protected $table = 'Documents'; 

    protected $fillable = [
        'name',
        'title',
        'userid',
    ];

    public function author() {




    }


 }