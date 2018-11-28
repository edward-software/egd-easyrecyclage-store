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
use Doctrine\ORM\ORMException;
use Exception;
use Paprec\CommercialBundle\Entity\Agency;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AgencyManager
{
    private $em;
    private $container;

    public function __construct(EntityManagerInterface $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    public function get($agency)
    {
        $id = $agency;
        if ($agency instanceof Agency) {
            $id = $agency->getId();
        }
        try {

            $agency = $this->em->getRepository('PaprecCommercialBundle:Agency')->find($id);

            if ($agency === null) {
                throw new EntityNotFoundException('agencyNotFound');
            }

            return $agency;

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }


    /**
     * Récupération du NOMBRE d'agences appartenant à la division @division
     * qui sont à moins de '@distance' à vol d'oiseau de la position définie par ($lat, $long)
     *
     * @param $long
     * @param $lat
     * @param $division
     * @param $distance
     * @return mixed
     * @throws Exception
     */
    public function getNearbyAgencies($long, $lat, $division, $distance)
    {
        try {
            $sqlDistance = '(6378 * acos(cos(radians(' . $lat . ')) * cos(radians(a.latitude)) * cos(radians(a.longitude) - radians(' . $long . ')) + sin(radians(' . $lat . ')) * sin(radians(a.latitude))))';

            $query = $this->em
                ->getRepository(Agency::class)
                ->createQueryBuilder('a')
                ->select('COUNT(a)')
                ->where('a.deleted IS NULL')
                ->andWhere('a.divisions LIKE :division')
                ->andWhere("" . $sqlDistance . " < :distance")
                ->setParameters(array('distance' => $distance, 'division' => '%' . $division . '%'));

            return $query->getQuery()->getSingleScalarResult();


        } catch (ORMException $e) {
            throw new Exception($e->getMessage(), 500);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }
}