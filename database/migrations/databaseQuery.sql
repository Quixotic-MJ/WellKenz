-- =============================================
-- 1. CLEANUP (Reset for a fresh run)
-- =============================================
DROP SCHEMA IF EXISTS public CASCADE;
CREATE SCHEMA public;

-- =============================================
-- 2. ENUM TYPES (Strict Status Definitions)
-- =============================================
CREATE TYPE user_role AS ENUM ('admin', 'supervisor', 'purchasing', 'inventory', 'employee');
CREATE TYPE po_status AS ENUM ('draft', 'ordered', 'partially_received', 'completed', 'cancelled');
CREATE TYPE req_status AS ENUM ('pending', 'approved', 'rejected', 'issued', 'completed', 'disputed');
CREATE TYPE trans_type AS ENUM ('in', 'out', 'adjustment', 'return', 'transfer');

-- =============================================
-- 3. MASTER DATA TABLES
-- =============================================

-- USERS: Handles Login and RBAC
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role user_role NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- SUPPLIERS: Procurement Sources
CREATE TABLE suppliers (
    id SERIAL PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    contact_person VARCHAR(100),
    email VARCHAR(100),
    phone VARCHAR(50),
    tin_number VARCHAR(50), -- Tax ID for compliance
    address TEXT,
    lead_time_days INT DEFAULT 3, -- Supplier Performance Metric
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- CATEGORIES: e.g., Dry Goods, Dairy, Packaging
CREATE TABLE categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT
);

