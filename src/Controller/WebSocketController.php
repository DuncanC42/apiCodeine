<?php
// src/Controller/WebSocketController.php
namespace App\Controller;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class WebSocketController extends AbstractController implements MessageComponentInterface
{
    private $clients;
    private $userCount = 0;
    private $clientTypes = []; // clé : resourceId => 'admin' ou 'player'

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
    }

    /**
     * @Route("/start-websocket", name="start_websocket")
     */
    public function startWebSocket()
    {
        // Cette route peut être utilisée pour vérifier le statut du serveur
        return new Response('WebSocket Server is running');
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
        echo "Nouvelle connexion! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $data = json_decode($msg, true);

        if (!$data || !isset($data['action'])) {
            return;
        }

        switch ($data['action']) {
            case 'userConnected':
                $this->clientTypes[$from->resourceId] = 'player';
                $this->userCount++;
                $this->broadcastCount();
                break;

            case 'adminConnected':
                $this->clientTypes[$from->resourceId] = 'admin';
                // Ne pas incrémenter le compteur pour les admins
                $this->broadcastCount(); // Envoi l'état actuel
                break;

            case 'userDisconnected':
                if (isset($this->clientTypes[$from->resourceId]) &&
                    $this->clientTypes[$from->resourceId] === 'player') {
                    $this->decrementUserCount();
                    $this->broadcastCount();
                }
                break;
        }

        echo "Action: {$data['action']} | Joueurs: {$this->userCount}\n";
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);

        // Vérifie le type et décrémente si joueur
        if (
            isset($this->clientTypes[$conn->resourceId]) &&
            $this->clientTypes[$conn->resourceId] === 'player'
        ) {
            $this->decrementUserCount();
            $this->broadcastCount();
        }

        unset($this->clientTypes[$conn->resourceId]);

        echo "Connexion {$conn->resourceId} fermée\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "Une erreur est survenue: {$e->getMessage()}\n";
        $conn->close();
    }

    /**
     * Diffuse le nombre d'utilisateurs actifs à tous les clients
     */
    private function broadcastCount()
    {
        $message = json_encode(['userCount' => $this->userCount]);

        foreach ($this->clients as $client) {
            $client->send($message);
        }
    }

    /**
     * décremente le nombre d'utilisateurs en faisant attention à ne pas descendre en dessous de 0
     */
    private function decrementUserCount(): void
    {
        $this->userCount = max(0, $this->userCount - 1);
    }
}