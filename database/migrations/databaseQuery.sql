/* ============================================================
   0. Clean-up (idempotent – harmless if objects don’t exist)
   ============================================================ */
DROP TABLE IF EXISTS
    memos,
    acknowledge_receipts,
    inventory_transactions,
    purchase_items,
    purchase_orders,
    requisition_items,
    requisitions,
    item_requests,
    sessions,
    notifications,
    items,
    categories,
    suppliers,
    users
CASCADE;

DROP FUNCTION IF EXISTS
    create_user,
    update_user,
    delete_user,
    get_user_by_id,
    get_all_users,
    change_user_password,
    toggle_user_status,
    create_item,
    update_item,
    delete_item,
    get_item_by_id,
    get_all_items,
    update_item_stock,
    get_low_stock_items,
    get_expiry_alerts,
    stock_in_summary
CASCADE;

DROP TYPE IF EXISTS
    user_role,
    user_status,
    req_status,
    req_priority,
    req_item_status,
    po_status,
    ar_status,
    trans_type
CASCADE;

/* ============================================================
   1. ENUMERATED TYPES
   ============================================================ */
CREATE TYPE user_role       AS ENUM ('admin','employee','inventory','purchasing','supervisor');
CREATE TYPE user_status     AS ENUM ('active','inactive');
CREATE TYPE req_status      AS ENUM ('pending','approved','rejected','completed');
CREATE TYPE req_priority    AS ENUM ('low','medium','high');
CREATE TYPE req_item_status AS ENUM ('pending','partially_fulfilled','fulfilled');
CREATE TYPE po_status       AS ENUM ('draft','ordered','delivered','cancelled');
CREATE TYPE ar_status       AS ENUM ('issued','received','cancelled');
CREATE TYPE trans_type      AS ENUM ('in','out','adjustment');

/* ============================================================
   2. TABLES
   ============================================================ */
CREATE TABLE sessions (
    id            VARCHAR(255) PRIMARY KEY,
    user_id       INTEGER      NULL,
    ip_address    VARCHAR(45)  NULL,
    user_agent    TEXT         NULL,
    payload       TEXT         NOT NULL,
    last_activity INTEGER      NOT NULL
);
CREATE INDEX idx_sessions_user_id       ON sessions(user_id);
CREATE INDEX idx_sessions_last_activity ON sessions(last_activity);

