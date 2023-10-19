<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\AlcoholRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AlcoholRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['alcohol']],
    paginationClientItemsPerPage: true,
    operations: [
        new GetCollection(),
        new Get(),
        new Post(
            security: "is_granted('ROLE_ADMIN')", 
        ),
        new Put(
            security: "is_granted('ROLE_ADMIN')", 
        ),
        new Delete(
            security: "is_granted('ROLE_ADMIN')", 
        ),
    ]
)]
#[ApiFilter(SearchFilter::class, properties: [
    'name' => 'partial',
    'type' => 'iexact',
])]
class Alcohol
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[ORM\Column(type: "uuid")]
    #[Groups(["alcohol"])]
    private ?UuidInterface $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Alcohol name is required.')]
    #[Groups(["alcohol"])]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Alcohol type is required.')]
    #[Assert\Choice(choices: ['beer', 'wine', 'whiskey', 'vodka', 'rum'], message: 'Invalid alcohol type.')]
    #[Groups(["alcohol"])]
    private ?string $type = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Alcohol description is required.')]
    #[Groups(["alcohol"])]
    private ?string $description = null;

    #[ORM\ManyToOne(targetEntity: Producer::class, cascade: ["persist"])]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Alcohol producer is required.')]
    #[Groups(["alcohol"])]
    private ?Producer $producer = null;

    #[ORM\Column(type: 'float')]
    #[Assert\NotBlank(message: 'Alcohol ABV is required.')]
    #[Assert\Type(type: 'float', message: 'ABV should be a float.')]
    #[Groups(["alcohol"])]
    private ?float $abv = null;

    #[ORM\ManyToOne(targetEntity: Image::class, cascade: ["persist"])]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Alcohol image is required.')]
    #[Groups(["alcohol"])]
    private ?Image $image = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(["alcohol"])]
    private ?\DateTimeInterface $dateCreated = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(["alcohol"])]
    private ?\DateTimeInterface $dateModified = null;

    public function getId(): ?UuidInterface
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getProducer(): ?Producer
    {
        return $this->producer;
    }

    public function setProducer(?Producer $producer): self
    {
        $this->producer = $producer;

        return $this;
    }

    public function getAbv(): ?float
    {
        return $this->abv;
    }

    public function setAbv(float $abv): self
    {
        $this->abv = $abv;

        return $this;
    }

    public function getImage(): ?Image
    {
        return $this->image;
    }

    public function setImage(?Image $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getDateCreated(): ?\DateTimeInterface
    {
        return $this->dateCreated;
    }

    public function setDateCreated(\DateTimeInterface $dateCreated): static
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    public function getDateModified(): ?\DateTimeInterface
    {
        return $this->dateModified;
    }

    public function setDateModified(\DateTimeInterface $dateModified): static
    {
        $this->dateModified = $dateModified;

        return $this;
    }
}
