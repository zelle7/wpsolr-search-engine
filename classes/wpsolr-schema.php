<?php

/**
 * Manage schema.xml definitions
 */
class WpSolrSchema {

	const EXTENSION_SEPARATOR = '_';

	// Solr dynamic types extensions
	const _SOLR_DYNAMIC_TYPE_STRING = '_str';
	const _SOLR_DYNAMIC_TYPE_S = '_s';
	const _SOLR_DYNAMIC_TYPE_INTEGER = '_i';
	const _SOLR_DYNAMIC_TYPE_INTEGER_LONG = '_l';
	const _SOLR_DYNAMIC_TYPE_FLOAT = '_f';
	const _SOLR_DYNAMIC_TYPE_FLOAT_DOUBLE = '_d';
	const _SOLR_DYNAMIC_TYPE_DATE = '_dt';
	const _SOLR_DYNAMIC_TYPE_CUSTOM_FIELD = 'custom_field';

	// List of Solr dynamic types extensions
	public static $SOLR_DYNAMIC_TYPE_EXTENSIONS = array(
		self::_SOLR_DYNAMIC_TYPE_STRING  => array(
			'label'    => 'Text, not sortable, multivalued',
			'sortable' => false,
		),
		self::_SOLR_DYNAMIC_TYPE_S       => array(
			'label'    => 'Text, sortable',
			'sortable' => true,
		),
		self::_SOLR_DYNAMIC_TYPE_INTEGER => array(
			'label'    => 'Integer number, sortable',
			'sortable' => true,
		),
		self::_SOLR_DYNAMIC_TYPE_FLOAT   => array(
			'label'    => 'Floating point number',
			'sortable' => true,
		),
		/*
		self::_SOLR_DYNAMIC_TYPE_DATE    => array(
			'label'    => 'Date, sortable',
			'sortable' => true,
		),
		*/
		/*
		self::_SOLR_DYNAMIC_TYPE_INTEGER_LONG => array('label' => 'Big integer, sortable','sortable' => false,),
		self::_SOLR_DYNAMIC_TYPE_FLOAT        => array(
			'label'    => 'Floating point number, sortable',
			'sortable' => false,
		),
		self::_SOLR_DYNAMIC_TYPE_FLOAT_DOUBLE => array(
			'label'    => 'Double float',
			'sortable' => true,
		),
		self::_SOLR_DYNAMIC_TYPE_CUSTOM_FIELD => array(
			'label'    => 'Field defined in schema.xml',
			'sortable' => true,
		),
		*/
	);

	// Field queried by default. Necessary to get highlighting right.
	const _FIELD_NAME_DEFAULT_QUERY = 'text';

	/*
	 * Solr document field names
	 */
	const _FIELD_NAME_ID = 'id';
	const _FIELD_NAME_PID = 'PID';
	const _FIELD_NAME_TITLE = 'title';
	const _FIELD_NAME_CONTENT = 'content';
	const _FIELD_NAME_AUTHOR = 'author';
	const _FIELD_NAME_AUTHOR_S = 'author_s';
	const _FIELD_NAME_TYPE = 'type';
	const _FIELD_NAME_DATE = 'date';
	const _FIELD_NAME_MODIFIED = 'modified';
	const _FIELD_NAME_DISPLAY_DATE = 'displaydate';
	const _FIELD_NAME_DISPLAY_MODIFIED = 'displaymodified';
	const _FIELD_NAME_PERMALINK = 'permalink';
	const _FIELD_NAME_COMMENTS = 'comments';
	const _FIELD_NAME_NUMBER_OF_COMMENTS = 'numcomments';
	const _FIELD_NAME_CATEGORIES = 'categories';
	const _FIELD_NAME_CATEGORIES_STR = 'categories_str';
	const _FIELD_NAME_TAGS = 'tags';
	const _FIELD_NAME_CUSTOM_FIELDS = 'categories';
	const _FIELD_NAME_FLAT_HIERARCHY = 'flat_hierarchy_%s'; // field contains hierarchy as a string with separator
	const _FIELD_NAME_NON_FLAT_HIERARCHY = 'non_flat_hierarchy_%s'; // filed contains hierarchy as an array
	const _FIELD_NAME_BLOG_NAME_STR = 'blog_name_str';
	const _FIELD_NAME_POST_THUMBNAIL_HREF_STR = 'post_thumbnail_href_str';
	const _FIELD_NAME_POST_HREF_STR = 'post_href_str';

