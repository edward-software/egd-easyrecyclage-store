<?php

namespace Paprec\CommercialBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * BusinessLine
 *
 * @ORM\Table(name="businessLines")
 * @ORM\Entity(repositoryClass="Paprec\CommercialBundle\Repository\BusinessLineRepository")
 */
class BusinessLine
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
     * @var \DateTime
     *
     * @ORM\Column(name="dateCreation", type="datetime")
     */
    private $dateCreation;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateUpdate", type="datetime", nullable=true)
     */
    private $dateUpdate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="deleted", type="datetime", nullable=true)
     */
    private $deleted;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @var array|null
     *
     * @ORM\Column(name="division", type="string", nullable=true)
     * @Assert\NotBlank()
     */
    private $division;

    /**
     * @ORM\OneToMany(targetEntity="Paprec\CommercialBundle\Entity\ProductDIQuote", mappedBy="businessLine")
     */
    private $productDIQuotes;

    /**
     * BusinessLine constructor.
     */
    public function __construct()
    {
        $this->dateCreation = new \DateTime();
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set dateCreation.
     *
     * @param \DateTime $dateCreation
     *
     * @return BusinessLine
     */
    public function setDateCreation($dateCreation)
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    /**
     * Get dateCreation.
     *
     * @return \DateTime
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    /**
     * Set dateUpdate.
     *
     * @param \DateTime|null $dateUpdate
     *
     * @return BusinessLine
     */
    public function setDateUpdate($dateUpdate = null)
    {
        $this->dateUpdate = $dateUpdate;

        return $this;
    }

    /**
     * Get dateUpdate.
     *
     * @return \DateTime|null
     */
    public function getDateUpdate()
    {
        return $this->dateUpdate;
    }

    /**
     * Set deleted.
     *
     * @param \DateTime|null $deleted
     *
     * @return BusinessLine
     */
    public function setDeleted($deleted = null)
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * Get deleted.
     *
     * @return \DateTime|null
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return BusinessLine
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set division.
     *
     * @param string|null $division
     *
     * @return BusinessLine
     */
    public function setDivision($division = null)
    {
        $this->division = $division;

        return $this;
    }

    /**
     * Get division.
     *
     * @return string|null
     */
    public function getDivision()
    {
        return $this->division;
    }

    /**
     * Add productDIQuote.
     *
     * @param \Paprec\CommercialBundle\Entity\ProductDIQuote $productDIQuote
     *
     * @return BusinessLine
     */
    public function addProductDIQuote(\Paprec\CommercialBundle\Entity\ProductDIQuote $productDIQuote)
    {
        $this->productDIQuotes[] = $productDIQuote;

        return $this;
    }

    /**
     * Remove productDIQuote.
     *
     * @param \Paprec\CommercialBundle\Entity\ProductDIQuote $productDIQuote
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeProductDIQuote(\Paprec\CommercialBundle\Entity\ProductDIQuote $productDIQuote)
    {
        return $this->productDIQuotes->removeElement($productDIQuote);
    }

    /**
     * Get productDIQuotes.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProductDIQuotes()
    {
        return $this->productDIQuotes;
    }
}
