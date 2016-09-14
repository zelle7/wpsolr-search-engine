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
	 * Get custom field properties
	 *
	 * @param string $field_name Field name (like 'price_str')
	 *
	 * @return array
	 */
	public function get_custom_field_properties( $field_name ) {

		// Get the properties of custom fields
		$custom_fields_properties = WPSOLR_Global::getOption()->get_option_index_custom_field_properties();

		$result = ( ! empty( $custom_fields_properties[ $field_name ] ) ? $custom_fields_properties[ $field_name ] : array() );

		return $result;
	}

	/**
	 * Get custom field type
	 *
	 * @param string $field_name Field name (like 'price_str')
	 *
	 * @return string
	 */
	public function get_custom_field_dynamic_type( $field_name ) {

		// Get the properties of this field
		$custom_field_properties = $this->get_custom_field_properties( $field_name );

		$result = ( ! empty( $custom_field_properties[ WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_SOLR_TYPE ] )
			? $custom_field_properties[ WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_SOLR_TYPE ]
			: '' );

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
		$custom_field_properties = $this->get_custom_field_properties( $field_name );

		$result = ( ! empty( $custom_field_properties[ WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION ] )
			? $custom_field_properties[ WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION ]
			: WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION_IGNORE_FIELD );

		return $result;
	}

	/**
	 * For compatibility reasons with previous versions (13.5), all custom fields are ending with _str.
	 * In field name, replace _str by a dynamic type
	 * ('price_str', '_f') => 'price_f'
	 *
	 * @param string $field_name Field name, like 'price_str', or 'title'
	 *
	 * @return string
	 */
	public function replace_field_name_extension( $field_name ) {

		$solr_dynamic_type_id = $this->get_custom_field_dynamic_type( $field_name );

		$result = ! empty( $solr_dynamic_type_id )
			? str_replace( WpSolrSchema::_SOLR_DYNAMIC_TYPE_STRING, $solr_dynamic_type_id, $field_name )
			: $field_name;

		return $result;
	}

	/**
	 * For compatibility reasons with previous versions (13.5), all custom fields are ending with _str.
	 * In field name, replace dynamic type by a _str
	 * 'price_f' => 'price_str'
	 * 'title' => 'title'
	 *
	 * @param string $field_name Field name, like 'price_str', or 'title'
	 *
	 * @return string
	 */
	public function replace_field_name_extension_back( $field_name ) {

		$extension = WpSolrSchema::EXTENSION_SEPARATOR . WPSOLR_Regexp::extract_last_separator( $field_name, WpSolrSchema::EXTENSION_SEPARATOR );

		if ( ( WpSolrSchema::EXTENSION_SEPARATOR === $extension ) || ( WpSolrSchema::_SOLR_DYNAMIC_TYPE_STRING === $extension ) ) {
			// No extension, nothing to do: title, content ... remain the same
			// color_str ... remain the same
			return $field_name;
		}

		if ( ! array_key_exists( $extension, WpSolrSchema::get_solr_dynamic_entensions() ) ) {
			// Extension is unknown, do nothing
			// price_def
			return $field_name;
		}


		return $field_name . WpSolrSchema::_SOLR_DYNAMIC_TYPE_STRING;

	}

	/**
	 * Get field without ending '_str'  ('price_str' => 'price', 'title' => 'title')
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
