<?php

return [
    'plain_analysis' => ['label' => 'Plain Analysis', 'expected_format' => 'text', 'requires_schema' => false, 'allow_chat' => true, 'allow_files' => true, 'allow_code' => false, 'default_template' => null],
    'structured_report' => ['label' => 'Structured Report', 'expected_format' => 'json', 'requires_schema' => true, 'allow_chat' => true, 'allow_files' => true, 'allow_code' => false, 'default_template' => null],
    'project_brief' => ['label' => 'Project Brief', 'expected_format' => 'json', 'requires_schema' => true, 'allow_chat' => true, 'allow_files' => true, 'allow_code' => false, 'default_template' => 'idealab.project_brief'],
    'mvp_definition' => ['label' => 'MVP Definition', 'expected_format' => 'json', 'requires_schema' => true, 'allow_chat' => true, 'allow_files' => false, 'allow_code' => false, 'default_template' => 'idealab.mvp_definition'],
    'roadmap' => ['label' => 'Roadmap', 'expected_format' => 'json', 'requires_schema' => true, 'allow_chat' => true, 'allow_files' => false, 'allow_code' => false, 'default_template' => 'projects.roadmap_builder'],
    'task_breakdown' => ['label' => 'Task Breakdown', 'expected_format' => 'json', 'requires_schema' => true, 'allow_chat' => true, 'allow_files' => false, 'allow_code' => false, 'default_template' => 'projects.task_breakdown'],
    'technical_spec' => ['label' => 'Technical Spec', 'expected_format' => 'json', 'requires_schema' => true, 'allow_chat' => true, 'allow_files' => true, 'allow_code' => false, 'default_template' => 'modules.lsg_architecture'],
    'lsg_module_blueprint' => ['label' => 'LSG Module Blueprint', 'expected_format' => 'json', 'requires_schema' => true, 'allow_chat' => true, 'allow_files' => false, 'allow_code' => false, 'default_template' => 'idealab.project_idea_to_lsg_module'],
    'lsg_module_files' => ['label' => 'LSG Module Files', 'expected_format' => 'json', 'requires_schema' => true, 'allow_chat' => true, 'allow_files' => false, 'allow_code' => true, 'default_template' => 'modules.lsg_file_plan'],
    'product_ad' => ['label' => 'Product Ad', 'expected_format' => 'json', 'requires_schema' => false, 'allow_chat' => true, 'allow_files' => true, 'allow_code' => false, 'default_template' => 'products.ad_generation_multilingual'],
    'seo_content' => ['label' => 'SEO Content', 'expected_format' => 'json', 'requires_schema' => false, 'allow_chat' => true, 'allow_files' => false, 'allow_code' => false, 'default_template' => 'products.seo_description'],
    'translations' => ['label' => 'Translations', 'expected_format' => 'json', 'requires_schema' => false, 'allow_chat' => false, 'allow_files' => false, 'allow_code' => false, 'default_template' => null],
    'risk_analysis' => ['label' => 'Risk Analysis', 'expected_format' => 'json', 'requires_schema' => true, 'allow_chat' => true, 'allow_files' => true, 'allow_code' => false, 'default_template' => null],
    'debug_explanation' => ['label' => 'Debug Explanation', 'expected_format' => 'markdown', 'requires_schema' => false, 'allow_chat' => true, 'allow_files' => true, 'allow_code' => false, 'default_template' => 'errors.exception_analysis'],
    'sql_suggestion' => ['label' => 'SQL Suggestion', 'expected_format' => 'json', 'requires_schema' => true, 'allow_chat' => true, 'allow_files' => false, 'allow_code' => false, 'default_template' => null],
    'json_schema' => ['label' => 'JSON Schema', 'expected_format' => 'json', 'requires_schema' => true, 'allow_chat' => false, 'allow_files' => false, 'allow_code' => false, 'default_template' => null],
];
