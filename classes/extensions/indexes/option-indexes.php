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

	/**
	 * Does a Solr index exist ?
	 *
	 * @param $solr_index_indice Indice in Solr indexes array
	 *
	 * @return bool
	 */
	public function has_index( $solr_index_indice ) {

		$solr_indexes = $this->get_indexes();

		return isset( $solr_indexes[ $solr_index_indice ] );
	}

	/**
	 * Get a Solr index
	 *
	 * @param $solr_index_indice Indice in Solr indexes array
	 *
	 * @return bool
	 */
	public function get_index( $solr_index_indice ) {

		$solr_indexes = $this->get_indexes();

		return isset( $solr_indexes[ $solr_index_indice ] ) ? $solr_indexes[ $solr_index_indice ] : null;
	}
}