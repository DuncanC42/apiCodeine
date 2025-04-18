<?php

namespace App\Entity;

use App\Repository\LeaderboardRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LeaderboardRepository::class)
 */
class Leaderboard
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $scorescore_global;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date_maj;

    /**
     * @ORM\OneToMany(targetEntity=Joueur::class, mappedBy="leaderboard")
     */
    private $joueurs;

    public function __construct()
    {
        $this->joueurs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getScorescoreGlobal(): ?int
    {
        return $this->scorescore_global;
    }

    public function setScorescoreGlobal(int $scorescore_global): self
    {
        $this->scorescore_global = $scorescore_global;

        return $this;
    }

    public function getDateMaj(): ?\DateTimeInterface
    {
        return $this->date_maj;
    }

    public function setDateMaj(\DateTimeInterface $date_maj): self
    {
        $this->date_maj = $date_maj;

        return $this;
    }

    /**
     * @return Collection<int, Joueur>
     */
    public function getJoueurs(): Collection
    {
        return $this->joueurs;
    }

    public function addJoueur(Joueur $joueur): self
    {
        if (!$this->joueurs->contains($joueur)) {
            $this->joueurs[] = $joueur;
            $joueur->setLeaderboard($this);
        }

        return $this;
    }

    public function removeJoueur(Joueur $joueur): self
    {
        if ($this->joueurs->removeElement($joueur)) {
            // set the owning side to null (unless already changed)
            if ($joueur->getLeaderboard() === $this) {
                $joueur->setLeaderboard(null);
            }
        }

        return $this;
    }
}
