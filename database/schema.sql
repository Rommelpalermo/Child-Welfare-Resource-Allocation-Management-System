-- ============================================================
-- Bahay Pag-asa San Antonio, Biñan City Laguna
-- Child Welfare & Resource Allocation Management System
-- Database Schema
-- ============================================================

CREATE DATABASE IF NOT EXISTS bahay_pagasa;
USE bahay_pagasa;

-- ------------------------------------------------------------
-- Users / Accounts
-- ------------------------------------------------------------
CREATE TABLE users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    full_name   VARCHAR(150)  NOT NULL,
    username    VARCHAR(80)   NOT NULL UNIQUE,
    password    VARCHAR(255)  NOT NULL,
    role        ENUM('admin','staff') NOT NULL DEFAULT 'staff',
    email       VARCHAR(150),
    contact     VARCHAR(30),
    status      ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Default admin  (password: Admin@1234)
-- password for both accounts is: password
INSERT INTO users (full_name, username, password, role) VALUES
('System Administrator', 'admin', '$2y$10$/Aio6d/LJCahNwf.4MeYYO60qfOx/FKXOLCPSpJW.ZA0Kqf3aWk2C', 'admin'),
('Staff User',           'staff', '$2y$10$/Aio6d/LJCahNwf.4MeYYO60qfOx/FKXOLCPSpJW.ZA0Kqf3aWk2C', 'staff');

-- ------------------------------------------------------------
-- Children Records
-- ------------------------------------------------------------
CREATE TABLE children (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    case_number     VARCHAR(50) UNIQUE,
    first_name      VARCHAR(80)  NOT NULL,
    last_name       VARCHAR(80)  NOT NULL,
    middle_name     VARCHAR(80),
    birth_date      DATE,
    gender          ENUM('Male','Female','Other'),
    address         TEXT,
    guardian_name   VARCHAR(150),
    guardian_contact VARCHAR(30),
    admission_date  DATE,
    case_type       VARCHAR(100),
    case_status     ENUM('Active','Closed','Referred','Reunified') DEFAULT 'Active',
    notes           TEXT,
    photo           VARCHAR(255),
    created_by      INT,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ------------------------------------------------------------
-- Case Status History
-- ------------------------------------------------------------
CREATE TABLE case_status_history (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    child_id    INT NOT NULL,
    old_status  VARCHAR(50),
    new_status  VARCHAR(50),
    remarks     TEXT,
    changed_by  INT,
    changed_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (child_id)   REFERENCES children(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id)    ON DELETE SET NULL
);

-- ------------------------------------------------------------
-- Partner Organizations
-- ------------------------------------------------------------
CREATE TABLE partners (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    org_name        VARCHAR(200) NOT NULL,
    contact_person  VARCHAR(150),
    contact_email   VARCHAR(150),
    contact_phone   VARCHAR(30),
    address         TEXT,
    partnership_type VARCHAR(100),
    status          ENUM('Active','Inactive') DEFAULT 'Active',
    notes           TEXT,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ------------------------------------------------------------
-- Resources / Inventory
-- ------------------------------------------------------------
CREATE TABLE resources (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    item_name       VARCHAR(150) NOT NULL,
    category        VARCHAR(100),
    description     TEXT,
    quantity        INT          NOT NULL DEFAULT 0,
    unit            VARCHAR(50),
    condition_status ENUM('Good','Fair','Poor','For Disposal') DEFAULT 'Good',
    location        VARCHAR(150),
    acquired_date   DATE,
    notes           TEXT,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ------------------------------------------------------------
-- Resource Allocation
-- ------------------------------------------------------------
CREATE TABLE resource_allocations (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    resource_id     INT NOT NULL,
    child_id        INT,
    allocated_to    VARCHAR(150),
    quantity        INT NOT NULL DEFAULT 1,
    allocated_by    INT,
    allocation_date DATE,
    return_date     DATE,
    status          ENUM('Allocated','Returned','Lost') DEFAULT 'Allocated',
    notes           TEXT,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (resource_id)  REFERENCES resources(id) ON DELETE CASCADE,
    FOREIGN KEY (child_id)     REFERENCES children(id)  ON DELETE SET NULL,
    FOREIGN KEY (allocated_by) REFERENCES users(id)     ON DELETE SET NULL
);

-- ------------------------------------------------------------
-- Donations
-- ------------------------------------------------------------
CREATE TABLE donations (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    donor_name      VARCHAR(200) NOT NULL,
    donor_type      ENUM('Individual','Organization','Government') DEFAULT 'Individual',
    partner_id      INT,
    donation_type   ENUM('Cash','In-Kind','Food','Clothing','Medical','Other') DEFAULT 'Cash',
    amount          DECIMAL(12,2) DEFAULT 0.00,
    items_desc      TEXT,
    donation_date   DATE NOT NULL,
    received_by     INT,
    status          ENUM('Received','Pending','Acknowledged') DEFAULT 'Received',
    acknowledgement_no VARCHAR(100),
    notes           TEXT,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (partner_id)   REFERENCES partners(id) ON DELETE SET NULL,
    FOREIGN KEY (received_by)  REFERENCES users(id)    ON DELETE SET NULL
);

-- ------------------------------------------------------------
-- Donation Monitoring (distribution of donated items/funds)
-- ------------------------------------------------------------
CREATE TABLE donation_monitoring (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    donation_id     INT NOT NULL,
    child_id        INT,
    distributed_to  VARCHAR(200),
    quantity        INT DEFAULT 1,
    amount          DECIMAL(12,2) DEFAULT 0.00,
    distributed_by  INT,
    distribution_date DATE,
    remarks         TEXT,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (donation_id)    REFERENCES donations(id) ON DELETE CASCADE,
    FOREIGN KEY (child_id)       REFERENCES children(id)  ON DELETE SET NULL,
    FOREIGN KEY (distributed_by) REFERENCES users(id)     ON DELETE SET NULL
);

-- ------------------------------------------------------------
-- Activity / Audit Log
-- ------------------------------------------------------------
CREATE TABLE activity_logs (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT,
    action      VARCHAR(255),
    module      VARCHAR(100),
    ip_address  VARCHAR(45),
    logged_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);
