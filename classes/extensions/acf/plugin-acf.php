<?php

/**
 * Class PluginAcf
 *
 * Manage Advanced Custom Fields (ACF) plugin
 * @link https://wordpress.org/plugins/advanced-custom-fields/
 */
class PluginAcf extends WpSolrExtensions {

	// Prefix of ACF fields
	const FIELD_PREFIX = '_';

	// Polylang options
	const _OPTIONS_NAME = 'wdm_solr_extension_acf_data';

	// acf fields indexed by name.
	private $_fields;

	// Options
	private $_options;


	/**
	 * Factory
	 *
	 * @return PluginAcf
	 */
	static function create() {

		return new self();
	}

	/*
	 * Constructor
	 * Subscribe to actions
	 */

	function __construct() {

		$this->_options = self::get_option_data( self::EXTENSION_ACF );

		add_filter( WpSolrFilters::WPSOLR_FILTER_SEARCH_PAGE_FACET_NAME, array(
			$this,
			'get_field_label'
		), 10, 1 );


	}


	/**
	 * Retrieve all field keys of all ACF fields.
	 *
	 * @return array
	 */
	function get_fields() {
		global $wpdb;

		// Uue cached fields if exist
		if ( isset( $this->_fields ) ) {
			return $this->_fields;
		}

		$fields = array();

		// Else create the cached fields
		$results = $wpdb->get_results( "SELECT distinct meta_key, meta_value
                                        FROM $wpdb->postmeta
                                        WHERE meta_key like '_%'
                                        AND   meta_value like 'field_%'" );

		$nb_results = count( $results );
		for ( $loop = 0; $loop < $nb_results; $loop ++ ) {
			$fields[ $results[ $loop ]->meta_key ] = $results[ $loop ]->meta_value;

		}

		// Save the cache
		$this->_fields = $fields;

		return $this->_fields;
	}


	/**
	 * Get the ACF field label from the custom field name.
	 *
	 * @param $custom_field_name
	 *
	 * @return mixed
	 */
	public
	function get_field_label(
		$custom_field_name
	) {

		$result = $custom_field_name;

		if ( ! isset( $this->_options['display_acf_label_on_facet'] ) ) {
			// No need to replace custom field name by acf field label
			return $result;
		}

		// Retrieve field among ACF fields
		$fields = $this->get_fields();
		if ( isset( $fields[ self::FIELD_PREFIX . $custom_field_name ] ) ) {
			$field_key = $fields[ self::FIELD_PREFIX . $custom_field_name ];
			$field     = get_field_object( $field_key );
			$result    = isset( $field['label'] ) ? $field['label'] : $custom_field_name;
		}

		return $result;
	}

}