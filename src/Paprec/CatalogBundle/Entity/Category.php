<?php

namespace Paprec\CatalogBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;



/**
 * Category
 *
 * @ORM\Table(name="categories")
 * @ORM\Entity(repositoryClass="Paprec\CatalogBundle\Repository\CategoryRepository")
 * @UniqueEntity("name")
 */
class Category
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
     * @ORM\Column(name="name", type="string", length=255, unique=true)
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
     * @var string
     * @ORM\Column(name="position", type="integer")
     * @Assert\NotBlank()
     */
    private $position;

    /**
     * @var bool
     * @ORM\Column(name="enabled", type="boolean")
     * @Assert\NotBlank()
     */
    protected $enabled;

    /**
     * @ORM\Column(name="picto", type="string", nullable=true)
     */
    private $picto;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**************************************************************************************************
     * RELATIONS
     **************************************************************************************************/

    /**
     * @ORM\OneToMany(targetEntity="Paprec\CatalogBundle\Entity\ProductDICategory", mappedBy="category",  cascade={"all"})
     */
    private $productDICategories;


    public function __construct()
    {
        $this->dateCreation = new \DateTime();
        $this->productDICategories = new ArrayCollection();

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
     * Set name.
     *
     * @param string $name
     *
     * @return Category
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
     * @return Category
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
     * Set position.
     *
     * @param int $position
     *
     * @return Category
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position.
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set enabled.
     *
     * @param bool $enabled
     *
     * @return Category
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Get enabled.
     *
     * @return bool
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set picto.
     *
     * @param string|null $picto
     *
     * @return Category
     */
    public function setPicto($picto = null)
    {
        $this->picto = $picto;

        return $this;
    }

    /**
     * Get picto.
     *
     * @return string|null
     */
    public function getPicto()
    {
        return $this->picto;
    }

    /**
     * Set description.
     *
     * @param string|null $description
     *
     * @return Category
     */
    public function setDescription($description = null)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Add productDICategory.
     *
     * @param \Paprec\CatalogBundle\Entity\ProductDICategory $productDICategory
     *
     * @return Category
     */
    public function addProductDICategory(\Paprec\CatalogBundle\Entity\ProductDICategory $productDICategory)
    {
        $this->productDICategories[] = $productDICategory;

        return $this;
    }

    /**
     * Remove productDICategory.
     *
     * @param \Paprec\CatalogBundle\Entity\ProductDICategory $productDICategory
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeProductDICategory(\Paprec\CatalogBundle\Entity\ProductDICategory $productDICategory)
    {
        return $this->productDICategories->removeElement($productDICategory);
    }

    /**
     * Get productDICategories.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProductDICategories()
    {
        return $this->productDICategories;
    }

    public function __toString()
    {
        return $this->name;
    }
}
