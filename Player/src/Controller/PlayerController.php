<?php
    namespace App\Controller;

    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Annotation\Route;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\R;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

    class PlayerController extends AbstractController{
        /**
         * @Route("/index", name="game")
         */
        public function index(){

            return $this->render('player/index.html.twig');
        }
    }