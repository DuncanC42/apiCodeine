<?php
// src/Command/WebSocketServerCommand.php
namespace App\Command;

use App\Controller\WebSocketController;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WebSocketServerCommand extends Command
{
    protected static $defaultName = 'app:websocket:server';

    protected function configure()
    {
        $this
            ->setDescription('Démarre le serveur WebSocket pour le suivi des utilisateurs connectés')
            ->setHelp('Cette commande démarre un serveur WebSocket pour suivre le nombre d\'utilisateurs connectés à l\'application.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Démarrage du serveur WebSocket sur le port 8051...');

        $server = IoServer::factory(
            new HttpServer(
                new WsServer(
                    new WebSocketController()
                )
            ),
            8051
        );

        $output->writeln('Serveur WebSocket démarré! Écoute sur le port 8051');
        $server->run();
    }
}
