<?php

namespace WpGraphQLCloudflareCache;

class ResponseHeaders {

	public static function init() {
		add_filter( 'graphql_response_headers_to_send', [ self::class, 'duplicateKeysToTags' ] );
	}

	public static function duplicateKeysToTags( $headers ) {
		if ( ! is_array( $headers ) ) {
			return $headers;
		}

		if ( isset( $headers['X-GraphQL-Keys'] ) ) {
			$headers['Cache-Tag'] = $headers['X-GraphQL-Keys'];
		}

		return $headers;
	}
	
}
