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

After activation, open `COLT Experience > Product CRM` in WordPress admin to manage WooCommerce products in bulk. The CRM includes product creation and editing in in-page modals, XLSX imports with column-to-field mapping and exact-name/SKU/ID matching, product slider shortcode creation from selected products, image padding to 1:1 or 3:4 without cropping, product search and filters, time-scoped stats, quick stock and price actions, category/tag bulk edits, bulk trash/permanent delete, display image metadata, coupon creation, active 3+1 promo rules, backorder signup tracking, sales insights, low-stock alerts, and customer summaries. Use `COLT Experience > Product Media` to upload/select product images and quickly create WooCommerce products from media items.

## What It Does

- Renders a premium COLT home experience.
- Renders a real-time guest Live Show with an arcade lobby, vendor booth placement, NPCs, and chat.
- Adds WooCommerce Product CRM and Product Media admin screens for product creation/editing from forms or media images, XLSX import mapping, product slider shortcode creation, image padding to fixed aspect ratios, bulk product operations, deletion, coupon creation, 3+1 promo rules, backorder signup tracking, and time-scoped store insights.
- Uses the site's WordPress custom logo when available.
- Links to existing service pages under `/services/...`.
- Pulls latest WooCommerce products when WooCommerce is active.
- Adds lightweight motion with CSS and vanilla JavaScript.

## Install

Upload the `colt-experience` folder into:

```text
wp-content/plugins/
```

Then activate `COLT Experience` in WordPress plugins.
