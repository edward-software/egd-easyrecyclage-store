<?php

namespace Paprec\PublicBundle\Controller\Chantier;

use Paprec\CommercialBundle\Entity\ProductChantierQuote;
use Paprec\CommercialBundle\Form\ProductChantierQuoteShortType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class SubscriptionController extends Controller
{

    /**
     * @Route("/chantier/step0/{cartUuid}", name="paprec_public_Chantier_subscription_step0")
     * @throws \Exception
     */
    public function step0Action(Request $request, $cartUuid)
    {
        $cartManager = $this->get('paprec.cart_manager');
        $cart = $cartManager->get($cartUuid);

        // Pour alimenter le "select" des types de déchets
        $divisions = $this->getParameter('paprec_divisions');

        return $this->render('@PaprecPublic/Chantier/index.html.twig', array(
            'divisions' => $divisions,
            'cart' => $cart,

        ));
    }

    /**
     * @Route("/chantier/setOrder/{cartUuid}", name="paprec_public_Chantier_subscription_setOrder")
     * @throws \Exception
     */
    public function setOrderAction(Request $request, $cartUuid)
    {
        $em = $this->getDoctrine()->getManager();
        $cartManager = $this->get('paprec.cart_manager');
        $cart = $cartManager->get($cartUuid);
        $cart->setType('order');
        $em->flush();

        return $this->redirectToRoute('paprec_public_Chantier_subscription_step1', array(
            'cartUuid' => $cart->getId()
        ));
    }

    /**
     * @Route("/chantier/setQuote/{cartUuid}", name="paprec_public_Chantier_subscription_setQuote")
     * @throws \Exception
     */
    public function setQuoteAction(Request $request, $cartUuid)
    {
        $em = $this->getDoctrine()->getManager();
        $cartManager = $this->get('paprec.cart_manager');
        $cart = $cartManager->get($cartUuid);
        $cart->setType('quote');
        $em->flush();

        return $this->redirectToRoute('paprec_public_Chantier_subscription_step1', array(
            'cartUuid' => $cart->getId()
        ));
    }

    /**
     * On passe le $type en paramère qui correspond à 'order' (commande) ou 'quote'(devis)
     * @Route("/chantier/step1/{cartUuid}", name="paprec_public_Chantier_subscription_step1")
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
        $divisions = $this->getParameter('paprec_divisions');

        /*
         * Si il y a des displayedCategories, il faut récupérer leurs produits pour les afficher
         */
        $productsCategories = array();
        foreach ($cart->getDisplayedCategories() as $displayedCategory) {
            $productsCategories[$displayedCategory] = $productChantierManager->getByCategory($displayedCategory, $type);
        }

        return $this->render('@PaprecPublic/Chantier/need.html.twig', array(
            'divisions' => $divisions,
            'cart' => $cart,
            'categories' => $categories,
            'productsCategories' => $productsCategories
        ));
    }

    /**
     * Etape du formulaire des informations contact
     * @Route("/chantier/step2/{cartUuid}", name="paprec_public_Chantier_subscription_step2")
     * @throws \Exception
     */
    public function step2Action(Request $request, $cartUuid)
    {
        $type = $request->get('type');
        $cartManager = $this->get('paprec.cart_manager');
        $productChantierQuoteManager = $this->get('paprec_catalog.product_chantier_quote_manager');

        $cart = $cartManager->get($cartUuid);
        $type = $cart->getType();

        $postalCode = substr($cart->getLocation(), 0, 5);
        $city = substr($cart->getLocation(), 5);

        if($type == 'quote') {
            $productChantierQuote = new productChantierQuote();
            $productChantierQuote->setCity($city);
            $productChantierQuote->setPostalCode($postalCode);


            $form = $this->createForm(productChantierQuoteShortType::class, $productChantierQuote);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $productChantierQuote = $form->getData();
                $productChantierQuote->setQuoteStatus('Créé');
                $productChantierQuote->setFrequency($cart->getFrequency());

                $em = $this->getDoctrine()->getManager();
                $em->persist($productChantierQuote);
                $em->flush();

                // On récupère tous les produits ajoutés au Cart
                foreach ($cart->getContent() as $item) {
                    $productChantierQuoteManager->addLineFromCart($productChantierQuote, $item['pId'], $item['qtty'], $item['cId']);
                }

                return $this->redirectToRoute('paprec_public_Chantier_subscription_step3', array(
                    'cartUuid' => $cart->getId(),
                    'quoteId' => $productChantierQuote->getId()
                ));

            }
        } else {

        }
        return $this->render('@PaprecPublic/Chantier/contactDetails.html.twig', array(
            'cart' => $cart,
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/chantier/step3/{cartUuid}/{quoteId}", name="paprec_public_Chantier_subscription_step3")
     */
    public function step3Action(Request $request, $cartUuid, $quoteId)
    {
        $cartManager = $this->get('paprec.cart_manager');
        $em = $this->getDoctrine()->getManager();

        $productChantierQuote = $em->getRepository('PaprecCommercialBundle:ProductChantierQuote')->find($quoteId);
        $cart = $cartManager->get($cartUuid);

        return $this->render('@PaprecPublic/Chantier/offerDetails.html.twig', array(
            'productChantierQuote' => $productChantierQuote
        ));
    }


    /**
     * @Route("/chantier/addDisplayedCategory/{cartUuid}/{categoryId}", name="paprec_public_Chantier_subscription_addDisplayedCategory")
     * @throws \Exception
     */
    public function addDisplayedCategoryAction(Request $request, $cartUuid, $categoryId)
    {
        $cartManager = $this->get('paprec.cart_manager');

        // On ajoute ou on supprime la catégorie sélecionnée au tableau des displayedCategories du Cart
        $cart = $cartManager->addOrRemoveDisplayedCategory($cartUuid, $categoryId);

        return $this->redirectToRoute('paprec_public_Chantier_subscription_step1', array(
            'cartUuid' => $cart->getId()
        ));
    }

    /**
     * Ajoute au cart un displayedProduct avec en key => value( categoryId => productId)
     * @Route("/chantier/addOrRemoveDisplayedProduct/{cartUuid}/{categoryId}/{productId}", name="paprec_public_Chantier_subscription_addOrRemoveDisplayedProduct")
     * @throws \Exception
     */
    public function addOrRemoveDisplayedProductAction(Request $request, $cartUuid, $categoryId, $productId)
    {
        $cartManager = $this->get('paprec.cart_manager');

        // On ajoute ou on supprime le produit sélecionné au tableau des displayedCategories du Cart
        $cart = $cartManager->addOrRemoveDisplayedProduct($cartUuid, $categoryId, $productId);

        return $this->redirectToRoute('paprec_public_Chantier_subscription_step1', array(
            'cartUuid' => $cart->getId()
        ));
    }

    /**
     * Ajoute au cart un product
     * @Route("/chantier/addContent/{cartUuid}/{categoryId}/{productId}/{quantity}", name="paprec_public_Chantier_subscription_addContent")
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
     * Ajoute au cart un displayedProduct avec en key => value( categoryId => productId)
     * @Route("/chantier/removeContent/{cartUuid}/{categoryId}/{productId}", name="paprec_public_Chantier_subscription_removeContent")
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
     * Retourne le twig du cart avec les produits dans celui-ci ainsi que le montant total
     * @Route("/chantier/loadCart/{cartUuid}", name="paprec_public_Chantier_subscription_loadCart", condition="request.isXmlHttpRequest()")
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

}