-- ITEMS: The Core Logic Definitions
CREATE TABLE items (
    id SERIAL PRIMARY KEY,
    category_id INT REFERENCES categories(id),
    name VARCHAR(150) NOT NULL, -- e.g., "Bread Flour"
    sku VARCHAR(50) UNIQUE,
    
    -- UNIT LOGIC (Crucial for Admin)
    purchase_unit VARCHAR(20) NOT NULL, -- e.g., "Sack"
    stock_unit VARCHAR(20) NOT NULL,    -- e.g., "kg"
    conversion_factor DECIMAL(10,4) NOT NULL, -- e.g., 25.0000 (1 Sack = 25kg)
    
    -- CONTROLS
    reorder_level DECIMAL(10,4) DEFAULT 10.00, -- Trigger for Purchasing
    is_perishable BOOLEAN DEFAULT FALSE, -- Forces Expiry Input
    standard_cost DECIMAL(12,2) DEFAULT 0.00,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =============================================
-- 4. PROCUREMENT MODULE (Purchasing Officer)
-- =============================================

-- PURCHASE ORDERS
CREATE TABLE purchase_orders (
    id SERIAL PRIMARY KEY,
    po_number VARCHAR(50) UNIQUE NOT NULL, -- e.g., PO-2023-001
    supplier_id INT REFERENCES suppliers(id),
    created_by_user_id INT REFERENCES users(id), -- Purchasing Officer
    
    status po_status DEFAULT 'draft',
    total_amount DECIMAL(12,2) DEFAULT 0.00,
    
    expected_delivery_date DATE,
    ordered_at TIMESTAMP, -- When "Send" or "Mark as Ordered" was clicked
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- PO ITEMS (Line Items)
CREATE TABLE purchase_order_items (
    id SERIAL PRIMARY KEY,
    purchase_order_id INT REFERENCES purchase_orders(id) ON DELETE CASCADE,
    item_id INT REFERENCES items(id),
    
    requested_qty DECIMAL(10,4) NOT NULL, -- In Purchase Unit (Sacks)
    unit_price DECIMAL(12,2) NOT NULL, -- Cost per Sack
    
    -- Tracks Partial Delivery
    received_qty DECIMAL(10,4) DEFAULT 0, -- Running total of received Sacks
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =============================================
-- 5. INVENTORY MODULE (Inventory Staff)
-- =============================================

-- RECEIVING REPORTS (The Physical Event)
CREATE TABLE receiving_reports (
    id SERIAL PRIMARY KEY,
    purchase_order_id INT REFERENCES purchase_orders(id),
    received_by_user_id INT REFERENCES users(id), -- Inventory Staff
    
    reference_no VARCHAR(100), -- Supplier's Delivery Receipt / Invoice No.
    remarks TEXT,
    received_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- INVENTORY BATCHES (The FIFO Engine)
CREATE TABLE inventory_batches (
    id SERIAL PRIMARY KEY,
    item_id INT REFERENCES items(id),
    receiving_report_id INT REFERENCES receiving_reports(id),
    
    batch_code VARCHAR(100) UNIQUE NOT NULL, -- e.g., "FLOUR-20231027-A"
    
    -- FIFO Tracking
    initial_qty DECIMAL(12,4) NOT NULL, -- In Stock Unit (kg)
    current_qty DECIMAL(12,4) NOT NULL, -- In Stock Unit (kg) - Decreases over time
    cost_per_unit DECIMAL(12,2) NOT NULL, -- Cost per Stock Unit (calculated from PO)
    
    expiry_date DATE, -- CRITICAL for Perishables
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =============================================
-- 6. PRODUCTION & REQUISITION MODULE (Employee/Supervisor)
-- =============================================

-- REQUISITIONS (The Request)
CREATE TABLE requisitions (
    id SERIAL PRIMARY KEY,
    control_no VARCHAR(50) UNIQUE NOT NULL, -- e.g., REQ-2023-009
    requested_by_user_id INT REFERENCES users(id), -- Employee (Baker)
    
    status req_status DEFAULT 'pending',
    remarks TEXT,
    
    -- Approval Trail (Supervisor)
    approved_by_user_id INT REFERENCES users(id),
    approved_at TIMESTAMP,
    
    -- Issuance Trail (Inventory Staff)
    issued_by_user_id INT REFERENCES users(id),
    issued_at TIMESTAMP,
    
    -- Digital Handshake (Baker Confirmation)
    received_by_user_id INT REFERENCES users(id),
    received_at TIMESTAMP, -- If NOT NULL, liability transferred to kitchen
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- REQUISITION ITEMS
CREATE TABLE requisition_items (
    id SERIAL PRIMARY KEY,
    requisition_id INT REFERENCES requisitions(id) ON DELETE CASCADE,
    item_id INT REFERENCES items(id),
    
    requested_qty DECIMAL(12,4) NOT NULL, -- In Stock Unit (kg)
    issued_qty DECIMAL(12,4) DEFAULT 0, -- If Supervisor edits quantity
    
    notes VARCHAR(255)
);

-- PRODUCTION LOGS (Yield Tracking)
CREATE TABLE production_logs (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(id), -- Baker
    product_name VARCHAR(150), -- e.g., "Ensaymada"
    quantity_produced DECIMAL(10,2),
    
    -- Variance Analysis
    remarks TEXT, -- "Spilled dough", "Burnt 2 pcs"
    logged_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =============================================
-- 7. AUDIT & TRANSACTIONS (The Ledger)
-- =============================================

-- STOCK TRANSACTIONS (History of every gram moved)
CREATE TABLE stock_transactions (
    id SERIAL PRIMARY KEY,
    item_id INT REFERENCES items(id),
    inventory_batch_id INT REFERENCES inventory_batches(id), -- Which specific batch was touched
    user_id INT REFERENCES users(id), -- Who did it
    
    transaction_type trans_type NOT NULL,
    quantity DECIMAL(12,4) NOT NULL, -- Negative for OUT, Positive for IN
    remaining_batch_balance DECIMAL(12,4) NOT NULL, -- Snapshot of balance after move
    
    -- Links to source documents
    requisition_id INT REFERENCES requisitions(id),
    receiving_report_id INT REFERENCES receiving_reports(id),
    
    remarks VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =============================================
-- 8. INDEXES (Performance Optimization)
-- =============================================

-- Speeds up FIFO lookup (Find oldest batch with > 0 qty)
CREATE INDEX idx_fifo_sort ON inventory_batches (item_id, expiry_date ASC, current_qty);

-- Speeds up Login
CREATE INDEX idx_users_email ON users (email);

-- Speeds up Status Filtering for Dashboards
CREATE INDEX idx_po_status ON purchase_orders (status);
CREATE INDEX idx_req_status ON requisitions (status);

-- =============================================
-- 9. SEED DATA (Initial Setup for Testing)
-- =============================================

-- Create Admin
INSERT INTO users (name, email, password_hash, role) 
VALUES ('System Admin', 'admin@bakery.com', 'hashed_password_here', 'admin');

-- Create Categories
INSERT INTO categories (name) VALUES ('Ingredients'), ('Packaging'), ('Cleaning Supplies');

-- Create Items (Example: Flour)
-- 1 Sack = 25kg. Reorder if below 50kg (2 sacks).
INSERT INTO items (category_id, name, sku, purchase_unit, stock_unit, conversion_factor, reorder_level, is_perishable)
VALUES (1, 'Bread Flour', 'ING-FLOUR-01', 'Sack', 'kg', 25.0000, 50.0000, FALSE);

-- Create Items (Example: Eggs)
-- 1 Tray = 30 pcs. Reorder if below 100 pcs.
INSERT INTO items (category_id, name, sku, purchase_unit, stock_unit, conversion_factor, reorder_level, is_perishable)
VALUES (1, 'Large Eggs', 'ING-EGG-L', 'Tray', 'pcs', 30.0000, 100.0000, TRUE);