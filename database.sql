CREATE DATABASE ram_janmabhoomi;
USE ram_janmabhoomi;

-- Users Table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(15),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    role ENUM('user', 'admin') DEFAULT 'user',
    is_verified BOOLEAN DEFAULT FALSE
);

-- Darshan Tours Table
CREATE TABLE darshan_tours (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    timetable TEXT,
    base_price DECIMAL(10,2) NOT NULL,
    wheelchair_price DECIMAL(10,2) DEFAULT 100.00,
    assistant_price DECIMAL(10,2) DEFAULT 150.00,
    food_price DECIMAL(10,2) DEFAULT 75.00,
    status ENUM('active', 'inactive') DEFAULT 'active'
);

-- Available Dates Table
CREATE TABLE available_dates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tour_id INT,
    date DATE NOT NULL,
    slots_available INT DEFAULT 100,
    FOREIGN KEY (tour_id) REFERENCES darshan_tours(id)
);

-- Bookings Table
CREATE TABLE bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    tour_id INT,
    booking_date DATE NOT NULL,
    total_people INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_id VARCHAR(100),
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (tour_id) REFERENCES darshan_tours(id)
);

-- Visitors Table
CREATE TABLE visitors (
    id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT,
    full_name VARCHAR(100) NOT NULL,
    age INT NOT NULL,
    gender ENUM('male', 'female', 'other') NOT NULL,
    aadhar_number VARCHAR(12) NOT NULL,
    has_disability BOOLEAN DEFAULT FALSE,
    needs_wheelchair BOOLEAN DEFAULT FALSE,
    needs_assistant BOOLEAN DEFAULT FALSE,
    needs_food BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (booking_id) REFERENCES bookings(id)
);

-- Password Reset Tokens Table
CREATE TABLE password_resets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) NOT NULL,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    used BOOLEAN DEFAULT FALSE
);

-- Create table for email verification OTP
CREATE TABLE email_verification (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    email VARCHAR(100) NOT NULL,
    otp VARCHAR(6) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    is_used BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Update visitors table structure