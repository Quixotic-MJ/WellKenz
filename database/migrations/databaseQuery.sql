-- WellKenz Bakery ERP System Database Schema
-- PostgreSQL Database Schema for Complete System

-- Enable UUID extension
CREATE EXTENSION IF NOT EXISTS "pgcrypto";

-- ============================================================================
-- DROP TABLES IN CORRECT ORDER (to handle foreign key constraints)
-- ============================================================================
DROP TABLE IF EXISTS password_reset_tokens CASCADE;
DROP TABLE IF EXISTS notifications CASCADE;
DROP TABLE IF EXISTS production_consumption CASCADE;
DROP TABLE IF EXISTS requisition_items CASCADE;
DROP TABLE IF EXISTS purchase_order_items CASCADE;
DROP TABLE IF EXISTS purchase_request_items CASCADE;
DROP TABLE IF EXISTS recipe_ingredients CASCADE;
DROP TABLE IF EXISTS supplier_items CASCADE;
DROP TABLE IF EXISTS stock_movements CASCADE;
DROP TABLE IF EXISTS current_stock CASCADE;
DROP TABLE IF EXISTS batches CASCADE;
DROP TABLE IF EXISTS production_orders CASCADE;
DROP TABLE IF EXISTS recipes CASCADE;
DROP TABLE IF EXISTS purchase_orders CASCADE;
DROP TABLE IF EXISTS purchase_requests CASCADE;
DROP TABLE IF EXISTS requisitions CASCADE;
DROP TABLE IF EXISTS items CASCADE;
DROP TABLE IF EXISTS suppliers CASCADE;
DROP TABLE IF EXISTS categories CASCADE;
DROP TABLE IF EXISTS units CASCADE;
DROP TABLE IF EXISTS user_profiles CASCADE;
DROP TABLE IF EXISTS audit_logs CASCADE;
DROP TABLE IF EXISTS system_settings CASCADE;
DROP TABLE IF EXISTS users CASCADE;

-- ============================================================================
-- USERS TABLE (Core Authentication)
-- ============================================================================
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'employee' CHECK (role IN ('admin', 'supervisor', 'purchasing', 'inventory', 'employee')),
    is_active BOOLEAN NOT NULL DEFAULT true,
    email_verified_at TIMESTAMP NULL,
    last_login_at TIMESTAMP NULL,
    login_attempts INTEGER NOT NULL DEFAULT 0,
    locked_until TIMESTAMP NULL,
    remember_token VARCHAR(100) NULL,
    password_reset_token VARCHAR(100) NULL,
    password_reset_expires TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Indexes for users table
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_users_active ON users(is_active);

