<tr valign="top">
		        <th scope="row">X-XSS-Protection
		        	<p class="description">This header enables the Cross-site scripting (XSS) filter built into most recent web browsers. It's usually enabled by default anyway, so the role of this header is to re-enable the filter for this particular website if it was disabled by the user. </p>
		        </th>
		        <td>
		       		<fieldset>
		        		<legend class="screen-reader-text">X-XSS-Protection</legend>
			        <?php
			        $x_xxs_protection = get_option('hh_x_xxs_protection', 0);
			        foreach ($bools as $k => $v)
			        {
			        	?><p><label><input type="radio" class="http-header" name="hh_x_xxs_protection" value="<?php echo $k; ?>"<?php checked($x_xxs_protection, $k, true); ?> /> <?php echo $v; ?></label></p><?php
			        }
			        ?>
		        	</fieldset>
		        </td>
		        <td>
		        	<?php settings_fields( 'http-headers-xss' ); ?>
					<?php do_settings_sections( 'http-headers-xss' ); ?>
		        	<select name="hh_x_xxs_protection_value" class="http-header-value"<?php disabled($x_xxs_protection, 0); ?>>
					<?php
					$items = array('0', '1', '1; mode=block');
					$x_xxs_protection_value = get_option('hh_x_xxs_protection_value');
					foreach ($items as $item) {
						?><option value="<?php echo $item; ?>"<?php selected($x_xxs_protection_value, $item); ?>><?php echo $item; ?></option><?php
					}
					?>
					</select>
				</td>
		        </tr>