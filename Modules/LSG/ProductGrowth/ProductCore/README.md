# ProductCore — LSG Product Growth

Módulo base do projeto **Product Growth**. Deve ficar em:

```txt
Modules/LSG/ProductGrowth/ProductCore
```

## Objetivo

O ProductCore é a **fonte de verdade dos produtos** no BO LSG. O PrestaShop 9 é tratado como canal de venda/publicação, não como local principal de edição.

Regra central:

```txt
BO decide. PrestaShop vende.
```

## Responsabilidades

- Produtos centrais `lsg_catalog_core_products`
- Produtos por loja/canal `lsg_catalog_store_products`
- Lojas/canais vindos da infraestrutura LSG (`lsg_sites`)
- Marcas e fornecedores sao dados de suporte vindos de master data/catalogo, nao CRUD interno do Product Growth.
- Categorias por loja pertencem ao catalogo/canais, nao ao fluxo de criacao de anuncios.
- Atributos e valores
- Assets de produto sao gerados/associados pelas etapas de conteudo visual, nao geridos como CRUD de master data neste modulo.
- Estados de aprovação e sincronização
- Preparação para PrestaShopBridge

## Não é responsabilidade deste módulo

- Escrever diretamente no PrestaShop 9
- Gerar anúncios
- Publicar posts
- Calcular buzz
- Criar 3D/AR/VR
- Executar AI Consensus

Essas responsabilidades pertencem aos módulos seguintes.

## Instalação

1. Copiar para:

```txt
Modules/LSG/ProductGrowth/ProductCore
```

2. Registar o provider se o teu loader modular não o fizer automaticamente:

```php
Modules\LSG\ProductGrowth\ProductCore\Providers\ProductCoreServiceProvider::class,
```

3. Executar migrations:

```bash
php artisan migrate
```

4. Executar seed inicial:

```bash
php artisan db:seed --class="Modules\\LSG\\ProductGrowth\\ProductCore\\Database\\Seeders\\ProductCoreSeeder"
```

## Rotas

Prefixo:

```txt
/product-growth/product-core
```

Route names:

```txt
product_growth.product_core.*
```

## Permissões sugeridas

- `permission_product_core_view`
- `permission_product_core_manage`
- `permission_product_core_create`
- `permission_product_core_edit`
- `permission_product_core_delete`
- `permission_product_core_approve`
- `permission_product_core_sync`
- `permission_product_core_assets_manage`
- `permission_product_core_settings`

## Estados principais do produto

- `draft`
- `in_review`
- `approved`
- `ready_to_sync`
- `synced`
- `needs_resync`
- `archived`
- `blocked`

## Estados de sincronização por loja

- `not_synced`
- `ready_to_sync`
- `syncing`
- `synced`
- `needs_resync`
- `sync_failed`
- `conflict`

## Próximo módulo esperado

```txt
Modules/LSG/ProductGrowth/PrestaShopBridge
```

O PrestaShopBridge deve consumir os produtos/store products marcados como `ready_to_sync`.
