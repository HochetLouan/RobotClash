<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class LangageControleur extends AbstractController
{
    #[Route('/changer-langue/{langage}', name: 'app_changer_langue')]
    public function changeLangue(
        string $langage,
        Request $request,
        EntityManagerInterface $em,
        TranslatorInterface $translator
    ): Response {
        $languesAutorisees = ['fr', 'en', 'es', 'ja', 'nl'];

        if (!in_array($langage, $languesAutorisees, true)) {
            throw $this->createNotFoundException($translator->trans('nav.language.error_unsupported'));
        }

        $user = $this->getUser();

        if ($user instanceof User) {
            $user->setLangue($langage);
            try {
                $em->flush();
            } catch (\Exception $e) {
            }
        }
        $request->getSession()->set('_locale', $langage);

        return $this->redirect(
            $request->headers->get('referer') ?? $this->generateUrl('app_default')
        );
    }
}
