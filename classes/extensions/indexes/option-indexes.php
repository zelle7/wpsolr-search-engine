<?php

/**
 * Class OptionIndexes
 *
 * Manage Solr Indexes options
 */
class OptionIndexes extends WpSolrExtensions {

	private $_options;

	/*
	 * Constructor
	 *
	 * Subscribe to actions
	 */
	function __construct() {
		$this->_options = self::get_option_data( self::OPTION_INDEXES );
	}


	/**
	 * Return all configured Solr indexes
	 */
	function get_indexes() {
		$result = $this->_options;
		$result = $result['solr_indexes'];

		return $result;
	}

}