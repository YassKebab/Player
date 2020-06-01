<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\Music;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\R;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GameController extends AbstractController
{
    /**
     * @Route("/index", name="game")
     */
    public function index()
    {
        $gameRepository = $this->getDoctrine()->getRepository(Game::class);
        $musicRepository = $this->getDoctrine()->getRepository(Music::class);
        //check si on a une game déjà présente
        if ($gameRepository->findOneBy(['player' => $this->getUser(), 'score' => null]) != null) {
            $game = $gameRepository->findOneBy(['player' => $this->getUser(), 'score' => null]);
        } else {
            $game = new Game();
            $game->setPlayer($this->getUser());
            $game->setDate(new \DateTime());

            $listRightMusic = [];
            $nbMusic = $musicRepository->countAllMusic();

            for ($i = 0; $i < Game::nbMaxStep; $i++) {
                $randMusicNb = rand(1, $nbMusic);
                $randMusic = $musicRepository->findOneBy(['id' => $randMusicNb])->getId();
                $listRightMusic [] = $randMusic;
            }
            $game->setMusicsRightAnswer($listRightMusic);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($game);
            $entityManager->flush();
        }

        return $this->render('player/index.html.twig', [
            'game' => $game,
        ]);
    }

    /**
     * @Route("/game", name="play")
     * @param Request $request
     *
     * @return Response
     */
    public function play(Request $request)
    {

        $gameRepository = $this->getDoctrine()->getRepository(Game::class);
        $musicRepository = $this->getDoctrine()->getRepository(Music::class);

        if ($gameRepository->findOneBy(['player' => $this->getUser(), 'score' => null]) == null) {
            $this->redirectToRoute('game');
        }

        /** @var Game $game */
        $game = $gameRepository->findOneBy(['player' => $this->getUser(), 'score' => null]);

        /*On cherche l'etape à laquelle on est */
        $musicSelected = $game->getMusicsSelected();
        $step = count($musicSelected);
        var_dump($step);
        if (Request::METHOD_POST === $request->getMethod()) {
            $olderSelectedMusic = $game->getMusicsSelected();
            $olderSelectedMusic[] = $request->get('form')[ 'selectedAnswer' ];
            $game->setMusicsSelected($olderSelectedMusic);

            $em = $this->getDoctrine()->getManager();
            $em->persist($game);
            $em->flush();
            if($step < Game::nbMaxStep-1) {
                return $this->redirect($this->generateUrl('play'));
            } else {
                return $this->redirect($this->generateUrl('finalscore'));
            }
        }

        $possibleAnswers = [];
        $nbMusic = $musicRepository->countAllMusic();

        $allMusics = $musicRepository->findAllExpectOne($game->getMusicsRightAnswer()[ $step ]);
        for ($i = 0; $i < Game::nbMaxError; $i++) {
            $randMusicNb = rand(0, $nbMusic - 2);
            $possibleAnswers [] = $allMusics [ $randMusicNb ];
        }
        $possibleAnswers [] = $musicRepository->findOneBy(['id' => $game->getMusicsRightAnswer()[ $step ]]);

        shuffle($possibleAnswers);
        $form = $this->createFormBuilder()
            ->add('selectedAnswer', ChoiceType::class, [
                'choices' => $possibleAnswers,
                'choice_label' => function ($choice, $key, $value) {
                    return $choice->getNom();
                },
                'choice_value' => function (?Music $entity) {
                    return $entity ? $entity->getId() : '';
                },
                'data' => $possibleAnswers[ 0 ],
                'expanded' => true,
                'multiple' => false,
                'required' => true,
            ])
            ->add('save', SubmitType::class, ['label' => 'Next'])
            ->getForm();

        return $this->render('player/play.html.twig', [
            'step' => $step,
            'link' => $musicRepository->findOneBy(['id' => $game->getMusicsRightAnswer() [$step]])->getLink(),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/finalscore", name="finalscore")
     * @param Request $request
     *
     * @return Response
     */
    public function finalscore(Request $request)
    {
        $gameRepository = $this->getDoctrine()->getRepository(Game::class);

        /** @var Game $game */
        $game = $gameRepository->findOneBy(['player' => $this->getUser(), 'score' => null]);
        $score = 0;

        for($i = 0; $i < Game::nbMaxStep; $i++){
            if($game->getMusicsSelected() [$i] == $game->getMusicsRightAnswer() [$i]){
                $score++;
            }
        }

        $game->setScore($score);

        $em = $this->getDoctrine()->getManager();
        $em->persist($game);
        $em->flush();

        return $this->render('player/score.html.twig', [
            'game' => $game
        ]);
    }
}