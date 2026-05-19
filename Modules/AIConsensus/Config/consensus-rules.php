<?php

return [
    'single_provider' => ['label' => 'Single Provider', 'providers' => 1, 'description' => 'Uses the first available provider response.'],
    'multi_provider_parallel' => ['label' => 'Multi Provider Parallel', 'providers' => 3, 'description' => 'Runs multiple providers and stores all responses.'],
    'weighted_consensus' => ['label' => 'Weighted Consensus', 'providers' => 3, 'description' => 'Combines responses using configured provider weights.'],
    'critic_review' => ['label' => 'Critic Review', 'providers' => 2, 'description' => 'Generates an answer and asks another provider to critique it.'],
    'architect_reviewer' => ['label' => 'Architect + Reviewer', 'providers' => 2, 'description' => 'Recommended for technical planning and LSG modules.'],
    'debate_mode' => ['label' => 'Debate Mode', 'providers' => 3, 'description' => 'Simulates divergent expert positions before consolidation.'],
    'lsg_validator' => ['label' => 'LSG Validator', 'providers' => 1, 'description' => 'Validates outputs against B.O. Custom LSG module rules.'],
    'cost_optimized' => ['label' => 'Cost Optimized', 'providers' => 1, 'description' => 'Chooses the cheapest capable provider.'],
];
