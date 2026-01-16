<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ListeUtilisateursControleur extends AbstractController
{
    #[Route('/utilisateurs', name: 'app_liste_utilisateurs')]
    public function index(Request $request, UserRepository $userRepository): Response
    {
        $q = trim($request->query->get('q', ''));
        $field = $request->query->get('field', 'all');

        $page = max(1, $request->query->getInt('page', 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $total = $userRepository->countUsersSearch($q, $field);
        $pages = (int) ceil($total / $limit);

        if ($pages > 0 && $page > $pages) {
            $page = $pages;
            $offset = ($page - 1) * $limit;
        }

        $utilisateurs = $userRepository->findUsersSearch($q, $field, $limit, $offset);

        return $this->render('liste_utilisateurs/index.html.twig', [
            'utilisateurs' => $utilisateurs,
            'total' => $total,
            'page' => $page,
            'pages' => $pages,
            'q' => $q,
            'field' => $field,
        ]);
    }

    #[Route('/utilisateurs/{id<\d+>}/rendre-organisateur', name: 'app_rendre_organisateur', methods: ['POST'])]
    public function rendreOrganisateur(
        int $id,
        EntityManagerInterface $em,
        Request $request,
        TranslatorInterface $translator
    ): Response {
        if (!$this->isCsrfTokenValid('promote_user_' . $id, $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $user = $em->getRepository(User::class)->find($id);
        if (!$user) {
            throw $this->createNotFoundException($translator->trans('user_not_found'));
        }

        $user->setRole(1);
        try {
            $em->flush();
        } catch (\Exception $e) {
            $this->addFlash('danger', $translator->trans('users_list.flash.role_change_error'));
            return $this->redirectToRoute('app_default');
        }

        return $this->redirectToRoute('app_liste_utilisateurs', $request->query->all());
    }
}
