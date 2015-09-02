<?php

/**
 * Class PluginWpml
 *
 * Manage WPML plugin
 * @link http://www.wpml.org/
 */
class PluginWpml extends WpSolrExtensions {

	/*
	 * Constructor
	 * Subscribe to actions
	 */

	function __construct() {

		$this->_extension_groups_options = $this->get_option_data( self::EXTENSION_WPML );

		add_filter( WpSolrFilters::WPSOLR_FILTER_SOLARIUM_DOCUMENT_FOR_UPDATE, array(
			$this,
			'add_language_fields_to_document_for_update',
		), 10, 4 );


		add_action( WpSolrFilters::WPSOLR_ACTION_SOLARIUM_QUERY, array( $this, 'set_query_keywords' ), 10, 1 );

		add_filter( WpSolrFilters::WPSOLR_FILTER_SEARCH_PAGE_URL, array(
			$this,
			'set_search_page_url',
		), 10, 2 );

	}


	/**
	 * Add multi-language fields to a Solarium document
	 *
	 * @param $solarium_document_for_update
	 * @param $solr_indexing_options
	 * @param $post
	 * @param $attachment_body
	 *
	 * @return object Solarium document updated with multi-language fields
	 */
	function add_language_fields_to_document_for_update( $solarium_document_for_update, $solr_indexing_options, $post, $attachment_body ) {

		// Retrieve current document language code from WPML
		$args               = array(
			'element_id'   => $solarium_document_for_update->id,
			'element_type' => $solarium_document_for_update->type,
		);
		$post_language_code = apply_filters( 'wpml_element_language_code', null, $args );

		if ( ! is_null( $post_language_code ) ) {
			// Now, just add the language fields

			// Language field
			$solarium_document_for_update[ WpSolrSchema::_FIELD_NAME_LANGUAGE_CODE ] = $post_language_code;

			// Add fields for the language code
			foreach ( WpSolrSchema::$multi_language_fields as $field_definitions ) {
				// Create the dynamic field name
				$field_name = self::create_field_name_for_language_code( $field_definitions['field_name'], $post_language_code, $field_definitions['field_extension'] );
				// Add the dynamic field name to the Solarium document
				$solarium_document_for_update->$field_name = $solarium_document_for_update[ $field_definitions['field_name'] ];
			}

		}

		return $solarium_document_for_update;
	}


	/**
	 * Get current language code
	 *
	 * @return string Current language code
	 */
	function get_current_language_code() {

		return apply_filters( 'wpml_current_language', null );

	}

	/**
	 * Get default language code
	 *
	 * @return string Default language code
	 */
	function get_default_language_code() {

		return apply_filters( 'wpml_default_language', null );

	}

	/**
	 * Create a field name for a language code
	 * Example: title_en_t from title
	 *
	 * @param $field_name Field name
	 * @param $language_code Language code
	 * @param $solr_dynamic_type_post_fix Solr postfix dynamic type of the field name (_t, _s, _i, ...)
	 *
	 * @return string New field name
	 */
	function create_field_name_for_language_code( $field_name, $language_code, $solr_dynamic_type_post_fix ) {

		return $field_name . '_' . $language_code . $solr_dynamic_type_post_fix;
	}


	/**
	 *
	 * Replace default field by language specific fields in query
	 *
	 * @param $parameters array
	 *
	 */
	public function set_query_keywords( $parameters ) {

		$current_language_code = self::get_current_language_code();

		$query        = $parameters[ WpSolrFilters::WPSOLR_ACTION_SOLARIUM_QUERY__PARAM_SOLARIUM_QUERY ];
		$search_terms = $parameters[ WpSolrFilters::WPSOLR_ACTION_SOLARIUM_QUERY__PARAM_SEARCH_TERMS ];

		// Add multi-language fields to the query
		$query_string = '';
		foreach ( WpSolrSchema::$multi_language_fields as $field_definitions ) {
			// Create the dynamic field name
			$field_name = self::create_field_name_for_language_code( $field_definitions['field_name'], $current_language_code, $field_definitions['field_extension'] );
			// Add the dynamic field name to the query
			$query_string .= ( $query_string === '' ? '' : ' OR ' ) . $field_name . ':' . $search_terms;
		}

		$query->setQuery( $query_string === '' ? ( WpSolrSchema::_FIELD_NAME_DEFAULT_QUERY . ':' . $search_terms ) : $query_string );

	}


	/**
	 * Define the sarch page url for the current language
	 *
	 * @param $default_search_page_id
	 * @param $default_search_page_url
	 *
	 * @return string
	 */
	function set_search_page_url( $default_search_page_url, $default_search_page_id ) {

		$translated_search_page_url = apply_filters( 'wpml_permalink', $default_search_page_url, null );

		if ( is_null( apply_filters( 'wpml_object_id', $default_search_page_id, 'page', false ) ) ) {

			// Need to create the translated search page. Once only.
			do_action( 'wpml_make_post_duplicates', $default_search_page_id );

		}

		return $translated_search_page_url;
	}
}