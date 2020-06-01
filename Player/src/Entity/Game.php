<?php

namespace App\Entity;

use App\Repository\GameRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=GameRepository::class)
 */
class Game
{
    const nbMaxStep = 10;
    const nbMaxError = 3;
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $score;

    /**
     * @ORM\ManyToMany(targetEntity=Music::class, inversedBy="games")
     */
    private $musics;

    /**
     * @ORM\ManyToOne(targetEntity=Player::class, inversedBy="games")
     * @ORM\JoinColumn(nullable=false)
     */
    private $player;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $Musics_Selected;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $Musics_Right_Answer;


    public function __construct()
    {
        $this->Musics_Selected = serialize([]);
        $this->Musics_Right_Answer = serialize([]);

        $this->musics = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(int $score): self
    {
        $this->score = $score;

        return $this;
    }

    /**
     * @return Collection|Music[]
     */
    public function getMusics(): Collection
    {
        return $this->musics;
    }

    public function addMusic(Music $music): self
    {
        if (!$this->musics->contains($music)) {
            $this->musics[] = $music;
        }

        return $this;
    }

    public function removeMusic(Music $music): self
    {
        if ($this->musics->contains($music)) {
            $this->musics->removeElement($music);
        }

        return $this;
    }

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function setPlayer(?Player $player): self
    {
        $this->player = $player;

        return $this;
    }

    public function getMusicsSelected(): ?array
    {
        return unserialize($this->Musics_Selected);
    }

    public function setMusicsSelected(?string $Musics_Selected): self
    {
        $this->Musics_Selected = serialize($Musics_Selected);

        return $this;
    }

    public function getMusicsRightAnswer(): ?array
    {
        return unserialize($this->Musics_Right_Answer);
    }

    public function setMusicsRightAnswer(?string $Musics_Right_Answer): self
    {
        $this->Musics_Right_Answer = serialize($Musics_Right_Answer);

        return $this;
    }
}
