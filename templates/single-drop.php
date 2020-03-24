<?php
do_action('drop_hit', get_the_ID());
$drop_url = get_post_meta(get_the_ID(), 'drop_preview_url', true);
/**
 * Set Base Path;
 */
echo '<base href="'.trailingslashit($drop_url).'" target="_blank">';

if (!empty($drop_url)) {
	$drop_path = str_replace(site_url('/'), ABSPATH, esc_url($drop_url));
	include($drop_path.'/index.html');
}
die();
