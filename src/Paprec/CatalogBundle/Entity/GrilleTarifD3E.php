<?php

namespace Paprec\CatalogBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * GrilleTarifD3E
 *
 * @ORM\Table(name="grilleTarifD3Es")
 * @ORM\Entity(repositoryClass="Paprec\CatalogBundle\Repository\GrilleTarifD3ERepository")
 */
class GrilleTarifD3E
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
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     */
    private $name;

    /**
     * #################################
     *              Relations
     * #################################
     */

    /**
     * @ORM\OneToMany(targetEntity="Paprec\CatalogBundle\Entity\ProductD3E", mappedBy="grilleTarifD3E", cascade={"all"})
     */

    private $productD3Es;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->productD3Es = new ArrayCollection();
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
     * @return GrilleTarifD3E
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
     * Add productD3E.
     *
     * @param \Paprec\CatalogBundle\Entity\ProductD3E $productD3E
     *
     * @return GrilleTarifD3E
     */
    public function addProductD3E(\Paprec\CatalogBundle\Entity\ProductD3E $productD3E)
    {
        $this->productD3Es[] = $productD3E;

        return $this;
    }

    /**
     * Remove productD3E.
     *
     * @param \Paprec\CatalogBundle\Entity\ProductD3E $productD3E
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeProductD3E(\Paprec\CatalogBundle\Entity\ProductD3E $productD3E)
    {
        return $this->productD3Es->removeElement($productD3E);
    }

    /**
     * Get productD3Es.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProductD3Es()
    {
        return $this->productD3Es;
    }
}
