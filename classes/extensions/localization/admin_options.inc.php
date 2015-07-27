<?php

/**
 * Included file to display admin options
 */


WpSolrExtensions::require_once_wpsolr_extension( WpSolrExtensions::OPTION_LOCALIZATION, true );

$options_name         = 'wdm_solr_localization_data';
$settings_fields_name = 'solr_localization_options';

// Retrieve all options in database
$options = OptionLocalization::get_options( true );

?>
<div id="localization-options" class="wdm-vertical-tabs-content">
	<form action="options.php" method="POST" id='localization_settings_form'>

		<?php
		settings_fields( $settings_fields_name );
		$localization = OptionLocalization::get_terms( $options );
		?>

		<div class='wrapper'>
			<h4 class='head_div'>Localization Options</h4>

			<div class="wdm_note">

				In this section, you will configure (localize) all the texts displayed on the front-end forms.<br/>
			</div>

			<div class="wdm_note">

				If WPML is activated, no need to use this section. All localizations are made in the WPML string
				translation plugin.<br>
				See https://wpml.org/documentation/support/language-configuration-files/<br>
			</div>

			<div class='wdm_row'>
				<div class='col_left'>
					Click here if you do not want to use this page localization texts.<br>
					For instance, if you use .mo files in your theme, or use WPML plugin.
				</div>
				<div class='col_right'>

					<?php
					$select_options = array(
						'localization_by_admin_options' => 'Use this page to localize all front-end texts',
						'localization_by_other_means'   => 'Use your theme/plugin .mo files to localize all front-end texts',
					);
					?>

					<select name='wdm_solr_localization_data[localization_method]' id='wpsolr_localization_method'>
						<?php foreach ( $select_options as $option_code => $option_label ) {

							echo sprintf( "<option value='%s' %s>%s</option>",
								$option_code,
								isset( $options['localization_method'] ) && $options['localization_method'] === $option_code ? "selected" : "",
								$option_label );

						}
						?>
					</select>

				</div>
			</div>
			<div style="clear:both"></div>

			<?php
			foreach ( $localization as $section_code => $section ) {
				?>

				<div class='wdm_row'>

					<div class='wdm_row'><h4
							class='head_div'><?php echo OptionLocalization::get_section_name( $section ); ?></h4></div>

					<?php
					foreach ( OptionLocalization::get_section_terms( $section ) as $term_code => $term_content ) {
						?>

						<div class='wdm_row'>
							<div class='col_left'>
								<?php echo $term_code; ?>
							</div>
							<div class='col_right'>

								<?php
								echo "<textarea id='message_user_without_capabilities_shown_no_results' name='wdm_solr_localization_data[terms][$section_code][section_terms][$term_code]'
						          rows='4' cols='100'>$term_content</textarea >"
								?>

							</div>
						</div>

						<?php
					} ?>
				</div>
				<div style="clear:both"></div>
				<?php
			}
			?>


			<div class='wdm_row'>
				<div class="submit">
					<input name="save_selected_options_res_form"
					       id="save_selected_extension_groups_form" type="submit"
					       class="button - primary wdm - save" value="Save Options"/>

				</div>
			</div>

		</div>

	</form>
</div>