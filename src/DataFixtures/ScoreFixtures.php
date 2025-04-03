<?php

namespace App\DataFixtures;

use App\Entity\Score;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ScoreFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        
        // For each player (50 players)
        for ($playerId = 0; $playerId < 50; $playerId++) {
            $player = $this->getReference(JoueurFixtures::JOUEUR_REFERENCE_PREFIX . $playerId);
            
            // For each game (5 games)
            for ($gameId = 0; $gameId < 5; $gameId++) {
                $jeu = $this->getReference(JeuFixtures::JEU_REFERENCE_PREFIX . $gameId);
                
                $score = new Score();
                $score->setPoints($faker->numberBetween(0, 2000));
                $score->setTempsJeu($faker->numberBetween(10, $jeu->getTempsMax()));
                $score->setNbEssais($faker->numberBetween(1, 10));
                $score->setJeu($jeu);
                $score->setPlayer($player);
                
                $manager->persist($score);
            }
        }
        
        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            JeuFixtures::class,
            JoueurFixtures::class,
        ];
    }
}
