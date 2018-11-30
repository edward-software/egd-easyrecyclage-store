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

    public function getByCategory($categoryId)
    {
        try {
            $query = $this->em
                ->getRepository(ProductDI::class)
                ->createQueryBuilder('p')
                ->innerJoin('PaprecCatalogBundle:ProductDICategory', 'pc', \Doctrine\ORM\Query\Expr\Join::WITH, 'p.id = pc.productDI')
                ->where('pc.category = :category')
                ->orderBy('pc.position', 'ASC')
                ->setParameter("category", $categoryId);

            return $query->getQuery()->getResult();


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