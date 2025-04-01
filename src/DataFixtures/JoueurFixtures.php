<?php

namespace App\DataFixtures;

use App\Entity\Joueur;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class JoueurFixtures extends Fixture
{
    public const JOUEUR_REFERENCE_PREFIX = 'joueur_';

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        for ($i = 0; $i < 50; $i++) {
            $joueur = new Joueur();
            $joueur->setEmail($faker->email);
            $joueur->setPseudo($faker->userName);
            $joueur->setDerniereConnexion($faker->dateTimeBetween('-30 days', 'now'));
            $joueur->setNbPartage($faker->numberBetween(0, 15));
            $joueur->setTempsJoue($faker->numberBetween(60, 3600));

            $manager->persist($joueur);
            
            // Store reference for ScoreFixtures
            $this->addReference(self::JOUEUR_REFERENCE_PREFIX . $i, $joueur);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            JeuFixtures::class,
        ];
    }
}
