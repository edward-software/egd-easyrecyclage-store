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
use Symfony\Component\Serializer\Encoder\JsonEncoder;


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

            if ($cart === null) {
                throw new EntityNotFoundException('cartNotFound');
            }

            return $cart;

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Créé un nouveau Cart
     * @param $location
     * @param $division
     * @param $frequency
     * @return Cart
     * @throws Exception
     */
    public function add($location, $division, $frequency)
    {
        try {

            $cart = new Cart();

            $cart->setDivision($division);
            $cart->setLocation($location);
            $cart->setFrequency($frequency);

            $this->em->persist($cart);
            $this->em->flush();

            return $cart;

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
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
        $productCategories = $cart->getContent();
        foreach ($productCategories as $key => $productCategory) {
            if ($productCategory['cId'] == $categoryId && $productCategory['pId'] == $productId) {
                unset($productCategories[$key]);
            }
        }
        $cart->setContent($productCategories);
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
    public function loadCart($id)
    {
        $cart = $this->get($id);
        $productDIManager = $this->container->get('paprec_catalog.product_di_manager');
        $categoryManager = $this->container->get('paprec_catalog.category_manager');


        // on récupère les products ajoutés au cart
        $productsCategories = $cart->getContent();
        $loadedCart = array();
        $loadedCart['sum'] = 0;
        if ($productsCategories && count($productsCategories)) {
            foreach ($productsCategories as $productsCategory) {
                $productDI = $productDIManager->get($productsCategory['pId']);
                $categoryName = $categoryManager->get($productsCategory['cId'])->getName();
                $loadedCart[$productsCategory['pId'] . '_' . $productsCategory['cId']] = ['qtty' => $productsCategory['qtty'], 'pName' => $productDI->getName(), 'pCapacity' => $productDI->getCapacity() . $productDI->getCapacityUnit(), 'cName' => $categoryName, 'frequency' => $cart->getFrequency()];
                $loadedCart['sum'] += $productDI->getUnitPrice()*$productsCategory['qtty'];
            }
        } else {
            return $loadedCart;
        }
        // On trie par ordre croissant sur les clés, donc par les id des produits
        // ainsi les mêmes produits dans 2 catégories différentes
        ksort($loadedCart);
        return $loadedCart;
    }

    private function calculateAmount()
    {
        return 100;
    }

}