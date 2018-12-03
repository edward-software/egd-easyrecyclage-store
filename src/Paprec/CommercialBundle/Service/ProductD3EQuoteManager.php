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
use Paprec\CommercialBundle\Entity\ProductD3EQuote;
use Paprec\CommercialBundle\Entity\ProductD3EQuoteLine;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ProductD3EQuoteManager
{
    private $em;
    private $container;

    public function __construct(EntityManagerInterface $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    public function get($productD3EQuote)
    {
        $id = $productD3EQuote;
        if ($productD3EQuote instanceof ProductD3EQuote) {
            $id = $productD3EQuote->getId();
        }
        try {

            $productD3EQuote = $this->em->getRepository('PaprecCatalogBundle:ProductD3EQuote')->find($id);

            if ($productD3EQuote === null || $this->isDeleted($productD3EQuote)) {
                throw new EntityNotFoundException('productD3EQuoteNotFound');
            }

            return $productD3EQuote;

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Vérification qu'à ce jour le productD3EQuote ne soit pas supprimé
     *
     * @param ProductD3EQuote $productD3EQuote
     * @param bool $throwException
     * @return bool
     * @throws EntityNotFoundException
     */
    public function isDeleted(ProductD3EQuote $productD3EQuote, $throwException = false)
    {
        try {
            $now = new \DateTime();
        } catch (Exception $e) {
        }

        if ($productD3EQuote->getDeleted() !== null && $productD3EQuote->getDeleted() instanceof \DateTime && $productD3EQuote->getDeleted() < $now) {

            if ($throwException) {
                throw new EntityNotFoundException('productD3EQuoteNotFound');
            }

            return true;

        }
        return false;
    }
    
    /**
     * Ajoute une productD3EQuoteLine à un productD3EQuote
     * @param ProductD3EQuote $productD3EQuote
     * @param ProductD3EQuoteLine $productD3EQuoteLine
     */
    public function addLine(ProductD3EQuote $productD3EQuote, ProductD3EQuoteLine $productD3EQuoteLine)
    {
        $priceListD3EManager = $this->container->get('paprec_catalog.price_list_d3e_manager');

        //Récupération de la grille liée au produit
        $priceList = $productD3EQuoteLine->getProductD3E()->getPriceListD3E();

        // On check s'il existe déjà une ligne pour ce produit, pour l'incrémenter
        $currentQuoteLine = $this->em->getRepository('PaprecCommercialBundle:ProductD3EQuoteLine')->findOneBy(
            array(
                'productD3EQuote' => $productD3EQuote,
                'productD3E' => $productD3EQuoteLine->getProductD3E()
            )
        );

        if ($currentQuoteLine) {
            $quantity = $productD3EQuoteLine->getQuantity() + $currentQuoteLine->getQuantity();
            $currentQuoteLine->setQuantity($quantity);

            // On vérifie de prix unitaire du produit exisntant qui a pu changer si l'on a changé de tranche
            // en augmentant la quantité
            $unitPrice = $priceListD3EManager->getUnitPriceByPostalCodeQtty($priceList, $productD3EQuote->getPostalCode(), $currentQuoteLine->getQuantity());
            $currentQuoteLine->setUnitPrice($unitPrice);

            //On recalcule le montant total de la ligne ainsi que celui du devis complet
            $totalLine = $this->calculateTotalLine($currentQuoteLine);
            $currentQuoteLine->setTotalAmount($totalLine);
            $this->em->flush();
        } else {
            // On lie la grille et la ligne
            $productD3EQuoteLine->setProductD3EQuote($productD3EQuote);
            $productD3EQuote->addProductD3EQuoteLine($productD3EQuoteLine);

            $productD3EQuoteLine->setProductName($productD3EQuoteLine->getProductD3E()->getName());

            // Récupération du prix unitaire du produit
            $unitPrice = $priceListD3EManager->getUnitPriceByPostalCodeQtty($priceList, $productD3EQuote->getPostalCode(), $productD3EQuoteLine->getQuantity());
            $productD3EQuoteLine->setUnitPrice($unitPrice);
            $this->em->persist($productD3EQuoteLine);

            //On recalcule le montant total de la ligne ainsi que celui du devis complet
            $totalLine = $this->calculateTotalLine($productD3EQuoteLine);
            $productD3EQuoteLine->setTotalAmount($totalLine);
            $this->em->flush();
        }

        $total = $this->calculateTotal($productD3EQuote);
        $productD3EQuote->setTotalAmount($total);
        $this->em->flush();
    }

    /**
     * Met à jour les montants totaux après l'édition d'une ligne
     * @param ProductD3EQuote $productD3EQuote
     * @param ProductD3EQuoteLine $productD3EQuoteLine
     */
    public function editLine(ProductD3EQuote $productD3EQuote, ProductD3EQuoteLine $productD3EQuoteLine)
    {
        $productD3EQuoteManager = $this->container->get('paprec_catalog.price_list_d3e_manager');

        // Récupération du prix unitaire du produit
        $unitPrice = $productD3EQuoteManager->getUnitPriceByPostalCodeQtty($productD3EQuoteLine->getProductD3E()->getPriceListD3E(), $productD3EQuote->getPostalCode(), $productD3EQuoteLine->getQuantity());
        $productD3EQuoteLine->setUnitPrice($unitPrice);

        $totalLine = $this->calculateTotalLine($productD3EQuoteLine);
        $productD3EQuoteLine->setTotalAmount($totalLine);
        $this->em->flush();

        $total = $this->calculateTotal($productD3EQuote);
        $productD3EQuote->setTotalAmount($total);
        $this->em->flush();
    }

    /**
     * Pour ajouter une productD3EQuoteLine depuis le Cart, il faut d'abord retrouver le ProductD3E
     * @param ProductD3EQuote $productD3EQuote
     * @param $productId
     * @param $qtty
     * @throws Exception
     */
    public function addLineFromCart(ProductD3EQuote $productD3EQuote, $productId, $qtty, $optHandling, $optSerialNumberStmt, $optDestruction)
    {
        $productD3EManager = $this->container->get('paprec_catalog.product_D3E_manager');

        try {
            $productD3E = $productD3EManager->get($productId);
            $productD3EQuoteLine = new ProductD3EQuoteLine();

            $productD3EQuoteLine->setOptHandling($optHandling);
            $productD3EQuoteLine->setOptSerialNumberStmt($optSerialNumberStmt);
            $productD3EQuoteLine->setOptDestruction($optDestruction);
            $productD3EQuoteLine->setProductD3E($productD3E);
            $productD3EQuoteLine->setQuantity($qtty);
            $this->addLine($productD3EQuote, $productD3EQuoteLine);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }


    }

    /**
     * Calcule le montant total d'un ProductD3EQuote
     * TODO relier le ProductD3EQuote aux PostalCodes pour calculer avec le coefficient multiplicateur du postalCode
     * @param ProductD3EQuote $productD3EQuote
     * @return float|int
     */
    public function calculateTotal(ProductD3EQuote $productD3EQuote)
    {
        $totalAmount = 0;
        foreach ($productD3EQuote->getProductD3EQuoteLines() as $productD3EQuoteLine) {
            $totalAmount += $this->calculateTotalLine($productD3EQuoteLine);
        }
        return $totalAmount;

    }

    /**
     * Retourne le montant total d'une ProductD3EQuoteLine
     * @param ProductD3EQuote $productD3EQuote
     * @param ProductD3EQuoteLine $productD3EQuoteLine
     * @return float|int
     */
    public function calculateTotalLine(ProductD3EQuoteLine $productD3EQuoteLine)
    {

        return $productD3EQuoteLine->getQuantity() * $productD3EQuoteLine->getUnitPrice();
    }

}