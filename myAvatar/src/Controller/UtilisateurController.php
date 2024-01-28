<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\ModifierProfilType;
use App\Form\UtilisateurType;
use App\Repository\UtilisateurRepository;
use App\Security\EmailVerifier;
use App\Service\MailerService;
use App\Service\FlashMessageServiceInterface;
use App\Service\UserImageService;
use App\Service\UtilisateurManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use PhpParser\Node\Scalar\String_;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UtilisateurController extends AbstractController
{

    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function index(UserImageService $imageService): Response
    {
        return $this->render('index.html.twig',[            'userImageService' => $imageService
        ]);
    }

    #[Route('/user/profil/{id<\d+>}', name: 'app_user_profil')]
    public function profil(UserImageService $imageService, int $id, UtilisateurRepository $utilisateurRepository): Response {

        $u = $utilisateurRepository->find($id);
        if (!$u)
            throw $this->createNotFoundException("L'utilisateur n'existe pas");
        if ($u!= $this->getUser())
            throw $this->createAccessDeniedException("Vous n'avez pas accès à ce profil");
        $loggedInUser = $this->getUser();

        $isCurrentUser = $loggedInUser && $loggedInUser->getUserIdentifier() != $u->getUserIdentifier();

        //dd($imageService->getUserImage($u->getId()));

        return $this->render('utilisateur/profil.html.twig', [
            'controller_name' => 'UtilisateurController',
            'utilisateur' => $u,
            'isCurrentUser' => $isCurrentUser,
            'userImageService' => $imageService,
            'pp' => $imageService->getUserImage($u->getId())
        ]);
    }

    #[Route('/user/profil/{id<\d+>}/edit', name: 'app_profil_edit')]
    public function edit( UserImageService $imageService,UserPasswordHasherInterface $userPasswordHasher,UtilisateurRepository $repository, #[MapEntity] Utilisateur $utilisateur, Request $request, EntityManagerInterface $em, UtilisateurManagerInterface $utilisateurManager): Response {

        if (!$utilisateur) {
            throw $this->createNotFoundException('Utilisateur non trouvé.');
        }


        $form = $this->createForm(ModifierProfilType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $pp = $form->get('photoProfil')->getData();
            $mdp = $form->get('password')->getData();
            $email = $form->get('email')->getData();
            $oldMdp = $form->get('oldPassword')->getData();
            if ($pp) {
                $image = file_get_contents($pp);
                $utilisateur->setPhotoProfil($image);
            }
            if ($mdp) {
                $userPasswordHasher->hashPassword(
                    $utilisateur,
                    $mdp
                );
            }
            if ($email) {
                $utilisateur->setEncEmail(md5($form->get('email')->getData()));
            }
            if (!$oldMdp) {
                $this->addFlash('error', 'Veuillez entrer votre mot de passe actuel');
                return $this->redirectToRoute('app_profil_edit', ['id' => $utilisateur->getId(), 'userImageService' => $imageService]);
            }else{
                if ($userPasswordHasher->isPasswordValid($utilisateur, $oldMdp)) {
                    $this->addFlash('success', 'Votre profil a été modifié avec succès.');
                    $em->persist($utilisateur);
                    $em->flush();
                    return $this->redirectToRoute('app_user_profil', ['id' => $utilisateur->getId(),'userImageService' => $imageService]);
                } else {
                    $this->addFlash('error', 'Mot de passe incorrect');
                    return $this->redirectToRoute('app_profil_edit', ['id' => $utilisateur->getId(), 'userImageService' => $imageService]);
                }
            }
        }

        return $this->render('utilisateur/modifierProfil.html.twig', [
            'controller_name' => 'UtilisateurController',
            'form' => $form->createView(),
            'utilisateur' => $utilisateur,
            'userImageService' => $imageService
        ]);
    }

    #[Route('/signup', name: 'app_user_signup', methods: ['GET', 'POST'])]
    public function signup(UserImageService $imageService, FlashMessageServiceInterface $ServiceMessageFlashInterface,Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, MailerService $mailerService, TokenGeneratorInterface $tokenGenerator): Response {

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
            'form' => $form->createView(),
            'userImageService' => $imageService
        ]);
    }

    #[Route('/my/avatar/{enc}', name: 'app_user_getPP', options: ['expose' => true], methods: ['GET'])]
    public function getPP(string $enc, UtilisateurRepository $utilisateurRepository): Response
    {
        $u = $utilisateurRepository->findOneBy(['encEmail' => $enc]);
        if (!$u)
            throw $this->createNotFoundException("L'utilisateur n'existe pas");
        $image = stream_get_contents( $u->getPhotoProfil());
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($image);
        $response = new Response();
        $response->headers->set('Content-Type', $mimeType);
        $response->setContent(base64_encode($image));
//        return $this->render('utilisateur/imagepp.html.twig', [
//            'pp' => $response->getContent(),
//        ]);
        return new Response($response->getContent());
    }



    #[Route('/signin', name: 'app_user_signin', methods: ['POST','GET'])]
    public function signin(AuthenticationUtils $authenticationUtils, UserImageService $imageService): Response {
        if($this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('app_home');
        }
        $lastUsername = $authenticationUtils->getLastUsername();
        return $this->render('utilisateur/signin.html.twig', [
            'controller_name' => 'UtilisateurController',
            'userImageService' => $imageService
        ]);
    }

    #[Route('/signout', name: 'app_user_signout', methods: ['GET'])]
    public function signout(): never {
        throw new \Exception('This should never be reached!');
    }

    #[Route('/user/profil/{id<\d+>}/delete', name: 'app_user_delete', methods: ['GET'])]
    public function delete(Utilisateur $utilisateur, EntityManagerInterface $entityManager): Response
    {
        if($utilisateur !== $this->getUser()){
            throw new AccessDeniedException();
        }
        $entityManager->remove($utilisateur);
        $entityManager->flush();
        $this->addFlash('success', 'Votre compte a bien été supprimé');
        return $this->redirectToRoute('app_user_signup');
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