CREATE TABLE categories (
    cat_id    SERIAL PRIMARY KEY,
    cat_name  VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE suppliers (
    sup_id          SERIAL PRIMARY KEY,
    sup_name        VARCHAR(255) NOT NULL,
    sup_email       VARCHAR(255) NULL,
    sup_address     TEXT         NULL,
    contact_person  VARCHAR(255) NULL,
    contact_number  VARCHAR(255) NULL,
    sup_status      VARCHAR(255) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE users (
    user_id  SERIAL PRIMARY KEY,
    username VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role     user_role DEFAULT 'employee',
    name     VARCHAR(255) NOT NULL,
    position VARCHAR(255) NOT NULL,
    email    VARCHAR(255) UNIQUE NOT NULL,
    contact  VARCHAR(255) NOT NULL,
    status   user_status DEFAULT 'active',
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE items (
    item_id           SERIAL PRIMARY KEY,
    item_code         VARCHAR(255) UNIQUE NOT NULL,
    item_name         VARCHAR(255) NOT NULL,
    item_description  TEXT NULL,
    item_unit         VARCHAR(255) NOT NULL,
    cat_id            INTEGER REFERENCES categories(cat_id),
    item_stock        DECIMAL(12,3) DEFAULT 0,
    item_expire_date  DATE NULL,
    last_updated      TIMESTAMP DEFAULT NOW(),
    reorder_level     DECIMAL(12,3) DEFAULT 0,
    min_stock_level   DECIMAL(12,3) DEFAULT 0,
    max_stock_level   DECIMAL(12,3) NULL,
    is_active         BOOLEAN DEFAULT TRUE,
    is_custom         BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW(),
    deleted_at TIMESTAMP NULL
);
CREATE INDEX idx_items_stock_levels ON items(item_stock, reorder_level);
CREATE INDEX idx_items_expire       ON items(item_expire_date);
CREATE INDEX idx_items_active       ON items(is_active);
CREATE INDEX idx_items_custom       ON items(is_custom);

CREATE TABLE item_requests (
    item_req_id        SERIAL PRIMARY KEY,
    item_req_name      VARCHAR(255) NOT NULL,
    item_req_unit      VARCHAR(255) NOT NULL,
    item_req_quantity  INTEGER NOT NULL,
    item_req_description TEXT NULL,
    item_req_status    VARCHAR(255) DEFAULT 'pending',
    requested_by       INTEGER REFERENCES users(user_id),
    approved_by        INTEGER REFERENCES users(user_id),
    item_req_reject_reason TEXT NULL,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);
CREATE INDEX idx_ir_status    ON item_requests(item_req_status);
CREATE INDEX idx_ir_requested ON item_requests(requested_by);
CREATE INDEX idx_ir_approved  ON item_requests(approved_by);
CREATE INDEX idx_ir_created   ON item_requests(created_at);

CREATE TABLE requisitions (
    req_id            SERIAL PRIMARY KEY,
    req_ref           VARCHAR(255) UNIQUE NOT NULL,
    req_purpose       TEXT NOT NULL,
    req_priority      req_priority DEFAULT 'medium',
    req_status        req_status DEFAULT 'pending',
    req_date          DATE NOT NULL,
    approved_date     DATE NULL,
    req_reject_reason VARCHAR(255) NULL,
    requested_by      INTEGER REFERENCES users(user_id),
    approved_by       INTEGER REFERENCES users(user_id),
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);
CREATE INDEX idx_req_status    ON requisitions(req_status);
CREATE INDEX idx_req_priority  ON requisitions(req_priority);
CREATE INDEX idx_req_date      ON requisitions(req_date);
CREATE INDEX idx_req_requested ON requisitions(requested_by);

CREATE TABLE requisition_items (
    req_item_id       SERIAL PRIMARY KEY,
    req_item_quantity INTEGER NOT NULL,
    req_item_status   req_item_status DEFAULT 'pending',
    item_unit         VARCHAR(255) NOT NULL,
    req_id            INTEGER REFERENCES requisitions(req_id) ON DELETE CASCADE,
    item_id           INTEGER REFERENCES items(item_id),
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);
CREATE INDEX idx_ri_req    ON requisition_items(req_id);
CREATE INDEX idx_ri_item   ON requisition_items(item_id);
CREATE INDEX idx_ri_status ON requisition_items(req_item_status);

CREATE TABLE purchase_orders (
    po_id                  SERIAL PRIMARY KEY,
    po_ref                 VARCHAR(255) UNIQUE NOT NULL,
    po_status              po_status DEFAULT 'draft',
    order_date             DATE NOT NULL,
    delivery_address       TEXT NOT NULL,
    expected_delivery_date DATE NULL,
    total_amount           DECIMAL(10,2) DEFAULT 0,
    sup_id                 INTEGER REFERENCES suppliers(sup_id),
    req_id                 INTEGER REFERENCES requisitions(req_id),
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE purchase_items (
    pi_id         SERIAL PRIMARY KEY,
    pi_quantity   INTEGER NOT NULL,
    pi_unit_price DECIMAL(10,2) NOT NULL,
    pi_subtotal   DECIMAL(10,2) NOT NULL,
    po_id         INTEGER REFERENCES purchase_orders(po_id),
    item_id       INTEGER REFERENCES items(item_id),
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE inventory_transactions (
    trans_id       SERIAL PRIMARY KEY,
    trans_ref      VARCHAR(255) UNIQUE NOT NULL,
    trans_type     trans_type NOT NULL,
    trans_quantity INTEGER NOT NULL,
    trans_date     DATE NOT NULL,
    trans_remarks  TEXT NULL,
    po_id          INTEGER REFERENCES purchase_orders(po_id),
    trans_by       INTEGER REFERENCES users(user_id),
    item_id        INTEGER REFERENCES items(item_id),
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE acknowledge_receipts (
    ar_id        SERIAL PRIMARY KEY,
    ar_ref       VARCHAR(255) UNIQUE NOT NULL,
    ar_remarks   TEXT NULL,
    ar_status    ar_status DEFAULT 'issued',
    issued_date  DATE NOT NULL,
    req_id       INTEGER REFERENCES requisitions(req_id),
    issued_by    INTEGER REFERENCES users(user_id),
    issued_to    INTEGER REFERENCES users(user_id),
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE memos (
    memo_id       SERIAL PRIMARY KEY,
    memo_ref      VARCHAR(255) UNIQUE NOT NULL,
    memo_remarks  TEXT NULL,
    received_date DATE NOT NULL,
    received_by   INTEGER REFERENCES users(user_id),
    po_ref        VARCHAR(255) REFERENCES purchase_orders(po_ref),
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE notifications (
    notif_id      SERIAL PRIMARY KEY,
    notif_title   VARCHAR(255) NOT NULL,
    notif_content TEXT NOT NULL,
    related_id    VARCHAR(255) NULL,
    related_type  VARCHAR(255) NULL,
    is_read       BOOLEAN DEFAULT FALSE,
    user_id       INTEGER REFERENCES users(user_id),
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

/* ============================================================
   3. USER CRUD FUNCTIONS
   ============================================================ */
CREATE OR REPLACE FUNCTION create_user(
    p_username VARCHAR,
    p_password VARCHAR,
    p_role     VARCHAR,
    p_name     VARCHAR,
    p_position VARCHAR,
    p_email    VARCHAR,
    p_contact  VARCHAR,
    p_status   VARCHAR DEFAULT 'active'
) RETURNS JSON AS $$
DECLARE new_user_id INTEGER;
BEGIN
    IF EXISTS (SELECT 1 FROM users WHERE username = p_username) THEN
        RETURN json_build_object('success',false,'message','Username already exists');
    END IF;
    IF EXISTS (SELECT 1 FROM users WHERE email = p_email) THEN
        RETURN json_build_object('success',false,'message','Email already exists');
    END IF;

    INSERT INTO users (username,password,role,name,"position",email,contact,status,created_at,updated_at)
    VALUES (p_username,p_password,p_role,p_name,p_position,p_email,p_contact,p_status, NOW(), NOW())
    RETURNING user_id INTO new_user_id;

    RETURN json_build_object('success',true,'message','User created successfully','user_id',new_user_id);
EXCEPTION WHEN OTHERS THEN
    RETURN json_build_object('success',false,'message',SQLERRM);
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION update_user(
    p_user_id INTEGER,
    p_username VARCHAR,
    p_role     VARCHAR,
    p_name     VARCHAR,
    p_position VARCHAR,
    p_email    VARCHAR,
    p_contact  VARCHAR
) RETURNS JSON AS $$
BEGIN
    IF EXISTS (SELECT 1 FROM users WHERE username=p_username AND user_id<>p_user_id) THEN
        RETURN json_build_object('success',false,'message','Username already exists');
    END IF;
    IF EXISTS (SELECT 1 FROM users WHERE email=p_email AND user_id<>p_user_id) THEN
        RETURN json_build_object('success',false,'message','Email already exists');
    END IF;

    UPDATE users
    SET username=p_username, role=p_role, name=p_name, "position"=p_position,
        email=p_email, contact=p_contact, updated_at=NOW()
    WHERE user_id=p_user_id;

    IF NOT FOUND THEN
        RETURN json_build_object('success',false,'message','User not found');
    END IF;
    RETURN json_build_object('success',true,'message','User updated successfully');
EXCEPTION WHEN OTHERS THEN
    RETURN json_build_object('success',false,'message',SQLERRM);
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION delete_user(p_user_id INTEGER) RETURNS JSON AS $$
BEGIN
    DELETE FROM users WHERE user_id=p_user_id;
    IF NOT FOUND THEN
        RETURN json_build_object('success',false,'message','User not found');
    END IF;
    RETURN json_build_object('success',true,'message','User deleted successfully');
EXCEPTION WHEN OTHERS THEN
    RETURN json_build_object('success',false,'message',SQLERRM);
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION get_user_by_id(p_user_id INTEGER)
RETURNS TABLE (
    user_id INTEGER, username VARCHAR, role VARCHAR, name VARCHAR,
    "position" VARCHAR, email VARCHAR, contact VARCHAR, status VARCHAR,
    created_at TIMESTAMP, updated_at TIMESTAMP
) AS $$
BEGIN
    RETURN QUERY
    SELECT u.user_id, u.username, u.role::VARCHAR, u.name, u."position",
           u.email, u.contact, u.status::VARCHAR, u.created_at, u.updated_at
    FROM users u WHERE u.user_id=p_user_id;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION get_all_users()
RETURNS TABLE (
    user_id INTEGER, username VARCHAR, role VARCHAR, name VARCHAR,
    "position" VARCHAR, email VARCHAR, contact VARCHAR, status VARCHAR,
    created_at TIMESTAMP, updated_at TIMESTAMP
) AS $$
BEGIN
    RETURN QUERY
    SELECT u.user_id, u.username, u.role::VARCHAR, u.name, u."position",
           u.email, u.contact, u.status::VARCHAR, u.created_at, u.updated_at
    FROM users u ORDER BY u.created_at DESC;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION change_user_password(p_user_id INTEGER, p_new_password VARCHAR)
RETURNS JSON AS $$
BEGIN
    UPDATE users SET password=p_new_password, updated_at=NOW() WHERE user_id=p_user_id;
    IF NOT FOUND THEN
        RETURN json_build_object('success',false,'message','User not found');
    END IF;
    RETURN json_build_object('success',true,'message','Password updated successfully');
EXCEPTION WHEN OTHERS THEN
    RETURN json_build_object('success',false,'message',SQLERRM);
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION toggle_user_status(p_user_id INTEGER) RETURNS JSON AS $$
DECLARE
    current_status user_status;
    new_status     user_status;
BEGIN
    SELECT status INTO current_status FROM users WHERE user_id=p_user_id;
    IF current_status IS NULL THEN
        RETURN json_build_object('success',false,'message','User not found');
    END IF;

    new_status := CASE WHEN current_status='active' THEN 'inactive' ELSE 'active' END;
    UPDATE users SET status=new_status, updated_at=NOW() WHERE user_id=p_user_id;
    RETURN json_build_object('success',true,'message','User status changed to '||new_status::VARCHAR,'new_status',new_status::VARCHAR);
EXCEPTION WHEN OTHERS THEN
    RETURN json_build_object('success',false,'message',SQLERRM);
END;
$$ LANGUAGE plpgsql;

/* ============================================================
   4. ITEM CRUD / STOCK FUNCTIONS
   ============================================================ */
CREATE OR REPLACE FUNCTION create_item(
    p_item_code         VARCHAR,
    p_item_name         VARCHAR,
    p_item_description  TEXT,
    p_item_unit         VARCHAR,
    p_cat_id            INTEGER,
    p_item_stock        DECIMAL DEFAULT 0,
    p_item_expire_date  DATE    DEFAULT NULL,
    p_reorder_level     DECIMAL DEFAULT 0,
    p_min_stock_level   DECIMAL DEFAULT 0,
    p_max_stock_level   DECIMAL DEFAULT NULL,
    p_is_custom         BOOLEAN DEFAULT FALSE
) RETURNS JSON AS $$
DECLARE new_item_id INTEGER;
BEGIN
    IF EXISTS (SELECT 1 FROM items WHERE item_code=p_item_code) THEN
        RETURN json_build_object('success',false,'message','Item code already exists');
    END IF;
    IF NOT EXISTS (SELECT 1 FROM categories WHERE cat_id=p_cat_id) THEN
        RETURN json_build_object('success',false,'message','Category does not exist');
    END IF;

    INSERT INTO items (
        item_code,item_name,item_description,item_unit,cat_id,
        item_stock,item_expire_date,reorder_level,min_stock_level,max_stock_level,
        is_custom,last_updated,created_at,updated_at
    ) VALUES (
        p_item_code,p_item_name,p_item_description,p_item_unit,p_cat_id,
        p_item_stock,p_item_expire_date,p_reorder_level,p_min_stock_level,p_max_stock_level,
        p_is_custom,NOW(),NOW(),NOW()
    ) RETURNING item_id INTO new_item_id;

    RETURN json_build_object('success',true,'message','Item created successfully','item_id',new_item_id);
EXCEPTION WHEN OTHERS THEN
    RETURN json_build_object('success',false,'message',SQLERRM);
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION update_item(
    p_item_id           INTEGER,
    p_item_code         VARCHAR,
    p_item_name         VARCHAR,
    p_item_description  TEXT,
    p_item_unit         VARCHAR,
    p_cat_id            INTEGER,
    p_item_stock        DECIMAL,
    p_item_expire_date  DATE,
    p_reorder_level     DECIMAL,
    p_min_stock_level   DECIMAL,
    p_max_stock_level   DECIMAL,
    p_is_custom         BOOLEAN
) RETURNS JSON AS $$
BEGIN
    IF EXISTS (SELECT 1 FROM items WHERE item_code=p_item_code AND item_id<>p_item_id) THEN
        RETURN json_build_object('success',false,'message','Item code already exists');
    END IF;
    IF NOT EXISTS (SELECT 1 FROM categories WHERE cat_id=p_cat_id) THEN
        RETURN json_build_object('success',false,'message','Category does not exist');
    END IF;

    UPDATE items
    SET
        item_code=p_item_code,item_name=p_item_name,item_description=p_item_description,
        item_unit=p_item_unit,cat_id=p_cat_id,item_stock=p_item_stock,
        item_expire_date=p_item_expire_date,reorder_level=p_reorder_level,
        min_stock_level=p_min_stock_level,max_stock_level=p_max_stock_level,
        is_custom=p_is_custom,last_updated=NOW(),updated_at=NOW()
    WHERE item_id=p_item_id;

    IF NOT FOUND THEN
        RETURN json_build_object('success',false,'message','Item not found');
    END IF;
    RETURN json_build_object('success',true,'message','Item updated successfully');
EXCEPTION WHEN OTHERS THEN
    RETURN json_build_object('success',false,'message',SQLERRM);
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION delete_item(p_item_id INTEGER) RETURNS JSON AS $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM items WHERE item_id=p_item_id) THEN
        RETURN json_build_object('success',false,'message','Item not found');
    END IF;
    IF EXISTS (SELECT 1 FROM inventory_transactions WHERE item_id=p_item_id) THEN
        RETURN json_build_object('success',false,'message','Cannot delete item with existing inventory transactions');
    END IF;

    DELETE FROM items WHERE item_id=p_item_id;
    RETURN json_build_object('success',true,'message','Item deleted successfully');
EXCEPTION WHEN OTHERS THEN
    RETURN json_build_object('success',false,'message',SQLERRM);
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION get_item_by_id(p_item_id INTEGER)
RETURNS TABLE (
    item_id INTEGER, item_code VARCHAR, item_name VARCHAR, item_description TEXT,
    item_unit VARCHAR, cat_id INTEGER, item_stock DECIMAL, item_expire_date DATE,
    last_updated TIMESTAMP, reorder_level DECIMAL, min_stock_level DECIMAL,
    max_stock_level DECIMAL, is_custom BOOLEAN, created_at TIMESTAMP, updated_at TIMESTAMP
) AS $$
BEGIN
    RETURN QUERY
    SELECT
        i.item_id,i.item_code,i.item_name,i.item_description,i.item_unit,
        i.cat_id,i.item_stock,i.item_expire_date,i.last_updated,
        i.reorder_level,i.min_stock_level,i.max_stock_level,
        i.is_custom,i.created_at,i.updated_at
    FROM items i WHERE i.item_id=p_item_id;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION get_all_items()
RETURNS TABLE (
    item_id INTEGER, item_code VARCHAR, item_name VARCHAR, item_description TEXT,
    item_unit VARCHAR, cat_id INTEGER, item_stock DECIMAL, item_expire_date DATE,
    last_updated TIMESTAMP, reorder_level DECIMAL, min_stock_level DECIMAL,
    max_stock_level DECIMAL, is_custom BOOLEAN, created_at TIMESTAMP, updated_at TIMESTAMP
) AS $$
BEGIN
    RETURN QUERY
    SELECT
        i.item_id,i.item_code,i.item_name,i.item_description,i.item_unit,
        i.cat_id,i.item_stock,i.item_expire_date,i.last_updated,
        i.reorder_level,i.min_stock_level,i.max_stock_level,
        i.is_custom,i.created_at,i.updated_at
    FROM items i ORDER BY i.item_name;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION update_item_stock(
    p_item_id           INTEGER,
    p_quantity_change   DECIMAL,
    p_transaction_type  VARCHAR DEFAULT 'ADJUSTMENT'
) RETURNS JSON AS $$
DECLARE
    current_stock DECIMAL;
    new_stock     DECIMAL;
BEGIN
    SELECT item_stock INTO current_stock FROM items WHERE item_id=p_item_id;
    IF current_stock IS NULL THEN
        RETURN json_build_object('success',false,'message','Item not found');
    END IF;

    new_stock := current_stock + p_quantity_change;
    IF new_stock < 0 THEN
        RETURN json_build_object('success',false,'message','Insufficient stock. Current stock: '||current_stock);
    END IF;

    UPDATE items SET item_stock=new_stock,last_updated=NOW(),updated_at=NOW() WHERE item_id=p_item_id;
    RETURN json_build_object('success',true,'message','Stock updated successfully','old_stock',current_stock,'new_stock',new_stock);
EXCEPTION WHEN OTHERS THEN
    RETURN json_build_object('success',false,'message',SQLERRM);
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION get_low_stock_items()
RETURNS TABLE (
    item_id         INTEGER,
    item_code       VARCHAR,
    item_name       VARCHAR,
    item_unit       VARCHAR,
    current_stock   DECIMAL,
    reorder_level   DECIMAL,
    min_stock_level DECIMAL,
    stock_status    VARCHAR
) AS $$
BEGIN
    RETURN QUERY
    SELECT
        i.item_id,
        i.item_code,
        i.item_name,
        i.item_unit,
        i.item_stock,
        i.reorder_level,
        i.min_stock_level,
        CASE
            WHEN i.item_stock <= i.min_stock_level THEN 'CRITICAL'
            WHEN i.item_stock <= i.reorder_level THEN 'LOW'
            ELSE 'NORMAL'
        END::VARCHAR
    FROM items i
    WHERE i.item_stock <= i.reorder_level
    ORDER BY i.item_stock ASC;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION get_expiry_alerts(p_days_threshold INTEGER DEFAULT 30)
RETURNS TABLE (
    item_id INTEGER, item_code VARCHAR, item_name VARCHAR, item_unit VARCHAR,
    current_stock DECIMAL, item_expire_date DATE, days_until_expiry INTEGER, expiry_status VARCHAR
) AS $$
BEGIN
    RETURN QUERY
    SELECT
        i.item_id,i.item_code,i.item_name,i.item_unit,
        i.item_stock AS current_stock,i.item_expire_date,
        (i.item_expire_date - CURRENT_DATE) AS days_until_expiry,
        CASE
            WHEN i.item_expire_date < CURRENT_DATE THEN 'EXPIRED'
            WHEN (i.item_expire_date - CURRENT_DATE) <= p_days_threshold THEN 'NEAR_EXPIRY'
            ELSE 'OK'
        END AS expiry_status
    FROM items i
    WHERE i.item_expire_date IS NOT NULL
      AND i.item_expire_date <= (CURRENT_DATE + p_days_threshold)
    ORDER BY i.item_expire_date ASC;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION stock_in_summary(
    p_days INTEGER DEFAULT 7
)
RETURNS TABLE (
    today_rcpt    BIGINT,
    week_rcpt     BIGINT,
    pending_rcpt  BIGINT,
    overdue_rcpt  BIGINT,
    week_value    NUMERIC
) AS $$
BEGIN
    RETURN QUERY
    SELECT
        (SELECT COUNT(*) FROM inventory_transactions
         WHERE trans_type='in' AND trans_date = CURRENT_DATE)  AS today_rcpt,

        (SELECT COUNT(*) FROM inventory_transactions
         WHERE trans_type='in'
           AND trans_date BETWEEN CURRENT_DATE - p_days AND CURRENT_DATE) AS week_rcpt,

        (SELECT COUNT(*) FROM purchase_orders
         WHERE po_status='ordered'
           AND expected_delivery_date IS NOT NULL
           AND expected_delivery_date >= CURRENT_DATE) AS pending_rcpt,

        (SELECT COUNT(*) FROM purchase_orders
         WHERE po_status='ordered'
           AND expected_delivery_date < CURRENT_DATE)  AS overdue_rcpt,

        (SELECT COALESCE(SUM(pi.pi_quantity * pi.pi_unit_price),0)
         FROM inventory_transactions t
         JOIN purchase_items pi ON t.po_id = pi.po_id
         WHERE t.trans_type='in'
           AND t.trans_date BETWEEN CURRENT_DATE - p_days AND CURRENT_DATE) AS week_value;
END;
$$ LANGUAGE plpgsql;

/* ============================================================
   5. DONE – everything in one shot
   ============================================================ */
SELECT 'All tables, types and functions created successfully!' AS result;