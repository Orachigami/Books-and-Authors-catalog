<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AuthorRepository")
 * @UniqueEntity(fields={"Surname", "Name", "MiddleName"})
 */
class Author
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=64)
     * @Assert\Length(
     *      min = 4,
     *      max = 64,
     *      minMessage = "Your first name must be at least {{ limit }} characters long",
     *      maxMessage = "Your first name cannot be longer than {{ limit }} characters"
     * )
	 * @Assert\Regex("/^[A-Z][a-z]+(?:-[A-Z][a-z]+){0,}$/")
     */
    private $Surname;

    /**
     * @ORM\Column(type="string", length=64)
     * @Assert\Length(
     *      min = 4,
     *      max = 64,
     *      minMessage = "Your first name must be at least {{ limit }} characters long",
     *      maxMessage = "Your first name cannot be longer than {{ limit }} characters"
     * )
	 * @Assert\Regex("/^[A-Z][a-z]+$/")
     */
    private $Name;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     * @Assert\Length(
     *      min = 4,
     *      max = 64,
     *      minMessage = "Your first name must be at least {{ limit }} characters long",
     *      maxMessage = "Your first name cannot be longer than {{ limit }} characters"
     * )
	 * @Assert\Regex("/^[A-Z][a-z]+$/")
     */
    private $MiddleName;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSurname(): ?string
    {
        return $this->Surname;
    }

    public function setSurname(string $Surname): self
    {
        $this->Surname = $Surname;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->Name;
    }

    public function setName(string $Name): self
    {
        $this->Name = $Name;

        return $this;
    }

    public function getMiddleName(): ?string
    {
        return $this->MiddleName;
    }

    public function setMiddleName(?string $MiddleName): self
    {
        $this->MiddleName = $MiddleName;

        return $this;
    }
}
