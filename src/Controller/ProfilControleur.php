<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ProfilControleur extends AbstractController
{
    public function __construct(
        private TranslatorInterface $translator
    ) {
    }

    #[Route('/profil', name: 'app_profil')]
    public function index(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $succesMdp = false;
        $erreurMdp = null;
        $succesEmail = false;
        $erreurEmail = null;

        if ($request->isMethod('POST')) {
            $formType = $request->request->get('form_type');

            if ($formType === 'motdepasse') {
                $motDePasseActuel = $request->request->get('mot_de_passe_actuel');
                $nouveauMotDePasse = $request->request->get('nouveau_mot_de_passe');

                if ($passwordHasher->isPasswordValid($this->getUser(), $motDePasseActuel)) {
                    $this->getUser()->setPassword(
                        $passwordHasher->hashPassword($this->getUser(), $nouveauMotDePasse)
                    );
                    try {
                        $entityManager->flush();
                    } catch (\Exception $e) {
                        $this->addFlash('danger', $this->translator->trans('general_error'));
                        return $this->redirectToRoute('app_default');
                    }
                    $succesMdp = true;
                } else {
                    $erreurMdp = $this->translator->trans('current_password_incorrect');
                }
            } elseif ($formType === 'email') {
                $nouvelEmail = $request->request->get('nouvel_email');

                if (filter_var($nouvelEmail, FILTER_VALIDATE_EMAIL)) {
                    $this->getUser()->setEmail($nouvelEmail);
                    try {
                        $entityManager->flush();
                    } catch (\Exception $e) {
                        $this->addFlash('danger', $this->translator->trans('general_error'));
                        return $this->redirectToRoute('app_default');
                    }

                    $email = (new Email())
                        ->from('no-reply@tonsite.fr')
                        ->to($nouvelEmail)
                        ->subject($this->translator->trans('email_updated_subject'))
                        ->text($this->translator->trans('email_updated_text'));

                    $mailer->send($email);

                    $succesEmail = true;
                } else {
                    $erreurEmail = $this->translator->trans('invalid_email');
                }
            }
        }

        return $this->render('profil/index.html.twig', [
            'succesMdp' => $succesMdp,
            'erreurMdp' => $erreurMdp,
            'succesEmail' => $succesEmail,
            'erreurEmail' => $erreurEmail,
        ]);
    }
}
