<?php

namespace Paprec\CommercialBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Agence
 *
 * @ORM\Table(name="agences")
 * @ORM\Entity(repositoryClass="Paprec\CommercialBundle\Repository\AgenceRepository")
 */
class Agence
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
     * @ORM\OneToMany(targetEntity="Paprec\CatalogBundle\Entity\GrilleTarifLigneD3E", mappedBy="agence", cascade={"all"})
     */
    private $grilleTarifLigneD3Es;

    public function __construct()
    {
        $this->dateCreation = new \DateTime();
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
     * @return Agence
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
     * @return Agence
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
     * @return Agence
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
     * @return Agence
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
     * @return Agence
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
     * @return Agence
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
     * @return Agence
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
     * @return Agence
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
     * @return Agence
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
     * @return Agence
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
     * @return Agence
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
     * @return Agence
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
     * @return Agence
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
}
