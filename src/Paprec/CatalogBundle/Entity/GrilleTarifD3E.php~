<?php

namespace Paprec\CatalogBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\DateTime;

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
     * @ORM\OneToMany(targetEntity="Paprec\CatalogBundle\Entity\GrilleTarifLigneD3E", mappedBy="grilleTarifD3E", cascade={"all"})
     */
    private $grilleTarifLigneD3Es;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->dateCreation = new \DateTime();
        $this->productD3Es = new ArrayCollection();
        $this->grilleTarifLigneD3Es = new ArrayCollection();
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

    /**
     * Set dateCreation.
     *
     * @param \DateTime $dateCreation
     *
     * @return GrilleTarifD3E
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
     * @return GrilleTarifD3E
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
     * @return GrilleTarifD3E
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

    public function __toString()
    {
        return $this->name;
    }

    /**
     * Add grilleTarifLigneD3E.
     *
     * @param \Paprec\CatalogBundle\Entity\GrilleTarifLigneD3E $grilleTarifLigneD3E
     *
     * @return GrilleTarifD3E
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
