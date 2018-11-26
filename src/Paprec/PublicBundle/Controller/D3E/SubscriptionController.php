<?php

namespace Paprec\PublicBundle\Controller\D3E;

use Paprec\CommercialBundle\Entity\ProductD3EOrder;
use Paprec\CommercialBundle\Entity\ProductD3EQuote;
use Paprec\CommercialBundle\Form\ProductD3EOrderDeliveryType;
use Paprec\CommercialBundle\Form\ProductD3EOrderShortType;
use Paprec\CommercialBundle\Form\ProductD3EQuoteShortType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class SubscriptionController extends Controller
{

    /**
     * @Route("/D3E/step0/{cartUuid}", name="paprec_public_corp_D3E_subscription_step0")
     * @throws \Exception
     */
    public function step0Action(Request $request, $cartUuid)
    {
        $cartManager = $this->get('paprec.cart_manager');
        $cart = $cartManager->get($cartUuid);

        // Pour alimenter le "select" des types de déchets
        $divisions = $this->getParameter('paprec_divisions');

        return $this->render('@PaprecPublic/D3E/index.html.twig', array(
            'divisions' => $divisions,
            'cart' => $cart,

        ));
    }

    /**
     * @Route("/D3E/setOrder/{cartUuid}", name="paprec_public_corp_D3E_subscription_setOrder")
     * @throws \Exception
     */
    public function setOrderAction(Request $request, $cartUuid)
    {
        $em = $this->getDoctrine()->getManager();
        $cartManager = $this->get('paprec.cart_manager');
        $cart = $cartManager->get($cartUuid);
        $cart->setType('order');
        $em->flush();

        return $this->redirectToRoute('paprec_public_corp_D3E_subscription_step1', array(
            'cartUuid' => $cart->getId()
        ));
    }

    /**
     * @Route("/D3E/setQuote/{cartUuid}", name="paprec_public_corp_D3E_subscription_setQuote")
     * @throws \Exception
     */
    public function setQuoteAction(Request $request, $cartUuid)
    {
        $em = $this->getDoctrine()->getManager();
        $cartManager = $this->get('paprec.cart_manager');
        $cart = $cartManager->get($cartUuid);
        $cart->setType('quote');
        $em->flush();

        return $this->redirectToRoute('paprec_public_corp_D3E_subscription_step1', array(
            'cartUuid' => $cart->getId()
        ));
    }

    /**
     * Etape "Mon besoin", choix des produits et ajout au Cart
     *
     * On passe le $type en paramère qui correspond à 'order' (commande) ou 'quote'(devis)
     * @Route("/D3E/step1/{cartUuid}", name="paprec_public_corp_D3E_subscription_step1")
     * @throws \Exception
     */
    public function step1Action(Request $request, $cartUuid)
    {
        $cartManager = $this->get('paprec.cart_manager');
        $productD3EManager = $this->get('paprec_catalog.product_D3E_manager');


        $cart = $cartManager->get($cartUuid);
        $type = $cart->getType();

        // On récupère les catégoriesDI pour afficher le choix des catégories
        $products = $productD3EManager->getByType($type);

        // Pour alimenter le "select" des types de déchets
        $divisions = $this->getParameter('paprec_divisions');

        return $this->render('@PaprecPublic/D3E/need.html.twig', array(
            'divisions' => $divisions,
            'cart' => $cart,
            'products' => $products
        ));
    }

    /**
     * Etape "Mes coordonnées"
     * où l'on créé le devis où la quote au submit du formulaire
     *
     * @Route("/D3E/step2/{cartUuid}", name="paprec_public_corp_D3E_subscription_step2")
     * @throws \Exception
     */
    public function step2Action(Request $request, $cartUuid)
    {
        $cartManager = $this->get('paprec.cart_manager');

        $cart = $cartManager->get($cartUuid);
        $type = $cart->getType();

        $postalCode = substr($cart->getLocation(), 0, 5);
        $city = substr($cart->getLocation(), 5);

        // si l'utilisateur est dans "J'établis un devis" alors on créé un devis D3E
        if ($type == 'quote') {
            $productD3EQuote = new ProductD3EQuote();
            $productD3EQuote->setCity($city);
            $productD3EQuote->setPostalCode($postalCode);


            $form = $this->createForm(productD3EQuoteShortType::class, $productD3EQuote);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $productD3EQuoteManager = $this->get('paprec_catalog.product_d3e_quote_manager');

                $productD3EQuote = $form->getData();
                $productD3EQuote->setQuoteStatus('Créé');
                $productD3EQuote->setFrequency($cart->getFrequency());

                $em = $this->getDoctrine()->getManager();
                $em->persist($productD3EQuote);
                $em->flush();

                // On récupère tous les produits ajoutés au Cart
                foreach ($cart->getContent() as $item) {
                    $productD3EQuoteManager->addLineFromCart($productD3EQuote, $item['pId'], $item['qtty']);
                }

                return $this->redirectToRoute('paprec_public_corp_D3E_subscription_step3', array(
                    'cartUuid' => $cart->getId(),
                    'quoteId' => $productD3EQuote->getId()
                ));

            }
        } else { // sinon on créé une commande D3E
            $productD3EOrderManager = $this->get('paprec_catalog.product_d3e_order_manager');


            $productD3EOrder = new ProductD3EOrder();
            $productD3EOrder->setCity($city);
            $productD3EOrder->setPostalCode($postalCode);

            $form = $this->createForm(ProductD3EOrderShortType::class, $productD3EOrder);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $productD3EOrder = $form->getData();
                $productD3EOrder->setOrderStatus('Créée');

                $em = $this->getDoctrine()->getManager();
                $em->persist($productD3EOrder);
                $em->flush();

                // On récupère tous les produits ajoutés au Cart
                foreach ($cart->getContent() as $item) {
                    $productD3EOrderManager->addLineFromCart($productD3EOrder, $item['pId'], $item['qtty']);
                }

                return $this->redirectToRoute('paprec_public_corp_D3E_subscription_step4', array(
                    'cartUuid' => $cart->getId(),
                    'orderId' => $productD3EOrder->getId()
                ));
            }

        }
        return $this->render('@PaprecPublic/D3E/contactDetails.html.twig', array(
            'cart' => $cart,
            'form' => $form->createView()
        ));
    }

    /**
     * Etape "Mon offre" qui récapitule le de vis créé par l'utilisateur
     *
     * @Route("/D3E/step3/{cartUuid}/{quoteId}", name="paprec_public_corp_D3E_subscription_step3")
     */
    public function step3Action(Request $request, $cartUuid, $quoteId)
    {
        $cartManager = $this->get('paprec.cart_manager');
        $em = $this->getDoctrine()->getManager();

        $productD3EQuote = $em->getRepository('PaprecCommercialBundle:ProductD3EQuote')->find($quoteId);
        $cart = $cartManager->get($cartUuid);

        return $this->render('@PaprecPublic/D3E/offerDetails.html.twig', array(
            'productD3EQuote' => $productD3EQuote
        ));
    }


    /**
     * Etape "Ma livraison" qui est encore un formulaire complétant les infos du productD3EOrder
     *
     * @Route("/D3E/step4/{cartUuid}/{orderId}", name="paprec_public_corp_D3E_subscription_step4")
     */
    public function step4Action(Request $request, $cartUuid, $orderId)
    {

        $em = $this->getDoctrine()->getManager();
        $cartManager = $this->get('paprec.cart_manager');

        $cart = $cartManager->get($cartUuid);
        $productD3EOrder = $em->getRepository('PaprecCommercialBundle:ProductD3EOrder')->find($orderId);
        $form = $this->createForm(ProductD3EOrderDeliveryType::class, $productD3EOrder);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $productD3EOrder = $form->getData();
            $em->merge($productD3EOrder);
            $em->flush();

            return $this->redirectToRoute('paprec_public_corp_D3E_subscription_step5', array(
                'cartUuid' => $cart->getId(),
                'orderId' => $productD3EOrder->getId()
            ));
        }
        return $this->render('@PaprecPublic/D3E/delivery.html.twig', array(
            'cart' => $cart,
            'productD3EOrder' => $productD3EOrder,
            'form' => $form->createView()
        ));
    }


    /**
     * Etape "Mon paiement" qui est encore un formulaire complétant les infos du productD3EOrder
     *
     * @Route("/D3E/step5/{cartUuid}/{orderId}", name="paprec_public_corp_D3E_subscription_step5")
     */
    public function step5Action(Request $request, $cartUuid, $orderId)
    {

        $em = $this->getDoctrine()->getManager();
        $cartManager = $this->get('paprec.cart_manager');

        $cart = $cartManager->get($cartUuid);
        $productD3EOrder = $em->getRepository('PaprecCommercialBundle:ProductD3EOrder')->find($orderId);
//        $form = $this->createForm(ProductD3EOrderDeliveryType::class, $productD3EOrder);
//
//        $form->handleRequest($request);
//        if ($form->isSubmitted() && $form->isValid()) {
//
//            $productD3EOrder = $form->getData();
//            $em->merge($productD3EOrder);
//            $em->flush();
//
//            return $this->redirectToRoute(paprec_public_corp_D3E_subscription_step5, array(
//                'cartUuid' => $cart->getId(),
//                'orderId' => $productD3EOrder->getId()
//            ));
//        }
        return $this->render('@PaprecPublic/D3E/payment.html.twig', array(
            'cart' => $cart,
            'productD3EOrder' => $productD3EOrder
//            'form' => $form->createView()
        ));
    }


    /**
     * Ajoute au cart un displayedProduct
     *
     * @Route("/D3E/addOrRemoveDisplayedProduct/{cartUuid}/{productId}", name="paprec_public_corp_D3E_subscription_addOrRemoveDisplayedProduct")
     * @throws \Exception
     */
    public function addOrRemoveDisplayedProductAction(Request $request, $cartUuid, $productId)
    {
        $cartManager = $this->get('paprec.cart_manager');

        // On ajoute ou on supprime le produit sélecionné au tableau des displayedProduct du Cart
        $cart = $cartManager->addOrRemoveDisplayedProductD3E($cartUuid, $productId);

        return $this->redirectToRoute('paprec_public_corp_D3E_subscription_step1', array(
            'cartUuid' => $cart->getId()
        ));
    }

    /**
     * Ajoute au cart un Product avec sa quantité
     *
     * @Route("/D3E/addContent/{cartUuid}/{productId}/{quantity}", name="paprec_public_corp_D3E_subscription_addContent")
     * @throws \Exception
     */
    public function addContentAction(Request $request, $cartUuid, $productId, $quantity)
    {
        $cartManager = $this->get('paprec.cart_manager');

        $cart = $cartManager->addContentD3E($cartUuid, $productId, $quantity);

        return new JsonResponse('200');
    }

    /**
     * Supprime un Product du contenu du Cart
     *
     * @Route("/D3E/removeContent/{cartUuid}/{productId}", name="paprec_public_corp_D3E_subscription_removeContent")
     * @throws \Exception
     */
    public function removeContentAction(Request $request, $cartUuid, $productId)
    {
        $cartManager = $this->get('paprec.cart_manager');

        // On ajoute ou on supprime le produit sélecionné au tableau des displayedCategories du Cart
        $cart = $cartManager->removeContentD3E($cartUuid, $productId);

        return new JsonResponse($cart->getContent());
    }

    /**
     * Retourne le twig.html du cart avec les produits dans celui-ci ainsi que le montant total
     *
     * @Route("/D3E/loadCart/{cartUuid}", name="paprec_public_corp_D3E_subscription_loadCart", condition="request.isXmlHttpRequest()")
     * @throws \Exception
     */
    public function  loadCartAction(Request $request, $cartUuid)
    {
        $cartManager = $this->get('paprec.cart_manager');

        // On récupère les informations du cart à afficher ainsi que le calcul de la somme du Cart
        $loadedCart = $cartManager->loadCartD3E($cartUuid);

        return $this->render('@PaprecPublic/D3E/partial/cartPartial.html.twig', array(
            'loadedCart' => $loadedCart
        ));
    }

}
