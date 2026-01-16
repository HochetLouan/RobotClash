<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class ListeOrganisateursControleur extends AbstractController
{
    #[Route('/organisateurs', name: 'app_liste_organisateurs')]
    public function index(Request $request, UserRepository $userRepository): Response
    {
        $q = trim($request->query->get('q', ''));
        $field = $request->query->get('field', 'all');

        $page = max(1, $request->query->getInt('page', 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $total = $userRepository->countOrganisateursSearch($q, $field);
        $pages = (int) ceil($total / $limit);

        if ($pages > 0 && $page > $pages) {
            $page = $pages;
            $offset = ($page - 1) * $limit;
        }

        $organisateurs = $userRepository->findOrganisateursSearch($q, $field, $limit, $offset);

        return $this->render('liste_organisateurs/index.html.twig', [
        'organisateurs' => $organisateurs,
        'total' => $total,
        'page' => $page,
        'pages' => $pages,
        'q' => $q,
        'field' => $field,
        ]);
    }
}
