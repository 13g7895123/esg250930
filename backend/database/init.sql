-- Create database if not exists
CREATE DATABASE IF NOT EXISTS esg_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Use the database
USE esg_db;

-- Create companies table
CREATE TABLE IF NOT EXISTS companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    address TEXT,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data
INSERT INTO companies (name, email, phone, address, created_at, updated_at) VALUES
('台積電股份有限公司', 'contact@tsmc.com', '03-5636688', '新竹市東區力行六路8號', NOW(), NOW()),
('鴻海精密工業股份有限公司', 'info@foxconn.com', '02-22682388', '新北市土城區自由街2號', NOW(), NOW()),
('台塑企業股份有限公司', 'service@fpg.com.tw', '02-27121212', '台北市松山區敦化北路201號', NOW(), NOW());

-- Create templates table (legacy)
CREATE TABLE IF NOT EXISTS templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    version VARCHAR(100) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample template data
INSERT INTO templates (name, version, created_at, updated_at) VALUES
('基礎風險評估', 'v1.0111', NOW(), NOW()),
('進階風險評估', 'v2.0222', NOW(), NOW());

-- Create risk_assessment_templates table (new structure)
CREATE TABLE IF NOT EXISTS risk_assessment_templates (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '範本ID（主鍵）',
    version_name VARCHAR(255) NOT NULL COMMENT '版本名稱',
    description TEXT NULL COMMENT '範本描述',
    status ENUM('active', 'inactive', 'draft') NOT NULL DEFAULT 'active' COMMENT '狀態',
    copied_from BIGINT(20) UNSIGNED NULL COMMENT '複製來源範本ID',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '建立時間',
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新時間',
    PRIMARY KEY (id),
    KEY idx_status (status),
    KEY idx_created_at (created_at),
    FOREIGN KEY (copied_from) REFERENCES risk_assessment_templates(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='風險評估範本表 - 存儲風險評估的版本範本資訊';

-- Insert sample risk assessment template data
INSERT INTO risk_assessment_templates (version_name, description, status, created_at, updated_at) VALUES
('基礎風險評估範本 v1.0', '適用於中小企業的基礎風險評估範本', 'active', NOW(), NOW()),
('進階風險評估範本 v2.0', '適用於大型企業的進階風險評估範本', 'active', NOW(), NOW()),
('草稿範本 v0.1', '測試用的草稿範本', 'draft', NOW(), NOW());

-- Create template contents table
CREATE TABLE IF NOT EXISTS template_contents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    template_id INT NOT NULL,
    category_id INT,
    topic VARCHAR(255) NOT NULL,
    content TEXT,
    sort_order INT DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (template_id) REFERENCES templates(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create risk categories table
CREATE TABLE IF NOT EXISTS risk_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    sort_order INT DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample risk categories
INSERT INTO risk_categories (name, sort_order, created_at, updated_at) VALUES
('環境風險', 1, NOW(), NOW()),
('社會風險', 2, NOW(), NOW()),
('治理風險', 3, NOW(), NOW()),
('營運風險', 4, NOW(), NOW());

-- Create question management table
CREATE TABLE IF NOT EXISTS question_managements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    template_id INT NOT NULL,
    year VARCHAR(10) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (template_id) REFERENCES templates(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample question management data
INSERT INTO question_managements (company_id, template_id, year, created_at, updated_at) VALUES
(1, 1, '2024', NOW(), NOW()),
(1, 2, '2024', NOW(), NOW()),
(2, 1, '2024', NOW(), NOW());

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'manager', 'user') DEFAULT 'user',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample users
INSERT INTO users (company_id, name, email, password, role, created_at, updated_at) VALUES
(1, '張三', 'zhang@tsmc.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager', NOW(), NOW()),
(1, '李四', 'li@tsmc.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', NOW(), NOW()),
(2, '王五', 'wang@foxconn.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager', NOW(), NOW());

-- Create NAS sync logs table
CREATE TABLE IF NOT EXISTS nas_sync_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sync_type ENUM('full', 'folder', 'test') DEFAULT 'full',
    folder_name VARCHAR(255) NULL,
    folder_path TEXT NULL,
    file_count INT UNSIGNED DEFAULT 0,
    total_size BIGINT UNSIGNED DEFAULT 0,
    status ENUM('pending', 'running', 'completed', 'failed') DEFAULT 'pending',
    error_message TEXT NULL,
    external_api_response JSON NULL,
    sync_started_at DATETIME NULL,
    sync_completed_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_sync_type (sync_type),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;