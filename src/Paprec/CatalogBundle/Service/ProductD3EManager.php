<?php
/**
 * Created by PhpStorm.
 * User: frede
 * Date: 13/11/2018
 * Time: 11:38
 */

namespace Paprec\CatalogBundle\Service;


use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\ORMException;
use Exception;
use Paprec\CatalogBundle\Entity\ProductD3E;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ProductD3EManager
{

    private $em;
    private $container;

    public function __construct(EntityManagerInterface $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    public function get($productD3E){
        $id = $productD3E;
        if ($productD3E instanceof ProductD3E) {
            $id = $productD3E->getId();
        }
        try {

            $productD3E = $this->em->getRepository('PaprecCatalogBundle:ProductD3E')->find($id);

            /**
             * Vérification que le produit existe ou ne soit pas supprimé
             */
            if ($productD3E === null || $this->isDeleted($productD3E)) {
                throw new EntityNotFoundException('productD3ENotFound');
            }

            return $productD3E;

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Vérification qu'à ce jour le produit n'est pas supprimé
     *
     * @param ProductD3E $productD3E
     * @param bool $throwException
     * @return bool
     * @throws EntityNotFoundException
     */
    public function isDeleted(ProductD3E $productD3E, $throwException = false)
    {
        $now = new \DateTime();

        if ($productD3E->getDeleted() !== null && $productD3E->getDeleted() instanceof \DateTime && $productD3E->getDeleted() < $now) {

            if ($throwException) {
                throw new EntityNotFoundException('productD3ENotFound');
            }

            return true;

        }
        return false;
    }

    /**
     * On passe en paramètre les options Type et PostalCode, retourne les produits  qui sont disponibles dans le postalCode
     * et si le Type est 'Order' alors il faut vérifier que les produits retournés sont payables en ligne
     * @param $categoryId
     * @param $type
     * @return mixed
     * @throws Exception
     */
    public function findAvailables($options)
    {
        $type = $options['type'];
        $postalCode = $options['postalCode'];
        try {
            $query = $this->em
                ->getRepository(ProductD3E::class)
                ->createQueryBuilder('p')
                ->where('p.deleted is NULL')
                ->orderBy('p.position', 'ASC');
            if ($type == 'order') {
                $query->andWhere('p.isPayableOnline = 1');
            }

            $products = $query->getQuery()->getResult();

            $productsPostalCodeMatch = array();


            // On parcourt tous les produits D3E pour récupérer ceux  qui possèdent le postalCode
            foreach ($products as $product) {
                $postalCodes = str_replace(' ', '', $product->getAvailablePostalCodes());
                $postalCodesArray = explode(',', $postalCodes);
                foreach ($postalCodesArray as $pC) {
                    //on teste juste les deux premiers caractères pour avoir le code du département
                    if (substr($pC, 0, 2) == substr($postalCode, 0, 2)) {
                        $productsPostalCodeMatch[] = $product;
                    }
                }
            }

            return $productsPostalCodeMatch;

        } catch (ORMException $e) {
            throw new Exception('unableToGetProductD3Es', 500);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

}