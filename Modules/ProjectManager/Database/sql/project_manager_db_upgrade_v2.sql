/*
    Project Manager - Database Upgrade v2
    Base analysed: bd_projects.sql
    Target: MariaDB 10.4+ / MySQL compatible

    Purpose:
    - Preserve current data.
    - Keep wt_projects as the main project table.
    - Extend the current schema to support:
        * project overview
        * visual identity / design system
        * assets, logos, icons, fonts and mockups
        * technical stack and environments
        * project modules / functional areas
        * roadmap phases and roadmap items
        * hierarchical tasks, dependencies, blocking reasons and execution state
        * guidelines, documentation, notes and technical decisions
        * useful links and activity/audit log

    Important:
    - This script is non-destructive.
    - It does not DROP existing tables.
    - Legacy tables wt_projects_details, wt_projects_graphics and wt_projects_social are left untouched.
*/

SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS;
SET FOREIGN_KEY_CHECKS=0;

/* -------------------------------------------------------------
   1. Normalize / extend wt_projects
------------------------------------------------------------- */

ALTER TABLE `wt_projects`
    CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE `wt_projects`
    MODIFY `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    MODIFY `id_parent` INT(11) UNSIGNED NOT NULL DEFAULT 0,
    MODIFY `name` VARCHAR(150) NOT NULL,
    MODIFY `status` VARCHAR(30) NOT NULL DEFAULT 'active',
    MODIFY `url` VARCHAR(500) DEFAULT NULL,
    MODIFY `logo` VARCHAR(500) DEFAULT NULL,
    MODIFY `slogan` VARCHAR(180) DEFAULT NULL,
    MODIFY `theme` VARCHAR(80) DEFAULT NULL,
    MODIFY `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    MODIFY `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

ALTER TABLE `wt_projects`
    ADD COLUMN IF NOT EXISTS `uuid` CHAR(36) DEFAULT NULL AFTER `id`,
    ADD COLUMN IF NOT EXISTS `group_id` BIGINT(20) UNSIGNED DEFAULT NULL AFTER `id_parent`,
    ADD COLUMN IF NOT EXISTS `code` VARCHAR(50) DEFAULT NULL AFTER `slug`,
    ADD COLUMN IF NOT EXISTS `project_type` VARCHAR(80) DEFAULT 'software' AFTER `code`,
    ADD COLUMN IF NOT EXISTS `client_name` VARCHAR(180) DEFAULT NULL AFTER `project_type`,
    ADD COLUMN IF NOT EXISTS `version` VARCHAR(50) DEFAULT NULL AFTER `client_name`,
    ADD COLUMN IF NOT EXISTS `environment_status` VARCHAR(50) DEFAULT NULL AFTER `version`,
    ADD COLUMN IF NOT EXISTS `repository_branch` VARCHAR(120) DEFAULT NULL AFTER `repository_url`,
    ADD COLUMN IF NOT EXISTS `staging_url` VARCHAR(500) DEFAULT NULL AFTER `website`,
    ADD COLUMN IF NOT EXISTS `production_url` VARCHAR(500) DEFAULT NULL AFTER `staging_url`,
    ADD COLUMN IF NOT EXISTS `design_status` VARCHAR(50) DEFAULT 'draft' AFTER `brand_notes`,
    ADD COLUMN IF NOT EXISTS `technical_status` VARCHAR(50) DEFAULT 'draft' AFTER `structure_notes`,
    ADD COLUMN IF NOT EXISTS `progress_percent` DECIMAL(5,2) NOT NULL DEFAULT 0.00 AFTER `deadline`,
    ADD COLUMN IF NOT EXISTS `health_status` VARCHAR(50) DEFAULT 'normal' AFTER `progress_percent`,
    ADD COLUMN IF NOT EXISTS `current_focus` VARCHAR(255) DEFAULT NULL AFTER `health_status`,
    ADD COLUMN IF NOT EXISTS `next_step` VARCHAR(255) DEFAULT NULL AFTER `current_focus`,
    ADD COLUMN IF NOT EXISTS `is_pinned` TINYINT(1) NOT NULL DEFAULT 0 AFTER `next_step`,
    ADD COLUMN IF NOT EXISTS `archived_at` DATETIME DEFAULT NULL AFTER `is_pinned`,
    ADD COLUMN IF NOT EXISTS `deleted_at` DATETIME DEFAULT NULL AFTER `archived_at`;

CREATE INDEX IF NOT EXISTS `idx_wt_projects_parent` ON `wt_projects` (`id_parent`);
CREATE INDEX IF NOT EXISTS `idx_wt_projects_group` ON `wt_projects` (`group_id`);
CREATE INDEX IF NOT EXISTS `idx_wt_projects_status` ON `wt_projects` (`status`);
CREATE INDEX IF NOT EXISTS `idx_wt_projects_slug` ON `wt_projects` (`slug`);
CREATE INDEX IF NOT EXISTS `idx_wt_projects_dates` ON `wt_projects` (`start_date`, `deadline`);
CREATE INDEX IF NOT EXISTS `idx_wt_projects_pinned` ON `wt_projects` (`is_pinned`);

/* -------------------------------------------------------------
   2. Visual identity / design system
------------------------------------------------------------- */

CREATE TABLE IF NOT EXISTS `wt_project_design_profiles` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `project_id` INT(11) UNSIGNED NOT NULL,
    `name` VARCHAR(150) NOT NULL DEFAULT 'Default',
    `status` ENUM('draft','active','archived') NOT NULL DEFAULT 'active',
    `brand_positioning` TEXT DEFAULT NULL,
    `visual_language` TEXT DEFAULT NULL,
    `layout_rules` TEXT DEFAULT NULL,
    `component_rules` TEXT DEFAULT NULL,
    `button_rules` TEXT DEFAULT NULL,
    `card_rules` TEXT DEFAULT NULL,
    `table_rules` TEXT DEFAULT NULL,
    `form_rules` TEXT DEFAULT NULL,
    `icon_rules` TEXT DEFAULT NULL,
    `logo_rules` TEXT DEFAULT NULL,
    `accessibility_rules` TEXT DEFAULT NULL,
    `notes` LONGTEXT DEFAULT NULL,
    `metadata_json` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (JSON_VALID(`metadata_json`)),
    `is_default` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME DEFAULT NULL,
    `updated_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_project_design_profiles_project` (`project_id`),
    KEY `idx_project_design_profiles_status` (`status`),
    KEY `idx_project_design_profiles_default` (`project_id`, `is_default`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `wt_project_design_tokens` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `project_id` INT(11) UNSIGNED NOT NULL,
    `design_profile_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `group` ENUM('color','typography','spacing','radius','shadow','border','icon','logo','motion','component','other') NOT NULL DEFAULT 'other',
    `token_key` VARCHAR(120) NOT NULL,
    `token_label` VARCHAR(180) DEFAULT NULL,
    `token_value` TEXT NOT NULL,
    `css_variable` VARCHAR(120) DEFAULT NULL,
    `description` TEXT DEFAULT NULL,
    `usage_notes` TEXT DEFAULT NULL,
    `preview_json` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (JSON_VALID(`preview_json`)),
    `execution_order` INT(10) UNSIGNED NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME DEFAULT NULL,
    `updated_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_project_design_token` (`project_id`, `group`, `token_key`),
    KEY `idx_project_design_tokens_profile` (`design_profile_id`),
    KEY `idx_project_design_tokens_group` (`project_id`, `group`),
    KEY `idx_project_design_tokens_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `wt_project_assets` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `project_id` INT(11) UNSIGNED NOT NULL,
    `design_profile_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `project_module_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `type` ENUM('logo','icon','image','font','mockup','html_preview','document','schema','export','video','other') NOT NULL DEFAULT 'other',
    `name` VARCHAR(180) NOT NULL,
    `variant` VARCHAR(120) DEFAULT NULL,
    `language` VARCHAR(10) DEFAULT NULL,
    `file_path` VARCHAR(500) DEFAULT NULL,
    `public_url` VARCHAR(500) DEFAULT NULL,
    `mime_type` VARCHAR(120) DEFAULT NULL,
    `file_size` BIGINT(20) UNSIGNED DEFAULT NULL,
    `width` INT(10) UNSIGNED DEFAULT NULL,
    `height` INT(10) UNSIGNED DEFAULT NULL,
    `description` TEXT DEFAULT NULL,
    `usage_rules` TEXT DEFAULT NULL,
    `version` VARCHAR(50) DEFAULT NULL,
    `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
    `execution_order` INT(10) UNSIGNED NOT NULL DEFAULT 0,
    `created_at` DATETIME DEFAULT NULL,
    `updated_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_project_assets_project` (`project_id`),
    KEY `idx_project_assets_profile` (`design_profile_id`),
    KEY `idx_project_assets_module` (`project_module_id`),
    KEY `idx_project_assets_type` (`project_id`, `type`),
    KEY `idx_project_assets_primary` (`project_id`, `is_primary`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* -------------------------------------------------------------
   3. Technical foundation
------------------------------------------------------------- */

CREATE TABLE IF NOT EXISTS `wt_project_modules` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `project_id` INT(11) UNSIGNED NOT NULL,
    `parent_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `name` VARCHAR(150) NOT NULL,
    `slug` VARCHAR(180) NOT NULL,
    `namespace` VARCHAR(180) DEFAULT NULL,
    `route_prefix` VARCHAR(180) DEFAULT NULL,
    `route_name_prefix` VARCHAR(180) DEFAULT NULL,
    `description` TEXT DEFAULT NULL,
    `technical_notes` TEXT DEFAULT NULL,
    `status` ENUM('planned','active','in_progress','blocked','completed','archived') NOT NULL DEFAULT 'planned',
    `priority` TINYINT(3) UNSIGNED NOT NULL DEFAULT 2,
    `execution_order` INT(10) UNSIGNED NOT NULL DEFAULT 0,
    `created_at` DATETIME DEFAULT NULL,
    `updated_at` DATETIME DEFAULT NULL,
    `deleted_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_project_module_slug` (`project_id`, `slug`),
    KEY `idx_project_modules_project` (`project_id`),
    KEY `idx_project_modules_parent` (`parent_id`),
    KEY `idx_project_modules_status` (`project_id`, `status`),
    KEY `idx_project_modules_order` (`execution_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `wt_project_technical_stack` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `project_id` INT(11) UNSIGNED NOT NULL,
    `project_module_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `category` ENUM('backend','frontend','database','cache','queue','storage','search','auth','api','devops','hosting','testing','monitoring','package','tool','other') NOT NULL DEFAULT 'other',
    `name` VARCHAR(150) NOT NULL,
    `version` VARCHAR(80) DEFAULT NULL,
    `purpose` VARCHAR(255) DEFAULT NULL,
    `notes` TEXT DEFAULT NULL,
    `documentation_url` VARCHAR(500) DEFAULT NULL,
    `is_required` TINYINT(1) NOT NULL DEFAULT 1,
    `execution_order` INT(10) UNSIGNED NOT NULL DEFAULT 0,
    `created_at` DATETIME DEFAULT NULL,
    `updated_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_project_technical_stack_project` (`project_id`),
    KEY `idx_project_technical_stack_module` (`project_module_id`),
    KEY `idx_project_technical_stack_category` (`project_id`, `category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `wt_project_environments` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `project_id` INT(11) UNSIGNED NOT NULL,
    `name` VARCHAR(120) NOT NULL,
    `type` ENUM('local','development','staging','production','testing','other') NOT NULL DEFAULT 'other',
    `url` VARCHAR(500) DEFAULT NULL,
    `repository_url` VARCHAR(500) DEFAULT NULL,
    `branch` VARCHAR(120) DEFAULT NULL,
    `server_notes` TEXT DEFAULT NULL,
    `deploy_notes` TEXT DEFAULT NULL,
    `credential_reference` VARCHAR(255) DEFAULT NULL,
    `php_version` VARCHAR(50) DEFAULT NULL,
    `database_name` VARCHAR(180) DEFAULT NULL,
    `status` ENUM('active','inactive','archived') NOT NULL DEFAULT 'active',
    `metadata_json` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (JSON_VALID(`metadata_json`)),
    `created_at` DATETIME DEFAULT NULL,
    `updated_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_project_environments_project` (`project_id`),
    KEY `idx_project_environments_type` (`project_id`, `type`),
    KEY `idx_project_environments_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* -------------------------------------------------------------
   4. Documentation / project guide
------------------------------------------------------------- */

CREATE TABLE IF NOT EXISTS `wt_project_guidelines` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `project_id` INT(11) UNSIGNED NOT NULL,
    `project_module_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `category` ENUM('layout','ui','ux','naming','architecture','database','backend','frontend','routes','models','controllers','services','views','translations','security','deploy','testing','logging','other') NOT NULL DEFAULT 'other',
    `title` VARCHAR(180) NOT NULL,
    `content` LONGTEXT DEFAULT NULL,
    `status` ENUM('draft','active','deprecated','archived') NOT NULL DEFAULT 'active',
    `is_required` TINYINT(1) NOT NULL DEFAULT 1,
    `execution_order` INT(10) UNSIGNED NOT NULL DEFAULT 0,
    `created_at` DATETIME DEFAULT NULL,
    `updated_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_project_guidelines_project` (`project_id`),
    KEY `idx_project_guidelines_module` (`project_module_id`),
    KEY `idx_project_guidelines_category` (`project_id`, `category`),
    KEY `idx_project_guidelines_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `wt_project_documentation_sections` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `project_id` INT(11) UNSIGNED NOT NULL,
    `project_module_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `parent_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `type` ENUM('overview','business_rules','layout_rules','technical_rules','architecture','database','api','workflow','decision','issue','how_to','release_notes','other') NOT NULL DEFAULT 'other',
    `title` VARCHAR(180) NOT NULL,
    `summary` VARCHAR(500) DEFAULT NULL,
    `content` LONGTEXT DEFAULT NULL,
    `content_json` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (JSON_VALID(`content_json`)),
    `status` ENUM('draft','active','review','archived') NOT NULL DEFAULT 'active',
    `is_pinned` TINYINT(1) NOT NULL DEFAULT 0,
    `execution_order` INT(10) UNSIGNED NOT NULL DEFAULT 0,
    `created_by` BIGINT(20) UNSIGNED DEFAULT NULL,
    `updated_by` BIGINT(20) UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT NULL,
    `updated_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_project_docs_project` (`project_id`),
    KEY `idx_project_docs_module` (`project_module_id`),
    KEY `idx_project_docs_parent` (`parent_id`),
    KEY `idx_project_docs_type` (`project_id`, `type`),
    KEY `idx_project_docs_pinned` (`project_id`, `is_pinned`),
    KEY `idx_project_docs_order` (`execution_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `wt_project_decisions` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `project_id` INT(11) UNSIGNED NOT NULL,
    `project_module_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `title` VARCHAR(180) NOT NULL,
    `context` TEXT DEFAULT NULL,
    `decision` TEXT NOT NULL,
    `reason` TEXT DEFAULT NULL,
    `impact` TEXT DEFAULT NULL,
    `status` ENUM('proposed','accepted','rejected','deprecated','replaced') NOT NULL DEFAULT 'accepted',
    `decided_by` BIGINT(20) UNSIGNED DEFAULT NULL,
    `decided_at` DATETIME DEFAULT NULL,
    `created_by` BIGINT(20) UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT NULL,
    `updated_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_project_decisions_project` (`project_id`),
    KEY `idx_project_decisions_module` (`project_module_id`),
    KEY `idx_project_decisions_status` (`project_id`, `status`),
    KEY `idx_project_decisions_date` (`decided_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `wt_project_notes` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `project_id` INT(11) UNSIGNED NOT NULL,
    `project_module_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `project_task_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `type` ENUM('general','technical','bug','setup','deployment','database','ui','client','research','other') NOT NULL DEFAULT 'general',
    `title` VARCHAR(180) NOT NULL,
    `content` LONGTEXT DEFAULT NULL,
    `visibility` ENUM('internal','public') NOT NULL DEFAULT 'internal',
    `is_pinned` TINYINT(1) NOT NULL DEFAULT 0,
    `created_by` BIGINT(20) UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT NULL,
    `updated_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_project_notes_project` (`project_id`),
    KEY `idx_project_notes_module` (`project_module_id`),
    KEY `idx_project_notes_task` (`project_task_id`),
    KEY `idx_project_notes_type` (`project_id`, `type`),
    KEY `idx_project_notes_pinned` (`project_id`, `is_pinned`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `wt_project_links` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `project_id` INT(11) UNSIGNED NOT NULL,
    `project_module_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `label` VARCHAR(180) NOT NULL,
    `url` VARCHAR(700) NOT NULL,
    `type` ENUM('repository','documentation','design','staging','production','api','board','credential_reference','external','other') NOT NULL DEFAULT 'other',
    `description` TEXT DEFAULT NULL,
    `is_pinned` TINYINT(1) NOT NULL DEFAULT 0,
    `execution_order` INT(10) UNSIGNED NOT NULL DEFAULT 0,
    `created_at` DATETIME DEFAULT NULL,
    `updated_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_project_links_project` (`project_id`),
    KEY `idx_project_links_module` (`project_module_id`),
    KEY `idx_project_links_type` (`project_id`, `type`),
    KEY `idx_project_links_pinned` (`project_id`, `is_pinned`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* -------------------------------------------------------------
   5. Roadmap and execution
------------------------------------------------------------- */

ALTER TABLE `wt_project_roadmap_groups`
    MODIFY `project_id` INT(11) UNSIGNED DEFAULT NULL COMMENT 'Optional. NULL means reusable/global group.',
    ADD COLUMN IF NOT EXISTS `parent_id` BIGINT(20) UNSIGNED DEFAULT NULL AFTER `project_id`,
    ADD COLUMN IF NOT EXISTS `target_version` VARCHAR(80) DEFAULT NULL AFTER `description`,
    ADD COLUMN IF NOT EXISTS `planned_start_date` DATE DEFAULT NULL AFTER `target_version`,
    ADD COLUMN IF NOT EXISTS `planned_end_date` DATE DEFAULT NULL AFTER `planned_start_date`,
    ADD COLUMN IF NOT EXISTS `progress_percent` DECIMAL(5,2) NOT NULL DEFAULT 0.00 AFTER `planned_end_date`;

CREATE INDEX IF NOT EXISTS `idx_project_roadmap_groups_parent` ON `wt_project_roadmap_groups` (`parent_id`);
CREATE INDEX IF NOT EXISTS `idx_project_roadmap_groups_dates` ON `wt_project_roadmap_groups` (`planned_start_date`, `planned_end_date`);

CREATE TABLE IF NOT EXISTS `wt_project_roadmap_items` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `project_id` INT(11) UNSIGNED NOT NULL,
    `roadmap_group_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `project_module_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `parent_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `title` VARCHAR(180) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `status` ENUM('pending','ready','in_progress','blocked','done','cancelled') NOT NULL DEFAULT 'pending',
    `priority` TINYINT(3) UNSIGNED NOT NULL DEFAULT 2,
    `planned_start_date` DATE DEFAULT NULL,
    `planned_end_date` DATE DEFAULT NULL,
    `completed_at` DATETIME DEFAULT NULL,
    `depends_on_item_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `progress_percent` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `execution_order` INT(10) UNSIGNED NOT NULL DEFAULT 0,
    `created_by` BIGINT(20) UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT NULL,
    `updated_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_project_roadmap_items_project` (`project_id`),
    KEY `idx_project_roadmap_items_group` (`roadmap_group_id`),
    KEY `idx_project_roadmap_items_module` (`project_module_id`),
    KEY `idx_project_roadmap_items_parent` (`parent_id`),
    KEY `idx_project_roadmap_items_status` (`project_id`, `status`),
    KEY `idx_project_roadmap_items_order` (`execution_order`),
    KEY `idx_project_roadmap_items_dependency` (`depends_on_item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `wt_project_tasks`
    MODIFY `project_id` INT(11) UNSIGNED NOT NULL,
    MODIFY `status` ENUM('pending','ready','in_progress','waiting','blocked','review','completed','done','cancelled') NOT NULL DEFAULT 'pending',
    MODIFY `type` ENUM('milestone','component','task','bug','feature','improvement','research','documentation','technical_debt','setup','design','test','deployment') NOT NULL DEFAULT 'task',
    ADD COLUMN IF NOT EXISTS `project_module_id` BIGINT(20) UNSIGNED DEFAULT NULL AFTER `roadmap_group_id`,
    ADD COLUMN IF NOT EXISTS `roadmap_item_id` BIGINT(20) UNSIGNED DEFAULT NULL AFTER `project_module_id`,
    ADD COLUMN IF NOT EXISTS `sprint_id` BIGINT(20) UNSIGNED DEFAULT NULL AFTER `roadmap_item_id`,
    ADD COLUMN IF NOT EXISTS `depends_status` ENUM('none','waiting','ready','blocked') NOT NULL DEFAULT 'none' AFTER `status`,
    ADD COLUMN IF NOT EXISTS `block_type` ENUM('technical_issue','missing_information','external_dependency','decision_needed','bug','access_required','design_pending','database_issue','other') DEFAULT NULL AFTER `blocked_reason`,
    ADD COLUMN IF NOT EXISTS `blocked_by` VARCHAR(180) DEFAULT NULL AFTER `block_type`,
    ADD COLUMN IF NOT EXISTS `blocked_at` DATETIME DEFAULT NULL AFTER `blocked_by`,
    ADD COLUMN IF NOT EXISTS `review_notes` TEXT DEFAULT NULL AFTER `comment`,
    ADD COLUMN IF NOT EXISTS `acceptance_criteria` TEXT DEFAULT NULL AFTER `review_notes`,
    ADD COLUMN IF NOT EXISTS `technical_notes` TEXT DEFAULT NULL AFTER `acceptance_criteria`,
    ADD COLUMN IF NOT EXISTS `actual_time` INT(11) DEFAULT NULL COMMENT 'Actual time in minutes' AFTER `expected_time`,
    ADD COLUMN IF NOT EXISTS `progress_percent` DECIMAL(5,2) NOT NULL DEFAULT 0.00 AFTER `actual_time`,
    ADD COLUMN IF NOT EXISTS `created_by` BIGINT(20) UNSIGNED DEFAULT NULL AFTER `source`,
    ADD COLUMN IF NOT EXISTS `updated_by` BIGINT(20) UNSIGNED DEFAULT NULL AFTER `created_by`,
    ADD COLUMN IF NOT EXISTS `deleted_at` DATETIME DEFAULT NULL AFTER `updated_at`;

CREATE INDEX IF NOT EXISTS `idx_wt_project_tasks_module` ON `wt_project_tasks` (`project_module_id`);
CREATE INDEX IF NOT EXISTS `idx_wt_project_tasks_roadmap_item` ON `wt_project_tasks` (`roadmap_item_id`);
CREATE INDEX IF NOT EXISTS `idx_wt_project_tasks_sprint` ON `wt_project_tasks` (`sprint_id`);
CREATE INDEX IF NOT EXISTS `idx_wt_project_tasks_depends_status` ON `wt_project_tasks` (`depends_status`);
CREATE INDEX IF NOT EXISTS `idx_wt_project_tasks_project_status_order` ON `wt_project_tasks` (`project_id`, `status`, `execution_order`);

ALTER TABLE `wt_project_task_dependencies`
    ADD COLUMN IF NOT EXISTS `project_id` INT(11) UNSIGNED DEFAULT NULL AFTER `id`,
    ADD COLUMN IF NOT EXISTS `dependency_type` ENUM('finish_to_start','start_to_start','finish_to_finish','soft','hard','blocks','requires','relates_to','duplicates') NOT NULL DEFAULT 'finish_to_start' AFTER `depends_on_project_task_id`,
    ADD COLUMN IF NOT EXISTS `status` ENUM('active','resolved','ignored') NOT NULL DEFAULT 'active' AFTER `dependency_type`,
    ADD COLUMN IF NOT EXISTS `reason` VARCHAR(255) DEFAULT NULL AFTER `status`,
    ADD COLUMN IF NOT EXISTS `resolved_at` DATETIME DEFAULT NULL AFTER `reason`;

UPDATE `wt_project_task_dependencies` d
JOIN `wt_project_tasks` t ON t.`id` = d.`project_task_id`
SET d.`project_id` = t.`project_id`
WHERE d.`project_id` IS NULL;

CREATE INDEX IF NOT EXISTS `idx_project_task_dependencies_project` ON `wt_project_task_dependencies` (`project_id`);
CREATE INDEX IF NOT EXISTS `idx_project_task_dependencies_status` ON `wt_project_task_dependencies` (`status`);
CREATE INDEX IF NOT EXISTS `idx_project_task_dependencies_type` ON `wt_project_task_dependencies` (`dependency_type`);

CREATE TABLE IF NOT EXISTS `wt_project_task_blocks` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `project_id` INT(11) UNSIGNED NOT NULL,
    `project_task_id` BIGINT(20) UNSIGNED NOT NULL,
    `block_type` ENUM('technical_issue','missing_information','external_dependency','decision_needed','bug','access_required','design_pending','database_issue','other') NOT NULL DEFAULT 'other',
    `title` VARCHAR(180) NOT NULL,
    `reason` TEXT DEFAULT NULL,
    `status` ENUM('open','resolved','ignored') NOT NULL DEFAULT 'open',
    `blocked_by` VARCHAR(180) DEFAULT NULL,
    `resolved_by` VARCHAR(180) DEFAULT NULL,
    `resolved_notes` TEXT DEFAULT NULL,
    `created_at` DATETIME DEFAULT NULL,
    `resolved_at` DATETIME DEFAULT NULL,
    `updated_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_project_task_blocks_project` (`project_id`),
    KEY `idx_project_task_blocks_task` (`project_task_id`),
    KEY `idx_project_task_blocks_status` (`project_id`, `status`),
    KEY `idx_project_task_blocks_type` (`block_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `wt_project_sprints`
    MODIFY `project_id` INT(11) UNSIGNED NOT NULL,
    ADD COLUMN IF NOT EXISTS `goal` VARCHAR(255) DEFAULT NULL AFTER `description`,
    ADD COLUMN IF NOT EXISTS `review_notes` TEXT DEFAULT NULL AFTER `goal`,
    ADD COLUMN IF NOT EXISTS `retrospective_notes` TEXT DEFAULT NULL AFTER `review_notes`;

CREATE TABLE IF NOT EXISTS `wt_project_sprint_tasks` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `project_id` INT(11) UNSIGNED NOT NULL,
    `sprint_id` BIGINT(20) UNSIGNED NOT NULL,
    `project_task_id` BIGINT(20) UNSIGNED NOT NULL,
    `execution_order` INT(10) UNSIGNED NOT NULL DEFAULT 0,
    `created_at` DATETIME DEFAULT NULL,
    `updated_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_project_sprint_task` (`sprint_id`, `project_task_id`),
    KEY `idx_project_sprint_tasks_project` (`project_id`),
    KEY `idx_project_sprint_tasks_task` (`project_task_id`),
    KEY `idx_project_sprint_tasks_order` (`execution_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* -------------------------------------------------------------
   6. Activity log
------------------------------------------------------------- */

CREATE TABLE IF NOT EXISTS `wt_project_activity_logs` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `project_id` INT(11) UNSIGNED NOT NULL,
    `entity_type` VARCHAR(80) NOT NULL,
    `entity_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `action` VARCHAR(80) NOT NULL,
    `title` VARCHAR(180) DEFAULT NULL,
    `description` TEXT DEFAULT NULL,
    `old_values_json` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (JSON_VALID(`old_values_json`)),
    `new_values_json` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (JSON_VALID(`new_values_json`)),
    `user_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `user_name` VARCHAR(180) DEFAULT NULL,
    `created_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_project_activity_logs_project` (`project_id`),
    KEY `idx_project_activity_logs_entity` (`entity_type`, `entity_id`),
    KEY `idx_project_activity_logs_action` (`action`),
    KEY `idx_project_activity_logs_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* -------------------------------------------------------------
   7. Optional migration of current brand fields into design tokens
------------------------------------------------------------- */

INSERT IGNORE INTO `wt_project_design_profiles` (`project_id`, `name`, `status`, `visual_language`, `layout_rules`, `notes`, `is_default`, `created_at`, `updated_at`)
SELECT
    p.`id`,
    'Default',
    'active',
    p.`brand_notes`,
    p.`structure_notes`,
    p.`documentation_notes`,
    1,
    NOW(),
    NOW()
FROM `wt_projects` p
WHERE NOT EXISTS (
    SELECT 1 FROM `wt_project_design_profiles` dp WHERE dp.`project_id` = p.`id` AND dp.`is_default` = 1
);

INSERT IGNORE INTO `wt_project_design_tokens` (`project_id`, `design_profile_id`, `group`, `token_key`, `token_label`, `token_value`, `description`, `execution_order`, `is_active`, `created_at`, `updated_at`)
SELECT p.`id`, dp.`id`, 'color', 'primary', 'Primary color', p.`primary_color`, 'Migrated from wt_projects.primary_color', 10, 1, NOW(), NOW()
FROM `wt_projects` p
JOIN `wt_project_design_profiles` dp ON dp.`project_id` = p.`id` AND dp.`is_default` = 1
WHERE p.`primary_color` IS NOT NULL AND p.`primary_color` <> '';

INSERT IGNORE INTO `wt_project_design_tokens` (`project_id`, `design_profile_id`, `group`, `token_key`, `token_label`, `token_value`, `description`, `execution_order`, `is_active`, `created_at`, `updated_at`)
SELECT p.`id`, dp.`id`, 'color', 'secondary', 'Secondary color', p.`secondary_color`, 'Migrated from wt_projects.secondary_color', 20, 1, NOW(), NOW()
FROM `wt_projects` p
JOIN `wt_project_design_profiles` dp ON dp.`project_id` = p.`id` AND dp.`is_default` = 1
WHERE p.`secondary_color` IS NOT NULL AND p.`secondary_color` <> '';

INSERT IGNORE INTO `wt_project_design_tokens` (`project_id`, `design_profile_id`, `group`, `token_key`, `token_label`, `token_value`, `description`, `execution_order`, `is_active`, `created_at`, `updated_at`)
SELECT p.`id`, dp.`id`, 'color', 'accent', 'Accent color', p.`accent_color`, 'Migrated from wt_projects.accent_color', 30, 1, NOW(), NOW()
FROM `wt_projects` p
JOIN `wt_project_design_profiles` dp ON dp.`project_id` = p.`id` AND dp.`is_default` = 1
WHERE p.`accent_color` IS NOT NULL AND p.`accent_color` <> '';

INSERT IGNORE INTO `wt_project_design_tokens` (`project_id`, `design_profile_id`, `group`, `token_key`, `token_label`, `token_value`, `description`, `execution_order`, `is_active`, `created_at`, `updated_at`)
SELECT p.`id`, dp.`id`, 'typography', 'font_family', 'Font family', p.`font_family`, 'Migrated from wt_projects.font_family', 10, 1, NOW(), NOW()
FROM `wt_projects` p
JOIN `wt_project_design_profiles` dp ON dp.`project_id` = p.`id` AND dp.`is_default` = 1
WHERE p.`font_family` IS NOT NULL AND p.`font_family` <> '';

/* -------------------------------------------------------------
   8. Compatibility notes
-------------------------------------------------------------

Recommended table usage from now on:

- Main projects:
  wt_projects

- Visual identity:
  wt_project_design_profiles
  wt_project_design_tokens
  wt_project_assets

- Technical guide:
  wt_project_modules
  wt_project_technical_stack
  wt_project_environments
  wt_project_guidelines
  wt_project_documentation_sections
  wt_project_decisions
  wt_project_notes
  wt_project_links

- Execution:
  wt_project_roadmap_groups
  wt_project_roadmap_items
  wt_project_tasks
  wt_project_task_dependencies
  wt_project_task_blocks
  wt_project_sprints
  wt_project_sprint_tasks

- Audit:
  wt_project_activity_logs

Legacy tables kept for compatibility:
  wt_projects_details
  wt_projects_graphics
  wt_projects_social
  wt_projects_contacts
*/

SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
