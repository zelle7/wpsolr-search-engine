<?php

/**
 * Interface for filters definitions.
 *
 * Developers: try to use these constants in your filters.
 */
class WpSolrFilters {

	// Add 'groups' plugin infos to a Solr results document
	const WPSOLR_FILTER_SOLR_RESULTS_DOCUMENT_GROUPS_INFOS = 'wpsolr_filter_solr_results_document_groups_infos';

	/**
	 * @link https://www.wpsolr.com/guide/actions-and-filters/index-modify-custom-fields/
	 * Customize a post custom fields before they are processed in a Solarium update document
	 **/
	const WPSOLR_FILTER_POST_CUSTOM_FIELDS = 'wpsolr_filter_post_custom_fields';

	/**
	 * @link https://www.wpsolr.com/guide/actions-and-filters/index-modify-a-document/
	 * Customize a fully processed Solarium update document before sending to Solr for indexing
	 **/
	const WPSOLR_FILTER_SOLARIUM_DOCUMENT_FOR_UPDATE = 'wpsolr_filter_solarium_document_for_update';

	// Customize a fully processed attachment content before sending to Solr for indexing
	const WPSOLR_FILTER_ATTACHMENT_TEXT_EXTRACTED_BY_APACHE_TIKA = 'wpsolr_filter_attachment_text_extracted_by_apache_tika';

	/**
	 * @link https://www.wpsolr.com/guide/actions-and-filters/search-query-query/
	 * Solarium query before a search is performed
	 **/
	const WPSOLR_ACTION_SOLARIUM_QUERY = 'wpsolr_action_solarium_query';
	const WPSOLR_ACTION_SOLARIUM_QUERY__PARAM_SOLARIUM_QUERY = 'solarium_query_object';
	const WPSOLR_ACTION_SOLARIUM_QUERY__PARAM_SEARCH_TERMS = 'keywords';
	const WPSOLR_ACTION_SOLARIUM_QUERY__PARAM_SEARCH_USER = 'user';
	const WPSOLR_ACTION_SOLARIUM_QUERY__PARAM_WPSOLR_QUERY = 'wpsolr_query';
	const WPSOLR_ACTION_SOLARIUM_QUERY__PARAM_SOLARIUM_CLIENT = 'solarium_client';

	// Customize the search page url
	const WPSOLR_FILTER_SEARCH_PAGE_URL = 'wpsolr_filter_search_page_url';

	// Action before a solr index configuration is deleted
	const WPSOLR_ACTION_BEFORE_A_SOLR_INDEX_CONFIGURATION_DELETION = 'wpsolr_action_before_a_solr_index_configuration_deletion';

	/**
	 * @link https://www.wpsolr.com/guide/actions-and-filters/index-modify-sql-statement/
	 * Modify sql query statement used to retrieve the posts to be indexed
	 **/
	const WPSOLR_FILTER_SQL_QUERY_STATEMENT = 'wpsolr_filter_sql_query_statement';

	// Filter to get the default search index indice
	const WPSOLR_FILTER_SEARCH_GET_DEFAULT_SOLR_INDEX_INDICE = 'wpsolr_filter_get_default_search_solr_index_indice';

	// Filter to get the indexing index indice for a post
	const WPSOLR_FILTER_INDEXING_GET_SOLR_INDEX_INDICE_FOR_A_POST = 'wpsolr_filter_get_default_indexing_solr_index_indice_for_a_post';

	// Filter to change search page parameters before creation
	const WPSOLR_FILTER_BEFORE_CREATE_SEARCH_PAGE = 'wpsolr_filter_before_create_search_page';

	// Filter to change search page slug parameters before creation
	const WPSOLR_FILTER_SEARCH_PAGE_SLUG = 'wpsolr_filter_search_page_slug';

	// Filter to retrieve a post language from multi-language extensions
	const WPSOLR_FILTER_POST_LANGUAGE = 'wpsolr_filter_post_language';

	// Filter to change a facet name on search page
	const WPSOLR_FILTER_SEARCH_PAGE_FACET_NAME = 'wpsolr_filter_search_page_facet_name';

	// Filter before retrieving an option value
	const WPSOLR_FILTER_BEFORE_GET_OPTION_VALUE = 'wpsolr_filter_before_get_option_value';

	// Filter after retrieving an option value
	const WPSOLR_FILTER_AFTER_GET_OPTION_VALUE = 'wpsolr_filter_after_get_option_value';

	// Filter a sort option
	const WPSOLR_FILTER_SORT = 'wpsolr_filter_sort';

	// Action to add string translations to WPML/Polylang
	const ACTION_TRANSLATION_REGISTER_STRINGS = 'wpsolr_action_translation_register_strings';

