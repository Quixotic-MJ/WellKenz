-- ============================================================================
-- SIMPLIFIED RTV TABLES FOR WELLKENZ BAKERY ERP
-- ============================================================================

-- Simple table to track return transactions
CREATE TABLE rtv_transactions (
    id SERIAL PRIMARY KEY,
    rtv_number VARCHAR(50) NOT NULL UNIQUE,
    purchase_order_id INTEGER REFERENCES purchase_orders(id),
    supplier_id INTEGER REFERENCES suppliers(id),
    return_date DATE NOT NULL DEFAULT CURRENT_DATE,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    total_value DECIMAL(12,2) DEFAULT 0.00,
    notes TEXT,
    created_by INTEGER REFERENCES users(id),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Items being returned in each RTV transaction
CREATE TABLE rtv_items (
    id SERIAL PRIMARY KEY,
    rtv_id INTEGER NOT NULL REFERENCES rtv_transactions(id) ON DELETE CASCADE,
    item_id INTEGER NOT NULL REFERENCES items(id),
    quantity_returned DECIMAL(10,3) NOT NULL,
    unit_cost DECIMAL(10,2) NOT NULL,
    reason TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Track supplier credits received
CREATE TABLE supplier_credits (
    id SERIAL PRIMARY KEY,
    rtv_id INTEGER NOT NULL REFERENCES rtv_transactions(id),
    supplier_id INTEGER NOT NULL REFERENCES suppliers(id),
    credit_number VARCHAR(100),
    credit_amount DECIMAL(12,2) NOT NULL,
    received_date DATE,
    status VARCHAR(20) DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Simple reason codes for returns
CREATE TABLE return_reasons (
    id SERIAL PRIMARY KEY,
    code VARCHAR(10) NOT NULL UNIQUE,
    description VARCHAR(100) NOT NULL,
    is_active BOOLEAN DEFAULT true
);

-- Insert default return reasons
INSERT INTO return_reasons (code, description) VALUES
('DAMAGED', 'Damaged Goods'),
('EXPIRED', 'Expired Goods'),
('NEAR_EXPIRY', 'Near Expiry Date'),
('WRONG_ITEM', 'Wrong Item Delivered'),
('SHORT_QTY', 'Short Quantity'),
('QUALITY', 'Quality Issues'),
('OTHER', 'Other');

-- Create indexes for performance
CREATE INDEX idx_rtv_transactions_po ON rtv_transactions(purchase_order_id);
CREATE INDEX idx_rtv_transactions_supplier ON rtv_transactions(supplier_id);
CREATE INDEX idx_rtv_transactions_date ON rtv_transactions(return_date);
CREATE INDEX idx_rtv_items_rtv ON rtv_items(rtv_id);
CREATE INDEX idx_rtv_items_item ON rtv_items(item_id);
CREATE INDEX idx_supplier_credits_rtv ON supplier_credits(rtv_id);
CREATE INDEX idx_supplier_credits_supplier ON supplier_credits(supplier_id);