<?php
    namespace App\Controller;

    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Annotation\Route;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\R;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

    class PlayerController extends AbstractController{
        /**
         * @Route("/")
         */
        public function index(){
            //return new Response('<html><body>Hello</body></html>');
            return $this->render('player/index.html.twig', array('name' => 'Bard'));
        }
    }