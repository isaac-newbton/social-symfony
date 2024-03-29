<?php

namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidV7Generator;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity(repositoryClass: PostRepository::class)]
class Post
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid_binary", unique: true)]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: UuidV7Generator::class)]
    protected UuidInterface|string $id;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $datetime = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $text = null;

    #[ORM\Column]
    private ?int $status = null;

    #[ORM\OneToMany(targetEntity: MediaAttachment::class, mappedBy: 'post', orphanRemoval: true)]
    private Collection $mediaAttachments;

    public function __construct() {
        $this->datetime = new \DateTime();
        $this->mediaAttachments = new ArrayCollection();
        $this->status = 0;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getDatetime(): ?\DateTimeInterface
    {
        return $this->datetime;
    }

    public function setDatetime(\DateTimeInterface $Datetime): static
    {
        $this->datetime = $Datetime;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): static
    {
        $this->text = $text;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function statusText(): string
    {
        $text = 'unknown';
        switch($this->status) {
            case 0:
                $text = 'draft';
                break;
            case 1:
                $text = 'public';
                break;
            case 2:
                $text = 'private';
                break;
            case 3:
                $text = 'expired';
                break;
            default:
                break;
        }
        return $text;
    }

    public function shouldExpire(): bool
    {
        if(1 < $this->status)
        {
            return false;
        }

        $diff = (new \DateTime())->getTimestamp() - $this->datetime->getTimestamp();

        return ($diff > 86400);
    }

    /**
     * @return Collection<int, MediaAttachment>
     */
    public function getMediaAttachments(): Collection
    {
        return $this->mediaAttachments;
    }

    public function addMediaAttachment(MediaAttachment $mediaAttachment): static
    {
        if (!$this->mediaAttachments->contains($mediaAttachment)) {
            $this->mediaAttachments->add($mediaAttachment);
            $mediaAttachment->setPost($this);
        }

        return $this;
    }

    public function removeMediaAttachment(MediaAttachment $mediaAttachment): static
    {
        if ($this->mediaAttachments->removeElement($mediaAttachment)) {
            // set the owning side to null (unless already changed)
            if ($mediaAttachment->getPost() === $this) {
                $mediaAttachment->setPost(null);
            }
        }

        return $this;
    }

    public function displayRelativeTime(): string
    {
        $diff = (new \DateTime())->getTimestamp() - $this->datetime->getTimestamp();
        if(60 >= $diff) {
            return 'Just now';
        }else if(120 > $diff) {
            return 'A minute ago';
        }else if(3600 > $diff) {
            return floor($diff/60) . ' minutes ago';
        }else if(7200 > $diff) {
            return 'An hour ago';
        }else if(86400 > $diff) {
            return floor($diff/3600) . ' hours ago';
        }else if(172800 > $diff) {
            return 'Yesterday';
        }else if(604800 > $diff) {
            return floor($diff/86400) . ' days ago';
        }else if(2678400 > $diff) {
            return ceil($diff/604800) . ' weeks ago';
        }else if(5184000 > $diff) {
            return 'Last month';
        }
        return $this->datetime->format('F j Y');
    }
}
