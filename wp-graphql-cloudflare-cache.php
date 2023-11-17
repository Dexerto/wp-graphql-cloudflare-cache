<?php
/**
 * WPGraphQL Cloudflare Cache
 *
 * Plugin Name:         WPGraphQL Cloudflare Cache
 * Plugin URI:          https://wordpress.org/plugins/wp-graphql-cloudflare-cache
 * GitHub Plugin URI:   https://github.com/gdidentity/wp-graphql-cloudflare-cache
 * Description:         Extends WPGraphQL to add Cloudflare Tags to GraphQL responses and purge them when content is updated.
 * Version:             1.0.0
 * Author:              humet
 * Author URI:          https://www.github.com/humet
 * Text Domain:         wp-graphql-cloudflare-cache
 * License:             GPL-3
 * License URI:         https://www.gnu.org/licenses/gpl-3.0.html
 */

use WpGraphQLCloudflareCache\Admin\Settings;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

add_action( 'admin_init', 'check_for_wpgraphql_dependency' );

if ( ! class_exists( 'WpGraphQLCloudflareCache' ) ) :

	/**
	 * This is the one true WpGraphQLCloudflareCache class
	 */
	final class WpGraphQLCloudflareCache {


		/**
		 * Stores the instance of the WpGraphQLCloudflareCache class
		 *
		 * @since 0.0.1
		 *
		 * @var WpGraphQLCloudflareCache The one true WpGraphQLCloudflareCache
		 */
		private static $instance;

		/**
		 * The instance of the WpGraphQLCloudflareCache object
		 *
		 * @since 0.0.1
		 *
		 * @return WpGraphQLCloudflareCache The one true WpGraphQLCloudflareCache
		 */
		public static function instance(): self {
			if ( ! isset( self::$instance ) && ! ( is_a( self::$instance, __CLASS__ ) ) ) {
				self::$instance = new self();
				self::$instance->setup_constants();
				if ( self::$instance->includes() ) {
					self::$instance->settings();
					self::$instance->headers();
					self::$instance->pluginLinks();
				}
			}

			/**
			 * Fire off init action.
			 *
			 * @param WpGraphQLCloudflareCache $instance The instance of the WpGraphQLCloudflareCache class
			 */
			do_action( 'wp_graphql_cloudflare_cache_init', self::$instance );

			// Return the WpGraphQLCloudflareCache Instance.
			return self::$instance;
		}

		/**
		 * Throw error on object clone.
		 * The whole idea of the singleton design pattern is that there is a single object
		 * therefore, we don't want the object to be cloned.
		 *
		 * @since 0.0.1
		 */
		public function __clone() {

			// Cloning instances of the class is forbidden.
			_doing_it_wrong(
				__FUNCTION__,
				esc_html__(
					'The WpGraphQLCloudflareCache class should not be cloned.',
					'wp-graphql-cloudflare-cache'
				),
				'0.0.1'
			);
		}

		/**
		 * Disable unserializing of the class.
		 *
		 * @since 0.0.1
		 */
		public function __wakeup() {

			// De-serializing instances of the class is forbidden.
			_doing_it_wrong(
				__FUNCTION__,
				esc_html__(
					'De-serializing instances of the WpGraphQLCloudflareCache class is not allowed.',
					'wp-graphql-cloudflare-cache'
				),
				'0.0.1'
			);
		}

		/**
		 * Setup plugin constants.
		 *
		 * @since 0.0.1
		 */
		private function setup_constants(): void {

			if ( ! function_exists( 'get_plugin_data' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			}

			// Plugin version.
			if ( ! defined( 'wp_graphql_cloudflare_cache_VERSION' ) ) {
				define( 'wp_graphql_cloudflare_cache_VERSION', get_plugin_data( __FILE__ )['Version'] );
			}

			// Plugin Folder Path.
			if ( ! defined( 'wp_graphql_cloudflare_cache_PLUGIN_DIR' ) ) {
				define( 'wp_graphql_cloudflare_cache_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
			}

			// Plugin Folder URL.
			if ( ! defined( 'wp_graphql_cloudflare_cache_PLUGIN_URL' ) ) {
				define( 'wp_graphql_cloudflare_cache_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
			}

			// Plugin Root File.
			if ( ! defined( 'wp_graphql_cloudflare_cache_PLUGIN_FILE' ) ) {
				define( 'wp_graphql_cloudflare_cache_PLUGIN_FILE', __FILE__ );
			}

			// Whether to autoload the files or not.
			if ( ! defined( 'wp_graphql_cloudflare_cache_AUTOLOAD' ) ) {
				define( 'wp_graphql_cloudflare_cache_AUTOLOAD', true );
			}
		}

		/**
		 * Uses composer's autoload to include required files.
		 *
		 * @since 0.0.1
		 *
		 * @return bool
		 */
		private function includes(): bool {

			// Autoload Required Classes.
			if ( defined( 'wp_graphql_cloudflare_cache_AUTOLOAD' ) && false !== wp_graphql_cloudflare_cache_AUTOLOAD ) {
				if ( file_exists( wp_graphql_cloudflare_cache_PLUGIN_DIR . 'vendor/autoload.php' ) ) {
					require_once wp_graphql_cloudflare_cache_PLUGIN_DIR . 'vendor/autoload.php';
				}

				// Bail if installed incorrectly.
				if ( ! class_exists( '\WpGraphQLCloudflareCache\Admin\Settings' ) ) {
					add_action( 'admin_notices', [ $this, 'missing_notice' ] );
					return false;
				}
			}

			return true;
		}

		/**
		 * Composer dependencies missing notice.
		 *
		 * @since 0.0.1
		 */
		public function missing_notice(): void {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			} ?>
			<div class="notice notice-error">
				<p>
					<?php esc_html_e( 'WPGraphQL Cloudflare Cache appears to have been installed without its dependencies. It will not work properly until dependencies are installed. This likely means you have cloned Next.js On-Demand Revalidation from Github and need to run the command `composer install`.', 'wp-graphql-cloudflare-cache' ); ?>
				</p>
			</div>
			<?php
		}

		/**
		 * Set up settings.
		 *
		 * @since 0.0.1
		 */
		private function settings(): void {

			$settings = new Settings();
			$settings->init();
		}

		/**
		 * Set up Purge.
		 *
		 * @since 0.0.1
		 */
		private function headers(): void {
			\WpGraphQLCloudflareCache\ResponseHeaders::init();
			\WpGraphQLCloudflareCache\Purge::init();
		}


		/**
		 * Set up Action Links.
		 *
		 * @since 0.0.1
		 */
		private function pluginLinks(): void {

			// Setup Settings link.
			add_filter('plugin_action_links_' . plugin_basename( __FILE__ ), function ( $links ) {
				$links[] = '<a href="/wp-admin/admin.php?page=wp-graphql-cloudflare-cache">Settings</a>';

				return $links;
			});
		}
	}

	function check_for_wpgraphql_dependency() {
		if ( is_admin() && current_user_can( 'activate_plugins' ) && !is_plugin_active( 'wp-graphql/wp-graphql.php' ) ) {
			add_action( 'admin_notices', 'show_wpgraphql_dependency_notice' );
	
			deactivate_plugins( plugin_basename( __FILE__ ) );
	
			if ( isset( $_GET['activate'] ) ) {
				unset( $_GET['activate'] );
			}
		} else {
			// Ensure get_plugin_data is available
			if ( ! function_exists('get_plugin_data') ){
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			}
	
			$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/wp-graphql/wp-graphql.php' );
			$plugin_version = $plugin_data['Version'];
	
			if ( version_compare( $plugin_version, '1.16.0', '<' ) ) {
				add_action( 'admin_notices', 'show_wpgraphql_version_notice' );
				
				deactivate_plugins( plugin_basename( __FILE__ ) );
		
				if ( isset( $_GET['activate'] ) ) {
					unset( $_GET['activate'] );
				}
			}
		}
	}
	
	function show_wpgraphql_dependency_notice() {
		?>
		<div class="error">
			<p><?php echo esc_html( 'Error: Plugin "WP GraphQL Cloudflare Cache" requires "WPGraphQL" to be activated.', 'wp-graphql-cloudflare-cache' ); ?></p>
		</div>
		<?php
	}

	function show_wpgraphql_version_notice() {
		?>
		<div class="error">
			<p><?php echo esc_html( 'Error: Plugin "WP GraphQL Cloudflare Cache" requires "WPGraphQL" version 1.16.0 or higher.', 'wp-graphql-cloudflare-cache' ); ?></p>
		</div>
		<?php
	}

endif;

\WpGraphQLCloudflareCache::instance();
