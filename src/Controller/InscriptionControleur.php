<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\InscriptionFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class InscriptionControleur extends AbstractController
{
    #[Route('/inscription', name: 'app_inscription')]
    public function inscrire(
        Request $requete,
        UserPasswordHasherInterface $HasherMdpUtilisateur,
        Security $securite,
        EntityManagerInterface $entiteManager,
        TranslatorInterface $translator
    ): Response {
        if ($this->getUser()) {
            $this->addFlash('info', $translator->trans('auth.register.already_logged'));
            return $this->redirectToRoute('app_default');
        }

        $utilisateur = new User();
        $formulaire = $this->createForm(InscriptionFormType::class, $utilisateur);
        $formulaire->handleRequest($requete);

        if ($formulaire->isSubmitted() && $formulaire->isValid()) {
            try {
                $motDePasse = $formulaire->get('motDePasse')->getData();
                $utilisateur->setMotDePasse($HasherMdpUtilisateur->hashPassword($utilisateur, $motDePasse));
                $utilisateur->setRole(2);
                $entiteManager->persist($utilisateur);
                $entiteManager->flush();
                return $this->redirectToRoute('app_connexion');
            } catch (\Exception $e) {
                $this->addFlash('danger', $translator->trans('auth.register.error_email_taken'));
                return $this->redirectToRoute('app_default');
            }
        }

        return $this->render('inscription/index.html.twig', [
            'FormulaireInscription' => $formulaire
        ]);
    }
}
