<?php

namespace App\DataFixtures;

use App\Entity\Token;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TokenFixtures extends Fixture
{
    public const TOKEN_REFERENCE_PREFIX = 'token_';

    public function load(ObjectManager $manager): void
    {
        $tokenData = [
            [
                'id' => 1,
                'key' => 'test',
                'created_at' => new \DateTimeImmutable('2025-03-30 00:00:00'),
            ],
            // Ajoutez d'autres enregistrements ici si nécessaire
        ];

        foreach ($tokenData as $index => $tokenItem) {
            $token = new Token();
            $token->setId($tokenItem['id']);
            $token->setKey($tokenItem['key']);
            $token->setCreatedAt($tokenItem['created_at']);

            $manager->persist($token);

            // Ajouter une référence pour chaque token
            $this->addReference(self::TOKEN_REFERENCE_PREFIX . $index, $token);
        }

        $manager->flush();
    }
}
