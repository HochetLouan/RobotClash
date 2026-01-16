<?php

namespace App\Controller;

use App\Repository\CompetitionRepository;
use App\Repository\EquipeRepository;
use App\Entity\Competition;
use App\Entity\Matchs;
use App\Entity\Equipe;
use App\Repository\MatchsRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

final class MatchsControleur extends AbstractController
{
    const PHASES = [
        32 => 'SEIZIEME',
        16 => 'HUITIEME',
        8  => 'QUART',
        4  => 'DEMI',
        2  => 'FINALE'
    ];

    #[Route('/matchs/{competitionId}', name: 'app_matchs', methods: ['GET'])]
    public function tousLesMatchs(
        int $competitionId,
        MatchsRepository $rep,
        CompetitionRepository $competitionRepository
    ): Response {
        $tousLesMatchs = $rep->trouveEquipesParCompetition($competitionId);
        $classement = $this->calculerClassementData($tousLesMatchs);
        $competitionRepo = $competitionRepository->trouveUnParId($competitionId);
        $matchsAAfficher = $tousLesMatchs;

        if ($competitionRepo && $competitionRepo->isArbreGenere()) {
            $matchsAAfficher = array_filter($tousLesMatchs, function ($m) {
                $commentaire = $m['commentaire'] ?? '';
                if (empty($commentaire)) {
                    return false;
                }
                foreach (self::PHASES as $phase) {
                    if (str_contains($commentaire, $phase)) {
                        return true;
                    }
                }
                if (str_contains($commentaire, 'PETITE_FINALE')) {
                    return true;
                }

                return false;
            });
        }

        return $this->render('matchs/index.html.twig', [
            'matchs' => $matchsAAfficher,
            'competitionId' => $competitionId,
            'classement' => $classement,
            'competition' => $competitionRepo
        ]);
    }

    #[Route('/matchs/{competitionId}/scores/update-all', name: 'app_scores_maj_all', methods: ['POST'])]
    public function updateAllScores(
        int $competitionId,
        Request $request,
        MatchsRepository $matchRepo,
        EntityManagerInterface $em
    ): Response {
        $scores = $request->request->all('scores');

        if (!$scores) {
            $this->addFlash('danger', 'Aucun score à enregistrer.');
            return $this->redirectToRoute('app_matchs', ['competitionId' => $competitionId]);
        }

        $updated = 0;

        foreach ($scores as $matchId => $data) {
            $match = $matchRepo->find((int) $matchId);
            if (!$match) {
                continue;
            }

            $scoreA = isset($data['scoreA']) ? (int) $data['scoreA'] : 0;
            $scoreB = isset($data['scoreB']) ? (int) $data['scoreB'] : 0;

            $match->setScore($scoreA . '-' . $scoreB);
            $updated++;
        }

        $em->flush();
        $this->addFlash('success', $updated . ' score(s) enregistré(s) ');

        return $this->redirectToRoute('app_matchs', ['competitionId' => $competitionId]);
    }


    #[Route('/matchs/{competitionId}/terminer-tout', name: 'app_matchs_terminer_tout', methods: ['POST'])]
    public function terminerTout(
        int $competitionId,
        EntityManagerInterface $em,
        MatchsRepository $matchRepo
    ): Response {
        $TERMINED_STATUS = 3;
        $matchs = $matchRepo->findMatchsValidesParCompetition($competitionId);

        if (!$matchs) {
            $this->addFlash('danger', "Aucun match validé trouvé pour cette compétition");
            return $this->redirectToRoute('app_matchs', ['competitionId' => $competitionId]);
        }

        $updated = 0;
        foreach ($matchs as $match) {
            if ($match->getStatusMatchId() === $TERMINED_STATUS) {
                continue;
            }
            $match->setStatusMatchId($TERMINED_STATUS);
            $this->verifierEtGenererSuite($match, $matchRepo, $em);

            $updated++;
        }

        try {
            $em->flush();
            $this->addFlash('success', $updated . ' match(s) terminé(s)');
        } catch (\Exception $e) {
            $this->addFlash('danger', "Impossible de terminer tous les matchs");
        }

        return $this->redirectToRoute('app_matchs', ['competitionId' => $competitionId]);
    }

