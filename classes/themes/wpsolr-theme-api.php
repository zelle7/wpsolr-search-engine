<?php

/* Include the Solr client */
require_once plugin_dir_path( __FILE__ ) . '../solr/wpsolr-search-solr-client.php';

/**
 * Api used by (child) themes to customize the search and search form templates.
 * The Api will accept parameters, to eventually override the admin options.
 * The Api will return pure data. No UI elements (html code, css class, ...).
 *
 * It is up to the (child) theme to display the data returned by the Api.
 */
class WPSolrThemeApi {

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

	// Search loop emulation
	protected $post_count;
	protected $current_post;
	protected $in_the_loop;

	/**
	 * WPSolrThemeApi constructor.
	 *
	 * @param $has_search_form
	 */
	public function __construct() {

		$this->search_form_template = '';
		$this->search_page_template = '';

		$this->solr_client = WPSolrSearchSolrClient::create_from_default_index_indice();


		// Search loop emulation
		$this->post_count   = 0;
		$this->current_post = - 1;
		$this->in_the_loop  = false;

		//add_action( 'wp_head', 'check_default_options_and_function' );

		add_filter( 'template_include', array( $this, 'wpsolr_theme_search_page_template' ), 99 );

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
	 * $wpsolr_theme_api = new WPSolrThemeApi();
	 * $search_id = $wpsolr_theme_api->get_search_query_parameter_name();
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


		return $search_page;
	}

	public function set_search_form_template( $search_form_path ) {

		$this->search_form_template = $search_form_path;

		add_filter( 'get_search_form', array( $this, 'wpsolr_theme_get_search_form' ) );
	}

	public function set_search_page_template( $search_page_path ) {

		$this->search_page_template = $search_page_path;
	}

	public function get_search_page_template() {

		return $this->search_page_template;
	}

	public function wpsolr_theme_get_search_form() {

		ob_start();

		include( $this->search_form_template );

		return ob_get_clean();
	}

	public function has_search_form_template() {

		$solr_options = get_option( 'wdm_solr_res_data' );

		return ( isset( $solr_options['default_search'] ) && ( $this->search_form_template !== '' ) );
	}

	function wpsolr_theme_search_page_template( $template ) {

		if ( is_page( self::_SEARCH_PAGE_SLUG ) && ( '' !== $this->search_page_template ) ) {

			return $this->search_page_template;
		}

		return $template;
	}


	/**************************************************************************
	 *
	 * Rewrite standard loop functions
	 *
	 *************************************************************************/

	function do_search( $search_query = null ) {

		// Query keywords
		if ( ! isset( $search_query ) ) {
			$search_query = isset( $_GET['search'] ) ? $_GET['search'] : '';
		}

		if ( $search_query != '' && $search_query != '*:*' ) {

			// Use default sort
			$sort_opt     = get_option( 'wdm_solr_sortby_data' );
			$sort_default = $sort_opt['sort_default'];

			try {

				$this->solr_client->do_search( $search_query, '', '', $sort_default );
				$this->post_count = $this->solr_client->get_solarium_results()->getNumFound();


			} catch ( Exception $e ) {

				$message = $e->getMessage();
				echo "<span class='infor'>$message</span>";
			}

		}

	}

	public function the_post() {
		global $post;
		$this->in_the_loop = true;

		if ( $this->current_post == - 1 ) // loop has just started
			/**
			 * Fires once the loop is started.
			 *
			 * @param WPSolrTheApi &$this The WPSolrTheApi instance (passed by reference).
			 */ {
			do_action_ref_array( 'wpsolr_loop_start', array( &$this ) );
		}

		$post = $this->next_post();
		$this->current_post ++;

		//setup_postdata( $this->post );
	}

	public function next_post() {

		$this->current_post++;

		$this->post = get_post( $this->solr_client->get_solarium_results()->getDocuments()[ $this->current_post ]['id'] );
		return $this->post;
	}

	public function have_posts() {
		if ( $this->current_post + 1 < $this->post_count ) {
			return true;
		} elseif ( $this->current_post + 1 == $this->post_count && $this->post_count > 0 ) {
			/**
			 * Fires once the loop has ended.
			 *
			 * @param WPSolrTheApi &$this The WPSolrTheApi instance (passed by reference).
			 */
			do_action_ref_array( 'wpsolr_loop_end', array( &$this ) );
			// Do some cleaning up after the loop
			$this->rewind_posts();
		}

		$this->in_the_loop = false;

		return false;
	}

	public function rewind_posts() {
		global $post;

		$this->current_post = - 1;
		if ( $this->post_count > 0 ) {
			$post = get_post( $this->solr_client->get_solarium_results()->getDocuments()[1]['id'] );
		}
	}

	function get_query_var( $var, $default = '' ) {

		switch ( $var ) {

			case 'paged':
				return 1;
				break;

			case 'posts_per_page':
				return 10;
				break;

			default:
				throw new ErrorException( sprintf( "get_query_var() does not recognize the variable '%s'.", $var ) );
				break;
		}

	}

}