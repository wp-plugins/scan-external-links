<?php
/*
Plugin Name: Scan External Links
Plugin URI: http://www.geektrick.com
Description: Scans all the external links in a wordpress site and displays it to administrators.
Version: 1.0
Author: Anshul Sojatia
Author URI: http://www.geektrick.com/author/anshulsojatia
*/
/*
Administration
*/
add_action("admin_menu","wp_sel_menu");
function wp_sel_menu()
{
	if(function_exists("add_menu_page")):
		add_menu_page("Scan External Links","SEL","administrator","scan-external-links\sel.php");
	endif;
	
	if(function_exists("add_submenu_page")):		
		add_submenu_page("scan-external-links\sel.php","Scan External Links","Posts","administrator","scan-external-links\posts.php","");
		add_submenu_page("scan-external-links\sel.php","Scan External Links","Custom Fields","administrator","scan-external-links\customfields.php","");
	endif;
}

?>
