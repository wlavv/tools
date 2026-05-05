-- ProjectManager v9 - productivity/kanban/gantt/eisenhower support
-- Safe additive columns. Run after the previous ProjectManager SQL upgrades.

SET @db := DATABASE();

SET @sql := IF(
    EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=@db AND TABLE_NAME='wt_project_tasks')
    AND NOT EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='wt_project_tasks' AND COLUMN_NAME='importance'),
    'ALTER TABLE wt_project_tasks ADD COLUMN importance TINYINT NULL DEFAULT 3 AFTER priority',
    'SELECT "importance already exists or wt_project_tasks missing" AS info'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
    EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=@db AND TABLE_NAME='wt_project_tasks')
    AND NOT EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='wt_project_tasks' AND COLUMN_NAME='urgency'),
    'ALTER TABLE wt_project_tasks ADD COLUMN urgency TINYINT NULL DEFAULT 3 AFTER importance',
    'SELECT "urgency already exists or wt_project_tasks missing" AS info'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
    EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=@db AND TABLE_NAME='wt_project_tasks')
    AND NOT EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='wt_project_tasks' AND COLUMN_NAME='started_at'),
    'ALTER TABLE wt_project_tasks ADD COLUMN started_at DATETIME NULL AFTER scheduled_for',
    'SELECT "started_at already exists or wt_project_tasks missing" AS info'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
    EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=@db AND TABLE_NAME='wt_project_tasks')
    AND NOT EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='wt_project_tasks' AND COLUMN_NAME='blocked_at'),
    'ALTER TABLE wt_project_tasks ADD COLUMN blocked_at DATETIME NULL AFTER completed_at',
    'SELECT "blocked_at already exists or wt_project_tasks missing" AS info'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
    EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=@db AND TABLE_NAME='wt_project_tasks')
    AND NOT EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='wt_project_tasks' AND COLUMN_NAME='block_type'),
    'ALTER TABLE wt_project_tasks ADD COLUMN block_type VARCHAR(80) NULL AFTER blocked_at',
    'SELECT "block_type already exists or wt_project_tasks missing" AS info'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ProjectManager v10 - optional calculated ordering for Eisenhower priority
SET @sql := IF(
    EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=@db AND TABLE_NAME='wt_project_tasks')
    AND NOT EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='wt_project_tasks' AND COLUMN_NAME='priority_score'),
    'ALTER TABLE wt_project_tasks ADD COLUMN priority_score INT NULL AFTER urgency',
    'SELECT "priority_score already exists or wt_project_tasks missing" AS info'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
    EXISTS(SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=@db AND TABLE_NAME='wt_project_tasks'),
    'UPDATE wt_project_tasks SET priority_score = (COALESCE(importance,3) * 2) + COALESCE(urgency,3) WHERE priority_score IS NULL',
    'SELECT "wt_project_tasks missing" AS info'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- v11 notes:
-- The Eisenhower matrix is now auto-fed by all open non-milestone tasks.
-- Existing tasks with NULL importance/urgency are initialized by the controller on first Productivity load.
