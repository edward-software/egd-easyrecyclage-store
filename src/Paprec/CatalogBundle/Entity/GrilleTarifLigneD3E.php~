<?php

namespace Paprec\CatalogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * GrilleTarifLigneD3E
 *
 * @ORM\Table(name="grilleTarifLigneD3Es")
 * @ORM\Entity(repositoryClass="Paprec\CatalogBundle\Repository\GrilleTarifLigneD3ERepository")
 */
class GrilleTarifLigneD3E
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
     * @var string
     *
     * @ORM\Column(name="postalCodes", type="text")
     * @Assert\NotBlank()
     * @Assert\Regex(
     *     pattern="@(\d{2}(?:\d{3})?)(,\s*)*@",
     *     htmlPattern="(\d{2}(?:\d{3})?)(,\s*)*",
     *     message="Les codes postaux doivent être des nombres de taille 2 ou 5 séparés par des virgules. (ex: '75, 92150, 36')"
     * )
     */
    private $postalCodes;

    /**
     * @var int
     *
     * @ORM\Column(name="minQuantity", type="integer")
     * @Assert\NotBlank()
     */
    private $minQuantity;

    /**
     * @var int
     *
     * @ORM\Column(name="maxQuantity", type="integer")
     */
    private $maxQuantity;

    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float")
     * @Assert\NotBlank()
     */
    private $price;

    /**
     * @ORM\ManyToOne(targetEntity="Paprec\CatalogBundle\Entity\GrilleTarifD3E", inversedBy="grilleTarifLigneD3Es")
     */
    private $grilleTarifD3E;

    /**
     * @ORM\ManyToOne(targetEntity="Paprec\CommercialBundle\Entity\Agency", inversedBy="grilleTarifLigneD3Es")
     */
    private $agency;

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
     * Set postalCodes.
     *
     * @param string $postalCodes
     *
     * @return GrilleTarifLigneD3E
     */
    public function setPostalCodes($postalCodes)
    {
        $this->postalCodes = $postalCodes;

        return $this;
    }

    /**
     * Get postalCodes.
     *
     * @return string
     */
    public function getPostalCodes()
    {
        return $this->postalCodes;
    }

    /**
     * Set minQuantity.
     *
     * @param int $minQuantity
     *
     * @return GrilleTarifLigneD3E
     */
    public function setMinQuantity($minQuantity)
    {
        $this->minQuantity = $minQuantity;

        return $this;
    }

    /**
     * Get minQuantity.
     *
     * @return int
     */
    public function getMinQuantity()
    {
        return $this->minQuantity;
    }

    /**
     * Set maxQuantity.
     *
     * @param int $maxQuantity
     *
     * @return GrilleTarifLigneD3E
     */
    public function setMaxQuantity($maxQuantity)
    {
        $this->maxQuantity = $maxQuantity;

        return $this;
    }

    /**
     * Get maxQuantity.
     *
     * @return int
     */
    public function getMaxQuantity()
    {
        return $this->maxQuantity;
    }

    /**
     * Set price.
     *
     * @param float $price
     *
     * @return GrilleTarifLigneD3E
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price.
     *
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set grilleTarifD3E.
     *
     * @param \Paprec\CatalogBundle\Entity\GrilleTarifD3E|null $grilleTarifD3E
     *
     * @return GrilleTarifLigneD3E
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

    /**
     * Set agency.
     *
     * @param \Paprec\CommercialBundle\Entity\Agency|null $agence
     *
     * @return GrilleTarifLigneD3E
     */
    public function setAgency(\Paprec\CommercialBundle\Entity\Agency $agency = null)
    {
        $this->agency = $agency;

        return $this;
    }

    /**
     * Get agency.
     *
     * @return \Paprec\CommercialBundle\Entity\Agency|null
     */
    public function getAgency()
    {
        return $this->agency;
    }
}
