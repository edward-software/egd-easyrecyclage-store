<?php
/**
 * Created by PhpStorm.
 * User: frede
 * Date: 13/11/2018
 * Time: 11:38
 */

namespace Paprec\PublicBundle\Service;


use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use \Exception;
use Paprec\PublicBundle\Entity\Cart;
use Symfony\Component\DependencyInjection\ContainerInterface;


class CartManager
{

    private $em;
    private $container;

    public function __construct(EntityManagerInterface $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    /**
     * Retourne un Cart en pasant son Id ou un object Cart
     * @param $cart
     * @return null|object|Cart
     * @throws Exception
     */
    public function get($cart)
    {
        $id = $cart;
        if ($cart instanceof Cart) {
            $id = $cart->getId();
        }
        try {

            $cart = $this->em->getRepository('PaprecPublicBundle:Cart')->find($id);

            if ($cart === null || $this->isDisabled($cart)) {
                throw new EntityNotFoundException('cartNotFound');
            }

            return $cart;

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Vérification qu'a ce jour, le cart ne soit pas désactivé
     *
     * @param Cart $cart
     * @param bool $throwException
     * @return bool
     * @throws EntityNotFoundException
     */
    public function isDisabled(Cart $cart, $throwException = false)
    {
        $now = new \DateTime();

        if ($cart->getDisabled() !== null && $cart->getDisabled() instanceof \DateTime && $cart->getDisabled() < $now) {

            if ($throwException) {
                throw new EntityNotFoundException('cartNotFound');
            }

            return true;

        }
        return false;
    }

    /**
     * Créé un nouveau Cart en initialisant sa date Disabled  dans Today + $deltaJours
     *
     * @param $deltaJours
     * @return Cart
     * @throws Exception
     */
    public function create($deltaJours)
    {
        try {

            $cart = new Cart();

            /**
             * Initialisant de $disabled
             */
            $now = new \DateTime();
            $disabledDate = $now->modify('+' . $deltaJours . 'day');
            $cart->setDisabled($disabledDate);


            $this->em->persist($cart);
            $this->em->flush();

            return $cart;

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Créé un nouveau Cart à partir d'un autre en copiant ses données géographiques
     * Utile lorsque l'on change de choix de division mais que l'on veut conserver les données "Je me situe"
     * @param $cart
     * @return Cart
     * @throws Exception
     */
    public function cloneCart($cart)
    {
        try {
            $cart = $this->get($cart);

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
        $newCart = $this->create(90);
        $newCart->setLocation($cart->getLocation());
        $newCart->setPostalCode($cart->getPostalCode());
        $newCart->setCity($cart->getCity());
        $newCart->setLongitude($cart->getLongitude());
        $newCart->setLatitude($cart->getLatitude());

        $this->em->persist($newCart);

        return $newCart;
    }

    /**
     * Ajoute une displayedCategory au simple_array displayedCategories du cart si elle n'est pas déjà existante
     * La supprime si elle existe déjà
     * @param $id
     * @param $categoryId
     * @return null|object|Cart
     * @throws Exception
     */
    public function addOrRemoveDisplayedCategory($id, $categoryId)
    {
        $cart = $this->get($id);
        $dCategories = $cart->getDisplayedCategories();

        if (in_array($categoryId, $dCategories)) {
            $index = array_search($categoryId, $dCategories);
            array_splice($dCategories, $index, 1);
        } else {
            $dCategories[] = $categoryId;
        }
        $cart->setDisplayedCategories($dCategories);
        $this->em->flush();
        return $cart;
    }

    /**
     * Ajoute un displayedProduct à l'array dispplayedProducts du cart si il n'est pas déjà existant
     * avec comme clé, l'id de la catégorie
     * La supprime si elle existe déjà
     * @param $id
     * @param $categoryId
     * @param $productId
     * @return null|object|Cart
     * @throws Exception
     */
    public function addOrRemoveDisplayedProduct($id, $categoryId, $productId)
    {
        $cart = $this->get($id);
        $dProducts = $cart->getDisplayedProducts();

        if ($dProducts && in_array($productId, $dProducts) && array_key_exists($categoryId, $dProducts)) {
            unset($dProducts[$categoryId]);
        } else {
            $dProducts = array();
            $dProducts[$categoryId] = $productId;
        }


        $cart->setDisplayedProducts($dProducts);
        $this->em->persist($cart);
        $this->em->flush();
        return $cart;
    }

    /**
     * Pour D3E,il n'y a pas de notion de catégorie, on remplace donc le displayedProdu
     * @param $id
     * @param $productId
     * @return null|object|Cart
     * @throws Exception
     */
    public function addOrRemoveDisplayedProductD3E($id, $productId)
    {
        $cart = $this->get($id);
        $dProducts = $cart->getDisplayedProducts();

        if ($dProducts && in_array($productId, $dProducts)) {
            $dProducts = array();
        } else {
            $dProducts = array();
            $dProducts[] = $productId;
        }

        $cart->setDisplayedProducts($dProducts);
        $this->em->persist($cart);
        $this->em->flush();
        return $cart;
    }

    /**
     * @param $id
     * @param $categoryId
     * @param $productId
     * @param $quantity
     * @return mixed
     * @throws Exception
     */
    public function addContent($id, $categoryId, $productId, $quantity)
    {
        $cart = $this->get($id);
        $content = $cart->getContent();
        $product = ['cId' => $categoryId, 'pId' => $productId, 'qtty' => $quantity];
        foreach ($content as $key => $value) {
            if ($value['cId'] == $categoryId && $value['pId'] == $productId) {
                unset($content[$key]);
            }
        }
        $content[] = $product;
        $cart->setContent($content);
        $this->em->persist($cart);
        $this->em->flush();
        return $cart;
    }

    /**
     * @param $id
     * @param $productId
     * @param $quantity
     * @return null|object|Cart
     * @throws Exception
     */
    public function addContentD3E($id, $productId, $quantity)
    {
        $cart = $this->get($id);
        $content = $cart->getContent();
        $product = ['pId' => $productId, 'qtty' => $quantity];
        foreach ($content as $key => $value) {
            if ($value['pId'] == $productId) {
                unset($content[$key]);
            }
        }
        $content[] = $product;
        $cart->setContent($content);
        $this->em->persist($cart);
        $this->em->flush();
        return $cart;
    }

    /**
     * Supprime un produit
     * @param $id
     * @param $categoryId
     * @param $productId
     * @return null|object|Cart
     * @throws Exception
     */
    public function removeContent($id, $categoryId, $productId)
    {
        $cart = $this->get($id);
        $products = $cart->getContent();
        foreach ($products as $key => $product) {
            if ($product['cId'] == $categoryId && $product['pId'] == $productId) {
                unset($products[$key]);
            }
        }
        $cart->setContent($products);
        $this->em->persist($cart);
        $this->em->flush();
        return $cart;
    }

    /**
     * @param $id
     * @param $productId
     * @return null|object|Cart
     * @throws Exception
     */
    public function removeContentD3E($id, $productId)
    {
        $cart = $this->get($id);
        $products = $cart->getContent();
        foreach ($products as $key => $product) {
            if ($product['pId'] == $productId) {
                unset($products[$key]);
            }
        }
        $cart->setContent($products);
        $this->em->persist($cart);
        $this->em->flush();
        return $cart;
    }

    /**
     * Fonction qui renvoie un tableau permettant d'afficher tous les produits dans le Cart dans la partie "Mon besoin"
     * ainsi que la somme du prix du Cart
     * @param $id
     * @return array
     * @throws Exception
     */
    public function loadCartDI($id)
    {
        $cart = $this->get($id);
        $productDIManager = $this->container->get('paprec_catalog.product_di_manager');
        $categoryManager = $this->container->get('paprec_catalog.category_manager');


        /**
         * TODO : gérer la somme du panier dans une autre méthode :
         * calcule en fonction de la division, du code postal, etc ...
         */


        // on récupère les products ajoutés au cart
        $productsCategories = $cart->getContent();
        $loadedCart = array();
        $loadedCart['sum'] = 0;
        if ($productsCategories && count($productsCategories)) {
            foreach ($productsCategories as $productsCategory) {
                $productDI = $productDIManager->get($productsCategory['pId']);
                $category = $categoryManager->get($productsCategory['cId']);
                $loadedCart[$productsCategory['pId'] . '_' . $productsCategory['cId']] = ['qtty' => $productsCategory['qtty'], 'pName' => $productDI->getName(), 'pCapacity' => $productDI->getCapacity() . $productDI->getCapacityUnit(), 'cName' => $category->getName(), 'frequency' => $cart->getFrequency()];
                $productDICategory = $this->em->getRepository('PaprecCatalogBundle:ProductDICategory')->findOneBy(
                    array(
                        'productDI' => $productDI,
                        'category' => $category
                    )
                );
                if ($productDICategory !== null) {
                    $loadedCart['sum'] += $productDICategory->getUnitPrice() * $productsCategory['qtty'];
                }
            }
        } else {
            return $loadedCart;
        }
        // On trie par ordre croissant sur les clés, donc par les id des produits
        // ainsi les mêmes produits dans 2 catégories différentes
        ksort($loadedCart);
        return $loadedCart;
    }

    public function loadCartChantier($id)
    {
        $cart = $this->get($id);
        $productChantierManager = $this->container->get('paprec_catalog.product_chantier_manager');
        $categoryManager = $this->container->get('paprec_catalog.category_manager');

        /**
         * TODO : gérer la somme du panier dans une autre méthode :
         * calcule en fonction de la division, du code postal, etc ...
         */

        // on récupère les products ajoutés au cart
        $productsCategories = $cart->getContent();
        $loadedCart = array();
        $loadedCart['sum'] = 0;
        if ($productsCategories && count($productsCategories)) {

            foreach ($productsCategories as $productsCategory) {
                $productChantier = $productChantierManager->get($productsCategory['pId']);
                $category = $categoryManager->get($productsCategory['cId']);
                $loadedCart[$productsCategory['pId'] . '_' . $productsCategory['cId']] = ['qtty' => $productsCategory['qtty'], 'pName' => $productChantier->getName(), 'pCapacity' => $productChantier->getCapacity() . $productChantier->getCapacityUnit(), 'cName' => $category->getName(), 'frequency' => $cart->getFrequency()];
                $productChantierCategory = $this->em->getRepository('PaprecCatalogBundle:ProductChantierCategory')->findOneBy(
                    array(
                        'productChantier' => $productChantier,
                        'category' => $category
                    )
                );
                $loadedCart['sum'] += $productChantierCategory->getUnitPrice() * $productsCategory['qtty'];
            }
        } else {
            return $loadedCart;
        }
        // On trie par ordre croissant sur les clés, donc par les id des produits
        // ainsi les mêmes produits dans 2 catégories différentes
        ksort($loadedCart);
        return $loadedCart;
    }

    public function loadCartD3E($id)
    {
        $cart = $this->get($id);
        $productChantierManager = $this->container->get('paprec_catalog.product_D3E_manager');
        $priceListD3EManager = $this->container->get('paprec_catalog.price_list_d3e_manager');

        /**
         * TODO : gérer la somme du panier dans une autre méthode :
         * calcule en fonction de la division, du code postal, etc ...
         */

        // on récupère les products ajoutés au cart
        $products = $cart->getContent();
        $loadedCart = array();
        $loadedCart['sum'] = 0;
        if ($products && count($products)) {
            foreach ($products as $product) {
                $productD3E = $productChantierManager->get($product['pId']);
                $loadedCart[$product['pId']] = ['qtty' => $product['qtty'], 'pName' => $productD3E->getName(), 'frequency' => $cart->getFrequency()];

                $postalCode = substr($cart->getLocation(), 0, 5);
                $loadedCart['sum'] += $priceListD3EManager->getUnitPriceByPostalCodeQtty($productD3E->getPriceListD3E(), $postalCode, $product['qtty']) * $product['qtty'];
            }
        } else {
            return $loadedCart;
        }
        // On trie par ordre croissant sur les clés, donc par les id des produits
        // ainsi les mêmes produits dans 2 catégories différentes
        ksort($loadedCart);
        return $loadedCart;
    }
}