    #[Route('/matchs/{competitionId}/generer', name: 'app_matchs_generer')]
    public function genererMatchsAleatoires(
        int $competitionId,
        EntityManagerInterface $em
    ): Response {
        $user = $this->getUser();
        if ($user == null || $user->getRole() != 1) {
            return $this->redirectToRoute('app_competition');
        }

        $equipes = $em->getRepository(Equipe::class)->findBy(['competitionId' => $competitionId]);

        if (count($equipes) < 2) {
            return new Response("Pas assez d'équipes pour générer des matchs");
        }
        $competition = $em->getRepository(Competition::class)->find($competitionId);
        $nbTerrains = $competition->getNbTerrain();
        $anciensMatchs = $em->getRepository(Matchs::class)->findBy(['competitionId' => $competitionId]);
        foreach ($anciensMatchs as $match) {
            $em->remove($match);
        }
        $em->flush();

        for ($i = 0; $i < count($equipes); $i++) {
            for ($j = $i + 1; $j < count($equipes); $j++) {
                $equipeA = $equipes[$i];
                $equipeB = $equipes[$j];
                $match = new Matchs();
                $match->setEquipeAId($equipeA->getId());
                $match->setEquipeBId($equipeB->getId());
                $match->setCompetitionId($competitionId);
                $match->setForfaitEquipeA(0);
                $match->setForfaitEquipeB(0);
                $match->setScore('0-0');
                $match->setCommentaire('');
                $em->persist($match);
                $match->setTerrain(random_int(1, $nbTerrains));
            }
        }
        try {
            $em->flush();
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Génération des matchs impossible');
        }

        return $this->redirectToRoute('app_matchs', ['competitionId' => $competitionId]);
    }

    #[Route('/matchs/{competitionId}/scores', name: 'app_matchs_scores')]
    public function scores(int $competitionId, MatchsRepository $rep): Response
    {
        return $this->render('matchs/score.html.twig', [
            'matchs' => $rep->trouveEquipesParCompetition($competitionId),
            'competitionId' => $competitionId
        ]);
    }

    #[Route('/matchs/{id}/score/maj', name: 'app_score_maj', methods: ['POST'])]
    public function majScore(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $match = $em->getRepository(Matchs::class)->find($id);

        if (!$match) {
            throw $this->createNotFoundException('Match introuvable');
        }

        $scoreEquipeA = $request->request->get('scoreA', 0);
        $scoreEquipeB = $request->request->get('scoreB', 0);

        if ($scoreEquipeA != null && $scoreEquipeB != null) {
            $match->setScore($scoreEquipeA . '-' . $scoreEquipeB);
            try {
                $em->flush();
            } catch (\Exception $e) {
                $this->addFlash('danger', "Impossible de mettre à jour le score");
            }
        }

        return $this->redirectToRoute('app_matchs', [
            'competitionId' => $match->getCompetitionId()
        ]);
    }

    #[Route('/matchs/{competitionId}/commentaire', name: 'app_matchs_comm', methods: ['GET'])]
    public function commentaires(
        int $competitionId,
        Request $request,
        MatchsRepository $rep
    ): Response {

        $matchs = $rep->trouveEquipesParCompetition($competitionId);

        return $this->render('matchs/commentaire.html.twig', [
            'matchs' => $matchs,
            'competitionId' => $competitionId,

        ]);
    }

