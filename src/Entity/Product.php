<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use App\Validator\UniquePersistedEntity;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * This class represents an entity of a product stored in a table and imported from a file.
 */
#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\Table(name: 'tblProductData')]
#[UniqueEntity('productCode')]
#[UniquePersistedEntity(['productCode'])]
#[ORM\HasLifecycleCallbacks]
class Product
{
    /** @var int The maximum length of the product name column in the database. */
    public const int NAME_MAX_LENGTH = 50;
    /** @var int The maximum length of the product code column in the database. */
    public const int CODE_MAX_LENGTH = 10;
    /** @var int The maximum length of the product description column in the database. */
    public const int DESCRIPTION_MAX_LENGTH = 255;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'intProductDataId', type: Types::INTEGER, options: ['unsigned' => true])]
    private ?int $productDataId = null;

    #[ORM\Column(name: 'strProductName', length: self::NAME_MAX_LENGTH)]
    private ?string $productName = null;

    #[ORM\Column(name: 'strProductDesc', length: self::DESCRIPTION_MAX_LENGTH)]
    private ?string $productDesc = null;

    #[ORM\Column(name: 'strProductCode', length: self::CODE_MAX_LENGTH, unique: true)]
    private ?string $productCode = null;

    #[ORM\Column(name: 'dtmAdded', nullable: true)]
    private ?DateTime $addedAt = null;

    #[ORM\Column(name: 'dtmDiscontinued', nullable: true)]
    private ?DateTime $discontinuedAt = null;

    #[ORM\Column(name: 'stmTimestamp', options: ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])]
    private DateTime $updatedAt;

    #[ORM\Column(name: 'intProductStock')]
    private ?int $productStock = null;

    #[ORM\Column(name: 'decProductCost', type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $productCost = null;

    /**
     * Provides the Primary Key identifier {@see $productDataId}.
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->productDataId;
    }

    /**
     * Provides the product name property {@see $productName}.
     *
     * @return string|null
     */
    public function getProductName(): ?string
    {
        return $this->productName;
    }

    /**
     * Setter for the product name property {@see $productName}.
     *
     * @param string $productName
     * @return $this
     */
    public function setProductName(string $productName): static
    {
        $this->productName = $productName;

        return $this;
    }

    /**
     * Provides the product description property {@see $productDesc}.
     *
     * @return string|null
     */
    public function getProductDesc(): ?string
    {
        return $this->productDesc;
    }

    /**
     * Setter for the product description property {@see $productDesc}.
     *
     * @param string $productDesc
     * @return $this
     */
    public function setProductDesc(string $productDesc): static
    {
        $this->productDesc = $productDesc;

        return $this;
    }

    /**
     * Provides the product code property {@see $productCode}.
     *
     * @return string|null
     */
    public function getProductCode(): ?string
    {
        return $this->productCode;
    }

    /**
     * Setter for the product code property {@see $productCode}.
     *
     * @param string $productCode
     * @return $this
     */
    public function setProductCode(string $productCode): static
    {
        $this->productCode = $productCode;

        return $this;
    }

    /**
     * Provides the product "added at" time property {@see $addedAt}.
     *
     * @return DateTime|null
     */
    public function getAddedAt(): ?DateTime
    {
        return $this->addedAt;
    }

    /**
     * Setter for the "added at" property {@see $addedAt}.
     *
     * @param DateTime|null $addedAt
     * @return $this
     */
    public function setAddedAt(?DateTime $addedAt): static
    {
        $this->addedAt = $addedAt;

        return $this;
    }

    /**
     * Provides the product "discontinued at" time property {@see $discontinuedAt}.
     *
     * @return DateTime|null
     */
    public function getDiscontinuedAt(): ?DateTime
    {
        return $this->discontinuedAt;
    }

    /**
     * Setter for the "discontinued at" property {@see $discontinuedAt}.
     *
     * @param DateTime|null $discontinuedAt
     * @return $this
     */
    public function setDiscontinuedAt(?DateTime $discontinuedAt): static
    {
        $this->discontinuedAt = $discontinuedAt;

        return $this;
    }

    /**
     * Provides the product "updated at" time property {@see $updatedAt}.
     *
     * @return DateTime
     */
    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    /**
     * Setter for the "updated at" property {@see $updatedAt}.
     *
     * @param DateTime $updatedAt
     * @return $this
     */
    public function setUpdatedAt(DateTime $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Provides the product stock property {@see $productStock}.
     *
     * @return int|null
     */
    public function getProductStock(): ?int
    {
        return $this->productStock;
    }

    /**
     * Setter for the product stock property {@see $productStock}.
     *
     * @param int $productStock
     * @return $this
     */
    public function setProductStock(int $productStock): static
    {
        $this->productStock = $productStock;

        return $this;
    }

    /**
     * Provides the product cost property {@see $productCost}.
     *
     * @return float|null
     */
    public function getProductCost(): ?float
    {
        return (float) $this->productCost;
    }

    /**
     * Setter for the product cost property {@see $productCost}.
     *
     * @param string $productCost
     * @return $this
     */
    public function setProductCost(string $productCost): static
    {
        $this->productCost = $productCost;

        return $this;
    }

    /**
     * Sets the default value for the {@see $addedAt} property if it has not been set before persist entity.
     *
     * @return void
     */
    #[ORM\PrePersist]
    public function setDefaultAddedAt(): void
    {
        if ($this->getAddedAt() === null) {
            $this->setAddedAt(new DateTime());
        }
    }

    /**
     * Updates {@see $updatedAt} property before persist and before update entity.
     *
     * @return void
     */
    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function updatedAt(): void
    {
        $this->setUpdatedAt(new DateTime());
    }
}
