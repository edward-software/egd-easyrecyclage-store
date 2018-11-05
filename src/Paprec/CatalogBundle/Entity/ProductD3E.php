<?php

namespace Paprec\CatalogBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * ProductD3E
 *
 * @ORM\Table(name="productD3Es")
 * @ORM\Entity(repositoryClass="Paprec\CatalogBundle\Repository\ProductD3ERepository")
 */
class ProductD3E
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
     * @var string
     * Lien description, URL vers une page de description longue du produit
     * @ORM\Column(name="reference", type="string", length=255, nullable=true)
     */
    private $reference;

    /**
     * @var float
     * Le coef de manutention
     * @ORM\Column(name="coefHandling", type="float")
     * @Assert\NotBlank()
     * @Assert\Type(type="float")
     */
    private $coefHandling;

    /**
     * @var float
     * Le coef de relevé de numéro de série
     * @ORM\Column(name="coefSerialNumberStmt", type="float")
     * @Assert\NotBlank()
     * @Assert\Type(type="float")
     */
    private $coefSerialNumberStmt;

    /**
     * @var float
     * Le coef de destruction par broyage
     * @ORM\Column(name="coefDestruction", type="float")
     * @Assert\NotBlank()
     * @Assert\Type(type="float")
     */
    private $coefDestruction;

    /**
     * @var string
     * @ORM\Column(name="position", type="integer")
     * @Assert\NotBlank()
     */
    private $position;

    /**
     * @var text
     * @ORM\Column(name="availablePostalCodeIds", type="text", nullable=true)
     */
    private $availablePostalCodes;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isDisplayed", type="boolean")
     * @Assert\NotBlank()
     */
    private $isDisplayed;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isPayableOnline", type="boolean")
     * @Assert\NotBlank()
     */
    private $isPayableOnline;

    /**
     * #################################
     *              Relations
     * #################################
     */

    /**
     * @ORM\OneToMany(targetEntity="Paprec\CatalogBundle\Entity\Picture", mappedBy="productD3E", cascade={"all"})
     */
    private $pictos;

    /**
     * @ORM\ManyToOne(targetEntity="Paprec\CatalogBundle\Entity\GrilleTarifD3E", inversedBy="productD3Es")
     */
    private $grilleTarifD3E;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->dateCreation = new \DateTime();
        $this->pictos = new ArrayCollection();
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
     * @return ProductD3E
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
     * @return ProductD3E
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
     * @return ProductD3E
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
     * @return ProductD3E
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
     * @return ProductD3E
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
     * Set reference.
     *
     * @param string|null $reference
     *
     * @return ProductD3E
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
     * Set coefHandling.
     *
     * @param float $coefHandling
     *
     * @return ProductD3E
     */
    public function setCoefHandling($coefHandling)
    {
        $this->coefHandling = $coefHandling;

        return $this;
    }

    /**
     * Get coefHandling.
     *
     * @return float
     */
    public function getCoefHandling()
    {
        return $this->coefHandling;
    }

    /**
     * Set coefSerialNumberStmt.
     *
     * @param float $coefSerialNumberStmt
     *
     * @return ProductD3E
     */
    public function setCoefSerialNumberStmt($coefSerialNumberStmt)
    {
        $this->coefSerialNumberStmt = $coefSerialNumberStmt;

        return $this;
    }

    /**
     * Get coefSerialNumberStmt.
     *
     * @return float
     */
    public function getCoefSerialNumberStmt()
    {
        return $this->coefSerialNumberStmt;
    }

    /**
     * Set coefDestruction.
     *
     * @param float $coefDestruction
     *
     * @return ProductD3E
     */
    public function setCoefDestruction($coefDestruction)
    {
        $this->coefDestruction = $coefDestruction;

        return $this;
    }

    /**
     * Get coefDestruction.
     *
     * @return float
     */
    public function getCoefDestruction()
    {
        return $this->coefDestruction;
    }

    /**
     * Set position.
     *
     * @param int $position
     *
     * @return ProductD3E
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
     * Set availablePostalCodes.
     *
     * @param string|null $availablePostalCodes
     *
     * @return ProductD3E
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
     * @return ProductD3E
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
     * Set isPayableOnline.
     *
     * @param bool $isPayableOnline
     *
     * @return ProductD3E
     */
    public function setIsPayableOnline($isPayableOnline)
    {
        $this->isPayableOnline = $isPayableOnline;

        return $this;
    }

    /**
     * Get isPayableOnline.
     *
     * @return bool
     */
    public function getIsPayableOnline()
    {
        return $this->isPayableOnline;
    }

    /**
     * Add picto.
     *
     * @param \Paprec\CatalogBundle\Entity\Picture $picto
     *
     * @return ProductD3E
     */
    public function addPicto(\Paprec\CatalogBundle\Entity\Picture $picto)
    {
        $this->pictos[] = $picto;

        return $this;
    }

    /**
     * Remove picto.
     *
     * @param \Paprec\CatalogBundle\Entity\Picture $picto
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removePicto(\Paprec\CatalogBundle\Entity\Picture $picto)
    {
        return $this->pictos->removeElement($picto);
    }

    /**
     * Get pictos.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPictos()
    {
        return $this->pictos;
    }

    /**
     * Set grilleTarifD3E.
     *
     * @param \Paprec\CatalogBundle\Entity\GrilleTarifD3E|null $grilleTarifD3E
     *
     * @return ProductD3E
     */
    public function setGrilleTarifD3E(\Paprec\CatalogBundle\Entity\GrilleTarifD3E $grilleTarifD3E = null)
    {
        $this->grilleTarifD3E = $grilleTarifD3E;

        return $this;
    }

    /**
     * Get grilleTarifD3E.
     *
     * @return \Paprec\CatalogBundle\Entity\GrilleTarifD3E|null
     */
    public function getGrilleTarifD3E()
    {
        return $this->grilleTarifD3E;
    }
}
