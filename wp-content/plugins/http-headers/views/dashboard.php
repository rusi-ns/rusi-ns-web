<?php 
include dirname(__FILE__) . '/includes/config.inc.php';
?>
<div class="hh-categories">
<?php
$tmp = array();
foreach ($headers as $item)
{
	if (!isset($tmp[$item[2]]))
	{
		$tmp[$item[2]] = array('total' => 0, 'on' => 0);
	}
	$tmp[$item[2]]['total'] += 1;
	if (get_option($item[1]) == 1)
	{
		$tmp[$item[2]]['on'] += 1;
	}
}
foreach ($categories as $key => $val)
{
	?>
	<a href="<?php echo get_admin_url(); ?>options-general.php?page=http-headers&amp;category=<?php echo $key; ?>" class="hh-category">
		<i></i>
		<span><?php echo $val[0]; ?></span>
		<strong><?php echo $val; ?></strong>(<?php printf('%u/%u', @$tmp[$key]['on'], @$tmp[$key]['total']); ?>)</a>
	<?php 
}
?>
</div>