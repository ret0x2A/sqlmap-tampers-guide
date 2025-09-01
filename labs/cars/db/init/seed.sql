CREATE DATABASE IF NOT EXISTS car_catalog;
USE car_catalog;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  display_name VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS cars (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  make VARCHAR(255),
  model VARCHAR(255),
  year INT,
  description TEXT,
  photo_url VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS refresh_tokens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  token VARCHAR(255) NOT NULL,
  expires_at DATETIME NOT NULL,
  revoked TINYINT(1) DEFAULT 0,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- users: password hashes correspond to 'password1'..'password10' (bcrypt with same salt here for demo)
INSERT INTO users (username, password_hash, display_name) VALUES
('user1','$2b$10$k4ts4zjQwLrC9LRx6hM.CuQznV9dOJ2VZZuhzUKZVye8sQnUnxG2u','User One'),
('user2','$2b$10$k4ts4zjQwLrC9LRx6hM.CuQznV9dOJ2VZZuhzUKZVye8sQnUnxG2u','User Two'),
('user3','$2b$10$k4ts4zjQwLrC9LRx6hM.CuQznV9dOJ2VZZuhzUKZVye8sQnUnxG2u','User Three'),
('user4','$2b$10$k4ts4zjQwLrC9LRx6hM.CuQznV9dOJ2VZZuhzUKZVye8sQnUnxG2u','User Four'),
('user5','$2b$10$k4ts4zjQwLrC9LRx6hM.CuQznV9dOJ2VZZuhzUKZVye8sQnUnxG2u','User Five'),
('user6','$2b$10$k4ts4zjQwLrC9LRx6hM.CuQznV9dOJ2VZZuhzUKZVye8sQnUnxG2u','User Six'),
('user7','$2b$10$k4ts4zjQwLrC9LRx6hM.CuQznV9dOJ2VZZuhzUKZVye8sQnUnxG2u','User Seven'),
('user8','$2b$10$k4ts4zjQwLrC9LRx6hM.CuQznV9dOJ2VZZuhzUKZVye8sQnUnxG2u','User Eight'),
('user9','$2b$10$k4ts4zjQwLrC9LRx6hM.CuQznV9dOJ2VZZuhzUKZVye8sQnUnxG2u','User Nine'),
('user10','$2b$10$k4ts4zjQwLrC9LRx6hM.CuQznV9dOJ2VZZuhzUKZVye8sQnUnxG2u','User Ten');

INSERT INTO cars (name, make, model, year, description, photo_url) VALUES
('Mustang GT','Ford','GT',2018,'Красивый спорткар','/img/mustang.svg'),
('Civic','Honda','Civic',2019,'Надёжный седан','/img/civic.svg'),
('Corolla','Toyota','Corolla',2020,'Экономичный авто','/img/corolla.svg'),
('Camaro','Chevrolet','Camaro',2017,'Мускул кар','/img/camaro.svg'),
('X5','BMW','X5',2021,'SUV премиум','/img/x5.svg'),
('A4','Audi','A4',2016,'Бизнес седан','/img/a4.svg'),
('Model 3','Tesla','Model 3',2022,'Электрокар','/img/model3.svg'),
('Impreza','Subaru','Impreza',2015,'AWD седан','/img/impreza.svg'),
('Polo','Volkswagen','Polo',2019,'Городской авто','/img/polo.svg'),
('Rio','Kia','Rio',2018,'Компактный авто','/img/rio.svg');
