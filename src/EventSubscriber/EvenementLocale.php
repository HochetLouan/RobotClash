<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class EvenementLocale implements EventSubscriberInterface
{
    public function lorsDeLaRequete(RequestEvent $evenement): void
    {
        $requete = $evenement->getRequest();
        if (!$requete->hasPreviousSession()) {
            return;
        }
        if ($langue = $requete->getSession()->get('_locale')) {
            $requete->setLocale($langue);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'kernel.request' => ['lorsDeLaRequete', 20],
        ];
    }
}
