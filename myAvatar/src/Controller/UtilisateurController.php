<?php

namespace App\Controller;

use App\Form\UtilisateurType;
use App\Repository\UtilisateurRepository;
use http\Env\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UtilisateurController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('index.html.twig');
    }

    #[Route('/user/profile/{id}', name: 'app_user_profile')]
    public function profile(int $id, UtilisateurRepository $utilisateurRepository): Response {

//        $u = $utilisateurRepository->find($id);
//        if (!$u)
//            throw $this->createNotFoundException("L'utilisateur n'existe pas");
//
//        $loggedInUser = $this->getUser();
//
//        $isCurrentUser = $loggedInUser && $loggedInUser->getUserIdentifier() != $u->getUserIdentifier();
//
//        return $this->render('utilisateur/profile.html.twig', [
//            'controller_name' => 'UtilisateurController',
//            'utilisateur' => $u,
//            'isCurrentUser' => $isCurrentUser,
//        ]);
    }

    #[Route('/user/profile/{id}/edit', name: 'app_profile_edit')]
    public function edit(UtilisateurRepository $repository, #[MapEntity] Utilisateur $utilisateur, Request $request, EntityManagerInterface $em, UtilisateurManagerInterface $utilisateurManager): Response {

        if (!$utilisateur) {
            throw $this->createNotFoundException('Utilisateur non trouvé.');
        }


        $form = $this->createForm(ModifierProfilType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $photoProfil = $form["fichierPhotoProfil"]->getData();
            $utilisateurManager->processNewUtilisateur($utilisateur, $photoProfil);
            $em->flush();
            $this->addFlash('success', 'Votre profil a été modifié avec succès.');
            return $this->redirectToRoute('app_user_profile', ['id' => $utilisateur->getId()]);
        }

        return $this->render('utilisateur/modifierProfil.html.twig', [
            'controller_name' => 'UtilisateurController',
            'form' => $form->createView(),
            'utilisateur' => $utilisateur,
        ]);
    }

    #[Route('/signup', name: 'app_user_signup'), Method('POST')]
    public function signup(Request $request): Response {

        $form = $this->createForm(UtilisateurType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

//            $photoProfil = $form["fichierPhotoProfil"]->getData();
//            $utilisateurManager->processNewUtilisateur($utilisateur, $photoProfil);
//            $em->flush();
//            $this->addFlash('success', 'Votre profil a été modifié avec succès.');
//            return $this->redirectToRoute('app_user_profile', ['id' => $utilisateur->getId()]);
        }

        return $this->render('utilisateur/signup.html.twig', [
            'controller_name' => 'UtilisateurController',
        ]);
    }

    #[Route('/login', name: 'app_user_signin'), Method('GET','POST')]
    public function signin(): Response {

        return $this->render('utilisateur/signin.html.twig', [
            'controller_name' => 'UtilisateurController',
        ]);
    }

    #[Route('/logout', name: 'app_user_logout', methods: ['GET'])]
    public function logout(): never {
        throw new \Exception('This should never be reached!');
    }

}
