-- ProjectManager v19
-- Safety upgrade: avoid NOT NULL execution_order errors on existing tables.
-- The module now calculates execution_order automatically in controllers.
-- These defaults are defensive only.

ALTER TABLE `wt_project_contacts` MODIFY `execution_order` INT(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE `wt_project_modules` MODIFY `execution_order` INT(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE `wt_project_design_tokens` MODIFY `execution_order` INT(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE `wt_project_assets` MODIFY `execution_order` INT(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE `wt_project_technical_stack` MODIFY `execution_order` INT(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE `wt_project_environments` MODIFY `execution_order` INT(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE `wt_project_guidelines` MODIFY `execution_order` INT(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE `wt_project_documentation_sections` MODIFY `execution_order` INT(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE `wt_project_links` MODIFY `execution_order` INT(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE `wt_project_roadmap_items` MODIFY `execution_order` INT(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE `wt_project_tasks` MODIFY `execution_order` INT(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE `wt_project_sprint_tasks` MODIFY `execution_order` INT(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE `wt_project_notes` MODIFY `execution_order` INT(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE `wt_project_task_blocks` MODIFY `execution_order` INT(10) UNSIGNED NOT NULL DEFAULT 0;
