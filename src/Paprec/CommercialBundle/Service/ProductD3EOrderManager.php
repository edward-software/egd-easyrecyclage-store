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
use Paprec\CommercialBundle\Entity\ProductD3EOrder;
use Paprec\CommercialBundle\Entity\ProductD3EOrderLine;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ProductD3EOrderManager
{
    private $em;
    private $container;

    public function __construct(EntityManagerInterface $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    public function get($productD3EOrder)
    {
        $id = $productD3EOrder;
        if ($productD3EOrder instanceof ProductD3EOrder) {
            $id = $productD3EOrder->getId();
        }
        try {

            $productD3EOrder = $this->em->getRepository('PaprecCatalogBundle:ProductD3EOrder')->find($id);

            if ($productD3EOrder === null) {
                throw new EntityNotFoundException('productD3EOrderNotFound');
            }

            return $productD3EOrder;

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Ajoute une productD3EOrderLine à un productD3EOrder
     * @param ProductD3EOrder $productD3EOrder
     * @param ProductD3EOrderLine $productD3EOrderLine
     */
    public function addLine(ProductD3EOrder $productD3EOrder, ProductD3EOrderLine $productD3EOrderLine)
    {
        $grilleTarifD3EManager = $this->container->get('paprec_catalog.grille_tarif_d3e_manager');

        //Récupération de la grille liée au produit
        $grille = $productD3EOrderLine->getProductD3E()->getGrilleTarifD3E();

        // On check s'il existe déjà une ligne pour ce produit, pour l'incrémenter
        $currentOrderLine = $this->em->getRepository('PaprecCommercialBundle:ProductD3EOrderLine')->findOneBy(
            array(
                'productD3EOrder' => $productD3EOrder,
                'productD3E' => $productD3EOrderLine->getProductD3E()
            )
        );

        if ($currentOrderLine) {
            $quantity = $productD3EOrderLine->getQuantity() + $currentOrderLine->getQuantity();
            $currentOrderLine->setQuantity($quantity);

            // On vérifie de prix unitaire du produit exisntant qui a pu changer si l'on a changé de tranche
            // en augmentant la quantité
            $unitPrice = $grilleTarifD3EManager->getUnitPriceByPostalCodeQtty($grille, $productD3EOrder->getPostalCode(), $currentOrderLine->getQuantity());
            $currentOrderLine->setUnitPrice($unitPrice);

            //On recalcule le montant total de la ligne ainsi que celui du devis complet
            $totalLine = $this->calculateTotalLine($currentOrderLine);
            $currentOrderLine->setTotalAmount($totalLine);
            $this->em->flush();
        } else {
            // On lie la grille et la ligne
            $productD3EOrderLine->setProductD3EOrder($productD3EOrder);
            $productD3EOrder->addProductD3EOrderLine($productD3EOrderLine);

            $productD3EOrderLine->setProductName($productD3EOrderLine->getProductD3E()->getName());

            // Récupération du prix unitaire du produit
            $unitPrice = $grilleTarifD3EManager->getUnitPriceByPostalCodeQtty($grille, $productD3EOrder->getPostalCode(), $productD3EOrderLine->getQuantity());
            $productD3EOrderLine->setUnitPrice($unitPrice);
            $this->em->persist($productD3EOrderLine);

            //On recalcule le montant total de la ligne ainsi que celui du devis complet
            $totalLine = $this->calculateTotalLine($productD3EOrderLine);
            $productD3EOrderLine->setTotalAmount($totalLine);
            $this->em->flush();
        }

        $total = $this->calculateTotal($productD3EOrder);
        $productD3EOrder->setTotalAmount($total);
        $this->em->flush();
    }

    /**
     * Met à jour les montants totaux après l'édition d'une ligne
     * @param ProductD3EOrder $productD3EOrder
     * @param ProductD3EOrderLine $productD3EOrderLine
     */
    public function editLine(ProductD3EOrder $productD3EOrder, ProductD3EOrderLine $productD3EOrderLine)
    {
        $productD3EOrderManager = $this->container->get('paprec_catalog.grille_tarif_d3e_manager');

        // Récupération du prix unitaire du produit
        $unitPrice = $productD3EOrderManager->getUnitPriceByPostalCodeQtty($productD3EOrderLine->getProductD3E()->getGrilleTarifD3E(), $productD3EOrder->getPostalCode(), $productD3EOrderLine->getQuantity());
        $productD3EOrderLine->setUnitPrice($unitPrice);

        $totalLine = $this->calculateTotalLine($productD3EOrderLine);
        $productD3EOrderLine->setTotalAmount($totalLine);
        $this->em->flush();

        $total = $this->calculateTotal($productD3EOrder);
        $productD3EOrder->setTotalAmount($total);
        $this->em->flush();
    }

    /**
     * Pour ajouter une productD3EOrderLine depuis le Cart, il faut d'abord retrouver le ProductD3EOrder
     * @param ProductD3EOrder $productD3EOrder
     * @param $productId
     * @param $qtty
     * @throws Exception
     */
    public function addLineFromCart(ProductD3EOrder $productD3EOrder, $productId, $qtty)
    {
        $productD3EManager = $this->container->get('paprec_catalog.product_D3E_manager');

        try {
            $productD3E = $productD3EManager->get($productId);
            $productD3EOrderLine = new ProductD3EOrderLine();


            $productD3EOrderLine->setProductD3E($productD3E);
            $productD3EOrderLine->setQuantity($qtty);
            $this->addLine($productD3EOrder, $productD3EOrderLine);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }


    }

    /**
     * Calcule le montant total d'un ProductD3EOrder
     * TODO relier le ProductD3EOrder aux PostalCodes pour calculer avec le coefficient multiplicateur du postalCode
     * @param ProductD3EOrder $productD3EOrder
     * @return float|int
     */
    public function calculateTotal(ProductD3EOrder $productD3EOrder)
    {
        $totalAmount = 0;
        foreach ($productD3EOrder->getProductD3EOrderLines() as $productD3EOrderLine) {
            $totalAmount += $this->calculateTotalLine($productD3EOrderLine);
        }
        return $totalAmount;

    }

    /**
     * Retourne le montant total d'une ProductD3EOrderLine
     * @param ProductD3EOrder $productD3EOrder
     * @param ProductD3EOrderLine $productD3EOrderLine
     * @return float|int
     */
    public function calculateTotalLine(ProductD3EOrderLine $productD3EOrderLine)
    {

        return $productD3EOrderLine->getQuantity() * $productD3EOrderLine->getUnitPrice();
    }

}