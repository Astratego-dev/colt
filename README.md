# COLT Experience

WordPress plugin for shortcode-driven premium COLT experience pages.

## Shortcode

Place this shortcode on the WordPress home page:

```text
[colt_home_experience]
```

Optional product count:

```text
[colt_home_experience products="12"]
```

Vault service page:

```text
[colt_vault_experience]
```

Mystery Box product page:

```text
[colt_mystery_box_experience]
```

Live Show page:

```text
[colt_live_show]
```

The Live Show shortcode creates a guest-based live card convention room. Visitors enter through a no-submit arcade lobby, vendors can place a booth only in allowed side zones, and participants can see each other, NPCs, booths, booth cards, and chat requests in real time through WordPress REST polling.

Product slider created from Product CRM:

```text
[colt_product_slider id="slider_260719_120000_123"]
```

## Admin Tools

After activation, open `COLT Experience > Product CRM` in WordPress admin to manage WooCommerce products in bulk. The CRM includes product creation and editing in in-page modals, XLSX imports with column-to-field mapping and exact-name/SKU/ID matching, product slider shortcode creation from selected products, image padding to 1:1 or 3:4 without cropping, product search and filters, time-scoped stats, quick stock and price actions, category/tag bulk edits, bulk trash/permanent delete, display image metadata, coupon creation, active 3+1 promo rules, backorder signup tracking, sales insights, low-stock alerts, and customer summaries. Use `COLT Experience > Product Media` to upload/select product images and quickly create WooCommerce products from media items. Use `COLT Experience > eBay` to connect OAuth, sync business policies, export products to eBay Sell Inventory, fetch eBay orders, update fulfillment tracking, receive notification webhooks, and inspect logs.

## What It Does

- Renders a premium COLT home experience.
- Renders a real-time guest Live Show with an arcade lobby, vendor booth placement, NPCs, and chat.
- Adds WooCommerce Product CRM, Product Media, and eBay admin screens for product creation/editing from forms or media images, eBay OAuth/listing/order tooling, XLSX import mapping, product slider shortcode creation, image padding to fixed aspect ratios, bulk product operations, deletion, coupon creation, 3+1 promo rules, backorder signup tracking, and time-scoped store insights.
- Uses the site's WordPress custom logo when available.
- Links to existing service pages under `/services/...`.
- Pulls latest WooCommerce products when WooCommerce is active.
- Adds lightweight motion with CSS and vanilla JavaScript.

## eBay Connector

The `COLT Experience > eBay` admin area is split into Listings, Settings, Orders, Notifications, Queue, and Logs. Settings store OAuth credentials, marketplace, currency conversion, default category, location key, business policy ids, the listing description template, and webhook verification token. Product exports are queued in `colt_ebay_queue`; the first few tasks run immediately and the rest continue through WordPress cron to reduce rate-limit and timeout risk.

Core Sell Inventory payloads:

```json
{
  "availability": { "shipToLocationAvailability": { "quantity": 1 } },
  "condition": "NEW",
  "product": {
    "title": "Product title trimmed to 80 chars",
    "description": "Plain product description",
    "imageUrls": ["https://example.com/product.jpg"]
  }
}
```

```json
{
  "sku": "STORE-SKU",
  "marketplaceId": "EBAY_US",
  "format": "FIXED_PRICE",
  "availableQuantity": 1,
  "categoryId": "183454",
  "merchantLocationKey": "default",
  "listingDescription": "<h2>Product</h2><p>Description</p>",
  "listingPolicies": {
    "paymentPolicyId": "payment-policy-id",
    "fulfillmentPolicyId": "shipping-policy-id",
    "returnPolicyId": "return-policy-id"
  },
  "pricingSummary": {
    "price": { "value": "29.99", "currency": "USD" }
  }
}
```

Fulfillment tracking payload:

```json
{
  "lineItems": [{ "lineItemId": "line-item-id", "quantity": 1 }],
  "shippedDate": "2026-08-25T12:00:00Z",
  "shippingCarrierCode": "Israel Post",
  "trackingNumber": "TRACKING-NUMBER"
}
```

## Install

Upload the `colt-experience` folder into:

```text
wp-content/plugins/
```

Then activate `COLT Experience` in WordPress plugins.