	// Get a translated string from WPML/Polylang
	const WPSOLR_FILTER_TRANSLATION_STRING = 'wpsolr_filter_translation_string';

	/**
	 * @link https://www.wpsolr.com/guide/actions-and-filters/index-embedded-files/
	 * Embedded files in post content.
	 **/
	const WPSOLR_FILTER_GET_POST_ATTACHMENTS = 'wpsolr_filter_get_post_attachments';

	/**
	 * @link https://www.wpsolr.com/guide/actions-and-filters/options-custom-fields/
	 * Custom fields shown in admin screen.
	 **/
	const WPSOLR_FILTER_INDEX_CUSTOM_FIELDS = 'wpsolr_filter_index_custom_fields';

	// Filter to add additional fields to the Ajax search form
	const WPSOLR_FILTER_APPEND_FIELDS_TO_AJAX_SEARCH_FORM = 'wpsolr_filter_append_fields_to_ajax_search_form';

	/**
	 * @link https://www.wpsolr.com/guide/actions-and-filters/search-results-url-parameters/
	 * Search url parameters
	 **/
	const WPSOLR_ACTION_URL_PARAMETERS = 'wpsolr_filter_url_parameters';

	/**
	 * @link https://www.wpsolr.com/guide/actions-and-filters/search-results-javascript-parameters/
	 * Javascript search parameters (fron-end)
	 **/
	const WPSOLR_FILTER_JAVASCRIPT_FRONT_LOCALIZED_PARAMETERS = 'wpsolr_filter_javascript_front_localized_parameters';

	/**
	 * @link https://www.wpsolr.com/guide/actions-and-filters/search-query-fields-list/
	 * Fields list in Solr query
	 **/
	const WPSOLR_FILTER_FIELDS = 'wpsolr_filter_fields';

	/**
	 * @link https://www.wpsolr.com/guide/actions-and-filters/options-solr-dynamic-field-types/
	 * Solr dynamic field types
	 **/
	const WPSOLR_FILTER_SOLR_FIELD_TYPES = 'wpsolr_filter_solr_field_types';

	/**
	 * @link https://www.wpsolr.com/guide/actions-and-filters/options-default-sort-list/
	 * Default sort list shown in admin.
	 **/
	const WPSOLR_FILTER_DEFAULT_SORT_FIELDS = 'wpsolr_filter_default_sort_fields';

	/**
	 * @link https://www.wpsolr.com/guide/actions-and-filters/search-results-sort-fields/
	 * Sort items to be shown in the drop-down list (front-end).
	 **/
	const WPSOLR_FILTER_SORT_FIELDS = 'wpsolr_filter_sort_fields';

	/**
	 * @link https://www.wpsolr.com/guide/actions-and-filters/search-results-default-sort/
	 * Default sort when none selected (front-end).
	 **/
	const WPSOLR_FILTER_DEFAULT_SORT = 'wpsolr_filter_default_sort';

	/**
	 * @link https://www.wpsolr.com/guide/actions-and-filters/search-results-append-html/
	 * Append custom html to each ajax results snippet
	 **/
	const WPSOLR_FILTER_SOLR_RESULTS_APPEND_CUSTOM_HTML = 'wpsolr_filter_solr_results_append_custom_html';

	/**
	 * @link https://www.wpsolr.com/guide/actions-and-filters/search-results-modify-posts/
	 * Modify posts before rendering
	 **/
	const WPSOLR_ACTION_POSTS_RESULTS = 'wpsolr_action_posts_results';


	/**
	 * @link ????
	 * Sanitize a field content before indexing.
	 **/
	const WPSOLR_FILTER_INDEX_SANITIZE_FIELD = '';

	/**
	 * @link ????
	 * Array of post statuses which can be indexed. Default is array('published').
	 **/
	const WPSOLR_FILTER_POST_STATUSES_TO_INDEX = 'wpsolr_filter_post_statuses_to_index';


	/**
	 * @link ????
	 * Replace the WP query by a WPSOLR query ?.
	 **/
	const WPSOLR_FILTER_IS_REPLACE_BY_WPSOLR_QUERY = 'wpsolr_filter_is_replace_by_wpsolr_query';

	/**
	 * @link ????
	 * Facets to display.
	 **/
	const WPSOLR_FILTER_FACETS_TO_DISPLAY = 'wpsolr_filter_facets_to_display';

	/**
	 * @link ????
	 * Filter facets content to display.
	 **/
	const WPSOLR_FILTER_FACETS_CONTENT_TO_DISPLAY = 'wpsolr_filter_facets_content_to_display';
}