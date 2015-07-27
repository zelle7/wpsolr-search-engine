<?php

/**
 * Class OptionLocalization
 *
 * Manage localization options
 */
class OptionLocalization extends WpSolrExtensions {


	/*
	 * Section code constants. Do not change.
	 */
	const TERMS = 'terms';
	const SECTION_CODE_SEARCH_FORM = 'section_code_search_form';
	const SECTION_CODE_SORT = 'section_code_sort';
	const SECTION_CODE_FACETS = 'section_code_facets';

	/*
	 * Array key constants. Do not change.
	 */
	const KEY_SECTION_NAME = 'section_name';
	const KEY_SECTION_TERMS = 'section_terms';

	private $_localization_options;

	/*
	 * Constructor
	 *
	 * Subscribe to actions
	 */
	function __construct() {

		$this->_localization_options = $this->get_option_data( self::OPTION_LOCALIZATION );

	}

	/**
	 * Get the whole array of default options
	 *
	 * @return array Array of default options
	 */
	static function get_default_options() {

		return array(
			'localization_method' => 'localization_by_admin_options',
			self::TERMS           => array(
				self::SECTION_CODE_SEARCH_FORM =>
					array(
						self::KEY_SECTION_NAME  => 'Search Form',
						self::KEY_SECTION_TERMS => array(
							'search_form_button_label'     => _x( 'Search', 'Search form button label', 'wpsolr' ),
							'search_form_edit_placeholder' => _x( 'Search ....', 'Search edit placeholder', 'wpsolr' ),
						)
					),
				self::SECTION_CODE_SORT        =>
					array(
						self::KEY_SECTION_NAME  => 'Sort list',
						self::KEY_SECTION_TERMS => array(
							'sort_header'                              => _x( 'Sort by', 'Sort list header', 'wpsolr' ),
							wp_Solr::SORT_CODE_BY_RELEVANCY_DESC       => _x( 'More relevant', 'Sort list element', 'wpsolr' ),
							wp_Solr::SORT_CODE_BY_DATE_ASC             => _x( 'Newest', 'Sort list element', 'wpsolr' ),
							wp_Solr::SORT_CODE_BY_DATE_DESC            => _x( 'Oldest', 'Sort list element', 'wpsolr' ),
							wp_Solr::SORT_CODE_BY_NUMBER_COMMENTS_ASC  => _x( 'The more commented', 'Sort list element', 'wpsolr' ),
							wp_Solr::SORT_CODE_BY_NUMBER_COMMENTS_DESC => _x( 'The least commented', 'Sort list element', 'wpsolr' ),
						)
					),
				self::SECTION_CODE_FACETS      =>
					array(
						self::KEY_SECTION_NAME  => 'Facets',
						self::KEY_SECTION_TERMS => array(
							'facets_header'              => _x( 'Filters', 'Facets list header', 'wpsolr' ),
							'facets_title'               => _x( 'By %s', 'Facets list title', 'wpsolr' ),
							'facets_element_all_results' => _x( 'All results', 'Facets list element all results', 'wpsolr' ),
							'facets_element'             => _x( '%s (%d)', 'Facets list element name with #results', 'wpsolr' ),
						)
					)
			)
		);
	}

	static function is_internal_localized( $options ) {

		return ( isset( $options['localization_method'] ) && ( 'localization_by_admin_options' === $options['localization_method'] ) );
	}

	/**
	 * Get the whole array of options.
	 * Merge between default options and customized options.
	 *
	 * @param $is_internal_localized boolean Force internal options
	 *
	 * @return array Array of options
	 */
	static function get_options( $is_internal_localized = null ) {

		$default_options = self::get_default_options();

		$database_options = get_option( 'wdm_solr_localization_data', null );

		$is_internal_localized = is_bool( $is_internal_localized ) ? $is_internal_localized : self::is_internal_localized( $database_options );

		if ( ! $is_internal_localized ) {
			// No need to use the database translated options.
			// Use the default options, which contain gettext calls
			return $default_options;
		}

		if ( $database_options != null ) {
			// Replace default values with by database (customized) values with same key.
			// Why do that ? Because we can have added new terms in the default terms,
			// and they must be used even not customized by the user.

			return array_replace_recursive( $default_options, $database_options );

		} else {
			// Return default options not customized

			return $default_options;
		}

	}

	/**
	 * Get the whole array of localized terms.
	 *
	 * @param $options Array of options
	 *
	 * @return array Array of localized terms
	 */
	static function get_terms( $options ) {

		return ( isset( $options ) && isset( $options[ self::TERMS ] ) )
			? $options[ self::TERMS ]
			: array();
	}

	/**
	 * Get a section of terms
	 *
	 * @param $options    Array of all localized terms
	 * @param $section_code A section of terms
	 *
	 * @return array Section of terms
	 */
	static function get_section( $options, $section_code ) {

		return
			( isset( $options[ self::TERMS ] ) && isset( $options[ self::TERMS ][ $section_code ] ) )
				? $options[ self::TERMS ][ $section_code ]
				: array();
	}

	/**
	 * Get a section name
	 *
	 * @param $section A section of terms
	 *
	 * @return string The section name
	 */
	static function get_section_name( $section ) {

		return
			( ! empty( $section ) )
				? $section[ self::KEY_SECTION_NAME ]
				: '';
	}

	/**
	 * Get terms of a section
	 *
	 * @param $section Section
	 *
	 * @return array Terms of the section
	 */
	static function get_section_terms( $section ) {

		return
			( ! empty( $section ) )
				? $section[ self::KEY_SECTION_TERMS ]
				: array();
	}

	/**
	 * Get a localized term.
	 * If it does not exist, send by the term code instead.
	 *
	 * @param $section A section of terms
	 * @param $term_code A term code
	 *
	 * @return string Term
	 */
	static function get_section_term( $section, $term_code ) {

		return
			( isset( $section[ self::KEY_SECTION_TERMS ][ $term_code ] ) )
				? $section[ self::KEY_SECTION_TERMS ][ $term_code ]
				: $term_code;
	}

}