# WebCatalogue v2.27.2 - Visual Recognition Focus System

This version improves the Visual Recognition capture UX before matching.

## Added

- Camera overlay that guides the user to place the product in the center.
- Optional focus enhancement toggle enabled by default.
- Browser-side preprocessing before upload:
  - keeps the central product area sharp;
  - blurs and slightly darkens/desaturates the background;
  - increases contrast inside the focus area.

## Goal

Reduce background noise before the recognition pipeline receives the image.

## Notes

This is an MVP browser-side focus system. It does not perform true object segmentation yet. Future versions can replace this with segmentation/embedding-based recognition worker.
