<?php

namespace App\Entity;

use App\Repository\MediaAttachmentRepository;
use App\Service\FileUploader;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidV7Generator;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity(repositoryClass: MediaAttachmentRepository::class)]
#[ORM\HasLifecycleCallbacks]
class MediaAttachment
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid_binary", unique: true)]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: UuidV7Generator::class)]
    protected UuidInterface|string $id;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\ManyToOne(inversedBy: 'mediaAttachments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Post $post = null;

    #[ORM\Column(length: 255)]
    private ?string $path = null;
    
    #[ORM\Column(length: 1023)]
    private ?string $systemPath = null;

    public function getId(): string
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function setPost(?Post $post): static
    {
        $this->post = $post;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): static
    {
        $this->path = $path;

        return $this;
    }

    public function getSystemPath(): ?string
    {
        return $this->systemPath;
    }

    public function setSystemPath(string $systemPath): static
    {
        $this->systemPath = $systemPath;

        return $this;
    }

    #[ORM\PostRemove]
    public function deleteFile() {
        if(file_exists($this->systemPath)) {
            unlink($this->systemPath);
        }
    }
}
