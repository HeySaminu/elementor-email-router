# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and the project follows [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [2.2.0] - 2026-09-16

### Added

- Conditional routing controls inside Elementor Pro Email and Email 2 actions.
- Ordered, first-match email variants.
- Equals, not-equals, contains, not-contains, starts-with, ends-with, empty and not-empty operators.
- Per-variant recipient, subject, message, sender, reply-to, Cc, Bcc and content-type settings.
- Configurable fallback behaviour when no condition matches.
- Elementor Pro dependency notice.
- WordPress.org metadata, privacy documentation and GPL licensing.

### Changed

- Renamed the public plugin to Conditional Email Router for Elementor.
- Blank route settings now inherit Elementor's normal email configuration.
- Removed organization-specific sender defaults from the public distribution.

### Security

- Validated routed email addresses and recipient lists.
- Removed line breaks, tags and control characters from mail headers.
- Rejected malformed route settings and unsafe header values.

[Unreleased]: https://github.com/HeySaminu/elementor-email-router/compare/v2.2.0...HEAD
[2.2.0]: https://github.com/HeySaminu/elementor-email-router/compare/9353a6d...v2.2.0
