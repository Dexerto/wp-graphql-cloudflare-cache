# WPGraphQL Cloudflare Cache

WordPress plugin that integrates [WPGraphQL](https://www.wpgraphql.com/) with Cloudflare's cache purge API. It adds `Cache-Tag` headers to GraphQL responses and automatically purges those tags via Cloudflare when content changes.

## Requirements

- WordPress 5.6+
- PHP 7.4+
- [WPGraphQL](https://wordpress.org/plugins/wp-graphql/) 1.16.0+
- [WPGraphQL Smart Cache](https://github.com/wp-graphql/wp-graphql-smart-cache) (provides the `graphql_purge` action that triggers purges)

## How it works

1. **Cache-Tag headers** -- On every GraphQL response, the plugin copies WPGraphQL's `X-GraphQL-Keys` header into a `Cache-Tag` header that Cloudflare reads for tag-based caching.
2. **Automatic purging** -- When content changes in WordPress, WPGraphQL Smart Cache fires a `graphql_purge` action with the relevant cache keys. This plugin sends those keys to Cloudflare's `/purge_cache` API to invalidate only the affected cached responses.

## Configuration

1. In the WordPress admin, go to **GraphQL > Settings > Cloudflare**.
2. Enter your **Zone ID** ([how to find it](https://developers.cloudflare.com/fundamentals/setup/find-account-and-zone-ids/)).
3. Enter a **Cloudflare API Token** with `Zone.Cache Purge` permission ([create one here](https://dash.cloudflare.com/profile/api-tokens)).
4. Check **Enable Cloudflare Revalidation**.

## Local development

### Prerequisites

- [Docker](https://docs.docker.com/get-docker/)
- [Node.js](https://nodejs.org/) 18+
- [Composer](https://getcomposer.org/)

### Setup

```bash
composer install
npm install
npm start          # starts WordPress at http://localhost:8888
npm run seed       # creates sample posts, pages, categories, and tags
```

**WP Admin:** http://localhost:8888/wp-admin/ (username: `admin`, password: `password`)

**GraphQL endpoint:** `http://localhost:8888/index.php?graphql`

The environment comes with WPGraphQL, WPGraphQL Smart Cache, and this plugin pre-activated.

### Available scripts

| Command | Description |
|---|---|
| `npm start` | Start the WordPress environment |
| `npm run seed` | Populate sample content (idempotent) |
| `npm stop` | Stop the environment |
| `npm run destroy` | Remove all containers and data |
| `composer phpcs` | Run PHP linting (WordPress/VIP coding standards) |
| `composer phpcbf` | Auto-fix lint issues |

## Commits and PRs

- [Conventional Commits](https://www.conventionalcommits.org/) enforced by a local git hook
- PRs are squash-merged to `main` -- the PR title becomes the commit message
