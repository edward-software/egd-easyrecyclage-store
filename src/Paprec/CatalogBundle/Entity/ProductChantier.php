<?php

namespace Paprec\CatalogBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ProductChantier
 *
 * @ORM\Table(name="productChantiers")
 * @ORM\Entity(repositoryClass="Paprec\CatalogBundle\Repository\ProductChantierRepository")
 */
class ProductChantier
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
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     * @Assert\NotBlank()
     */
    private $description;

    /**
     * @var float
     * Le volume du produit
     * @ORM\Column(name="capacity", type="float")
     * @Assert\NotBlank()
     * @Assert\Type(type="float")
     */
    private $capacity;

    /**
     * @var string
     * L'unité du volume du produit (litre, m²,..)
     * @ORM\Column(name="capacityUnit", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $capacityUnit;

    /**
     * @var string
     *
     * @ORM\Column(name="dimensions", type="string", length=500)
     * @Assert\NotBlank()
     */
    private $dimensions;

    /**
     * @var string
     * Lien description, URL vers une page de description longue du produit
     * @ORM\Column(name="reference", type="string", length=255, nullable=true)
     */
    private $reference;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isDisplayed", type="boolean")
     * @Assert\NotBlank()
     */
    private $isDisplayed;


    /**
     * @var text
     * @ORM\Column(name="availablePostalCodeIds", type="text", nullable=true)
     */
    private $availablePostalCodes;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isPayableOnline", type="boolean")
     * @Assert\NotBlank()
     */
    private $isPayableOnline;

    /**************************************************************************************************
     * RELATIONS
     **************************************************************************************************/

    /**
     * @ORM\ManyToMany(targetEntity="Paprec\CatalogBundle\Entity\Argument", inversedBy="productChantiers")
     */
    private $arguments;


    /**
     * @ORM\OneToMany(targetEntity="Paprec\CatalogBundle\Entity\Picture", mappedBy="productChantier", cascade={"all"})
     */
    private $pictures;


    /**
     * @ORM\OneToMany(targetEntity="Paprec\CatalogBundle\Entity\ProductChantierCategory", mappedBy="productChantier", cascade={"all"})
     */
    private $productChantierCategories;

    private $categories;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->dateCreation = new \DateTime();
        $this->arguments = new ArrayCollection();
        $this->productChantierCategories = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->pictures = new ArrayCollection();
    }


    /**
     * Exemple pris à cette adresse : http://www.prowebdev.us/2012/07/symfnoy2-many-to-many-relation-with.html
     *
     */
    public function getCategories()
    {
        $categories = new ArrayCollection();

        foreach ($this->productChantierCategories as $productChantierCategory) {
            $categories[] = $productChantierCategory->getCategory();
        }

        return $categories;
    }

    public function setCategories($categories)
    {
        foreach ($categories as $category) {
            $pC = new ProductChantierCategory();

            $pC->setProductChantier($this);
            $pC->setCategory($category);

            $this->addProductChantierCategory($pC);
        }

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
     * Set name.
     *
     * @param string $name
     *
     * @return ProductChantier
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
     * Set description.
     *
     * @param string $description
     *
     * @return ProductChantier
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
     * Set capacity.
     *
     * @param float $capacity
     *
     * @return ProductChantier
     */
    public function setCapacity($capacity)
    {
        $this->capacity = $capacity;

        return $this;
    }

    /**
     * Get capacity.
     *
     * @return float
     */
    public function getCapacity()
    {
        return $this->capacity;
    }

    /**
     * Set dimensions.
     *
     * @param string $dimensions
     *
     * @return ProductChantier
     */
    public function setDimensions($dimensions)
    {
        $this->dimensions = $dimensions;

        return $this;
    }

    /**
     * Get dimensions.
     *
     * @return string
     */
    public function getDimensions()
    {
        return $this->dimensions;
    }

    /**
     * Set reference.
     *
     * @param string|null $reference
     *
     * @return ProductChantier
     */
    public function setReference($reference = null)
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * Get reference.
     *
     * @return string|null
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * Set capacityUnit.
     *
     * @param string $capacityUnit
     *
     * @return ProductChantier
     */
    public function setCapacityUnit($capacityUnit)
    {
        $this->capacityUnit = $capacityUnit;

        return $this;
    }

    /**
     * Get capacityUnit.
     *
     * @return string
     */
    public function getCapacityUnit()
    {
        return $this->capacityUnit;
    }

    /**
     * Set dateCreation.
     *
     * @param \DateTime $dateCreation
     *
     * @return ProductChantier
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
     * @return ProductChantier
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
     * @return ProductChantier
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
     * Set availablePostalCodes.
     *
     * @param string|null $availablePostalCodes
     *
     * @return ProductChantier
     */
    public function setAvailablePostalCodes($availablePostalCodes = null)
    {
        $this->availablePostalCodes = $availablePostalCodes;

        return $this;
    }

    /**
     * Get availablePostalCodes.
     *
     * @return string|null
     */
    public function getAvailablePostalCodes()
    {
        return $this->availablePostalCodes;
    }


    /**
     * Set isDisplayed.
     *
     * @param bool $isDisplayed
     *
     * @return ProductChantier
     */
    public function setIsDisplayed($isDisplayed)
    {
        $this->isDisplayed = $isDisplayed;

        return $this;
    }

    /**
     * Get isDisplayed.
     *
     * @return bool
     */
    public function getIsDisplayed()
    {
        return $this->isDisplayed;
    }

    /**
     * Set isPayableOnline
     *
     * @param bool $isPayableOnline
     *
     * @return bool
     */
    public function setIsPayableOnline($isPayableOnline)
    {
        $this->isPayableOnline = $isPayableOnline;
    }

    /**
     * Get isPayableOnline
     *
     * @return bool
     */
    public function getIsPayableOnline()
    {
        return $this->isPayableOnline;
    }


    /**
     * Add argument.
     *
     * @param \Paprec\CatalogBundle\Entity\Argument $argument
     *
     * @return ProductChantier
     */
    public function addArgument(\Paprec\CatalogBundle\Entity\Argument $argument)
    {
        $this->arguments[] = $argument;

        return $this;
    }

    /**
     * Remove argument.
     *
     * @param \Paprec\CatalogBundle\Entity\Argument $argument
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeArgument(\Paprec\CatalogBundle\Entity\Argument $argument)
    {
        return $this->arguments->removeElement($argument);
    }

    /**
     * Get arguments.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * Get productChantierCategories.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProductChantierCategories()
    {
        return $this->productChantierCategories;
    }

    /**
     * Add productChantierCategory.
     *
     * @param \Paprec\CatalogBundle\Entity\ProductChantierCategory $productChantierCategory
     *
     * @return ProductChantier
     */
    public function addProductChantierCategory(\Paprec\CatalogBundle\Entity\ProductChantierCategory $productChantierCategory)
    {
        $this->productChantierCategories[] = $productChantierCategory;

        return $this;
    }

    /**
     * Remove productChantierCategory.
     *
     * @param \Paprec\CatalogBundle\Entity\ProductChantierCategory $productChantierCategory
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeProductChantierCategory(\Paprec\CatalogBundle\Entity\ProductChantierCategory $productChantierCategory)
    {
        return $this->productChantierCategories->removeElement($productChantierCategory);
    }

    /**
     * Add picture.
     *
     * @param \Paprec\CatalogBundle\Entity\Picture $picture
     *
     * @return ProductChantier
     */
    public function addPicture(\Paprec\CatalogBundle\Entity\Picture $picture)
    {
        $this->pictures[] = $picture;

        return $this;
    }

    /**
     * Remove picture.
     *
     * @param \Paprec\CatalogBundle\Entity\Picture $picture
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removePicture(\Paprec\CatalogBundle\Entity\Picture $picture)
    {
        return $this->pictures->removeElement($picture);
    }

    /**
     * Get pictures.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPictures()
    {
        return $this->pictures;
    }

    public function getPilotPictures() {
        $pilotPictures = array();
        foreach($this->pictures as $picture) {
            if($picture->getType() == 'PilotPicture') {
                $pilotPictures[] = $picture;
            }
        }
        return $pilotPictures;
    }

    public function getPictos() {
        $pictos = array();
        foreach($this->pictures as $picture) {
            if($picture->getType() == 'Picto') {
                $pictos[] = $picture;
            }
        }
        return $pictos;
    }

    public function getPicturesPictures() {
        $pictures = array();
        foreach($this->pictures as $picture) {
            if($picture->getType() == 'Picture') {
                $pictures[] = $picture;
            }
        }
        return $pictures;
    }
}