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
use Paprec\CommercialBundle\Entity\ProductDIQuote;
use Paprec\CommercialBundle\Entity\ProductDIQuoteLine;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ProductDIQuoteManager
{
    private $em;
    private $container;

    public function __construct(EntityManagerInterface $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    public function get($productDIQuote)
    {
        $id = $productDIQuote;
        if ($productDIQuote instanceof ProductDIQuote) {
            $id = $productDIQuote->getId();
        }
        try {

            $productDIQuote = $this->em->getRepository('PaprecCatalogBundle:ProductDIQuote')->find($id);

            if ($productDIQuote === null || $this->isDeleted($productDIQuote)) {
                throw new EntityNotFoundException('productDIQuoteNotFound');
            }

            return $productDIQuote;

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Vérification qu'à ce jour le ProductDIQuote ne soit pas supprimé
     *
     * @param ProductDIQuote $productDIQuote
     * @param bool $throwException
     * @return bool
     * @throws EntityNotFoundException
     */
    public function isDeleted(ProductDIQuote $productDIQuote, $throwException = false)
    {
        try {
            $now = new \DateTime();
        } catch (Exception $e) {
        }

        if ($productDIQuote->getDeleted() !== null && $productDIQuote->getDeleted() instanceof \DateTime && $productDIQuote->getDeleted() < $now) {

            if ($throwException) {
                throw new EntityNotFoundException('productDIQuoteNotFound');
            }

            return true;

        }
        return false;
    }

    /**
     * Ajoute une productQuoteDiLine à un productDIQuote
     * @param ProductDIQuote $productDIQuote
     * @param ProductDIQuoteLine $productDIQuoteLine
     */
    public function addLine(ProductDIQuote $productDIQuote, ProductDIQuoteLine $productDIQuoteLine)
    {

        // On check s'il existe déjà une ligne pour ce produit, pour l'incrémenter
        $currentQuoteLine = $this->em->getRepository('PaprecCommercialBundle:ProductDIQuoteLine')->findOneBy(
            array(
                'productDIQuote' => $productDIQuote,
                'productDI' => $productDIQuoteLine->getProductDI(),
                'category' => $productDIQuoteLine->getCategory()
            )
        );

        if ($currentQuoteLine) {
            $quantity = $productDIQuoteLine->getQuantity() + $currentQuoteLine->getQuantity();
            $currentQuoteLine->setQuantity($quantity);
        } else {
            $productDIQuoteLine->setProductDIQuote($productDIQuote);
            $productDIQuote->addProductDIQuoteLine($productDIQuoteLine);
            $productDICategory = $this->em->getRepository('PaprecCatalogBundle:ProductDICategory')->findOneBy(
                array(
                    'productDI' => $productDIQuoteLine->getProductDI(),
                    'category' => $productDIQuoteLine->getCategory()
                )
            );
            $productDIQuoteLine->setUnitPrice($productDICategory->getUnitPrice());
            $productDIQuoteLine->setProductName($productDIQuoteLine->getProductDI()->getName());
            $productDIQuoteLine->setCategoryName($productDIQuoteLine->getCategory()->getName());

            $this->em->persist($productDIQuoteLine);
        }

        //On recalcule le montant total de la ligne ainsi que celui du devis complet
        $totalLine = $this->calculateTotalLine($productDIQuoteLine);
        $productDIQuoteLine->setTotalAmount($totalLine);
        $this->em->flush();

        $total = $this->calculateTotal($productDIQuote);
        $productDIQuote->setTotalAmount($total);
        $this->em->flush();
    }

    /**
     * Met à jour les montants totaux après l'édition d'une ligne
     * @param ProductDIQuote $productDIQuote
     * @param ProductDIQuoteLine $productDIQuoteLine
     */
    public function editLine(ProductDIQuote $productDIQuote, ProductDIQuoteLine $productDIQuoteLine)
    {
        $totalLine = $this->calculateTotalLine($productDIQuoteLine);
        $productDIQuoteLine->setTotalAmount($totalLine);
        $this->em->flush();

        $total = $this->calculateTotal($productDIQuote);
        $productDIQuote->setTotalAmount($total);
        $this->em->flush();
    }

    /**
     * Pour ajouter une productDIQuoteLine depuis le Cart, il faut d'abord retrouver le ProductDI
     * @param ProductDIQuote $productDIQuote
     * @param $productId
     * @param $qtty
     * @throws Exception
     */
    public function addLineFromCart(ProductDIQuote $productDIQuote, $productId, $qtty, $categoryId)
    {
        $productDIManager = $this->container->get('paprec_catalog.product_di_manager');
        $categoryManager = $this->container->get('paprec_catalog.category_manager');

        try {
            $productDI = $productDIManager->get($productId);
            $productDIQuoteLine = new ProductDIQuoteLine();
            $category = $categoryManager->get($categoryId);


            $productDIQuoteLine->setProductDI($productDI);
            $productDIQuoteLine->setCategory($category);
            $productDIQuoteLine->setQuantity($qtty);
            $this->addLine($productDIQuote, $productDIQuoteLine);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }


    }

    /**
     * Calcule le montant total d'un ProductDIQuote
     * TODO relier le ProductDIQuote aux PostalCodes pour calculer avec le coefficient multiplicateur du postalCode
     * @param ProductDIQuote $productDIQuote
     * @return float|int
     */
    public function calculateTotal(ProductDIQuote $productDIQuote)
    {
        $totalAmount = 0;
        foreach ($productDIQuote->getProductDIQuoteLines() as $productDIQuoteLine) {
            $totalAmount += $this->calculateTotalLine($productDIQuoteLine);
        }
        return $totalAmount;

    }

    /**
     * Retourne le montant total d'une ProductDIQuoteLine
     * @param ProductDIQuote $productDIQuote
     * @param ProductDIQuoteLine $productDIQuoteLine
     * @return float|int
     */
    public function calculateTotalLine(ProductDIQuoteLine $productDIQuoteLine)
    {

        return $productDIQuoteLine->getQuantity() * $productDIQuoteLine->getUnitPrice();
    }

}