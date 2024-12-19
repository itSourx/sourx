<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;


class FilesController extends Controller
{
    public function uploadFile(UploadedFile $file, $folder = null, $filename = null)
    {
        $name = !is_null($filename) ? $filename : Str::random(25);

        $path = $file->storeAs(
            null,
            $name . "." . $file->getClientOriginalExtension(),
            'gcs'
        );

        if (!$path) {
            Log::error("Le fichier n'a pas été stocké correctement. Le chemin retourné est vide.");
        }
    
        return $path;
    }
    
}
