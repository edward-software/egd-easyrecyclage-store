<?php

namespace Paprec\CatalogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * Argument
 *
 * @ORM\Table(name="arguments")
 * @ORM\Entity(repositoryClass="Paprec\CatalogBundle\Repository\ArgumentRepository")
 */
class Argument
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
     * @ORM\Column(name="description", type="text")
     * @Assert\NotBlank()
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="picto", type="string", length=255, nullable=true)
     */
    private $picto;

    /**************************************************************************************************
     * RELATIONS
     **************************************************************************************************/

    /**
     * @ORM\ManyToMany(targetEntity="Paprec\CatalogBundle\Entity\ProductDI", mappedBy="arguments", cascade={"persist"})
     */
    private $productDIs;



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
     * @return Category
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
     * @return Category
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
     * @return Category
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
     * Set description.
     *
     * @param string $description
     *
     * @return Argument
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set picto.
     *
     * @param string $picto
     *
     * @return Argument
     */
    public function setPicto($picto)
    {
        $this->picto = $picto;

        return $this;
    }

    /**
     * Get picto.
     *
     * @return string
     */
    public function getPicto()
    {
        return $this->picto;
    }

    /**
     * Add productDIs.
     *
     * @param \Paprec\CatalogBundle\Entity\ProductDI $productDIs
     *
     * @return Argument
     */
    public function addproductDIs(\Paprec\CatalogBundle\Entity\ProductDI $productDIs)
    {
        $this->productDIs[] = $productDIs;

        return $this;
    }

    /**
     * Remove productDIs.
     *
     * @param \Paprec\CatalogBundle\Entity\ProductDI $productDIs
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeproductDIs(\Paprec\CatalogBundle\Entity\ProductDI $productDIs)
    {
        return $this->productDIs->removeElement($productDIs);
    }

    /**
     * Get productDIs.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getproductDIs()
    {
        return $this->productDIs;
    }

    public function __toString()
{
    return $this->description;
}
}
