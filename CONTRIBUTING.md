# Contributing

Contributions that improve compatibility, security, accessibility or the routing workflow are welcome.

## Before starting

1. Search the existing issues to avoid duplicating work.
2. Open an issue before making a breaking change or introducing a new dependency.
3. Keep changes focused on conditional routing for Elementor Pro form email actions.

## Local setup

Use a local WordPress installation with:

- WordPress 6.5 or later
- PHP 7.4 or later
- Elementor
- Elementor Pro

Clone the repository into `wp-content/plugins/conditional-email-router-for-elementor` and activate it from WordPress.

## Coding expectations

- Follow the WordPress PHP Coding Standards.
- Prefix global functions, classes, constants and stored keys with `ceer` or `CEER`.
- Escape output as late as possible.
- Sanitize and validate values before using them in email headers.
- Preserve inheritance from Elementor's normal email settings.
- Avoid external services, telemetry and new dependencies unless they are essential and documented.
- Add translator comments for strings containing placeholders.

## Testing

Before submitting a pull request:

1. Run PHP syntax checks on every PHP file.
2. Run the official WordPress Plugin Check in new-plugin mode.
3. Test Email and Email 2 independently.
4. Test first-match ordering and no-match fallback behaviour.
5. Test field shortcodes in recipient settings.
6. Confirm malformed email headers are ignored.
7. Confirm the plugin fails gracefully when Elementor Pro is inactive.

## Pull requests

Include:

- A concise explanation of the problem and solution.
- Reproduction and verification steps.
- Screenshots for editor-interface changes.
- Documentation and changelog updates when behaviour changes.

By contributing, you agree that your contribution is licensed under GPL-2.0-or-later.
