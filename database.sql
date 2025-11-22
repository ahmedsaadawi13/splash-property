-- FILE: /database.sql
-- SplashProperty - Multi-tenant Real Estate Management SaaS
-- Complete Database Schema

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS splashproperty CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE splashproperty;

-- =====================================================
-- SUBSCRIPTION & PLANS
-- =====================================================

CREATE TABLE plans (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(50) NOT NULL UNIQUE,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    billing_cycle ENUM('monthly', 'yearly') NOT NULL DEFAULT 'monthly',
    max_units INT UNSIGNED NOT NULL DEFAULT 0,
    max_customers INT UNSIGNED NOT NULL DEFAULT 0,
    max_active_contracts INT UNSIGNED NOT NULL DEFAULT 0,
    max_users INT UNSIGNED NOT NULL DEFAULT 0,
    max_storage_size BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'in bytes',
    max_api_calls_per_month INT UNSIGNED NOT NULL DEFAULT 0,
    features JSON COMMENT 'customer_portal, advanced_reports, payment_reminders, api_access',
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TENANTS
-- =====================================================

CREATE TABLE tenants (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    code VARCHAR(50) NOT NULL UNIQUE,
    logo_path VARCHAR(500),
    primary_contact_name VARCHAR(200),
    primary_contact_email VARCHAR(200),
    phone VARCHAR(50),
    address TEXT,
    default_currency VARCHAR(3) NOT NULL DEFAULT 'SAR',
    default_country VARCHAR(100),
    default_timezone VARCHAR(50) DEFAULT 'UTC',
    settings JSON COMMENT 'tax_percentage, contract_type, payment_terms, etc.',
    status ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_code (code),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TENANT SUBSCRIPTIONS
-- =====================================================

CREATE TABLE tenant_subscriptions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    plan_id INT UNSIGNED NOT NULL,
    status ENUM('trialing', 'active', 'past_due', 'canceled') NOT NULL DEFAULT 'trialing',
    start_date DATE NOT NULL,
    end_date DATE,
    renewal_date DATE,
    current_units_count INT UNSIGNED NOT NULL DEFAULT 0,
    current_customers_count INT UNSIGNED NOT NULL DEFAULT 0,
    current_active_contracts_count INT UNSIGNED NOT NULL DEFAULT 0,
    current_users_count INT UNSIGNED NOT NULL DEFAULT 0,
    storage_used_bytes BIGINT UNSIGNED NOT NULL DEFAULT 0,
    api_calls_count_current_period INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES plans(id),
    INDEX idx_tenant_status (tenant_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- INVOICES (SaaS billing)
-- =====================================================

CREATE TABLE invoices (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    invoice_number VARCHAR(50) NOT NULL UNIQUE,
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) NOT NULL DEFAULT 'SAR',
    due_date DATE NOT NULL,
    status ENUM('unpaid', 'paid', 'overdue') NOT NULL DEFAULT 'unpaid',
    description TEXT,
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    INDEX idx_tenant_status (tenant_id, status),
    INDEX idx_due_date (due_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- INVOICE PAYMENTS (SaaS billing)
-- =====================================================

CREATE TABLE invoice_payments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    invoice_id INT UNSIGNED NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50),
    transaction_reference VARCHAR(200),
    paid_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
    INDEX idx_tenant (tenant_id),
    INDEX idx_invoice (invoice_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- USERS
-- =====================================================

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NULL COMMENT 'NULL for platform_admin',
    name VARCHAR(200) NOT NULL,
    email VARCHAR(200) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('platform_admin', 'tenant_admin', 'sales_manager', 'sales_agent', 'contract_manager', 'accountant', 'customer_support', 'viewer') NOT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    phone VARCHAR(50),
    avatar_path VARCHAR(500),
    last_login_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_tenant_email (tenant_id, email),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    INDEX idx_tenant_status (tenant_id, status),
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- LOGIN ATTEMPTS
-- =====================================================

CREATE TABLE login_attempts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(200) NOT NULL,
    ip_address VARCHAR(45),
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email_time (email, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- PROJECTS
-- =====================================================

CREATE TABLE projects (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    name VARCHAR(200) NOT NULL,
    code VARCHAR(50) NOT NULL,
    location VARCHAR(500),
    city VARCHAR(100),
    country VARCHAR(100),
    description TEXT,
    project_type ENUM('compound', 'tower', 'building', 'land', 'mixed') NOT NULL DEFAULT 'building',
    status ENUM('active', 'completed', 'on_hold') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    UNIQUE KEY unique_tenant_code (tenant_id, code),
    INDEX idx_tenant_status (tenant_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- PROJECT BLOCKS
-- =====================================================

CREATE TABLE project_blocks (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    project_id INT UNSIGNED NOT NULL,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    INDEX idx_tenant_project (tenant_id, project_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- FEATURES
-- =====================================================

CREATE TABLE features (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(50) NOT NULL,
    category VARCHAR(50) COMMENT 'amenity, security, utilities, etc.',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    UNIQUE KEY unique_tenant_code (tenant_id, code),
    INDEX idx_tenant (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- UNITS
-- =====================================================

CREATE TABLE units (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    project_id INT UNSIGNED NULL,
    block_id INT UNSIGNED NULL,
    unit_code VARCHAR(100) NOT NULL,
    unit_number VARCHAR(50),
    unit_type ENUM('apartment', 'villa', 'office', 'shop', 'land', 'warehouse', 'other') NOT NULL,
    floor_number INT,
    bedrooms INT UNSIGNED DEFAULT 0,
    bathrooms INT UNSIGNED DEFAULT 0,
    area_size DECIMAL(10,2),
    area_unit VARCHAR(20) DEFAULT 'sqm',
    view VARCHAR(100) COMMENT 'street, garden, sea, etc.',
    furnishing_status ENUM('unfurnished', 'semi_furnished', 'furnished') DEFAULT 'unfurnished',
    listing_type ENUM('sale', 'rent', 'both') NOT NULL DEFAULT 'sale',
    sale_price DECIMAL(12,2) DEFAULT 0,
    rent_price DECIMAL(12,2) DEFAULT 0,
    currency VARCHAR(3) NOT NULL DEFAULT 'SAR',
    status ENUM('available', 'reserved', 'booked', 'under_contract', 'sold', 'rented', 'blocked') NOT NULL DEFAULT 'available',
    availability_date DATE,
    description TEXT,
    main_image_path VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL,
    FOREIGN KEY (block_id) REFERENCES project_blocks(id) ON DELETE SET NULL,
    UNIQUE KEY unique_tenant_code (tenant_id, unit_code),
    INDEX idx_tenant_status (tenant_id, status),
    INDEX idx_tenant_type (tenant_id, unit_type),
    INDEX idx_listing_type (listing_type),
    INDEX idx_sale_price (sale_price),
    INDEX idx_rent_price (rent_price)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- UNIT IMAGES
-- =====================================================

CREATE TABLE unit_images (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    unit_id INT UNSIGNED NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    original_name VARCHAR(255),
    sort_order INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE,
    INDEX idx_tenant_unit (tenant_id, unit_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- UNIT FEATURES (pivot)
-- =====================================================

CREATE TABLE unit_features (
    unit_id INT UNSIGNED NOT NULL,
    feature_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (unit_id, feature_id),
    FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE,
    FOREIGN KEY (feature_id) REFERENCES features(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- CUSTOMERS
-- =====================================================

CREATE TABLE customers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    customer_code VARCHAR(100) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100),
    full_name VARCHAR(200) GENERATED ALWAYS AS (CONCAT(first_name, ' ', COALESCE(last_name, ''))) STORED,
    email VARCHAR(200),
    phone VARCHAR(50),
    secondary_phone VARCHAR(50),
    type ENUM('lead', 'buyer', 'tenant', 'owner', 'investor') NOT NULL DEFAULT 'lead',
    preferred_contact_channel VARCHAR(50) COMMENT 'phone, email, whatsapp',
    preferred_city VARCHAR(100),
    preferred_unit_type VARCHAR(50),
    preferred_budget_min DECIMAL(12,2),
    preferred_budget_max DECIMAL(12,2),
    source VARCHAR(100) COMMENT 'walk_in, website, social_media, referral, broker',
    assigned_to_user_id INT UNSIGNED NULL,
    status ENUM('new', 'contacted', 'qualified', 'not_interested', 'active_client') NOT NULL DEFAULT 'new',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to_user_id) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_tenant_code (tenant_id, customer_code),
    INDEX idx_tenant_status (tenant_id, status),
    INDEX idx_tenant_type (tenant_id, type),
    INDEX idx_assigned (assigned_to_user_id),
    INDEX idx_email (email),
    INDEX idx_phone (phone)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- CUSTOMER USERS (for portal access)
-- =====================================================

CREATE TABLE customer_users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    customer_id INT UNSIGNED NOT NULL,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(200) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    is_portal_enabled TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    UNIQUE KEY unique_tenant_email (tenant_id, email),
    INDEX idx_tenant_customer (tenant_id, customer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- CUSTOMER DOCUMENTS
-- =====================================================

CREATE TABLE customer_documents (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    customer_id INT UNSIGNED NOT NULL,
    type VARCHAR(100) COMMENT 'ID, passport, contract_copy, etc.',
    file_path VARCHAR(500) NOT NULL,
    original_name VARCHAR(255),
    uploaded_by_user_id INT UNSIGNED NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by_user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_tenant_customer (tenant_id, customer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- CUSTOMER ACTIVITIES
-- =====================================================

CREATE TABLE customer_activities (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    customer_id INT UNSIGNED NOT NULL,
    activity_type VARCHAR(50) COMMENT 'call, email, meeting, note, visit_scheduled, contract_discussion',
    subject VARCHAR(500),
    description TEXT,
    scheduled_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    owner_user_id INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (owner_user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_tenant_customer (tenant_id, customer_id),
    INDEX idx_scheduled (scheduled_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- VISITS
-- =====================================================

CREATE TABLE visits (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    customer_id INT UNSIGNED NOT NULL,
    unit_id INT UNSIGNED NOT NULL,
    scheduled_date DATE NOT NULL,
    scheduled_time TIME,
    status ENUM('scheduled', 'completed', 'canceled', 'no_show') NOT NULL DEFAULT 'scheduled',
    notes TEXT,
    feedback_rating TINYINT UNSIGNED COMMENT '1-5',
    feedback_comments TEXT,
    created_by_user_id INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_tenant_status (tenant_id, status),
    INDEX idx_tenant_customer (tenant_id, customer_id),
    INDEX idx_tenant_unit (tenant_id, unit_id),
    INDEX idx_scheduled_date (scheduled_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- BOOKINGS
-- =====================================================

CREATE TABLE bookings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    booking_code VARCHAR(100) NOT NULL,
    customer_id INT UNSIGNED NOT NULL,
    unit_id INT UNSIGNED NOT NULL,
    booking_date DATE NOT NULL,
    reserved_until_date DATE NOT NULL,
    booking_status ENUM('active', 'expired', 'canceled', 'converted_to_contract') NOT NULL DEFAULT 'active',
    reservation_amount DECIMAL(12,2) DEFAULT 0,
    currency VARCHAR(3) NOT NULL DEFAULT 'SAR',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE,
    UNIQUE KEY unique_tenant_code (tenant_id, booking_code),
    INDEX idx_tenant_status (tenant_id, booking_status),
    INDEX idx_tenant_customer (tenant_id, customer_id),
    INDEX idx_tenant_unit (tenant_id, unit_id),
    INDEX idx_reserved_until (reserved_until_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- CONTRACTS
-- =====================================================

CREATE TABLE contracts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    contract_code VARCHAR(100) NOT NULL,
    contract_type ENUM('sale', 'rent') NOT NULL,
    customer_id INT UNSIGNED NOT NULL,
    unit_id INT UNSIGNED NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NULL COMMENT 'for rent contracts',
    signing_date DATE,
    base_amount DECIMAL(12,2) NOT NULL,
    tax_percentage DECIMAL(5,2) DEFAULT 0,
    tax_amount DECIMAL(12,2) DEFAULT 0,
    total_amount DECIMAL(12,2) NOT NULL,
    currency VARCHAR(3) NOT NULL DEFAULT 'SAR',
    payment_plan_type VARCHAR(50) COMMENT 'installments, milestone, single_payment, monthly_rent',
    status ENUM('draft', 'active', 'completed', 'canceled', 'expired') NOT NULL DEFAULT 'draft',
    created_by_user_id INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_tenant_code (tenant_id, contract_code),
    INDEX idx_tenant_status (tenant_id, status),
    INDEX idx_tenant_type (tenant_id, contract_type),
    INDEX idx_tenant_customer (tenant_id, customer_id),
    INDEX idx_tenant_unit (tenant_id, unit_id),
    INDEX idx_start_date (start_date),
    INDEX idx_end_date (end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- CONTRACT DOCUMENTS
-- =====================================================

CREATE TABLE contract_documents (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    contract_id INT UNSIGNED NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    original_name VARCHAR(255),
    mime_type VARCHAR(100),
    size_bytes INT UNSIGNED,
    uploaded_by_user_id INT UNSIGNED NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (contract_id) REFERENCES contracts(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by_user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_tenant_contract (tenant_id, contract_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- CONTRACT STATUS HISTORY
-- =====================================================

CREATE TABLE contract_status_history (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    contract_id INT UNSIGNED NOT NULL,
    old_status VARCHAR(50),
    new_status VARCHAR(50),
    changed_by_user_id INT UNSIGNED NULL,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    note TEXT,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (contract_id) REFERENCES contracts(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by_user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_tenant_contract (tenant_id, contract_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- PAYMENT SCHEDULES
-- =====================================================

CREATE TABLE payment_schedules (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    contract_id INT UNSIGNED NOT NULL,
    installment_number INT UNSIGNED NOT NULL,
    due_date DATE NOT NULL,
    amount_due DECIMAL(12,2) NOT NULL,
    currency VARCHAR(3) NOT NULL DEFAULT 'SAR',
    status ENUM('pending', 'partially_paid', 'paid', 'overdue') NOT NULL DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (contract_id) REFERENCES contracts(id) ON DELETE CASCADE,
    INDEX idx_tenant_contract (tenant_id, contract_id),
    INDEX idx_tenant_status (tenant_id, status),
    INDEX idx_due_date (due_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- PAYMENTS
-- =====================================================

CREATE TABLE payments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    contract_id INT UNSIGNED NOT NULL,
    schedule_id INT UNSIGNED NULL COMMENT 'link to payment_schedules if applicable',
    payment_date DATE NOT NULL,
    amount_paid DECIMAL(12,2) NOT NULL,
    currency VARCHAR(3) NOT NULL DEFAULT 'SAR',
    method VARCHAR(50) COMMENT 'cash, bank_transfer, card, cheque, other',
    reference_number VARCHAR(200),
    received_by_user_id INT UNSIGNED NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (contract_id) REFERENCES contracts(id) ON DELETE CASCADE,
    FOREIGN KEY (schedule_id) REFERENCES payment_schedules(id) ON DELETE SET NULL,
    FOREIGN KEY (received_by_user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_tenant_contract (tenant_id, contract_id),
    INDEX idx_payment_date (payment_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TENANT API KEYS
-- =====================================================

CREATE TABLE tenant_api_keys (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    api_key VARCHAR(64) NOT NULL UNIQUE,
    name VARCHAR(100) COMMENT 'e.g., Production, Development',
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    last_used_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    INDEX idx_api_key (api_key),
    INDEX idx_tenant_status (tenant_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- ACTIVITY LOGS
-- =====================================================

CREATE TABLE activity_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NULL,
    user_id INT UNSIGNED NULL,
    entity_type VARCHAR(50) COMMENT 'unit, customer, contract, payment, booking, etc.',
    entity_id INT UNSIGNED,
    action VARCHAR(50) COMMENT 'created, updated, deleted, status_changed, login, logout',
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tenant (tenant_id),
    INDEX idx_user (user_id),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- SAMPLE DATA
-- =====================================================

-- Insert sample plans
INSERT INTO plans (name, code, price, billing_cycle, max_units, max_customers, max_active_contracts, max_users, max_storage_size, max_api_calls_per_month, features) VALUES
('Starter', 'starter', 99.00, 'monthly', 50, 100, 20, 3, 1073741824, 1000, '{"customer_portal": false, "advanced_reports": false, "payment_reminders": true, "api_access": false}'),
('Professional', 'professional', 299.00, 'monthly', 200, 500, 100, 10, 5368709120, 5000, '{"customer_portal": true, "advanced_reports": true, "payment_reminders": true, "api_access": true}'),
('Enterprise', 'enterprise', 699.00, 'monthly', 1000, 5000, 1000, 50, 21474836480, 50000, '{"customer_portal": true, "advanced_reports": true, "payment_reminders": true, "api_access": true}');

-- Insert sample tenant
INSERT INTO tenants (name, code, primary_contact_name, primary_contact_email, phone, address, default_currency, status) VALUES
('Demo Real Estate', 'DEMO001', 'Ahmed Admin', 'admin@demo.com', '+966501234567', 'Riyadh, Saudi Arabia', 'SAR', 'active');

-- Insert tenant subscription
INSERT INTO tenant_subscriptions (tenant_id, plan_id, status, start_date, end_date, renewal_date) VALUES
(1, 2, 'active', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), DATE_ADD(CURDATE(), INTERVAL 1 YEAR));

-- Insert platform admin (password: Admin@123)
INSERT INTO users (tenant_id, name, email, password_hash, role, status) VALUES
(NULL, 'Platform Admin', 'platform@splashproperty.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'platform_admin', 'active');

-- Insert tenant admin (password: Admin@123)
INSERT INTO users (tenant_id, name, email, password_hash, role, status) VALUES
(1, 'Ahmed Admin', 'admin@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'tenant_admin', 'active');

-- Insert sales agent (password: Agent@123)
INSERT INTO users (tenant_id, name, email, password_hash, role, status) VALUES
(1, 'Sara Sales', 'agent@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'sales_agent', 'active');

-- Insert sample project
INSERT INTO projects (tenant_id, name, code, location, city, country, project_type, status) VALUES
(1, 'Al Noor Towers', 'ANT001', 'King Fahd Road', 'Riyadh', 'Saudi Arabia', 'tower', 'active');

-- Insert sample features
INSERT INTO features (tenant_id, name, code, category) VALUES
(1, 'Swimming Pool', 'pool', 'amenity'),
(1, 'Parking', 'parking', 'amenity'),
(1, 'Gym', 'gym', 'amenity'),
(1, '24/7 Security', 'security_24_7', 'security'),
(1, 'Central AC', 'central_ac', 'utilities');

-- Insert sample units
INSERT INTO units (tenant_id, project_id, unit_code, unit_number, unit_type, floor_number, bedrooms, bathrooms, area_size, area_unit, listing_type, sale_price, rent_price, currency, status, description) VALUES
(1, 1, 'ANT-101', '101', 'apartment', 1, 2, 2, 120.00, 'sqm', 'sale', 450000.00, 0, 'SAR', 'available', '2 bedroom apartment with garden view'),
(1, 1, 'ANT-201', '201', 'apartment', 2, 3, 2, 150.00, 'sqm', 'both', 650000.00, 3500.00, 'SAR', 'available', '3 bedroom apartment with city view'),
(1, 1, 'ANT-301', '301', 'apartment', 3, 3, 3, 180.00, 'sqm', 'sale', 850000.00, 0, 'SAR', 'available', 'Luxury 3 bedroom apartment'),
(1, 1, 'ANT-PH1', 'PH1', 'apartment', 10, 4, 4, 300.00, 'sqm', 'sale', 1500000.00, 0, 'SAR', 'available', 'Penthouse with panoramic views');

-- Insert sample customers
INSERT INTO customers (tenant_id, customer_code, first_name, last_name, email, phone, type, source, assigned_to_user_id, status) VALUES
(1, 'CUST-001', 'Mohammed', 'Ali', 'mohammed@example.com', '+966501111111', 'lead', 'website', 3, 'contacted'),
(1, 'CUST-002', 'Fatima', 'Ahmed', 'fatima@example.com', '+966502222222', 'buyer', 'referral', 3, 'active_client'),
(1, 'CUST-003', 'Omar', 'Hassan', 'omar@example.com', '+966503333333', 'lead', 'walk_in', 3, 'qualified');

-- Insert API key for tenant
INSERT INTO tenant_api_keys (tenant_id, api_key, name, status) VALUES
(1, SHA2(CONCAT('DEMO001', NOW(), RAND()), 256), 'Production API Key', 'active');
