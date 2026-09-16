# Elementor Email Router

Adds conditional email variants directly to Elementor Pro Form widgets.

## Configure a form

1. Open the Form widget in Elementor.
2. Add **Email** and/or **Email 2** under **Actions After Submit**.
3. Open the corresponding Email settings section.
4. Enable **Conditional Email Routing**.
5. Add conditional email variants in priority order. The first enabled match wins.
6. Choose whether Elementor should send the normal email or skip that email action when no condition matches.

Each variant can override:

- To
- Subject
- Message
- From Email
- From Name
- Reply-To
- Cc
- Bcc
- Send As

Blank variant email fields inherit the normal Email or Email 2 setting. This includes Subject, From Name, and From Email. The default From Email for routed forms is `web@sterling.ng`. Elementor field shortcodes such as `[field id="email"]` and `[all-fields]` are supported.

For the primary Email action, Reply-To should be the ID of an email form field, such as `email`. Email 2 also accepts an email address or field shortcode.

## Conditions

Enter an Elementor field ID such as `request_type`. The plugin also accepts `form-field-request_type` and `form_fields[request_type]` and normalizes them automatically.

Available operators:

- Equals
- Does not equal
- Contains
- Does not contain
- Starts with
- Ends with
- Is empty
- Is not empty

Matching is case-insensitive by default. It can be made case-sensitive per variant.

## Security

The plugin does not expose its own public endpoint or read submitted request variables directly. Elementor continues to handle form authorization, validation, shortcode replacement, and email delivery. Routed header values are validated, line breaks and control characters are removed, and malformed route settings are ignored.
