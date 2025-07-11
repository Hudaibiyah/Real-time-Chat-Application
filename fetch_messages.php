<?php
include "db.php";

$room_id = isset($_GET['room_id']) ? intval($_GET['room_id']) : 1;

$stmt = $conn->prepare("SELECT m.message_text, m.timestamp, u.username 
                        FROM messages m 
                        JOIN users u ON m.user_id = u.id 
                        WHERE m.room_id = ? 
                        ORDER BY m.timestamp ASC");
$stmt->bind_param("i", $room_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = [
        'username' => $row['username'],
        'message' => $row['message_text'],   
        'timestamp' => $row['timestamp']
    ];
}

echo json_encode($messages);
?>
