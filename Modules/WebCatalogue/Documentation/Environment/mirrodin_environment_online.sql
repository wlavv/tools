-- WebCatalogue Mirrodin VR environment
-- Execute online after uploading:
--   public/envs/mirrodin_artifact_vault_360_4k.jpg
--   public/envs/mirrodin_artifact_vault_360.mp3
--
-- Expected public URLs:
--   /envs/mirrodin_artifact_vault_360_4k.jpg
--   /envs/mirrodin_artifact_vault_360.mp3

SET @store_slug := 'tcg-collectors';
SET @catalogue_slug := 'mirrodin';
SET @env_slug := 'mirrodin-artifact-vault';

SELECT @store_id := id
FROM wc_stores
WHERE slug = @store_slug OR code = @store_slug
ORDER BY id
LIMIT 1;

SELECT @catalogue_id := id
FROM wc_catalogues
WHERE id_store = @store_id
  AND (slug = @catalogue_slug OR LOWER(name) LIKE '%mirrodin%')
ORDER BY id
LIMIT 1;

INSERT INTO wc_store_environments (
    id_store,
    id_catalogue,
    name,
    slug,
    is_default,
    environment_type,
    background_type,
    background_color,
    lighting_preset,
    camera_preset,
    vr_scene_config,
    ar_scene_config,
    metadata,
    status,
    created_at,
    updated_at
) VALUES (
    @store_id,
    @catalogue_id,
    'Mirrodin Artifact Vault',
    @env_slug,
    0,
    'vr',
    'equirectangular_360',
    '#0b1018',
    'mirrodin_cold_forge',
    'artifact_inspection',
    JSON_OBJECT(
        'scene', 'mirrodin_artifact_vault',
        'background', JSON_OBJECT(
            'type', 'equirectangular',
            'url', '/envs/mirrodin_artifact_vault_360_4k.jpg',
            'mapping', 'EquirectangularReflectionMapping',
            'tone_mapping_exposure', 0.92,
            'environment_intensity', 0.72
        ),
        'audio', JSON_OBJECT(
            'enabled', true,
            'url', '/envs/mirrodin_artifact_vault_360.mp3',
            'loop', true,
            'volume', 0.34,
            'spatial', false,
            'profile', 'mirrodin_artifact_vault_ambience'
        ),
        'lighting', JSON_OBJECT(
            'hemisphere', JSON_OBJECT('skyColor', '#9ec8ff', 'groundColor', '#1b1510', 'intensity', 0.62),
            'key', JSON_OBJECT('type', 'directional', 'color', '#d1b463', 'intensity', 1.05, 'position', JSON_ARRAY(2.2, 3.4, 2.0)),
            'rim', JSON_OBJECT('type', 'directional', 'color', '#62b6ff', 'intensity', 1.25, 'position', JSON_ARRAY(-2.4, 1.8, -1.8)),
            'fill', JSON_OBJECT('type', 'point', 'color', '#2f7fc5', 'intensity', 0.45, 'distance', 5.5, 'position', JSON_ARRAY(0.0, 1.2, 1.8))
        ),
        'camera', JSON_OBJECT(
            'fov', 52,
            'near', 0.01,
            'far', 200,
            'position', JSON_ARRAY(0.75, 0.42, 1.32),
            'target', JSON_ARRAY(0.0, 0.0, 0.0),
            'minDistance', 0.55,
            'maxDistance', 3.2,
            'enableDamping', true,
            'dampingFactor', 0.08
        ),
        'scene', JSON_OBJECT(
            'fog', JSON_OBJECT('enabled', true, 'color', '#0b1018', 'density', 0.018),
            'floor', JSON_OBJECT('type', 'procedural_dark_metal_grid', 'color', '#151a20', 'accent', '#7a6330', 'roughness', 0.78),
            'particles', JSON_OBJECT('enabled', true, 'type', 'dust_sparks', 'density', 0.18, 'color', '#c7a755'),
            'performance', JSON_OBJECT('mobile_texture_max', 2048, 'quest_texture_max', 4096, 'prefer_low_power_audio', true)
        )
    ),
    JSON_OBJECT(
        'placement', 'tabletop',
        'scale', 0.22,
        'shadow', true,
        'environment_audio', false,
        'background_passthrough', true
    ),
    JSON_OBJECT(
        'theme', 'mirrodin',
        'scope', 'catalogue',
        'asset_pack', 'mirrodin_vr_v1',
        'visual_direction', JSON_OBJECT(
            'materials', JSON_ARRAY('oxidized metal', 'brass artifact trims', 'etched stone', 'blue mana glow'),
            'palette', JSON_ARRAY('#0b1018', '#1f2933', '#7a6330', '#c7a755', '#62b6ff'),
            'notes', 'Immersive artifact vault with readable contrast for MTG card inspection.'
        ),
        'audio_direction', JSON_OBJECT(
            'description', 'Low metallic resonance, distant machinery and soft arcane shimmer.',
            'target_lufs', -22,
            'loop_seconds', 42
        )
    ),
    'active',
    NOW(),
    NOW()
)
ON DUPLICATE KEY UPDATE
    id_catalogue = VALUES(id_catalogue),
    name = VALUES(name),
    environment_type = VALUES(environment_type),
    background_type = VALUES(background_type),
    background_color = VALUES(background_color),
    lighting_preset = VALUES(lighting_preset),
    camera_preset = VALUES(camera_preset),
    vr_scene_config = VALUES(vr_scene_config),
    ar_scene_config = VALUES(ar_scene_config),
    metadata = VALUES(metadata),
    status = VALUES(status),
    updated_at = NOW();