    #[Route('/matchs/{id}/commentaire/maj', name: 'app_comm_maj', methods: ['POST'])]
    public function majSCommentaires(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $match = $em->getRepository(Matchs::class)->find($id);
        if (!$match) {
            throw $this->createNotFoundException('Match introuvable');
        }

        $nouveauCommentaire = $request->request->get('commentaire');
        if ($nouveauCommentaire) {
            $match->setCommentaire($nouveauCommentaire);
            $em->flush();
        }

        return $this->redirectToRoute('app_matchs_comm', [
            'competitionId' => $match->getCompetitionId()
        ]);
    }

    #[Route('/matchs/{id}/setForfaitA', name: 'app_equipe_forfaitA', methods: ['GET'])]
    public function mettreEquipeForfaitA(int $id, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if ($user == null || $user->getRole() != 1) {
            return $this->redirectToRoute('app_competition');
        }
        $match = $em->getRepository(Matchs::class)->find($id);
        if (!$match) {
            throw $this->createNotFoundException('Match introuvable');
        }

        $match->setForfaitEquipeA(1);
        $em->flush();

        return $this->redirectToRoute('app_matchs', ['competitionId' => $match->getCompetitionId()]);
    }

    #[Route('/matchs/{id}/setForfaitB', name: 'app_equipe_forfaitB', methods: ['GET'])]
    public function mettreEquipeForfaitB(int $id, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if ($user == null || $user->getRole() != 1) {
            return $this->redirectToRoute('app_competition');
        }
        $match = $em->getRepository(Matchs::class)->find($id);
        if (!$match) {
            throw $this->createNotFoundException('Match introuvable');
        }

        $match->setForfaitEquipeB(1);
        $em->flush();

        return $this->redirectToRoute('app_matchs', ['competitionId' => $match->getCompetitionId()]);
    }

    #[Route('/matchs/{id}/terminer', name: 'app_match_terminer', methods: ['POST'])]
    public function terminerMatch(
        int $id,
        EntityManagerInterface $em,
        MatchsRepository $matchsRepository
    ): Response {
        $match = $em->getRepository(Matchs::class)->find($id);

        if (!$match) {
            throw $this->createNotFoundException('Match introuvable');
        }
        $match->setStatusMatchId(3);
        $em->flush();
        $this->verifierEtGenererSuite($match, $matchsRepository, $em);

        return $this->redirectToRoute('app_matchs', [
            'competitionId' => $match->getCompetitionId()
        ]);
    }

    #[Route('/matchs/{id}/supprimer', name: 'app_match_supprimer', methods: ['POST'])]
    public function supprimerMatch(int $id, EntityManagerInterface $em): Response
    {
        $match = $em->getRepository(Matchs::class)->find($id);
        if (!$match) {
            throw $this->createNotFoundException('Match introuvable');
        }

        $match->setStatusMatchId(2);
        $em->flush();

        return $this->redirectToRoute('app_matchs', ['competitionId' => $match->getCompetitionId()]);
    }

