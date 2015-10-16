<?php
/**
 * Managed Solr server temporary free account
 */
?>

<?php

?>

<div class="wrapper">

	<form method="POST">
		<h4 class='head_div'>Get your free Solr index to test WPSOLR</h4>

		<div class="wdm_row">
			<div class='col_left'>
				<select name='managed_solr_service_id'>
					<?php
					foreach ( OptionManagedSolrServer::get_managed_solr_services() as $list_managed_solr_service_id => $managed_solr_service ) {
						printf( "<option value='%s' %s>%s</option>",
							$list_managed_solr_service_id,
							selected( $list_managed_solr_service_id, $managed_solr_service_id, false ),
							$managed_solr_service['menu_label']
						);
					}
					?>
				</select>
			</div>

			<div class="col_right">

				<input name="submit_form_temporary_account" type="submit"
				       class="button-primary wdm-save"
				       value="Create my instant free Solr index"/>

				<div class="wdm_row">
					<h4>
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

