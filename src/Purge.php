<?php

namespace WpGraphQLCloudflareCache;

class Purge {
    const CLOUDFLARE_API_URL = 'https://api.cloudflare.com/client/v4/zones/';

	public static function init() {
		add_action( 'graphql_purge', [ self::class, 'purge_tags' ], 10, 1 );
	}

	public static function purge_tags( $purge_keys, $event = '', $hostname = '' ) {
        $cloudflare_enabled = get_graphql_setting( 'cloudflare_enabled', false, 'wp_graphql_cloudflare_cache' );
        $cloudflare_zone_id = get_graphql_setting( 'cloudflare_zone_id', '', 'wp_graphql_cloudflare_cache' );
        $cloudflare_api_token = get_graphql_setting( 'cloudflare_api_token', '', 'wp_graphql_cloudflare_cache' );

        if ( $cloudflare_enabled !== "on" || empty($cloudflare_zone_id) || empty($cloudflare_api_token) ) {
			return;
		}

		self::cloudflare_cache_purge( $purge_keys, $cloudflare_zone_id, $cloudflare_api_token );
	}

    public static function cloudflare_cache_purge( $purge_keys, $zone_id, $auth_key ) {
        if ( ! is_array( $purge_keys ) ) {
            $purge_keys = [ $purge_keys ];
        }
        
		$api_url = self::CLOUDFLARE_API_URL . $zone_id . '/purge_cache';
        
        $response = wp_remote_post( $api_url, [
			'method' => 'POST',
			'headers' => [
				'Content-Type' => 'application/json',
				'Authorization' => 'Bearer ' . $auth_key,
			],
			'body' => json_encode([
				'tags' => $purge_keys
			])
		]);

        if ( is_wp_error( $response ) ) {
            $error_message = $response->get_error_message();
			error_log( 'Cloudflare Cache Purge Error: ' . $error_message );
			return $error_message;
        }

        return json_decode( wp_remote_retrieve_body( $response ), true );
	}
}
