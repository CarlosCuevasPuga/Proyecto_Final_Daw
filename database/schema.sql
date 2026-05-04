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
    restaurant_id INT NULL,
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

-- Tabla de Rutas completadas por el usuario
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

-- =====================================================
-- DATOS DE PRUEBA
-- =====================================================

INSERT INTO restaurants (name, description, address, lat, lng, category) VALUES 
('Bar El Paso',          'Tapas tradicionales y ambiente local.',          'Plaza de España 1, Motril',               36.74230000, -3.51860000, 'Tapas'),
('Chiringuito Hoyo 19',  'Pescaito frito y vistas al mar.',                'Paseo Marítimo, Playa Granada, Motril',    36.71180000, -3.54120000, 'Marisco'),
('Restaurante Zarcillo', 'Cocina de autor y vinos.',                       'Calle Nueva 5, Motril',                   36.74450000, -3.51700000, 'Gourmet'),
('Café Paradiso',        'Café con terraza y pasteles caseros.',           'Plaza de la Constitución 8, Motril',      36.74520000, -3.51680000, 'Café'),
('La Bodega del Mar',    'Vinos locales y tapas de marisco.',              'Calle Real 15, Motril',                   36.74380000, -3.51750000, 'Tapas'),
('Heladería La Tropical','Helados artesanales con frutas tropicales.',     'Paseo de los Álamos 22, Motril',          36.74050000, -3.51920000, 'Postres'),
('Restaurante El Faro',  'Cocina mediterránea con vistas al mar.',         'Avenida del Mediterráneo 45, Motril',     36.71500000, -3.53500000, 'Mediterránea'),
('Bar La Plaza',         'Cervezas artesanales y raciones.',               'Plaza de la Aurora 3, Motril',            36.74600000, -3.51600000, 'Tapas'),
('Pizzeria Bella Italia','Pizzas al estilo italiano con ingredientes locales.','Calle de los Naranjos 12, Motril',   36.74150000, -3.51880000, 'Italiana'),
('Churrería Los Ángeles','Churros y chocolate caliente tradicionales.',    'Mercado Municipal, Motril',               36.74400000, -3.51780000, 'Postres');

-- Rutas: se insertarán con IDs 1 y 2 (auto_increment desde 1)
INSERT INTO routes (name, description, reward_points, estimated_duration_mins, is_premium) VALUES 
('Ruta del Pescador (Premium)', 'Disfruta del mejor marisco en los chiringuitos de la costa de Motril.', 250, 90, TRUE),   -- ID 1
('Ruta Dulce Motril',           'Recorre las mejores heladerías y pastelerías de Motril.',               120, 60, FALSE);  -- ID 2

-- CORRECCIÓN: route_id 1 = Ruta del Pescador, route_id 2 = Ruta Dulce Motril
INSERT INTO route_restaurants (route_id, restaurant_id, order_num) VALUES 
(1, 2,  1),  -- Ruta Pescador:   Chiringuito Hoyo 19
(1, 7,  2),  -- Ruta Pescador:   Restaurante El Faro
(2, 4,  1),  -- Ruta Dulce:      Café Paradiso
(2, 6,  2),  -- Ruta Dulce:      Heladería La Tropical
(2, 10, 3);  -- Ruta Dulce:      Churrería Los Ángeles

INSERT INTO coupons (title, description, points_cost, discount_code, restaurant_id) VALUES 
('Bebida Gratis',  'Consigue una bebida gratis con tu próxima tapa.', 100, 'BEBIDA100',   1),
('10% Descuento',  '10% de descuento en tu cuenta total.',            250, 'ZARCILLO10',  3);
