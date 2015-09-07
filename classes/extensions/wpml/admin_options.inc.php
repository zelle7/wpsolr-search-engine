<?php

/**
 * Included file to display admin options
 */


WpSolrExtensions::require_once_wpsolr_extension( WpSolrExtensions::EXTENSION_WPML, true );
WpSolrExtensions::require_once_wpsolr_extension( WpSolrExtensions::OPTION_INDEXES, true );

$extension_options_name = 'wdm_solr_extension_wpml_data';
$settings_fields_name   = 'solr_extension_wpml_options';

$array_extension_options = get_option( $extension_options_name );
$is_plugin_active        = WpSolrExtensions::is_plugin_active( WpSolrExtensions::EXTENSION_WPML );
?>

<div id="extension_wpml-options" class="wdm-vertical-tabs-content">
	<form action="options.php" method="POST" id='extension_wpml_settings_form'>
		<?php
		settings_fields( $settings_fields_name );
		$solr_extension_wpml_options = get_option( $extension_options_name, array(
			'is_extension_active' => '0',
		) );
		?>

		<div class='wrapper'>
			<h4 class='head_div'>WPML plugin Options</h4>

			<div class="wdm_note">

				In this section, you will configure how to manage your multi-language Solr search with WPML plugin.
				<br/>

				<?php if ( ! $is_plugin_active ): ?>
					<p>
						Status: <a href="https://wpml.org/"
						           target="_blank">WPML
							plugin</a> is not activated. First, you need to install and
						activate it to configure WPSOLR.
					</p>
					<p>
						You will also need to re-index all your data if you activated
						<a href="https://wpml.org/"
						   target="_blank">WPML
							plugin</a>
						after you activated WPSOLR.
					</p>
				<?php else: ?>
					<p>
						Status: <a href="https://wpml.org/"
						           target="_blank">WPML
							plugin</a>
						is activated. You can now configure WPSOLR to use it.
					</p>
				<?php endif; ?>
			</div>
			<div class="wdm_row">
				<div class='col_left'>Use the <a
						href="https://wpml.org/"
						target="_blank">WPML
						plugin</a>
					to manage multi-language Solr search.
					<br/><br/>Think of re-indexing all your data if <a
						href="https://wpml.org/"
						target="_blank">WPML
						plugin</a> was installed after WPSOLR.
				</div>
				<div class='col_right'>
					<input type='checkbox'
					       name='wdm_solr_extension_wpml_data[is_extension_active]'
					       value='is_extension_active'
						<?php checked( 'is_extension_active', isset( $solr_extension_wpml_options['is_extension_active'] ) ? $solr_extension_wpml_options['is_extension_active'] : '' ); ?>>
				</div>
				<div class="clear"></div>
			</div>


			<h4 class='head_div'>Select which Solr index will index which language</h4>

			<div class="wdm_note">
				Each language must be stored, and queried, on it's own Solr index.<br/>
				- Language awareness: each language can be configured with it's own schema.xml: language
				specific filters,
				analysers, stemmers .... <br/>
				- Easy to understand: each schema.xml has the same stucture, except for the language specific settings
				<br/>
				- Fully featured: autocompletion and suggestions work out of the box by language <br/>
				- Custom: each language can have a totally custom schema.xml if necessary <br/>

			</div>
			<?php
			$option_indexes = new OptionIndexes();
			$solr_indexes   = $option_indexes->get_indexes();
			foreach ( PluginWpml::get_languages() as $language_code => $language ) {
				?>
				<div class="wdm_row">
					<div class='col_left'>
						Language '<?php echo $language_code ?>'
					</div>
					<div class='col_right'>

						<?php
						// Language has a Solr index ?
						$language_has_solr_index = isset( $solr_extension_wpml_options['solr_indexes_by_languages'][ $language_code ] )
						                           && $solr_extension_wpml_options['solr_indexes_by_languages'][ $language_code ] != '';

						// Solr index exists ?
						if ( $language_has_solr_index ) {
							$language_has_solr_index = $option_indexes->has_index( $solr_extension_wpml_options['solr_indexes_by_languages'][ $language_code ] );
						}
						?>

						<?php
						// Language has a Solr index ?
						echo $language_has_solr_index
							? 'is managed by Solr index:&nbsp;&nbsp;'
							: 'is not managed by any Solr index yet';
						?>

						<select
							name='wdm_solr_extension_wpml_data[solr_indexes_by_languages][<?php echo $language_code ?>]'>

							<?php
							// Empty option
							echo sprintf( "<option value='%s' %s>%s</option>",
								'',
								'',
								''
							);
							?>

							<?php
							foreach ( $solr_indexes as $solr_index_indice => $solr_index ) {

								echo sprintf( "<option value='%s' %s>%s</option>",
									$solr_index_indice,
									selected( $solr_index_indice, isset( $solr_extension_wpml_options['solr_indexes_by_languages'][ $language_code ] ) ? $solr_extension_wpml_options['solr_indexes_by_languages'][ $language_code ] : '' ),
									$solr_index['index_name'] );

							}
							?>

						</select>

						<?php
						// Warning message: the language has no Solr index
						echo $language_has_solr_index
							? ''
							: sprintf( "<div class='solr_error'>Warning: language '%s' is not managed by Solr. '%s' data will not appear in the search results.</div>", $language_code, $language_code );
						?>

					</div>
					<div class="clear"></div>
				</div>
				<?php
			} // end of languages loop
			?>

			<?php
			// One Solr index by language ?
			$each_language_has_a_unique_solr_index = PluginWpml::each_language_has_a_unique_solr_index();

			echo $each_language_has_a_unique_solr_index
				? ''
				: sprintf( "<div class='solr_error'>Warning: <br/>Each language should have it's own unique Solr index. <br/>Search results will return mixed content from the languages with the same Solr index.</div>" );
			?>

			<div class='wdm_row'>
				<div class="submit">
					<input name="save_selected_options_res_form"
					       id="save_selected_extension_wpml_form" type="submit"
					       class="button-primary wdm-save" value="Save Options"/>
				</div>
			</div>
		</div>

	</form>
</div>