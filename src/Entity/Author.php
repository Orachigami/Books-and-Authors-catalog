<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
     *      minMessage = "Surname must be at least {{ limit }} characters long",
     *      maxMessage = "Surname cannot be longer than {{ limit }} characters"
     * )
	 * @Assert\Regex("/^[A-Z][a-z]+(?:-[A-Z][a-z]+){0,}$/")
     */
    private $Surname;

    /**
     * @ORM\Column(type="string", length=64)
     * @Assert\Length(
     *      min = 4,
     *      max = 64,
     *      minMessage = "First name must be at least {{ limit }} characters long",
     *      maxMessage = "First name cannot be longer than {{ limit }} characters"
     * )
	 * @Assert\Regex("/^[A-Z][a-z]+$/")
     */
    private $Name;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     * @Assert\Length(
     *      min = 4,
     *      max = 64,
     *      minMessage = "Middle name must be at least {{ limit }} characters long",
     *      maxMessage = "Middle name cannot be longer than {{ limit }} characters"
     * )
	 * @Assert\Regex("/^[A-Z][a-z]+$/")
     */
    private $MiddleName;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Book", inversedBy="authors") 
     * @ORM\JoinTable(name="authors_books")
     */
    private $books;

    public function __construct()
    {
        $this->books = new ArrayCollection();
    }

    public function getSurnameAndInitials(): ?string
    {
		$str = $this->Surname . ' ' . substr($this->Name, 0, 1) . '.';
		if (strlen($this->MiddleName) > 0)
			$str .= ' ' . substr($this->MiddleName, 0, 1). '.';
        return $str;
    }

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

    /**
     * @return Collection|Book[]
     */
    public function getBooks(): Collection
    {
        return $this->books;
    }

    public function addBook(Book $book): self
    {
        if (!$this->books->contains($book)) {
            $this->books[] = $book;
        }

        return $this;
    }

    public function removeBook(Book $book): self
    {
        if ($this->books->contains($book)) {
            $this->books->removeElement($book);
        }

        return $this;
    }
}
