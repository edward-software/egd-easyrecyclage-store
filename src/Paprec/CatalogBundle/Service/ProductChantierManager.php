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
use Paprec\CatalogBundle\Entity\ProductChantier;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ProductChantierManager
{

    private $em;
    private $container;

    public function __construct(EntityManagerInterface $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    public function get($productChantier){
        $id = $productChantier;
        if ($productChantier instanceof ProductChantier) {
            $id = $productChantier->getId();
        }
        try {

            $productChantier = $this->em->getRepository('PaprecCatalogBundle:ProductChantier')->find($id);

            /**
             * Vérification que le produit existe ou ne soit pas supprimé
             */
            if ($productChantier === null || $this->isDeleted($productChantier)) {
                throw new EntityNotFoundException('productChantierNotFound');
            }

            return $productChantier;

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Vérification qu'à ce jour le produit n'est pas supprimé
     *
     * @param ProductChantier $productChantier
     * @param bool $throwException
     * @return bool
     * @throws EntityNotFoundException
     */
    public function isDeleted(ProductChantier $productChantier, $throwException = false)
    {
        $now = new \DateTime();

        if ($productChantier->getDeleted() !== null && $productChantier->getDeleted() instanceof \DateTime && $productChantier->getDeleted() < $now) {

            if ($throwException) {
                throw new EntityNotFoundException('productChantierNotFound');
            }

            return true;

        }
        return false;
    }

    /**
     * On passe en paramètre les options Type et PostalCode, retourne les produits appartenant à la catégorie,
     * qui sont disponibles dans le postalCode
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
        $categoryId = $options['category'];

        try {
            $query = $this->em
                ->getRepository(ProductChantier::class)
                ->createQueryBuilder('p')
                ->innerJoin('PaprecCatalogBundle:ProductChantierCategory', 'pc', \Doctrine\ORM\Query\Expr\Join::WITH, 'p.id = pc.productChantier')
                ->where('pc.category = :category')
                ->orderBy('pc.position', 'ASC')
                ->setParameter("category", $categoryId);
            if ($type == 'order') {
                $query->andWhere('p.isPayableOnline = 1');
            }

            $products = $query->getQuery()->getResult();

            $productsPostalCodeMatch = array();


            // On parcourt tous les produits Chantier    pour récupérer ceux  qui possèdent le postalCode
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
            throw new Exception('unableToGetProductChantiers', 500);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

}