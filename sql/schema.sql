CREATE DATABASE IF NOT EXISTS ip_inventory CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ip_inventory;

CREATE TABLE IF NOT EXISTS ip_addresses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ip VARCHAR(45) NOT NULL,
    hostname VARCHAR(255) DEFAULT NULL,
    location VARCHAR(50) NOT NULL,
    zone VARCHAR(50) DEFAULT NULL,
    vlan VARCHAR(20) NOT NULL,
    device VARCHAR(100) DEFAULT NULL,
    os VARCHAR(100) DEFAULT NULL,
    status ENUM('Used', 'Free', 'Reserved', 'Static') NOT NULL DEFAULT 'Used',
    owner VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_ip_scope (ip, location, zone, vlan),
    KEY idx_status (status),
    KEY idx_location (location)
);
