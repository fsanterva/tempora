<?php
/**
 * Our custom dashboard page
 */

/** WordPress Administration Bootstrap */
require_once( ABSPATH . 'wp-load.php' );
require_once( ABSPATH . 'wp-admin/admin.php' );
require_once( ABSPATH . 'wp-admin/admin-header.php' );
?>
<div class="dashboard">
    <?php $url = "https://www.dilate.com.au/dashboard/dashboard.html";
    $dashboard = file_get_contents($url);
    echo empty($dashboard) ? "<h1><a href='http://help.dilate.com.au/'>help.dilate.com.au</h1>" : $dashboard;
    ?>
</div>
