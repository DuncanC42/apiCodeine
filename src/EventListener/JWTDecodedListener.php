<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTDecodedEvent;
use Symfony\Component\HttpFoundation\RequestStack;

class JWTDecodedListener
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @param JWTDecodedEvent $event
     *
     * @return void
     */
    public function onJWTDecoded(JWTDecodedEvent $event)
    {
        $request = $this->requestStack->getCurrentRequest();
        
        if (!$request) {
            return;
        }
        
        $payload = $event->getPayload();
        $pathInfo = $request->getPathInfo();
        
        // Check if user type matches route type
        if (isset($payload['user_type'])) {
            if ($payload['user_type'] === 'joueur' && strpos($pathInfo, '/intranet/') === 0) {
                $event->markAsInvalid('Joueur tokens cannot access intranet routes');
            } elseif ($payload['user_type'] === 'admin' && strpos($pathInfo, '/api/') === 0) {
                $event->markAsInvalid('Admin tokens cannot access API routes');
            }
        }
    }
}