    #[Route('/matchs/{competitionId}/horaire/generer', name: 'app_horaire_generer', methods: ['POST'])]
    public function genererHoraire(
        int $competitionId,
        Request $request,
        MatchsRepository $matchsRepository,
        CompetitionRepository $competitionRepository
    ): Response {
        $matchs = $matchsRepository->trouveEquipesParCompetition($competitionId);
        $competition = $competitionRepository->find($competitionId);
        $nbTerrains = $competition ? $competition->getNbTerrain() : 1;

        $heureDebut = $request->request->get('heureDebut');
        $heureFin = $request->request->get('heureFin');
        $dureeSaisie = (int) $request->request->get('duree');
        $duree = $dureeSaisie + 10;

        if (!$heureDebut || !$heureFin || $duree <= 0) {
            return $this->redirectToRoute('app_match_horaire', ['competitionId' => $competitionId]);
        }

        $dateJour = (new \DateTimeImmutable('today'))->format('Y-m-d');
        $finLimite = \DateTime::createFromFormat('Y-m-d H:i:s', $dateJour . ' ' . $heureFin . ':00');
        $disponibiliteTerrains = [];
        for ($t = 1; $t <= $nbTerrains; $t++) {
            $disponibiliteTerrains[$t] = \DateTime::createFromFormat('Y-m-d H:i:s', $dateJour . ' ' . $heureDebut . ':00');
        }

        foreach ($matchs as &$m) {
            if ($m['statusA'] == 1 && $m['statusB'] == 1) {
                $numTerrain = (int) $m['terrain'];
                if (!isset($disponibiliteTerrains[$numTerrain])) {
                    $numTerrain = 1;
                }
                $horairePourCeMatch = clone $disponibiliteTerrains[$numTerrain];
                if ($horairePourCeMatch > $finLimite) {
                    $m['horaireGenere'] = null;
                    continue;
                }
                $m['horaireGenere'] = $horairePourCeMatch;
                $disponibiliteTerrains[$numTerrain]->modify("+{$duree} minutes");
            } else {
                $m['horaireGenere'] = null;
            }
        }

        return $this->render('matchs/horaire.html.twig', [
            'matchs' => $matchs,
            'competitionId' => $competitionId,
            'horaireGenere' => true,
        ]);
    }

    #[Route('/matchs/{competitionId}/horaire', name: 'app_match_horaire', methods: ['GET'])]
    public function horaires(int $competitionId, MatchsRepository $rep): Response
    {
        $matchs = $rep->trouveEquipesParCompetition($competitionId);
        return $this->render('matchs/horaire.html.twig', [
            'matchs' => $matchs,
            'competitionId' => $competitionId,
            'horaireGenere' => false,
        ]);
    }

    #[Route('/matchs/{id}/horaire/maj', name: 'app_horaire_maj', methods: ['POST'])]
    public function majHoraire(
        int $id,
        Request $request,
        EntityManagerInterface $em,
        MatchsRepository $matchsRepository
    ): Response {
        $match = $matchsRepository->find($id);
        if (!$match) {
            throw $this->createNotFoundException('Match introuvable');
        }

        $nouvelHoraire = $request->request->get('horaire');
        if ($nouvelHoraire) {
            try {
                $date = new \DateTime($nouvelHoraire);
                $match->setHoraire($date);
                $em->persist($match);
                $em->flush();
            } catch (\Exception $e) {
                $this->addFlash('danger', 'Impossible de sauvegarder : conflit horaire.');
            }
        }
        return $this->redirectToRoute('app_matchs', ['competitionId' => $match->getCompetitionId()]);
    }

    #[Route('/matchs/{competitionId}/horaire/sauvegarder', name: 'app_horaire_sauvegarder', methods: ['POST'])]
    public function sauvegarderHoraireGenere(
        int $competitionId,
        Request $request,
        EntityManagerInterface $em,
        MatchsRepository $matchsRepository
    ): Response {
        $matchs = $matchsRepository->trouveEquipesParCompetition($competitionId);
        foreach ($matchs as $m) {
            $horaireStr = $request->request->get('horaire_' . $m['id']);
            if ($horaireStr) {
                $matchEntity = $em->getRepository(Matchs::class)->find($m['id']);
                if ($matchEntity) {
                    $matchEntity->setHoraire(new \DateTime($horaireStr));
                    $em->persist($matchEntity);
                }
            }
        }
        try {
            $em->flush();
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Erreur sauvegarde horaires.');
        }
        return $this->redirectToRoute('app_matchs', ['competitionId' => $competitionId]);
    }

