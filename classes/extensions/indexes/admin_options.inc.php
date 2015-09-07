<?php

/**
 * Included file to display admin options
 */


WpSolrExtensions::require_once_wpsolr_extension( WpSolrExtensions::OPTION_INDEXES, true );

$options_name         = 'wdm_solr_conf_data';
$settings_fields_name = 'solr_conf_options';

$solr_options = get_option( $options_name );

$option_indexes = new OptionIndexes();

?>
<div id="solr-hosting-tab">

	<?php

	$subtabs = array();


	// Move the 2 old style indexes in the new structure
	foreach (
		array(
			''      => 0,
			'_goto' => 1,
		) as $old_index_postfix => $old_index_indice
	) {
		if ( ! empty( $solr_options[ 'solr_host' . $old_index_postfix ] ) ) {

			// Copy the old index structure in the new index structure
			$index_array                   = array();
			$index_array['index_name']     = 'Index with no name';
			$index_array['index_protocol'] = isset( $solr_options[ 'solr_protocol' . $old_index_postfix ] ) ? $solr_options[ 'solr_protocol' . $old_index_postfix ] : 'http';
			$index_array['index_host']     = isset( $solr_options[ 'solr_host' . $old_index_postfix ] ) ? $solr_options[ 'solr_host' . $old_index_postfix ] : 'localhost';
			$index_array['index_port']     = isset( $solr_options[ 'solr_port' . $old_index_postfix ] ) ? $solr_options[ 'solr_port' . $old_index_postfix ] : '8983';
			$index_array['index_path']     = isset( $solr_options[ 'solr_path' . $old_index_postfix ] ) ? $solr_options[ 'solr_path' . $old_index_postfix ] : '/sol/index_name';
			$index_array['index_key']      = isset( $solr_options[ 'solr_key' . $old_index_postfix ] ) ? $solr_options[ 'solr_key' . $old_index_postfix ] : '';
			$index_array['index_secret']   = isset( $solr_options[ 'solr_secret' . $old_index_postfix ] ) ? $solr_options[ 'solr_secret' . $old_index_postfix ] : '';

			// Save the new index structure
			$solr_options['solr_indexes'][ $old_index_indice ] = $index_array;
		}
	}

	// Create the tabs from the Solr indexes already configured
	foreach ( $solr_options['solr_indexes'] as $index_indice => $index ) {
		$subtabs[ $index_indice ] = isset( $index['index_name'] ) ? $index['index_name'] : 'Index with no name';
	}


	if ( ! empty( $subtabs ) ) {
		$subtabs['new_index'] = 'Configure another index';
	}

	// Create subtabs on the left side
	$subtab = wpsolr_admin_sub_tabs( $subtabs );

	if ( 'new_index' === $subtab ) {
		$subtab                                  = strtoupper( md5( uniqid( rand(), true ) ) );
		$solr_options['solr_indexes'][ $subtab ] = array();
	} else {
		// Verify that current subtab is a Solr index indice.
		if ( ! $option_indexes->has_index( $subtab ) ) {
			// Use the first subtab element
			$subtab = key( $subtabs );
		}

	}

	?>

	<div id="solr-results-options" class="wdm-vertical-tabs-content">
		<form action="options.php" method="POST" id='settings_conf_form'>

			<?php
			settings_fields( $settings_fields_name );
			?>

			<!--  <div class="wdm_heading wrapper"><h3>Configure Solr</h3></div>-->
			<input type='hidden' id='adm_path' value='<?php echo admin_url(); ?>'>

			<?php
			foreach ( $solr_options['solr_indexes'] as $index_indice => $index ) {
				?>
				<div
					id="<?php echo $subtab != $index_indice ? $index_indice : "current_index_configuration_edited_id" ?>"
					class="wrapper" <?php echo $subtab != $index_indice ? "style='display:none'" : "" ?> >
					<h4 class='head_div'>Configure a Solr index</h4>

					<div class="wdm_row">
						<div class='solr_error'></div>
					</div>

					<div class="wdm_row">
						<div class='col_left'>Index name</div>

						<div class='col_right'><input type='text'
						                              placeholder="Give a name to your index"
						                              name="wdm_solr_conf_data[solr_indexes][<?php echo $index_indice ?>][index_name]"
								<?php echo $subtab === $index_indice ? "id='index_name'" : "" ?>
								                      value="<?php echo empty( $solr_options['solr_indexes'][ $index_indice ]['index_name'] ) ? '' : $solr_options['solr_indexes'][ $index_indice ]['index_name']; ?>">

							<div class="clear"></div>
							<span class='name_err'></span>
						</div>
						<div class="clear"></div>
					</div>

					<div class="wdm_row">
						<div class='col_left'>Solr Protocol</div>

						<div class='col_right'>
							<select
								name="wdm_solr_conf_data[solr_indexes][<?php echo $index_indice ?>][index_protocol]"
								<?php echo $subtab === $index_indice ? "id='index_protocol'" : "" ?>
								>
								<option value='http'
									<?php selected( 'http', empty( $solr_options['solr_indexes'][ $index_indice ]['index_protocol'] ) ? 'http' : $solr_options['solr_indexes'][ $index_indice ]['index_protocol'] ) ?>
									>http
								</option>
								<option value='https'
									<?php selected( 'https', empty( $solr_options['solr_indexes'][ $index_indice ]['index_protocol'] ) ? 'http' : $solr_options['solr_indexes'][ $index_indice ]['index_protocol'] ) ?>
									>https
								</option>

							</select>

							<div class="clear"></div>
							<span class='protocol_err'></span>
						</div>
						<div class="clear"></div>
					</div>
					<div class="wdm_row">
						<div class='col_left'>Solr Host</div>

						<div class='col_right'>
							<input type='text'
							       placeholder="localhost or ip adress or hostname. No 'http', no '/', no ':'"
							       name="wdm_solr_conf_data[solr_indexes][<?php echo $index_indice ?>][index_host]"
								<?php echo $subtab === $index_indice ? "id='index_host'" : "" ?>
								   value="<?php echo empty( $solr_options['solr_indexes'][ $index_indice ]['index_host'] ) ? '' : $solr_options['solr_indexes'][ $index_indice ]['index_host']; ?>">

							<div class="clear"></div>
							<span class='host_err'></span>
						</div>
						<div class="clear"></div>
					</div>
					<div class="wdm_row">
						<div class='col_left'>Solr Port</div>
						<div class='col_right'>
							<input type="text"
							       placeholder="8983 or 443 or any other port"
							       name="wdm_solr_conf_data[solr_indexes][<?php echo $index_indice ?>][index_port]"
								<?php echo $subtab === $index_indice ? "id='index_port'" : "" ?>
								   value="<?php echo empty( $solr_options['solr_indexes'][ $index_indice ]['index_port'] ) ? '' : $solr_options['solr_indexes'][ $index_indice ]['index_port']; ?>">

							<div class="clear"></div>
							<span class='port_err'></span>
						</div>
						<div class="clear"></div>
					</div>
					<div class="wdm_row">
						<div class='col_left'>Solr Path</div>
						<div class='col_right'>
							<input type='text'
							       placeholder="For instance /solr/index_name. Begins with '/', no '/' at the end"
							       name="wdm_solr_conf_data[solr_indexes][<?php echo $index_indice ?>][index_path]"
								<?php echo $subtab === $index_indice ? "id='index_path'" : "" ?>
								   value="<?php echo empty( $solr_options['solr_indexes'][ $index_indice ]['index_path'] ) ? '' : $solr_options['solr_indexes'][ $index_indice ]['index_path']; ?>">

							<div class="clear"></div>
							<span class='path_err'></span>
						</div>
						<div class="clear"></div>
					</div>
					<div class="wdm_row">
						<div class='col_left'>Key</div>
						<div class='col_right'>
							<input type='text'
							       placeholder="Optional security user if the index is protected with Http Basic Authentication"
							       name="wdm_solr_conf_data[solr_indexes][<?php echo $index_indice ?>][index_key]"
								<?php echo $subtab === $index_indice ? "id='index_key'" : "" ?>
								   value="<?php echo empty( $solr_options['solr_indexes'][ $index_indice ]['index_key'] ) ? '' : $solr_options['solr_indexes'][ $index_indice ]['index_key']; ?>">

							<div class="clear"></div>
							<span class='key_err'></span>
						</div>
						<div class="clear"></div>
					</div>
					<div class="wdm_row">
						<div class='col_left'>Secret</div>
						<div class='col_right'>
							<input type='text'
							       placeholder="Optional security password if the index is protected with Http Basic Authentication"
							       name="wdm_solr_conf_data[solr_indexes][<?php echo $index_indice ?>][index_secret]"
								<?php echo $subtab === $index_indice ? "id='index_secret'" : "" ?>
								   value="<?php echo empty( $solr_options['solr_indexes'][ $index_indice ]['index_secret'] ) ? '' : $solr_options['solr_indexes'][ $index_indice ]['index_secret']; ?>">

							<div class="clear"></div>
							<span class='sec_err'></span>
						</div>
						<div class="clear"></div>
					</div>

				</div>
			<?php } // foreach
			?>

			<div class="wdm_row">
				<div class="submit">
					<input name="check_solr_status" id='check_index_status' type="button"
					       class="button-primary wdm-save"
					       value="Check Solr Status, then Save this configuration"/> <span><img
							src='<?php echo plugins_url( '../../../images/gif-load_cir.gif', __FILE__ ) ?>'
							style='height:18px;width:18px;margin-top: 10px;display: none'
							class='img-load'>

                                             <img
	                                             src='<?php echo plugins_url( '../../../images/success.png', __FILE__ ) ?>'
	                                             style='height:18px;width:18px;margin-top: 10px;display: none'
	                                             class='img-succ'/>
                                                <img
	                                                src='<?php echo plugins_url( '../../../images/warning.png', __FILE__ ) ?>'
	                                                style='height:18px;width:18px;margin-top: 10px;display: none'
	                                                class='img-err'/></span>
				</div>
				<input name="delete_index_configuration" id='delete_index_configuration' type="button"
				       class="button-secondary wdm-delete"
				       value="Delete this configuration"/>
			</div>
			<div class="clear"></div>

		</form>
	</div>

</div>
