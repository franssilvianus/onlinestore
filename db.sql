-- Import file ini di phpMyAdmin (XAMPP)
CREATE DATABASE IF NOT EXISTS simple_store CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE simple_store;

CREATE TABLE IF NOT EXISTS sellers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  owner_name VARCHAR(120) NULL,
  phone VARCHAR(50) NULL,
  email VARCHAR(120) NULL,
  address TEXT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  description TEXT,
  price INT NOT NULL,
  image_path VARCHAR(255),
  sizes VARCHAR(120) DEFAULT 'S,M,L,XL',
  seller_id INT NULL DEFAULT NULL,
  is_best_seller TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_products_seller_id (seller_id),
  CONSTRAINT fk_products_seller FOREIGN KEY (seller_id) REFERENCES sellers(id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO products (name, description, price, image_path, sizes) VALUES
('Kaos Polos', 'Kaos cotton combed 30s, adem dan nyaman.', 75000, NULL, 'S,M,L,XL'),
('Kemeja Flanel', 'Cocok buat nongkrong dan kerja santai.', 150000, NULL, 'M,L,XL'),
('Hoodie Basic', 'Nyaman buat cuaca adem, bahan fleece.', 200000, NULL, 'M,L,XL,XXL');
