<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DocRepository")
 */
class Doc
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Contact", inversedBy="docs")
     */
    private $contact;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $number;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $create_at;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $update_at;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $delete_at;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Contact", mappedBy="contact")
     */
    private $contacts;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContact(): ?Contact
    {
        return $this->contact;
    }

    public function setContact(?Contact $contact): self
    {
        $this->contact = $contact;

        return $this;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(string $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getCreateAt(): ?\DateTimeInterface
    {
        return $this->create_at;
    }

    public function setCreateAt(?\DateTimeInterface $create_at): self
    {
        $this->create_at = $create_at;

        return $this;
    }

    public function getUpdateAt(): ?\DateTimeInterface
    {
        return $this->update_at;
    }

    public function setUpdateAt(?\DateTimeInterface $update_at): self
    {
        $this->update_at = $update_at;

        return $this;
    }

    public function getDeleteAt(): ?\DateTimeInterface
    {
        return $this->delete_at;
    }

    public function setDeleteAt(?\DateTimeInterface $delete_at): self
    {
        $this->delete_at = $delete_at;

        return $this;
    }
}
