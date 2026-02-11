-- Hapus database jika sudah ada agar bisa dibuat ulang dari awal
DROP DATABASE IF EXISTS rentease;

-- Buat database baru
CREATE DATABASE rentease;

-- Gunakan database
USE rentease;

-- Tabel untuk pengguna (users dan admin)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    profile_picture VARCHAR(255) DEFAULT 'default.jpg',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel untuk properti (kos)
CREATE TABLE properties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    location VARCHAR(255) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    total_rooms INT NOT NULL,
    available_rooms INT NOT NULL,
    description TEXT,
    image_url VARCHAR(255) DEFAULT 'default.jpg',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabel untuk pemesanan (bookings)
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    property_id INT NOT NULL,
    booking_date DATE NOT NULL,
    payment_method VARCHAR(50),
    payment_proof_url VARCHAR(255),
    total_amount DECIMAL(10, 2),
    status ENUM('pending', 'confirmed', 'rejected') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
);

-- Tabel untuk pesan
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message_content TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert satu admin default untuk memulai
INSERT INTO users (name, email, password, role) VALUES 
('Admin Rentease', 'admin@rentease.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'); -- password = 'password'
