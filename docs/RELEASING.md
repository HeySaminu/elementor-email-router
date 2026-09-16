# Release Process

GitHub is the development source. WordPress.org SVN is the distribution source after directory approval.

## Prepare the release

1. Update the `Version` header in `conditional-email-router-for-elementor.php`.
2. Update `Stable tag` and the changelog in `readme.txt`.
3. Update `CHANGELOG.md` and replace the Unreleased comparison link.
4. Confirm the main file, directory and text domain use `conditional-email-router-for-elementor`.
5. Run PHP syntax checks and the official WordPress Plugin Check.
6. Test routing with current supported WordPress, Elementor and Elementor Pro versions.
7. Build and inspect an installation ZIP containing one top-level plugin directory.

## GitHub release

1. Commit the release changes.
2. Create an annotated tag such as `v2.2.0`.
3. Push the branch and tag to GitHub.
4. Create a GitHub release from the tag and attach the tested installation ZIP.

## WordPress.org release

After approval, check out the assigned SVN repository. It contains:

- `trunk/` for current plugin files.
- `tags/` for immutable release copies.
- `assets/` for directory icons, banners and screenshots.

Copy the tested plugin files into `trunk/`, then create a matching tag from trunk. The `Stable tag` in `trunk/readme.txt` must match the version directory under `tags/`.

Do not include Git metadata, GitHub workflow files or WordPress.org listing assets in the installable plugin ZIP.

## Post-release checks

1. Install the published ZIP on a clean test site.
2. Confirm the displayed version and dependency notice.
3. Verify the WordPress.org page, screenshots and changelog.
4. Submit an Email and Email 2 routing test.
5. Monitor the support forum and security reports.