    #[Route('/matchs/{competitionId}/parametres', name: 'app_matchs_parametres')]
    public function parametresBracket(
        int $competitionId,
        CompetitionRepository $competitionRepository,
        EquipeRepository $equipeRepository
    ): Response {
        $user = $this->getUser();
        if ($user == null || $user->getRole() != 1) {
            return $this->redirectToRoute('app_competition');
        }

        $competitionRepo = $competitionRepository->trouveUnParId($competitionId);
        $petiteFinale = $competitionRepo->isPetiteFinale();
        $nbEquipes = count($equipeRepository->findBy(['competitionId' => $competitionId]));
        $nbEquipesMaxBracket = $competitionRepo->getNbEquipesMaxBracket();

        return $this->render('matchs/parametres.html.twig', [
            'competition' => $competitionRepo,
            'nbEquipes' => $nbEquipes,
            'nbEquipesMaxBracket' => $nbEquipesMaxBracket,
            'petiteFinale' => $petiteFinale
        ]);
    }

    #[Route('/competition/{competitionId}/miseAJourBracket', name: 'app_competition_mise_a_jour_bracket', methods: ['POST'])]
    public function miseAJourBracket(int $competitionId, Request $request, EntityManagerInterface $em)
    {
        $competition = $em->getRepository(Competition::class)->find($competitionId);
        if (!$competition) {
            throw $this->createNotFoundException('Compétition introuvable');
        }

        $nbEquipesMax = (int) $request->request->get('nbEquipesMax', 0);
        $petiteFinale = $request->request->has('petiteFinale');

        $competition->setNbEquipesMaxBracket($nbEquipesMax);
        $competition->setPetiteFinale($petiteFinale);
        $em->flush();

        $this->addFlash('success', 'Format du tournoi mis à jour ');
        return $this->redirectToRoute('app_matchs_parametres', ['competitionId' => $competitionId]);
    }

    #[Route('/matchs/{competitionId}/generer-arbre-init', name: 'app_matchs_generer_arbre_init')]
    public function genererArbreInit(
        int $competitionId,
        CompetitionRepository $competitionRepository,
        MatchsRepository $matchsRepository,
        EntityManagerInterface $em
    ): Response {
        $user = $this->getUser();
        if (!$user || $user->getRole() != 1) {
            return $this->redirectToRoute('app_competition');
        }

        $competition = $competitionRepository->find($competitionId);
        $nbBracket = $competition->getNbEquipesMaxBracket();
        $matchs = $matchsRepository->trouveEquipesParCompetition($competitionId);
        $classement = $this->calculerClassementData($matchs);
        $equipesQualifieesNoms = array_slice(array_keys($classement), 0, $nbBracket);
        if (count($equipesQualifieesNoms) < $nbBracket) {
            $this->addFlash('danger', "Pas assez d'équipes classées ($nbBracket requises).");
            return $this->redirectToRoute('app_matchs_parametres', ['competitionId' => $competitionId]);
        }

        $equipesObj = [];
        foreach ($equipesQualifieesNoms as $nom) {
            $equipesObj[] = $em->getRepository(Equipe::class)->findOneBy([
                'nom' => $nom,
                'competitionId' => $competitionId
            ]);
        }
        $tousLesMatchs = $em->getRepository(Matchs::class)->findBy(['competitionId' => $competitionId]);
        foreach ($tousLesMatchs as $m) {
            foreach (self::PHASES as $key => $val) {
                if (str_contains($m->getCommentaire(), $val)) {
                    $em->remove($m);
                }
            }
        }
        $em->flush();
        $phaseName = self::PHASES[$nbBracket] ?? null;
        if (!$phaseName) {
            $this->addFlash('danger', "Taille de bracket invalide.");
            return $this->redirectToRoute('app_matchs_parametres', ['competitionId' => $competitionId]);
        }

        $nbMatchs = $nbBracket / 2;
        for ($i = 0; $i < $nbMatchs; $i++) {
            $eqA = $equipesObj[$i];
            $eqB = $equipesObj[$nbBracket - 1 - $i];

            $match = new Matchs();
            $match->setCompetitionId($competitionId);
            $match->setEquipeAId($eqA->getId());
            $match->setEquipeBId($eqB->getId());
            $match->setScore('0-0');
            $match->setStatusMatchId(1);
            $match->setCommentaire($phaseName . '|' . $i);
            $match->setForfaitEquipeA(0);
            $match->setForfaitEquipeB(0);

            $em->persist($match);
        }

        $competition->setArbreGenere(true);
        $em->flush();

        $this->addFlash('success', "Phase finale ($phaseName) générée !");
        return $this->redirectToRoute('app_matchs', ['competitionId' => $competitionId]);
    }

