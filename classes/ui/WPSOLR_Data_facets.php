<?php

/**
 * Facets data
 *
 * Class WPSOLR_Data_Facets
 */
class WPSOLR_Data_Facets {

	/**
	 * @param $facets_selected
	 * @param $facets_to_display
	 * @param $facets_in_results
	 *
	 * @return array    [
	 *                  {"items":[{"name":"post","count":5,"selected":true}],"id":"type","name":"Type"},
	 *                  {"items":[{"name":"admin","count":6,"selected":false}],"id":"author","name":"Author"},
	 *                  {"items":[{"name":"Blog","count":13,"selected":true}],"id":"categories","name":"Categories"}
	 *                  ]
	 */
	public static function get_data( $facets_selected, $facets_to_display, $facets_in_results ) {

		$results = array();

		if ( count( $facets_in_results ) && count( $facets_to_display ) ) {

			foreach ( $facets_to_display as $facet_to_display_id ) {

				if ( isset( $facets_in_results[ $facet_to_display_id ] ) && count( $facets_in_results[ $facet_to_display_id ] ) > 0 ) {

					// Remove the ending "_str"
					$facet_to_display_id_without_str = preg_replace( '/_str$/', '', $facet_to_display_id );

					// Give plugins a chance to change the facet name (ACF).
					$facet_to_display_name = apply_filters( WpSolrFilters::WPSOLR_FILTER_SEARCH_PAGE_FACET_NAME, $facet_to_display_id_without_str );

					$facet_to_display_name = str_replace( '_', ' ', $facet_to_display_name );
					$facet_to_display_name = ucfirst( $facet_to_display_name );

					$facet          = array();
					$facet['items'] = array();
					$facet['id']    = $facet_to_display_id;
					$facet['name']  = $facet_to_display_name;

					foreach ( $facets_in_results[ $facet_to_display_id ] as $facet_in_results ) {
						array_push( $facet['items'], array(
							'name'     => $facet_in_results[0],
							'count'    => $facet_in_results[1],
							'selected' => isset( $facets_selected[ $facet_to_display_id ] ) && ( in_array( $facet_in_results[0], $facets_selected[ $facet_to_display_id ] ) )
						) );
					}

					// Add current facet to results
					array_push( $results, $facet );
				}

			}

		}

		return $results;
	}

}
