<?php

/* Include the Solr client */
require_once plugin_dir_path( __FILE__ ) . '../solr/wpsolr-search-solr-client.php';

/* Include the WPSOLR_Url class */
require_once plugin_dir_path( __FILE__ ) . 'wpsolr-url.php';

/**
 * Api used by (child) themes to customize the search and search form templates.
 * The Api will accept parameters, to eventually override the admin options.
 * The Api will return pure data. No UI elements (html code, css class, ...).
 *
 * It is up to the (child) theme to display the data returned by the Api.
 */
class WPSOLR_Query extends WP_Query {

	// Search template file relative to the current (child) theme directory
	const _SEARCH_PAGE_TEMPLATE = 'wpsolr-search-engine/search.php';

	// Search form template file relative to the current (child) theme directory
	const _SEARCH_FORM_PAGE_TEMPLATE = 'wpsolr-search-engine/searchform.php';

	// Search page slug
	const _SEARCH_PAGE_SLUG = 'search-wpsolr';

	// Search page query parameter
	const _SEARCH_PARAMETER_QUERY_NAME = 'search';

	// The theme search form template
	protected $search_form_template;

	// The theme search page template
	protected $search_page_template;

	// Solr client
	protected $solr_client;

	// Localization options
	protected $localization_options;

	// Search options
	protected $search_options;

	// WPSOLR_Url object corresponding to the query
	protected $url;

	// Facet options
	protected $facet_options;

	// Sort options
	protected $sort_options;

	// WP_Query
	protected $wp_query;

	/*
	 * Clone WP_Query
	 */
	public static function copy( $wp_query ) {
		return new WPSOLR_Query( $wp_query );
	}

	/**
	 * WPSOLR_Query constructor.
	 *
	 * @param $has_search_form
	 */
	public function __construct( $wp_query = null ) {

		if ( ! is_null( $wp_query ) ) {

			// Reuse some of $wp_query. Especially the 'paged' parameter.
			$this->wp_query   = $wp_query;
			$this->query_vars = $wp_query->query_vars;

			$this->url = new WPSOLR_Url();
		}


		$this->localization_options = OptionLocalization::get_options();
		$this->search_options       = get_option( 'wdm_solr_res_data' );
		$this->facet_options        = get_option( 'wdm_solr_facet_data' );
		$this->sort_options         = get_option( 'wdm_solr_sortby_data' );

		$this->search_form_template = '';
		$this->search_page_template = '';

		$this->solr_client = WPSolrSearchSolrClient::create_from_default_index_indice();


		// Search loop emulation
		$this->post_count   = 0;
		$this->current_post = - 1;
		$this->in_the_loop  = false;

		$this->is_search = true;

		//add_action( 'wp_head', 'check_default_options_and_function' );

	}


	/**
	 * Get the path of the (child) theme's search template file.
	 * When WPSOLR is configured to replace the default WP search, it will first try to load this template.
	 * If it cannot, WPSOLR will load it's default internal search template instead.
	 *
	 * @return string
	 */
	public function get_path_search_page_template() {
		return self::_SEARCH_PAGE_TEMPLATE;
	}

	/**
	 * Get the path of the (child) theme's search form template file.
	 * When WPSOLR is configured to replace the default WP search, it will first try to load this template.
	 * If it cannot, WPSOLR will load it's default internal search form template instead.
	 *
	 * @return string
	 */
	public function get_path_search_form_page_template() {
		return self::_SEARCH_FORM_PAGE_TEMPLATE;
	}

	/**
	 * Get the search parameter name
	 *
	 * $wpsolr_query = new WPSOLR_Query();
	 * $search_id = $wpsolr_query->get_search_query_parameter_name();
	 *
	 *
	 * @return string
	 */
	public function get_search_query_parameter_name() {
		return self::_SEARCH_PARAMETER_QUERY_NAME;
	}


	/**
	 * Get the slug (permalink uri) of the WPSOLR search page.
	 *
	 * @return string
	 */
	public function get_search_page_slug() {
		return self::_SEARCH_PAGE_SLUG;
	}