    private function verifierEtGenererSuite(Matchs $matchTermine, MatchsRepository $repo, EntityManagerInterface $em): void
    {
        $commentaire = $matchTermine->getCommentaire();

        if (!str_contains($commentaire, '|')) {
            return;
        }

        [$phaseActuelle, $index] = explode('|', $commentaire);
        $nbEquipesPhase = 0;
        foreach (self::PHASES as $nb => $nom) {
            if ($nom === $phaseActuelle) {
                $nbEquipesPhase = $nb;
                break;
            }
        }
        if ($nbEquipesPhase <= 2) {
            return;
        }

        $matchsPhase = $repo->findMatchsByPhase($matchTermine->getCompetitionId(), $phaseActuelle);

        foreach ($matchsPhase as $m) {
            if ($m->getStatusMatchId() != 3) {
                return;
            }
        }
        usort($matchsPhase, function ($a, $b) {
            $indA = (int) explode('|', $a->getCommentaire())[1];
            $indB = (int) explode('|', $b->getCommentaire())[1];
            return $indA <=> $indB;
        });

        $vainqueurs = [];
        $perdants = [];

        foreach ($matchsPhase as $m) {
            $gagnantId = null;
            $perdantId = null;

            if ($m->getForfaitEquipeA()) {
                $gagnantId = $m->getEquipeBId();
                $perdantId = $m->getEquipeAId();
            } elseif ($m->getForfaitEquipeB()) {
                $gagnantId = $m->getEquipeAId();
                $perdantId = $m->getEquipeBId();
            } else {
                [$sA, $sB] = explode('-', $m->getScore());
                if ((int)$sA > (int)$sB) {
                    $gagnantId = $m->getEquipeAId();
                    $perdantId = $m->getEquipeBId();
                } else {
                    $gagnantId = $m->getEquipeBId();
                    $perdantId = $m->getEquipeAId();
                }
            }
            $vainqueurs[] = $gagnantId;
            $perdants[] = $perdantId;
        }

        $nextNb = $nbEquipesPhase / 2;
        $nextPhaseName = self::PHASES[$nextNb];

        for ($i = 0; $i < count($vainqueurs); $i += 2) {
            $newMatch = new Matchs();
            $newMatch->setCompetitionId($matchTermine->getCompetitionId());
            $newMatch->setEquipeAId($vainqueurs[$i]);
            if (isset($vainqueurs[$i + 1])) {
                $newMatch->setEquipeBId($vainqueurs[$i + 1]);
            }
            $newMatch->setScore('0-0');
            $newMatch->setStatusMatchId(1);
            $newMatch->setCommentaire($nextPhaseName . '|' . ($i / 2));
            $newMatch->setForfaitEquipeA(0);
            $newMatch->setForfaitEquipeB(0);
            $em->persist($newMatch);
        }
        $competition = $em->getRepository(Competition::class)->find($matchTermine->getCompetitionId());
        if ($phaseActuelle === 'DEMI' && $competition->isPetiteFinale()) {
            $pf = new Matchs();
            $pf->setCompetitionId($matchTermine->getCompetitionId());
            $pf->setEquipeAId($perdants[0]);
            $pf->setEquipeBId($perdants[1]);
            $pf->setScore('0-0');
            $pf->setStatusMatchId(1);
            $pf->setCommentaire('PETITE_FINALE|0');
            $pf->setForfaitEquipeA(0);
            $pf->setForfaitEquipeB(0);
            $em->persist($pf);
        }

        $em->flush();
        $this->addFlash('success', "Phase suivante ($nextPhaseName) générée automatiquement !");
    }
    private function calculerClassementData(array $matchs): array
    {
        $classement = [];
        foreach ($matchs as $m) {
            if ($m['statusA'] != 1 || $m['statusB'] != 1) {
                continue;
            }

            if (!isset($classement[$m['equipeA']])) {
                $classement[$m['equipeA']] = [
                    'points' => 0, 'matchs' => 0, 'bp' => 0, 'bc' => 0, 'diff' => 0, 'dateCreation' => $m['dateA']
                ];
            }
            if (!isset($classement[$m['equipeB']])) {
                $classement[$m['equipeB']] = [
                    'points' => 0, 'matchs' => 0, 'bp' => 0, 'bc' => 0, 'diff' => 0, 'dateCreation' => $m['dateB']
                ];
            }

            if ($m['statusMatchId'] != 3) {
                continue;
            }

            $pointsA = 0;
            $pointsB = 0;

            if ($m['forfaitEquipeA'] == 1 && $m['forfaitEquipeB'] == 0) {
                $pointsA = -30;
                $classement[$m['equipeB']]['matchs']--;
            } elseif ($m['forfaitEquipeB'] == 1 && $m['forfaitEquipeA'] == 0) {
                $pointsB = -30;
                $classement[$m['equipeA']]['matchs']--;
            } elseif ($m['forfaitEquipeA'] == 1 && $m['forfaitEquipeB'] == 1) {
                $pointsA = -30;
                $pointsB = -30;
            } else {
                $scoreA = $m['scoreA'];
                $scoreB = $m['scoreB'];
                $classement[$m['equipeA']]['bp'] += $scoreA;
                $classement[$m['equipeA']]['bc'] += $scoreB;
                $classement[$m['equipeB']]['bp'] += $scoreB;
                $classement[$m['equipeB']]['bc'] += $scoreA;

                if ($scoreA > $scoreB) {
                    $pointsA = 100;
                    $pointsB = 0;
                } elseif ($scoreA < $scoreB) {
                    $pointsA = 0;
                    $pointsB = 100;
                } else {
                    $pointsA = 30;
                    $pointsB = 30;
                }
            }

            $classement[$m['equipeA']]['points'] += $pointsA;
            $classement[$m['equipeA']]['matchs']++;
            $classement[$m['equipeB']]['points'] += $pointsB;
            $classement[$m['equipeB']]['matchs']++;
        }

        foreach ($classement as &$data) {
            $data['diff'] = $data['bp'] - $data['bc'];
            $data['moyenne'] = $data['matchs'] > 0 ? round($data['points'] / $data['matchs'], 2) : 0;
        }

        uasort($classement, function ($a, $b) {
            if ($a['points'] != $b['points']) {
                return $b['points'] <=> $a['points'];
            }
            if ($a['diff'] != $b['diff']) {
                return $b['diff'] <=> $a['diff'];
            }
            if ($a['bp'] != $b['bp']) {
                return $b['bp'] <=> $a['bp'];
            }
            return 0;
        });

        return $classement;
    }

