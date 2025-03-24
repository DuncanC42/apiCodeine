<?php

namespace App\Entity;

use App\Repository\StatistiquesRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=StatistiquesRepository::class)
 */
class Statistiques
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
    private $nb_joueurs_total;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNbJoueursTotal(): ?int
    {
        return $this->nb_joueurs_total;
    }

    public function setNbJoueursTotal(int $nb_joueurs_total): self
    {
        $this->nb_joueurs_total = $nb_joueurs_total;

        return $this;
    }
}
