<?php

require_once plugin_dir_path( __FILE__ ) . '../extensions/wpsolr-extensions.php';
require_once plugin_dir_path( __FILE__ ) . '../wpsolr-filters.php';
require_once plugin_dir_path( __FILE__ ) . '../wpsolr-schema.php';

class WPSolrAbstractSolrClient {

	// Timeout in seconds when calling Solr
	const DEFAULT_SOLR_TIMEOUT_IN_SECOND = 30;

	public $solarium_client;
	protected $solarium_config;

	// Indice of the Solr index configuration in admin options
	protected $index_indice;


	// Array of active extension objects
	protected $wpsolr_extensions;


	/**
	 * Execute a solarium query. Retry 2 times if an error occurs.
	 *
	 * @param $solarium_client
	 * @param $solarium_update_query
	 *
	 * @return mixed
	 */
	protected function execute( $solarium_client, $solarium_update_query ) {


		for ( $i = 0; ; $i ++ ) {

			try {

				$result = $solarium_client->execute( $solarium_update_query );

				return $result;

			} catch ( Exception $e ) {

				// Catch error here, to retry in next loop, or throw error after enough retries.
				if ( $i >= 3 ) {
					throw $e;
				}

				// Sleep 3 seconds before retrying
				sleep( 3 );

			}

		}

	}
}
