<?php

namespace Paprec\PublicBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Cart
 *
 * @ORM\Table(name="carts")
 * @ORM\Entity(repositoryClass="Paprec\PublicBundle\Repository\CartRepository")
 */
class Cart
{
    /**
     * @var \Ramsey\Uuid\UuidInterface
     *
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateCreation", type="datetime")
     */
    private $dateCreation;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="dateUpdate", type="datetime", nullable=true)
     */
    private $dateUpdate;

    /**
     * @var string
     *
     * @ORM\Column(name="division", type="string", length=255)
     */
    private $division;

    /**
     * @var string
     *
     * @ORM\Column(name="location", type="string", length=255)
     */
    private $location;

    /**
     * @var string
     *
     * @ORM\Column(name="frequency", type="string", length=255)
     */
    private $frequency;

    /**
     * @var array
     *
     * @ORM\Column(name="displayedCategories", type="simple_array", nullable=true)
     */
    private $displayedCategories;

    /**
     * @var array
     *
     * @ORM\Column(name="displayedProducts", type="array", nullable=true)
     */
    private $displayedProducts;

    /**
     * @var array|null
     *
     * @ORM\Column(name="content", type="json", nullable=true)
     */
    private $content;


    public function __construct()
    {
        $this->dateCreation = new \DateTime();
        $this->setDisplayedProducts = array();
        $this->content = array();

    }

    /**
     * Get id.
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
     * @return Cart
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
     * @return Cart
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
     * Set division.
     *
     * @param string $division
     *
     * @return Cart
     */
    public function setDivision($division)
    {
        $this->division = $division;

        return $this;
    }

    /**
     * Get division.
     *
     * @return string
     */
    public function getDivision()
    {
        return $this->division;
    }

    /**
     * Set location.
     *
     * @param string $location
     *
     * @return Cart
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location.
     *
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set frequency.
     *
     * @param string $frequency
     *
     * @return Cart
     */
    public function setFrequency($frequency)
    {
        $this->frequency = $frequency;

        return $this;
    }

    /**
     * Get frequency.
     *
     * @return string
     */
    public function getFrequency()
    {
        return $this->frequency;
    }

    /**
     * Set displayedCategories.
     *
     * @param array|null $displayedCategories
     *
     * @return Cart
     */
    public function setDisplayedCategories($displayedCategories = null)
    {
        $this->displayedCategories = $displayedCategories;

        return $this;
    }

    /**
     * Get displayedCategories.
     *
     * @return array|null
     */
    public function getDisplayedCategories()
    {
        return $this->displayedCategories;
    }

    /**
     * Set displayedProducts.
     *
     * @param array|null $displayedProducts
     *
     * @return Cart
     */
    public function setDisplayedProducts($displayedProducts = null)
    {
        $this->displayedProducts = $displayedProducts;

        return $this;
    }

    /**
     * Get displayedProducts.
     *
     * @return array|null
     */
    public function getDisplayedProducts()
    {
        return $this->displayedProducts;
    }

    /**
     * Set content.
     *
     * @param json|null $content
     *
     * @return Cart
     */
    public function setContent($content = null)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content.
     *
     * @return json|null
     */
    public function getContent()
    {
        return $this->content;
    }
}
