<?php
/**
 * Created by PhpStorm.
 * User: frede
 * Date: 16/11/2018
 * Time: 12:08
 */

namespace Paprec\CommercialBundle\Service;


use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Exception;
use Paprec\CommercialBundle\Entity\ProductChantierOrder;
use Paprec\CommercialBundle\Entity\ProductChantierOrderLine;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ProductChantierOrderManager
{
    private $em;
    private $container;

    public function __construct(EntityManagerInterface $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    public function get($productChantierOrder)
    {
        $id = $productChantierOrder;
        if ($productChantierOrder instanceof ProductChantierOrder) {
            $id = $productChantierOrder->getId();
        }
        try {

            $productChantierOrder = $this->em->getRepository('PaprecCatalogBundle:ProductChantierOrder')->find($id);

            if ($productChantierOrder === null) {
                throw new EntityNotFoundException('productChantierOrderNotFound');
            }

            return $productChantierOrder;

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Ajoute une productChantierOrderLine à un productChantierOrder
     * @param ProductChantierOrder $productChantierOrder
     * @param ProductChantierOrderLine $productChantierOrderLine
     */
    public function addLine(ProductChantierOrder $productChantierOrder, ProductChantierOrderLine $productChantierOrderLine)
    {
        $productChantierOrderLine->setProductChantierOrder($productChantierOrder);
        $productChantierOrder->addProductChantierOrderLine($productChantierOrderLine);
        $productChantierCategory = $this->em->getRepository('PaprecCatalogBundle:ProductChantierCategory')->findOneBy(
            array(
                'productChantier' => $productChantierOrderLine->getProductChantier(),
                'category' => $productChantierOrderLine->getCategory()
            )
        );
        $productChantierOrderLine->setUnitPrice($productChantierCategory->getUnitPrice());
        $productChantierOrderLine->setProductName($productChantierOrderLine->getProductChantier()->getName());
        $productChantierOrderLine->setCategoryName($productChantierOrderLine->getCategory()->getName());


        // On check s'il existe déjà une ligne pour ce produit, pour l'incrémenter
        $currentOrderLine = $this->em->getRepository('PaprecCommercialBundle:ProductChantierOrderLine')->findOneBy(
            array(
                'productChantierOrder' => $productChantierOrder,
                'productChantier' => $productChantierOrderLine->getProductChantier(),
                'category' => $productChantierOrderLine->getCategory()
            )
        );

        if ($currentOrderLine) {
            $quantity = $productChantierOrderLine->getQuantity() + $currentOrderLine->getQuantity();
            $currentOrderLine->setQuantity($quantity);
        } else {

            $this->em->persist($productChantierOrderLine);
        }

        //On recalcule le montant total de la ligne ainsi que celui du devis complet
        $totalLine = $this->calculateTotalLine($productChantierOrderLine);
        $productChantierOrderLine->setTotalAmount($totalLine);
        $this->em->flush();

        $total = $this->calculateTotal($productChantierOrder);
        $productChantierOrder->setTotalAmount($total);
        $this->em->flush();
    }

    /**
     * Met à jour les montants totaux après l'édition d'une ligne
     * @param ProductChantierOrder $productChantierOrder
     * @param ProductChantierOrderLine $productChantierOrderLine
     */
    public function editLine(ProductChantierOrder $productChantierOrder, ProductChantierOrderLine $productChantierOrderLine)
    {
        $totalLine = $this->calculateTotalLine($productChantierOrderLine);
        $productChantierOrderLine->setTotalAmount($totalLine);
        $this->em->flush();

        $total = $this->calculateTotal($productChantierOrder);
        $productChantierOrder->setTotalAmount($total);
        $this->em->flush();
    }

    /**
     * Pour ajouter une productChantierOrderLine depuis le Cart, il faut d'abord retrouver le ProductChantier et la catégorie
     * @param ProductChantierOrder $productChantierOrder
     * @param $productId
     * @param $qtty
     * @throws Exception
     */
    public function addLineFromCart(ProductChantierOrder $productChantierOrder, $productId, $qtty, $categoryId)
    {
        $productChantierManager = $this->container->get('paprec_catalog.product_chantier_manager');
        $categoryManager = $this->container->get('paprec_catalog.category_manager');

        try {
            $productChantier = $productChantierManager->get($productId);
            $productChantierOrderLine = new ProductChantierOrderLine();
            $category = $categoryManager->get($categoryId);


            $productChantierOrderLine->setProductChantier($productChantier);
            $productChantierOrderLine->setCategory($category);
            $productChantierOrderLine->setQuantity($qtty);
            $this->addLine($productChantierOrder, $productChantierOrderLine);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }


    }

    /**
     * Calcule le montant total d'un ProductChantierOrder
     * TODO relier le ProductChantierOrder aux PostalCodes pour calculer avec le coefficient multiplicateur du postalCode
     * @param ProductChantierOrder $productChantierOrder
     * @return float|int
     */
    public function calculateTotal(ProductChantierOrder $productChantierOrder)
    {
        $totalAmount = 0;
        foreach ($productChantierOrder->getProductChantierOrderLines() as $productChantierOrderLine) {
            $totalAmount += $this->calculateTotalLine($productChantierOrderLine);
        }
        return $totalAmount;

    }

    /**
     * Retourne le montant total d'une ProductChantierOrderLine
     * @param ProductChantierOrder $productChantierOrder
     * @param ProductChantierOrderLine $productChantierOrderLine
     * @return float|int
     */
    public function calculateTotalLine(ProductChantierOrderLine $productChantierOrderLine)
    {

        return $productChantierOrderLine->getQuantity() * $productChantierOrderLine->getUnitPrice();
    }

}