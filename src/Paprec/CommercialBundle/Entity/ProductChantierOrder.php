<?php

namespace Paprec\CommercialBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * ProductChantierOrder
 *
 * @ORM\Table(name="productChantierOrders")
 * @ORM\Entity(repositoryClass="Paprec\CommercialBundle\Repository\ProductChantierOrderRepository")
 */
class ProductChantierOrder
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
     *
     * @ORM\Column(name="function", type="string", length=255, nullable=true)
     */
    private $function;


    /**
     * @var string
     * @ORM\Column(name="email", type="string", length=255)
     * @Assert\Email(
     *      message = "Le format de l'email est invalide"
     * )
     */
    private $email;

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
     * @var string
     *
     * @ORM\Column(name="orderStatus", type="string", length=255)
     */
    private $orderStatus;

    /**
     * @var float
     *
     * @ORM\Column(name="totalAmount", type="float", nullable=true)
     */
    private $totalAmount;

    /**
     * @var string
     *
     * @ORM\Column(name="paymentMethod", type="string", length=255, nullable=true)
     */
    private $paymentMethod;


    /**
     * @var string
     *
     * @ORM\Column(name="signatoryToken", type="string", length=255, nullable=true)
     */
    private $signatoryToken;

    /**
     *
     * Identifiant de la dernière transaction de signature générée
     *
     * @var string
     *
     * @ORM\Column(name="signatoryTransactionId", type="string", length=255, nullable=true)
     */
    private $signatoryTransactionId;

    /**
     *
     * Identifiant de la signature de la dernière transaction de signature électronique
     *
     * @var string
     *
     * @ORM\Column(name="signatorySignatureId", type="string", length=255, nullable=true)
     */
    private $signatorySignatureId;

    /**
     * Facture associée
     * @var string
     *
     * @ORM\Column(name="associatedInvoice", type="string", length=255, nullable=true)
     * @Assert\File(mimeTypes={ "application/pdf" })
     */
    private $associatedInvoice;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="installationDate", type="datetime", nullable=true)
     * @Assert\NotBlank(groups={"delivery"})

     */
    private $installationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="removalDate", type="datetime", nullable=true)
     * @Assert\NotBlank(groups={"delivery"})
     */
    private $removalDate;

    /**
     * @var string
     *
     * @ORM\Column(name="domainType", type="string", length=10, nullable=true)
     * @Assert\NotBlank(groups={"delivery"})
     */
    private $domainType;

    /**
     * @var string
     *
     * @ORM\Column(name="accessConditions", type="text", nullable=true)
     * @Assert\NotBlank(groups={"delivery"})
     */
    private $accessConditions;

    /** ###########################
     *
     *  RELATIONS
     *
     * ########################### */


    /**
     * @ORM\OneToMany(targetEntity="Paprec\CommercialBundle\Entity\ProductChantierOrderLine", mappedBy="productChantierOrder", cascade={"all"})
     */
    private $productChantierOrderLines;


    /**
     * @ORM\ManyToOne(targetEntity="Paprec\CommercialBundle\Entity\BusinessLine", inversedBy="productChantierOrders")
     * @ORM\JoinColumn(name="businessLineId", referencedColumnName="id", nullable=true)
     * @Assert\NotBlank()
     */
    private $businessLine;

    /**
     * ProductChantierOrder constructor.
     */
    public function __construct()
    {
        $this->dateCreation = new \DateTime();
        $this->productChantierOrderLines = new ArrayCollection();
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
     * @return ProductChantierOrder
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
     * @return ProductChantierOrder
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
     * @return ProductChantierOrder
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
     * @return ProductChantierOrder
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
     * @return ProductChantierOrder
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
     * @return ProductChantierOrder
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
     * @return ProductChantierOrder
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
     * Set function.
     *
     * @param string|null $function
     *
     * @return ProductChantierOrder
     */
    public function setFunction($function = null)
    {
        $this->function = $function;

        return $this;
    }

    /**
     * Get function.
     *
     * @return string|null
     */
    public function getFunction()
    {
        return $this->function;
    }

    /**
     * Set email.
     *
     * @param string $email
     *
     * @return ProductChantierOrder
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
     * Set address.
     *
     * @param string $address
     *
     * @return ProductChantierOrder
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
     * @return ProductChantierOrder
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
     * @return ProductChantierOrder
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
     * @return ProductChantierOrder
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
     * @return ProductChantierOrder
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
     * Set totalAmount.
     *
     * @param float|null $totalAmount
     *
     * @return ProductChantierOrder
     */
    public function setTotalAmount($totalAmount = null)
    {
        $this->totalAmount = $totalAmount;

        return $this;
    }

    /**
     * Get totalAmount.
     *
     * @return float|null
     */
    public function getTotalAmount()
    {
        return $this->totalAmount;
    }

    /**
     * Set paymentMethod.
     *
     * @param string|null $paymentMethod
     *
     * @return ProductChantierOrder
     */
    public function setPaymentMethod($paymentMethod = null)
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    /**
     * Get paymentMethod.
     *
     * @return string|null
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * Set signatoryToken.
     *
     * @param string|null $signatoryToken
     *
     * @return ProductChantierOrder
     */
    public function setSignatoryToken($signatoryToken = null)
    {
        $this->signatoryToken = $signatoryToken;

        return $this;
    }

    /**
     * Get signatoryToken.
     *
     * @return string|null
     */
    public function getSignatoryToken()
    {
        return $this->signatoryToken;
    }

    /**
     * Set signatoryTransactionId.
     *
     * @param string|null $signatoryTransactionId
     *
     * @return ProductChantierOrder
     */
    public function setSignatoryTransactionId($signatoryTransactionId = null)
    {
        $this->signatoryTransactionId = $signatoryTransactionId;

        return $this;
    }

    /**
     * Get signatoryTransactionId.
     *
     * @return string|null
     */
    public function getSignatoryTransactionId()
    {
        return $this->signatoryTransactionId;
    }

    /**
     * Set signatorySignatureId.
     *
     * @param string|null $signatorySignatureId
     *
     * @return ProductChantierOrder
     */
    public function setSignatorySignatureId($signatorySignatureId = null)
    {
        $this->signatorySignatureId = $signatorySignatureId;

        return $this;
    }

    /**
     * Get signatorySignatureId.
     *
     * @return string|null
     */
    public function getSignatorySignatureId()
    {
        return $this->signatorySignatureId;
    }

    /**
     * Set associatedInvoice.
     *
     * @param string|null $associatedInvoice
     *
     * @return ProductChantierOrder
     */
    public function setAssociatedInvoice($associatedInvoice = null)
    {
        $this->associatedInvoice = $associatedInvoice;

        return $this;
    }

    /**
     * Get associatedInvoice.
     *
     * @return string|null
     */
    public function getAssociatedInvoice()
    {
        return $this->associatedInvoice;
    }

    /**
     * Add productChantierOrderLine.
     *
     * @param \Paprec\CommercialBundle\Entity\ProductChantierOrderLine $productChantierOrderLine
     *
     * @return ProductChantierOrder
     */
    public function addProductChantierOrderLine(\Paprec\CommercialBundle\Entity\ProductChantierOrderLine $productChantierOrderLine)
    {
        $this->productChantierOrderLines[] = $productChantierOrderLine;

        return $this;
    }

    /**
     * Remove productChantierOrderLine.
     *
     * @param \Paprec\CommercialBundle\Entity\ProductChantierOrderLine $productChantierOrderLine
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeProductChantierOrderLine(\Paprec\CommercialBundle\Entity\ProductChantierOrderLine $productChantierOrderLine)
    {
        return $this->productChantierOrderLines->removeElement($productChantierOrderLine);
    }

    /**
     * Get productChantierOrderLines.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProductChantierOrderLines()
    {
        return $this->productChantierOrderLines;
    }

    /**
     * Set businessLine.
     *
     * @param \Paprec\CommercialBundle\Entity\BusinessLine|null $businessLine
     *
     * @return ProductChantierOrder
     */
    public function setBusinessLine(\Paprec\CommercialBundle\Entity\BusinessLine $businessLine = null)
    {
        $this->businessLine = $businessLine;

        return $this;
    }

    /**
     * Get businessLine.
     *
     * @return \Paprec\CommercialBundle\Entity\BusinessLine|null
     */
    public function getBusinessLine()
    {
        return $this->businessLine;
    }

    /**
     * Set installationDate.
     *
     * @param \DateTime $installationDate
     *
     * @return ProductChantierOrder
     */
    public function setInstallationDate($installationDate)
    {
        $this->installationDate = $installationDate;

        return $this;
    }

    /**
     * Get installationDate.
     *
     * @return \DateTime
     */
    public function getInstallationDate()
    {
        return $this->installationDate;
    }

    /**
     * Set removalDate.
     *
     * @param \DateTime $removalDate
     *
     * @return ProductChantierOrder
     */
    public function setRemovalDate($removalDate)
    {
        $this->removalDate = $removalDate;

        return $this;
    }

    /**
     * Get removalDate.
     *
     * @return \DateTime
     */
    public function getRemovalDate()
    {
        return $this->removalDate;
    }

    /**
     * Set domainType.
     *
     * @param string|null $domainType
     *
     * @return ProductChantierOrder
     */
    public function setDomainType($domainType = null)
    {
        $this->domainType = $domainType;

        return $this;
    }

    /**
     * Get domainType.
     *
     * @return string|null
     */
    public function getDomainType()
    {
        return $this->domainType;
    }

    /**
     * Set accessConditions.
     *
     * @param string|null $accessConditions
     *
     * @return ProductChantierOrder
     */
    public function setAccessConditions($accessConditions = null)
    {
        $this->accessConditions = $accessConditions;

        return $this;
    }

    /**
     * Get accessConditions.
     *
     * @return string|null
     */
    public function getAccessConditions()
    {
        return $this->accessConditions;
    }
}