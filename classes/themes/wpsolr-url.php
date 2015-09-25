<?php

/**
 * Manage urls of queries.
 *
 */
class WPSOLR_Url {

	protected $parameter_facets = array();

	/**
	 * Create an url object from a url string
	 *
	 * @param $url_string Url as a string
	 */
	public function from_string( $url_string ) {


		return $this;
	}

	/**
	 * Create an url string from current url object
	 *
	 */
	public function to_string() {

		$url_string = '';

		return $url_string;
	}


}