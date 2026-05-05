# WebCatalogue storage structure

The runtime storage service creates these paths under `storage/app/public/webcatalogue`:

```txt
stores/{id_store}/branding
stores/{id_store}/themes
stores/{id_store}/environments
stores/{id_store}/catalogues
stores/{id_store}/products
stores/{id_store}/imports
stores/{id_store}/exports
shared/placeholders
shared/templates
shared/viewer
temp
```

Product-specific resources should be placed under:

```txt
stores/{id_store}/products/{id_product}/images
stores/{id_store}/products/{id_product}/documents
stores/{id_store}/products/{id_product}/videos
stores/{id_store}/products/{id_product}/audio
stores/{id_store}/products/{id_product}/models
stores/{id_store}/products/{id_product}/ar
stores/{id_store}/products/{id_product}/vr
stores/{id_store}/products/{id_product}/thumbnails
stores/{id_store}/products/{id_product}/temp
```
