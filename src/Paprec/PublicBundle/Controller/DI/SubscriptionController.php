<?php

namespace Paprec\PublicBundle\Controller\DI;

use Paprec\CommercialBundle\Entity\ProductDIOrder;
use Paprec\CommercialBundle\Form\ProductDIOrderShortType;
use Paprec\PublicBundle\Entity\Cart;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class SubscriptionController extends Controller
{

    /**
     * @Route("/step1/{cartUuid}", name="paprec_public_DI_subscription_step1")
     */
    public function step1Action(Request $request, $cartUuid)
    {
        $cartManager = $this->get('paprec.cart_manager');
        $categoryManager = $this->get('paprec_catalog.category_manager');
        $productDICategoryManager = $this->get('paprec_catalog.product_di_manager');


        $cart = $cartManager->get($cartUuid);

        // On récupère les catégoriesDI pour afficher le choix des catégories
        $categories = $categoryManager->getCategoriesDI();

        // Pour alimenter le "select" des types de déchets
        $divisions = $this->getParameter('paprec_divisions');

        /*
         * Si il y a des displayedCategories, il faut récupérer leurs produits pour les afficher
         */
        $productsCategories = array();
        foreach ($cart->getDisplayedCategories() as $displayedCategory) {
            $productsCategories[$displayedCategory] = $productDICategoryManager->getByCategory($displayedCategory);
        }
        return $this->render('@PaprecPublic/DI/index.html.twig', array(
            'divisions' => $divisions,
            'cart' => $cart,
            'categories' => $categories,
            'productsCategories' => $productsCategories
        ));
    }

    /**
     * @Route("/step2/{cartUuid}", name="paprec_public_DI_subscription_step2")
     */
    public function step2Action(Request $request, $cartUuid)
    {
        $cartManager = $this->get('paprec.cart_manager');
        $productDIOrderManager = $this->get('paprec_catalog.product_di_order_manager');

        $cart = $cartManager->get($cartUuid);

        $postalCode = substr($cart->getLocation(),0, 5);
        $city = substr($cart->getLocation(),5);

        $productDIOrder = new ProductDIOrder();
        $productDIOrder->setCity($city);
        $productDIOrder->setPostalCode($postalCode);

        $form = $this->createForm(ProductDIOrderShortType::class, $productDIOrder);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $productDIOrder = $form->getData();
            $productDIOrder->setOrderStatus('Créé');

            $em = $this->getDoctrine()->getManager();
            $em->persist($productDIOrder);
            $em->flush();

            // On récupère tous les produits ajoutés au Cart
            foreach ($cart->getContent() as $item) {
                $productDIOrderManager->addLineFromCart($productDIOrder, $item['pId'], $item['qtty']);
            }



            return $this->redirectToRoute('paprec_public_DI_subscription_step3', array(
                'cartUuid' => $cart->getId()
            ));

        }


        return $this->render('@PaprecPublic/DI/contactDetails.html.twig', array(
            'cart' => $cart,
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/step3/{cartUuid}", name="paprec_public_DI_subscription_step3")
     */
    public function step3Action(Request $request, $cartUuid)
    {
        $cartManager = $this->get('paprec.cart_manager');
        $categoryManager = $this->get('paprec_catalog.category_manager');
        $productDICategoryManager = $this->get('paprec_catalog.product_di_manager');


        $cart = $cartManager->get($cartUuid);
        return $this->render('@PaprecPublic/DI/offerDetails.html.twig', array(
            'cart' => $cart
        ));
    }





    /**
     * @Route("/addDisplayedCategory/{cartUuid}/{categoryId}", name="paprec_public_DI_subscription_addDisplayedCategory")
     * @throws \Exception
     */
    public function addDisplayedCategoryAction(Request $request, $cartUuid, $categoryId)
    {
        $cartManager = $this->get('paprec.cart_manager');

        // On ajoute ou on supprime la catégorie sélecionnée au tableau des displayedCategories du Cart
        $cart = $cartManager->addOrRemoveDisplayedCategory($cartUuid, $categoryId);

        return $this->redirectToRoute('paprec_public_DI_subscription_step1', array(
            'cartUuid' => $cart->getId()
        ));
    }

    /**
     * Ajoute au cart un displayedProduct avec en key => value( categoryId => productId)
     * @Route("/addOrRemoveDisplayedProduct/{cartUuid}/{categoryId}/{productId}", name="paprec_public_DI_subscription_addOrRemoveDisplayedProduct")
     * @throws \Exception
     */
    public function addOrRemoveDisplayedProductAction(Request $request, $cartUuid, $categoryId, $productId)
    {
        $cartManager = $this->get('paprec.cart_manager');

        // On ajoute ou on supprime le produit sélecionné au tableau des displayedCategories du Cart
        $cart = $cartManager->addOrRemoveDisplayedProduct($cartUuid, $categoryId, $productId);

        return $this->redirectToRoute('paprec_public_DI_subscription_step1', array(
            'cartUuid' => $cart->getId()
        ));
    }

    /**
     * Ajoute au cart un product
     * @Route("/addContent/{cartUuid}/{categoryId}/{productId}/{quantity}", name="paprec_public_DI_subscription_addContent")
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
     * @Route("/removeContent/{cartUuid}/{categoryId}/{productId}", name="paprec_public_DI_subscription_removeContent")
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
     * @Route("/loadCart/{cartUuid}", name="paprec_public_DI_subscription_loadCart", condition="request.isXmlHttpRequest()")
     */
    public function loadCartAction(Request $request, $cartUuid)
    {
        $cartManager = $this->get('paprec.cart_manager');

        // On récupère les informations du cart à afficher ainsi que le calcul de la somme du Cart
        $loadedCart = $cartManager->loadCart($cartUuid);

        return $this->render('@PaprecPublic/DI/partial/cartPartial.html.twig', array(
            'loadedCart' => $loadedCart
        ));
    }

}
