<?php
session_start();
include "db.php";
if (!isset($_SESSION['user_id'])) header("Location: login.php");

$rooms = $conn->query("SELECT * FROM chat_rooms");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard | Chat App</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(to right, #f0f4ff,rgb(222, 224, 229));
      font-family: 'Inter', sans-serif;
    }
    .dashboard-card {
      border: none;
      border-radius: 16px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      background-color: white;
    }
    .chat-room-item {
      border-radius: 10px;
    }
    .btn-primary {
      border-radius: 8px;
      background-color: #4F46E5;
      border: none;
    }
    .btn-primary:hover {
      background-color: #4338CA;
    }
    .btn-danger {
      border-radius: 8px;
    }
    h2, h4 {
      font-weight: 600;
    }
    .navbar {
      margin-bottom: 2rem;
    }
  </style>
</head>
<body>


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
  <a class="btn btn-outline-dark ms-2" href="logout.php">Logout 🚪</a>
</li>
      </ul>
    </div>
  </div>
</nav>


<div class="container">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="card dashboard-card p-4">
        <h2 class="mb-4 text-center">Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</h2>

        <h4 class="mb-3">Available Chat Rooms</h4>
        <ul class="list-group mb-4">
          <?php while($room = $rooms->fetch_assoc()): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center chat-room-item">
              <?= htmlspecialchars($room['name']) ?>
              <a href="chatroom.php?room_id=<?= $room['id'] ?>" class="btn btn-sm btn-primary">Join</a>
            </li>
          <?php endwhile; ?>
        </ul>

        
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
