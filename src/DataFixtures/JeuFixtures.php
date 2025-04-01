<?php

namespace App\DataFixtures;

use App\Entity\Jeu;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class JeuFixtures extends Fixture
{
    public const JEU_REFERENCE_PREFIX = 'jeu_';
    
    public function load(ObjectManager $manager): void
    {
        $jeuxData = [
            [
                'nom' => 'Carte vitale',
                'etape' => 1,
                'nb_niveau' => 1,
                'description' => 'Amélie retrouve sa carte Vitale en morceaux après la soirée d\'intégration. Aide-la à la reconstituer au plus vite !',
                'regles' => '',
                'message_fin' => 'La carte Vitale contient les informations personnelles nécessaires au remboursement de tes frais de santé ou en cas d\'hospitalisation. C\'est la garantie d\'être bien remboursé rapidement. En cas de perte, tu peux en commander une nouvelle directement depuis ton compte sur ameli.fr',
                'photo' => '',
                'temps_max' => 60
            ],
            [
                'nom' => 'C2S',
                'etape' => 2,
                'nb_niveau' => 1,
                'description' => 'Seb est allé aux urgences parce qu\'il s\'est cassé le pied au foot. Il doit payer une partie des soins \(radio, plâtre…\) parce qu\'il n\'a pas de complémentaire santé ! Mets dans le panier tout ce qu\'une complémentaire santé prend en charge !',
                'regles' => '',
                'message_fin' => 'La complémentaire santé solidaire \(C2S\) est une aide pour payer ses dépenses de santé, si tes ressources sont faibles. Avec la C2S tu ne paies pas le médecin, ni tes médicaments en pharmacie. La plupart des lunettes et des soins dentaires sont pris en charge. Tu peux faire une simulation sur ameli.fr pour savoir si tu y as droit !',
                'photo' => '',
                'temps_max' => 60
            ],
            [
                'nom' => 'RIB',
                'etape' => 3,
                'nb_niveau' => 2,
                'description' => 'La mère de Pauline l\'appelle pour lui dire qu\'elle a eu des remboursements de consultations par l\'Assurance Maladie qui ne la concernent pas… « T\'as bien mis à jour ton RIB à la CPAM ? » Aide Pauline à compléter son RIB !',
                'regles' => '',
                'message_fin' => 'Les remboursements de l\'Assurance Maladie se font par virement bancaire. Depuis ton compte ameli, enregistrer ton RIB c\'est être sûr de recevoir les remboursements sur ton propre compte bancaire !',
                'photo' => '',
                'temps_max' => 60
            ],
            [
                'nom' => 'Examen de prévention',
                'etape' => 4,
                'nb_niveau' => 1,
                'description' => 'Amélie adore manger des pizzas accompagnées d\'un soda, même si elle sait que pas top pour sa santé… Coupe les aliments les moins bons pour sa santé !',
                'regles' => '',
                'message_fin' => 'L\'Assurance Maladie offre aux jeunes de 16 à 25 ans un examen de prévention santé. Il peut être réalisé dans un centre d\'examens de santé.',
                'photo' => '',
                'temps_max' => 60
            ],
            [
                'nom' => 'M\'T dents',
                'etape' => 5,
                'nb_niveau' => 1,
                'description' => 'Damien a mal aux dents… Brosse ses dents avec du dentifrice pour enlever les restes alimentaires et lui faire retrouver le sourire !',
                'regles' => '',
                'message_fin' => 'Pour garder le sourire : 1/ brosse-toi correctement les dents 2 fois par jour pendant 2 minutes 2/ consulte ton dentiste au moins une fois par an L\'Assurance Maladie offre des rendez-vous de prévention avec le dentiste appelés « M\'T dents » aux jeunes de âgés de 18, 21 et 24 ans !',
                'photo' => '',
                'temps_max' => 60
            ]
        ];

        foreach ($jeuxData as $index => $jeuData) {
            $jeu = new Jeu();
            $jeu->setNom($jeuData['nom']);
            $jeu->setEtape($jeuData['etape']);
            $jeu->setNbNiveau($jeuData['nb_niveau']);
            $jeu->setDescription($jeuData['description']);
            $jeu->setRegles($jeuData['regles']);
            $jeu->setMessageFin($jeuData['message_fin']);
            $jeu->setPhoto($jeuData['photo']);
            $jeu->setTempsMax($jeuData['temps_max']);

            $manager->persist($jeu);
            
            // Add reference for each game
            $this->addReference(self::JEU_REFERENCE_PREFIX . $index, $jeu);
        }

        $manager->flush();
    }
}