    #[Route('/matchs/{id}/export', name: 'app_matchs_export', methods: ['GET'])]
    public function exportICAL(int $id, MatchsRepository $rep, EntityManagerInterface $em)
    {
        $fichier = 'monfichier.ics';
        $f = fopen($fichier, 'w+');
        $matchs = $rep->trouveEquipesParCompetition($id);
        $ics = "BEGIN:VCALENDAR\nVERSION:2.0\nPRODID:-//hacksw/handcal//NONSGML v1.0//EN\nX-WR-TIMEZONE:Europe/Paris\n";
        foreach ($matchs as $match) {
            $date_debut = $match["horaire"];
            if (isset($date_debut)) {
                $date_fin = clone $date_debut;
                $date_fin->modify('+60 minutes');
                $ics .= "BEGIN:VEVENT\n";
                $ics .= "DTSTART:" . $date_debut->format('Ymd\THis') . "\n";
                $ics .= "DTEND:" . $date_fin->format('Ymd\THis') . "\n";
                $ics .= "SUMMARY:" . $match["equipeA"] . " VS " . $match["equipeB"] . "\n";
                $ics .= "END:VEVENT\n";
            }
        }
        $ics .= "END:VCALENDAR\n";
        fputs($f, $ics);
        fclose($f);
        $response = new Response($ics);
        $response->headers->set('Content-Type', 'text/calendar');
        $response->headers->set('Content-Disposition', 'attachment; filename="calendrier_matchs.ics"');
        return $response;
    }

