<?php
/**
 * Created by PhpStorm.
 * User: frede
 * Date: 30/11/2018
 * Time: 17:14
 */

namespace Paprec\CommercialBundle\Service;


use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Exception;
use Paprec\CommercialBundle\Entity\QuoteRequestNonCorporate;
use Symfony\Component\DependencyInjection\ContainerInterface;

class QuoteRequestNonCorporateManager
{
    private $em;
    private $container;

    public function __construct(EntityManagerInterface $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    public function get($quoteRequestNonCorporate)
    {
        $id = $quoteRequestNonCorporate;
        if ($quoteRequestNonCorporate instanceof QuoteRequestNonCorporate) {
            $id = $quoteRequestNonCorporate->getId();
        }
        try {

            $quoteRequestNonCorporate = $this->em->getRepository('PaprecCommercialBundle:QuoteRequestNonCorporate')->find($id);

            if ($quoteRequestNonCorporate === null || $this->isDeleted($quoteRequestNonCorporate)) {
                throw new EntityNotFoundException('quoteRequestNonCorporateNotFound');
            }

            return $quoteRequestNonCorporate;

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Vérification qu'à ce jour la demande de devis non entreprise ne soit pas supprimée
     *
     * @param QuoteRequestNonCorporate $quoteRequestNonCorporate
     * @param bool $throwException
     * @return bool
     * @throws EntityNotFoundException
     */
    public function isDeleted(QuoteRequestNonCorporate $quoteRequestNonCorporate, $throwException = false)
    {
        try {
            $now = new \DateTime();
        } catch (Exception $e) {
        }

        if ($quoteRequestNonCorporate->getDeleted() !== null && $quoteRequestNonCorporate->getDeleted() instanceof \DateTime && $quoteRequestNonCorporate->getDeleted() < $now) {

            if ($throwException) {
                throw new EntityNotFoundException('quoteRequestNonCorporateNotFound');
            }

            return true;

        }
        return false;
    }
}