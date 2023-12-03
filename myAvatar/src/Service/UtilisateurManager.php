<?php
namespace App\Service;

use App\Entity\Utilisateur;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UtilisateurManager implements UtilisateurManagerInterface
{

    public function __construct(
        //Injection du paramètre dossier_photo_profil
        #[Autowire("%kernel.project_dir%/public/uploads/user")] private string $dossier_photo_profil,

    ){}
    /**
     * Sauvegarde l'image de profil dans le dossier de destination puis affecte son nom au champ correspondant dans la classe de l'utilisateur
     */
    private function sauvegarderPhotoProfil(Utilisateur $utilisateur, ?UploadedFile $fichierPhotoProfil) : void {
        if($fichierPhotoProfil != null) {
            //On configure le nom de l'image à sauvegarder
            //On la déplace vers son dossier de destination
            //On met à jour l'attribut "nomPhotoProfil" de l'utilisateur
            $fileName = uniqid() . '.' . $fichierPhotoProfil->guessExtension();
            $fichierPhotoProfil->move($this->dossier_photo_profil, $fileName);
            $utilisateur->setNomPhotoProfil($fileName);
        }
    }

    /**
     * Réalise toutes les opérations nécessaires avant l'enregistrement en base d'un nouvel utilisateur, après soumissions du formulaire (hachage du mot de passe, sauvegarde de la photo de profil...)
     */
    public function processNewUtilisateur(Utilisateur $utilisateur, ?UploadedFile $fichierPhotoProfil) : void {
        //On sauvegarde (et on déplace) l'image de profil
        $this->sauvegarderPhotoProfil($utilisateur,$fichierPhotoProfil);
    }

}
?>