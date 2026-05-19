<?php

namespace Modules\AIConsensus\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\AIConsensus\Models\AIConsensusProvider;
use Modules\AIConsensus\Models\AIConsensusTemplate;

class AIConsensusCentralSeeder extends Seeder
{
    public function run(): void
    {
        foreach (config('ai-consensus-providers', config('ai_consensus_providers', [])) as $key => $provider) {
            AIConsensusProvider::query()->updateOrCreate(
                ['provider_key' => $key],
                [
                    'name' => $provider['name'] ?? $key,
                    'driver' => $provider['driver'] ?? $key,
                    'model' => $provider['model'] ?? null,
                    'is_active' => (bool) ($provider['is_active'] ?? false),
                    'priority' => (int) ($provider['priority'] ?? 100),
                    'weight' => (float) ($provider['weight'] ?? 1),
                    'config' => $provider['config'] ?? [],
                ]
            );
        }

        foreach ($this->templates() as $template) {
            AIConsensusTemplate::query()->updateOrCreate(
                ['template_key' => $template['template_key']],
                $template
            );
        }
    }

    protected function templates(): array
    {
        $lsgRules = implode("\n- ", config('ai-consensus-lsg.rules', []));

        $baseSystem = "You are AI Consensus, the central intelligence service for WebTools Manager / B.O. Custom LSG.\n"
            . "Respect the LSG module standard:\n- " . $lsgRules . "\n"
            . "Never apply code automatically. Return structured, reviewable outputs.";

        $jsonInstruction = 'Return valid JSON. Include assumptions, risks, execution priorities and next actions.';

        return [
            $this->template('idealab.project_idea_discovery', 'IdeaLab Project Idea Discovery', 'IdeaLab', 'discovery', 'project_brief', $baseSystem, "Analyze the project idea:\n{{input_payload}}\n\nDiscover problem, users, value, scope, concepts, MVP, risks and open questions. {$jsonInstruction}"),
            $this->template('idealab.project_idea_refinement', 'IdeaLab Project Idea Refinement', 'IdeaLab', 'refinement', 'structured_report', $baseSystem, "Refine this project idea into a clearer proposal:\n{{input_payload}}\n\nReturn refined description, target users, value proposition, constraints and maturity score. {$jsonInstruction}"),
            $this->template('idealab.mvp_definition', 'IdeaLab MVP Definition', 'IdeaLab', 'mvp', 'mvp_definition', $baseSystem, "Define the MVP for this idea:\n{{input_payload}}\n\nSeparate must-have, should-have, later, acceptance criteria and validation plan. {$jsonInstruction}"),
            $this->template('idealab.project_brief', 'IdeaLab Project Brief', 'IdeaLab', 'brief', 'project_brief', $baseSystem, "Create a Project Manager ready brief:\n{{input_payload}}\n\nInclude objective, scope, out_of_scope, milestones, tasks, dependencies and first sprint. {$jsonInstruction}"),
            $this->template('idealab.project_idea_to_project', 'IdeaLab Idea to Project', 'IdeaLab', 'conversion', 'task_breakdown', $baseSystem, "Convert this idea into Project Manager payload:\n{{input_payload}}\n\nReturn ordered milestones and tasks with priority, importance, urgency and acceptance criteria. {$jsonInstruction}"),
            $this->template('idealab.project_idea_to_lsg_module', 'IdeaLab Idea to LSG Module', 'IdeaLab', 'module_blueprint', 'lsg_module_blueprint', $baseSystem, "Convert this idea into an LSG module blueprint:\n{{input_payload}}\n\nReturn module name, module.json, permissions, migrations, models, controllers, services, routes, views, translations, risks and validation checklist. Do not generate executable code yet. {$jsonInstruction}"),
            $this->template('modules.lsg_discovery', 'LSG Module Discovery', 'Modules', 'discovery', 'structured_report', $baseSystem, "Discover requirements for an LSG module:\n{{input_payload}}\n\nReturn actors, workflows, entities, integrations, permissions and risks. {$jsonInstruction}"),
            $this->template('modules.lsg_blueprint', 'LSG Module Blueprint', 'Modules', 'blueprint', 'lsg_module_blueprint', $baseSystem, "Create an LSG module blueprint:\n{{input_payload}}\n\nReturn structure, files, database, services, UI, permissions and validation plan. {$jsonInstruction}"),
            $this->template('modules.lsg_architecture', 'LSG Module Architecture', 'Modules', 'architecture', 'technical_spec', $baseSystem, "Design the technical architecture:\n{{input_payload}}\n\nReturn components, data flow, service boundaries and risks. {$jsonInstruction}"),
            $this->template('modules.lsg_database_schema', 'LSG Database Schema', 'Modules', 'database', 'technical_spec', $baseSystem, "Propose database schema:\n{{input_payload}}\n\nUse safe Laravel migrations, short index names and rollback notes. {$jsonInstruction}"),
            $this->template('modules.lsg_file_plan', 'LSG File Plan', 'Modules', 'file_plan', 'lsg_module_files', $baseSystem, "Create a file plan only:\n{{input_payload}}\n\nList files, responsibilities, dependencies and validation checks. {$jsonInstruction}"),
            $this->template('modules.lsg_validation', 'LSG Validation', 'Modules', 'validation', 'risk_analysis', $baseSystem, "Validate this module plan against LSG rules:\n{{input_payload}}\n\nReturn pass/fail, missing components, risks and corrections. {$jsonInstruction}"),
            $this->template('products.ad_generation_multilingual', 'Multilingual Product Ad', 'Products', 'marketing', 'product_ad', $baseSystem, "Create multilingual product ad copy:\n{{input_payload}}\n\nReturn channels, languages, headlines, descriptions and compliance notes. {$jsonInstruction}"),
            $this->template('products.seo_description', 'SEO Product Description', 'Products', 'seo', 'seo_content', $baseSystem, "Create SEO content:\n{{input_payload}}\n\nReturn title, meta description, keywords, slug and product description. {$jsonInstruction}"),
            $this->template('projects.roadmap_builder', 'Project Roadmap Builder', 'Projects', 'roadmap', 'roadmap', $baseSystem, "Build a roadmap:\n{{input_payload}}\n\nReturn phases, milestones, dependencies, effort and sequence. {$jsonInstruction}"),
            $this->template('projects.task_breakdown', 'Project Task Breakdown', 'Projects', 'tasks', 'task_breakdown', $baseSystem, "Break down into tasks:\n{{input_payload}}\n\nReturn task hierarchy with priority, importance, urgency and acceptance criteria. {$jsonInstruction}"),
            $this->template('errors.exception_analysis', 'Exception Analysis', 'Errors', 'debug', 'debug_explanation', $baseSystem, "Analyze this exception:\n{{input_payload}}\n\nReturn likely cause, affected module, fix plan and tests. {$jsonInstruction}"),
            $this->template('documents.document_summary', 'Document Summary', 'Documents', 'summary', 'structured_report', $baseSystem, "Summarize this document:\n{{input_payload}}\n\nReturn executive summary, entities, risks, actions and tags. {$jsonInstruction}"),
            $this->template('catalog.product_enrichment', 'Catalog Product Enrichment', 'Catalog', 'enrichment', 'structured_report', $baseSystem, "Enrich catalog product data:\n{{input_payload}}\n\nReturn attributes, descriptions, SEO, category hints and data quality issues. {$jsonInstruction}"),
            $this->template('travel.trip_planning', 'Trip Planning', 'Travel', 'planning', 'structured_report', $baseSystem, "Plan this trip:\n{{input_payload}}\n\nReturn itinerary, constraints, budget, risks and alternatives. {$jsonInstruction}"),
        ];
    }

    protected function template(string $key, string $name, string $scope, string $category, string $outputType, string $system, string $user): array
    {
        return [
            'template_key' => $key,
            'name' => $name,
            'description' => $name,
            'module_scope' => $scope,
            'category' => $category,
            'system_prompt' => $system,
            'user_prompt_template' => $user,
            'expected_output_schema' => [
                'type' => 'object',
                'required' => ['summary', 'recommendations', 'risks', 'next_actions'],
            ],
            'default_output_type' => $outputType,
            'default_options' => [
                'language' => 'pt',
                'return_format' => 'json',
                'consensus_mode' => str_contains($key, 'lsg') ? 'architect_reviewer' : 'single_provider',
            ],
            'version' => '1.0.0',
            'is_active' => true,
        ];
    }
}
