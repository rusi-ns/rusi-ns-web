<tr valign="top">
					<th scope="row">X-Frame-Options
						<p class="description">This header can be used to indicate whether or not a browser should be allowed to render a page in a &lt;frame&gt;, &lt;iframe&gt; or &lt;object&gt; . Use this to avoid clickjacking attacks.</p>
					</th>
					<td>
			       		<fieldset>
			        		<legend class="screen-reader-text">X-Frame-Options</legend>
				        <?php
				        $x_frame_options = get_option('hh_x_frame_options', 0);
				        foreach ($bools as $k => $v)
				        {
				        	?><p><label><input type="radio" class="http-header" name="hh_x_frame_options" value="<?php echo $k; ?>"<?php checked($x_frame_options, $k, true); ?> /> <?php echo $v; ?></label></p><?php
				        }
				        ?>
			        	</fieldset>
			        </td>
		        	<td>
		        		<?php settings_fields( 'http-headers-xfo' ); ?>
						<?php do_settings_sections( 'http-headers-xfo' ); ?>
		<select name="hh_x_frame_options_value" class="http-header-value"<?php echo $x_frame_options == 1 ? NULL : ' readonly'; ?>>
						<?php
						$items = array('deny', 'sameorigin', 'allow-from');
						$x_frame_options_value = get_option('hh_x_frame_options_value');
						foreach ($items as $item)
						{
			?><option value="<?php echo $item; ?>"<?php selected($x_frame_options_value, $item); ?>><?php echo strtoupper($item); ?></option><?php
						}
						?>		
						</select>
		<input type="text" name="hh_x_frame_options_domain" placeholder="Domain" value="<?php echo esc_attr(get_option('hh_x_frame_options_domain')); ?>"<?php echo $x_frame_options == 1 && $x_frame_options_value == 'allow-from' ? NULL : ' style="display: none" readonly'; ?> />
					</td>
		        </tr>