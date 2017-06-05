<?php 
$bools = array(0 => 'Off', 1 => 'On');
?>
<p><a href="<?php echo $_SERVER['PHP_SELF']; ?>?page=http-headers">&laquo; Back to list of headers</a></p>

<section class="hh-panel">
	<form method="post" action="options.php">
	    <table class="form-table hh-table">
			<tbody>
			<?php
			$header_file = sprintf('%s/%s.php', dirname(__FILE__), basename($_GET['header']));
			if (is_file($header_file))
			{
				include $header_file;
			}
			?>
			</tbody>
		</table>
		<?php submit_button(); ?>
	</form>
</section>