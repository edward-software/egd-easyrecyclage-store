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
use Paprec\CatalogBundle\Entity\GrilleTarifD3E;
use Symfony\Component\DependencyInjection\ContainerInterface;

class GrilleTarifD3EManager
{

    private $em;
    private $container;

    public function __construct(EntityManagerInterface $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    public function get($grilleTarifD3E){
        $id = $grilleTarifD3E;
        if ($grilleTarifD3E instanceof GrilleTarifD3E) {
            $id = $grilleTarifD3E->getId();
        }
        try {

            $grilleTarifD3E = $this->em->getRepository('PaprecCatalogBundle:GrilleTarifD3E')->find($id);

            if ($grilleTarifD3E === null) {
                throw new EntityNotFoundException('grilleTarifD3ENotFound');
            }

            return $grilleTarifD3E;

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Fonction qui récupère le prix de la grilleTarifLigneD3E qui correspond à la grille, au code postal et à la quantité en param
     *
     * @param GrilleTarifD3E $grilleTarifD3E
     * @param $postalCodeQuote
     * @param $qtty
     * @return int
     */
    public function getUnitPriceByPostalCodeQtty(GrilleTarifD3E $grilleTarifD3E, $postalCodeQuote, $qtty) {
        $lignesPostalCodeMatch = array();
        $return = 0;

        // On parcourt toutes les lignes de la grille pour récupérer celles qui possèdent le postalCodeQuote
        foreach ($grilleTarifD3E->getGrilleTarifLigneD3Es() as $grilleTarifLigneD3E) {
            $postalCodes = str_replace(' ', '', $grilleTarifLigneD3E->getPostalCodes());
            $postalCodesArray = explode(',', $postalCodes);
            foreach ($postalCodesArray as $pC) {
                //on teste juste les deux premiers caractères pour avoir le code du département
                if (substr($pC, 0, 2) == substr($postalCodeQuote, 0, 2)) {
                    $lignesPostalCodeMatch[] = $grilleTarifLigneD3E;
                }
            }
        }
        // On récupère ensuite la ligne dont les tranches Min et Max comprennt la $qtty
        // Attention, pas de contrôle si plusieurs lignes se chevauchent, à l'utilisateur de gérer
        foreach ($lignesPostalCodeMatch as $ligne) {
            if ($qtty >= $ligne->getMinQuantity() && $qtty <= $ligne->getMaxQuantity()) {
                $return = $ligne->getPrice();
            }
        }
        return $return;
    }
}