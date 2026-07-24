DROP DATABASE IF EXISTS smart_mess;
CREATE DATABASE smart_mess;
USE smart_mess;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    phone VARCHAR(20) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    duration_days INT NOT NULL,
    description TEXT
);

CREATE TABLE menu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    day_of_week VARCHAR(15) NOT NULL,
    meal_type ENUM('breakfast', 'lunch', 'dinner') NOT NULL,
    items TEXT NOT NULL,
    image_url VARCHAR(255) DEFAULT 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=500&q=80'
);

CREATE TABLE subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    plan_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('pending', 'active', 'expired', 'cancelled') DEFAULT 'pending',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES plans(id) ON DELETE CASCADE
);

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    date DATE NOT NULL,
    meal_type ENUM('breakfast', 'lunch', 'dinner') NOT NULL,
    status ENUM('placed', 'cancelled', 'delivered') DEFAULT 'placed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE meal_pauses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    pause_date DATE NOT NULL,
    meal_type ENUM('breakfast', 'lunch', 'dinner') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE polls (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question VARCHAR(255) NOT NULL,
    option_1 VARCHAR(100) NOT NULL,
    option_2 VARCHAR(100) NOT NULL,
    poll_date DATE NOT NULL,
    active BOOLEAN DEFAULT TRUE
);

CREATE TABLE poll_votes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    poll_id INT NOT NULL,
    choice INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (poll_id) REFERENCES polls(id) ON DELETE CASCADE,
    UNIQUE KEY (user_id, poll_id)
);

-- Dummy Admin
INSERT INTO users (id, name, email, password, role) VALUES (1, 'Admin (Boss)', 'admin@smartmess.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'); 

-- Plans
INSERT INTO plans (name, price, duration_days, description) VALUES 
('Fun Weekly Plan', 700.00, 7, '7 days of super yummy lunch and dinner!'),
('Magic Monthly Plan', 2500.00, 30, '30 days of standard meals with weekend ice cream!');

-- Menu
INSERT INTO menu (day_of_week, meal_type, items, image_url) VALUES 
('Monday', 'lunch', 'Rainbow Rice, Dal, Veggies, Chapati', 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=500&q=80'),
('Monday', 'dinner', 'Buttery Paneer, Magic Naan', 'https://images.unsplash.com/photo-1589302168068-964664d93dc0?w=500&q=80'),
('Tuesday', 'breakfast', 'Smiley Face Pancakes', 'https://images.unsplash.com/photo-1528207776546-3221b2bb20d6?w=500&q=80'),
('Tuesday', 'lunch', 'Rajma Chawal (Red Beans)', 'https://images.unsplash.com/photo-1512152272829-410aabe2ba33?w=500&q=80');
