<?php
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Socket\socketController;

require dirname(__DIR__) . '/../vendor/autoload.php';

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new SocketController()
        )
    ), 6466 // port
);

$server->run();
