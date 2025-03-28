<?php

namespace App\Entity;

use App\Repository\JoueurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=JoueurRepository::class)
 */
class Joueur implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=150, unique=true)
     * @Assert\NotBlank(message="L'email est obligatoire.")
     * @Assert\Email(message="L'email '{{ value }}' n'est pas valide.")
     */
    private $email;

    /**
     * @ORM\Column(type="datetime")
     */
    private $derniere_connexion;

    /**
     * @ORM\Column(type="string", length=50, unique=true)
     * @Assert\NotBlank(message="Le pseudo est obligatoire.")
     * @Assert\Length(
     *      min=3,
     *      max=20,
     *      minMessage="Le pseudo doit contenir au moins {{ limit }} caractères.",
     *      maxMessage="Le pseudo ne peut pas dépasser {{ limit }} caractères."
     * )
     */
    private $pseudo;

    /**
     * @ORM\Column(type="integer")
     */
    private $nb_partage;

    /**
     * @ORM\Column(type="integer")
     */
    private $temps_joue;

    /**
     * @ORM\OneToMany(targetEntity=Score::class, mappedBy="player", orphanRemoval=true)
     */
    private $scores;

    /**
     * @ORM\ManyToOne(targetEntity=Leaderboard::class, inversedBy="joueurs")
     */
    private $leaderboard;

    public function __construct()
    {
        $this->scores = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getDerniereConnexion(): ?\DateTimeInterface
    {
        return $this->derniere_connexion;
    }

    public function setDerniereConnexion(\DateTimeInterface $derniere_connexion): self
    {
        $this->derniere_connexion = $derniere_connexion;

        return $this;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): self
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function getNbPartage(): ?int
    {
        return $this->nb_partage;
    }

    public function setNbPartage(int $nb_partage): self
    {
        $this->nb_partage = $nb_partage;

        return $this;
    }

    public function getTempsJoue(): ?int
    {
        return $this->temps_joue;
    }

    public function setTempsJoue(int $temps_joue): self
    {
        $this->temps_joue = $temps_joue;

        return $this;
    }

    /**
     * @return Collection<int, Score>
     */
    public function getScores(): Collection
    {
        return $this->scores;
    }

    public function addScore(Score $score): self
    {
        if (!$this->scores->contains($score)) {
            $this->scores[] = $score;
            $score->setPlayer($this);
        }

        return $this;
    }

    public function removeScore(Score $score): self
    {
        if ($this->scores->removeElement($score)) {
            // set the owning side to null (unless already changed)
            if ($score->getPlayer() === $this) {
                $score->setPlayer(null);
            }
        }

        return $this;
    }

    public function getLeaderboard(): ?leaderboard
    {
        return $this->leaderboard;
    }

    public function setLeaderboard(?leaderboard $leaderboard): self
    {
        $this->leaderboard = $leaderboard;

        return $this;
    }

    // UserInterface methods
    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function getPassword(): ?string
    {
        return null; // No password
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials()
    {
        // No sensitive data to erase
    }

    public function getUsername(): string
    {
        return $this->email; // or $this->pseudo
    }
}