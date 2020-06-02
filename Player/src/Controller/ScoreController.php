<?php
    namespace App\Controller;

    use App\Entity\Game;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Annotation\Route;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\R;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

    class ScoreController extends AbstractController{
        /**
         * @Route("/scores", name="scores")
         */
        public function index(){
            $gameRepository = $this->getDoctrine()->getRepository(Game::class);

            $games = $gameRepository->findBy(['player' => $this->getUser()]);


            return $this->render('scores/index.html.twig', [
                'games' => $games
            ]);
        }
        /**
         * @Route("/deleteGame/{id}", name="delete")
         */
        public function deleteGame($id){
            $gameRepository = $this->getDoctrine()->getRepository(Game::class);
            $game = $gameRepository->findOneBy(['player' => $this->getUser(), 'id' => $id]);

            $em = $this->getDoctrine()->getManager();
            $em->remove($game);
            $em->flush();
        }

    }