<?php

//WpSolrExtensions::load();

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
	 * Migrate the old index data to the new index data.
	 * Then delete the old index data.
	 */
	function migrate_data_from_v4_9() {

		// Load the old options data
		$old_options_name = 'wdm_solr_conf_data';
		$old_options      = get_option( $old_options_name );

		/* Clean data for migration tests */
		/*
		$old_options['migrated'] = false;
		update_option( $old_options_name, $old_options );
		delete_option( self::get_option_name( self::OPTION_INDEXES ) );
		*/

		if ( $old_options === false ) {
			// Nothing to migrate
			return;
		}

		$new_options = $this->_options;
		if ( $new_options != false ) {
			// Migration already done
			return;
		}

		// Move the 2 old style (version <= 4.8) indexes in the new structure
		foreach (
			array(
				''      => array(
					'indice'    => self::generate_uuid(),
					'name'      => 'Solr index local',
					'host_type' => 'self_hosted',
					'post_fix'  => '_in_self_index',
				),
				'_goto' => array(
					'indice'    => self::generate_uuid(),
					'name'      => 'Solr index cloud',
					'host_type' => 'other_hosted',
					'post_fix'  => '_in_cloud_index',
				),
			) as $old_index_postfix => $old_index
		) {
			if ( ! empty( $old_options[ 'solr_host' . $old_index_postfix ] ) ) {

				// Copy the old index structure in the a temporary index structure
				$index_array                   = array();
				$index_array['index_name']     = $old_index['name'];
				$index_array['index_protocol'] = isset( $old_options[ 'solr_protocol' . $old_index_postfix ] ) ? $old_options[ 'solr_protocol' . $old_index_postfix ] : 'http';
				$index_array['index_host']     = isset( $old_options[ 'solr_host' . $old_index_postfix ] ) ? $old_options[ 'solr_host' . $old_index_postfix ] : 'localhost';
				$index_array['index_port']     = isset( $old_options[ 'solr_port' . $old_index_postfix ] ) ? $old_options[ 'solr_port' . $old_index_postfix ] : '8983';
				$index_array['index_path']     = isset( $old_options[ 'solr_path' . $old_index_postfix ] ) ? $old_options[ 'solr_path' . $old_index_postfix ] : '/sol/index_name';
				$index_array['index_key']      = isset( $old_options[ 'solr_key' . $old_index_postfix ] ) ? $old_options[ 'solr_key' . $old_index_postfix ] : '';
				$index_array['index_secret']   = isset( $old_options[ 'solr_secret' . $old_index_postfix ] ) ? $old_options[ 'solr_secret' . $old_index_postfix ] : '';

				// Copy the new index structure
				$new_options['solr_indexes'][ $old_index['indice'] ] = $index_array;

				// Set this index as the default index if it was the default
				if ( ( isset( $old_options['host_type'] ) ? $old_options['host_type'] : '' ) === $old_index['host_type'] ) {

					// Default search Solr index
					$results_options                                  = get_option( 'wdm_solr_res_data', array() );
					$results_options['default_solr_index_for_search'] = $old_index['indice'];
					update_option( 'wdm_solr_res_data', $results_options );

					// Copy the last post date to this index, to prevent re-indexing all its data
					$option_last_post_indexed = get_option( 'solr_last_post_date_indexed' . $old_index['post_fix'], null );
					if ( isset( $option_last_post_indexed ) ) {

						update_option( 'solr_last_post_date_indexed', array( $old_index['indice'] => $option_last_post_indexed ) );
					}

				}

			}
		}

		// Save the new option
		self::set_option_data( self::OPTION_INDEXES, $new_options );

		// Do not delete the old options. If the user wants to rollback the version, he can.
		//delete_option( $old_options_name );

	}

	/**
	 * Return all configured Solr indexes
	 */
	function get_indexes() {
		$result = $this->_options;
		$result = isset( $result['solr_indexes'] ) ? $result['solr_indexes'] : array();

		return $result;
	}

	/**
	 * Does a Solr index exist ?
	 *
	 * @param $solr_index_indice Indice in Solr indexes array
	 *
	 * @return bool
	 */
	public
	function has_index(
		$solr_index_indice
	) {

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
	public
	function get_index(
		$solr_index_indice
	) {

		$solr_indexes = $this->get_indexes();

		return isset( $solr_indexes[ $solr_index_indice ] ) ? $solr_indexes[ $solr_index_indice ] : null;
	}

	/**
	 * Generate a long random id
	 *
	 * @return string
	 */
	public
	function generate_uuid() {

		return strtoupper( md5( uniqid( rand(), true ) ) );
	}


	/**
	 * @param $timeout
	 *
	 * @return array Solarium configuration
	 * @throws Exception
	 */
	public function build_solarium_config( $solr_index_indice = null, $timeout ) {

		if ( ! isset( $solr_index_indice ) ) {

			// Give a chance to set the solr index indice
			$solr_index_indice = apply_filters( WpSolrFilters::WPSOLR_FILTER_SEARCH_GET_DEFAULT_SOLR_INDEX_INDICE, null );

			if ( ! isset( $solr_index_indice ) ) {
				// Retrieve the default indexing Solr index

				$solr_options = get_option( 'wdm_solr_res_data' );
				if ( $this->_options === false ) {
					throw new Exception( 'Please complete the setup of your Solr options. We could not find any.' );
				}

				if ( ! isset( $solr_options['default_solr_index_for_search'] ) ) {
					throw new Exception( 'Please complete the setup of your Solr options. There is no Solr index configured for searching.' );
				}
				$solr_index_indice = $solr_options['default_solr_index_for_search'];

			}
		}

		$solr_index = $this->get_index( $solr_index_indice );
		if ( ! isset( $solr_index ) ) {

			throw new Exception( sprintf( "Please complete the setup of your Solr options.
			The Solr index configured for indexing, with indice '%s', is missing.
			It probably was removed from the Solr index list, but is still setup as the default indexing Solr index.", $solr_index_indice ) );
		}

		// Copy the index parameters in the Solarium endpoint
		$config = array(
			'endpoint' => array(
				'localhost1' => array(
					'scheme'   => $solr_index['index_protocol'],
					'host'     => $solr_index['index_host'],
					'username' => $solr_index['index_key'],
					'password' => $solr_index['index_secret'],
					'port'     => $solr_index['index_port'],
					'path'     => $solr_index['index_path'],
					'timeout'  => $timeout,
				)
			)
		);

		return $config;
	}
}