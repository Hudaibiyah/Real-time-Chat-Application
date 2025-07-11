<?php 
session_start();
include "db.php";
if (!isset($_SESSION['user_id'])) header("Location: login.php");

$room_id = isset($_GET['room_id']) ? intval($_GET['room_id']) : 1;
$stmt = $conn->prepare("SELECT name FROM chat_rooms WHERE id=?");
$stmt->bind_param("i", $room_id);
$stmt->execute();
$room = $stmt->get_result()->fetch_assoc();
if (!$room) die("No such room");
?>
<!DOCTYPE html>
<html>
<head>
  <title><?= htmlspecialchars($room['name']) ?> Room</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    #chatBox {
      height: 350px;
      overflow-y: auto;
      padding: 10px;
      background: #f8f8f8;
      border: 1px solid #ccc;
    }
    .chat-message {
      background-color: #e1f5fe;
      padding: 8px 12px;
      border-radius: 10px;
      margin-bottom: 6px;
      max-width: 75%;
      display: inline-block;
      clear: both;
      float: left;
    }
    .chat-message.self {
      background-color: #dcf8c6;
      float: right;
      text-align: right;
    }
    #typingStatus {
      font-size: 0.9em;
      color: gray;
    }
  </style>
  <nav class="navbar navbar-expand-lg navbar-dark bg-secondary">
  <div class="container-fluid">
    <a class="navbar-brand d-flex align-items-center" href="#">
      <img src="https://cdn-icons-png.flaticon.com/512/9639/9639625.png"
        
           alt="Chat Icon" 
           width="40" 
           height="40" 
           class="me-2 rounded-circle">
      Chat App
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link active" href="dashboard.php">Rooms</a>
        </li>
        <li class="nav-item">
  <a class="btn btn-outline-dark ms-2" href="logout.php">Logout</a>
</li>
      </ul>
    </div>
  </div>
</nav>

</head>
<body>
<div class="container mt-5">
  <h3><?= htmlspecialchars($room['name']) ?> Room</h3>

  <h6>👥 Active Users:</h6>
  <ul id="userList" class="list-group mb-3"></ul>

  <div id="chatBox"></div>
  <div id="typingStatus"></div>

  <form id="chatForm" class="d-flex mt-2">
    <input id="messageInput" class="form-control me-2" placeholder="Type a message..." autocomplete="off" />
    <button class="btn btn-primary">Send</button>
  </form>

  <a href="dashboard.php" class="btn btn-dark mt-4">⬅️ Leave</a>
</div>

<script>
function escapeHTML(str) {
  return str.replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
}

const chatBox = document.getElementById("chatBox");
const typingStatus = document.getElementById("typingStatus");
const messageInput = document.getElementById("messageInput");
const chatForm = document.getElementById("chatForm");
const userList = document.getElementById("userList");
let typingTimeout = null;


fetch('fetch_messages.php?room_id=<?= $room_id ?>')
  .then(response => response.json())
  .then(data => {
    data.forEach(message => {
      const msgDiv = document.createElement('div');
      const username = escapeHTML(message.username);
      const text = escapeHTML(message.message); 
      const time = message.timestamp;

      msgDiv.innerHTML = `<strong>${username}</strong> [${time}]: ${text}`;
      msgDiv.classList.add("chat-message");
      if (username === "<?= $_SESSION['username'] ?>") {
        msgDiv.classList.add("self");
      }
      chatBox.appendChild(msgDiv);
    });
    chatBox.scrollTop = chatBox.scrollHeight;
  });


const conn = new WebSocket('ws://localhost:8080?username=<?= urlencode($_SESSION["username"]) ?>&room_id=<?= $room_id ?>');

conn.onmessage = (e) => {
  try {
    console.log("WS Message:", e.data);
    const data = JSON.parse(e.data);

    if (data.system) {
      const systemMsg = document.createElement("div");
      systemMsg.style.textAlign = "center";
      systemMsg.style.color = "#888";
      systemMsg.innerText = `[System]: ${data.text}`;
      chatBox.appendChild(systemMsg);
      chatBox.scrollTop = chatBox.scrollHeight;
    } 
    else if (data.active_users) {
      userList.innerHTML = "";
      data.active_users.forEach(user => {
        const li = document.createElement("li");
        li.className = "list-group-item";
        li.textContent = user;
        userList.appendChild(li);
      });
    } 
    else if (data.username && data.text && data.time) {
      const msgDiv = document.createElement("div");
      msgDiv.classList.add("chat-message");
      if (parseInt(data.user_id) === <?= $_SESSION['user_id'] ?>) {
        msgDiv.classList.add("self");
      }

      msgDiv.innerHTML = `<strong>${escapeHTML(data.username)}</strong> [${data.time}]: ${escapeHTML(data.text)}`;
      chatBox.appendChild(msgDiv);
      chatBox.scrollTop = chatBox.scrollHeight;
      typingStatus.innerText = "";

      if (document.hidden) {
        showNotification(`${data.username}: ${data.text}`);
      }
    } else {
      console.warn("Unexpected WS data format:", data);
    }

  } catch (err) {
    if (e.data.startsWith("TYPING:")) {
      const username = e.data.substring(7).replace(" is typing...", "");
      if (username !== "<?= $_SESSION['username'] ?>") {
        typingStatus.innerText = `${username} is typing...`;
      }
    } else if (e.data === "STOP_TYPING") {
      typingStatus.innerText = "";
    } else {
      console.error("Message parse error:", err, e.data);
    }
  }
};

chatForm.addEventListener("submit", (e) => {
  e.preventDefault();
  const msg = messageInput.value.trim();
  if (!msg) return;

  const fullMsg = "<?= $_SESSION['user_id'] ?>||<?= $_SESSION['username'] ?>||<?= $room_id ?>||" + msg;
  conn.send(fullMsg);
  messageInput.value = "";
});

messageInput.addEventListener("input", () => {
  conn.send("TYPING||<?= $room_id ?>||<?= $_SESSION['username'] ?>");
  clearTimeout(typingTimeout);
  typingTimeout = setTimeout(() => {
    conn.send("STOP_TYPING||<?= $room_id ?>||<?= $_SESSION['username'] ?>");
  }, 2000);
});

function showNotification(message) {
  if ("Notification" in window && Notification.permission === "granted") {
    new Notification("💬 New Message", {
      body: message,
      icon: "https://cdn-icons-png.flaticon.com/512/2950/2950651.png"
    });
  }
}

if ("Notification" in window && Notification.permission !== "granted") {
  Notification.requestPermission();
}
</script>

</body>
</html>
