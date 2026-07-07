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

The Live Show shortcode creates a guest-based live card convention room. Visitors can enter as collectors or vendors, vendors can place a booth only in allowed side zones, and participants can see each other, NPCs, booths, booth cards, and chat requests in real time through WordPress REST polling.

## What It Does

- Renders a premium COLT home experience.
- Renders a real-time guest Live Show MVP with vendor booth placement, NPCs, and chat.
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
