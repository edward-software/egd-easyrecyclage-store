<?php

namespace Paprec\CommercialBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Agency
 *
 * @ORM\Table(name="agencies")
 * @ORM\Entity(repositoryClass="Paprec\CommercialBundle\Repository\AgencyRepository")
 */
class Agency
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
     * @var array
     *
     * @ORM\Column(name="divisions", type="simple_array", nullable=true)
     * @Assert\NotBlank()
     */
    private $divisions;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="text")
     * @Assert\NotBlank()
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(name="postalCode", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $postalCode;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $city;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $phone;

    /**
     * @var float
     *
     * @ORM\Column(name="latitude", type="decimal", precision=18, scale=15)
     * @Assert\NotBlank()
     */
    private $latitude;

    /**
     * @var float
     *
     * @ORM\Column(name="longitude", type="decimal", precision=18, scale=15)
     * @Assert\NotBlank()
     */
    private $longitude;

    /**
     * @var bool
     *
     * @ORM\Column(name="isDisplayed", type="boolean")
     * @Assert\NotBlank()
     */
    private $isDisplayed;

    /** #########################
     *
     *  RELATIONS
     * ########################## */
    /**
     * @ORM\OneToMany(targetEntity="Paprec\CatalogBundle\Entity\GrilleTarifLigneD3E", mappedBy="agency", cascade={"all"})
     */
    private $grilleTarifLigneD3Es;

    /**
     * @ORM\OneToMany(targetEntity="Paprec\CommercialBundle\Entity\ProductDIOrder", mappedBy="agency")
     */
    private $productDIOrders;

    /**
     * @ORM\OneToMany(targetEntity="Paprec\CommercialBundle\Entity\QuoteRequest", mappedBy="agency")
     */
    private $quoteRequests;



    public function __construct()
    {
        $this->dateCreation = new \DateTime();
        $this->productDIOrders = new ArrayCollection();
    }

    public function __toString()
    {
     return $this->getName();
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
     * @return Agency
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
     * Set address.
     *
     * @param string $address
     *
     * @return Agency
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address.
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set postalCode.
     *
     * @param string $postalCode
     *
     * @return Agency
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    /**
     * Get postalCode.
     *
     * @return string
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * Set city.
     *
     * @param string $city
     *
     * @return Agency
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city.
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set phone.
     *
     * @param string $phone
     *
     * @return Agency
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone.
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set latitude.
     *
     * @param float $latitude
     *
     * @return Agency
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Get latitude.
     *
     * @return float
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set longitude.
     *
     * @param float $longitude
     *
     * @return Agency
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Get longitude.
     *
     * @return float
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Set isDisplayed.
     *
     * @param bool $isDisplayed
     *
     * @return Agency
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
     * Set dateCreation.
     *
     * @param \DateTime $dateCreation
     *
     * @return Agency
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
     * @return Agency
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
     * @return Agency
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
     * Set divisions.
     *
     * @param array|null $divisions
     *
     * @return Agency
     */
    public function setDivisions($divisions = null)
    {
        $this->divisions = $divisions;

        return $this;
    }

    /**
     * Get divisions.
     *
     * @return array|null
     */
    public function getDivisions()
    {
        return $this->divisions;
    }

    /**
     * Add grilleTarifLigneD3E.
     *
     * @param \Paprec\CatalogBundle\Entity\GrilleTarifLigneD3E $grilleTarifLigneD3E
     *
     * @return Agency
     */
    public function addGrilleTarifLigneD3E(\Paprec\CatalogBundle\Entity\GrilleTarifLigneD3E $grilleTarifLigneD3E)
    {
        $this->grilleTarifLigneD3Es[] = $grilleTarifLigneD3E;

        return $this;
    }

    /**
     * Remove grilleTarifLigneD3E.
     *
     * @param \Paprec\CatalogBundle\Entity\GrilleTarifLigneD3E $grilleTarifLigneD3E
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeGrilleTarifLigneD3E(\Paprec\CatalogBundle\Entity\GrilleTarifLigneD3E $grilleTarifLigneD3E)
    {
        return $this->grilleTarifLigneD3Es->removeElement($grilleTarifLigneD3E);
    }

    /**
     * Get grilleTarifLigneD3Es.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGrilleTarifLigneD3Es()
    {
        return $this->grilleTarifLigneD3Es;
    }

    /**
     * Add productDIOrder.
     *
     * @param \Paprec\CommercialBundle\Entity\ProductDIOrder $productDIOrder
     *
     * @return Agency
     */
    public function addProductDIOrder(\Paprec\CommercialBundle\Entity\ProductDIOrder $productDIOrder)
    {
        $this->productDIOrders[] = $productDIOrder;

        return $this;
    }

    /**
     * Remove productDIOrder.
     *
     * @param \Paprec\CommercialBundle\Entity\ProductDIOrder $productDIOrder
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeProductDIOrder(\Paprec\CommercialBundle\Entity\ProductDIOrder $productDIOrder)
    {
        return $this->productDIOrders->removeElement($productDIOrder);
    }

    /**
     * Get productDIOrders.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProductDIOrders()
    {
        return $this->productDIOrders;
    }

    /**
     * Add quoteRequest.
     *
     * @param \Paprec\CommercialBundle\Entity\QuoteRequest $quoteRequest
     *
     * @return Agency
     */
    public function addQuoteRequest(\Paprec\CommercialBundle\Entity\QuoteRequest $quoteRequest)
    {
        $this->quoteRequests[] = $quoteRequest;

        return $this;
    }

    /**
     * Remove quoteRequest.
     *
     * @param \Paprec\CommercialBundle\Entity\QuoteRequest $quoteRequest
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeQuoteRequest(\Paprec\CommercialBundle\Entity\QuoteRequest $quoteRequest)
    {
        return $this->quoteRequests->removeElement($quoteRequest);
    }

    /**
     * Get quoteRequests.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getQuoteRequests()
    {
        return $this->quoteRequests;
    }
}
