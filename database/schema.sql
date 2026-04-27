-- Base de datos inicial para PAPM (Passport Pal Motril)

CREATE DATABASE IF NOT EXISTS papm_db;
USE papm_db;

-- Tabla de Usuarios
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    points INT DEFAULT 0,
    is_premium BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de Restaurantes/Establecimientos
CREATE TABLE IF NOT EXISTS restaurants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    address VARCHAR(255),
    lat DECIMAL(10, 8),
    lng DECIMAL(11, 8),
    category VARCHAR(50),
    rating DECIMAL(3, 1) DEFAULT 0.0
);

-- Tabla de Rutas Gastronómicas
CREATE TABLE IF NOT EXISTS routes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    reward_points INT DEFAULT 100,
    estimated_duration_mins INT DEFAULT 60,
    is_premium BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Relación: Restaurantes en cada Ruta (con su orden de visita)
CREATE TABLE IF NOT EXISTS route_restaurants (
    route_id INT,
    restaurant_id INT,
    order_num INT,
    PRIMARY KEY (route_id, restaurant_id),
    FOREIGN KEY (route_id) REFERENCES routes(id) ON DELETE CASCADE,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
);

-- Tabla de Cupones/Recompensas
CREATE TABLE IF NOT EXISTS coupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    description TEXT,
    points_cost INT NOT NULL,
    discount_code VARCHAR(50) NOT NULL,
    restaurant_id INT NULL, -- Puede ser un cupón general o para un restaurante específico
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE SET NULL
);

-- Tabla de Cupones canjeados por usuarios
CREATE TABLE IF NOT EXISTS user_coupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    coupon_id INT,
    redeemed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE CASCADE
);

-- Tabla de Rutas completadas por el usuario (para no dar puntos infinitos)
CREATE TABLE IF NOT EXISTS user_completed_routes (
    user_id INT,
    route_id INT,
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, route_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (route_id) REFERENCES routes(id) ON DELETE CASCADE
);

-- Insertar algunos datos de prueba
INSERT INTO restaurants (name, description, address, lat, lng, category) VALUES 
('Bar El Paso', 'Tapas tradicionales y ambiente local.', 'Plaza de España 1, Motril', 36.7423, -3.5186, 'Tapas'),
('Chiringuito Hoyo 19', 'Pescaito frito y vistas al mar.', 'Paseo Marítimo, Playa Granada, Motril', 36.7118, -3.5412, 'Marisco'),
('Restaurante Zarcillo', 'Cocina de autor y vinos.', 'Calle Nueva 5, Motril', 36.7445, -3.5170, 'Gourmet');

INSERT INTO routes (name, description, reward_points, is_premium) VALUES 
('Ruta Centro Histórico', 'Descubre los mejores bares de tapas en el centro de Motril.', 150, FALSE),
('Ruta del Pescador (Premium)', 'Disfruta del mejor marisco cerca de la costa con vistas exclusivas.', 250, TRUE);

INSERT INTO route_restaurants (route_id, restaurant_id, order_num) VALUES 
(1, 1, 1),
(1, 3, 2),
(2, 2, 1);

INSERT INTO coupons (title, description, points_cost, discount_code, restaurant_id) VALUES 
('Bebida Gratis', 'Consigue una bebida gratis con tu próxima tapa.', 100, 'BEBIDA100', 1),
('10% Descuento', '10% de descuento en tu cuenta total.', 250, 'ZARCILLO10', 3);
