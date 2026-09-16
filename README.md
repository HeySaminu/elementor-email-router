# Conditional Email Router for Elementor

Conditional Email Router for Elementor adds conditional variants directly to the **Email** and **Email 2** actions in Elementor Pro Form widgets.

Use one form to send different messages, notify different recipients or suppress an email action according to submitted field values. The first enabled matching route is applied, and blank route settings inherit the form's normal Elementor email configuration.

> Elementor Pro is a commercial dependency and is not bundled with this plugin. This project is an independent add-on and is not affiliated with or endorsed by Elementor Ltd.

## Features

- Configure routes inside each Elementor Pro Form widget.
- Route **Email** and **Email 2** independently.
- Create multiple ordered variants; the first enabled match wins.
- Override the recipient, subject, message, sender, reply-to, Cc, Bcc and content type.
- Inherit any blank variant setting from the normal Elementor email action.
- Skip an email action when no route matches.
- Use Elementor field shortcodes such as `[field id="email"]` and `[all-fields]`.
- Match values case-insensitively by default, with an optional case-sensitive mode.
- Validate routed mail headers and reject malformed addresses.

## Requirements

- WordPress 6.5 or later
- PHP 7.4 or later
- Elementor
- Elementor Pro with the Form widget

When Elementor Pro is unavailable, the router remains inactive and displays an administrator notice instead of producing a fatal error.

## Installation

### WordPress dashboard

1. Open **Plugins > Add New > Upload Plugin**.
2. Select the plugin ZIP and choose **Install Now**.
3. Activate **Conditional Email Router for Elementor**.

### Manual installation

1. Copy the `conditional-email-router-for-elementor` directory to `wp-content/plugins/`.
2. Activate the plugin from **Plugins** in WordPress.

## Configuration

1. Edit a page containing an Elementor Pro Form widget.
2. Add **Email** and/or **Email 2** under **Actions After Submit**.
3. Open the corresponding email action.
4. Enable **Conditional Email Routing**.
5. Add variants in priority order.
6. Enter the Elementor field ID, choose an operator and enter the value to match.
7. Complete only the email settings that should differ from the normal action.
8. Choose what happens when no condition matches, then update the page.

Field IDs can be entered as `request_type`, `form-field-request_type` or `form_fields[request_type]`. The plugin normalizes these formats internally.

## Example

Assume a select field has the ID `request_type` and the choices `Complaint`, `Feedback` and `Question`.

Create these Email routes in order:

1. `request_type` **Equals** `Complaint`: send to the complaints team with a complaint-specific subject.
2. `request_type` **Equals** `Feedback`: send to the customer experience team.
3. `request_type` **Equals** `Question`: send to the support team.

Email 2 can use the same conditions with customer-facing messages and a recipient such as `[field id="email"]`.

## Operators

| Operator | Behaviour |
| --- | --- |
| Equals | Submitted value exactly matches the configured value. |
| Does not equal | Submitted value differs from the configured value. |
| Contains | Submitted value contains the configured text. |
| Does not contain | Submitted value does not contain the configured text. |
| Starts with | Submitted value begins with the configured text. |
| Ends with | Submitted value ends with the configured text. |
| Is empty | The field has no submitted value. |
| Is not empty | The field has a submitted value. |

## Inheritance rules

A blank route setting does not erase the normal Elementor setting. It inherits it. This applies to:

- To
- Subject
- Message
- From Email
- From Name
- Reply-To
- Cc
- Bcc
- Send As

For the primary Email action, Elementor expects Reply-To to contain an email field ID such as `email`. Email 2 also accepts an email address or a field shortcode.

## Privacy and security

The plugin does not create a public endpoint, contact an external service, collect analytics or independently transmit form data. Elementor continues to handle form validation, shortcode replacement and delivery through the WordPress mail system.

Routed header values are stripped of line breaks and control characters. Email addresses are validated before they replace Elementor's normal settings, and malformed values are ignored.

See [SECURITY.md](SECURITY.md) for responsible disclosure instructions.

## Troubleshooting

### Conditional controls are missing

- Confirm Elementor Pro is installed and active.
- Confirm **Email** or **Email 2** is listed under **Actions After Submit**.
- Reload the Elementor editor after activating the plugin.

### A route does not match

- Confirm the field ID, not the label, is configured.
- Check the submitted option value for exact wording.
- Review route order because only the first enabled match is applied.
- Enable case-sensitive matching only when required.

### Email is not delivered

The plugin changes Elementor's email settings but does not replace WordPress mail delivery. Test the form's normal email action, review the site mail log and confirm the SMTP or hosting mail configuration.

## Development

Development takes place in this repository. See [CONTRIBUTING.md](CONTRIBUTING.md) before opening a pull request and [docs/RELEASING.md](docs/RELEASING.md) for the release process.

Changes are recorded in [CHANGELOG.md](CHANGELOG.md).

## Support

Use [GitHub Issues](https://github.com/HeySaminu/elementor-email-router/issues) for reproducible bugs and feature requests. Do not post vulnerability details in a public issue.

## Licence

Conditional Email Router for Elementor is licensed under the [GNU General Public License v2.0 or later](LICENSE).
