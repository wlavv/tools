<?php

namespace Modules\IdeaLab\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\IdeaLab\Models\IdeaAiTemplate;
use Modules\IdeaLab\Models\IdeaCategory;

class IdeaLabDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'B.O. Module', 'icon' => 'fa-solid fa-cubes', 'color' => '#0d6efd'],
            ['name' => 'SaaS / Platform', 'icon' => 'fa-solid fa-cloud', 'color' => '#198754'],
            ['name' => 'AI Tool', 'icon' => 'fa-solid fa-brain', 'color' => '#6f42c1'],
            ['name' => 'E-commerce', 'icon' => 'fa-solid fa-cart-shopping', 'color' => '#fd7e14'],
            ['name' => 'Internal Operations', 'icon' => 'fa-solid fa-gears', 'color' => '#20c997'],
        ];

        foreach ($categories as $index => $category) {
            IdeaCategory::query()->firstOrCreate(
                ['slug' => Str::slug($category['name'])],
                array_merge($category, ['sort_order' => $index + 1])
            );
        }

        IdeaAiTemplate::query()->updateOrCreate(
            ['key' => 'idea_deconstruction'],
            [
                'name' => 'Idea Deconstruction',
                'entrypoint_type' => 'idea_discussion',
                'description' => 'Breaks down a raw idea into problem, audience, value, MVP, risks, roadmap, task backlog and conversion readiness.',
                'system_prompt' => 'You are AI Consensus inside WebTools Manager. Analyze ideas with a practical product, technical, business and implementation perspective. Return a conversion-ready structured project brief.',
                'user_prompt_template' => "Analyze this project idea and return a structured project brief that can be converted into Project Manager.\n\nTitle: {{title}}\n\nRaw description:\n{{description_raw}}\n\nRefined description:\n{{description_refined}}\n\nReturn valid JSON with these keys: executive_summary, problem, target_users, value_proposition, concepts, complexity, mvp, technical_requirements, risks, dependencies, monetization, milestones, tasks, scores, recommendation.\n\nMilestones must be an ordered array. Each milestone must include title, description, priority and tasks. Each task must include title, description, type, priority, importance, urgency, expected_time, acceptance_criteria and technical_notes.\n\nUse priority 1 for most important work. Use importance and urgency from 1 to 5. Separate concepts by functional area and implementation complexity.",
                'expected_schema' => [
                    'executive_summary' => 'string',
                    'problem' => 'string',
                    'target_users' => 'array',
                    'value_proposition' => 'string',
                    'concepts' => 'array',
                    'complexity' => 'object',
                    'mvp' => 'array',
                    'technical_requirements' => 'array',
                    'risks' => 'array',
                    'dependencies' => 'array',
                    'monetization' => 'array',
                    'milestones' => 'array<object{title,description,priority,tasks}>',
                    'tasks' => 'array<object{title,description,type,priority,importance,urgency,expected_time,acceptance_criteria,technical_notes}>',
                    'scores' => 'object',
                    'recommendation' => 'string',
                ],
                'supports_chat' => true,
                'sort_order' => 1,
            ]
        );

        IdeaAiTemplate::query()->updateOrCreate(
            ['key' => 'project_conversion_brief'],
            [
                'name' => 'Project Conversion Brief',
                'entrypoint_type' => 'project_conversion',
                'description' => 'Prepares an IdeaLab idea to become a Project Manager project.',
                'system_prompt' => 'You prepare structured project creation payloads for Project Manager. Be precise, phased and implementation-oriented.',
                'user_prompt_template' => "Convert this idea into a Project Manager project proposal.\n\nTitle: {{title}}\nDescription: {{description_refined}}\n\nReturn valid JSON with: project_name, objective, scope, out_of_scope, milestones, tasks, dependencies, risks, mvp_acceptance_criteria, first_implementation_sprint. Milestones and tasks must be ordered and have priority, importance and urgency.",
                'supports_chat' => true,
                'sort_order' => 2,
            ]
        );
    }
}
