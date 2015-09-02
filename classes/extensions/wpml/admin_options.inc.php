<?php

/**
 * Included file to display admin options
 */


WpSolrExtensions::require_once_wpsolr_extension( WpSolrExtensions::EXTENSION_WPML, true );

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
				<br/><br/>
				This is how it works, when WPML is activated and checked below:<br/>
				- New field types, postfixed by language code, are defined in schema.xml and solrconfig.xml. Each type
				has it's own analyser, stemmer, porter .... that you can modify in schema.xml. For instance, *_fr and
				*_en.<br/>
				- For every Solr document, a language field will be added<br/>
				- Every Solr search qery will be done on the fields corresponding to the current WPML language. For
				instance "q=text_fr:recherche solr", or "q=text_en:solr search".<br/>
				- Autocomplete will also be applied to new fields. For instance, spelling_fr and spelling_en.

				<br/><br/>

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