<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SwitchUserControleur extends AbstractController
{
    #[Route('/changer-utilisateur', name: 'app_changer_utilisateur', methods: ['GET','POST'])]
    public function switch(Request $request): RedirectResponse
    {
        $params = [];
        if ($request->query->has('_switch_user')) {
            $params['_switch_user'] = $request->query->get('_switch_user');
        }

        return $this->redirectToRoute('app_default', $params);
    }
}
