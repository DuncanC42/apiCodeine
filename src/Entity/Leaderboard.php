<?php

namespace App\Entity;

use App\Repository\LeaderboardRepository;
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
}