	// Separator of a flatten hierarchy
	const FACET_HIERARCHY_SEPARATOR = '->';

	/*
		 * Dynamic types
		 */
	// Solr dynamic type postfix for text
	const _DYNAMIC_TYPE_POSTFIX_TEXT = '_t';


	// Definition translated fields when multi-languages plugins are activated
	public static $multi_language_fields = array(
		array(
			'field_name'      => WpSolrSchema::_FIELD_NAME_TITLE,
			'field_extension' => WpSolrSchema::_DYNAMIC_TYPE_POSTFIX_TEXT,
		),
		array(
			'field_name'      => WpSolrSchema::_FIELD_NAME_CONTENT,
			'field_extension' => WpSolrSchema::_DYNAMIC_TYPE_POSTFIX_TEXT,
		),
	);

	/**
	 * Get all extensions
	 *
	 * @return array
	 */
	public static function get_solr_dynamic_entensions() {
		return self::$SOLR_DYNAMIC_TYPE_EXTENSIONS;
	}

	/**
	 * Get extension id used by default
	 *
	 * @return array
	 */
	public static function get_solr_dynamic_entension_id_by_default() {
		return self::_SOLR_DYNAMIC_TYPE_STRING;
	}


	/**
	 * Get extension label
	 *
	 * @param $solr_dynamic_type
	 *
	 * @return string
	 */
	public static function get_solr_dynamic_entension_label( $solr_dynamic_type ) {

		return ( ! empty( $solr_dynamic_type ) && ! empty( $solr_dynamic_type['label'] ) ? $solr_dynamic_type['label'] : '' );
	}

	/**
	 * Is extension id sortable ?
	 *
	 * @param $solr_dynamic_type
	 *
	 * @return string
	 */
	public static function get_solr_dynamic_entension_is_sortable( $solr_dynamic_type ) {

		return ( ! empty( $solr_dynamic_type ) && ! empty( $solr_dynamic_type['sortable'] ) ? $solr_dynamic_type['sortable'] : false );
	}

	/**
	 * Get an extension definition by it's id
	 *
	 * @param string $solr_dynamic_type_id
	 *
	 * @return array
	 */
	public static function get_solr_dynamic_entension( $solr_dynamic_type_id ) {
		return ( ! empty( self::$SOLR_DYNAMIC_TYPE_EXTENSIONS[ $solr_dynamic_type_id ] ) ? self::$SOLR_DYNAMIC_TYPE_EXTENSIONS[ $solr_dynamic_type_id ] : array() );
	}

	/**
	 * Is an extension id sortable ?
	 *
	 * @param $solr_dynamic_type_id
	 *
	 * @return string
	 */
	public static function get_solr_dynamic_entension_id_is_sortable( $solr_dynamic_type_id ) {

		$extension = self::get_solr_dynamic_entension( $solr_dynamic_type_id );

		return self::get_solr_dynamic_entension_is_sortable( $extension );
	}

	/**
	 * Gey an extension id label
	 *
	 * @param $solr_dynamic_type_id
	 *
	 * @return string
	 */
	public static function get_solr_dynamic_entension_id_label( $solr_dynamic_type_id ) {

		$extension = self::get_solr_dynamic_entension( $solr_dynamic_type_id );

		return self::get_solr_dynamic_entension_label( $extension );
	}

}