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
        $this->broadcastCount();

        echo "Nouvelle connexion! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $data = json_decode($msg, true);

        if (!$data) {
            return;
        }

        if ($data['action'] === 'userConnected') {
            $this->userCount++;
        }

        $this->broadcastCount();

        echo "Action reçue: {$data['action']}, Nombre de joueurs: {$this->userCount}\n";
    }



    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        $this->userCount--;

        $this->broadcastCount();

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
}
