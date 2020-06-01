<?php
    namespace App\Controller;

    use App\Entity\Game;
    use App\Entity\Music;
    use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
    use Symfony\Component\Form\Extension\Core\Type\SubmitType;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Annotation\Route;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\R;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

    class GameController extends AbstractController{
        /**
         * @Route("/index", name="game")
         */
        public function index(){
            $gameRepository = $this->getDoctrine()->getRepository(Game::class);
            $musicRepository = $this->getDoctrine()->getRepository(Music::class);
            //check si on a une game déjà présente
            if($gameRepository->findOneBy(['player'=>$this->getUser(), 'score'=>null]) != NULL){
                $game = $gameRepository->findOneBy(['player'=>$this->getUser(), 'score'=>null]);
            } else {
                $game = new Game();
                $game->setPlayer($this->getUser());
                $game->setDate(new \DateTime());

                $listRightMusic = [];
                $nbMusic = $musicRepository->countAllMusic();

                for($i = 0; $i < Game::nbMaxStep; $i++){
                    $randMusicNb = rand(1, $nbMusic);
                    $randMusic = $musicRepository->findOneBy(['id'=>$randMusicNb])->getId();
                    $listRightMusic [] = $randMusic;
                }
                $game->setMusicsRightAnswer($listRightMusic);

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($game);
                $entityManager->flush();
            }

            return $this->render('player/index.html.twig',  [
                'game'=>$game
            ]);
        }

        /**
         * @Route("/game", name="play")
         *
         */
        public function play(){
            $gameRepository = $this->getDoctrine()->getRepository(Game::class);
            $musicRepository = $this->getDoctrine()->getRepository(Music::class);

            if($gameRepository->findOneBy(['player'=>$this->getUser(), 'score'=>null]) == NULL){
                $this->redirectToRoute('game');
            }

            /** @var Game $game */
            $game = $gameRepository->findOneBy(['player'=>$this->getUser(), 'score'=>null]);

            /*On cherche l'etape à laquelle on est */
            $musicSelected = $game->getMusicsSelected();
            $step = count($musicSelected);

            $possibleAnswers = [];
            $nbMusic = $musicRepository->countAllMusic();
            $allMusics = $musicRepository->findAllExpectOne($game->getMusicsRightAnswer()[$step]);
            for($i = 0; $i < Game::nbMaxError; $i++){
                $randMusicNb = rand(0, $nbMusic-1);
                $possibleAnswers [] = $allMusics [$randMusicNb];
            }
            $possibleAnswers [] = $musicRepository->findOneBy(['id'=>$game->getMusicsRightAnswer()[$step]]);
            shuffle($possibleAnswers);
            $form = $this->createFormBuilder()
                ->add('selectedAnswer', ChoiceType::class, [
                    'choices'=> $possibleAnswers,
                    'choice_label' => function ($choice, $key, $value)
                    {
                        return $choice->getNom();
                    },
                    'choice_value' => function (Music $entity) {
                        return $entity->getId();
                    },
                    'expanded'=> true,
                    'multiple'=> false
                ])
                ->add('save', SubmitType::class, ['label' => 'Next'])
                ->getForm();
            if($form->isSubmitted() && $form->isValid()){
                $selectedAnswer = $form->get('selectedAnswer')->getData();
                $game->setMusicsSelected($game->getMusicsSelected() [] = $selectedAnswer->getId());
            }

            return $this->render('player/play.html.twig', [
                'step'=>$step,
                'form'=>$form->createView()
            ]);
        }
    }