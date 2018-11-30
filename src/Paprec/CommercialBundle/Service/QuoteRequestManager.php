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
use Paprec\CommercialBundle\Entity\QuoteRequest;
use Symfony\Component\DependencyInjection\ContainerInterface;

class QuoteRequestManager
{
    private $em;
    private $container;

    public function __construct(EntityManagerInterface $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    public function get($quoteRequest)
    {
        $id = $quoteRequest;
        if ($quoteRequest instanceof QuoteRequest) {
            $id = $quoteRequest->getId();
        }
        try {

            $quoteRequest = $this->em->getRepository('PaprecCommercialBundle:QuoteRequest')->find($id);

            if ($quoteRequest === null || $this->isDeleted($quoteRequest)) {
                throw new EntityNotFoundException('quoteRequestNotFound');
            }

            return $quoteRequest;

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Vérification qu'à ce jour la demande de devis ne soit pas supprimée
     *
     * @param QuoteRequest $quoteRequest
     * @param bool $throwException
     * @return bool
     * @throws EntityNotFoundException
     */
    public function isDeleted(QuoteRequest $quoteRequest, $throwException = false)
    {
        try {
            $now = new \DateTime();
        } catch (Exception $e) {
        }

        if ($quoteRequest->getDeleted() !== null && $quoteRequest->getDeleted() instanceof \DateTime && $quoteRequest->getDeleted() < $now) {

            if ($throwException) {
                throw new EntityNotFoundException('quoteRequestNotFound');
            }

            return true;

        }
        return false;
    }
}