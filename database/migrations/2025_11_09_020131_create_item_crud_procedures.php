<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        /* ----------  0.  DROP OLD SIGNATURES BEFORE REPLACING  ---------- */
        DB::unprepared('DROP FUNCTION IF EXISTS create_item(
            varchar,varchar,text,varchar,integer,numeric,date,numeric,numeric,numeric) CASCADE');
        DB::unprepared('DROP FUNCTION IF EXISTS update_item(
            integer,varchar,varchar,text,varchar,integer,numeric,date,numeric,numeric,numeric) CASCADE');
        DB::unprepared('DROP FUNCTION IF EXISTS get_item_by_id(integer) CASCADE');
        DB::unprepared('DROP FUNCTION IF EXISTS get_all_items() CASCADE');
        DB::unprepared('DROP FUNCTION IF EXISTS delete_item(integer) CASCADE');
        DB::unprepared('DROP FUNCTION IF EXISTS update_item_stock(integer,numeric,varchar) CASCADE');
        DB::unprepared('DROP FUNCTION IF EXISTS get_low_stock_items() CASCADE');
        DB::unprepared('DROP FUNCTION IF EXISTS get_expiry_alerts(integer) CASCADE');

        /* ----------  1.  CREATE ITEM  ---------- */
        DB::unprepared('
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
                p_is_custom         BOOLEAN DEFAULT false
            ) RETURNS JSON AS $$
            DECLARE
                new_item_id INTEGER;
            BEGIN
                IF EXISTS (SELECT 1 FROM items WHERE item_code = p_item_code) THEN
                    RETURN json_build_object(\'success\', false, \'message\', \'Item code already exists\');
                END IF;
                IF NOT EXISTS (SELECT 1 FROM categories WHERE cat_id = p_cat_id) THEN
                    RETURN json_build_object(\'success\', false, \'message\', \'Category does not exist\');
                END IF;

                INSERT INTO items (
                    item_code, item_name, item_description, item_unit, cat_id,
                    item_stock, item_expire_date, reorder_level, min_stock_level, max_stock_level,
                    is_custom, last_updated, created_at, updated_at
                ) VALUES (
                    p_item_code, p_item_name, p_item_description, p_item_unit, p_cat_id,
                    p_item_stock, p_item_expire_date, p_reorder_level, p_min_stock_level, p_max_stock_level,
                    p_is_custom, NOW(), NOW(), NOW()
                ) RETURNING item_id INTO new_item_id;

                RETURN json_build_object(
                    \'success\', true,
                    \'message\', \'Item created successfully\',
                    \'item_id\', new_item_id
                );
            EXCEPTION WHEN OTHERS THEN
                RETURN json_build_object(\'success\', false, \'message\', SQLERRM);
            END;
            $$ LANGUAGE plpgsql;
        ');

        /* ----------  2.  UPDATE ITEM  ---------- */
        DB::unprepared('
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
            DECLARE
                affected_rows INTEGER;
            BEGIN
                IF EXISTS (SELECT 1 FROM items WHERE item_code = p_item_code AND item_id <> p_item_id) THEN
                    RETURN json_build_object(\'success\', false, \'message\', \'Item code already exists\');
                END IF;
                IF NOT EXISTS (SELECT 1 FROM categories WHERE cat_id = p_cat_id) THEN
                    RETURN json_build_object(\'success\', false, \'message\', \'Category does not exist\');
                END IF;

                UPDATE items
                SET
                    item_code        = p_item_code,
                    item_name        = p_item_name,
                    item_description = p_item_description,
                    item_unit        = p_item_unit,
                    cat_id           = p_cat_id,
                    item_stock       = p_item_stock,
                    item_expire_date = p_item_expire_date,
                    reorder_level    = p_reorder_level,
                    min_stock_level  = p_min_stock_level,
                    max_stock_level  = p_max_stock_level,
                    is_custom        = p_is_custom,
                    last_updated     = NOW(),
                    updated_at       = NOW()
                WHERE item_id = p_item_id;

                GET DIAGNOSTICS affected_rows = ROW_COUNT;
                IF affected_rows = 0 THEN
                    RETURN json_build_object(\'success\', false, \'message\', \'Item not found\');
                END IF;

                RETURN json_build_object(\'success\', true, \'message\', \'Item updated successfully\');
            EXCEPTION WHEN OTHERS THEN
                RETURN json_build_object(\'success\', false, \'message\', SQLERRM);
            END;
            $$ LANGUAGE plpgsql;
        ');

        /* ----------  3.  DELETE ITEM  ---------- */
        DB::unprepared('
            CREATE OR REPLACE FUNCTION delete_item(p_item_id INTEGER) RETURNS JSON AS $$
            DECLARE
                affected_rows INTEGER;
            BEGIN
                IF NOT EXISTS (SELECT 1 FROM items WHERE item_id = p_item_id) THEN
                    RETURN json_build_object(\'success\', false, \'message\', \'Item not found\');
                END IF;
                IF EXISTS (SELECT 1 FROM inventory_transactions WHERE item_id = p_item_id) THEN
                    RETURN json_build_object(\'success\', false, \'message\', \'Cannot delete item with existing inventory transactions\');
                END IF;

                DELETE FROM items WHERE item_id = p_item_id;
                GET DIAGNOSTICS affected_rows = ROW_COUNT;

                RETURN json_build_object(\'success\', true, \'message\', \'Item deleted successfully\');
            EXCEPTION WHEN OTHERS THEN
                RETURN json_build_object(\'success\', false, \'message\', SQLERRM);
            END;
            $$ LANGUAGE plpgsql;
        ');

        /* ----------  4.  GET ITEM BY ID  ---------- */
        DB::unprepared('
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
                    i.item_id, i.item_code, i.item_name, i.item_description, i.item_unit,
                    i.cat_id, i.item_stock, i.item_expire_date, i.last_updated,
                    i.reorder_level, i.min_stock_level, i.max_stock_level,
                    i.is_custom, i.created_at, i.updated_at
                FROM items i
                WHERE i.item_id = p_item_id;
            END;
            $$ LANGUAGE plpgsql;
        ');

        /* ----------  5.  GET ALL ITEMS  ---------- */
        DB::unprepared('
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
                    i.item_id, i.item_code, i.item_name, i.item_description, i.item_unit,
                    i.cat_id, i.item_stock, i.item_expire_date, i.last_updated,
                    i.reorder_level, i.min_stock_level, i.max_stock_level,
                    i.is_custom, i.created_at, i.updated_at
                FROM items i
                ORDER BY i.item_name;
            END;
            $$ LANGUAGE plpgsql;
        ');

        /* ----------  6.  UPDATE ITEM STOCK  ---------- */
        DB::unprepared('
            CREATE OR REPLACE FUNCTION update_item_stock(
                p_item_id INTEGER,
                p_quantity_change DECIMAL,
                p_transaction_type VARCHAR DEFAULT \'ADJUSTMENT\'
            ) RETURNS JSON AS $$
            DECLARE
                current_stock DECIMAL;
                new_stock DECIMAL;
            BEGIN
                SELECT item_stock INTO current_stock
                FROM items
                WHERE item_id = p_item_id;

                IF current_stock IS NULL THEN
                    RETURN json_build_object(\'success\', false, \'message\', \'Item not found\');
                END IF;

                new_stock := current_stock + p_quantity_change;
                IF new_stock < 0 THEN
                    RETURN json_build_object(\'success\', false, \'message\', \'Insufficient stock. Current stock: \' || current_stock);
                END IF;

                UPDATE items
                SET item_stock = new_stock, last_updated = NOW(), updated_at = NOW()
                WHERE item_id = p_item_id;

                RETURN json_build_object(
                    \'success\', true,
                    \'message\', \'Stock updated successfully\',
                    \'old_stock\', current_stock,
                    \'new_stock\', new_stock
                );
            EXCEPTION WHEN OTHERS THEN
                RETURN json_build_object(\'success\', false, \'message\', SQLERRM);
            END;
            $$ LANGUAGE plpgsql;
        ');

        /* ----------  7.  GET LOW STOCK ITEMS  ---------- */
        DB::unprepared('
            CREATE OR REPLACE FUNCTION get_low_stock_items()
            RETURNS TABLE (
                item_id INTEGER, item_code VARCHAR, item_name VARCHAR,
                item_unit VARCHAR, current_stock DECIMAL, reorder_level DECIMAL,
                min_stock_level DECIMAL, stock_status VARCHAR
            ) AS $$
            BEGIN
                RETURN QUERY
                SELECT
                    i.item_id, i.item_code, i.item_name, i.item_unit,
                    i.item_stock AS current_stock, i.reorder_level, i.min_stock_level,
                    CASE
                        WHEN i.item_stock <= i.min_stock_level THEN \'CRITICAL\'
                        WHEN i.item_stock <= i.reorder_level THEN \'LOW\'
                        ELSE \'NORMAL\'
                    END AS stock_status
                FROM items i
                WHERE i.item_stock <= i.reorder_level
                ORDER BY i.item_stock ASC;
            END;
            $$ LANGUAGE plpgsql;
        ');

        /* ----------  8.  GET EXPIRY ALERTS  ---------- */
        DB::unprepared('
            CREATE OR REPLACE FUNCTION get_expiry_alerts(p_days_threshold INTEGER DEFAULT 30)
            RETURNS TABLE (
                item_id INTEGER, item_code VARCHAR, item_name VARCHAR, item_unit VARCHAR,
                current_stock DECIMAL, item_expire_date DATE, days_until_expiry INTEGER, expiry_status VARCHAR
            ) AS $$
            BEGIN
                RETURN QUERY
                SELECT
                    i.item_id, i.item_code, i.item_name, i.item_unit,
                    i.item_stock AS current_stock, i.item_expire_date,
                    (i.item_expire_date - CURRENT_DATE) AS days_until_expiry,
                    CASE
                        WHEN i.item_expire_date < CURRENT_DATE THEN \'EXPIRED\'
                        WHEN (i.item_expire_date - CURRENT_DATE) <= p_days_threshold THEN \'NEAR_EXPIRY\'
                        ELSE \'OK\'
                    END AS expiry_status
                FROM items i
                WHERE i.item_expire_date IS NOT NULL
                  AND i.item_expire_date <= (CURRENT_DATE + p_days_threshold)
                ORDER BY i.item_expire_date ASC;
            END;
            $$ LANGUAGE plpgsql;
        ');
    }

    public function down()
    {
        $list = [
            'create_item(varchar,varchar,text,varchar,integer,numeric,date,numeric,numeric,numeric,boolean)',
            'update_item(integer,varchar,varchar,text,varchar,integer,numeric,date,numeric,numeric,numeric,boolean)',
            'delete_item(integer)',
            'get_item_by_id(integer)',
            'get_all_items()',
            'update_item_stock(integer,numeric,varchar)',
            'get_low_stock_items()',
            'get_expiry_alerts(integer)'
        ];
        foreach ($list as $f) {
            DB::unprepared("DROP FUNCTION IF EXISTS {$f} CASCADE;");
        }
    }
};