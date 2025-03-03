CREATE DATABASE income_codejoki_codetech;

USE income_codejoki_codetech;

-- Tabel Users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

-- Tabel Transactions
CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('income', 'expense') NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    description TEXT,
    date DATE NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

ALTER TABLE transactions
ADD COLUMN net_amount INT NOT NULL AFTER amount,
ADD COLUMN worker_amount INT DEFAULT 0 AFTER net_amount;

ALTER TABLE transactions
ADD COLUMN company VARCHAR(255) NOT NULL AFTER type;


ALTER TABLE transactions 
ADD COLUMN company VARCHAR(255) NOT NULL AFTER type,
ADD COLUMN net_amount DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER amount,
ADD COLUMN worker_amount DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER net_amount;


ALTER TABLE transactions ADD INDEX idx_date (date);


CREATE TABLE `transactions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `type` ENUM('income', 'expense') NOT NULL,
    `company` VARCHAR(255) NOT NULL,
    `amount` DECIMAL(10, 2) NOT NULL,
    `net_amount` DECIMAL(10, 2) DEFAULT 0,
    `worker_amount` DECIMAL(10, 2) DEFAULT 0,
    `description` TEXT,
    `date` DATE NOT NULL,
    `category_id` INT DEFAULT NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`),
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`)
);
