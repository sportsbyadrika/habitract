CREATE TABLE suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    address TEXT NULL,
    phone VARCHAR(25) NULL,
    email VARCHAR(150) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT NULL,
    name VARCHAR(150) NOT NULL,
    mobile VARCHAR(20) NULL,
    address TEXT NULL,
    email VARCHAR(150) NULL,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('super_admin','supplier_admin','supplier_staff','supplier_driver') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_users_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    route VARCHAR(100) NULL,
    address TEXT NOT NULL,
    phone VARCHAR(20) NULL,
    email VARCHAR(150) NULL,
    supply_type VARCHAR(50) NULL,
    frequency VARCHAR(50) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_customers_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT NOT NULL,
    customer_id INT NOT NULL,
    driver_id INT NOT NULL,
    supply_type VARCHAR(50) NOT NULL,
    scheduled_date DATE NOT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_schedules_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE,
    CONSTRAINT fk_schedules_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    CONSTRAINT fk_schedules_driver FOREIGN KEY (driver_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO suppliers (name, address, phone, email) VALUES
('Sunrise Supplies', '1 Supply Street, City Center', '+91 98765 43210', 'info@sunrisesupplies.test');

INSERT INTO users (supplier_id, name, mobile, address, email, username, password_hash, role) VALUES
(NULL, 'System Super Admin', '+91 90000 00000', 'HQ Address', 'super@habitract.test', 'superadmin', '$2y$12$8dAg32gXsVkvKwiik3Z25OkxtoEIwFdQ1SYmJ0ExVsy0z1edEEvVO', 'super_admin'),
(1, 'Supplier Admin', '+91 98888 88888', 'Branch Address', 'admin@sunrise.test', 'supplieradmin', '$2y$12$cXhAFuwIV1DzqnpMAsRzke3AEtApNcEbrqoVLK1e19EilSIks3zEi', 'supplier_admin');
