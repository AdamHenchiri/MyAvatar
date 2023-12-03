<?php

namespace App\Service;

use App\Entity\Festival;
use App\Entity\Utilisateur;
use Doctrine\ORM\Query\Expr\Func;

class UtilisateurUtils {

    public function __construct() {
    }

    public function isBenevole(Utilisateur $u, Festival $f): bool {
        $benevoles = $f->getBenevoles();
        return $benevoles->filter(function (Utilisateur $benevole) use ($u) {
            return $benevole->getId() == $u->getId();
        })->count() > 0;
    }

    public function isResponsable(Utilisateur $u, Festival $f): bool {
        $responsables = $f->getResponsables();
        return $responsables->filter(function (Utilisateur $responsable) use ($u) {
            return $responsable->getId() == $u->getId();
        })->count() > 0;
    }

    public function isOrganisateur(Utilisateur $u, Festival $f): bool {
        $organisateur = $f->getOrganisateur();
        return $organisateur->getId() == $u->getId();
    }

    public function hasApplied(Utilisateur $u, Festival $f) : bool {
        $demandes = $f->getDemandesBenevole();
        return $demandes->filter(function (Utilisateur $demande) use ($u) {
            return $demande->getId() == $u->getId();
        })->count() > 0;
    }
}
