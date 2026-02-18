-- Migration: add user_id and updated_at to tasks, add index and FK to users
USE task_management;

-- Add user_id column if it does not exist (MySQL 8+ supports IF NOT EXISTS)
ALTER TABLE tasks ADD COLUMN IF NOT EXISTS user_id INT NULL AFTER id;

-- Add updated_at column
ALTER TABLE tasks ADD COLUMN IF NOT EXISTS updated_at DATETIME NULL AFTER created_at;

-- Add index on user_id
ALTER TABLE tasks ADD INDEX IF NOT EXISTS idx_tasks_user_id (user_id);

-- Add foreign key constraint (may fail if a constraint with same name exists)
ALTER TABLE tasks ADD CONSTRAINT fk_tasks_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL;

-- If any of the above statements fail because your MySQL version doesn't support IF NOT EXISTS,
-- run the following simpler statements instead (use them one by one in phpMyAdmin SQL tab):
-- ALTER TABLE tasks ADD COLUMN user_id INT NULL AFTER id;
-- ALTER TABLE tasks ADD COLUMN updated_at DATETIME NULL AFTER created_at;
-- ALTER TABLE tasks ADD INDEX (user_id);
-- ALTER TABLE tasks ADD CONSTRAINT fk_tasks_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL;
