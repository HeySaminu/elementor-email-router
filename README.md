# Conditional Email Router for Elementor

Conditional Email Router for Elementor adds conditional variants directly to the **Email** and **Email 2** actions in Elementor Pro Form widgets.

## Requirements

- WordPress 6.5 or later
- PHP 7.4 or later
- Elementor
- Elementor Pro with the Form widget

Elementor Pro is a commercial dependency and is not bundled with this plugin. This project is an independent add-on and is not affiliated with or endorsed by Elementor Ltd.

## Configure a form

1. Open a Form widget in Elementor.
2. Add **Email** and/or **Email 2** under **Actions After Submit**.
3. Open the corresponding email settings section.
4. Enable **Conditional Email Routing**.
5. Add conditional variants in priority order. The first enabled match wins.
6. Choose whether Elementor should send the normal email or skip that email action when no condition matches.

Each variant can override the recipient, subject, message, sender, reply-to, carbon-copy recipients and content type. Blank settings inherit the normal Elementor email configuration. Elementor field shortcodes such as `[field id="email"]` and `[all-fields]` are supported.

## Matching operators

- Equals
- Does not equal
- Contains
- Does not contain
- Starts with
- Ends with
- Is empty
- Is not empty

Matching is case-insensitive by default and can be made case-sensitive per variant.

## Privacy and security

The plugin does not create a public endpoint, contact an external service or collect analytics. Elementor continues to handle form validation, shortcode replacement and email delivery. Routed mail headers are validated, control characters are removed and malformed route settings are ignored.

## Development

Development takes place at <https://github.com/HeySaminu/elementor-email-router>.

## Licence

GPL-2.0-or-later.