SELECT @env_id := id
FROM wc_store_environments
WHERE id_store = @store_id AND slug = @env_slug
LIMIT 1;

DELETE FROM wc_resources
WHERE id_store = @store_id
  AND resource_owner_type = 'environment'
  AND resource_owner_id = @env_id
  AND resource_type IN ('skybox_360', 'ambient_audio')
  AND title IN ('Mirrodin Artifact Vault 360', 'Mirrodin Artifact Vault Ambience');

INSERT INTO wc_resources (
    id_store,
    id_catalogue,
    resource_owner_type,
    resource_owner_id,
    resource_type,
    title,
    description,
    source_type,
    source_url,
    file_path,
    public_url,
    filename,
    mime_type,
    extension,
    is_main,
    sort_order,
    status,
    metadata,
    created_at,
    updated_at
) VALUES
(
    @store_id,
    @catalogue_id,
    'environment',
    @env_id,
    'skybox_360',
    'Mirrodin Artifact Vault 360',
    'Lightweight equirectangular 360 background for the Mirrodin VR catalogue environment.',
    'public',
    '/envs/mirrodin_artifact_vault_360_4k.jpg',
    'public/envs/mirrodin_artifact_vault_360_4k.jpg',
    '/envs/mirrodin_artifact_vault_360_4k.jpg',
    'mirrodin_artifact_vault_360_4k.jpg',
    'image/jpeg',
    'jpg',
    1,
    10,
    'active',
    JSON_OBJECT('width', 4096, 'height', 2048, 'projection', 'equirectangular', 'theme', 'mirrodin'),
    NOW(),
    NOW()
),
(
    @store_id,
    @catalogue_id,
    'environment',
    @env_id,
    'ambient_audio',
    'Mirrodin Artifact Vault Ambience',
    'Lightweight looping ambient audio for Mirrodin VR: low metallic drone, distant machinery, soft shimmer.',
    'public',
    '/envs/mirrodin_artifact_vault_360.mp3',
    'public/envs/mirrodin_artifact_vault_360.mp3',
    '/envs/mirrodin_artifact_vault_360.mp3',
    'mirrodin_artifact_vault_360.mp3',
    'audio/mpeg',
    'mp3',
    0,
    20,
    'active',
    JSON_OBJECT('duration_seconds', 42, 'sample_rate', 22050, 'channels', 1, 'loop', true, 'theme', 'mirrodin'),
    NOW(),
    NOW()
);

SELECT @skybox_resource_id := id
FROM wc_resources
WHERE id_store = @store_id
  AND resource_owner_type = 'environment'
  AND resource_owner_id = @env_id
  AND resource_type = 'skybox_360'
ORDER BY id DESC
LIMIT 1;

UPDATE wc_store_environments
SET skybox_resource_id = @skybox_resource_id,
    background_resource_id = @skybox_resource_id,
    updated_at = NOW()
WHERE id = @env_id;

SELECT
    @env_id AS environment_id,
    @store_id AS store_id,
    @catalogue_id AS catalogue_id,
    '/envs/mirrodin_artifact_vault_360_4k.jpg' AS skybox_url,
    '/envs/mirrodin_artifact_vault_360.mp3' AS audio_url;


