<?php

namespace WpGraphQLCloudflareCache;

class Purge {
    const CLOUDFLARE_API_URL = 'https://api.cloudflare.com/client/v4/zones/';
    const ERROR_TRANSIENT    = 'wpgraphql_cf_purge_error';

	public static function init() {
		add_action( 'graphql_purge', [ self::class, 'purge_tags' ], 10, 1 );
		add_action( 'admin_notices', [ self::class, 'show_purge_error_notice' ] );
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
			'body' => wp_json_encode([
				'tags' => $purge_keys
			])
		]);

        if ( is_wp_error( $response ) ) {
			self::set_error( $response->get_error_message() );
			return false;
        }

        $status_code = wp_remote_retrieve_response_code( $response );
        $body        = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( $status_code < 200 || $status_code >= 300 || empty( $body['success'] ) ) {
			$errors = isset( $body['errors'] ) ? wp_json_encode( $body['errors'] ) : "HTTP $status_code";
			self::set_error( $errors );
			return false;
        }

        return $body;
	}

	private static function set_error( $message ) {
		error_log( 'Cloudflare Cache Purge Error: ' . $message );
		set_transient( self::ERROR_TRANSIENT, $message, HOUR_IN_SECONDS );
	}

	public static function show_purge_error_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$error = get_transient( self::ERROR_TRANSIENT );
		if ( ! $error ) {
			return;
		}

		delete_transient( self::ERROR_TRANSIENT );
		?>
		<div class="notice notice-error">
			<p>
				<?php
				printf(
					/* translators: %s: error message from Cloudflare API */
					esc_html__( 'Cloudflare cache purge failed: %s', 'wp-graphql-cloudflare-cache' ),
					esc_html( $error )
				);
				?>
			</p>
		</div>
		<?php
	}
}
