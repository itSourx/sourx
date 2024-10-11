<?php

namespace App\Models\AirTable;

use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Tapp\Airtable\Facades\AirtableFacade;

class AirtableUser extends Model implements JWTSubject
{
    private $record;

    public function __construct($record)
    {
        $this->record = $record;
    }

    protected $fillable = [
        'id',
        'nom',
        'prenom',
        'email',
        'telephone',
        'mdp',
        'Photo',
        'equipe',
        'isAdmin',
        'role',
        'Poste',
        'Equipe'
    ];

    // Implement JWTSubject methods

    public function getJWTIdentifier()
    {
        return $this->getAirtableId(); // Return the primary key of the user
    }

    public function getJWTCustomClaims()
    {
        return []; 
    }

    // Accessors for Airtable fields

    public function getAirtableId()
    {
        return AirtableFacade::table('Employee')->where('id', $this->record['id'])->get()[0]['id'];
    }

    public function getId()
    {
        return $this->record['id'];
    }

    public function getNom()
    {
        return $this->record['nom'];
    }

    public function getPrenom()
    {
        return $this->record['prenom'];
    }

    public function getEmail()
    {
        return $this->record['email'];
    }

    public function getTelephone()
    {
        return $this->record['telephone'];
    }

    public function getMdp()
    {
        return $this->record['mdp'];
    }

    public function getPhoto()
    {
        return $this->record['Photo'];
    }

    public function getEquipe()
    {
        return $this->record['equipe'];
    }

    public function getIsAdmin()
    {
        return $this->record['isAdmin'];
    }

    public function getRole()
    {
        return $this->record['role'];
    }

    public function getPoste()
    {
        return $this->record['Poste'];
    }

    // Mutators for Airtable fields

    public function setNom($nom)
    {
        $this->record['nom'] = $nom;
    }

    public function setPrenom($prenom)
    {
        $this->record['prenom'] = $prenom;
    }

    public function setEmail($email)
    {
        $this->record['email'] = $email;
    }

    public function setTelephone($telephone)
    {
        $this->record['telephone'] = $telephone;
    }

    public function setMdp($mdp)
    {
        $this->record['mdp'] = $mdp;
    }

    public function setPhoto($photo)
    {
        $this->record['Photo'] = $photo;
    }

    public function setEquipe($equipe)
    {
        $this->record['equipe'] = $equipe;
    }

    public function setIsAdmin($isAdmin)
    {
        $this->record['isAdmin'] = $isAdmin;
    }

    public function setRole($role)
    {
        $this->record['role'] = $role;
    }

    public function setPoste($poste)
    {
        $this->record['Poste'] = $poste;
    }

    public function __get($key)
    {
        if (array_key_exists($key, $this->record)) {
            return $this->record[$key];
        }

        // Vous pouvez aussi lever une exception ou retourner une valeur par défaut ici
        return null;
    }
}
