# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

- `npm run lint` — lint PHP inside Docker (preferred; uses PHP 8.2 matching production)
- `npm run lint:fix` — auto-fix lint issues inside Docker
- `composer phpcs` — lint PHP on host (requires `composer install` first; may fail on newer PHP versions due to phpcompatibility sniff)
- `composer phpcbf` — auto-fix lint issues on host

Lint **must** pass before committing. Always run `npm run lint` (or `npm run lint:fix`) before creating a commit.

There is no test suite.

## Architecture

WordPress plugin that bridges WPGraphQL's cache system with Cloudflare's cache purge API. Requires WPGraphQL ≥1.16.0 and WPGraphQL Smart Cache (provides the `graphql_purge` action).

**Entry point:** `wp-graphql-cloudflare-cache.php` — singleton (`WpGraphQLCloudflareCache`) that checks for WPGraphQL, loads composer autoload, then initializes the three classes below.

**Three classes** (PSR-4 under `WpGraphQLCloudflareCache\` → `src/`):

- `ResponseHeaders` — filters `graphql_response_headers_to_send` to copy `X-GraphQL-Keys` into a `Cache-Tag` header that Cloudflare reads
- `Purge` — queues purge keys from `graphql_purge` actions and sends a single batched request to Cloudflare's `/purge_cache` API on the `shutdown` hook
- `Admin\Settings` — registers a "Cloudflare" tab in WPGraphQL's settings page via `graphql_register_settings` (zone ID, API token, enable toggle)

All settings are stored under the `wp_graphql_cloudflare_cache` option group and accessed via `get_graphql_setting()`.

## Code Style

- WordPress Coding Standards with VIP rules (phpcs.xml.dist)
- Short array syntax `[]` is enforced (not `array()`)
- camelCase method names are allowed (ValidFunctionName rule is excluded)

## Commits & PRs

- Conventional Commits enforced by local git hook (`.githooks/commit-msg`)
- PRs are squash-merged — PR title becomes the commit message, so it must follow the same format
- Branch from `main`, PR back to `main`

## Releases

Do **not** manually edit version numbers. Two files contain the version (`wp-graphql-cloudflare-cache.php` header, `readme.txt` stable tag) — the **Create Release** workflow (`release.yml`) bumps both, commits, tags, creates a GitHub Release, and the **Deploy to WordPress.org** workflow triggers on the `release: published` event.
