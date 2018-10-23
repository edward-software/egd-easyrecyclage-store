<?php

namespace Paprec\HomeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class HomeController extends Controller
{
    /**
     * @Route("/", name="paprec_home_dashboard")
     * @Security("has_role('ROLE_USER')")
     */
    public function indexAction()
    {
        return $this->render('PaprecHomeBundle:Home:index.html.twig');
    }
}
