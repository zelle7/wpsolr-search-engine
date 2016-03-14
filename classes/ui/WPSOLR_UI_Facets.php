<?php

/**
 * Display facets
 *
 * Class WPSOLR_UI_Facets
 */
class WPSOLR_UI_Facets {

	/**
	 * Build facets UI
	 *
	 * @param array $facets
	 * @param $localization_options array
	 *
	 * @return string
	 */
	public static function Build( $facets, $localization_options ) {

		$html = '';

		if ( ! empty( $facets ) ) {

			$wpsolr_facet_checkbox_class = 'wpsolr_facet_checkbox';


			$facet_element = OptionLocalization::get_term( $localization_options, 'facets_element' );
			$facet_title   = OptionLocalization::get_term( $localization_options, 'facets_title' );

			$is_facet_selected = false;
			foreach ( $facets as $facet ) {

				$html .= "<lh >" . sprintf( $facet_title, $facet['name'] ) . "</lh><br>";

				$facet_id = strtolower( str_replace( ' ', '_', $facet['id'] ) );
				foreach ( $facet['items'] as $item ) {

					$item_name     = $item['name'];
					$item_count    = $item['count'];
					$item_selected = isset( $item['selected'] ) ? $item['selected'] : false;

					// Check if one facet item is selected (once only).
					if ( $item_selected && ! $is_facet_selected ) {
						$is_facet_selected = true;
					}

					$facet_class = $wpsolr_facet_checkbox_class . ( $item_selected ? ' checked' : '' );

					$html .= "<div class='select_opt $facet_class' id='$facet_id:$item_name'>"
					         . sprintf( $facet_element, $item_name, $item_count )
					         . "</div>";
				}

			}

			$html = sprintf( "<div><label class='wdm_label'>%s</label>
                                    <input type='hidden' name='sel_fac_field' id='sel_fac_field' data-wpsolr-facets-selected=''>
                                    <div class='wdm_ul' id='wpsolr_section_facets'><div class='select_opt %s' id='wpsolr_remove_facets'>%s</div>",
					OptionLocalization::get_term( $localization_options, 'facets_header' ),
					$wpsolr_facet_checkbox_class . ( ! $is_facet_selected ? ' checked' : '' ),
					OptionLocalization::get_term( $localization_options, 'facets_element_all_results' )
			        )
			        . $html;

			$html .= '</div></div>';
		}

		return $html;
	}

}
