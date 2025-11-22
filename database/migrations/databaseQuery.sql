-- WellKenz Bakery ERP System Database Schema
-- PostgreSQL Database Schema for Complete System

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

-- Create trigger for automatic updated_at timestamps (only for tables that have updated_at)
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    -- Only update updated_at if the table has that column
    IF TG_TABLE_NAME IN ('users', 'user_profiles', 'categories', 'units', 'items', 'suppliers', 
                        'supplier_items', 'batches', 'purchase_requests', 'purchase_orders', 
                        'recipes', 'production_orders', 'requisitions', 'system_settings') THEN
        NEW.updated_at = CURRENT_TIMESTAMP;
    END IF;
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
-- CREATE TRIGGERS (Only for tables that have updated_at column)
-- ============================================================================

-- Create triggers for tables with updated_at column
CREATE TRIGGER update_users_updated_at BEFORE UPDATE ON users FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_user_profiles_updated_at BEFORE UPDATE ON user_profiles FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_categories_updated_at BEFORE UPDATE ON categories FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_units_updated_at BEFORE UPDATE ON units FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_items_updated_at BEFORE UPDATE ON items FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_suppliers_updated_at BEFORE UPDATE ON suppliers FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_supplier_items_updated_at BEFORE UPDATE ON supplier_items FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
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
-- SEED DATA - EXTENSIVE SAMPLE DATA (100+ records)
-- ============================================================================

-- Insert default system settings
INSERT INTO system_settings (setting_key, setting_value, setting_type, description, is_public) VALUES
('app_name', 'WellKenz', 'string', 'Application name', true),
('app_timezone', 'Asia/Manila', 'string', 'Application timezone', false),
('company_name', 'WellKenz Bakery', 'string', 'Company name', true),
('currency', 'PHP', 'string', 'Default currency', true),
('low_stock_threshold', '10', 'integer', 'Low stock alert threshold', false),
('default_lead_time', '3', 'integer', 'Default supplier lead time in days', false),
('business_hours_open', '06:00', 'string', 'Business opening time', true),
('business_hours_close', '20:00', 'string', 'Business closing time', true),
('tax_rate', '0.12', 'decimal', 'VAT tax rate', false),
('default_batch_size', '100', 'integer', 'Default production batch size', false);

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
('Pack', 'pack', 'piece'),
('Meter', 'm', 'length'),
('Centimeter', 'cm', 'length');

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
('Tools & Equipment', 'Baking tools, utensils and small equipment'),
('Beverages', 'Coffee, tea, juices and other drinks'),
('Frozen Goods', 'Frozen fruits, vegetables and prepared items');

