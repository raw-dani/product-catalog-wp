<?php
/**
 * Main Plugin Orchestrator for Rejaton Product Catalog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Rejaton_Catalog_Plugin {

	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {}

	public function run() {
		$post_types = new Rejaton_Catalog_Post_Types();
		$post_types->init();

		$taxonomies = new Rejaton_Catalog_Taxonomies();
		$taxonomies->init();

		$filter_controller = new Rejaton_Catalog_Filter_Controller();
		$filter_controller->init();

		$shortcodes = new Rejaton_Catalog_Shortcodes();
		$shortcodes->init();

		$settings = new Rejaton_Catalog_Settings();
		$settings->init();

		$seo = new Rejaton_Catalog_SEO_Integration();
		$seo->init();

		$cache = new Rejaton_Catalog_Cache();
		$cache->init();

		if ( is_admin() ) {
			$admin = new Rejaton_Catalog_Admin();
			$admin->init();

			$product_fields = new Rejaton_Catalog_Product_Fields();
			$product_fields->init();

			$field_manager = new Rejaton_Catalog_Field_Manager();
			$field_manager->init();

			$template_editor = new Rejaton_Catalog_Template_Editor();
			$template_editor->init();

			$duplicate = new Rejaton_Catalog_Duplicate();
			$duplicate->init();

			$notices = new Rejaton_Catalog_Admin_Notices();
			$notices->init();
		}

		$public = new Rejaton_Catalog_Public();
		$public->init();
	}
}
