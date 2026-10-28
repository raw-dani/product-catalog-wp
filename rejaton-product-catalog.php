<?php
/**
 * Plugin Name:       Rejaton Product Catalog
 * Plugin URI:        https://rejaton.com/
 * Description:       Katalog produk pompa air tenaga surya mandiri tanpa ketergantungan WooCommerce, dilengkapi custom field, dynamic template engine, filter frontend, dan integrasi WhatsApp CTA.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Rohmat Ali Wardani
 * Author URI:        https://rejaton.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       rejaton-product-catalog
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'REJATON_CATALOG_VERSION', '1.0.2' );
define( 'REJATON_CATALOG_FILE', __FILE__ );
define( 'REJATON_CATALOG_PATH', plugin_dir_path( __FILE__ ) );
define( 'REJATON_CATALOG_URL', plugin_dir_url( __FILE__ ) );

// Autoload / include core class files.
require_once REJATON_CATALOG_PATH . 'includes/helpers.php';
require_once REJATON_CATALOG_PATH . 'includes/class-activator.php';
require_once REJATON_CATALOG_PATH . 'includes/class-deactivator.php';
require_once REJATON_CATALOG_PATH . 'includes/class-post-types.php';
require_once REJATON_CATALOG_PATH . 'includes/class-taxonomies.php';
require_once REJATON_CATALOG_PATH . 'includes/class-field-registry.php';
require_once REJATON_CATALOG_PATH . 'includes/class-product-meta.php';
require_once REJATON_CATALOG_PATH . 'includes/class-template-repository.php';
require_once REJATON_CATALOG_PATH . 'includes/class-template-validator.php';
require_once REJATON_CATALOG_PATH . 'includes/class-template-renderer.php';
require_once REJATON_CATALOG_PATH . 'includes/class-product-query.php';
require_once REJATON_CATALOG_PATH . 'includes/class-filter-controller.php';
require_once REJATON_CATALOG_PATH . 'includes/class-shortcodes.php';
require_once REJATON_CATALOG_PATH . 'includes/class-settings.php';
require_once REJATON_CATALOG_PATH . 'includes/class-seo-integration.php';
require_once REJATON_CATALOG_PATH . 'includes/class-cache.php';
require_once REJATON_CATALOG_PATH . 'includes/class-plugin.php';

// Admin modules
if ( is_admin() ) {
	require_once REJATON_CATALOG_PATH . 'admin/class-admin.php';
	require_once REJATON_CATALOG_PATH . 'admin/class-product-fields.php';
	require_once REJATON_CATALOG_PATH . 'admin/class-field-manager.php';
	require_once REJATON_CATALOG_PATH . 'admin/class-template-editor.php';
	require_once REJATON_CATALOG_PATH . 'admin/class-duplicate.php';
	require_once REJATON_CATALOG_PATH . 'admin/class-admin-notices.php';
}

// Public module
require_once REJATON_CATALOG_PATH . 'public/class-public.php';

register_activation_hook( __FILE__, array( 'Rejaton_Catalog_Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Rejaton_Catalog_Deactivator', 'deactivate' ) );

/**
 * Bootstrap the plugin.
 */
function rejaton_catalog_init() {
	$plugin = Rejaton_Catalog_Plugin::get_instance();
	$plugin->run();
}
add_action( 'plugins_loaded', 'rejaton_catalog_init' );
