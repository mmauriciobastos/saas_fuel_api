<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\DeliveryTruckRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['truck:read']]),
        new Get(normalizationContext: ['groups' => ['truck:read']]),
        new Post(
            normalizationContext: ['groups' => ['truck:read']],
            denormalizationContext: ['groups' => ['truck:write']],
            processor: \App\StateProcessor\CompanyStateProcessor::class
        ),
        new Put(
            normalizationContext: ['groups' => ['truck:read']],
            denormalizationContext: ['groups' => ['truck:write']],
            processor: \App\StateProcessor\CompanyStateProcessor::class
        ),
        new Patch(
            normalizationContext: ['groups' => ['truck:read']],
            denormalizationContext: ['groups' => ['truck:write']],
            processor: \App\StateProcessor\CompanyStateProcessor::class
        ),
        new Delete(),
    ]
)]
#[ORM\Entity(repositoryClass: DeliveryTruckRepository::class)]
class DeliveryTruck
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['truck:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Groups(['truck:read', 'truck:write'])]
    private ?string $licensePlate = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['truck:read', 'truck:write'])]
    private ?string $model = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['truck:read', 'truck:write'])]
    private ?string $driverName = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['truck:read', 'truck:write'])]
    private ?float $currentFuelLevel = null;

    #[ORM\Column(length: 20)]
    #[Groups(['truck:read', 'truck:write'])]
    private ?string $status = null;

    #[ORM\Column]
    #[Groups(['truck:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['truck:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'deliveryTrucks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Company $company = null;

    #[ORM\OneToMany(mappedBy: 'deliveryTruck', targetEntity: Order::class)]
    private Collection $orders;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->status = 'available';
        $this->orders = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLicensePlate(): ?string
    {
        return $this->licensePlate;
    }

    public function setLicensePlate(string $licensePlate): static
    {
        $this->licensePlate = $licensePlate;

        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(?string $model): static
    {
        $this->model = $model;

        return $this;
    }

    public function getDriverName(): ?string
    {
        return $this->driverName;
    }

    public function setDriverName(?string $driverName): static
    {
        $this->driverName = $driverName;

        return $this;
    }

    public function getCurrentFuelLevel(): ?float
    {
        return $this->currentFuelLevel;
    }

    public function setCurrentFuelLevel(?float $currentFuelLevel): static
    {
        $this->currentFuelLevel = $currentFuelLevel;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): static
    {
        $this->company = $company;

        return $this;
    }

    /**
     * @return Collection<int, Order>
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): static
    {
        if (!$this->orders->contains($order)) {
            $this->orders->add($order);
            $order->setDeliveryTruck($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): static
    {
        if ($this->orders->removeElement($order)) {
            if ($order->getDeliveryTruck() === $this) {
                $order->setDeliveryTruck(null);
            }
        }

        return $this;
    }
}
