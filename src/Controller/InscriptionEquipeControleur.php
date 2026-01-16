<?php

namespace App\Controller;

use App\Entity\Equipe;
use App\Form\InscriptionEquipeFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\EquipeRepository;
use Symfony\Contracts\Translation\TranslatorInterface;

final class InscriptionEquipeControleur extends AbstractController
{
    #[Route('/inscription/{id}/equipe', name: 'app_inscription_equipe', methods: ['GET', 'POST'])]
    public function inscrire(
        Request $request,
        EntityManagerInterface $entityManager,
        $id,
        TranslatorInterface $translator
    ): Response {
        $equipe = new Equipe();
        $formulaire = $this->createForm(InscriptionEquipeFormType::class, $equipe);
        $formulaire->handleRequest($request);

        if ($formulaire->isSubmitted() && $formulaire->isValid()) {
            try {
                $user = $this->getUser();
                if (!$user) {
                    throw new \LogicException($translator->trans('team_registration.error.access_denied'));
                }

                $jsonMembres = $formulaire->get('membres')->getData();
                $equipe->setUserId($user->getId());
                $equipe->setCompetitionId((int) $id);
                $equipe->setSeqId(2);
                $equipe->setDateCreation(new \DateTimeImmutable());
                $equipe->setMembres($jsonMembres);

                $entityManager->persist($equipe);
                $entityManager->flush();

                return $this->redirectToRoute('app_inscription_equipe', ['id' => $id]);
            } catch (\Exception $e) {
                $this->addFlash('danger', $translator->trans('team_registration.error.name_taken'));
            }
        }

        return $this->render('inscription_equipe/index.html.twig', [
            'FormulaireInscriptionEquipe' => $formulaire->createView(),
        ]);
    }

    #[Route('/mesEquipes/{id}/equipes', name: 'app_utilisateur_equipe', methods: ['GET', 'POST'])]
    public function getMesEquipes(Request $request, EquipeRepository $equipeRepository, $id): Response
    {
        $etat = $request->query->get('etat', 'all');

        $equipes = $equipeRepository->findBy(['userId' => $id]);

        $equipesFiltrees = array_values(array_filter($equipes, function (Equipe $equipe) use ($etat) {
            $seq = $equipe->getSeqId();

            return match ($etat) {
                'accepted' => (int) $seq === 1,
                'pending'  => $seq === null || (int) $seq === 2,
                'refused'  => (int) $seq === 3,
                default    => true,
            };
        }));

        return $this->render('inscription_equipe/mesEquipes.html.twig', [
            'equipes' => $equipesFiltrees,
            'etat' => $etat,
            'userId' => $id,
        ]);
    }

    #[Route('/equipe/{id}/modifier', name: 'app_equipe_modifier', methods: ['GET', 'POST'])]
    public function modifier(
        Request $request,
        EntityManagerInterface $entityManager,
        int $id,
        TranslatorInterface $translator
    ): Response {
        $equipe = $entityManager->getRepository(Equipe::class)->find($id);

        if (!$equipe) {
            throw $this->createNotFoundException($translator->trans('team_registration.error.not_found'));
        }

        $formulaire = $this->createForm(InscriptionEquipeFormType::class, $equipe);
        $formulaire->handleRequest($request);

        if ($formulaire->isSubmitted() && $formulaire->isValid()) {
            $jsonMembres = $formulaire->get('membres')->getData();
            $equipe->setMembres($jsonMembres);

            try {
                $entityManager->flush();
            } catch (\Exception $e) {
                $this->addFlash('danger', $translator->trans('team_registration.error.name_taken_competition'));
                return $this->redirectToRoute('app_default');
            }

            $this->addFlash('success', $translator->trans('team_registration.flash.edit_success'));
            return $this->redirectToRoute('app_utilisateur_equipe', ['id' => $equipe->getUserId()]);
        }

        return $this->render('inscription_equipe/modifier.html.twig', [
            'FormulaireInscriptionEquipe' => $formulaire->createView(),
        ]);
    }
}
