<?php
require 'vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class ChatServer implements MessageComponentInterface {
    protected $clients = [];
    protected $usernames = [];

    public function onOpen(ConnectionInterface $conn) {
        parse_str($conn->httpRequest->getUri()->getQuery(), $query);
        $roomId = $query['room_id'] ?? 1;
        $username = $query['username'] ?? 'Guest';

        $conn->room_id = $roomId;
        $conn->username = $username;

        $this->clients[$roomId][$conn->resourceId] = $conn;
        $this->usernames[$roomId][$conn->resourceId] = $username;

        $this->broadcastActiveUsers($roomId);

        $conn->send(json_encode(["system" => true, "text" => "$username joined room $roomId"]));
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        if (str_starts_with($msg, "TYPING||")) {
            foreach ($this->clients[$from->room_id] as $client) {
                if ($from !== $client) {
                    $client->send("TYPING: {$from->username} is typing...");
                }
            }
            return;
        }

        if (str_starts_with($msg, "STOP_TYPING||")) {
            foreach ($this->clients[$from->room_id] as $client) {
                if ($from !== $client) {
                    $client->send("STOP_TYPING");
                }
            }
            return;
        }

        list($user_id, $username, $room_id, $text) = explode("||", $msg, 4);
        $time = date("H:i");

        $payload = json_encode([
            "user_id" => $user_id,
            "username" => $username,
            "room_id" => $room_id,
            "text" => $text,
            "time" => $time
        ]);

        foreach ($this->clients[$room_id] ?? [] as $client) {
            $client->send($payload);
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $roomId = $conn->room_id;
        unset($this->clients[$roomId][$conn->resourceId]);
        unset($this->usernames[$roomId][$conn->resourceId]);
        $this->broadcastActiveUsers($roomId);
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Error: {$e->getMessage()}\n";
        $conn->close();
    }

    private function broadcastActiveUsers($roomId) {
        $active = array_values($this->usernames[$roomId] ?? []);
        $payload = json_encode(["active_users" => $active]);
        foreach ($this->clients[$roomId] ?? [] as $client) {
            $client->send($payload);
        }
    }
}

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new ChatServer()
        )
    ),
    8080
);

echo "WebSocket server running at ws://localhost:8080\n";
$server->run();