	/**
	 * Is the post the WPSOLR seach page ?
	 *
	 * @param $post
	 *
	 * @return boolean
	 */
	public function is_search_page( $post ) {
		return $post && ( self::_SEARCH_PAGE_SLUG === $post->post_name );
	}

	/**
	 * Get suggestions from keywords
	 *
	 * @param $input Keywords
	 *
	 * @return array Array of suggestions (string)
	 */
	public function get_suggestions( $input ) {

		$results = $this->solr_client->get_suggestions( $input );

		return $results;
	}

	/**
	 * @return array
	 */
	public function get_results_facets() {

		// Initiate results with empty array, rather than null.
		$facets = array();

		if ( isset( $this->facet_options ) && isset( $this->facet_options['facets'] ) ) {

			// Array of facets (string) configured in admin
			$facet_names = explode( ',', $this->facet_options['facets'] );

			foreach ( $facet_names as $facet_name ) {

				$fact = strtolower( $facet_name );

				/*
				 *  Replace old categories facet name by new name
				 *
				 * @since Version 5.4
				 */
				if ( WpSolrSchema::_FIELD_NAME_CATEGORIES === $fact ) {
					$fact = WpSolrSchema::_FIELD_NAME_CATEGORIES_STR;
				}

				// Retrieve facet object from Solarium results, by the facet name
				$facet = $this->solr_client->get_solarium_results()->getFacetSet()->getFacet( "$fact" );

				// Fill the results
				foreach ( $facet as $facet_data => $facet_count ) {
					$facets[ $fact ][] = array(
						'facet_data'  => $facet_data,
						'facet_count' => $facet_count,
					);
				}


			}

		}


		return $facets;
	}

	/**
	 * Pagination: current page number being searched.
	 *
	 * @return int
	 */
	function get_current_page_number() {

		return $this->get( 'paged', 0 );
	}

	/**
	 * Get the WPSOLR search page.
	 * Create it if it does not exist yet.
	 *
	 * @return WP_Post Search page
	 */
	public function get_search_page() {

		// The search page is found by it's path (hard-coded).
		$search_page = get_page_by_path( $this->get_search_page_slug() );

		if ( ! $search_page ) {

			$_p = array(
				'post_type'      => 'page',
				'post_title'     => 'Search Results',
				'post_content'   => '[solr_search_shortcode]',
				'post_status'    => 'publish',
				'post_author'    => 1,
				'comment_status' => 'closed',
				'post_name'      => $this->get_search_page_slug()
			);

			$search_page_id = wp_insert_post( $_p );

			update_post_meta( $search_page_id, 'bwps_enable_ssl', '1' );

			$search_page = get_post( $search_page_id );

		} else {

			if ( $search_page->post_status != 'publish' ) {

				$search_page->post_status = 'publish';

				wp_update_post( $search_page );
			}
		}

		// Give a chance to translate the search page
		$url = apply_filters( WpSolrFilters::WPSOLR_FILTER_SEARCH_PAGE_URL, get_permalink( $search_page->ID ), $search_page->ID );

		return $search_page;
	}

	public function set_search_form_template( $search_form_path ) {


		$this->search_form_template = $search_form_path;

		add_filter( 'get_search_form', array( $this, 'wpsolr_theme_get_search_form' ) );
	}

	public function set_search_page_template( $search_page_path ) {

		$this->search_page_template = $search_page_path;

		// Replace the search page template by the (child) theme template passed as parameter.
		add_filter( 'template_include', array( $this, 'wpsolr_theme_search_page_template' ), 99 );

	}

	public function get_search_page_template() {

		return $this->search_page_template;

	}

	public function wpsolr_theme_get_search_form() {

		ob_start();

		include( $this->search_form_template );

		return ob_get_clean();
	}

	public function is_replace_search_form_by_plugin() {

		return ( $this->is_replace_default_wp_search_form() && ( $this->search_form_template == '' ) );
	}

	public function is_replace_default_wp_search_form() {

		return isset( $this->search_options['default_search'] );
	}

	function wpsolr_theme_search_page_template( $template ) {

		if ( is_page( self::_SEARCH_PAGE_SLUG ) && ( '' !== $this->search_page_template ) ) {

			return $this->search_page_template;
		}

		return $template;
	}

