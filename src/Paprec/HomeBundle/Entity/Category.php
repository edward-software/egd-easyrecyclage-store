<?php

namespace Paprec\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Category
 *
 * @ORM\Table(name="category")
 * @ORM\Entity(repositoryClass="Paprec\HomeBundle\Repository\CategoryRepository")
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
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=255, unique=true)
     */
    private $libelle;

    /**
     * @var array|null
     *
     * @ORM\Column(name="divisions", type="simple_array", nullable=true)
     */
    private $divisions;


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
     * Set libelle.
     *
     * @param string $libelle
     *
     * @return Category
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * Get libelle.
     *
     * @return string
     */
    public function getLibelle()
    {
        return $this->libelle;
    }

    /**
     * Set divisions.
     *
     * @param array|null $divisions
     *
     * @return Category
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
}