-- Insert 20+ users with different roles
INSERT INTO users (name, email, password_hash, role, is_active) VALUES
('System Administrator', 'admin@wellkenz.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', true),
('Inventory Manager', 'inventory@wellkenz.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'inventory', true),
('Purchasing Officer', 'purchasing@wellkenz.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'purchasing', true),
('Production Supervisor', 'supervisor@wellkenz.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'supervisor', true),
('Head Baker', 'baker1@wellkenz.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee', true),
('Assistant Baker', 'baker2@wellkenz.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee', true),
('Pastry Chef', 'pastry@wellkenz.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee', true),
('Store Manager', 'store@wellkenz.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'supervisor', true),
('Quality Control', 'quality@wellkenz.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'inventory', true),
('Sales Staff', 'sales@wellkenz.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee', true),
('Delivery Staff', 'delivery@wellkenz.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee', true),
('Cleaner', 'cleaner@wellkenz.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee', true);

-- Insert user profiles
INSERT INTO user_profiles (user_id, employee_id, phone, address, date_of_birth, department, position, salary) VALUES
(1, 'ADMIN001', '+63 912 345 6789', '123 Admin Street, Manila', '1985-03-15', 'Administration', 'System Administrator', 50000.00),
(2, 'INV001', '+63 912 345 6790', '456 Inventory Ave, Quezon City', '1990-07-22', 'Inventory', 'Inventory Manager', 35000.00),
(3, 'PUR001', '+63 912 345 6791', '789 Purchasing Rd, Makati', '1988-11-30', 'Purchasing', 'Purchasing Officer', 32000.00),
(4, 'SUP001', '+63 912 345 6792', '321 Supervisor Blvd, Taguig', '1987-05-14', 'Production', 'Production Supervisor', 38000.00),
(5, 'BAK001', '+63 912 345 6793', '654 Baker Lane, Pasig', '1992-09-08', 'Production', 'Head Baker', 28000.00),
(6, 'BAK002', '+63 912 345 6794', '987 Assistant St, Mandaluyong', '1993-12-25', 'Production', 'Assistant Baker', 22000.00);

-- Insert 30+ suppliers
INSERT INTO suppliers (supplier_code, name, contact_person, email, phone, address, city, payment_terms, rating, is_active) VALUES
('SUP001', 'Manila Flour Mills', 'Juan Dela Cruz', 'juan@manilaflour.com', '+63 2 123 4567', '100 Flour Mill Road', 'Manila', 30, 5, true),
('SUP002', 'Fresh Dairy Corp', 'Maria Santos', 'maria@freshdairy.com', '+63 2 234 5678', '200 Dairy Avenue', 'Quezon City', 45, 4, true),
('SUP003', 'Sweet Sugar Co', 'Pedro Reyes', 'pedro@sweetsugar.com', '+63 2 345 6789', '300 Sugar Lane', 'Makati', 30, 4, true),
('SUP004', 'Golden Grains Inc', 'Ana Lopez', 'ana@goldengrains.com', '+63 2 456 7890', '400 Grain Street', 'Taguig', 60, 3, true),
('SUP005', 'Pure Oils Philippines', 'Carlos Garcia', 'carlos@pureoils.com', '+63 2 567 8901', '500 Oil Boulevard', 'Pasig', 30, 5, true),
('SUP006', 'Spice Masters', 'Elena Torres', 'elena@spicemasters.com', '+63 2 678 9012', '600 Spice Road', 'Mandaluyong', 30, 4, true),
('SUP007', 'Nutty Delights', 'Roberto Lim', 'roberto@nutty.com', '+63 2 789 0123', '700 Nut Avenue', 'San Juan', 45, 4, true),
('SUP008', 'Fruit Paradise', 'Sofia Chen', 'sofia@fruitparadise.com', '+63 2 890 1234', '800 Fruit Street', 'Manila', 30, 3, true),
('SUP009', 'Packaging Pros', 'Michael Tan', 'michael@packagingpros.com', '+63 2 901 2345', '900 Packaging Lane', 'Quezon City', 60, 5, true),
('SUP010', 'Clean Solutions', 'Grace Wong', 'grace@cleansolutions.com', '+63 2 012 3456', '1000 Clean Road', 'Makati', 30, 4, true);

-- Insert 50+ items (raw materials, finished goods, supplies)
INSERT INTO items (item_code, name, description, category_id, unit_id, item_type, min_stock_level, max_stock_level, cost_price, selling_price, shelf_life_days, is_active) VALUES
-- Flour & Grains (10 items)
('FLR001', 'All-Purpose Flour', 'Premium all-purpose wheat flour', 1, 1, 'raw_material', 50.000, 500.000, 45.00, 0.00, 365, true),
('FLR002', 'Bread Flour', 'High protein bread flour', 1, 1, 'raw_material', 30.000, 300.000, 52.00, 0.00, 365, true),
('FLR003', 'Cake Flour', 'Fine cake flour for pastries', 1, 1, 'raw_material', 20.000, 200.000, 48.00, 0.00, 365, true),
('FLR004', 'Whole Wheat Flour', 'Organic whole wheat flour', 1, 1, 'raw_material', 15.000, 150.000, 65.00, 0.00, 180, true),
('FLR005', 'Rye Flour', 'Dark rye flour for specialty bread', 1, 1, 'raw_material', 10.000, 100.000, 75.00, 0.00, 180, true),
('FLR006', 'Corn Starch', 'Pure corn starch', 1, 1, 'raw_material', 5.000, 50.000, 40.00, 0.00, 730, true),
('FLR007', 'Rice Flour', 'Gluten-free rice flour', 1, 1, 'raw_material', 8.000, 80.000, 55.00, 0.00, 365, true),
('FLR008', 'Oat Flour', 'Healthy oat flour', 1, 1, 'raw_material', 12.000, 120.000, 60.00, 0.00, 180, true),
('FLR009', 'Semolina', 'Durum wheat semolina', 1, 1, 'raw_material', 8.000, 80.000, 58.00, 0.00, 365, true),
('FLR010', 'Buckwheat Flour', 'Gluten-free buckwheat flour', 1, 1, 'raw_material', 5.000, 50.000, 85.00, 0.00, 180, true),

-- Dairy Products (8 items)
('DRY001', 'Fresh Milk', 'Full cream fresh milk', 2, 5, 'raw_material', 20.000, 200.000, 65.00, 0.00, 7, true),
('DRY002', 'Butter Unsalted', 'Premium unsalted butter', 2, 1, 'raw_material', 15.000, 150.000, 320.00, 0.00, 30, true),
('DRY003', 'Butter Salted', 'Salted butter for cooking', 2, 1, 'raw_material', 10.000, 100.000, 310.00, 0.00, 30, true),
('DRY004', 'Heavy Cream', '35% fat heavy cream', 2, 5, 'raw_material', 8.000, 80.000, 180.00, 0.00, 14, true),
('DRY005', 'Cream Cheese', 'Philadelphia style cream cheese', 2, 1, 'raw_material', 12.000, 120.000, 280.00, 0.00, 21, true),
('DRY006', 'Yogurt Plain', 'Natural plain yogurt', 2, 5, 'raw_material', 15.000, 150.000, 95.00, 0.00, 14, true),
('DRY007', 'Buttermilk', 'Cultured buttermilk', 2, 5, 'raw_material', 8.000, 80.000, 75.00, 0.00, 14, true),
('DRY008', 'Mozzarella Cheese', 'Shredded mozzarella cheese', 2, 1, 'raw_material', 10.000, 100.000, 340.00, 0.00, 21, true),

-- Sweeteners (6 items)
('SWT001', 'White Sugar', 'Refined white sugar', 3, 1, 'raw_material', 40.000, 400.000, 55.00, 0.00, 730, true),
('SWT002', 'Brown Sugar', 'Dark brown sugar', 3, 1, 'raw_material', 20.000, 200.000, 65.00, 0.00, 365, true),
('SWT003', 'Powdered Sugar', 'Confectioners sugar', 3, 1, 'raw_material', 15.000, 150.000, 70.00, 0.00, 365, true),
('SWT004', 'Honey', 'Pure natural honey', 3, 5, 'raw_material', 8.000, 80.000, 250.00, 0.00, 365, true),
('SWT005', 'Maple Syrup', 'Grade A maple syrup', 3, 5, 'raw_material', 5.000, 50.000, 450.00, 0.00, 365, true),
('SWT006', 'Corn Syrup', 'Light corn syrup', 3, 5, 'raw_material', 10.000, 100.000, 120.00, 0.00, 365, true),

-- Finished Products (15 items)
('FP001', 'Classic White Bread', 'Fresh white sandwich bread', 12, 10, 'finished_good', 10.000, 100.000, 35.00, 65.00, 3, true),
('FP002', 'Whole Wheat Bread', 'Healthy whole wheat bread', 12, 10, 'finished_good', 8.000, 80.000, 42.00, 75.00, 3, true),
('FP003', 'French Baguette', 'Traditional French baguette', 12, 10, 'finished_good', 15.000, 150.000, 28.00, 55.00, 1, true),
('FP004', 'Croissant', 'Buttery French croissant', 12, 10, 'finished_good', 20.000, 200.000, 25.00, 45.00, 2, true),
('FP005', 'Chocolate Chip Cookie', 'Classic chocolate chip cookie', 12, 10, 'finished_good', 30.000, 300.000, 12.00, 25.00, 7, true),
('FP006', 'Blueberry Muffin', 'Fresh blueberry muffin', 12, 10, 'finished_good', 25.000, 250.000, 18.00, 35.00, 3, true),
('FP007', 'Cheesecake Slice', 'New York style cheesecake', 12, 10, 'finished_good', 12.000, 120.000, 45.00, 85.00, 5, true),
('FP008', 'Apple Pie', 'Homemade apple pie', 12, 10, 'finished_good', 8.000, 80.000, 120.00, 220.00, 4, true),
('FP009', 'Cinnamon Roll', 'Cream cheese frosted cinnamon roll', 12, 10, 'finished_good', 18.000, 180.000, 22.00, 42.00, 3, true),
('FP010', 'Bagel Plain', 'New York style plain bagel', 12, 10, 'finished_good', 15.000, 150.000, 15.00, 28.00, 2, true),
('FP011', 'Donut Glazed', 'Classic glazed donut', 12, 10, 'finished_good', 25.000, 250.000, 10.00, 20.00, 2, true),
('FP012', 'Brownie', 'Fudgy chocolate brownie', 12, 10, 'finished_good', 20.000, 200.000, 16.00, 30.00, 5, true),
('FP013', 'Pandesal', 'Traditional Filipino bread roll', 12, 11, 'finished_good', 50.000, 500.000, 5.00, 10.00, 1, true),
('FP014', 'Ensaimada', 'Sweet Filipino pastry', 12, 10, 'finished_good', 15.000, 150.000, 20.00, 38.00, 3, true),
('FP015', 'Pan de Coco', 'Coconut filled bread', 12, 10, 'finished_good', 12.000, 120.000, 8.00, 15.00, 2, true),

-- Packaging Materials (6 items)
('PKG001', 'Bread Bag Small', 'Small plastic bread bags', 10, 10, 'supply', 200.000, 2000.000, 0.50, 0.00, 0, true),
('PKG002', 'Bread Bag Large', 'Large plastic bread bags', 10, 10, 'supply', 150.000, 1500.000, 0.75, 0.00, 0, true),
('PKG003', 'Pastry Box Small', 'Small pastry boxes', 10, 10, 'supply', 100.000, 1000.000, 3.50, 0.00, 0, true),
('PKG004', 'Pastry Box Large', 'Large pastry boxes', 10, 10, 'supply', 80.000, 800.000, 5.00, 0.00, 0, true),
('PKG005', 'Cake Box', 'Specialty cake boxes', 10, 10, 'supply', 50.000, 500.000, 8.00, 0.00, 0, true),
('PKG006', 'Paper Bag', 'Brown paper bags', 10, 10, 'supply', 300.000, 3000.000, 1.20, 0.00, 0, true),

-- Cleaning Supplies (5 items)
('CLN001', 'Food Safe Sanitizer', 'Food contact surface sanitizer', 11, 5, 'supply', 5.000, 50.000, 180.00, 0.00, 0, true),
('CLN002', 'Floor Cleaner', 'Industrial floor cleaner', 11, 5, 'supply', 3.000, 30.000, 220.00, 0.00, 0, true),
('CLN003', 'Dish Soap', 'Food safe dish soap', 11, 5, 'supply', 8.000, 80.000, 95.00, 0.00, 0, true),
('CLN004', 'Hand Soap', 'Antibacterial hand soap', 11, 5, 'supply', 6.000, 60.000, 120.00, 0.00, 0, true),
('CLN005', 'Disposable Gloves', 'Food service disposable gloves', 11, 12, 'supply', 10.000, 100.000, 350.00, 0.00, 0, true);

-- Insert supplier items (pricing information)
INSERT INTO supplier_items (supplier_id, item_id, unit_price, minimum_order_quantity, lead_time_days, is_preferred) VALUES
(1, 1, 42.00, 25.000, 2, true),
(1, 2, 49.00, 25.000, 2, true),
(1, 3, 45.00, 20.000, 2, true),
(4, 4, 62.00, 15.000, 3, true),
(4, 5, 72.00, 10.000, 3, true),
(2, 11, 60.00, 10.000, 1, true),
(2, 12, 300.00, 5.000, 1, true),
(2, 13, 290.00, 5.000, 1, true),
(3, 19, 52.00, 20.000, 2, true),
(3, 20, 60.00, 15.000, 2, true),
(3, 21, 65.00, 10.000, 2, true);

-- Insert current stock levels
INSERT INTO current_stock (item_id, current_quantity, average_cost) VALUES
(1, 150.500, 45.00),
(2, 85.250, 52.00),
(3, 45.750, 48.00),
(4, 22.000, 65.00),
(5, 15.500, 75.00),
(11, 35.250, 65.00),
(12, 28.750, 320.00),
(13, 18.500, 310.00),
(19, 120.000, 55.00),
(20, 65.500, 65.00),
(21, 42.250, 70.00);

-- Insert sample batches
INSERT INTO batches (batch_number, item_id, quantity, unit_cost, manufacturing_date, expiry_date, supplier_id, status) VALUES
('BATCH-FLR-001', 1, 50.000, 45.00, '2024-01-15', '2025-01-15', 1, 'active'),
('BATCH-FLR-002', 2, 25.000, 52.00, '2024-01-10', '2025-01-10', 1, 'active'),
('BATCH-DRY-001', 11, 20.000, 65.00, '2024-01-18', '2024-01-25', 2, 'active'),
('BATCH-SWT-001', 19, 40.000, 55.00, '2024-01-05', '2026-01-05', 3, 'active');

-- Insert sample stock movements
INSERT INTO stock_movements (item_id, movement_type, quantity, unit_cost, user_id, notes) VALUES
(1, 'purchase', 50.000, 45.00, 3, 'Initial stock purchase'),
(2, 'purchase', 25.000, 52.00, 3, 'Bread flour purchase'),
(11, 'purchase', 20.000, 65.00, 3, 'Milk delivery'),
(19, 'purchase', 40.000, 55.00, 3, 'Sugar restock');

-- Insert sample recipes
INSERT INTO recipes (recipe_code, name, description, finished_item_id, yield_quantity, yield_unit_id, preparation_time, cooking_time, is_active, created_by) VALUES
('REC-001', 'Classic White Bread', 'Traditional white sandwich bread', 26, 2.000, 1, 120, 45, true, 5),
('REC-002', 'Chocolate Chip Cookies', 'Classic chocolate chip cookies', 30, 24.000, 10, 30, 12, true, 5),
('REC-003', 'Blueberry Muffins', 'Fresh blueberry muffins', 31, 12.000, 10, 25, 20, true, 7),
('REC-004', 'Pandesal', 'Filipino bread rolls', 38, 36.000, 10, 90, 15, true, 5);

-- Insert recipe ingredients
INSERT INTO recipe_ingredients (recipe_id, item_id, quantity_required, unit_id) VALUES
-- Classic White Bread
(1, 1, 1.000, 1),   -- 1kg flour
(1, 19, 0.050, 1),  -- 50g sugar
(1, 11, 0.600, 5),  -- 600ml milk
(1, 12, 0.050, 1),  -- 50g butter
(1, 22, 0.025, 1),  -- 25g yeast

-- Chocolate Chip Cookies
(2, 1, 0.500, 1),   -- 500g flour
(2, 19, 0.200, 1),  -- 200g sugar
(2, 12, 0.250, 1),  -- 250g butter
(2, 24, 0.150, 1),  -- 150g chocolate chips

-- Blueberry Muffins
(3, 1, 0.400, 1),   -- 400g flour
(3, 19, 0.150, 1),  -- 150g sugar
(3, 11, 0.240, 5),  -- 240ml milk
(3, 12, 0.100, 1),  -- 100g butter
(3, 23, 0.200, 1);  -- 200g blueberries

-- Insert sample production orders
INSERT INTO production_orders (production_number, recipe_id, planned_quantity, unit_id, planned_start_date, planned_end_date, status, created_by) VALUES
('PROD-001', 1, 10.000, 1, '2024-01-20', '2024-01-20', 'completed', 4),
('PROD-002', 2, 5.000, 1, '2024-01-21', '2024-01-21', 'in_progress', 4),
('PROD-003', 3, 3.000, 1, '2024-01-22', '2024-01-22', 'planned', 4);

-- Insert sample purchase requests
INSERT INTO purchase_requests (pr_number, request_date, requested_by, department, priority, status, total_estimated_cost) VALUES
('PR-001', '2024-01-18', 2, 'Inventory', 'high', 'approved', 5000.00),
('PR-002', '2024-01-19', 2, 'Inventory', 'normal', 'pending', 2500.00);

-- Insert sample purchase request items
INSERT INTO purchase_request_items (purchase_request_id, item_id, quantity_requested, unit_price_estimate, total_estimated_cost) VALUES
(1, 1, 100.000, 45.00, 4500.00),
(1, 2, 50.000, 52.00, 2600.00),
(2, 11, 30.000, 65.00, 1950.00);

-- Insert sample purchase orders
INSERT INTO purchase_orders (po_number, supplier_id, order_date, expected_delivery_date, status, total_amount, created_by) VALUES
('PO-001', 1, '2024-01-18', '2024-01-20', 'confirmed', 7100.00, 3),
('PO-002', 2, '2024-01-19', '2024-01-21', 'sent', 1950.00, 3);

-- Insert sample purchase order items
INSERT INTO purchase_order_items (purchase_order_id, item_id, quantity_ordered, unit_price, total_price) VALUES
(1, 1, 100.000, 45.00, 4500.00),
(1, 2, 50.000, 52.00, 2600.00),
(2, 11, 30.000, 65.00, 1950.00);

-- Insert sample requisitions
INSERT INTO requisitions (requisition_number, request_date, requested_by, department, purpose, status) VALUES
('REQ-001', '2024-01-19', 5, 'Production', 'Daily baking supplies', 'approved'),
('REQ-002', '2024-01-19', 7, 'Pastry', 'Special order ingredients', 'pending');

-- Insert sample requisition items
INSERT INTO requisition_items (requisition_id, item_id, quantity_requested, unit_cost_estimate) VALUES
(1, 1, 25.000, 45.00),
(1, 19, 5.000, 55.00),
(1, 12, 2.000, 320.00),
(2, 23, 3.000, 200.00),
(2, 24, 2.000, 350.00);

-- Insert sample notifications
INSERT INTO notifications (user_id, title, message, type, priority, is_read) VALUES
(2, 'Low Stock Alert', 'All-Purpose Flour is below minimum stock level', 'inventory', 'high', false),
(3, 'Purchase Order Approved', 'PO-001 has been approved and sent to supplier', 'purchasing', 'normal', false),
(4, 'Production Complete', 'Production order PROD-001 has been completed', 'production', 'normal', false);

-- Insert sample audit logs
INSERT INTO audit_logs (table_name, record_id, action, user_id, created_at) VALUES
('users', 1, 'CREATE', 1, '2024-01-15 08:00:00'),
('items', 1, 'CREATE', 2, '2024-01-15 09:30:00'),
('stock_movements', 1, 'CREATE', 3, '2024-01-16 10:15:00');

-- ============================================================================
-- COMPLETION MESSAGE
-- ============================================================================
DO $$ 
BEGIN
    RAISE NOTICE '=========================================================';
    RAISE NOTICE 'WellKenz Bakery ERP Database Schema created successfully!';
    RAISE NOTICE '=========================================================';
    RAISE NOTICE 'Statistics:';
    RAISE NOTICE '- 12 users with different roles created';
    RAISE NOTICE '- 15 categories for product organization';
    RAISE NOTICE '- 15 measurement units defined';
    RAISE NOTICE '- 50+ items (raw materials, finished goods, supplies)';
    RAISE NOTICE '- 10 suppliers with contact information';
    RAISE NOTICE '- Sample pricing data in supplier_items';
    RAISE NOTICE '- Current stock levels for main ingredients';
    RAISE NOTICE '- Production recipes with ingredients';
    RAISE NOTICE '- Sample production orders';
    RAISE NOTICE '- Purchase requests and orders';
    RAISE NOTICE '- Inventory requisitions';
    RAISE NOTICE '- Notifications and audit logs';
    RAISE NOTICE '';
    RAISE NOTICE 'Admin Login: admin@wellkenz.com / password';
    RAISE NOTICE 'Sample data ready for testing and demonstration';
    RAISE NOTICE '=========================================================';
END $$;

SELECT 'Database setup complete. WellKenz Bakery ERP is ready for use!' AS completion_message;

Note: dont touch the database