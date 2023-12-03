<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\UtilisateurType;
use App\Repository\UtilisateurRepository;
use App\Service\MailerService;
use App\Service\FlashMessageServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\String\Slugger\SluggerInterface;

class UtilisateurController extends AbstractController
{

    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('index.html.twig');
    }

    #[Route('/user/profile/{id}', name: 'app_user_profile')]
    public function profile(int $id, UtilisateurRepository $utilisateurRepository): Response {

        $u = $utilisateurRepository->find($id);
        if (!$u)
            throw $this->createNotFoundException("L'utilisateur n'existe pas");

        $loggedInUser = $this->getUser();

        $isCurrentUser = $loggedInUser && $loggedInUser->getUserIdentifier() != $u->getUserIdentifier();

        return $this->render('utilisateur/profile.html.twig', [
            'controller_name' => 'UtilisateurController',
            'utilisateur' => $u,
            'isCurrentUser' => $isCurrentUser,
        ]);
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

    #[Route('/signup', name: 'app_user_signup', methods: ['GET', 'POST'])]
    public function signup(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager,SluggerInterface $slugger, MailerService $mailerService, TokenGeneratorInterface $tokenGenerator, FlashMessageServiceInterface $ServiceMessageFlashInterface): Response {

        $user = new Utilisateur();
        $form = $this->createForm(UtilisateurType::class,$user);
        $form->handleRequest($request);

        if($this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('app_home');
        }

        if(!($form->isSubmitted() && $form->isValid())){
            $ServiceMessageFlashInterface->addErrorsForm($form);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash('success', 'Please confirm your email');
            $pp = $form->get('photoProfil')->getData();

            if ($pp) {
                $image = file_get_contents($pp);
                $user->setPhotoProfil($image);
            }


            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );

            $user->setIsVerified(false);

            $user->setEncEmail(md5($form->get('email')->getData()));
            $tokenRegistration = $tokenGenerator->generateToken();
            $user->setTokenRegistration($tokenRegistration);
            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user
            $mailerService->send(
                $user->getEmail(),
                'Confirmation d\'adresse mail',
                'confirmation.html.twig',
                [
                    'utilisateur' => $user,
                    'token' => $tokenRegistration,
                ]
            );

            return $this->redirectToRoute('app_home');
        }

        return $this->render('utilisateur/signup.html.twig', [
            'controller_name' => 'UtilisateurController' ,
            'form' => $form->createView()
        ]);
    }

    #[Route('/my/avatar/{enc}', name: 'app_user_getPP', methods: ['GET'])]
    public function getPP(string $enc, UtilisateurRepository $utilisateurRepository): Response {
        $u = $utilisateurRepository->findOneBy(['encEmail' => $enc]);
        if (!$u)
            throw $this->createNotFoundException("L'utilisateur n'existe pas");
        $image = stream_get_contents( $u->getPhotoProfil());
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($image);
        $response = new Response();
        $response->headers->set('Content-Type', $mimeType);
        $response->setContent(base64_encode($image));
        return $this->render('utilisateur/imagepp.html.twig', [
            'pp' => $response->getContent(),
        ]);
    }



    #[Route('/signin', name: 'app_user_signin', methods: ['POST','GET'])]
    public function signin(AuthenticationUtils $authenticationUtils): Response {
        if($this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('app_home');
        }
        $lastUsername = $authenticationUtils->getLastUsername();
        return $this->render('utilisateur/signin.html.twig', [
            'controller_name' => 'UtilisateurController',
        ]);
    }

    #[Route('/signout', name: 'app_user_signout', methods: ['GET'])]
    public function signout(): never {
        throw new \Exception('This should never be reached!');
    }

    #[Route('/verify/{token}/{id<\d+>}', name: 'account_verify', methods: ['GET'])]
    public function verify(string $token, Utilisateur $utilisateur, EntityManagerInterface $entityManager): Response
    {
        if($utilisateur->getTokenRegistration() !== $token
            || $utilisateur->getTokenRegistration() === null
        ){
            throw new AccessDeniedException();
        }

        $utilisateur->setIsVerified(true);
        $utilisateur->setTokenRegistration(null);
        $entityManager->flush();
        $this->addFlash('success', 'Votre compte a bien été vérifié');
        return $this->redirectToRoute('app_user_signin');
    }

}
