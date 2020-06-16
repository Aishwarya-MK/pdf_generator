<?php

namespace App\Entity;

use App\Repository\TemplateRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=TemplateRepository::class)
 */
class Template
{

    const PDF_PORTRAIT =0;
    const PDF_LANDSCAPE =1;
     const PDF_PORTRAIT_VALUE = "portrait";
    const PDF_LANDSCAPE_VALUE = "landscape";

    const PDFSTORAGE = "pdf";
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string",name="name", length=100, unique=true)
     * @Assert\Length(
     *      max = 100,
     *      maxMessage = "Field username cannot be longer than {{ limit }} characters"
     * )
     * @Assert\NotBlank(message="Field name should not be blank")
     */
    private $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $content;

    /**
     * @ORM\Column(type="text", nullable=true, options={"comment":"List separated with ','"})
     */
    private $modifiers;

    /**
     * @ORM\Column(type="boolean", name="is_active",  options={"default" : 0,"comment":"0=inactive,1=active"})
     */
    private $isActive;

    /**
     * @ORM\Column(name ="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(name ="updated_at", type="datetime")
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="boolean", length=30, nullable=true,options={"default" : 0})
     */
    private $type;

    public function getId(): ?int
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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getModifiers(): ?string
    {
        return $this->modifiers;
    }

    public function setModifiers(?string $modifiers): self
    {
        $this->modifiers = $modifiers;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Gets triggered only on insert
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->createdAt = new \DateTime("now");
        $this->updatedAt = new \DateTime("now");
    }

    /**
     * Gets triggered every time on update
     * @ORM\PreUpdate
     */
    public function onPreUpdate()
    {
        $this->updatedAt = new \DateTime("now");
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

}
