<div class="wrap">
	<h1>HTTP Headers</h1>
	<p>To see how to configure the plugin read the <a href="https://zinoui.com/blog/http-headers-for-wordpress" target="_blank" title="HTTP Headers">HTTP Headers</a>' getting started tutorial.</p>
	<?php 
	if (isset($_GET['header']) && !empty($_GET['header']))
	{
		include dirname(__FILE__) . '/header.php';
	} else {
		include dirname(__FILE__) . '/list.php';
	}
	?>
</div>