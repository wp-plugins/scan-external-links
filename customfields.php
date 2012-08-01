<?php
include("functions.php");
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$c = explode("\n", get_option('sel_custom_fields'));
$max_per_page = 5000;
$current_page = isset($_REQUEST['pageno'])? $_REQUEST['pageno'] : 1;
$offset = ($current_page - 1) * $max_per_page;
$sel_data = scan_custom_fields($c, array('numberposts'=>$max_per_page, 'offset'=>$offset));

$total = $sel_data['total'];
$sel_data = $sel_data['links'];
$pagination = true;
$title = "External Links within Post Custom Fields";
include("display.php")
?>
