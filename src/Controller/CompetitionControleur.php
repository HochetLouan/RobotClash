<?php

namespace App\Controller;

use App\Entity\Competition;
use App\Entity\Equipe;
use App\Entity\User;
use App\Repository\CompetitionRepository;
use App\Repository\EquipeRepository;
use App\Repository\UserRepository;
use PharIo\Manifest\Email;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class CompetitionControleur extends AbstractController
{
    #[Route('/competition', name: 'app_competition')]
    public function index(
        Request $request,
        CompetitionRepository $competitionRepository,
        UserRepository $userRepository,
        EquipeRepository $equipeRepository
    ): Response {
        $q = trim($request->query->get('q', ''));
        $field = $request->query->get('field', 'all');

        $page = max(1, $request->query->getInt('page', 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;
        $status = $request->query->get('status', 'all');

        $total = $competitionRepository->compteCompetitionParNom($q, $status);
        $pages = (int) ceil($total / $limit);

        if ($pages > 0 && $page > $pages) {
            $page = $pages;
            $offset = ($page - 1) * $limit;
        }

        $competitions = $competitionRepository->findCompetitionsByName($q, $status, $limit, $offset);

        $equipes = $equipeRepository->getAll();

        foreach ($equipes as $equipe) {
            $membres = json_decode($equipe->getMembres(), true);
            $equipe->membresArray = $membres ?? [];
        }

        $users = [];
        foreach ($equipes as $equipe) {
            $users[$equipe->getId()] = $userRepository->find($equipe->getUserId());
        }

        return $this->render('competition/index.html.twig', [
            'competitions' => $competitions,
            'equipes' => $equipeRepository->getAll(),
            'users' => $users,
            'total' => $total,
            'page' => $page,
            'pages' => $pages,
            'q' => $q,
            'field' => $field,
            'status' => $status,
        ]);
    }

    #[Route('/competition/{id}/modifier', name: 'app_competition_modifier')]
    public function modifier(int $id, CompetitionRepository $repo, TranslatorInterface $translator)
    {
        $competition = $repo->trouveUnParId($id);

        if (!$competition) {
            throw $this->createNotFoundException($translator->trans('competition.error.not_found'));
        }

        return $this->render('competition/modifier.html.twig', [
            'competition' => $competition,
        ]);
    }
    #[Route('/competition/{id}/enregistrerModif', name: 'app_competition_enregistrerModif', methods: ['POST'])]
    public function enregistrerModif(
        int $id,
        Request $request,
        CompetitionRepository $competitionRepository,
        EntityManagerInterface $em
    ): Response {
        $competition = $competitionRepository->trouveUnParId($id);

        if (!$competition) {
            throw $this->createNotFoundException('Compétition introuvable');
        }
        try {
            $competition->setNom($request->request->get('nom'));
            $competition->setDescription($request->request->get('description'));
            $competition->setLieu($request->request->get('lieu'));
            $competition->setNbTerrain((int) $request->request->get('nbTerrains'));
            $competition->setDateDebut(
                new \DateTime($request->request->get('dateDebut'))
            );
            $competition->setDateFin(
                new \DateTime($request->request->get('dateFin'))
            );
            $em->flush();
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Nom d\'équipe déjà pris ou date de début supérieure à celle de fin');
            return $this->redirectToRoute('app_competition_modifier', [
                'id' => $id
            ]);
        }
        return $this->redirectToRoute('app_competition');
    }

    #[Route('/competition/nouvelle', name: 'app_competition_nouvelle', methods: ['GET'])]
    public function viewNouvelle(): Response
    {
        return $this->render('competition/nouvelle.html.twig');
    }
    #[Route('/competition/creer', name: 'app_competition_enregistrerCompetition', methods: ['POST'])]
    public function creer(
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $user = $this->getUser();
        $nom = $request->request->get('nom');
        $description = $request->request->get('description');
        $lieu = $request->request->get('lieu');
        $dateDebut = $request->request->get('dateDebut');
        $dateFin = $request->request->get('dateFin');
        $nbTerrain = $request->request->get('nbTerrain');

        if (!$nom || !$description || !$lieu || !$dateDebut || !$dateFin || !$nbTerrain) {
            throw new \InvalidArgumentException('Tous les champs sont obligatoires.');
        }

        try {
            $id = $user->getId();
            $competition = new Competition();
            $competition->setNom($nom);
            $competition->setDescription($description);
            $competition->setLieu($lieu);
            $competition->setDateDebut(new \DateTime($dateDebut));
            $competition->setDateFin(new \DateTime($dateFin));
            $competition->setUTLId($id);
            $competition->setNbTerrain((int) $nbTerrain);

            $em->persist($competition);
            $em->flush();
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Nom d\'équipe déjà pris ou date de début supérieure à celle de fin');
            return $this->redirectToRoute('app_competition_nouvelle');
        }
        return $this->redirectToRoute('app_competition');
    }
    #[Route('/competition/supprimer/{id}', name: 'app_competition_supprimer')]
    public function supprimer(
        int $id,
        CompetitionRepository $competitionRepository,
        EntityManagerInterface $em,
        TranslatorInterface $translator
    ): Response {

        $competition = $competitionRepository->find($id);
        $user = $this->getUser();
        if ($user == null) {
            return $this->redirectToRoute('app_competition');
        } elseif ($user->getRole() != 1) {
            return $this->redirectToRoute('app_competition');
        }

        if (!$competition) {
            throw $this->createNotFoundException($translator->trans('competition.error.not_found'));
        }

        $em->remove($competition);
        try {
            $em->flush();
        } catch (\Exception $e) {
            $this->addFlash('danger', "Impossible de s'enregister : le nom de compétition est deja utilisé ou les dates ne sont pas correctes");
            return $this->redirectToRoute(('app_default'));
        }

        return $this->redirectToRoute('app_competition');
    }
    #[Route('/competition/{id}/gerer', name: 'app_competition_gerer_inscription')]
    public function versGestionInscription(int $id, CompetitionRepository $competitionRepository, EquipeRepository $equipeRepository, Request $request): Response
    {
        $etat = $request->query->get('etat', 'all');

        $competition = $competitionRepository->find($id);

        $equipes = $competitionRepository->trouveCompetionInscription($id);
        foreach ($equipes as &$equipe) {
            $equipe['membresArray'] = [];

            if (!empty($equipe['membres'])) {
                $equipe['membresArray'] = json_decode($equipe['membres'], true) ?? [];
            }
        }
        unset($equipe);

        $equipesFiltrees = array_values(array_filter($equipes, function ($equipe) use ($etat) {
            $seq = $equipe['seqId'] ?? $equipe['SEQ_ID'] ?? null;

            return match ($etat) {
                'accepted' => (int) $seq === 1,
                'pending' => $seq === null || (int) $seq === 2,
                'refused' => (int) $seq === 3,
                default => true,
            };
        }));

        return $this->render('inscription_equipe/gestionInscr.html.twig', [
            'competition' => $competition,
            'competitionId' => $id,
            'equipes' => $equipesFiltrees,
            'etat' => $etat,
        ]);
    }

    #[Route('/competition/enregistrerEquipe', name: 'app_competition_enregistrer_statut_inscription_equipe')]
    public function updateEquipeStatus(Request $request, EntityManagerInterface $entityManager): Response
    {
        $statuts = $request->request->all('statut');

        if (!is_array($statuts)) {
            throw new \Exception('Les données de statut sont incorrectes.');
        }

        foreach ($statuts as $idEquipe => $statut) {
            $statut = (int) $statut;

            $equipe = $entityManager->getRepository(Equipe::class)->find($idEquipe);

            if ($equipe) {
                $equipe->setSeqId($statut);
                $entityManager->persist($equipe);
            }
        }
        try {
            $entityManager->flush();
        } catch (\Exception $e) {
            $this->addFlash('danger', "Impossible de s'enregister : le nom de compétition est deja utilisé ou les dates ne sont pas correctes");
            return $this->redirectToRoute(('app_default'));
        }

        return $this->redirectToRoute('app_competition');
    }
    #[Route('/mesCompetitions', name: 'app_competition_organisateur_competition')]
    public function getMesCompetitions(Request $request, CompetitionRepository $competitionRepository, EquipeRepository $equipeRepository): Response
    {
        $q = trim($request->query->get('q', ''));
        $field = $request->query->get('field', 'all');
        $user = $this->getUser();
        if ($user == null) {
            return $this->redirectToRoute('app_connexion');
        }
        $id = $user->getId();

        $page = max(1, $request->query->getInt('page', 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;
        $status = $request->query->get('status', 'all');

        $total = $competitionRepository->compteCompetitionParNomParOrga($q, $id, $status);
        $pages = (int) ceil($total / $limit);
        $etat = $request->query->get('etat', 'all');

        if ($pages > 0 && $page > $pages) {
            $page = $pages;
            $offset = ($page - 1) * $limit;
        }

        $competitions = $competitionRepository->trouveCompetitionParNomParCreateur($q, $limit, $offset, $id, $status);

        $equipes = $equipeRepository->getAll();
        $equipesFiltrees = array_filter($equipes, function ($equipe) use ($etat) {
            $seq = $equipe->getSeqId();

            return match ($etat) {
                'accepted' => $seq === 1,
                'pending' => $seq === 2 || $seq === null,
                'refused' => $seq === 3,
                default => true,
            };
        });

        foreach ($equipesFiltrees as $equipe) {
            $membres = json_decode($equipe->getMembres(), true);
            $equipe->membresArray = $membres ?? [];
        }

        return $this->render('competition/mesCompetitions.html.twig', [
            'competitions' => $competitions,
            'equipes' => $equipesFiltrees,
            'etat' => $etat,
            'total' => $total,
            'page' => $page,
            'pages' => $pages,
            'q' => $q,
            'field' => $field,
            'status' => $status,
        ]);
    }

    #[Route('/competitionAssociee/{equipeId}', name: 'app_competition_associee')]
    public function getCompetitionAssociee(int $equipeId, EntityManagerInterface $em, CompetitionRepository $competitionRepo, EquipeRepository $equipeRepo, TranslatorInterface $translator): Response
    {
        $equipe = $em->getRepository(Equipe::class)->find($equipeId);
        if (!$equipe) {
            throw $this->createNotFoundException('Équipe introuvable');
        }

        $competitionId = $equipe->getCompetitionId();
        $competition = $competitionRepo->trouveUnParId($competitionId);
        if (!$competition) {
            throw $this->createNotFoundException($translator->trans('competition.error.not_found'));
        }

        $equipes = $em->getRepository(Equipe::class)->findBy(['competitionId' => $competitionId]);

        foreach ($equipes as $eq) {
            $membres = json_decode($eq->getMembres(), true);
            $eq->membresArray = $membres ?? [];
        }

        return $this->render('competition/index.html.twig', [
            'competitions' => [$competition],
            'equipes' => $equipes,
            'total' => 1,
            'page' => 1,
            'pages' => 1,
            'q' => '',
            'field' => 'all',
            'mode_associee' => true,
        ]);
    }
}
