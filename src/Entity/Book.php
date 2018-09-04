<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BookRepository")
 * @UniqueEntity(fields={"Name", "Year"})
 * @UniqueEntity("ISBN")
 */
class Book
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
	 * @Assert\Length(
     *      min = 1,
     *      max = 255,
     *      minMessage = "Book name must be at least {{ limit }} characters long",
     *      maxMessage = "Book name cannot be longer than {{ limit }} characters"
     * )
     */
    private $Name;

    /**
     * @ORM\Column(type="integer")
	 * @Assert\GreaterThanOrEqual(0)
     */
    private $year;

    /**
     * @ORM\Column(type="string", length=17)
	 * @Assert\Length(
     *      min = 17,
     *      max = 17,
     *      minMessage = "ISBN must be {{ limit }} characters long",
     *      maxMessage = "ISBN must be {{ limit }} characters long"
     * )
	 * @Assert\Regex("/^[0-9]+(?:-[0-9]+){4}$/")
     */
    private $ISBN;

    /**
     * @ORM\Column(type="integer")
	 * @Assert\GreaterThan(0)
     */
    private $Pages;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(int $year): self
    {
        $this->year = $year;

        return $this;
    }

    public function getISBN(): ?string
    {
        return $this->ISBN;
    }

    public function setISBN(string $ISBN): self
    {
        $this->ISBN = $ISBN;

        return $this;
    }

    public function getPages(): ?int
    {
        return $this->Pages;
    }

    public function setPages(int $Pages): self
    {
        $this->Pages = $Pages;

        return $this;
    }
}
