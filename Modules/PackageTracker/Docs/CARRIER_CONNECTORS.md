# Package Tracker — Connector Layer

## Drivers included

| Carrier | Code | Driver | Integration status |
|---|---|---|---|
| DPD | `dpd` | `DpdTrackingClient` | Configurable REST connector. DPD APIs vary by country/account, so endpoint/path/param are configurable. |
| DHL | `dhl` | `DhlUnifiedTrackingClient` | DHL Shipment Tracking Unified API style connector. |
| CTT | `ctt` | `CttTrackingClient` | Configurable REST connector for CTT API credentials/endpoint. |
| UPS | `ups` | `UpsTrackingClient` | UPS REST Track API style connector with OAuth client credentials support. |
| NACEX | `nacex` | `NacexTrackingClient` | Legacy/configurable NACEX webservice connector. |
| InPost | `inpost` | `InpostTrackingClient` | InPost Tracking API / ShipX-style connector. |
| Mondial Relay | `mondial_relay` | `MondialRelayTrackingClient` | Mondial Relay SOAP/ASMX style connector with security hash. |

## Request model

The internal request contract is:

```php
Modules\PackageTracker\Services\Carriers\Contracts\TrackingRequest
```

It standardizes:

- tracking number
- carrier code
- destination country
- postal code
- order reference
- language
- shipment metadata

## Response model

The internal response contract remains:

```php
Modules\PackageTracker\Services\Carriers\CarrierTrackingResponse
```

Each driver maps the carrier payload to:

- normalized main status source
- substatus
- last location
- estimated delivery date
- delivered date
- tracking event list
- raw payload

## Install carrier records

```bash
php artisan package-tracker:install-carriers
```

To refresh existing carrier driver/base config:

```bash
php artisan package-tracker:install-carriers --force
```

By default, new carriers are created as inactive. Add credentials and enable each one after testing.

## Required `.env` examples

```dotenv
PACKAGE_TRACKER_DHL_API_KEY=
PACKAGE_TRACKER_DHL_BASE_URL=https://api-eu.dhl.com
PACKAGE_TRACKER_DHL_REQUESTER_COUNTRY=PT

PACKAGE_TRACKER_UPS_BASE_URL=https://onlinetools.ups.com
PACKAGE_TRACKER_UPS_CLIENT_ID=
PACKAGE_TRACKER_UPS_CLIENT_SECRET=
PACKAGE_TRACKER_UPS_LOCALE=en_US

PACKAGE_TRACKER_INPOST_BASE_URL=https://api-shipx-pl.easypack24.net
PACKAGE_TRACKER_INPOST_TOKEN=

PACKAGE_TRACKER_MONDIAL_RELAY_BASE_URL=https://api.mondialrelay.com
PACKAGE_TRACKER_MONDIAL_RELAY_ENSEIGNE=
PACKAGE_TRACKER_MONDIAL_RELAY_PRIVATE_KEY=
PACKAGE_TRACKER_MONDIAL_RELAY_LANGUAGE=FR

PACKAGE_TRACKER_NACEX_BASE_URL=https://pda.nacex.com/nacex_ws
PACKAGE_TRACKER_NACEX_USER=
PACKAGE_TRACKER_NACEX_PASSWORD=
PACKAGE_TRACKER_NACEX_TRACKING_PATH=ws
PACKAGE_TRACKER_NACEX_METHOD_NAME=getEstadoEnvio

PACKAGE_TRACKER_CTT_BASE_URL=
PACKAGE_TRACKER_CTT_API_KEY=
PACKAGE_TRACKER_CTT_TRACKING_PATH=tracking
PACKAGE_TRACKER_CTT_METHOD=GET

PACKAGE_TRACKER_DPD_BASE_URL=
PACKAGE_TRACKER_DPD_API_KEY=
PACKAGE_TRACKER_DPD_TRACKING_PATH=tracking
PACKAGE_TRACKER_DPD_TRACKING_PARAM=trackingNumber
PACKAGE_TRACKER_DPD_METHOD=GET
```

## Notes

DPD, CTT and NACEX tend to vary by contract, country, and account type. Their drivers are intentionally configurable so the final endpoint, method and parameter names can be adjusted without changing module code.

Mondial Relay still exposes SOAP/ASMX endpoints in current technical documentation; this module uses a lightweight form POST approach to avoid adding SOAP client dependencies. If your production account requires strict SOAP envelopes, create a dedicated `MondialRelaySoapTrackingClient` while keeping the same `TrackingRequest` and `CarrierTrackingResponse` contracts.
