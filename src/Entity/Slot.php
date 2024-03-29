<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SlotRepository")
 */
class Slot
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetimetz")
     * @Assert\NotBlank
     * @Assert\DateTime
     * @var string A "Y-m-d\TH:i:sP" formatted value
     */
    private $starts;

    /**
     * @ORM\Column(type="datetimetz")
     * @Assert\NotBlank
     * @Assert\DateTime
     * @var string A "Y-m-d\TH:i:sP" formatted value
     */
    private $ends;

    /**
     * @ORM\Column(type="float")
     * @Assert\NotBlank
     * @Assert\PositiveOrZero
     */
    private $price;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Currency", inversedBy="slots")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotBlank
     */
    private $currency;

    /**
     * @ORM\Column(type="boolean")
     * Assert\NotNull
     * @Assert\Type("bool")
     */
    private $available;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Pitch", inversedBy="slots")
     * @ORM\JoinColumn(nullable=false)
     */
    private $pitch;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStarts(): ?\DateTimeInterface
    {
        return $this->starts;
    }

    public function setStarts(\DateTimeInterface $starts): self
    {
        $this->starts = $starts;

        return $this;
    }

    public function getEnds(): ?\DateTimeInterface
    {
        return $this->ends;
    }

    public function setEnds(\DateTimeInterface $ends): self
    {
        $this->ends = $ends;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getCurrency(): ?Currency
    {
        return $this->currency;
    }

    public function setCurrency(?Currency $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function getAvailable(): ?bool
    {
        return $this->available;
    }

    public function setAvailable(bool $available): self
    {
        $this->available = $available;

        return $this;
    }

    public function getPitch(): ?Pitch
    {
        return $this->pitch;
    }

    public function setPitch(?Pitch $pitch): self
    {
        $this->pitch = $pitch;

        return $this;
    }
}
