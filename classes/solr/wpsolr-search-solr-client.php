<?php

require_once plugin_dir_path( __FILE__ ) . 'wpsolr-abstract-solr-client.php';

class WPSolrSearchSolrClient extends WPSolrAbstractSolrClient {

	public $select_query;
	protected $config;

	// Array of active extension objects
	protected $wpsolr_extensions;

	// Search template
	const _SEARCH_PAGE_TEMPLATE = 'wpsolr-search-engine/search.php';

	// Search page slug
	const _SEARCH_PAGE_SLUG = 'search-wpsolr';

	// Do not change - Sort by most relevant
	const SORT_CODE_BY_RELEVANCY_DESC = 'sort_by_relevancy_desc';

	// Do not change - Sort by newest
	const SORT_CODE_BY_DATE_DESC = 'sort_by_date_desc';

	// Do not change - Sort by oldest
	const SORT_CODE_BY_DATE_ASC = 'sort_by_date_asc';

	// Do not change - Sort by least comments
	const SORT_CODE_BY_NUMBER_COMMENTS_ASC = 'sort_by_number_comments_asc';

	// Do not change - Sort by most comments
	const SORT_CODE_BY_NUMBER_COMMENTS_DESC = 'sort_by_number_comments_desc';


	/**
	 * Search parameters used in url or ajax
	 */
	const SEARCH_PARAMETER_AJAX_URL_PARAMETERS = 'url_parameters';
	const SEARCH_PARAMETER_SEARCH = 'search'; // Old query name, here for compatibility
	const SEARCH_PARAMETER_Q = 'wpsolr_q'; // New query name
	const SEARCH_PARAMETER_FQ = 'wpsolr_fq';
	const SEARCH_PARAMETER_PAGE = 'wpsolr_page';
	const SEARCH_PARAMETER_SORT = 'wpsolr_sort';


	// Create using a configuration
	static function create_from_solarium_config( $solarium_config ) {

		return new self( $solarium_config );
	}

	// Create using the default index configuration
	static function create_from_default_index_indice() {

		return self::create_from_index_indice( null );
	}

	// Create using an index configuration
	static function create_from_index_indice( $index_indice ) {

		// Build Solarium config from the default indexing Solr index
		WpSolrExtensions::require_once_wpsolr_extension( WpSolrExtensions::OPTION_INDEXES, true );
		$options_indexes = new OptionIndexes();
		$solarium_config = $options_indexes->build_solarium_config( $index_indice, null, self::DEFAULT_SOLR_TIMEOUT_IN_SECOND );

		return new self( $solarium_config );
	}

	public function __construct( $solarium_config ) {

		// Load active extensions
		$this->wpsolr_extensions = new WpSolrExtensions();


		$path = plugin_dir_path( __FILE__ ) . '../../vendor/autoload.php';
		require_once $path;
		$this->client = new Solarium\Client( $solarium_config );

	}


	/*
	 * Manage options by hosting mode
	 * Use a dedicated postfix added to the option name.
	 */

	public function get_suggestions( $input ) {

		$results = array();

		$client = $this->client;


		$suggestqry = $client->createSuggester();
		$suggestqry->setHandler( 'suggest' );
		$suggestqry->setDictionary( 'suggest' );
		$suggestqry->setQuery( $input );
		$suggestqry->setCount( 5 );
		$suggestqry->setCollate( true );
		$suggestqry->setOnlyMorePopular( true );

		$resultset = $client->execute( $suggestqry );

		foreach ( $resultset as $term => $termResult ) {

			foreach ( $termResult as $result ) {

				array_push( $results, $result );
			}
		}

		return $results;
	}

	/**
	 * Retrieve or create the search page
	 */
	static function get_search_page() {

		// Let other plugins (POLYLANG, ...) modify the search page slug
		$search_page_slug = apply_filters( WpSolrFilters::WPSOLR_FILTER_SEARCH_PAGE_SLUG, self::_SEARCH_PAGE_SLUG );

		// Search page is found by it's path (hard-coded).
		$search_page = get_page_by_path( $search_page_slug );

		if ( ! $search_page ) {

			$search_page = self::create_default_search_page();

		} else {

			if ( $search_page->post_status != 'publish' ) {

				$search_page->post_status = 'publish';

				wp_update_post( $search_page );
			}
		}


		return $search_page;
	}


