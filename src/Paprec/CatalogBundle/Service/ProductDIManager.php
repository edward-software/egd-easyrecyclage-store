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
use Paprec\CatalogBundle\Entity\ProductDI;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ProductDIManager
{

    private $em;
    private $container;

    public function __construct(EntityManagerInterface $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    public function get($productDI){
        $id = $productDI;
        if ($productDI instanceof ProductDI) {
            $id = $productDI->getId();
        }
        try {

            $productDI = $this->em->getRepository('PaprecCatalogBundle:ProductDI')->find($id);

            /**
             * Vérification que le produit existe ou ne soit pas supprimé
             */
            if ($productDI === null || $this->isDeleted($productDI)) {
                throw new EntityNotFoundException('productDINotFound');
            }


            return $productDI;

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * On passe en paramètre les options Category et PostalCode, retourne les produits qui appartiennent à la catégorie
     * et qui sont disponibles dans le postalCode
     * @param $options
     * @return array
     * @throws Exception
     */
    public function findAvailables($options)
    {
        $categoryId = $options['category'];
        $postalCode= $options['postalCode'];

        try {
            $query = $this->em
                ->getRepository(ProductDI::class)
                ->createQueryBuilder('p')
                ->innerJoin('PaprecCatalogBundle:ProductDICategory', 'pc', \Doctrine\ORM\Query\Expr\Join::WITH, 'p.id = pc.productDI')
                ->where('pc.category = :category')
                ->orderBy('pc.position', 'ASC')
                ->setParameter("category", $categoryId);

            $products = $query->getQuery()->getResult();


            $productsPostalCodeMatch = array();


            // On parcourt tous les produits DI pour récupérer ceux  qui possèdent le postalCode
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
            throw new Exception('unableToGetProductDIs', 500);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Vérifie qu'à ce jour, le produit ce soit pas supprimé
     *
     * @param ProductDI $productDI
     * @param bool $throwException
     * @return bool
     * @throws EntityNotFoundException
     */
    public function isDeleted(ProductDI $productDI, $throwException = false)
    {
        $now = new \DateTime();

        if ($productDI->getDeleted() !== null && $productDI->getDeleted() instanceof \DateTime && $productDI->getDeleted() < $now) {

            if ($throwException) {
                throw new EntityNotFoundException('productDINotFound');
            }

            return true;

        }
        return false;
    }

}