-- ============================================================================
-- USER PROFILES TABLE (Extended User Information)
-- ============================================================================
CREATE TABLE user_profiles (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    employee_id VARCHAR(50) UNIQUE,
    phone VARCHAR(20),
    address TEXT,
    date_of_birth DATE,
    hire_date DATE NOT NULL DEFAULT CURRENT_DATE,
    department VARCHAR(100),
    position VARCHAR(100),
    salary DECIMAL(10,2),
    emergency_contact_name VARCHAR(255),
    emergency_contact_phone VARCHAR(20),
    profile_photo_path VARCHAR(255),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_user_profiles_user_id ON user_profiles(user_id);
CREATE INDEX idx_user_profiles_employee_id ON user_profiles(employee_id);

-- ============================================================================
-- CATEGORIES TABLE (Product Categories)
-- ============================================================================
CREATE TABLE categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    parent_id INTEGER REFERENCES categories(id) ON DELETE SET NULL,
    is_active BOOLEAN NOT NULL DEFAULT true,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_categories_parent_id ON categories(parent_id);
CREATE INDEX idx_categories_active ON categories(is_active);

-- ============================================================================
-- UNITS TABLE (Measurement Units)
-- ============================================================================
CREATE TABLE units (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    symbol VARCHAR(10) NOT NULL UNIQUE,
    type VARCHAR(20) NOT NULL CHECK (type IN ('weight', 'volume', 'piece', 'length')),
    base_unit_id INTEGER REFERENCES units(id) ON DELETE SET NULL,
    conversion_factor DECIMAL(10,6) DEFAULT 1.000000,
    is_active BOOLEAN NOT NULL DEFAULT true,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_units_type ON units(type);
CREATE INDEX idx_units_active ON units(is_active);

-- ============================================================================
-- ITEMS TABLE (Product/Ingredient Master List)
-- ============================================================================
CREATE TABLE items (
    id SERIAL PRIMARY KEY,
    item_code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    category_id INTEGER NOT NULL REFERENCES categories(id),
    unit_id INTEGER NOT NULL REFERENCES units(id),
    item_type VARCHAR(20) NOT NULL CHECK (item_type IN ('raw_material', 'finished_good', 'semi_finished', 'supply')),
    barcode VARCHAR(100),
    min_stock_level DECIMAL(10,3) DEFAULT 0.000,
    max_stock_level DECIMAL(10,3) DEFAULT 0.000,
    reorder_point DECIMAL(10,3) DEFAULT 0.000,
    cost_price DECIMAL(10,2) DEFAULT 0.00,
    selling_price DECIMAL(10,2) DEFAULT 0.00,
    shelf_life_days INTEGER,
    storage_requirements TEXT,
    is_active BOOLEAN NOT NULL DEFAULT true,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_items_code ON items(item_code);
CREATE INDEX idx_items_category ON items(category_id);
CREATE INDEX idx_items_type ON items(item_type);
CREATE INDEX idx_items_active ON items(is_active);

-- ============================================================================
-- SUPPLIERS TABLE
-- ============================================================================
CREATE TABLE suppliers (
    id SERIAL PRIMARY KEY,
    supplier_code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    contact_person VARCHAR(255),
    email VARCHAR(255),
    phone VARCHAR(20),
    mobile VARCHAR(20),
    address TEXT,
    city VARCHAR(100),
    province VARCHAR(100),
    postal_code VARCHAR(20),
    tax_id VARCHAR(50),
    payment_terms INTEGER DEFAULT 30,
    credit_limit DECIMAL(12,2) DEFAULT 0.00,
    rating INTEGER CHECK (rating >= 1 AND rating <= 5),
    is_active BOOLEAN NOT NULL DEFAULT true,
    notes TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_suppliers_code ON suppliers(supplier_code);
CREATE INDEX idx_suppliers_active ON suppliers(is_active);

-- ============================================================================
-- SUPPLIER ITEMS TABLE (Supplier-Pricelist)
-- ============================================================================
CREATE TABLE supplier_items (
    id SERIAL PRIMARY KEY,
    supplier_id INTEGER NOT NULL REFERENCES suppliers(id) ON DELETE CASCADE,
    item_id INTEGER NOT NULL REFERENCES items(id) ON DELETE CASCADE,
    supplier_item_code VARCHAR(100),
    unit_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    minimum_order_quantity DECIMAL(10,3) DEFAULT 1.000,
    lead_time_days INTEGER DEFAULT 1,
    last_purchase_price DECIMAL(10,2),
    last_purchase_date DATE,
    is_preferred BOOLEAN NOT NULL DEFAULT false,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(supplier_id, item_id)
);

CREATE INDEX idx_supplier_items_supplier ON supplier_items(supplier_id);
CREATE INDEX idx_supplier_items_item ON supplier_items(item_id);

-- ============================================================================
-- STOCK MOVEMENTS TABLE (Inventory Transactions)
-- ============================================================================
CREATE TABLE stock_movements (
    id SERIAL PRIMARY KEY,
    item_id INTEGER NOT NULL REFERENCES items(id),
    movement_type VARCHAR(30) NOT NULL CHECK (movement_type IN ('purchase', 'sale', 'adjustment', 'transfer', 'production', 'waste', 'return')),
    reference_number VARCHAR(100),
    quantity DECIMAL(10,3) NOT NULL,
    unit_cost DECIMAL(10,2) DEFAULT 0.00,
    total_cost DECIMAL(12,2) DEFAULT 0.00,
    batch_number VARCHAR(100),
    expiry_date DATE,
    location VARCHAR(100),
    notes TEXT,
    user_id INTEGER NOT NULL REFERENCES users(id),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_stock_movements_item ON stock_movements(item_id);
CREATE INDEX idx_stock_movements_type ON stock_movements(movement_type);
CREATE INDEX idx_stock_movements_batch ON stock_movements(batch_number);
CREATE INDEX idx_stock_movements_user ON stock_movements(user_id);

-- ============================================================================
-- CURRENT STOCK TABLE (Running Balances)
-- ============================================================================
CREATE TABLE current_stock (
    id SERIAL PRIMARY KEY,
    item_id INTEGER NOT NULL REFERENCES items(id) ON DELETE CASCADE,
    current_quantity DECIMAL(10,3) NOT NULL DEFAULT 0.000,
    average_cost DECIMAL(10,2) DEFAULT 0.00,
    last_updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(item_id)
);

CREATE INDEX idx_current_stock_item ON current_stock(item_id);

-- ============================================================================
-- BATCHES TABLE (Inventory Batches for Traceability)
-- ============================================================================
CREATE TABLE batches (
    id SERIAL PRIMARY KEY,
    batch_number VARCHAR(100) NOT NULL UNIQUE,
    item_id INTEGER NOT NULL REFERENCES items(id),
    quantity DECIMAL(10,3) NOT NULL,
    unit_cost DECIMAL(10,2) NOT NULL,
    manufacturing_date DATE,
    expiry_date DATE,
    supplier_id INTEGER REFERENCES suppliers(id),
    location VARCHAR(100),
    status VARCHAR(20) NOT NULL DEFAULT 'active' CHECK (status IN ('active', 'expired', 'quarantine', 'consumed')),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_batches_item ON batches(item_id);
CREATE INDEX idx_batches_number ON batches(batch_number);
CREATE INDEX idx_batches_expiry ON batches(expiry_date);
CREATE INDEX idx_batches_status ON batches(status);

-- ============================================================================
-- PURCHASE REQUESTS TABLE
-- ============================================================================
CREATE TABLE purchase_requests (
    id SERIAL PRIMARY KEY,
    pr_number VARCHAR(50) NOT NULL UNIQUE,
    request_date DATE NOT NULL DEFAULT CURRENT_DATE,
    requested_by INTEGER NOT NULL REFERENCES users(id),
    department VARCHAR(100),
    priority VARCHAR(20) NOT NULL DEFAULT 'normal' CHECK (priority IN ('low', 'normal', 'high', 'urgent')),
    status VARCHAR(20) NOT NULL DEFAULT 'pending' CHECK (status IN ('draft', 'pending', 'approved', 'rejected', 'converted')),
    total_estimated_cost DECIMAL(12,2) DEFAULT 0.00,
    approved_by INTEGER REFERENCES users(id),
    approved_at TIMESTAMP NULL,
    notes TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_purchase_requests_number ON purchase_requests(pr_number);
CREATE INDEX idx_purchase_requests_requested_by ON purchase_requests(requested_by);
CREATE INDEX idx_purchase_requests_status ON purchase_requests(status);

-- ============================================================================
-- PURCHASE REQUEST ITEMS TABLE
-- ============================================================================
CREATE TABLE purchase_request_items (
    id SERIAL PRIMARY KEY,
    purchase_request_id INTEGER NOT NULL REFERENCES purchase_requests(id) ON DELETE CASCADE,
    item_id INTEGER NOT NULL REFERENCES items(id),
    quantity_requested DECIMAL(10,3) NOT NULL,
    unit_price_estimate DECIMAL(10,2) DEFAULT 0.00,
    total_estimated_cost DECIMAL(12,2) DEFAULT 0.00,
    notes TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_purchase_request_items_pr ON purchase_request_items(purchase_request_id);
CREATE INDEX idx_purchase_request_items_item ON purchase_request_items(item_id);

-- ============================================================================
-- PURCHASE ORDERS TABLE
-- ============================================================================
CREATE TABLE purchase_orders (
    id SERIAL PRIMARY KEY,
    po_number VARCHAR(50) NOT NULL UNIQUE,
    supplier_id INTEGER NOT NULL REFERENCES suppliers(id),
    order_date DATE NOT NULL DEFAULT CURRENT_DATE,
    expected_delivery_date DATE,
    actual_delivery_date DATE,
    status VARCHAR(20) NOT NULL DEFAULT 'draft' CHECK (status IN ('draft', 'sent', 'confirmed', 'partial', 'completed', 'cancelled')),
    total_amount DECIMAL(12,2) DEFAULT 0.00,
    tax_amount DECIMAL(12,2) DEFAULT 0.00,
    discount_amount DECIMAL(12,2) DEFAULT 0.00,
    grand_total DECIMAL(12,2) DEFAULT 0.00,
    payment_terms INTEGER DEFAULT 30,
    notes TEXT,
    created_by INTEGER NOT NULL REFERENCES users(id),
    approved_by INTEGER REFERENCES users(id),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_purchase_orders_number ON purchase_orders(po_number);
CREATE INDEX idx_purchase_orders_supplier ON purchase_orders(supplier_id);
CREATE INDEX idx_purchase_orders_status ON purchase_orders(status);

-- ============================================================================
-- PURCHASE ORDER ITEMS TABLE
-- ============================================================================
CREATE TABLE purchase_order_items (
    id SERIAL PRIMARY KEY,
    purchase_order_id INTEGER NOT NULL REFERENCES purchase_orders(id) ON DELETE CASCADE,
    item_id INTEGER NOT NULL REFERENCES items(id),
    quantity_ordered DECIMAL(10,3) NOT NULL,
    quantity_received DECIMAL(10,3) DEFAULT 0.000,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(12,2) NOT NULL,
    notes TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_purchase_order_items_po ON purchase_order_items(purchase_order_id);
CREATE INDEX idx_purchase_order_items_item ON purchase_order_items(item_id);

-- ============================================================================
-- RECIPES TABLE (Production Recipes/Formulas)
-- ============================================================================
CREATE TABLE recipes (
    id SERIAL PRIMARY KEY,
    recipe_code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    finished_item_id INTEGER NOT NULL REFERENCES items(id),
    yield_quantity DECIMAL(10,3) NOT NULL,
    yield_unit_id INTEGER NOT NULL REFERENCES units(id),
    preparation_time INTEGER,
    cooking_time INTEGER,
    serving_size DECIMAL(10,3),
    instructions TEXT,
    notes TEXT,
    is_active BOOLEAN NOT NULL DEFAULT true,
    created_by INTEGER NOT NULL REFERENCES users(id),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_recipes_code ON recipes(recipe_code);
CREATE INDEX idx_recipes_finished_item ON recipes(finished_item_id);
CREATE INDEX idx_recipes_active ON recipes(is_active);

-- ============================================================================
-- RECIPE INGREDIENTS TABLE
-- ============================================================================
CREATE TABLE recipe_ingredients (
    id SERIAL PRIMARY KEY,
    recipe_id INTEGER NOT NULL REFERENCES recipes(id) ON DELETE CASCADE,
    item_id INTEGER NOT NULL REFERENCES items(id),
    quantity_required DECIMAL(10,3) NOT NULL,
    unit_id INTEGER NOT NULL REFERENCES units(id),
    is_optional BOOLEAN NOT NULL DEFAULT false,
    notes TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(recipe_id, item_id)
);

CREATE INDEX idx_recipe_ingredients_recipe ON recipe_ingredients(recipe_id);
CREATE INDEX idx_recipe_ingredients_item ON recipe_ingredients(item_id);

-- ============================================================================
-- PRODUCTION ORDERS TABLE
-- ============================================================================
CREATE TABLE production_orders (
    id SERIAL PRIMARY KEY,
    production_number VARCHAR(50) NOT NULL UNIQUE,
    recipe_id INTEGER NOT NULL REFERENCES recipes(id),
    planned_quantity DECIMAL(10,3) NOT NULL,
    actual_quantity DECIMAL(10,3) DEFAULT 0.000,
    unit_id INTEGER NOT NULL REFERENCES units(id),
    planned_start_date DATE NOT NULL,
    planned_end_date DATE NOT NULL,
    actual_start_date DATE,
    actual_end_date DATE,
    status VARCHAR(20) NOT NULL DEFAULT 'planned' CHECK (status IN ('planned', 'in_progress', 'completed', 'cancelled')),
    total_material_cost DECIMAL(12,2) DEFAULT 0.00,
    total_labor_cost DECIMAL(12,2) DEFAULT 0.00,
    overhead_cost DECIMAL(12,2) DEFAULT 0.00,
    notes TEXT,
    created_by INTEGER NOT NULL REFERENCES users(id),
    supervisor_id INTEGER REFERENCES users(id),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_production_orders_number ON production_orders(production_number);
CREATE INDEX idx_production_orders_recipe ON production_orders(recipe_id);
CREATE INDEX idx_production_orders_status ON production_orders(status);

-- ============================================================================
-- PRODUCTION CONSUMPTION TABLE (Material Usage Tracking)
-- ============================================================================
CREATE TABLE production_consumption (
    id SERIAL PRIMARY KEY,
    production_order_id INTEGER NOT NULL REFERENCES production_orders(id) ON DELETE CASCADE,
    item_id INTEGER NOT NULL REFERENCES items(id),
    quantity_consumed DECIMAL(10,3) NOT NULL,
    batch_number VARCHAR(100),
    consumption_date DATE NOT NULL DEFAULT CURRENT_DATE,
    notes TEXT,
    created_by INTEGER NOT NULL REFERENCES users(id),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_production_consumption_order ON production_consumption(production_order_id);
CREATE INDEX idx_production_consumption_item ON production_consumption(item_id);

-- ============================================================================
-- REQUISITIONS TABLE (Internal Material Requests)
-- ============================================================================
CREATE TABLE requisitions (
    id SERIAL PRIMARY KEY,
    requisition_number VARCHAR(50) NOT NULL UNIQUE,
    request_date DATE NOT NULL DEFAULT CURRENT_DATE,
    requested_by INTEGER NOT NULL REFERENCES users(id),
    department VARCHAR(100),
    purpose TEXT,
    status VARCHAR(20) NOT NULL DEFAULT 'pending' CHECK (status IN ('pending', 'approved', 'rejected', 'fulfilled')),
    total_estimated_value DECIMAL(12,2) DEFAULT 0.00,
    approved_by INTEGER REFERENCES users(id),
    approved_at TIMESTAMP NULL,
    fulfilled_by INTEGER REFERENCES users(id),
    fulfilled_at TIMESTAMP NULL,
    notes TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_requisitions_number ON requisitions(requisition_number);
CREATE INDEX idx_requisitions_requested_by ON requisitions(requested_by);
CREATE INDEX idx_requisitions_status ON requisitions(status);

-- ============================================================================
-- REQUISITION ITEMS TABLE
-- ============================================================================
CREATE TABLE requisition_items (
    id SERIAL PRIMARY KEY,
    requisition_id INTEGER NOT NULL REFERENCES requisitions(id) ON DELETE CASCADE,
    item_id INTEGER NOT NULL REFERENCES items(id),
    quantity_requested DECIMAL(10,3) NOT NULL,
    quantity_issued DECIMAL(10,3) DEFAULT 0.000,
    unit_cost_estimate DECIMAL(10,2) DEFAULT 0.00,
    total_estimated_value DECIMAL(12,2) DEFAULT 0.00,
    notes TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_requisition_items_requisition ON requisition_items(requisition_id);
CREATE INDEX idx_requisition_items_item ON requisition_items(item_id);

-- ============================================================================
-- AUDIT LOGS TABLE (System Activity Tracking)
-- ============================================================================
CREATE TABLE audit_logs (
    id SERIAL PRIMARY KEY,
    table_name VARCHAR(100) NOT NULL,
    record_id INTEGER,
    action VARCHAR(20) NOT NULL CHECK (action IN ('CREATE', 'UPDATE', 'DELETE')),
    old_values JSONB,
    new_values JSONB,
    user_id INTEGER REFERENCES users(id),
    ip_address INET,
    user_agent TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_audit_logs_table ON audit_logs(table_name);
CREATE INDEX idx_audit_logs_record ON audit_logs(record_id);
CREATE INDEX idx_audit_logs_user ON audit_logs(user_id);
CREATE INDEX idx_audit_logs_action ON audit_logs(action);
CREATE INDEX idx_audit_logs_created_at ON audit_logs(created_at);

-- ============================================================================
-- SYSTEM SETTINGS TABLE
-- ============================================================================
CREATE TABLE system_settings (
    id SERIAL PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_type VARCHAR(20) NOT NULL DEFAULT 'string' CHECK (setting_type IN ('string', 'integer', 'decimal', 'boolean', 'json')),
    description TEXT,
    is_public BOOLEAN NOT NULL DEFAULT false,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_system_settings_key ON system_settings(setting_key);

-- ============================================================================
-- NOTIFICATIONS TABLE
-- ============================================================================
CREATE TABLE notifications (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type VARCHAR(50) NOT NULL,
    priority VARCHAR(20) NOT NULL DEFAULT 'normal' CHECK (priority IN ('low', 'normal', 'high', 'urgent')),
    is_read BOOLEAN NOT NULL DEFAULT false,
    action_url VARCHAR(255),
    metadata JSONB,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_notifications_user ON notifications(user_id);
CREATE INDEX idx_notifications_read ON notifications(is_read);
CREATE INDEX idx_notifications_type ON notifications(type);

-- ============================================================================
-- PASSWORD RESET TOKENS TABLE (Laravel Password Reset)
-- ============================================================================
CREATE TABLE password_reset_tokens (
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    PRIMARY KEY (email, token)
);

-- ============================================================================
-- CREATE FUNCTIONS AND TRIGGERS
-- ============================================================================

-- Create trigger for automatic updated_at timestamps
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ language 'plpgsql';

-- Create function to update current stock automatically
CREATE OR REPLACE FUNCTION update_current_stock()
RETURNS TRIGGER AS $$
BEGIN
    -- Insert or update current stock for the item
    INSERT INTO current_stock (item_id, current_quantity, average_cost, last_updated)
    VALUES (NEW.item_id, NEW.quantity, NEW.unit_cost, CURRENT_TIMESTAMP)
    ON CONFLICT (item_id) DO UPDATE
    SET 
        current_quantity = current_stock.current_quantity + NEW.quantity,
        average_cost = CASE 
            WHEN NEW.unit_cost > 0 THEN NEW.unit_cost 
            ELSE current_stock.average_cost 
        END,
        last_updated = CURRENT_TIMESTAMP;
    
    RETURN NEW;
END;
$$ language 'plpgsql';

-- Create function to calculate totals for purchase requests/orders
CREATE OR REPLACE FUNCTION calculate_request_totals()
RETURNS TRIGGER AS $$
BEGIN
    -- For purchase request items
    IF TG_TABLE_NAME = 'purchase_request_items' THEN
        UPDATE purchase_requests 
        SET total_estimated_cost = (
            SELECT COALESCE(SUM(total_estimated_cost), 0)
            FROM purchase_request_items
            WHERE purchase_request_id = NEW.purchase_request_id
        )
        WHERE id = NEW.purchase_request_id;
    END IF;
    
    -- For purchase order items
    IF TG_TABLE_NAME = 'purchase_order_items' THEN
        UPDATE purchase_orders 
        SET total_amount = (
            SELECT COALESCE(SUM(total_price), 0)
            FROM purchase_order_items
            WHERE purchase_order_id = NEW.purchase_order_id
        ),
        grand_total = (
            SELECT COALESCE(SUM(total_price), 0) + COALESCE(tax_amount, 0) - COALESCE(discount_amount, 0)
            FROM purchase_order_items
            WHERE purchase_order_id = NEW.purchase_order_id
        )
        WHERE id = NEW.purchase_order_id;
    END IF;
    
    -- For requisition items
    IF TG_TABLE_NAME = 'requisition_items' THEN
        UPDATE requisitions 
        SET total_estimated_value = (
            SELECT COALESCE(SUM(total_estimated_value), 0)
            FROM requisition_items
            WHERE requisition_id = NEW.requisition_id
        )
        WHERE id = NEW.requisition_id;
    END IF;
    
    RETURN NEW;
END;
$$ language 'plpgsql';

-- ============================================================================
-- CREATE TRIGGERS
-- ============================================================================

-- Create triggers for all tables with updated_at column
CREATE TRIGGER update_users_updated_at BEFORE UPDATE ON users FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_user_profiles_updated_at BEFORE UPDATE ON user_profiles FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_categories_updated_at BEFORE UPDATE ON categories FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_units_updated_at BEFORE UPDATE ON units FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_items_updated_at BEFORE UPDATE ON items FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_suppliers_updated_at BEFORE UPDATE ON suppliers FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_supplier_items_updated_at BEFORE UPDATE ON supplier_items FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_current_stock_updated_at BEFORE UPDATE ON current_stock FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_batches_updated_at BEFORE UPDATE ON batches FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_purchase_requests_updated_at BEFORE UPDATE ON purchase_requests FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_purchase_orders_updated_at BEFORE UPDATE ON purchase_orders FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_recipes_updated_at BEFORE UPDATE ON recipes FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_production_orders_updated_at BEFORE UPDATE ON production_orders FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_requisitions_updated_at BEFORE UPDATE ON requisitions FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_system_settings_updated_at BEFORE UPDATE ON system_settings FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- Create trigger to automatically update current stock
CREATE TRIGGER update_stock_movement_trigger
    AFTER INSERT ON stock_movements
    FOR EACH ROW
    EXECUTE FUNCTION update_current_stock();

-- Create triggers for automatic total calculations
CREATE TRIGGER calculate_pr_item_totals
    AFTER INSERT OR UPDATE OR DELETE ON purchase_request_items
    FOR EACH ROW EXECUTE FUNCTION calculate_request_totals();

CREATE TRIGGER calculate_po_item_totals
    AFTER INSERT OR UPDATE OR DELETE ON purchase_order_items
    FOR EACH ROW EXECUTE FUNCTION calculate_request_totals();

CREATE TRIGGER calculate_req_item_totals
    AFTER INSERT OR UPDATE OR DELETE ON requisition_items
    FOR EACH ROW EXECUTE FUNCTION calculate_request_totals();

-- ============================================================================
-- SEED DATA
-- ============================================================================

-- Insert default system settings
INSERT INTO system_settings (setting_key, setting_value, setting_type, description, is_public) VALUES
('app_name', 'WellKenz', 'string', 'Application name', true),
('app_timezone', 'Asia/Manila', 'string', 'Application timezone', false),
('company_name', 'WellKenz Bakery', 'string', 'Company name', true),
('currency', 'PHP', 'string', 'Default currency', true),
('low_stock_threshold', '10', 'integer', 'Low stock alert threshold', false),
('default_lead_time', '3', 'integer', 'Default supplier lead time in days', false);

-- Insert default units
INSERT INTO units (name, symbol, type) VALUES
('Kilogram', 'kg', 'weight'),
('Gram', 'g', 'weight'),
('Pound', 'lb', 'weight'),
('Ounce', 'oz', 'weight'),
('Liter', 'L', 'volume'),
('Milliliter', 'ml', 'volume'),
('Cup', 'cup', 'volume'),
('Tablespoon', 'tbsp', 'volume'),
('Teaspoon', 'tsp', 'volume'),
('Piece', 'pc', 'piece'),
('Dozen', 'doz', 'piece'),
('Box', 'box', 'piece'),
('Pack', 'pack', 'piece');

-- Insert default categories
INSERT INTO categories (name, description) VALUES
('Flour & Grains', 'Wheat flour, rice, oats and other grain products'),
('Dairy Products', 'Milk, cheese, butter, cream and dairy items'),
('Sweeteners', 'Sugar, honey, syrups and artificial sweeteners'),
('Fats & Oils', 'Cooking oils, butter, margarine and shortening'),
('Leavening Agents', 'Yeast, baking powder, baking soda'),
('Flavoring & Spices', 'Vanilla, cinnamon, nutmeg and other spices'),
('Nuts & Seeds', 'Almonds, walnuts, sesame seeds and similar'),
('Fruits', 'Fresh and dried fruits for baking'),
('Additives & Preservatives', 'Food additives, preservatives and stabilizers'),
('Packaging Materials', 'Boxes, bags, containers and wrapping materials'),
('Cleaning Supplies', 'Sanitizers, detergents and cleaning agents'),
('Finished Products', 'Ready-to-sell bakery items'),
('Tools & Equipment', 'Baking tools, utensils and small equipment');

-- Insert default admin user (password: password)
INSERT INTO users (name, email, password_hash, role, is_active) VALUES
('System Administrator', 'admin@wellkenz.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', true);

-- Insert sample roles for other users
INSERT INTO users (name, email, password_hash, role, is_active) VALUES
('Inventory Manager', 'inventory@wellkenz.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'inventory', true),
('Purchasing Officer', 'purchasing@wellkenz.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'purchasing', true),
('Production Supervisor', 'supervisor@wellkenz.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'supervisor', true),
('Baker Employee', 'baker@wellkenz.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee', true);

-- Create user profiles for the admin user
INSERT INTO user_profiles (user_id, employee_id, phone, department, position) 
SELECT id, 'ADMIN001', '+63 912 345 6789', 'Administration', 'System Administrator' 
FROM users WHERE email = 'admin@wellkenz.com';

-- ============================================================================
-- COMPLETION MESSAGE
-- ============================================================================
DO $$ 
BEGIN
    RAISE NOTICE '=========================================================';
    RAISE NOTICE 'WellKenz Bakery ERP Database Schema created successfully!';
    RAISE NOTICE '=========================================================';
    RAISE NOTICE 'All tables created with SERIAL primary keys';
    RAISE NOTICE 'All foreign keys properly configured';
    RAISE NOTICE 'Automatic triggers for timestamps and calculations implemented';
    RAISE NOTICE 'Seed data inserted successfully';
    RAISE NOTICE '';
    RAISE NOTICE 'Admin Login: admin@wellkenz.com / password';
    RAISE NOTICE 'Other test users created with different roles';
    RAISE NOTICE '=========================================================';
END $$;