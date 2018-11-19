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
use Paprec\CommercialBundle\Entity\ProductDIOrder;
use Paprec\CommercialBundle\Entity\ProductDIOrderLine;
use Paprec\CommercialBundle\Form\ProductDIOrderShortType;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ProductDIOrderManager
{
    private $em;
    private $container;

    public function __construct(EntityManagerInterface $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    public function get($productDIOrder)
    {
        $id = $productDIOrder;
        if ($productDIOrder instanceof ProductDIOrder) {
            $id = $productDIOrder->getId();
        }
        try {

            $productDIOrder = $this->em->getRepository('PaprecCatalogBundle:ProductDIOrder')->find($id);

            if ($productDIOrder === null) {
                throw new EntityNotFoundException('productDIOrderNotFound');
            }

            return $productDIOrder;

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Ajoute une productOrderDiLine à un productDIOrder
     * @param ProductDIOrder $productDIOrder
     * @param ProductDIOrderLine $productDIOrderLine
     */
    public function addLine(ProductDIOrder $productDIOrder, ProductDIOrderLine $productDIOrderLine)
    {
        $productDIOrderLine->setProductDIOrder($productDIOrder);
        $productDIOrder->addProductDIOrderLine($productDIOrderLine);
        $productDIOrderLine->setUnitPrice($productDIOrderLine->getProductDI()->getUnitPrice());
        $productDIOrderLine->setProductName($productDIOrderLine->getProductDI()->getName());
        $productDIOrderLine->setCategoryName($productDIOrderLine->getCategory()->getName());


        // On check s'il existe déjà une ligne pour ce produit, pour l'incrémenter
        $currentOrderLine = $this->em->getRepository('PaprecCommercialBundle:ProductDIOrderLine')->findOneBy(
            array(
                'productDIOrder' => $productDIOrder,
                'productDI' => $productDIOrderLine->getProductDI(),
                'category' => $productDIOrderLine->getCategory()
            )
        );

        if ($currentOrderLine) {
            $quantity = $productDIOrderLine->getQuantity() + $currentOrderLine->getQuantity();
            $currentOrderLine->setQuantity($quantity);
        } else {

            $this->em->persist($productDIOrderLine);
        }

        //On recalcule le montant total de la ligne ainsi que celui du devis complet
        $totalLine = $this->calculateTotalLine($productDIOrderLine);
        $productDIOrderLine->setTotalAmount($totalLine);
        $this->em->flush();

        $total = $this->calculateTotal($productDIOrder);
        $productDIOrder->setTotalAmount($total);
        $this->em->flush();
    }

    /**
     * Met à jour les montants totaux après l'édition d'une ligne
     * @param ProductDIOrder $productDIOrder
     * @param ProductDIOrderLine $productDIOrderLine
     */
    public function editLine(ProductDIOrder $productDIOrder, ProductDIOrderLine $productDIOrderLine)
    {
        $totalLine = $this->calculateTotalLine($productDIOrderLine);
        $productDIOrderLine->setTotalAmount($totalLine);
        $this->em->flush();

        $total = $this->calculateTotal($productDIOrder);
        $productDIOrder->setTotalAmount($total);
        $this->em->flush();
    }

    /**
     * Pour ajouter une productDIOrderLine depuis le Cart, il faut d'abord retrouver le ProductDI
     * @param ProductDIOrder $productDIOrder
     * @param $productId
     * @param $qtty
     * @throws Exception
     */
    public function addLineFromCart(ProductDIOrder $productDIOrder, $productId, $qtty, $categoryId)
    {
        $productDIManager = $this->container->get('paprec_catalog.product_di_manager');
        $categoryManager = $this->container->get('paprec_catalog.category_manager');

        try {
            $productDI = $productDIManager->get($productId);
            $productDIOrderLine = new ProductDIOrderLine();
            $category = $categoryManager->get($categoryId);


            $productDIOrderLine->setProductDI($productDI);
            $productDIOrderLine->setCategory($category);
            $productDIOrderLine->setQuantity($qtty);
            $this->addLine($productDIOrder, $productDIOrderLine);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }


    }

    /**
     * Calcule le montant total d'un ProductDIOrder
     * TODO relier le ProductDIOrder aux PostalCodes pour calculer avec le coefficient multiplicateur du postalCode
     * @param ProductDIOrder $productDIOrder
     * @return float|int
     */
    public function calculateTotal(ProductDIOrder $productDIOrder)
    {
        $totalAmount = 0;
        foreach ($productDIOrder->getProductDIOrderLines() as $productDIOrderLine) {
            $totalAmount += $this->calculateTotalLine($productDIOrderLine);
        }
        return $totalAmount;

    }

    /**
     * Retourne le montant total d'une ProductDIOrderLine
     * @param ProductDIOrder $productDIOrder
     * @param ProductDIOrderLine $productDIOrderLine
     * @return float|int
     */
    public function calculateTotalLine(ProductDIOrderLine $productDIOrderLine)
    {

        return $productDIOrderLine->getQuantity() * $productDIOrderLine->getUnitPrice();
    }

}