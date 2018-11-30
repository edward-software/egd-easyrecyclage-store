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
use Paprec\CommercialBundle\Entity\ContactUs;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ContactUsManager
{
    private $em;
    private $container;

    public function __construct(EntityManagerInterface $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    public function get($contactUs)
    {
        $id = $contactUs;
        if ($contactUs instanceof ContactUs) {
            $id = $contactUs->getId();
        }
        try {

            $contactUs = $this->em->getRepository('PaprecCommercialBundle:ContactUs')->find($id);

            if ($contactUs === null || $this->isDeleted($contactUs)) {
                throw new EntityNotFoundException('contactUsNotFound');
            }

            return $contactUs;

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Vérification qu'à ce jour la demande de contact ne soit pas supprimée
     *
     * @param ContactUs $contactUs
     * @param bool $throwException
     * @return bool
     * @throws EntityNotFoundException
     */
    public function isDeleted(ContactUs $contactUs, $throwException = false)
    {
        try {
            $now = new \DateTime();
        } catch (Exception $e) {
        }

        if ($contactUs->getDeleted() !== null && $contactUs->getDeleted() instanceof \DateTime && $contactUs->getDeleted() < $now) {

            if ($throwException) {
                throw new EntityNotFoundException('contactUsNotFound');
            }

            return true;

        }
        return false;
    }
}