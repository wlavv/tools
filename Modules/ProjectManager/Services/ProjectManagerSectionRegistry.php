<?php

namespace Modules\ProjectManager\Services;

class ProjectManagerSectionRegistry
{
    public static function all(): array
    {
        return [
            'modules' => [
                'label' => 'Módulos / Áreas', 'icon' => 'fa-solid fa-cubes', 'table' => 'wt_project_modules',
                'description' => 'Áreas funcionais, módulos internos, namespaces, prefixos de rota e estado de implementação.',
                'fields' => ['parent_id','name','slug','namespace','route_prefix','route_name_prefix','description','technical_notes','status','priority','execution_order'],
                'textarea' => ['description','technical_notes'],
                'selects' => ['status' => ['planned','active','in_progress','blocked','completed','archived']],
            ],
            'design-profiles' => [
                'label' => 'Identidade Visual', 'icon' => 'fa-solid fa-palette', 'table' => 'wt_project_design_profiles',
                'description' => 'Regras de marca, linguagem visual, layout, componentes, botões, cards, tabelas, formulários, ícones e logos.',
                'fields' => ['name','status','brand_positioning','visual_language','layout_rules','component_rules','button_rules','card_rules','table_rules','form_rules','icon_rules','logo_rules','accessibility_rules','notes','is_default'],
                'textarea' => ['brand_positioning','visual_language','layout_rules','component_rules','button_rules','card_rules','table_rules','form_rules','icon_rules','logo_rules','accessibility_rules','notes'],
                'selects' => ['status' => ['draft','active','archived']],
                'booleans' => ['is_default'],
            ],
            'design-tokens' => [
                'label' => 'Design Tokens', 'icon' => 'fa-solid fa-droplet', 'table' => 'wt_project_design_tokens',
                'description' => 'Cores, tipografia, espaçamentos, radius, sombras, borders, motion e tokens CSS reutilizáveis.',
                'fields' => ['design_profile_id','group','token_key','token_label','token_value','css_variable','description','usage_notes','execution_order','is_active'],
                'textarea' => ['token_value','description','usage_notes'],
                'selects' => ['group' => ['color','typography','spacing','radius','shadow','border','icon','logo','motion','component','other']],
                'booleans' => ['is_active'],
            ],
            'assets' => [
                'label' => 'Assets', 'icon' => 'fa-solid fa-image', 'table' => 'wt_project_assets',
                'description' => 'Logos, ícones, fontes, screenshots, mockups, previews HTML, documentos e esquemas.',
                'fields' => ['design_profile_id','project_module_id','type','name','variant','language','file_path','public_url','mime_type','file_size','width','height','description','usage_rules','version','is_primary','execution_order'],
                'textarea' => ['description','usage_rules'],
                'selects' => ['type' => ['logo','icon','image','font','mockup','html_preview','document','schema','export','video','other']],
                'booleans' => ['is_primary'],
            ],
            'technical-stack' => [
                'label' => 'Stack Técnica', 'icon' => 'fa-solid fa-layer-group', 'table' => 'wt_project_technical_stack',
                'description' => 'Frameworks, bibliotecas, APIs, ferramentas, hosting, devops e dependências técnicas.',
                'fields' => ['project_module_id','category','name','version','purpose','notes','documentation_url','is_required','execution_order'],
                'textarea' => ['purpose','notes'],
                'selects' => ['category' => ['backend','frontend','database','cache','queue','storage','search','auth','api','devops','hosting','testing','monitoring','package','tool','other']],
                'booleans' => ['is_required'],
            ],
            'environments' => [
                'label' => 'Ambientes', 'icon' => 'fa-solid fa-server', 'table' => 'wt_project_environments',
                'description' => 'Ambientes local, staging, produção e testing sem guardar passwords diretamente.',
                'fields' => ['name','type','url','repository_branch','database_name','php_version','node_version','notes','credential_reference','is_active','execution_order'],
                'textarea' => ['notes'],
                'selects' => ['type' => ['local','development','staging','production','testing','demo','other']],
                'booleans' => ['is_active'],
            ],
            'guidelines' => [
                'label' => 'Guidelines Técnicas', 'icon' => 'fa-solid fa-book-open', 'table' => 'wt_project_guidelines',
                'description' => 'Regras técnicas por categoria: arquitetura, controllers, models, services, views, rotas, deploy e segurança.',
                'fields' => ['project_module_id','category','title','content','importance','status','execution_order'],
                'textarea' => ['content'],
                'selects' => ['category' => ['architecture','database','backend','frontend','ui','security','deployment','testing','documentation','naming','other'], 'importance' => ['low','medium','high','critical'], 'status' => ['draft','active','deprecated']],
            ],
            'documentation' => [
                'label' => 'Documentação', 'icon' => 'fa-solid fa-file-lines', 'table' => 'wt_project_documentation_sections',
                'description' => 'Documentação viva organizada em secções e subsecções.',
                'fields' => ['project_module_id','parent_id','type','title','summary','content','status','is_pinned','execution_order'],
                'textarea' => ['summary','content'],
                'selects' => ['type' => ['overview','setup','architecture','database','api','ui','deployment','troubleshooting','other'], 'status' => ['draft','active','archived']],
                'booleans' => ['is_pinned'],
            ],
            'decisions' => [
                'label' => 'Decisões', 'icon' => 'fa-solid fa-scale-balanced', 'table' => 'wt_project_decisions',
                'description' => 'Decisões técnicas e funcionais, contexto, motivo, impacto e estado.',
                'fields' => ['project_module_id','title','context','decision','reason','impact','status','decided_by','decided_at'],
                'textarea' => ['context','decision','reason','impact'],
                'selects' => ['status' => ['proposed','accepted','rejected','deprecated']],
            ],
            'notes' => [
                'label' => 'Notas', 'icon' => 'fa-solid fa-note-sticky', 'table' => 'wt_project_notes',
                'description' => 'Notas gerais, técnicas, bugs, setup, deployment, base de dados e UI.',
                'fields' => ['project_module_id','project_task_id','type','title','content','visibility','is_pinned'],
                'textarea' => ['content'],
                'selects' => ['type' => ['general_note','technical_note','bug_note','setup_note','deployment_note','database_note','ui_note','client_note'], 'visibility' => ['internal','public','private']],
                'booleans' => ['is_pinned'],
            ],
            'links' => [
                'label' => 'Links Úteis', 'icon' => 'fa-solid fa-link', 'table' => 'wt_project_links',
                'description' => 'Links para repositórios, documentação, staging, produção, dashboards e ferramentas externas.',
                'fields' => ['project_module_id','type','label','url','description','is_primary','execution_order'],
                'textarea' => ['description'],
                'selects' => ['type' => ['repository','documentation','staging','production','design','dashboard','api','tool','other']],
                'booleans' => ['is_primary'],
            ],
            'roadmap-items' => [
                'label' => 'Roadmap', 'icon' => 'fa-solid fa-route', 'table' => 'wt_project_roadmap_items',
                'description' => 'Fases e itens de roadmap com encadeamento lógico.',
                'fields' => ['project_module_id','parent_id','phase','title','description','status','priority','depends_on_id','planned_start_date','planned_end_date','completed_at','execution_order'],
                'textarea' => ['description'],
                'selects' => ['status' => ['pending','ready','in_progress','blocked','done','cancelled']],
            ],
            'tasks' => [
                'label' => 'Tasks', 'icon' => 'fa-solid fa-list-check', 'table' => 'wt_project_tasks',
                'description' => 'Tasks hierárquicas, execução real, prioridades, bloqueios e datas.',
                'fields' => ['roadmap_group_id','parent_id','type','title','description','priority','status','execution_order','start_date','scheduled_for','deadline','completed_at','expected_time','comment','source','blocked_reason'],
                'textarea' => ['description','comment','blocked_reason'],
                'selects' => ['type' => ['milestone','component','task','bug','feature','improvement','research','documentation','technical_debt','setup','design','test','deployment'], 'status' => ['pending','ready','in_progress','waiting','blocked','review','completed','done','cancelled']],
            ],
            'task-dependencies' => [
                'label' => 'Dependências', 'icon' => 'fa-solid fa-diagram-project', 'table' => 'wt_project_task_dependencies',
                'description' => 'Relações entre tasks: blocks, requires, relates_to e duplicates.',
                'fields' => ['task_id','depends_on_task_id','dependency_type','status','notes'],
                'textarea' => ['notes'],
                'selects' => ['dependency_type' => ['blocks','requires','relates_to','duplicates'], 'status' => ['active','resolved','ignored']],
            ],
            'task-blocks' => [
                'label' => 'Bloqueios', 'icon' => 'fa-solid fa-triangle-exclamation', 'table' => 'wt_project_task_blocks',
                'description' => 'Bloqueios reais causados por problema técnico, informação em falta, decisão pendente ou dependência externa.',
                'fields' => ['project_task_id','block_type','title','description','status','blocked_by','blocked_at','resolved_by','resolved_at'],
                'textarea' => ['description'],
                'selects' => ['block_type' => ['technical_issue','missing_information','external_dependency','decision_needed','bug','access_required','design_pending','database_issue','other'], 'status' => ['open','resolved','cancelled']],
            ],
            'blocks' => [
                'label' => 'Blocos de Conteúdo', 'icon' => 'fa-solid fa-table-cells-large', 'table' => 'wt_project_blocks',
                'description' => 'Blocos livres por projeto para organizar informação transversal.',
                'fields' => ['type','title','summary','content','status','execution_order','is_pinned'],
                'textarea' => ['summary','content'],
                'booleans' => ['is_pinned'],
            ],
            'contacts' => [
                'label' => 'Contactos', 'icon' => 'fa-solid fa-address-book', 'table' => 'wt_project_contacts',
                'description' => 'Contactos associados ao projeto.',
                'fields' => ['name','role','company','email','phone','notes','is_primary','execution_order'],
                'textarea' => ['notes'],
                'booleans' => ['is_primary'],
            ],
            'external-dependencies' => [
                'label' => 'Dependências Externas', 'icon' => 'fa-solid fa-plug-circle-exclamation', 'table' => 'wt_project_external_dependencies',
                'description' => 'Dependências externas, owners, risco e datas necessárias.',
                'fields' => ['name','type','owner','status','description','risk_level','needed_by','resolved_at'],
                'textarea' => ['description'],
                'selects' => ['status' => ['open','waiting','resolved','cancelled'], 'risk_level' => ['low','medium','high','critical']],
            ],
            'activity' => [
                'label' => 'Activity Log', 'icon' => 'fa-solid fa-clock-rotate-left', 'table' => 'wt_project_activity_logs',
                'description' => 'Registo de alterações e eventos importantes do projeto.',
                'fields' => ['user_id','event_type','entity_type','entity_id','description','old_values','new_values'],
                'textarea' => ['description','old_values','new_values'],
            ],
        ];
    }

    public static function get(string $key): ?array
    {
        $sections = self::all();
        return $sections[$key] ?? null;
    }

    public static function routeKey(string $section): string
    {
        return str_replace('-', '_', $section);
    }

    public static function routeName(string $section, string $action = 'index'): string
    {
        return 'project_manager.projects.' . self::routeKey($section) . '.' . $action;
    }

}
