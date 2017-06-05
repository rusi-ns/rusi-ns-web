<tr valign="top">
		        <th scope="row">X-Content-Type-Options
		        	<p class="description">Prevents Internet Explorer and Google Chrome from MIME-sniffing a response away from the declared content-type. This also applies to Google Chrome, when downloading extensions. This reduces exposure to drive-by download attacks and sites serving user uploaded content that, by clever naming, could be treated by MSIE as executable or dynamic HTML files.</p>
		        </th>
		        <td>
		       		<fieldset>
		        		<legend class="screen-reader-text">X-Content-Type-Options</legend>
			        <?php
			        $x_content_type_options = get_option('hh_x_content_type_options', 0);
			        foreach ($bools as $k => $v)
			        {
			        	?><p><label><input type="radio" class="http-header" name="hh_x_content_type_options" value="<?php echo $k; ?>"<?php checked($x_content_type_options, $k); ?> /> <?php echo $v; ?></label></p><?php
			        }
			        ?>
		        	</fieldset>
		        </td>
				<td>
					<?php settings_fields( 'http-headers-cto' ); ?>
					<?php do_settings_sections( 'http-headers-cto' ); ?>
					<select name="hh_x_content_type_options_value" class="http-header-value"<?php disabled($x_content_type_options, 0); ?>>
					<?php
					$items = array('nosniff');
					$x_content_type_options_value = get_option('hh_x_content_type_options_value');
					foreach ($items as $item) {
						?><option value="<?php echo $item; ?>"<?php selected($x_content_type_options_value, $item); ?>><?php echo $item; ?></option><?php
					}
					?>
					</select>
				</td>
		        </tr>