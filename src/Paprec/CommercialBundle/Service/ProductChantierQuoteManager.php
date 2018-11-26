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
use Paprec\CommercialBundle\Entity\ProductChantierQuote;
use Paprec\CommercialBundle\Entity\ProductChantierQuoteLine;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ProductChantierQuoteManager
{
    private $em;
    private $container;

    public function __construct(EntityManagerInterface $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    public function get($productChantierQuote)
    {
        $id = $productChantierQuote;
        if ($productChantierQuote instanceof ProductChantierQuote) {
            $id = $productChantierQuote->getId();
        }
        try {

            $productChantierQuote = $this->em->getRepository('PaprecCatalogBundle:ProductChantierQuote')->find($id);

            if ($productChantierQuote === null) {
                throw new EntityNotFoundException('productChantierQuoteNotFound');
            }

            return $productChantierQuote;

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Ajoute une productChantierQuoteLine à un productChantierQuote
     * @param ProductChantierQuote $productChantierQuote
     * @param ProductChantierQuoteLine $productChantierQuoteLine
     */
    public function addLine(ProductChantierQuote $productChantierQuote, ProductChantierQuoteLine $productChantierQuoteLine)
    {
        // On check s'il existe déjà une ligne pour ce produit, pour l'incrémenter
        $currentQuoteLine = $this->em->getRepository('PaprecCommercialBundle:ProductChantierQuoteLine')->findOneBy(
            array(
                'productChantierQuote' => $productChantierQuote,
                'productChantier' => $productChantierQuoteLine->getProductChantier(),
                'category' => $productChantierQuoteLine->getCategory()
            )
        );

        if ($currentQuoteLine) {
            $quantity = $productChantierQuoteLine->getQuantity() + $currentQuoteLine->getQuantity();
            $currentQuoteLine->setQuantity($quantity);
        } else {
            $productChantierQuoteLine->setProductChantierQuote($productChantierQuote);
            $productChantierQuote->addProductChantierQuoteLine($productChantierQuoteLine);
            $productChantierCategory = $this->em->getRepository('PaprecCatalogBundle:ProductChantierCategory')->findOneBy(
                array(
                    'productChantier' => $productChantierQuoteLine->getProductChantier(),
                    'category' => $productChantierQuoteLine->getCategory()
                )
            );
            $productChantierQuoteLine->setUnitPrice($productChantierCategory->getUnitPrice());
            $productChantierQuoteLine->setProductName($productChantierQuoteLine->getProductChantier()->getName());
            $productChantierQuoteLine->setCategoryName($productChantierQuoteLine->getCategory()->getName());

            $this->em->persist($productChantierQuoteLine);
        }

        //On recalcule le montant total de la ligne ainsi que celui du devis complet
        $totalLine = $this->calculateTotalLine($productChantierQuoteLine);
        $productChantierQuoteLine->setTotalAmount($totalLine);
        $this->em->flush();

        $total = $this->calculateTotal($productChantierQuote);
        $productChantierQuote->setTotalAmount($total);
        $this->em->flush();
    }

    /**
     * Met à jour les montants totaux après l'édition d'une ligne
     * @param ProductChantierQuote $productChantierQuote
     * @param ProductChantierQuoteLine $productChantierQuoteLine
     */
    public function editLine(ProductChantierQuote $productChantierQuote, ProductChantierQuoteLine $productChantierQuoteLine)
    {
        $totalLine = $this->calculateTotalLine($productChantierQuoteLine);
        $productChantierQuoteLine->setTotalAmount($totalLine);
        $this->em->flush();

        $total = $this->calculateTotal($productChantierQuote);
        $productChantierQuote->setTotalAmount($total);
        $this->em->flush();
    }

    /**
     * Pour ajouter une productChantierQuoteLine depuis le Cart, il faut d'abord retrouver le ProductChantier
     * @param ProductChantierQuote $productChantierQuote
     * @param $productId
     * @param $qtty
     * @throws Exception
     */
    public function addLineFromCart(ProductChantierQuote $productChantierQuote, $productId, $qtty, $categoryId)
    {
        $productChantierManager = $this->container->get('paprec_catalog.product_chantier_manager');
        $categoryManager = $this->container->get('paprec_catalog.category_manager');

        try {
            $productChantier = $productChantierManager->get($productId);
            $productChantierQuoteLine = new ProductChantierQuoteLine();
            $category = $categoryManager->get($categoryId);


            $productChantierQuoteLine->setProductChantier($productChantier);
            $productChantierQuoteLine->setCategory($category);
            $productChantierQuoteLine->setQuantity($qtty);
            $this->addLine($productChantierQuote, $productChantierQuoteLine);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }


    }

    /**
     * Calcule le montant total d'un ProductChantierQuote
     * TODO relier le ProductChantierQuote aux PostalCodes pour calculer avec le coefficient multiplicateur du postalCode
     * @param ProductChantierQuote $productChantierQuote
     * @return float|int
     */
    public function calculateTotal(ProductChantierQuote $productChantierQuote)
    {
        $totalAmount = 0;
        foreach ($productChantierQuote->getProductChantierQuoteLines() as $productChantierQuoteLine) {
            $totalAmount += $this->calculateTotalLine($productChantierQuoteLine);
        }
        return $totalAmount;

    }

    /**
     * Retourne le montant total d'une ProductChantierQuoteLine
     * @param ProductChantierQuote $productChantierQuote
     * @param ProductChantierQuoteLine $productChantierQuoteLine
     * @return float|int
     */
    public function calculateTotalLine(ProductChantierQuoteLine $productChantierQuoteLine)
    {

        return $productChantierQuoteLine->getQuantity() * $productChantierQuoteLine->getUnitPrice();
    }

}