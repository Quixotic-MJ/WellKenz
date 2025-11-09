<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Create function to insert a user
        DB::unprepared('
            CREATE OR REPLACE FUNCTION create_user(
                p_username VARCHAR,
                p_password VARCHAR,
                p_role VARCHAR,
                p_name VARCHAR,
                p_position VARCHAR,
                p_email VARCHAR,
                p_contact VARCHAR,
                p_status VARCHAR DEFAULT \'active\'
            ) RETURNS JSON AS $$
            DECLARE
                new_user_id INTEGER;
                result JSON;
            BEGIN
                -- Check if username already exists
                IF EXISTS (SELECT 1 FROM users WHERE username = p_username) THEN
                    RETURN json_build_object(
                        \'success\', false,
                        \'message\', \'Username already exists\'
                    );
                END IF;

                -- Check if email already exists
                IF EXISTS (SELECT 1 FROM users WHERE email = p_email) THEN
                    RETURN json_build_object(
                        \'success\', false,
                        \'message\', \'Email already exists\'
                    );
                END IF;

                -- Insert new user
                INSERT INTO users (username, password, role, name, "position", email, contact, status, created_at, updated_at)
                VALUES (p_username, p_password, p_role, p_name, p_position, p_email, p_contact, p_status, NOW(), NOW())
                RETURNING user_id INTO new_user_id;

                -- Return success response
                RETURN json_build_object(
                    \'success\', true,
                    \'message\', \'User created successfully\',
                    \'user_id\', new_user_id
                );
            EXCEPTION
                WHEN OTHERS THEN
                    RETURN json_build_object(
                        \'success\', false,
                        \'message\', SQLERRM
                    );
            END;
            $$ LANGUAGE plpgsql;
        ');

        // Create function to update a user
        DB::unprepared('
            CREATE OR REPLACE FUNCTION update_user(
                p_user_id INTEGER,
                p_username VARCHAR,
                p_role VARCHAR,
                p_name VARCHAR,
                p_position VARCHAR,
                p_email VARCHAR,
                p_contact VARCHAR
            ) RETURNS JSON AS $$
            DECLARE
                affected_rows INTEGER;
            BEGIN
                -- Check if username already exists (excluding current user)
                IF EXISTS (SELECT 1 FROM users WHERE username = p_username AND user_id != p_user_id) THEN
                    RETURN json_build_object(
                        \'success\', false,
                        \'message\', \'Username already exists\'
                    );
                END IF;

                -- Check if email already exists (excluding current user)
                IF EXISTS (SELECT 1 FROM users WHERE email = p_email AND user_id != p_user_id) THEN
                    RETURN json_build_object(
                        \'success\', false,
                        \'message\', \'Email already exists\'
                    );
                END IF;

                -- Update user
                UPDATE users 
                SET 
                    username = p_username,
                    role = p_role,
                    name = p_name,
                    "position" = p_position,
                    email = p_email,
                    contact = p_contact,
                    updated_at = NOW()
                WHERE user_id = p_user_id;

                GET DIAGNOSTICS affected_rows = ROW_COUNT;

                IF affected_rows = 0 THEN
                    RETURN json_build_object(
                        \'success\', false,
                        \'message\', \'User not found\'
                    );
                END IF;

                RETURN json_build_object(
                    \'success\', true,
                    \'message\', \'User updated successfully\'
                );
            EXCEPTION
                WHEN OTHERS THEN
                    RETURN json_build_object(
                        \'success\', false,
                        \'message\', SQLERRM
                    );
            END;
            $$ LANGUAGE plpgsql;
        ');

        // Create function to delete a user
        DB::unprepared('
            CREATE OR REPLACE FUNCTION delete_user(p_user_id INTEGER) RETURNS JSON AS $$
            DECLARE
                affected_rows INTEGER;
            BEGIN
                -- Check if user exists and get current data for audit
                IF NOT EXISTS (SELECT 1 FROM users WHERE user_id = p_user_id) THEN
                    RETURN json_build_object(
                        \'success\', false,
                        \'message\', \'User not found\'
                    );
                END IF;

                -- Delete user
                DELETE FROM users WHERE user_id = p_user_id;

                GET DIAGNOSTICS affected_rows = ROW_COUNT;

                RETURN json_build_object(
                    \'success\', true,
                    \'message\', \'User deleted successfully\'
                );
            EXCEPTION
                WHEN OTHERS THEN
                    RETURN json_build_object(
                        \'success\', false,
                        \'message\', SQLERRM
                    );
            END;
            $$ LANGUAGE plpgsql;
        ');

        // Create function to get user by ID - FIXED: quoted "position"
        DB::unprepared('
            CREATE OR REPLACE FUNCTION get_user_by_id(p_user_id INTEGER) RETURNS TABLE (
                user_id INTEGER,
                username VARCHAR,
                role VARCHAR,
                name VARCHAR,
                "position" VARCHAR,
                email VARCHAR,
                contact VARCHAR,
                status VARCHAR,
                created_at TIMESTAMP,
                updated_at TIMESTAMP
            ) AS $$
            BEGIN
                RETURN QUERY
                SELECT 
                    u.user_id,
                    u.username,
                    u.role,
                    u.name,
                    u."position",
                    u.email,
                    u.contact,
                    u.status,
                    u.created_at,
                    u.updated_at
                FROM users u
                WHERE u.user_id = p_user_id;
            END;
            $$ LANGUAGE plpgsql;
        ');

        // Create function to get all users - FIXED: quoted "position"
        DB::unprepared('
            CREATE OR REPLACE FUNCTION get_all_users() RETURNS TABLE (
                user_id INTEGER,
                username VARCHAR,
                role VARCHAR,
                name VARCHAR,
                "position" VARCHAR,
                email VARCHAR,
                contact VARCHAR,
                status VARCHAR,
                created_at TIMESTAMP,
                updated_at TIMESTAMP
            ) AS $$
            BEGIN
                RETURN QUERY
                SELECT 
                    u.user_id,
                    u.username,
                    u.role,
                    u.name,
                    u."position",
                    u.email,
                    u.contact,
                    u.status,
                    u.created_at,
                    u.updated_at
                FROM users u
                ORDER BY u.created_at DESC;
            END;
            $$ LANGUAGE plpgsql;
        ');

        // Create function to change user password
        DB::unprepared('
            CREATE OR REPLACE FUNCTION change_user_password(
                p_user_id INTEGER,
                p_new_password VARCHAR
            ) RETURNS JSON AS $$
            DECLARE
                affected_rows INTEGER;
            BEGIN
                UPDATE users 
                SET 
                    password = p_new_password,
                    updated_at = NOW()
                WHERE user_id = p_user_id;

                GET DIAGNOSTICS affected_rows = ROW_COUNT;

                IF affected_rows = 0 THEN
                    RETURN json_build_object(
                        \'success\', false,
                        \'message\', \'User not found\'
                    );
                END IF;

                RETURN json_build_object(
                    \'success\', true,
                    \'message\', \'Password updated successfully\'
                );
            EXCEPTION
                WHEN OTHERS THEN
                    RETURN json_build_object(
                        \'success\', false,
                        \'message\', SQLERRM
                    );
            END;
            $$ LANGUAGE plpgsql;
        ');

        // Create function to toggle user status
        DB::unprepared('
            CREATE OR REPLACE FUNCTION toggle_user_status(p_user_id INTEGER) RETURNS JSON AS $$
            DECLARE
                current_status VARCHAR;
                new_status VARCHAR;
                affected_rows INTEGER;
            BEGIN
                -- Get current status
                SELECT status INTO current_status 
                FROM users 
                WHERE user_id = p_user_id;

                IF current_status IS NULL THEN
                    RETURN json_build_object(
                        \'success\', false,
                        \'message\', \'User not found\'
                    );
                END IF;

                -- Toggle status
                new_status = CASE WHEN current_status = \'active\' THEN \'inactive\' ELSE \'active\' END;

                UPDATE users 
                SET 
                    status = new_status,
                    updated_at = NOW()
                WHERE user_id = p_user_id;

                GET DIAGNOSTICS affected_rows = ROW_COUNT;

                RETURN json_build_object(
                    \'success\', true,
                    \'message\', \'User status changed to \' || new_status,
                    \'new_status\', new_status
                );
            EXCEPTION
                WHEN OTHERS THEN
                    RETURN json_build_object(
                        \'success\', false,
                        \'message\', SQLERRM
                    );
            END;
            $$ LANGUAGE plpgsql;
        ');
    }

    public function down()
    {
        DB::unprepared('DROP FUNCTION IF EXISTS create_user;');
        DB::unprepared('DROP FUNCTION IF EXISTS update_user;');
        DB::unprepared('DROP FUNCTION IF EXISTS delete_user;');
        DB::unprepared('DROP FUNCTION IF EXISTS get_user_by_id;');
        DB::unprepared('DROP FUNCTION IF EXISTS get_all_users;');
        DB::unprepared('DROP FUNCTION IF EXISTS change_user_password;');
        DB::unprepared('DROP FUNCTION IF EXISTS toggle_user_status;');
    }
};