	/**
	 * Create a default search page
	 *
	 * @return WP_Post The search page
	 */
	static function create_default_search_page() {

		// Let other plugins (POLYLANG, ...) modify the search page slug
		$search_page_slug = apply_filters( WpSolrFilters::WPSOLR_FILTER_SEARCH_PAGE_SLUG, self::_SEARCH_PAGE_SLUG );

		$_search_page = array(
			'post_type'      => 'page',
			'post_title'     => 'Search Results',
			'post_content'   => '[solr_search_shortcode]',
			'post_status'    => 'publish',
			'post_author'    => 1,
			'comment_status' => 'closed',
			'post_name'      => $search_page_slug
		);

		// Let other plugins (POLYLANG, ...) modify the search page
		$_search_page = apply_filters( WpSolrFilters::WPSOLR_FILTER_BEFORE_CREATE_SEARCH_PAGE, $_search_page );

		$search_page_id = wp_insert_post( $_search_page );

		update_post_meta( $search_page_id, 'bwps_enable_ssl', '1' );

		return get_post( $search_page_id );
	}

	/**
	 * Get all sort by options available
	 *
	 * @param string $sort_code_to_retrieve
	 *
	 * @return array
	 */
	public
	static function get_sort_options() {

		$results = array(

			array(
				'code'  => self::SORT_CODE_BY_RELEVANCY_DESC,
				'label' => 'Most relevant',
			),
			array(
				'code'  => self::SORT_CODE_BY_DATE_DESC,
				'label' => 'Newest',
			),
			array(
				'code'  => self::SORT_CODE_BY_DATE_ASC,
				'label' => 'Oldest',
			),
			array(
				'code'  => self::SORT_CODE_BY_NUMBER_COMMENTS_DESC,
				'label' => 'More comments',
			),
			array(
				'code'  => self::SORT_CODE_BY_NUMBER_COMMENTS_ASC,
				'label' => 'Less comments',
			),
		);

		return $results;
	}

	/**
	 * Get all sort by options available
	 *
	 * @param string $sort_code_to_retrieve
	 *
	 * @return array
	 */
	public static function get_sort_option_from_code( $sort_code_to_retrieve, $sort_options = null ) {

		if ( $sort_options == null ) {
			$sort_options = self::get_sort_options();
		}

		if ( $sort_code_to_retrieve != null ) {
			foreach ( $sort_options as $sort ) {

				if ( $sort['code'] === $sort_code_to_retrieve ) {
					return $sort;
				}
			}
		}


		return null;
	}


	/*
	 * Manage options by hosting mode
	 * Use a dedicated postfix added to the option name.
	 */

