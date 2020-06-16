<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\HasLifecycleCallbacks
 *  @UniqueEntity(
 *     fields={"username"},
 *     errorPath="username",
 *     message="This user is already exist."
 * )
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string",name="username", length=30, unique=true)
     * @Assert\Length(
     *      max = 30,
     *      maxMessage = "Field username cannot be longer than {{ limit }} characters"
     * )
     * @Assert\NotBlank(message="Field username should not be blank")
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=500)
     * @Assert\NotBlank(message="Field password should not be blank")
     * @Assert\Length(
     *      max = 500,
     *      maxMessage = "Your password cannot be longer than {{ limit }} characters"
     * )
     */
    private $password;

    /**
     * @ORM\Column(type="boolean", name="is_active",  options={"default" : 0,"comment":"0=inactive,1=active"})
     */
    private $isActive;

    /**
     * @ORM\Column(name ="created_at",type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(name= "updated_at",type="datetime")
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="string", length=30, nullable=true)
     * @Assert\Length(
     *      max = 30,
     *      maxMessage = "Field role cannot be longer than {{ limit }} characters"
     * )
     */
    private $role;

    public function __construct($username)
    {
        $this->isActive = true;
        $this->role ="ROLE_ADMIN";
        $this->username = $username;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(?bool $isActive): self
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
    public function getRoles()
    {
        return array('ROLE_ADMIN');
    }

    public function getSalt()
    {
        return null;
    }

    public function eraseCredentials()
    {
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

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(?string $role): self
    {
        $this->role = $role;

        return $this;
    }
}
