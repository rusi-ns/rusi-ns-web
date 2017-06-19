<tr>
	<th scope="row">Access-Control-Max-Age
		<p class="description">The Access-Control-Max-Age header indicates how much time, the result of a preflight request, can be cached.</p>
	</th>
	<td>
        <fieldset>
        	<legend class="screen-reader-text">Access-Control-Max-Age</legend>
	    <?php
        $access_control_max_age = get_option('hh_access_control_max_age', 0);
        foreach ($bools as $k => $v)
        {
        	?><p><label><input type="radio" class="http-header" name="hh_access_control_max_age" value="<?php echo $k; ?>"<?php checked($access_control_max_age, $k); ?> /> <?php echo $v; ?></label></p><?php
        }
        ?>
		</fieldset>
	</td>
	<td>
		<?php settings_fields( 'http-headers-acma' ); ?>
		<?php do_settings_sections( 'http-headers-acma' ); ?>
		<input type="text" name="hh_access_control_max_age_value" class="http-header-value" value="<?php echo esc_attr(get_option('hh_access_control_max_age_value')); ?>"<?php disabled($access_control_max_age, 0); ?>>
	</td>
</tr>