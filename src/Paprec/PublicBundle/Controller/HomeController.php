<?php

namespace Paprec\PublicBundle\Controller;

use Paprec\PublicBundle\Entity\Cart;
use Paprec\PublicBundle\Service\CartManager;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class HomeController extends Controller
{
    /**
     * @Route("/step0", name="paprec_public_home_index")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function indexAction(Request $request)
    {
        $location = $request->get('l');
        $division = $request->get('d');
        $frequency = $request->get('f');
        $cartManager = $this->get('paprec.cart_manager');

        /**
         * step définie le prochain champ à afficher
         * Par défaut on est à la step l (location)
         * Quand l est définie on passe à l'étape d puis f
         */
        $step = "l";
        $divisions = $this->getParameter('paprec_divisions');
        if (isset($location)) {
            $step = "d";
        }
        if (isset($location) && isset($division) && !empty($division)) {
            $step = "f";
        }
        if (isset($location) && isset($division) && isset($frequency) && !empty($frequency)) {

            /**
             * On créé un Cart qui va porter les informations saisies et que l'on va passer aux SubscriptionControllers
             */
            $cart = $cartManager->add($location, $division, $frequency);


            switch ($cart->getDivision()) {
                case('DI'):
                    if ($cart->getFrequency() == 'ponctual') {
                        return $this->redirectToRoute('paprec_public_DI_subscription_step1', array(
                            'cartUuid' => $cart->getId()
                        ));
                    } else {

                    }
            }
        }


        return $this->render('@PaprecPublic/Home/index.html.twig', array(
            'divisions' => $divisions,
            'step' => $step
        ));
    }
}
