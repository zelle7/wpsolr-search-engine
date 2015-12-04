<?php

/**
 * Display facets
 *
 * Class WpSolrUiFacets
 */
class WpSolrUiFacets {

	/**
	 * WpSolrUiFacets constructor.
	 */
	public function __construct() {
	}

	/**
	 * Build facets UI
	 *
	 * @param $url_parameters array Url search parameters
	 * @param $options array Facets options
	 * @param $data array Facets data array('facet1' => array('content1', 10))
	 * @param $localization_options array
	 *
	 * @return string
	 */
	public static function Build( $url_parameters, $options, $data, $localization_options ) {

		$result = '';

		if ( $data != '0' ) {

			if ( $options != '' ) {

				// Extract field queries selected from url parameters
				$search_facets_in_url = array();
				$facets_parameters    = !empty( $url_parameters[ WPSolrSearchSolrClient::SEARCH_PARAMETER_FQ ] ) ? $url_parameters[ WPSolrSearchSolrClient::SEARCH_PARAMETER_FQ ] : array();
				$facets_parameters    = is_array( $facets_parameters ) ? $facets_parameters : Array( $facets_parameters );
				foreach ( $facets_parameters as $search_fq_str ) {
					// $search_fq is like 'type:post'
					$search_fq_array = explode( ':', $search_fq_str );
					if ( count( $search_fq_array ) == 2 ) {

						if ( ! isset( $search_facets_in_url[ $search_fq_array[0] ] ) ) {
							$search_facets_in_url[ $search_fq_array[0] ] = array( $search_fq_array[1] );
						} else {
							$search_facets_in_url[ $search_fq_array[0] ][] .= $search_fq_array[1];
						}
					}
				}

				// Facets configured in admin options
				$facets_array = explode( ',', $options );


				$facet_class = empty( $facets_parameters ) ? 'wpsolr_facet_checkbox checked' : 'wpsolr_facet_checkbox';

				$result = sprintf( "<div><label class='wdm_label'>%s</label>
                                    <input type='hidden' name='sel_fac_field' id='sel_fac_field' data-wpsolr-facets-selected=''>
                                    <div class='wdm_ul' id='wpsolr_section_facets'><div class='select_opt %s' id='wpsolr_remove_facets'>%s</div>",
					OptionLocalization::get_term( $localization_options, 'facets_header' ),
					$facet_class,
					OptionLocalization::get_term( $localization_options, 'facets_element_all_results' )
				);

				$facet_element = OptionLocalization::get_term( $localization_options, 'facets_element' );
				$facet_title   = OptionLocalization::get_term( $localization_options, 'facets_title' );
				foreach ( $facets_array as $arr ) {
					if ( isset( $data[ $arr ] ) && count( $data[ $arr ] ) > 0 ) {
						$arr_val = $arr;
						if ( substr( $arr_val, ( strlen( $arr_val ) - 4 ), strlen( $arr_val ) ) == "_str" ) {
							$arr_val = substr( $arr_val, 0, ( strlen( $arr_val ) - 4 ) );
						}

						// Give plugins a chance to change the facet name (ACF).
						$arr_val = apply_filters( WpSolrFilters::WPSOLR_FILTER_SEARCH_PAGE_FACET_NAME, $arr_val );

						$arr_val = str_replace( '_', ' ', $arr_val );
						$arr_val = ucfirst( $arr_val );

						$result .= "<lh >" . sprintf( $facet_title, $arr_val ) . "</lh><br>";

						foreach ( $data[ $arr ] as $val ) {
							$name  = $val[0];
							$count = $val[1];

							$facet_class = isset( $search_facets_in_url[ $arr ] ) && ( in_array( $name, $search_facets_in_url[ $arr ] ) ) ? 'wpsolr_facet_checkbox checked' : 'wpsolr_facet_checkbox';

							$result .= "<div class='select_opt $facet_class' id='$arr:$name'>"
							           . sprintf( $facet_element, $name, $count )
							           . "</div>";
						}
					}

				}

				$result .= '</div></div>';


			}


		}

		return $result;
	}
}