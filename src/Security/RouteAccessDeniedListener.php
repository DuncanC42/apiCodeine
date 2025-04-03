<?php

namespace App\Security;

use App\Entity\Admin;
use App\Entity\Joueur;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Security;

class RouteAccessDeniedListener implements EventSubscriberInterface
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();
        $pathInfo = $request->getPathInfo();
        $user = $this->security->getUser();

        // Skip for non-authenticated routes
        if (!$user || 
            strpos($pathInfo, '/api/login') === 0 ||
            strpos($pathInfo, '/intranet/login') === 0 ||
            strpos($pathInfo, '/api/register') === 0 ||
            strpos($pathInfo, '/intranet/register') === 0) {
            return;
        }

        // Strictly enforce route access by user type
        if (strpos($pathInfo, '/intranet/') === 0 && !$user instanceof Admin) {
            $event->setResponse(new JsonResponse(
                ['error' => 'Access denied: Only admin users can access intranet routes'],
                JsonResponse::HTTP_FORBIDDEN
            ));
        } elseif (strpos($pathInfo, '/api/') === 0 && !$user instanceof Joueur) {
            $event->setResponse(new JsonResponse(
                ['error' => 'Access denied: Only player users can access API routes'],
                JsonResponse::HTTP_FORBIDDEN
            ));
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 30], // Higher priority than firewall
        ];
    }
}
