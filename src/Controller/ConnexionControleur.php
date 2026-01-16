<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class ConnexionControleur extends AbstractController
{
    #[Route('/connexion', name: 'app_connexion')]
    public function login(AuthenticationUtils $outilsAuthentification): Response
    {
        $erreur = $outilsAuthentification->getLastAuthenticationError();
        $dernierNomUtilisateur = $outilsAuthentification->getLastUsername();
        return $this->render('connexion/index.html.twig', ['dernierNomUtilisateur' => $dernierNomUtilisateur, 'erreur' => $erreur]);
    }

    #[Route(path: '/deconnexion', name: 'app_deconnexion')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
