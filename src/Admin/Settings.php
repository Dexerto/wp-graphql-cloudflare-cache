<?php

namespace WpGraphQLCloudflareCache\Admin;

class Settings {
	/**
	 * Initialize the Settings Pages
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'graphql_register_settings', [ self::class, 'add_settings' ] );
	}

	public static function add_settings() {
		// This registers a new tab within the GraphQL Settings page
		register_graphql_settings_section( 'wp_graphql_cloudflare_cache', [
			'title' => __( 'Cloudflare', 'wp-graphql-cloudflare-cache' ),
		]);

		register_graphql_settings_fields( 'wp_graphql_cloudflare_cache', [
			[
				'name'              => 'cloudflare_enabled',
				'label'             => __( 'Enable Cloudflare Revalidation', 'wp-graphql-cloudflare-cache' ),
				'type'              => 'checkbox',
			],
			[
				'name'              => 'cloudflare_zone_id',
				'label'             => __( 'Zone ID', 'wp-graphql-cloudflare-cache' ),
				'desc'              => __( 'How to find your Zone ID at https://developers.cloudflare.com/fundamentals/setup/find-account-and-zone-ids/', 'wp-graphql-cloudflare-cache' ),
				'type'              => 'text',
			],
			[
				'name'              => 'cloudflare_api_token',
				'label'             => __( 'API Token', 'wp-graphql-cloudflare-cache' ),
				'desc'              => __( 'Create an API Token at https://dash.cloudflare.com/profile/api-tokens', 'wp-graphql-cloudflare-cache' ),
				'type'              => 'text',
			],
		]);
	}

}
