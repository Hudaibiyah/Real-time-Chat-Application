<?php
include "db.php";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $u = trim($_POST["username"]);
    $e = trim($_POST["email"]);
    $p = password_hash($_POST["password"], PASSWORD_BCRYPT);
    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $u, $e, $p);
    if ($stmt->execute()) {
        header("Location: login.php");
        exit();
    } else {
        $error = "Registration failed: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Register | Chat App</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #f0f4ff,rgb(222, 224, 229));
      font-family: 'Inter', sans-serif;
    }
    .card {
      border: none;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      border-radius: 16px;
    }
    .form-control {
      border-radius: 10px;
    }
    .btn-primary {
      border-radius: 10px;
      background-color: #4F46E5;
      border: none;
    }
    .btn-primary:hover {
      background-color: #4338CA;
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
      
      
      </ul>
    </div>
  </div>
</nav>
</head>
<body>
<div class="container d-flex align-items-center justify-content-center min-vh-100">
  <div class="col-md-5">
    <div class="card p-4">
      <h2 class="text-center mb-4 fw-semibold">Create Account</h2>
      <form method="POST">
        <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
        <div class="mb-3">
          <label class="form-label">Username</label>
          <input name="username" class="form-control" placeholder="Your name" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input name="email" type="email" class="form-control" placeholder="you@example.com" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input name="password" type="password" class="form-control" placeholder="Create password" required>
        </div>
        <button class="btn btn-primary w-100">Register</button>
        <p class="text-center mt-3 mb-0">Already have an account? <a href="login.php">Login</a></p>
      </form>
    </div>
  </div>
</div>
</body>
</html>