	function get_translation( $term_to_translate ) {

		return OptionLocalization::get_term( $this->localization_options, $term_to_translate );
	}

	function get_sort_options() {

		$results = array();

		if ( isset( $this->sort_options ) && ( $this->sort_options != '' ) ) {

			// Sort codes selected in WPSOLR admin options
			$sort_codes = $this->sort_options['sort'];

			// Default sort code selected in WPSOLR admin options
			$sort_default = $this->sort_options['sort_default'];

			if ( isset( $sort_codes ) && ( $sort_codes != '' ) ) {

				foreach ( explode( ',', $sort_codes ) as $sort_code ) {

					// Sort code label localization
					$sort_label_localized = $this->get_translation( $sort_code );

					// Add current sort option to results
					$results[] = array(
						'code'       => $sort_code,
						'label'      => $sort_label_localized,
						'is_default' => ( $sort_default == $sort_code ) ? true : false,
					);
				}

			}
		}

		return $results;
	}

	/**************************************************************************
	 *
	 * Override WP_Query methods
	 *
	 *************************************************************************/

	function query( $query = '' ) {
		// Query keywords
		if ( '' == $query ) {
			$query = isset( $_GET[ self::_SEARCH_PARAMETER_QUERY_NAME ] ) ? $_GET[ self::_SEARCH_PARAMETER_QUERY_NAME ] : '';
		}

		if ( $query != '' && $query != '*:*' ) {

			// $_GET['s'] is used internally by some themes
			$_GET['s'] = $query;

			// Set variable 's', so that get_search_query() and other standard WP_Query methods still work with our own search parameter
			$this->set( 's', $query );

			// Use default sort
			$sort_opt     = get_option( 'wdm_solr_sortby_data' );
			$sort_default = $sort_opt['sort_default'];

			$number_of_res = $this->search_options['no_res'];
			if ( $number_of_res == '' ) {
				$number_of_res = 20;
			}

			$this->solr_client->query( $query, '', $number_of_res, $this->get_current_page_number(), $sort_default );


			$option_number_of_rows_per_page = $this->search_options['no_res'];

			// Fetch all posts from the documents ids, in ONE call.
			$posts_ids = array();
			foreach ( $this->solr_client->get_solarium_results() as $document ) {

				array_push( $posts_ids, $document->id );
			}
			$posts_in_results = get_posts( array(
					'numberposts' => count( $posts_ids ),
					'post_type'   => 'any',
					'post__in'    => $posts_ids
				)
			);


			$this->posts       = $posts_in_results;
			$this->post_count  = count( $this->posts );
			$this->found_posts = $this->solr_client->get_solarium_results()->getNumFound();

			$this->posts_per_page = $number_of_res;
			$this->set( "posts_per_page", $this->posts_per_page );
			$this->max_num_pages = ceil( $this->found_posts / $this->posts_per_page );

		}

	}

	protected function get_highlighting_of_field( $field_name ) {

		$post_id = get_the_ID();

		$highlighting = $this->solr_client->get_solarium_results()->getHighlighting();

		$highlightedDoc = $highlighting->getResult( $post_id );
		if ( $highlightedDoc ) {

			$highlighted_field = $highlightedDoc->getField( $field_name );

			return empty( $highlighted_field ) ? '' : implode( ' (...) ', $highlighted_field );
		}


		return '';
	}

	function get_the_title( $post = 0 ) {

		$result = $this->get_highlighting_of_field( WpSolrSchema::_FIELD_NAME_TITLE );

		return '' === $result ? get_the_title( $post ) : $result;
	}


	function get_the_excerpt() {

		$result = $this->get_highlighting_of_field( WpSolrSchema::_FIELD_NAME_CONTENT );

		return '' === $result ? get_the_excerpt() : $result;
	}

	/*
	public function get( $query_var, $default = '' ) {

		// Replace call to 's' parameter by our search parameter
		return parent::get( 's' == $query_var ? self::_SEARCH_PARAMETER_QUERY_NAME : $query_var, $default );
	}*/


	/**************************************************************************
	 *
	 * Non standard query methods
	 *
	 *************************************************************************/

	/**
	 * @return String
	 */
	public
	function get_did_you_mean() {
		return $this->solr_client->get_did_you_mean();
	}


}