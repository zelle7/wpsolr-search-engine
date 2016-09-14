<?php

/**
 * WPSOLR Widget Sort.
 */
class WPSOLR_Widget_Sort extends WPSOLR_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'wpsolr_widget_sort', // Base ID
			__( 'WPSOLR Sort list', 'wpsolr_admin' ), // Name
			array( 'description' => __( 'Display Solr drop-down sort list', 'wpsolr_admin' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {

		if ( WPSOLR_Global::getOption()->get_search_is_replace_default_wp_search() && WPSOLR_Global::getOption()->get_search_is_use_current_theme_search_template() && WPSOLR_Query_Parameters::is_wp_search() ) {

			echo $args['before_widget'];

			echo '<div id="res_facets">';

			echo WPSOLR_UI_Sort::build(
				WPSOLR_Data_Sort::get_data(
					WPSOLR_Global::getOption()->get_sortby_items_as_array(),
					WPSOLR_Global::getOption()->get_sortby_items_labels(),
					WPSOLR_Global::getQuery()->get_wpsolr_sort(),
					OptionLocalization::get_options()
				)
			);

			echo '</div>';

			echo $args['after_widget'];
		}

	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		?>
		<p>
			Position this widget where you want your sort list to appear.
		</p>
		<p>
			Use the sort list to sort your results, with pretty much any field in your post types. Sort items must have
			been defined in WPSOLR admin pages.
		</p>
		<p>
			In next releases of WPSOLR, you will be able to configure your widget layout, to match your theme layout.
		</p>

		<?php
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	/*
	public function update( $new_instance, $old_instance ) {
		$instance          = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}*/

}