<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
	if (isset($_POST['do']))
	{
		switch ($_POST['do'])
		{
			case 'inspect':
				
			    include '../../../../wp-load.php';
			    load_plugin_textdomain('http-headers', false, basename(dirname(dirname(__FILE__))) . '/languages');
			    
				if (!(isset($_POST['url']) && preg_match('|^https?://|', $_POST['url'])))
				{
					?>
					<section class="hh-panel">
						<h3><span class="hh-highlight"><?php _e('URL malformed', 'http-headers'); ?></span></h3>
					</section>
					<?php
					exit;
				}
				
				include 'includes/http.class.php';
				include 'includes/config.inc.php';
				$http = new Http();
				
				if (isset($_POST['authentication'], $_POST['auth_type'], $_POST['username'], $_POST['password'])
					&& in_array($_POST['auth_type'], array('basic', 'digest', 'gss', 'ntlm'))
					&& !empty($_POST['username'])
					&& !empty($_POST['password'])
				)
				{
					$http->setAuthType($_POST['auth_type']);
					$http->setPassword($_POST['password']);
					$http->setUsername($_POST['username']);
				}
				
				$http->request($_POST['url']);
				$responseHeaders = $http->getResponseHeaders();
				$status = $http->getHttpCode();
				$error = $http->getError();
				if ($status !== 200)
				{
					?>
					<section class="hh-panel">
						<h3><span class="hh-highlight"><?php _e('HTTP Status', 'http-headers'); ?>: <?php echo $status; ?></span></h3>
						<p><?php 
						switch ($status)
						{
							case 400:
								echo 'Bad Request';
								break;
							case 401:
								echo 'Unauthorized';
								break;
							case 403:
								echo 'Forbidden';
								break;
							case 404:
								echo 'Not Found';
								break;
							case 405:
								echo 'Method Not Allowed';
								break;
							default:
						}
						?></p>
					</section>
					<?php
					exit;
				}
				?>
				<section class="hh-panel">
					<h3><span class="hh-highlight"><?php _e('Response headers', 'http-headers'); ?></span></h3>
					<table class="hh-results">
						<thead>
							<tr>
								<th style="width: 30%"><?php _e('Header', 'http-headers'); ?></th>
								<th><?php _e('Value', 'http-headers'); ?></th>
							</tr>
						</thead>
						<tbody>
						<?php 
						$reportOnly = array('content-security-policy-report-only', 'public-key-pins-report-only');
						foreach ($responseHeaders as $k => $v)
						{
							$k = strtolower($k);
							$found = in_array($k, $reportOnly);
							?>
							<tr<?php echo array_key_exists($k, $headers) || $found ? ' class="hh-found"' : NULL; ?>>
								<td><?php echo htmlspecialchars($k); ?></td>
								<td><?php echo htmlspecialchars($v); ?></td>
							</tr>
							<?php
						}
						?>
						</tbody>
					</table>
				</section>
				<?php
				$special = array('content-security-policy', 'public-key-pins');
				$exclude = array('custom-headers', 'cookie-security', 'x-powered-by');
				$missing = array();
				foreach ($headers as $k => $v)
				{
					if (!array_key_exists($k, $responseHeaders)
					    && !in_array($k, $exclude)
					    && !(in_array($k, $special) && array_key_exists($k . '-report-only', $responseHeaders) ))
					{
						$missing[$k] = @$categories[$v[2]];
					}
				}
				
				if (!empty($missing))
				{
					asort($missing);
					?>
					<section class="hh-panel">
						<h3><span class="hh-highlight"><?php _e('Missing headers', 'http-headers'); ?></span></h3>
						<table class="hh-results">
							<thead>
								<tr>
									<th style="width: 30%"><?php _e('Header', 'http-headers'); ?></th>
									<th><?php _e('Category', 'http-headers'); ?></th>
								</tr>
							</thead>
							<tbody>
							<?php
							foreach ($missing as $k => $v)
							{
								?>
								<tr>
									<td><a href="<?php echo get_admin_url(); ?>options-general.php?page=http-headers&amp;header=<?php echo htmlspecialchars($k); ?>"><?php echo $k; ?></a></td>
									<td><?php echo $v; ?></td>
								</tr>
								<?php
							}
							?>
							</tbody>
						</table>
					</section>
					<?php
				}
				break;
            case 'import':
                include '../../../../wp-load.php';
                
                if (!(isset($_FILES['file']['tmp_name'])
                    && is_uploaded_file($_FILES['file']['tmp_name'])
                    && $_FILES['file']['error'] == UPLOAD_ERR_OK
                )) {
                    wp_redirect(sprintf("%soptions-general.php?page=http-headers&tab=advanced&status=ERR", get_admin_url()));
                    exit;
                }                

                $string = @file_get_contents($_FILES['file']['tmp_name']);
                if ($string === false) {
                    wp_redirect(sprintf("%soptions-general.php?page=http-headers&tab=advanced&status=ERR", get_admin_url()));
                    exit;
                }

                $arr = preg_split('/;(\s+)?\n/', $string);
                foreach ($arr as $statement) {
                    $wpdb->query($statement);
                }
                
                wp_redirect(sprintf("%soptions-general.php?page=http-headers&tab=advanced&status=OK", get_admin_url()));
                exit;
                
                break;
            case 'export':
                include '../../../../wp-load.php';
                include 'includes/config.inc.php';
                $statement = sprintf("SELECT * FROM wp_options WHERE option_name IN ('%s');", join("','", $options));
                $results = $wpdb->get_results($statement, ARRAY_A);
                $sql = array();
                foreach ($results as $item)
                {
                    $value = str_replace("'", "''", $item['option_value']);
                    $query = array();
                    $query[] = "INSERT INTO wp_options (option_id, option_name, option_value, autoload)";
                    $query[] = sprintf("VALUES (NULL, '%s', '%s', '%s')", $item['option_name'], $value, $item['autoload']);
                    $query[] = sprintf("ON DUPLICATE KEY UPDATE option_value = '%s', autoload = '%s';", $value, $item['autoload']);
                    $sql[] = join("\n", $query);
                }
                
                $sql = join("\n\n", $sql);
                $length = function_exists('mb_strlen') ? mb_strlen($sql) : strlen($sql);
                $name = sprintf('WP-HTTP-Headers-%u.sql', time());
                
                # Send headers
                header('Pragma: public');
                header('Expires: 0');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Cache-Control: private', false);
                header('Content-Transfer-Encoding: binary');
                header('Content-Disposition: attachment; filename="'.$name.'";');
                header('Content-Type: application/sql');
                header('Content-Length: ' . $length);
                
                echo $sql;
                exit;
                
                break;
		}
	}
}