	public function get_search_results( $term, $facet_options, $start, $sort ) {

		$output        = array();
		$search_result = array();

		// Fields query should be an array
		if ( ! is_array( $facet_options ) ) {
			$facet_options = array( $facet_options );
		}

		// Load options
		$ind_opt              = get_option( 'wdm_solr_form_data' );
		$res_opt              = get_option( 'wdm_solr_res_data' );
		$fac_opt              = get_option( 'wdm_solr_facet_data' );
		$localization_options = OptionLocalization::get_options();

		$number_of_res = $res_opt['no_res'];
		if ( $number_of_res == '' ) {
			$number_of_res = 20;
		}

		$field_comment = isset( $ind_opt['comments'] ) ? $ind_opt['comments'] : '';
		$options       = $fac_opt['facets'];


		$msg    = '';
		$client = $this->client;
		//$term   = str_replace( ' ', '\ ', $term );

		$query = $client->createSelect();
		$query->setQuery( WpSolrSchema::_FIELD_NAME_DEFAULT_QUERY . ':' . $term );

		// Let extensions change query
		do_action( WpSolrFilters::WPSOLR_ACTION_SOLARIUM_QUERY,
			array(
				WpSolrFilters::WPSOLR_ACTION_SOLARIUM_QUERY__PARAM_SOLARIUM_QUERY => $query,
				WpSolrFilters::WPSOLR_ACTION_SOLARIUM_QUERY__PARAM_SEARCH_TERMS   => $term,
				WpSolrFilters::WPSOLR_ACTION_SOLARIUM_QUERY__PARAM_SEARCH_USER    => wp_get_current_user(),
			)
		);


		switch ( $sort ) {
			case self::SORT_CODE_BY_DATE_DESC:
				$query->addSort( WpSolrSchema::_FIELD_NAME_DATE, $query::SORT_DESC );
				break;
			case self::SORT_CODE_BY_DATE_ASC:
				$query->addSort( WpSolrSchema::_FIELD_NAME_DATE, $query::SORT_ASC );
				break;
			case self::SORT_CODE_BY_NUMBER_COMMENTS_DESC:
				$query->addSort( WpSolrSchema::_FIELD_NAME_NUMBER_OF_COMMENTS, $query::SORT_DESC );
				break;
			case self::SORT_CODE_BY_NUMBER_COMMENTS_ASC:
				$query->addSort( WpSolrSchema::_FIELD_NAME_NUMBER_OF_COMMENTS, $query::SORT_ASC );
				break;
			case self::SORT_CODE_BY_RELEVANCY_DESC:
			default:
				// None is relevancy
				break;
		}

		$query->setQueryDefaultOperator( 'AND' );


		$fac_count = $res_opt['no_fac'];
		if ( $fac_count == '' ) {
			$fac_count = 20;
		}

		if ( $options != '' ) {

			$facets_array = explode( ',', $fac_opt['facets'] );

			$facetSet = $query->getFacetSet();
			$facetSet->setMinCount( 1 );
			// $facetSet->;
			foreach ( $facets_array as $facet ) {
				$fact = strtolower( $facet );

				if ( WpSolrSchema::_FIELD_NAME_CATEGORIES === $fact ) {
					$fact = WpSolrSchema::_FIELD_NAME_CATEGORIES_STR;
				}

				$facetSet->createFacetField( "$fact" )->setField( "$fact" )->setLimit( $fac_count );

			}
		}

		$bound = '';
		foreach ( is_array( $facet_options ) ? $facet_options : array() as $facet_option ) {
			if ( $facet_option != null || $facet_option != '' ) {
				$f_array = explode( ':', $facet_option );

				$fac_field = strtolower( $f_array[0] );
				$fac_type  = isset( $f_array[1] ) ? $f_array[1] : '';


				if ( $fac_field != '' && $fac_type != '' ) {
					$fac_fd = "$fac_field";

					// In case the facet contains white space, we enclose it with "".
					$fac_type_escaped = "\"$fac_type\"";

					$query->addFilterQuery( array( 'key' => "$fac_fd", 'query' => "$fac_fd:$fac_type_escaped" ) );
				}

				if ( isset( $f_array[2] ) && $f_array[2] != '' ) {
					$bound = $f_array[2];
				}

			}
		}


		if ( $start == 0 || $start == 1 ) {
			$st = 0;

		} else {
			$st = ( ( $start - 1 ) * $number_of_res );

		}

		if ( $bound != '' && $bound < $number_of_res ) {

			$query->setStart( $st )->setRows( $bound );

		} else {
			$query->setStart( $st )->setRows( $number_of_res );

		}

		/*
		 * Set highlighting parameters
		 */
		$this->set_highlighting( $query, $res_opt );

		/*
		 * Set fields returned by the query.
		 * We do not ask for 'content', because it can be huge for attachments, and is anyway replaced by highlighting.
		 */
		$query->setFields( array(
				WpSolrSchema::_FIELD_NAME_ID,
				WpSolrSchema::_FIELD_NAME_TITLE,
				WpSolrSchema::_FIELD_NAME_NUMBER_OF_COMMENTS,
				WpSolrSchema::_FIELD_NAME_COMMENTS,
				WpSolrSchema::_FIELD_NAME_DISPLAY_DATE,
				WpSolrSchema::_FIELD_NAME_CATEGORIES_STR,
				WpSolrSchema::_FIELD_NAME_AUTHOR
			)
		);

		// Perform the query
		$resultset = $client->execute( $query );

		$found = $resultset->getNumFound();

		// No results: try a new query if spellchecking works
		if ( ( $found === 0 ) && ( $res_opt['spellchecker'] == 'spellchecker' ) ) {

			$spellChk = $query->getSpellcheck();
			$spellChk->setCount( 10 );
			$spellChk->setCollate( true );
			$spellChk->setExtendedResults( true );
			$spellChk->setCollateExtendedResults( true );
			$resultset = $client->execute( $query );


			$spellChkResult = $resultset->getSpellcheck();
			if ( $spellChkResult && ! $spellChkResult->getCorrectlySpelled() ) {
				$collations          = $spellChkResult->getCollations();
				$queryTermsCorrected = $term; // original query
				foreach ( $collations as $collation ) {
					foreach ( $collation->getCorrections() as $input => $correction ) {
						$queryTermsCorrected = str_replace( $input, is_array( $correction ) ? $correction[0] : $correction, $queryTermsCorrected );
					}

				}

				if ( $queryTermsCorrected != $term ) {

					$err_msg         = sprintf( OptionLocalization::get_term( $localization_options, 'results_header_did_you_mean' ), $queryTermsCorrected ) . '<br/>';
					$search_result[] = $err_msg;

					// Execute query with spelled terms
					$query->setQuery( $queryTermsCorrected );
					try {
						$resultset = $client->execute( $query );
						$found     = $resultset->getNumFound();

					} catch ( Exception $e ) {
						// Sometimes, the spelling query returns errors
						// java.lang.StringIndexOutOfBoundsException: String index out of range: 15\n\tat java.lang.AbstractStringBuilder.charAt(AbstractStringBuilder.java:203)\n\tat
						// java.lang.StringBuilder.charAt(StringBuilder.java:72)\n\tat org.apache.solr.spelling.SpellCheckCollator.getCollation(SpellCheckCollator.java:164)\n\tat

						$found = 0;
					}

				} else {
					$search_result[] = 0;
				}

			} else {
				$search_result[] = 0;
			}

		} else {
			$search_result[] = 0;
		}

		// Retrieve facets from resultset
		if ( $options != '' ) {
			foreach ( $facets_array as $facet ) {

				$fact = strtolower( $facet );
				if ( WpSolrSchema::_FIELD_NAME_CATEGORIES === $fact ) {
					$fact = WpSolrSchema::_FIELD_NAME_CATEGORIES_STR;
				}
				$facet_res = $resultset->getFacetSet()->getFacet( "$fact" );

				foreach ( $facet_res as $value => $count ) {
					$output[ $facet ][] = array( $value, $count );
				}


			}
			$search_result[] = $output;

		} else {
			$search_result[] = 0;
		}

		if ( $bound != '' ) {
			$search_result[] = $bound;


		} else {
			$search_result[] = $found;

		}

		$results      = array();
		$highlighting = $resultset->getHighlighting();

		$i       = 1;
		$cat_arr = array();
		foreach ( $resultset as $document ) {

			$id      = $document->id;
			$title   = $document->title;
			$content = '';

			$image_url = wp_get_attachment_image_src( get_post_thumbnail_id( $id ) );

			$no_comments = $document->numcomments;
			if ( $field_comment == 1 ) {
				$comments = $document->comments;
			}
			$date = date( 'm/d/Y', strtotime( $document->displaydate ) );

			if ( property_exists( $document, 'categories_str' ) ) {
				$cat_arr = $document->categories_str;
			}


			$cat  = implode( ',', $cat_arr );
			$auth = $document->author;

			$url = get_permalink( $id );

			$highlightedDoc = $highlighting->getResult( $document->id );
			$cont_no        = 0;
			$comm_no        = 0;
			if ( $highlightedDoc ) {

				foreach ( $highlightedDoc as $field => $highlight ) {

					if ( $field == WpSolrSchema::_FIELD_NAME_TITLE ) {

						$title = implode( ' (...) ', $highlight );

					} else if ( $field == WpSolrSchema::_FIELD_NAME_CONTENT ) {

						$content = implode( ' (...) ', $highlight );

					} else if ( $field == WpSolrSchema::_FIELD_NAME_COMMENTS ) {

						$comments = implode( ' (...) ', $highlight );
						$comm_no  = 1;

					}

				}

			}

			$msg = '';
			$msg .= "<div id='res$i'><div class='p_title'><a href='$url'>$title</a></div>";

			$image_fragment = '';
			// Display first image
			if ( is_array( $image_url ) && count( $image_url ) > 0 ) {
				$image_fragment .= "<img class='wdm_result_list_thumb' src='$image_url[0]' />";
			}

			if ( empty( $content ) ) {
				// Set a default value for content if no highlighting returned.
				$post_to_show = get_post( $id );
				if ( isset( $post_to_show ) ) {
					// Excerpt first, or content.
					$content = ( ! empty( $post_to_show->post_excerpt ) ) ? $post_to_show->post_excerpt : $post_to_show->post_content;

					if ( isset( $ind_opt['is_shortcode_expanded'] ) && ( strpos( $content, '[solr_search_shortcode]' ) === false ) ) {

						// Expand shortcodes which have a plugin active, and are not the search form shortcode (else pb).
						global $post;
						$post    = $post_to_show;
						$content = do_shortcode( $content );
					}

					// Remove shortcodes tags remaining, but not their content.
					// strip_shortcodes() does nothing, probably because shortcodes from themes are not loaded in admin.
					// Credit: https://wordpress.org/support/topic/stripping-shortcodes-keeping-the-content.
					// Modified to enable "/" in attributes
					$content = preg_replace( "~(?:\[/?)[^\]]+/?\]~s", '', $content );  # strip shortcodes, keep shortcode content;


					// Strip HTML and PHP tags
					$content = strip_tags( $content );

					if ( isset( $res_opt['highlighting_fragsize'] ) && is_numeric( $res_opt['highlighting_fragsize'] ) ) {
						// Cut content at the max length defined in options.
						$content = substr( $content, 0, $res_opt['highlighting_fragsize'] );
					}
				}
			}


			// Format content text a little bit
			$content = str_replace( '&nbsp;', '', $content );
			$content = str_replace( '  ', ' ', $content );
			$content = ucfirst( trim( $content ) );
			$content .= '...';

			//if ( $cont_no == 1 ) {
			if ( false ) {
				$msg .= "<div class='p_content'>$image_fragment $content - <a href='$url'>Content match</a></div>";
			} else {
				$msg .= "<div class='p_content'>$image_fragment $content</div>";
			}
			if ( $comm_no == 1 ) {
				$msg .= "<div class='p_comment'>" . $comments . "-<a href='$url'>Comment match</a></div>";
			}

			// Groups bloc - Bottom right
			$wpsolr_groups_message = apply_filters( WpSolrFilters::WPSOLR_FILTER_SOLR_RESULTS_DOCUMENT_GROUPS_INFOS, get_current_user_id(), $document );
			if ( isset( $wpsolr_groups_message ) ) {

				// Display groups of this user which owns at least one the document capability
				$message = $wpsolr_groups_message['message'];
				$msg .= "<div class='p_misc'>$message";
				$msg .= "</div>";
				$msg .= '<br/>';

			}

			// Informative bloc - Bottom right
			$msg .= "<div class='p_misc'>";
			$msg .= "<span class='pauthor'>" . sprintf( OptionLocalization::get_term( $localization_options, 'results_row_by_author' ), $auth ) . "</span>";
			$msg .= empty( $cat ) ? "" : "<span class='pcat'>" . sprintf( OptionLocalization::get_term( $localization_options, 'results_row_in_category' ), $cat ) . "</span>";
			$msg .= "<span class='pdate'>" . sprintf( OptionLocalization::get_term( $localization_options, 'results_row_on_date' ), $date ) . "</span>";
			$msg .= empty( $no_comments ) ? "" : "<span class='pcat'>" . sprintf( OptionLocalization::get_term( $localization_options, 'results_row_number_comments' ), $no_comments ) . "</span>";
			$msg .= "</div>";

			// End of snippet bloc
			$msg .= "</div><hr>";

			array_push( $results, $msg );
			$i = $i + 1;
		}
		//  $msg.='</div>';


		if ( count( $results ) < 0 ) {
			$search_result[] = 0;
		} else {
			$search_result[] = $results;
		}

		$fir = $st + 1;

		$last = $st + $number_of_res;
		if ( $last > $found ) {
			$last = $found;
		} else {
			$last = $st + $number_of_res;
		}

		$search_result[] = "<span class='infor'>" . sprintf( OptionLocalization::get_term( $localization_options, 'results_header_pagination_numbers' ), $fir, $last, $found ) . "</span>";


		return $search_result;
	}

	/**
	 * Set highlighting parameters
	 *
	 * @param $query Solarium query object
	 */
	public function set_highlighting( $query, $searching_options ) {

		$hl = $query->getHighlighting();

		foreach (
			array(
				WpSolrSchema::_FIELD_NAME_TITLE,
				WpSolrSchema::_FIELD_NAME_CONTENT,
				WpSolrSchema::_FIELD_NAME_COMMENTS
			) as $highlited_field_name
		) {

			$hl->getField( $highlited_field_name )->setSimplePrefix( '<b>' )->setSimplePostfix( '</b>' );

			if ( isset( $searching_options['highlighting_fragsize'] ) && is_numeric( $searching_options['highlighting_fragsize'] ) ) {
				// Max size of each highlighting fragment for post content
				$hl->getField( $highlited_field_name )->setFragSize( $searching_options['highlighting_fragsize'] );
			}

		}

	}

	public function ping() {

		$this->client->ping( $this->client->createPing() );
	}


}
