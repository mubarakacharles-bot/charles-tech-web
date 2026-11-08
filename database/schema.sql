CREATE DATABASE IF NOT EXISTS charles_tech
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE charles_tech;

CREATE TABLE IF NOT EXISTS users (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  email VARCHAR(254) NOT NULL,
  name VARCHAR(150) NOT NULL,
  phone VARCHAR(40) NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('customer', 'admin', 'student') NOT NULL DEFAULT 'customer',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS auth_login_attempts (
  attempt_key CHAR(64) NOT NULL,
  failed_attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
  locked_until DATETIME NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (attempt_key),
  KEY idx_auth_login_attempts_updated (updated_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS products (
  id VARCHAR(32) NOT NULL,
  name VARCHAR(200) NOT NULL,
  category VARCHAR(120) NOT NULL,
  description TEXT NOT NULL,
  icon VARCHAR(32) NOT NULL DEFAULT '',
  price DECIMAL(12,2) UNSIGNED NOT NULL,
  specs JSON NOT NULL,
  image_paths JSON NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_products_category_active (category, is_active),
  KEY idx_products_active_sort (is_active, sort_order)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS product_variants (
  variant_id VARCHAR(40) NOT NULL,
  product_id VARCHAR(32) NOT NULL,
  label VARCHAR(100) NOT NULL,
  sku VARCHAR(100) NOT NULL,
  stock INT UNSIGNED NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (product_id, variant_id),
  UNIQUE KEY uq_product_variants_sku (sku),
  KEY idx_product_variants_active (product_id, is_active),
  CONSTRAINT fk_product_variants_product
    FOREIGN KEY (product_id) REFERENCES products (id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS orders (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  order_number VARCHAR(32) NOT NULL,
  user_id BIGINT UNSIGNED NULL,
  customer_email VARCHAR(254) NULL,
  customer_name VARCHAR(150) NOT NULL,
  customer_phone VARCHAR(40) NOT NULL,
  delivery_address TEXT NOT NULL,
  payment_method VARCHAR(60) NOT NULL,
  transaction_reference VARCHAR(120) NULL,
  payment_status ENUM('unpaid', 'pending', 'paid', 'failed', 'refunded') NOT NULL DEFAULT 'pending',
  status ENUM('pending', 'processing', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
  currency CHAR(3) NOT NULL DEFAULT 'RWF',
  total DECIMAL(12,2) UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_orders_order_number (order_number),
  KEY idx_orders_user_created (user_id, created_at),
  KEY idx_orders_status_created (status, created_at),
  CONSTRAINT fk_orders_user
    FOREIGN KEY (user_id) REFERENCES users (id)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS order_items (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  order_id BIGINT UNSIGNED NOT NULL,
  product_id VARCHAR(32) NOT NULL,
  variant_id VARCHAR(40) NOT NULL,
  product_name VARCHAR(200) NOT NULL,
  variant_label VARCHAR(100) NOT NULL,
  sku VARCHAR(100) NOT NULL,
  unit_price DECIMAL(12,2) UNSIGNED NOT NULL,
  quantity INT UNSIGNED NOT NULL,
  line_total DECIMAL(12,2) UNSIGNED NOT NULL,
  PRIMARY KEY (id),
  KEY idx_order_items_order (order_id),
  KEY idx_order_items_product (product_id),
  CONSTRAINT fk_order_items_order
    FOREIGN KEY (order_id) REFERENCES orders (id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_order_items_variant
    FOREIGN KEY (product_id, variant_id) REFERENCES product_variants (product_id, variant_id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS inventory_movements (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  product_id VARCHAR(32) NOT NULL,
  variant_id VARCHAR(40) NOT NULL,
  quantity_delta INT NOT NULL,
  reason VARCHAR(200) NOT NULL,
  order_id BIGINT UNSIGNED NULL,
  performed_by BIGINT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_inventory_movements_variant_created (product_id, variant_id, created_at),
  KEY idx_inventory_movements_order (order_id),
  CONSTRAINT fk_inventory_movements_variant
    FOREIGN KEY (product_id, variant_id) REFERENCES product_variants (product_id, variant_id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_inventory_movements_order
    FOREIGN KEY (order_id) REFERENCES orders (id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT fk_inventory_movements_user
    FOREIGN KEY (performed_by) REFERENCES users (id)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS business_settings (
  setting_key VARCHAR(100) NOT NULL,
  value_json JSON NOT NULL,
  updated_by BIGINT UNSIGNED NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (setting_key),
  CONSTRAINT fk_business_settings_user
    FOREIGN KEY (updated_by) REFERENCES users (id)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;
