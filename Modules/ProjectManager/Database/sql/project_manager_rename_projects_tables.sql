/*
    Project Manager - Normalização de nomes de tabelas
    Objetivo: uniformizar tabelas wt_projects_xxx para wt_project_xxx

    Regra final:
      - wt_projects       = tabela principal
      - wt_project_xxx    = tabelas filhas / detalhe do projeto

    Este script é NÃO destrutivo:
      - só faz RENAME se a tabela antiga existir
      - só faz RENAME se a tabela nova ainda não existir
      - não apaga dados

    Compatível com MySQL/MariaDB via SQL dinâmico.
*/

SET @OLD_FOREIGN_KEY_CHECKS = @@FOREIGN_KEY_CHECKS;
SET FOREIGN_KEY_CHECKS = 0;

DROP PROCEDURE IF EXISTS wt_project_rename_table_if_needed;

DELIMITER $$

CREATE PROCEDURE wt_project_rename_table_if_needed(
    IN p_old_table VARCHAR(128),
    IN p_new_table VARCHAR(128)
)
BEGIN
    DECLARE v_old_exists INT DEFAULT 0;
    DECLARE v_new_exists INT DEFAULT 0;

    SELECT COUNT(*)
      INTO v_old_exists
      FROM information_schema.tables
     WHERE table_schema = DATABASE()
       AND table_name = p_old_table;

    SELECT COUNT(*)
      INTO v_new_exists
      FROM information_schema.tables
     WHERE table_schema = DATABASE()
       AND table_name = p_new_table;

    IF v_old_exists = 1 AND v_new_exists = 0 THEN
        SET @sql = CONCAT('RENAME TABLE `', p_old_table, '` TO `', p_new_table, '`');
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$

DELIMITER ;

CALL wt_project_rename_table_if_needed('wt_projects_contacts', 'wt_project_contacts_legacy');
CALL wt_project_rename_table_if_needed('wt_projects_details',  'wt_project_details');
CALL wt_project_rename_table_if_needed('wt_projects_graphics', 'wt_project_graphics');
CALL wt_project_rename_table_if_needed('wt_projects_social',   'wt_project_social');

DROP PROCEDURE IF EXISTS wt_project_rename_table_if_needed;

SET FOREIGN_KEY_CHECKS = @OLD_FOREIGN_KEY_CHECKS;

/*
    Notas importantes:

    1) wt_projects_contacts foi renomeada para wt_project_contacts_legacy porque já existe
       uma tabela nova chamada wt_project_contacts na estrutura atual.

    2) Depois de validares os dados, podes migrar os contactos antigos para wt_project_contacts
       com um INSERT SELECT adaptado aos campos reais.

    3) As tabelas normalizadas ficam:
       - wt_projects
       - wt_project_contacts_legacy
       - wt_project_details
       - wt_project_graphics
       - wt_project_social

    4) Se preferires substituir a tabela nova wt_project_contacts pela antiga,
       não recomendo fazer automaticamente porque pode causar colisão/perda funcional.
*/
