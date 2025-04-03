<?php

namespace App\EventListener;

use App\Entity\Admin;
use App\Entity\Joueur;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;

class JWTCreatedListener
{
    /**
     * @param JWTCreatedEvent $event
     *
     * @return void
     */
    public function onJWTCreated(JWTCreatedEvent $event)
    {
        $user = $event->getUser();
        $payload = $event->getData();
        
        // Add user type to the payload
        if ($user instanceof Admin) {
            $payload['user_type'] = 'admin';
        } elseif ($user instanceof Joueur) {
            $payload['user_type'] = 'joueur';
        }
        
        $event->setData($payload);
    }
}
