<?php

namespace Paprec\CatalogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * PostalCode
 *
 * @ORM\Table(name="postalCodes")
 * @ORM\Entity(repositoryClass="Paprec\CatalogBundle\Repository\PostalCodeRepository")
 */
class PostalCode
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="code", type="string", length=20)
     * @Assert\NotBlank()
     * @Assert\Regex(
     *     pattern="/^\d{2}(\*|(?:\d{3}))$/",
     *     message="Les codes postaux doivent être aux formats : 75* ou 92150"
     * )
     */
    private $code;

    /**
     * @var int
     *
     * @ORM\Column(name="rate", type="integer")
     * @Assert\NotBlank()
     * @Assert\Regex(
     *     pattern="/^\d{1,2}((\.|\,)\d{1,2})?$/",
     *     match=true,
     *     message="la valeur doit être un nombre entre 0 et 99,99 (ou 99.99)"
     * )
     */
    private $rate;

    /**
     * @var array|null
     *
     * @ORM\Column(name="division", type="string", nullable=true)
     * @Assert\NotBlank()
     */
    private $division;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="deleted", type="datetime", nullable=true)
     */
    private $deleted;

    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Set division.
     *
     * @param array|null $division
     *
     * @return PostalCode
     */
    public function setDivision($division = null)
    {
        $this->division = $division;

        return $this;
    }

    /**
     * Get division.
     *
     * @return array|null
     */
    public function getDivision()
    {
        return $this->division;
    }

    /**
     * Set deleted
     *
     * @param \DateTime $deleted
     * @return PostalCode
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * Get deleted
     *
     * @return \DateTime 
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return PostalCode
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }



    /**
     * Set rate.
     *
     * @param int $rate
     *
     * @return PostalCode
     */
    public function setRate($rate)
    {
        $this->rate = $rate;

        return $this;
    }

    /**
     * Get rate.
     *
     * @return int
     */
    public function getRate()
    {
        return $this->rate;
    }
}
