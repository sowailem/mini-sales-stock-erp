-- ============================================================
-- Migration 001: add the minimal permission fields to `users`
-- ============================================================
-- For databases created before this feature, run this script once:
--
--   mysql -u root -p mini_sales_stock_erp < application/database/migrations/001_add_user_permissions.sql
--
-- It adds:
--   * user_type    ENUM('admin','user_warehouse') NOT NULL DEFAULT 'admin'
--   * warehouse_id BIGINT UNSIGNED NULL (FK -> warehouses, ON DELETE SET NULL)
--
-- Fresh installs get the same columns from application/database/schema.sql
-- and do NOT need this script. Re-running the ALTER below will fail with
-- a "duplicate column/constraint" error; guard against that when needed.

ALTER TABLE users
    ADD COLUMN user_type ENUM('admin','user_warehouse') NOT NULL DEFAULT 'admin' AFTER password,
    ADD COLUMN warehouse_id BIGINT UNSIGNED NULL AFTER user_type,
    ADD KEY idx_users_warehouse_id (warehouse_id),
    ADD CONSTRAINT fk_users_warehouse_id FOREIGN KEY (warehouse_id)
        REFERENCES warehouses (id)
        ON DELETE SET NULL;
