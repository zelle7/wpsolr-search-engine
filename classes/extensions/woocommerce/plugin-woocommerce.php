<?php
use Solarium\QueryType\Select\Query\Query;

/**
 * Class PluginWooCommerce
 *
 * Manage WooCommerce plugin
 */
class PluginWooCommerce extends WpSolrExtensions {

	// Polylang options
	const _OPTIONS_NAME = 'wdm_solr_extension_woocommerce_data';

	// Product types
	const PRODUCT_TYPE_VARIABLE = 'variable';

	// Post type of orders
	const CONST_POST_TYPE_SHOP_ORDER = 'shop_order';

	// Order fields
	const FIELD_POST_DATE_DT = 'post_date_dt';
	const FIELD_ORDER_TOTAL_F = '_order_total_f';

	/**
	 * Helper instance.
	 *
	 * @var \Solarium\Core\Query\Helper Helper
	 */
	protected $helper;


	function __construct() {

		add_filter( WpSolrFilters::WPSOLR_FILTER_POST_CUSTOM_FIELDS, array(
			$this,
			'filter_custom_fields',
		), 10, 2 );

		add_filter( WpSolrFilters::WPSOLR_FILTER_POST_STATUSES_TO_INDEX, array(
			$this,
			'filter_post_statuses_to_index',
		), 10, 2 );

		add_action( WpSolrFilters::WPSOLR_ACTION_SOLARIUM_QUERY, array(
			$this,
			'wpsolr_action_solarium_query',
		), 10, 1 );

		add_action( WpSolrFilters::WPSOLR_FILTER_IS_REPLACE_BY_WPSOLR_QUERY, array(
			$this,
			'wpsolr_filter_is_replace_by_wpsolr_query',
		), 10, 1 );

		add_filter( WpSolrFilters::WPSOLR_FILTER_SOLARIUM_DOCUMENT_FOR_UPDATE, array(
			$this,
			'add_fields_to_document_for_update',
		), 10, 4 );
	}

	/*
	 * Constructor
	 * Subscribe to actions
	 */

	/**
	 * Factory
	 *
	 * @return PluginWooCommerce
	 */
	static function create() {

		return new self();
	}

	/**
	 * Return all woo commerce attributes names (slugs)
	 * @return array
	 */
	static function get_attribute_taxonomy_names() {

		$results = array();

		foreach ( self::get_attribute_taxonomies() as $woo_attribute ) {

			// Add woo attribute terms to custom fields
			array_push( $results, $woo_attribute->attribute_name );
		}

		return $results;
	}

	/**
	 * Return all woo commerce attributes
	 * @return array
	 */
	static function get_attribute_taxonomies() {

		// Standard woo function
		return wc_get_attribute_taxonomies();
	}

	/**
	 *
	 * Replace WP query by a WPSOLR query when the current WP Query is an order type query.
	 *
	 * @param bool $is_replace_by_wpsolr_query
	 *
	 * @return bool
	 */
	public function wpsolr_filter_is_replace_by_wpsolr_query( $is_replace_by_wpsolr_query ) {

		if ( is_admin() && WPSOLR_Global::getOption()->get_option_plugin_woocommerce_is_replace_admin_orders_search() ) {

			// ) && ! empty( $_REQUEST['s']
			if ( ( false !== strpos( $_SERVER['REQUEST_URI'], '/wp-admin/edit.php' ) ) && ! empty( $_REQUEST['post_type'] ) && ( self::CONST_POST_TYPE_SHOP_ORDER === $_REQUEST['post_type'] ) ) {
				// This is an order query, in the admin.
				return true;
			}
		}

		return $is_replace_by_wpsolr_query;
	}

