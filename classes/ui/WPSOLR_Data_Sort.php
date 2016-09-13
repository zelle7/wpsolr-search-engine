<?php

/**
 * Sort data
 *
 * Class WPSOLR_Data_Sort
 */
class WPSOLR_Data_Sort {

	// Labels for field values
	protected static $fields_items_labels;

	/*
	 * @param $facets_selected
	 * @param $facets_to_display
	 * @param $facets_in_results
	 *
	 * @return array    [
	 *                      ['id' => '_price_str_desc',         'name' => 'More expensive'],
	 *                      ['id' => '_price_str_asc',          'name' => 'Cheapest'],
	 *                      ['id' => 'sort_by_relevancy_desc',  'name' => 'More relevant'],
	 *                  ]
	 */
	public static function get_data( $sorts_selected, $sorts_labels_selected, $sort_selected_in_url, $localization_options ) {

		$results           = array();
		$results['items']  = array();
		$results['header'] = OptionLocalization::get_term( $localization_options, 'sort_header' );

		if ( is_array( $sorts_selected ) && ! empty( $sorts_selected ) ) {

			foreach ( $sorts_selected as $sort_code ) {

				// Give plugins a chance to change the sort label (ACF).
				$sort_label = apply_filters( WpSolrFilters::WPSOLR_FILTER_SEARCH_PAGE_FACET_NAME, $sort_code );

				if ( $sort_label === $sort_code ) {
					// Sort label not changed by filter

					if ( ! empty( $sorts_labels_selected[ $sort_code ] ) ) {
						// Sort label is defined in options

						// Give plugins a chance to change the sort label (WPML, POLYLANG).
						$sort_label = apply_filters( WpSolrFilters::WPSOLR_FILTER_TRANSLATION_STRING, $sorts_labels_selected[ $sort_code ],
							array(
								'domain' => WPSOLR_Option::TRANSLATION_DOMAIN_SORT_LABEL,
								'name'   => $sort_code,
								'text'   => $sorts_labels_selected[ $sort_code ],
							)
						);

					} else {
						// Try to make a decent label from the facet raw id
						$sort_label = OptionLocalization::get_term( $localization_options, $sort_code );
					}
				}

				$sort             = array();
				$sort['id']       = $sort_code;
				$sort['name']     = $sort_label;
				$sort['selected'] = ( $sort_code === $sort_selected_in_url );

				// Add sort to results
				array_push( $results['items'], $sort );
			}


			return $results;
		}
	}
}
