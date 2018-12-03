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
use Paprec\CommercialBundle\Entity\CallBack;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CallBackManager
{
    private $em;
    private $container;

    public function __construct(EntityManagerInterface $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    public function get($callBack)
    {
        $id = $callBack;
        if ($callBack instanceof CallBack) {
            $id = $callBack->getId();
        }
        try {

            $callBack = $this->em->getRepository('PaprecCommercialBundle:CallBack')->find($id);

            if ($callBack === null || $this->isDeleted($callBack)) {
                throw new EntityNotFoundException('callBackNotFound');
            }

            return $callBack;

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Vérification qu'à ce jour la demande de rappel ne soit pas supprimée
     *
     * @param CallBack $callBack
     * @param bool $throwException
     * @return bool
     * @throws EntityNotFoundException
     */
    public function isDeleted(CallBack $callBack, $throwException = false)
    {
        try {
            $now = new \DateTime();
        } catch (Exception $e) {
        }

        if ($callBack->getDeleted() !== null && $callBack->getDeleted() instanceof \DateTime && $callBack->getDeleted() < $now) {

            if ($throwException) {
                throw new EntityNotFoundException('callBackNotFound');
            }

            return true;

        }
        return false;
    }
}