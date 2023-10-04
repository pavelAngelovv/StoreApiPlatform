<?php

namespace App\Entity;

use App\Repository\ImageRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ImageRepository::class)]
// #[ApiResource]
class Image
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[ORM\Column(type: "uuid")]
    #[Groups(["alcohol"])]
    private ?UuidInterface $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["alcohol"])]
    private ?string $filename = null;

    #[Groups(["alcohol"])]
    private ?string $name = null;

    #[Groups(["alcohol"])]
    private ?string $url = null;

    public function getId(): ?UuidInterface
    {
        return $this->id;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): static
    {
        $this->filename = $filename;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getUrl(): ?string
    {
        if ($this->name) {
            return '/storage/images/' . $this->filename;
        }

        return null;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;
        return $this;
    }
}
