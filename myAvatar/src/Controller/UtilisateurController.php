<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\UtilisateurType;
use App\Repository\UtilisateurRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class UtilisateurController extends AbstractController
{
    private EmailVerifier $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier) {
        $this->emailVerifier = $emailVerifier;
    }

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

    #[Route('/signup', name: 'app_user_signup', methods: ['GET', 'POST'])]
    public function signup(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager,): Response {

        $user = new Utilisateur();
        $form = $this->createForm(UtilisateurType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );
            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('no-reply@festiflux.com', 'No Reply'))
                    ->to($user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );
            return $this->redirectToRoute('app_home');
        }

        return $this->render('utilisateur/signup.html.twig', [
            'controller_name' => 'UtilisateurController',
        ]);
    }

    #[Route('/login', name: 'app_user_signin', methods: ['GET', 'POST'])]
    public function signin(): Response {

        return $this->render('utilisateur/signin.html.twig', [
            'controller_name' => 'UtilisateurController',
        ]);
    }

    #[Route('/logout', name: 'app_user_logout', methods: ['GET'])]
    public function logout(): never {
        throw new \Exception('This should never be reached!');
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_user_signup');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_home');
    }

}
