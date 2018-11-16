<?php

namespace Paprec\CommercialBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * ProductDIOrderLine
 *
 * @ORM\Table(name="productDIOrderLines")
 * @ORM\Entity(repositoryClass="Paprec\CommercialBundle\Repository\ProductDIOrderLineRepository")
 */
class ProductDIOrderLine
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
     * @ORM\Column(name="productName", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $productName;

    /**
     * @var float
     *
     * @ORM\Column(name="unitPrice", type="float")
     * @Assert\NotBlank()
     * @Assert\Type(type="float")
     */
    private $unitPrice;

    /**
     * @var integer
     *
     * @ORM\Column(name="quantity", type="integer")
     * @Assert\NotBlank()
     */
    private $quantity;


    /**************************************************************************************************
     * RELATIONS
     */

    /**
     * @ORM\ManyToOne(targetEntity="Paprec\CatalogBundle\Entity\ProductDI", inversedBy="productDIOrderLines")
     * @ORM\JoinColumn(name="productId", referencedColumnName="id", nullable=false)
     */
    private $productDI;

    /**
     * @ORM\ManyToOne(targetEntity="Paprec\CommercialBundle\Entity\ProductDIOrder", inversedBy="productDIOrderLines")
     * @ORM\JoinColumn(name="productDIOrderId", referencedColumnName="id", nullable=false)
     */
    private $productDIOrder;


    /**
     * ProductDIOrderLine constructor.
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
     * @return ProductDIOrderLine
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
     * @return ProductDIOrderLine
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
     * @return ProductDIOrderLine
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
     * Set productName.
     *
     * @param string $productName
     *
     * @return ProductDIOrderLine
     */
    public function setProductName($productName)
    {
        $this->productName = $productName;

        return $this;
    }

    /**
     * Get productName.
     *
     * @return string
     */
    public function getProductName()
    {
        return $this->productName;
    }

    /**
     * Set unitPrice.
     *
     * @param float $unitPrice
     *
     * @return ProductDIOrderLine
     */
    public function setUnitPrice($unitPrice)
    {
        $this->unitPrice = $unitPrice;

        return $this;
    }

    /**
     * Get unitPrice.
     *
     * @return float
     */
    public function getUnitPrice()
    {
        return $this->unitPrice;
    }

    /**
     * Set quantity.
     *
     * @param int $quantity
     *
     * @return ProductDIOrderLine
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get quantity.
     *
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set productDI.
     *
     * @param \Paprec\CatalogBundle\Entity\ProductDI $productDI
     *
     * @return ProductDIOrderLine
     */
    public function setProductDI(\Paprec\CatalogBundle\Entity\ProductDI $productDI)
    {
        $this->productDI = $productDI;

        return $this;
    }

    /**
     * Get productDI.
     *
     * @return \Paprec\CatalogBundle\Entity\ProductDI
     */
    public function getProductDI()
    {
        return $this->productDI;
    }

    /**
     * Set productDIOrder.
     *
     * @param \Paprec\CommercialBundle\Entity\ProductDIOrder $productDIOrder
     *
     * @return ProductDIOrderLine
     */
    public function setProductDIOrder(\Paprec\CommercialBundle\Entity\ProductDIOrder $productDIOrder)
    {
        $this->productDIOrder = $productDIOrder;

        return $this;
    }

    /**
     * Get productDIOrder.
     *
     * @return \Paprec\CommercialBundle\Entity\ProductDIOrder
     */
    public function getProductDIOrder()
    {
        return $this->productDIOrder;
    }
}
