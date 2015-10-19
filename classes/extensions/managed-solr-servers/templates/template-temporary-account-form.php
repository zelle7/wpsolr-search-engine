<?php
/**
 * Managed Solr server temporary free account
 */
?>

<?php
?>

<div class="wrapper">

	<form method="POST" id="form_temporary_index">
		<input type="hidden" name="wpsolr_action" value="wpsolr_admin_action_form_temporary_index"/>

		<h4 class='head_div'>Get your free Solr index to test WPSOLR</h4>

		<div class="wdm_row">
			<div class='col_left'>
				<select name='managed_solr_service_id'>
					<?php
					foreach ( OptionManagedSolrServer::get_managed_solr_services() as $list_managed_solr_service_id => $managed_solr_service ) {
						printf( "<option value='%s' %s>%s</option>",
							$list_managed_solr_service_id,
							selected( $list_managed_solr_service_id, $managed_solr_service_id, false ),
							$managed_solr_service[ OptionManagedSolrServer::MANAGED_SOLR_SERVICE_LABEL ]
						);
					}
					?>
				</select>
			</div>

			<div class="col_right">

				<input name="submit_button_form_temporary_index" type="submit"
				       class="button-primary wdm-save"
				       value="Create my instant free Solr index"/>

				<div class="wdm_row">
					<h4 class="solr_error">
						<?php
						if ( ! empty( $response_error ) ) {
							echo $response_error;
						}
						?>
					</h4>
				</div>

				<div class="wdm_note">
					If you want to quickly test WPSOLR, without the burden of your own Solr server.</br><br/>
					Valid during 2 hours. After that, the index will be deleted automatically.<br/><br/>
				</div>

			</div>
			<div class="clear"></div>
		</div>

	</form>
</div>

<div class="numberCircle">or</div>
<div style="clear: both; margin-bottom: 15px;"></div>

