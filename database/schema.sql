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
    is_admin BOOLEAN DEFAULT FALSE,
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

-- Tabla de Rutas activas/en progreso por el usuario
CREATE TABLE IF NOT EXISTS user_active_routes (
    user_id INT,
    route_id INT,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, route_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (route_id) REFERENCES routes(id) ON DELETE CASCADE
);

-- Insertar algunos datos de prueba
INSERT INTO restaurants (name, description, address, lat, lng, category) VALUES 
('Bar El Paso', 'Tapas tradicionales y ambiente local.', 'Plaza de España 1, Motril', 36.7423, -3.5186, 'Tapas'),
('Chiringuito Hoyo 19', 'Pescaito frito y vistas al mar.', 'Paseo Marítimo, Playa Granada, Motril', 36.7118, -3.5412, 'Marisco'),
('Restaurante Zarcillo', 'Cocina de autor y vinos.', 'Calle Nueva 5, Motril', 36.7445, -3.5170, 'Gourmet'),
('Café Paradiso', 'Café con terraza y pasteles caseros.', 'Plaza de la Constitución 8, Motril', 36.7452, -3.5168, 'Café'),
('La Bodega del Mar', 'Vinos locales y tapas de marisco.', 'Calle Real 15, Motril', 36.7438, -3.5175, 'Tapas'),
('Heladería La Tropical', 'Helados artesanales con frutas tropicales.', 'Paseo de los Álamos 22, Motril', 36.7405, -3.5192, 'Postres'),
('Restaurante El Faro', 'Cocina mediterránea con vistas al mar.', 'Avenida del Mediterráneo 45, Motril', 36.7150, -3.5350, 'Mediterránea'),
('Bar La Plaza', 'Cervezas artesanales y raciones.', 'Plaza de la Aurora 3, Motril', 36.7460, -3.5160, 'Tapas'),
('Pizzeria Bella Italia', 'Pizzas al estilo italiano con ingredientes locales.', 'Calle de los Naranjos 12, Motril', 36.7415, -3.5188, 'Italiana'),
('Churrería Los Ángeles', 'Churros y chocolate caliente tradicionales.', 'Mercado Municipal, Motril', 36.7440, -3.5178, 'Postres');

INSERT INTO routes (name, description, reward_points, is_premium) VALUES 
('Ruta del Pescador (Premium)', 'Disfruta del mejor marisco en los chiringuitos de la costa de Motril.', 250, TRUE),
('Ruta Dulce Motril', 'Recorre las mejores heladerías y pastelerías de Motril.', 120, FALSE);

INSERT INTO route_restaurants (route_id, restaurant_id, order_num) VALUES 
(2, 2, 1), -- Ruta Pescador: Chiringuito Hoyo 19
(2, 7, 2), -- Restaurante El Faro
(3, 4, 1), -- Ruta Dulce: Café Paradiso
(3, 6, 2), -- Heladería La Tropical
(3, 10, 3); -- Churrería Los Ángeles

INSERT INTO coupons (title, description, points_cost, discount_code, restaurant_id) VALUES 
('Bebida Gratis', 'Consigue una bebida gratis con tu próxima tapa.', 100, 'BEBIDA100', 1),
('10% Descuento', '10% de descuento en tu cuenta total.', 250, 'ZARCILLO10', 3);
