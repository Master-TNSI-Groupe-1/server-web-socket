<?php
namespace Socket;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class SocketController implements MessageComponentInterface {
    protected $users;

    public function __construct() {
        $this->users = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        //Creation of the user for later use
        $user = new \stdClass();
        $user->conn = $conn;
        $this->users->attach($user);
        echo "New connection: ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        //TODO Define behaviour

        //TEST PURPOSE ONLY
        // $numRecv = count($this->users) - 1;
        // echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
        //     , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

        // foreach ($this->users as $user) {
        //     if ($from !== $user) {
        //         // The sender is not the receiver, send to each client connected
        //         $user->conn->send($msg);
        //     }
        // }
        //END TEST
    }

    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->users->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}