	/**
	 *
	 * Add a filter on order post type.
	 *
	 * @param array $parameters
	 *
	 */
	public function wpsolr_action_solarium_query( $parameters ) {

		// @var WPSOLR_Query $wpsolr_query
		$wpsolr_query   = $parameters[ WpSolrFilters::WPSOLR_ACTION_SOLARIUM_QUERY__PARAM_WPSOLR_QUERY ];
		$solarium_query = $parameters[ WpSolrFilters::WPSOLR_ACTION_SOLARIUM_QUERY__PARAM_SOLARIUM_QUERY ];

		if ( is_admin() && WPSOLR_Global::getOption()->get_option_plugin_woocommerce_is_replace_admin_orders_search() ) {
			if ( ! empty( $wpsolr_query->query['post_type'] ) && ( self::CONST_POST_TYPE_SHOP_ORDER === $wpsolr_query->query['post_type'] ) ) {

				//if ( WPSOLR_Global::getOption()->get_option_geolocation_is_filter_results_with_empty_coordinates() ) {

				// @var Document $solarium_query
				$solarium_query->addFilterQuery(
					array(
						'key'   => sprintf( 'type:%s', self::CONST_POST_TYPE_SHOP_ORDER ),
						'query' => sprintf( 'type:%s', self::CONST_POST_TYPE_SHOP_ORDER ),
					)
				);

				// sort by
				$wpsolr_order_by_mapping_fields = array(
					'ID'          => 'PID',
					'date'        => self::FIELD_POST_DATE_DT,
					'order_total' => self::FIELD_ORDER_TOTAL_F,
				);
				$original_order_by              = ! empty( $_GET['orderby'] ) ? $_GET['orderby'] : 'post_date';
				$orderby                        = ! empty( $wpsolr_order_by_mapping_fields[ $original_order_by ] ) ? $wpsolr_order_by_mapping_fields[ $original_order_by ] : self::FIELD_POST_DATE_DT;
				$order                          = ( empty( $_GET['order'] ) || ( 'desc' === $_GET['order'] ) ) ? Query::SORT_DESC : Query::SORT_ASC;
				$solarium_query->addSort( $orderby, $order );

				// Filter by order status
				$order_status = ! empty( $_GET['post_status'] ) ? $_GET['post_status'] : '';
				if ( ! empty( $order_status ) ) {
					$solarium_query->addFilterQuery(
						array(
							'key'   => 'post_status',
							'query' => sprintf( '%s:%s', WpSolrSchema::_FIELD_NAME_STATUS_S, $order_status ),
						)
					);
				}
			}
		} elseif ( is_search() && WPSOLR_Global::getOption()->get_option_plugin_woocommerce_is_replace_admin_orders_search() ) {
			// search page on front-end, filter out orders from results.

			// @var Document $solarium_query
			$solarium_query->addFilterQuery(
				array(
					'key'   => sprintf( '-type:%s', self::CONST_POST_TYPE_SHOP_ORDER ),
					'query' => sprintf( '-type:%s', self::CONST_POST_TYPE_SHOP_ORDER ),
				)
			);
		}


	}

	/**
	 * Return post status valid for orders
	 *
	 * @param string[] $post_statuses
	 * @param WP_Post $post
	 *
	 * @return string[]
	 */
	public function filter_post_statuses_to_index( array $post_statuses ) {

		// Add order statuses to indexable statuses
		$results = array_merge( $post_statuses, array_keys( wc_get_order_statuses() ) );

		// Default statuses.
		return $results;
	}

	/**
	 * Add woo attributes to a custom field with the same name
	 *
	 * @param $custom_fields
	 * @param $post_id
	 *
	 * @return mixed
	 */
	public function filter_custom_fields( $custom_fields, $post_id ) {

		if ( ! isset( $custom_fields ) ) {
			$custom_fields = array();
		}

		// Get the product correponding to this post
		$product = wc_get_product( $post_id );

		if ( false === $product ) {
			// Not a product
			return $custom_fields;
		}

		switch ( $product->get_type() ) {

			case self::PRODUCT_TYPE_VARIABLE:

				$product_variable = new WC_Product_Variable( $product );
				foreach ( $product_variable->get_available_variations() as $variation_array ) {

					foreach ( $variation_array['attributes'] as $attribute_name => $attribute_value ) {

						if ( ! isset( $custom_fields[ $attribute_name ] ) ) {
							$custom_fields[ $attribute_name ] = array();
						}

						if ( ! in_array( $attribute_value, $custom_fields[ $attribute_name ], true ) ) {

							array_push( $custom_fields[ $attribute_name ], $attribute_value );
						}
					}
				}


				break;

			default:

				foreach ( $product->get_attributes() as $attribute ) {

					//$terms = wc_get_product_terms( $product->id, $attribute['name'], array( 'fields' => 'names' ) );

					// Remove the eventual 'pa_' prefix from the global attribute name
					$attribute_name = $attribute['name'];
					if ( substr( $attribute_name, 0, 3 ) === 'pa_' ) {
						$attribute_name = substr( $attribute_name, 3, strlen( $attribute_name ) );
					}

					$custom_fields[ $attribute_name ] = explode( ',', $product->get_attribute( $attribute['name'] ) );
				}

				break;
		}


		return $custom_fields;
	}

	/**
	 * Add fields to a Solarium document
	 *
	 * @param $solarium_document_for_update
	 * @param $solr_indexing_options
	 * @param $post
	 * @param $attachment_body
	 *
	 * @return object Solarium document updated with fields
	 */
	function add_fields_to_document_for_update( $solarium_document_for_update, $solr_indexing_options, $post, $attachment_body ) {

		if ( self::CONST_POST_TYPE_SHOP_ORDER === $post->post_type ) {
			if ( ! $this->helper ) {
				$this->helper = new \Solarium\Core\Query\Helper();
			}

			// add order post_date for sorting
			if ( ! empty( $post->post_date ) ) {
				$field_name                                = self::FIELD_POST_DATE_DT;
				$solarium_document_for_update->$field_name = $this->helper->formatDate( $post->post_date );
			}
		}

		return $solarium_document_for_update;
	}

}