    #[Route('/competition/{competitionId}/classement', name: 'app_competition_classement', methods: ['POST', 'GET'])]
    public function classementCompetition(int $competitionId, MatchsRepository $rep, CompetitionRepository $competitionRepository): Response
    {
        $matchs = $rep->trouveEquipesParCompetition($competitionId);
        $classement = $this->calculerClassementData($matchs);
        $competition = $competitionRepository->trouveUnParId($competitionId);
        return $this->render('matchs/classement.html.twig', [
            'competitionId' => $competitionId,
            'classement' => $classement,
            'competition' => $competition,
        ]);
    }

    #[Route('/matchs/{id}/projection', name: 'app_matchs_projection', methods: ['GET', 'POST'])]
    public function projection(int $id, MatchsRepository $rep): Response
    {
        $tousLesMatchs = $rep->trouveEquipesParCompetition($id);
        $maintenant = new DateTime();
        $maintenant = $maintenant->modify('+60 minutes');
        $matchsPasses = [];
        $matchsEnCours = [];
        $matchsFuturs = [];
        foreach ($tousLesMatchs as $match) {
            if (!isset($match['horaire']) || !$match['horaire'] instanceof \DateTimeInterface) {
                continue;
            }
            $debut = $match['horaire'];
            $fin = (clone $debut)->modify('+60 minutes');
            if ($fin < $maintenant) {
                $matchsPasses[] = $match;
            } elseif ($debut > $maintenant) {
                $matchsFuturs[] = $match;
            } else {
                $matchsEnCours[] = $match;
            }
        }
        return $this->render('projeter/projeter.html.twig', [
            'matchsPasses'  => array_slice($matchsPasses, 0, 2),
            'matchsEnCours' => $matchsEnCours,
            'matchsFuturs'  => array_slice($matchsFuturs, 0, 2),
            'competitionId' => $id,
            'matchs'        => $tousLesMatchs
        ]);
    }

    #[Route('/matchs/{id}/exportJson', name: 'app_matchs_export_json', methods: ['GET'])]
    public function exportJsaon(int $id, MatchsRepository $rep, EntityManagerInterface $em, CompetitionRepository $comp)
    {
        $matchs = $rep->trouveEquipesParCompetition($id);
        $competition = $comp->findOneById($id);
        $data = ['NomCompetition' => [$competition->getNom() => []]];
        foreach ($matchs as $match) {
            if (isset($match["horaire"])) {
                $fin = (clone $match["horaire"])->modify('+60 minutes');
                $data['NomCompetition'][$competition->getNom()][] = [
                    'Equipes' => $match["equipeA"] . " VS " . $match["equipeB"],
                    'DebutMatch' => $match["horaire"]->format('d/m/Y à H\hi'),
                    'FinMatch' => $fin->format('d/m/Y à H\hi'),
                    'Lieu' => $competition->getLieu()
                ];
            }
        }
        $json = json_encode($data);
        $response = new Response($json);
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Content-Disposition', 'attachment; filename="exportMatch.json"');
        return $response;
    }
}
