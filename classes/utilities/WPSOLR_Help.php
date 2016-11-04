<?php


/**
 * Show help links on admin screens.
 *
 * Class WPSOLR_Help
 */
class WPSOLR_Help {

	// Url of help
	const _SEARCH_URL = '<a class="wpsolr-help" href="https://www.wpsolr.com/?s=&wpsolr_fq[]=wpsolr_feature_str:%s" target="_help"></a>';

	// Help ids
	const HELP_GEOLOCATION = 1;
	const HELP_MULTI_SITE = 2;
	const HELP_SEARCH_TEMPLATE = 3;
	const HELP_JQUERY_SELECTOR = 4;
	const HELP_SEARCH_ORDERS = 5;
	const HELP_ACF_REPEATERS_AND_FLEXIBLE_CONTENT_LAYOUTS = 6;
	const HELP_WOOCOMMERCE_REPLACE_SORT = 7;


	/**
	 * Show a help_id description
	 *
	 * @param $help_id
	 *
	 * @return string
	 */
	public static function get_help( $help_id ) {

		$url = sprintf( self::_SEARCH_URL, $help_id );

		return $url;
	}
}