<?php

namespace Paprec\CommercialBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * QuoteRequest
 *
 * @ORM\Table(name="quoteRequests")
 * @ORM\Entity(repositoryClass="Paprec\CommercialBundle\Repository\QuoteRequestRepository")
 */
class QuoteRequest
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
     * @ORM\Column(name="businessName", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $businessName;


    /**
     * @var string
     *
     * @ORM\Column(name="civility", type="string", length=10)
     * @Assert\NotBlank()
     */
    private $civility;

    /**
     * @var string
     *
     * @ORM\Column(name="lastName", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="firstName", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $firstName;


    /**
     * @var string
     * @ORM\Column(name="email", type="string", length=255)
     * @Assert\Email(
     *      message = "The email '{{ value }}' is not a valid email.",
     *      checkMX = true
     * )
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="function", type="string", length=255, nullable=true)
     */
    private $function;
    
    /**
     * @var string
     *
     * @ORM\Column(name="orderStatus", type="string", length=255)
     */
    private $orderStatus;

    /**
     * "Mon besoin" rempli par l'utilisateur Front Office
     * @var string
     *
     * @ORM\Column(name="need", type="text")
     * @Assert\NotBlank()
     */
    private $need;

    /**
     * @var array
     *
     * @ORM\Column(name="attachedFiles", type="array", nullable=true)
     */
    private $attachedFiles;


    /**
     * @var float
     *
     * @ORM\Column(name="generatedTurnover", type="float", nullable=true)
     */
    private $generatedTurnover;

    /**
     * @var array|null
     *
     * @ORM\Column(name="division", type="string")
     */
    private $division;

    /**
     * @var string
     *
     * @ORM\Column(name="postalCode", type="string", length=255)
     */
    private $postalCode;

    /**
     * Devis associé
     * @var string
     *
     * @ORM\Column(name="associatedOrder", type="string", length=255, nullable=true)
     * @Assert\File(mimeTypes={ "application/pdf" })
     */
    private $associatedOrder;

    /**
     * Résumé du besoin rempli par le commercial
     * @var string
     *
     * @ORM\Column(name="summary", type="text", nullable=true)
     */
    private $summary;

    /**
     * @var string
     *
     * @ORM\Column(name="frequency", type="string", length=10, nullable=true)
     */
    private $frequency;

    /**
     * @var float
     *
     * @ORM\Column(name="tonnage", type="float", nullable=true)
     */
    private $tonnage;

    /**
     * @var integer
     *
     * @ORM\Column(name="kookaburaNumber", type="integer", nullable=true)
     */
    private $kookaburaNumber;


    /** ###########################
     *
     *  RELATIONS
     *
    ########################### */

    /**
     * @ORM\ManyToOne(targetEntity="Paprec\UserBundle\Entity\User", inversedBy="quoteRequests", cascade={"all"})
     * @ORM\JoinColumn(name="userInChargeId", referencedColumnName="id", nullable=true)
     */
    private $userInCharge;

    /**
     * @ORM\ManyToOne(targetEntity="Paprec\CommercialBundle\Entity\Agency", inversedBy="quoteRequests")
     * @ORM\JoinColumn(name="agencyId", referencedColumnName="id", nullable=true)
     */
    private $agency;


    /**
     * QuoteRequest constructor.
     */
    public function __construct()
    {
        $this->dateCreation = new \DateTime();
        $this->attachedFiles = array();
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
     * @return QuoteRequest
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
     * @return QuoteRequest
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
     * @return QuoteRequest
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
     * Set businessName.
     *
     * @param string $businessName
     *
     * @return QuoteRequest
     */
    public function setBusinessName($businessName)
    {
        $this->businessName = $businessName;

        return $this;
    }

    /**
     * Get businessName.
     *
     * @return string
     */
    public function getBusinessName()
    {
        return $this->businessName;
    }

    /**
     * Set civility.
     *
     * @param string $civility
     *
     * @return QuoteRequest
     */
    public function setCivility($civility)
    {
        $this->civility = $civility;

        return $this;
    }

    /**
     * Get civility.
     *
     * @return string
     */
    public function getCivility()
    {
        return $this->civility;
    }

    /**
     * Set lastName.
     *
     * @param string $lastName
     *
     * @return QuoteRequest
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName.
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set firstName.
     *
     * @param string $firstName
     *
     * @return QuoteRequest
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName.
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set email.
     *
     * @param string $email
     *
     * @return QuoteRequest
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set phone.
     *
     * @param string $phone
     *
     * @return QuoteRequest
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
     * Set orderStatus.
     *
     * @param string $orderStatus
     *
     * @return QuoteRequest
     */
    public function setOrderStatus($orderStatus)
    {
        $this->orderStatus = $orderStatus;

        return $this;
    }

    /**
     * Get orderStatus.
     *
     * @return string
     */
    public function getOrderStatus()
    {
        return $this->orderStatus;
    }

    /**
     * Set need.
     *
     * @param string $need
     *
     * @return QuoteRequest
     */
    public function setNeed($need)
    {
        $this->need = $need;

        return $this;
    }

    /**
     * Get need.
     *
     * @return string
     */
    public function getNeed()
    {
        return $this->need;
    }

    /**
     * Set attachedFiles.
     *
     * @param array|null $attachedFiles
     *
     * @return QuoteRequest
     */
    public function setAttachedFiles($attachedFiles = null)
    {
        $this->attachedFiles = $attachedFiles;

        return $this;
    }

    /**
     * Get attachedFiles.
     *
     * @return array|null
     */
    public function getAttachedFiles()
    {
        return $this->attachedFiles;
    }

    /**
     * Set generatedTurnover.
     *
     * @param float|null $generatedTurnover
     *
     * @return QuoteRequest
     */
    public function setGeneratedTurnover($generatedTurnover = null)
    {
        $this->generatedTurnover = $generatedTurnover;

        return $this;
    }

    /**
     * Get generatedTurnover.
     *
     * @return float|null
     */
    public function getGeneratedTurnover()
    {
        return $this->generatedTurnover;
    }

    /**
     * Set division.
     *
     * @param string|null $division
     *
     * @return QuoteRequest
     */
    public function setDivision($division = null)
    {
        $this->division = $division;

        return $this;
    }

    /**
     * Get division.
     *
     * @return string|null
     */
    public function getDivision()
    {
        return $this->division;
    }

    /**
     * Set postalCode.
     *
     * @param string $postalCode
     *
     * @return QuoteRequest
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
     * Set associatedOrder.
     *
     * @param string|null $associatedOrder
     *
     * @return QuoteRequest
     */
    public function setAssociatedOrder($associatedOrder = null)
    {
        $this->associatedOrder = $associatedOrder;

        return $this;
    }

    /**
     * Get associatedOrder.
     *
     * @return string|null
     */
    public function getAssociatedOrder()
    {
        return $this->associatedOrder;
    }

    /**
     * Set summary.
     *
     * @param string|null $summary
     *
     * @return QuoteRequest
     */
    public function setSummary($summary = null)
    {
        $this->summary = $summary;

        return $this;
    }

    /**
     * Get summary.
     *
     * @return string|null
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * Set frequency.
     *
     * @param string|null $frequency
     *
     * @return QuoteRequest
     */
    public function setFrequency($frequency = null)
    {
        $this->frequency = $frequency;

        return $this;
    }

    /**
     * Get frequency.
     *
     * @return string|null
     */
    public function getFrequency()
    {
        return $this->frequency;
    }

    /**
     * Set tonnage.
     *
     * @param float|null $tonnage
     *
     * @return QuoteRequest
     */
    public function setTonnage($tonnage = null)
    {
        $this->tonnage = $tonnage;

        return $this;
    }

    /**
     * Get tonnage.
     *
     * @return float|null
     */
    public function getTonnage()
    {
        return $this->tonnage;
    }

    /**
     * Set kookaburaNumber.
     *
     * @param int|null $kookaburaNumber
     *
     * @return QuoteRequest
     */
    public function setKookaburaNumber($kookaburaNumber = null)
    {
        $this->kookaburaNumber = $kookaburaNumber;

        return $this;
    }

    /**
     * Get kookaburaNumber.
     *
     * @return int|null
     */
    public function getKookaburaNumber()
    {
        return $this->kookaburaNumber;
    }

    /**
     * Set userInCharge.
     *
     * @param \Paprec\UserBundle\Entity\User|null $userInCharge
     *
     * @return QuoteRequest
     */
    public function setUserInCharge(\Paprec\UserBundle\Entity\User $userInCharge = null)
    {
        $this->userInCharge = $userInCharge;

        return $this;
    }

    /**
     * Get userInCharge.
     *
     * @return \Paprec\UserBundle\Entity\User|null
     */
    public function getUserInCharge()
    {
        return $this->userInCharge;
    }

    /**
     * Set agency.
     *
     * @param \Paprec\CommercialBundle\Entity\Agency|null $agency
     *
     * @return QuoteRequest
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

    /**
     * Set function.
     *
     * @param string $function
     *
     * @return QuoteRequest
     */
    public function setFunction($function)
    {
        $this->function = $function;

        return $this;
    }

    /**
     * Get function.
     *
     * @return string
     */
    public function getFunction()
    {
        return $this->function;
    }
}
