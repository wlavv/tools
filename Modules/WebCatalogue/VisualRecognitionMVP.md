# WebCatalogue Visual Recognition MVP

## Public routes

- `/catalogue/{store_slug}/scan`
- `POST /catalogue/{store_slug}/scan/session`
- `POST /catalogue/{store_slug}/scan/capture`
- `POST /catalogue/{store_slug}/scan/match`
- `POST /catalogue/{store_slug}/scan/unmatched`
- `/catalogue/{store_slug}/scan/result/{session_token}`

## Backoffice routes

- `/webcatalogue/recognition`
- `/webcatalogue/recognition/sessions`
- `/webcatalogue/recognition/leads`

## MVP behavior

The current MVP captures camera images, stores visual recognition sessions/captures, allows users to submit unmatched product information, creates prospect leads and sends an internal notification when a new unmatched product is reported.

Automatic AI/product matching is intentionally stubbed in this release and returns no match. The service layer is ready for an internal pHash/embedding matcher or external AI provider.
