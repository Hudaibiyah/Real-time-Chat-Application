CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100),
  email VARCHAR(150) UNIQUE,
  password VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE chat_rooms (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) UNIQUE,
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
INSERT INTO chat_rooms (name,description) VALUES
('General','General chat'),('Tech','Tech talk'),('Sports','Sports room');
CREATE TABLE messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  room_id INT DEFAULT 1,
  user_id INT,
  message_text TEXT,
  timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (room_id) REFERENCES chat_rooms(id),
  FOREIGN KEY (user_id) REFERENCES users(id)
);
