<?php

namespace App\DataFixtures;

use App\Entity\Parametres;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ParametreFixtures extends Fixture
{
    public const PARAMETRES_REFERENCE_PREFIX = 'parametres_';

    public function load(ObjectManager $manager): void
    {
        $parametresData = [
            [
                'id' => 1,
                'date_cloture' => new \DateTime('2025-10-30 00:00:00'),
                'date_debut' => new \DateTime('2025-04-08 00:00:00')
            ],
            // Ajoutez d'autres enregistrements ici si nécessaire
        ];

        foreach ($parametresData as $index => $parametreData) {
            $parametre = new Parametres();
            $parametre->setId($parametreData['id']);
            $parametre->setDateCloture($parametreData['date_cloture']);
            $parametre->setDateDebut($parametreData['date_debut']);

            $manager->persist($parametre);

            // Ajouter une référence pour chaque paramètre
            $this->addReference(self::PARAMETRES_REFERENCE_PREFIX . $index, $parametre);
        }

        $manager->flush();
    }
}
