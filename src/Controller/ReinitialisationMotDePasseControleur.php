<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ResetPasswordRequestFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/motdepasse')]
class ReinitialisationMotDePasseControleur extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TranslatorInterface $translator
    ) {
    }

    #[Route('/mot-de-passe-oublié', name: 'app_requete_motdepasse_oublie')]
    public function request(
        Request $request,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        MailerInterface $mailer
    ): Response {
        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $user = $userRepository->findOneBy(['email' => $email]);

            if ($user) {
                $temporaryPassword = bin2hex(random_bytes(4)); // 8 caractères
                $user->setMotDePasse($passwordHasher->hashPassword($user, $temporaryPassword));

                try {
                    $this->entityManager->flush();
                } catch (\Exception $e) {
                    $this->addFlash('danger', $this->translator->trans('general_error'));
                    return $this->redirectToRoute('app_default');
                }

                $emailMessage = (new TemplatedEmail())
                    ->from(new Address($_ENV['MAILER_FROM'], 'Robot Clash'))
                    ->to($user->getEmail())
                    ->subject($this->translator->trans('password_reset_subject'))
                    ->htmlTemplate('reinitialisation_motdepasse/email_temp.html.twig')
                    ->context([
                        'temporaryPassword' => $temporaryPassword,
                        'user' => $user,
                    ]);
                $mailer->send($emailMessage);
            }

            $this->addFlash('success', $this->translator->trans('temporary_password_sent'));
            return $this->redirectToRoute('app_check_email');
        }

        return $this->render('reinitialisation_motdepasse/reinitialisation.html.twig', [
            'requestForm' => $form,
        ]);
    }

    #[Route('/organisateur/reinitialiser-motdepasse/{id}', name: 'app_orga_reinitialiser_motdepasse')]
    public function resetUserPassword(
        int $id,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        MailerInterface $mailer
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ORGANISATEUR');
        $user = $userRepository->find($id);

        if (!$user) {
            $this->addFlash('error', $this->translator->trans('user_not_found'));
            return $this->redirectToRoute('app_liste_utilisateurs');
        }

        $temporaryPassword = bin2hex(random_bytes(4));
        $user->setMotDePasse($passwordHasher->hashPassword($user, $temporaryPassword));

        try {
            $this->entityManager->flush();
        } catch (\Exception $e) {
            $this->addFlash('danger', $this->translator->trans('general_error'));
            return $this->redirectToRoute('app_default');
        }

        $organisateur = $this->getUser();

        $emailMessage = (new TemplatedEmail())
            ->from(new Address($_ENV['MAILER_FROM'], 'Robot Clash'))
            ->to($user->getEmail())
            ->subject($this->translator->trans('password_reset_subject'))
            ->htmlTemplate('reinitialisation_motdepasse/email.html.twig')
            ->context([
                'temporaryPassword' => $temporaryPassword,
                'user' => $user,
                'organisateur' => $organisateur,
            ]);

        $mailer->send($emailMessage);

        $this->addFlash(
            'success',
            $this->translator->trans('password_reset_email_sent', ['%email%' => $user->getEmail()])
        );

        return $this->redirectToRoute('app_liste_utilisateurs');
    }

    #[Route('/check-email', name: 'app_check_email')]
    public function checkEmail(): Response
    {
        return $this->render('reinitialisation_motdepasse/check_email.html.twig');
    }
}
