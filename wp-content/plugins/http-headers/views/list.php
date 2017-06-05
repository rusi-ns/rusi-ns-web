<table class="hh-index-table">
	<thead>
		<tr>
			<th>Header</th>
			<th style="width: 45%">Value</th>
			<th class="hh-status">Status</th>
			<th></th>
		</tr>
	</thead>
	<tbody>
	<?php 
	$headers = array(
		'hh_x_frame_options' => array('X-Frame-Options', 'x-frame-options'),
		'hh_x_xxs_protection' => array('X-XSS-Protection', 'x-xss-protection'),
		'hh_x_content_type_options' => array('X-Content-Type-Options', 'x-content-type-options'),
		'hh_x_ua_compatible' => array('X-UA-Compatible', 'x-ua-compatible'),
		'hh_strict_transport_security' => array('Strict-Transport-Security', 'strict-transport-security'),
		'hh_p3p' => array('P3P', 'p3p'),
		'hh_public_key_pins' => array('Public-Key-Pins', 'public-key-pins'),
		'hh_referrer_policy' => array('Referrer-Policy', 'referrer-policy'),
		'hh_content_security_policy' => array('Content Security Policy', 'content-security-policy'),
		'hh_access_control_allow_origin' => array('Access-Control-Allow-Origin', 'access-control-allow-origin'),
		'hh_access_control_allow_credentials' => array('Access-Control-Allow-Credentials', 'access-control-allow-credentials'),
		'hh_access_control_max_age' => array('Access-Control-Max-Age', 'access-control-max-age'),
		'hh_access_control_allow_methods' => array('Access-Control-Allow-Methods', 'access-control-allow-methods'),
		'hh_access_control_allow_headers' => array('Access-Control-Allow-Headers', 'access-control-allow-headers'),
		'hh_access_control_expose_headers' => array('Access-Control-Expose-Headers', 'access-control-expose-headers'),
	);
	foreach ($headers as $key => $item)
	{
		$option = get_option($key, 0);
		$isOn = (int) $option === 1;
		$value = NULL;
		if ($isOn)
		{
			$value = get_option($key .'_value');
			switch ($key)
			{
				case 'hh_p3p':
					if (!empty($value))
					{
						$value = sprintf('CP="%s"', join(' ', array_keys($value)));
					}
					break;
				case 'hh_x_frame_options':
					if ($value == 'allow-from')
					{
						$value .= ' ' . get_option('hh_x_frame_options_domain');
					}
					break;
				case 'hh_strict_transport_security':
					$tmp = array();
					$hh_strict_transport_security_max_age = get_option('hh_strict_transport_security_max_age');
					if ($hh_strict_transport_security_max_age !== false)
					{
						$tmp[] = sprintf('max-age=%u', $hh_strict_transport_security_max_age);
						if (get_option('hh_strict_transport_security_sub_domains'))
						{
							$tmp[] = 'includeSubDomains';
						}
						if (get_option('hh_strict_transport_security_preload'))
						{
							$tmp[] = 'preload';
						}
					} else {
						$tmp = array(get_option('hh_strict_transport_security_value'));
					}
					if (!empty($tmp))
					{
						$value = join('; ', $tmp);
					}
					break;
				case 'hh_public_key_pins':
					$public_key_pins_sha256_1 = get_option('hh_public_key_pins_sha256_1');
					$public_key_pins_sha256_2 = get_option('hh_public_key_pins_sha256_2');
					$public_key_pins_max_age = get_option('hh_public_key_pins_max_age');
					$public_key_pins_sub_domains = get_option('hh_public_key_pins_sub_domains');
					$public_key_pins_report_uri = get_option('hh_public_key_pins_report_uri');
					if (!empty($public_key_pins_sha256_1) && !empty($public_key_pins_sha256_2) && !empty($public_key_pins_max_age)) {
							
						$public_key_pins = array();
						$public_key_pins[] = sprintf('pin-sha256="%s"', $public_key_pins_sha256_1);
						$public_key_pins[] = sprintf('pin-sha256="%s"', $public_key_pins_sha256_2);
						$public_key_pins[] = sprintf("max-age=%u", $public_key_pins_max_age);
						if ($public_key_pins_sub_domains) {
							$public_key_pins[] = "includeSubDomains";
						}
						if (!empty($public_key_pins_report_uri)) {
							$public_key_pins[] = sprintf('report-uri="%s"', $public_key_pins_report_uri);
						}
						$value = join('; ', $public_key_pins);
					}
					break;
				case 'hh_access_control_allow_origin':
					if ($value == 'origin')
					{
						$value = get_option('hh_access_control_allow_origin_url');
					}
					break;
				case 'hh_access_control_expose_headers':
				case 'hh_access_control_allow_headers':
				case 'hh_access_control_allow_methods':
					$value = join(', ', array_keys($value));
					break;
				case 'hh_content_security_policy':
					$csp = array();
					foreach ($value as $key => $val)
					{
						if (!empty($val))
						{
							$csp[] = sprintf("%s %s", $key, $val);
						}
					}
					if (!empty($csp))
					{
						$value = join('; ', $csp);
					}
					break;
				default:
					$value = !is_array($value) ? $value : join(', ', $value);
			}
		}
		$status = $isOn ? 'On' : 'Off';
		?>
		<tr<?php echo $isOn ? ' class="active"' : NULL; ?>>
			<td><?php echo $item[0]; ?></td>
			<td><?php echo $value; ?></td>
			<td class="hh-status hh-status-<?php echo $isOn ? 'on' : 'off'; ?>"><span><?php echo $status; ?></span></td>
			<td><a href="<?php echo $_SERVER['PHP_SELF']; ?>?page=http-headers&header=<?php 
				echo $item[1]; ?>">Edit</a></td>
		</tr>
		<?php
	}
	?>
	</tbody>
</table>