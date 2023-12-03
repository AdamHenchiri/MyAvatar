<?php

namespace App\Service;

use App\Entity\Utilisateur;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface UtilisateurManagerInterface
{
    /**
     * Réalise toutes les opérations nécessaires avant l'enregistrement en base d'un nouvel utilisateur, après soumissions du formulaire (hachage du mot de passe, sauvegarde de la photo de profil...)
     */
    public function processNewUtilisateur(Utilisateur $utilisateur, ?UploadedFile $fichierPhotoProfil): void;
}