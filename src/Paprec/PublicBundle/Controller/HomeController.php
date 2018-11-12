<?php

namespace Paprec\PublicBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class HomeController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        $session = $this->get('session');


        $session->set('variable', 'valeur');
        $session->get('variable');


        return $this->render('@PaprecPublic/Default/index.html.twig');
    }
}
