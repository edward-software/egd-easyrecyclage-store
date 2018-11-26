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

            if ($productD3E === null) {
                throw new EntityNotFoundException('productD3ENotFound');
            }

            return $productD3E;

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * On passe le type de produits pour renvoyer les produits payables en ligne si c'est de type 'Order'
     * tous les produits si c'est un devis  ('Ouote')
     * @param $categoryId
     * @param $type
     * @return mixed
     * @throws Exception
     */
    public function getByType($type)
    {
        try {
            $query = $this->em
                ->getRepository(ProductD3E::class)
                ->createQueryBuilder('p')
                ->orderBy('p.position', 'ASC');
            if ($type == 'order') {
                $query->andWhere('p.isPayableOnline = 1');
            }

            return $query->getQuery()->getResult();

        } catch (ORMException $e) {
            throw new Exception('unableToGetProductD3Es', 500);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

}