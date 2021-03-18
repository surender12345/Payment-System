<div class="wrap" bis_skin_checked="1">
<?php
	if(isset($_POST['submit-payment']) && !empty($_POST['submit-payment'])){
		update_option( 'calculator_rate', $_POST['calculator_rate'] );
	}
?>
	<h1>General Settings</h1>
		<p><span style="font-size: 16px;
    font-weight: 700;">Shortcode</span> -   [payment_system]</p>
		<form method="post" action="" novalidate="novalidate">
			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row"><label for="blogname">Calculator %</label></th>
						<td>

							<input name="calculator_rate" type="text" id="calculator_rate" value="<?php 
							echo get_option( 'calculator_rate' );
							?>" class="regular-text">
						</td>
					</tr>
				</tbody>
			</table>
			<p class="submit"><input type="submit" name="submit-payment" id="submit" class="button button-primary" value="Save Changes"></p>
		</form>
</div>


