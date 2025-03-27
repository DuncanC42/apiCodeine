<?php

namespace App\Entity;

use App\Repository\ScoreRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ScoreRepository::class)
 */
class Score
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
    private $points;

    /**
     * @ORM\Column(type="integer")
     */
    private $temps_jeu;

    /**
     * @ORM\Column(type="integer")
     */
    private $nb_essais;

    /**
     * @ORM\ManyToOne(targetEntity=jeu::class, inversedBy="scores")
     * @ORM\JoinColumn(nullable=false)
     */
    private $jeu;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPoints(): ?int
    {
        return $this->points;
    }

    public function setPoints(int $points): self
    {
        $this->points = $points;

        return $this;
    }

    public function getTempsJeu(): ?int
    {
        return $this->temps_jeu;
    }

    public function setTempsJeu(int $temps_jeu): self
    {
        $this->temps_jeu = $temps_jeu;

        return $this;
    }

    public function getNbEssais(): ?int
    {
        return $this->nb_essais;
    }

    public function setNbEssais(int $nb_essais): self
    {
        $this->nb_essais = $nb_essais;

        return $this;
    }

    public function getJeu(): ?jeu
    {
        return $this->jeu;
    }

    public function setJeu(?jeu $jeu): self
    {
        $this->jeu = $jeu;

        return $this;
    }
}
