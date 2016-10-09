<?php

require_once plugin_dir_path( __FILE__ ) . '../utilities/WPSOLR_Regexp.php';
require_once plugin_dir_path( __FILE__ ) . '../extensions/wpsolr-extensions.php';
require_once plugin_dir_path( __FILE__ ) . '../wpsolr-filters.php';
require_once plugin_dir_path( __FILE__ ) . '../wpsolr-schema.php';

class WPSolrAbstractSolrClient {

	// Timeout in seconds when calling Solr
	const DEFAULT_SOLR_TIMEOUT_IN_SECOND = 30;

	public $solarium_client;
	protected $solarium_config;

	// Indice of the Solr index configuration in admin options
	protected $index_indice;

	// Index
	public $index;


	// Array of active extension objects
	protected $wpsolr_extensions;

	// Is blog in a galaxy
	protected $is_in_galaxy;

	// Is blog a slave search
	protected $is_galaxy_slave;

	// Is blog a master search
	protected $is_galaxy_master;

	// Galaxy slave filter value
	protected $galaxy_slave_filter_value;

	// Custom fields properties
	protected $custom_field_properties;

	/**
	 * Execute a solarium query. Retry 2 times if an error occurs.
	 *
	 * @param $solarium_client
	 * @param $solarium_update_query
	 *
	 * @return mixed
	 */
	protected function execute( $solarium_client, $solarium_update_query ) {


		for ( $i = 0; ; $i ++ ) {

			try {

				$result = $solarium_client->execute( $solarium_update_query );

				return $result;

			} catch ( Exception $e ) {

				// Catch error here, to retry in next loop, or throw error after enough retries.
				if ( $i >= 3 ) {
					throw $e;
				}

				// Sleep 3 seconds before retrying
				sleep( 3 );

			}

		}

	}

	/**
	 * Init details
	 */
	protected function init() {

		$this->custom_field_properties = WPSOLR_Global::getOption()->get_option_index_custom_field_properties();

		$this->init_galaxy();
	}

	/**
	 * Init galaxy details
	 */
	protected function init_galaxy() {

		$this->is_in_galaxy     = WPSOLR_Global::getOption()->get_search_is_galaxy_mode();
		$this->is_galaxy_slave  = WPSOLR_Global::getOption()->get_search_is_galaxy_slave();
		$this->is_galaxy_master = WPSOLR_Global::getOption()->get_search_is_galaxy_master();

		// After
		$this->galaxy_slave_filter_value = get_bloginfo( 'blogname' );
	}

	/**
	 * Geenrate a unique post_id for sites in a galaxy, else keep post_id
	 *
	 * @param $post_id
	 *
	 * @return string
	 */
	protected function generate_unique_post_id( $post_id ) {

		if ( ! $this->is_in_galaxy ) {
			// Current site is not in a galaxy: post_id is already unique
			return $post_id;
		}

		// Create a unique id by adding the galaxy name to the post_id
		$result = sprintf( '%s_%s', $this->galaxy_slave_filter_value, $post_id );

		return $result;
	}


	/**
	 * Get custom field error conversion action
	 *
	 * @param string $field_name Field name (like 'price_str')
	 *
	 * @return string
	 */
	public function get_custom_field_error_conversion_action( $field_name ) {

		// Get the properties of this field
		$custom_field_properties = WpSolrSchema::get_custom_field_properties( $field_name );

		$result = ( ! empty( $custom_field_properties[ WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION ] )
			? $custom_field_properties[ WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION ]
			: WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION_IGNORE_FIELD );

		return $result;
	}


	/**
	 * Get field without ending WpSolrSchema::_SOLR_DYNAMIC_TYPE_STRING  ('price_str' => 'price', 'title' => 'title')
	 *
	 * @param string $field_name_with_str_ending Field name (like 'price_str')
	 *
	 * @return string
	 */
	public function get_field_without_str_ending( $field_name_with_str_ending ) {

		$result = WPSOLR_Regexp::remove_string_at_the_end( $field_name_with_str_ending, WpSolrSchema::_SOLR_DYNAMIC_TYPE_STRING );

		return $result;
	}

}
