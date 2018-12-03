<?php

namespace Paprec\PublicBundle\Controller\Chantier;

use Paprec\CommercialBundle\Entity\ProductChantierOrder;
use Paprec\CommercialBundle\Entity\ProductChantierQuote;
use Paprec\CommercialBundle\Form\ProductChantierOrder\ProductChantierOrderDeliveryType;
use Paprec\CommercialBundle\Form\ProductChantierOrder\ProductChantierOrderShortType;
use Paprec\CommercialBundle\Form\ProductChantierQuote\ProductChantierQuoteShortType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class SubscriptionController extends Controller
{

    /**
     * @Route("/chantier/step0/{cartUuid}", name="paprec_public_corp_Chantier_subscription_step0")
     * @throws \Exception
     */
    public function step0Action(Request $request, $cartUuid)
    {
        $cartManager = $this->get('paprec.cart_manager');
        $cart = $cartManager->get($cartUuid);

        // Pour alimenter le "select" des types de déchets
        $divisions = array();
        foreach ($this->getParameter('paprec_divisions_select') as $division => $divisionLong) {
            $divisions[$division] = $divisionLong;
        }

        return $this->render('@PaprecPublic/Chantier/index.html.twig', array(
            'divisions' => $divisions,
            'cart' => $cart,

        ));
    }

    /**
     * @Route("/chantier/setOrder/{cartUuid}", name="paprec_public_corp_Chantier_subscription_setOrder")
     * @throws \Exception
     */
    public function setOrderAction(Request $request, $cartUuid)
    {
        $em = $this->getDoctrine()->getManager();
        $cartManager = $this->get('paprec.cart_manager');
        $cart = $cartManager->get($cartUuid);
        $cart->setType('order');
        $em->flush();

        return $this->redirectToRoute('paprec_public_corp_Chantier_subscription_step1', array(
            'cartUuid' => $cart->getId()
        ));
    }

    /**
     * @Route("/chantier/setQuote/{cartUuid}", name="paprec_public_corp_Chantier_subscription_setQuote")
     * @throws \Exception
     */
    public function setQuoteAction(Request $request, $cartUuid)
    {
        $em = $this->getDoctrine()->getManager();
        $cartManager = $this->get('paprec.cart_manager');
        $cart = $cartManager->get($cartUuid);
        $cart->setType('quote');
        $em->flush();

        return $this->redirectToRoute('paprec_public_corp_Chantier_subscription_step1', array(
            'cartUuid' => $cart->getId()
        ));
    }

    /**
     * Etape "Mon besoin", choix des produits et ajout au Cart
     *
     * On passe le $type en paramère qui correspond à 'order' (commande) ou 'quote'(devis)
     * @Route("/chantier/step1/{cartUuid}", name="paprec_public_corp_Chantier_subscription_step1")
     * @throws \Exception
     */
    public function step1Action(Request $request, $cartUuid)
    {
        $cartManager = $this->get('paprec.cart_manager');
        $categoryManager = $this->get('paprec_catalog.category_manager');
        $productChantierManager = $this->get('paprec_catalog.product_chantier_manager');


        $cart = $cartManager->get($cartUuid);
        $type = $cart->getType();

        // On récupère les catégoriesDI pour afficher le choix des catégories
        $categories = $categoryManager->getCategoriesChantier($type);

        // Pour alimenter le "select" des types de déchets
        $divisions = array();
        foreach ($this->getParameter('paprec_divisions_select') as $division => $divisionLong) {
            $divisions[$division] = $divisionLong;
        }

        /*
         * Si il y a des displayedCategories, il faut récupérer leurs produits pour les afficher
         */
        $productsCategories = array();
        foreach ($cart->getDisplayedCategories() as $displayedCategory) {
            $productsCategories[$displayedCategory] = $productChantierManager->findAvailables(array(
                'category' => $displayedCategory,
                'type' => $type,
                'postalCode' => $cart->getPostalCode()
            ));
        }

        return $this->render('@PaprecPublic/Chantier/need.html.twig', array(
            'divisions' => $divisions,
            'cart' => $cart,
            'categories' => $categories,
            'productsCategories' => $productsCategories
        ));
    }

    /**
     * Etape "Mes coordonnées"
     * où l'on créé le devis où la quote au submit du formulaire
     *
     * @Route("/chantier/step2/{cartUuid}", name="paprec_public_corp_Chantier_subscription_step2")
     * @throws \Exception
     */
    public function step2Action(Request $request, $cartUuid)
    {
        $type = $request->get('type');
        $cartManager = $this->get('paprec.cart_manager');
        $productChantierQuoteManager = $this->get('paprec_commercial.product_chantier_quote_manager');

        $cart = $cartManager->get($cartUuid);
        $type = $cart->getType();

        $postalCode =$cart->getPostalCode();
        $city = $cart->getCity();

        // si l'utilisateur est dans "J'établis un devis" alors on créé un devis Chantier
        if ($type == 'quote') {
            $productChantierQuote = new ProductChantierQuote();
            $productChantierQuote->setCity($city);
            $productChantierQuote->setPostalCode($postalCode);


            $form = $this->createForm(productChantierQuoteShortType::class, $productChantierQuote);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $productChantierQuoteManager = $this->get('paprec_commercial.product_chantier_quote_manager');

                $productChantierQuote = $form->getData();
                $productChantierQuote->setQuoteStatus('CREATED');
                $productChantierQuote->setFrequency($cart->getFrequency());

                $em = $this->getDoctrine()->getManager();
                $em->persist($productChantierQuote);
                $em->flush();

                // On récupère tous les produits ajoutés au Cart
                foreach ($cart->getContent() as $item) {
                    $productChantierQuoteManager->addLineFromCart($productChantierQuote, $item['pId'], $item['qtty'], $item['cId']);
                }

                return $this->redirectToRoute('paprec_public_corp_Chantier_subscription_step3', array(
                    'cartUuid' => $cart->getId(),
                    'quoteId' => $productChantierQuote->getId()
                ));

            }
        } else { // sinon on créé une commande Chantier
            $productChantierOrderManager = $this->get('paprec_commercial.product_chantier_order_manager');


            $productChantierOrder = new ProductChantierOrder();
            $productChantierOrder->setCity($city);
            $productChantierOrder->setPostalCode($postalCode);

            $form = $this->createForm(ProductChantierOrderShortType::class, $productChantierOrder);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $productChantierOrder = $form->getData();
                $productChantierOrder->setOrderStatus('CREATED');

                $em = $this->getDoctrine()->getManager();
                $em->persist($productChantierOrder);
                $em->flush();

                // On récupère tous les produits ajoutés au Cart
                foreach ($cart->getContent() as $item) {
                    $productChantierOrderManager->addLineFromCart($productChantierOrder, $item['pId'], $item['qtty'], $item['cId']);
                }

                return $this->redirectToRoute('paprec_public_corp_Chantier_subscription_step4', array(
                    'cartUuid' => $cart->getId(),
                    'orderId' => $productChantierOrder->getId()
                ));
            }

        }
        return $this->render('@PaprecPublic/Chantier/contactDetails.html.twig', array(
            'cart' => $cart,
            'form' => $form->createView()
        ));
    }

    /**
     * Etape "Mon offre" qui récapitule le de vis créé par l'utilisateur
     *
     * @Route("/chantier/step3/{cartUuid}/{quoteId}", name="paprec_public_corp_Chantier_subscription_step3")
     */
    public function step3Action(Request $request, $cartUuid, $quoteId)
    {
        $cartManager = $this->get('paprec.cart_manager');
        $em = $this->getDoctrine()->getManager();

        $productChantierQuote = $em->getRepository('PaprecCommercialBundle:ProductChantierQuote')->find($quoteId);
        $cart = $cartManager->get($cartUuid);

        return $this->render('@PaprecPublic/Chantier/offerDetails.html.twig', array(
            'productChantierQuote' => $productChantierQuote,
            'cart' => $cart
        ));
    }


    /**
     * Etape "Ma livraison" qui est encore un formulaire complétant les infos du productChantierOrder
     *
     * @Route("/chantier/step4/{cartUuid}/{orderId}", name="paprec_public_corp_Chantier_subscription_step4")
     */
    public function step4Action(Request $request, $cartUuid, $orderId)
    {

        $em = $this->getDoctrine()->getManager();
        $cartManager = $this->get('paprec.cart_manager');

        $cart = $cartManager->get($cartUuid);
        $productChantierOrder = $em->getRepository('PaprecCommercialBundle:ProductChantierOrder')->find($orderId);
        $form = $this->createForm(ProductChantierOrderDeliveryType::class, $productChantierOrder);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $productChantierOrder = $form->getData();
            $em->merge($productChantierOrder);
            $em->flush();

            return $this->redirectToRoute('paprec_public_corp_Chantier_subscription_step5', array(
                'cartUuid' => $cart->getId(),
                'orderId' => $productChantierOrder->getId()
            ));
        }
        return $this->render('@PaprecPublic/Chantier/delivery.html.twig', array(
            'cart' => $cart,
            'productChantierOrder' => $productChantierOrder,
            'form' => $form->createView()
        ));
    }


    /**
     * Etape "Mon paiement" qui est encore un formulaire complétant les infos du productChantierOrder
     *
     * @Route("/chantier/step5/{cartUuid}/{orderId}", name="paprec_public_corp_Chantier_subscription_step5")
     */
    public function step5Action(Request $request, $cartUuid, $orderId)
    {

        $em = $this->getDoctrine()->getManager();
        $cartManager = $this->get('paprec.cart_manager');

        $cart = $cartManager->get($cartUuid);
        $productChantierOrder = $em->getRepository('PaprecCommercialBundle:ProductChantierOrder')->find($orderId);
//        $form = $this->createForm(ProductChantierOrderDeliveryType::class, $productChantierOrder);
//
//        $form->handleRequest($request);
//        if ($form->isSubmitted() && $form->isValid()) {
//
//            $productChantierOrder = $form->getData();
//            $em->merge($productChantierOrder);
//            $em->flush();
//
//            return $this->redirectToRoute(paprec_public_corp_Chantier_subscription_step5, array(
//                'cartUuid' => $cart->getId(),
//                'orderId' => $productChantierOrder->getId()
//            ));
//        }
        return $this->render('@PaprecPublic/Chantier/payment.html.twig', array(
            'cart' => $cart,
            'productChantierOrder' => $productChantierOrder
//            'form' => $form->createView()
        ));
    }


    /**
     * Au clic sur une catégorie, on l'ajoute ou on la supprime des catégories affichées
     *
     * @Route("/chantier/addDisplayedCategory/{cartUuid}/{categoryId}", name="paprec_public_corp_Chantier_subscription_addDisplayedCategory")
     * @throws \Exception
     */
    public function addDisplayedCategoryAction(Request $request, $cartUuid, $categoryId)
    {
        $cartManager = $this->get('paprec.cart_manager');

        // On ajoute ou on supprime la catégorie sélecionnée au tableau des displayedCategories du Cart
        $cart = $cartManager->addOrRemoveDisplayedCategory($cartUuid, $categoryId);

        return $this->redirectToRoute('paprec_public_corp_Chantier_subscription_step1', array(
            'cartUuid' => $cart->getId()
        ));
    }

    /**
     * Ajoute au cart un displayedProduct avec en key => value( categoryId => productId)
     *
     * @Route("/chantier/addOrRemoveDisplayedProduct/{cartUuid}/{categoryId}/{productId}", name="paprec_public_corp_Chantier_subscription_addOrRemoveDisplayedProduct")
     * @throws \Exception
     */
    public function addOrRemoveDisplayedProductAction(Request $request, $cartUuid, $categoryId, $productId)
    {
        $cartManager = $this->get('paprec.cart_manager');

        // On ajoute ou on supprime le produit sélecionné au tableau des displayedCategories du Cart
        $cart = $cartManager->addOrRemoveDisplayedProduct($cartUuid, $categoryId, $productId);

        return $this->redirectToRoute('paprec_public_corp_Chantier_subscription_step1', array(
            'cartUuid' => $cart->getId()
        ));
    }

    /**
     * Ajoute au cart un Product avec sa quantité et  sa catégorie
     *
     * @Route("/chantier/addContent/{cartUuid}/{categoryId}/{productId}/{quantity}", name="paprec_public_corp_Chantier_subscription_addContent")
     * @throws \Exception
     */
    public function addContentAction(Request $request, $cartUuid, $categoryId, $productId, $quantity)
    {
        $cartManager = $this->get('paprec.cart_manager');

        // On ajoute ou on supprime le produit sélecionné au tableau des displayedCategories du Cart
        $cart = $cartManager->addContent($cartUuid, $categoryId, $productId, $quantity);

        return new JsonResponse('200');
    }

    /**
     * Supprime un Product du contenu du Cart
     *
     * @Route("/chantier/removeContent/{cartUuid}/{categoryId}/{productId}", name="paprec_public_corp_Chantier_subscription_removeContent")
     * @throws \Exception
     */
    public function removeContentAction(Request $request, $cartUuid, $categoryId, $productId)
    {
        $cartManager = $this->get('paprec.cart_manager');

        // On ajoute ou on supprime le produit sélecionné au tableau des displayedCategories du Cart
        $cart = $cartManager->removeContent($cartUuid, $categoryId, $productId);

        return new JsonResponse($cart->getContent());
    }

    /**
     * Retourne le twig.html du cart avec les produits dans celui-ci ainsi que le montant total
     *
     * @Route("/chantier/loadCart/{cartUuid}", name="paprec_public_corp_Chantier_subscription_loadCart", condition="request.isXmlHttpRequest()")
     * @throws \Exception
     */
    public function loadCartAction(Request $request, $cartUuid)
    {
        $cartManager = $this->get('paprec.cart_manager');

        // On récupère les informations du cart à afficher ainsi que le calcul de la somme du Cart
        $loadedCart = $cartManager->loadCartChantier($cartUuid);

        return $this->render('@PaprecPublic/Chantier/partial/cartPartial.html.twig', array(
            'loadedCart' => $loadedCart
        ));
    }

    /**
     * Retourne le twig des agences proches
     * @Route("/chantier/loadNearbyAgencies/{cartUuid}", name="paprec_public_corp_Chantier_subscription_loadNearbyAgencies", condition="request.isXmlHttpRequest()")
     */
    public function loadNearbyAgenciesAction(Request $request, $cartUuid) {
        $cartManager = $this->get('paprec.cart_manager');
        $agencyManager = $this->get('paprec_commercial.agency_manager');

        $cart = $cartManager->get($cartUuid);
        $distance  = 50;
        $nbAgencies = $agencyManager->getNearbyAgencies($cart->getLongitude(), $cart->getLatitude(), 'CHANTIER', $distance);

        return $this->render('@PaprecPublic/Shared/partial/nearbyAgencies.html.twig', array(
            'nbAgencies' => $nbAgencies,
            'distance' => $distance
        ));
    }

}