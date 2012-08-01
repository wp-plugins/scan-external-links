<?php
if(!current_user_can("administrator"))
{
	die("Accessed Denied");
        exit;
}
$msg = '';
if(isset($_REQUEST['submit']))
{
	$exclude_urls = '';
	if(isset($_REQUEST['sel_exclude_urls']))
		$exclude_urls = $_REQUEST['sel_exclude_urls'];
	
        $custom_fields = '';
	if(isset($_REQUEST['sel_custom_fields']))
		$custom_fields = $_REQUEST['sel_custom_fields'];
	update_option('sel_exclude_urls', $exclude_urls);	
	update_option('sel_custom_fields', $custom_fields);	
	$msg = 'Successfully saved the options';	
}
else
{
	$exclude_urls = get_option('sel_exclude_urls');	
	$custom_fields = get_option('sel_custom_fields');	
}



?>
<div class="wrap">
<h2>Search External Links Options</h2>
<div><br/><h3><?php echo $msg; ?></h3><br/></div>
<form action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="post">

<table class="form-table">
	<tr>
		<th scope="row">Exclude Words</th>
		<td>
                    <textarea rows="5" cols="60" name="sel_exclude_urls"><?php echo $exclude_urls; ?></textarea>
                </td>
		<td>This will exclude the urls that contain the matching words. Write each matching string on a new line.</td>
	</tr>
    <tr>
		<th scope="row">Custom Fields to Scan</th>
		<td>
                    <textarea rows="5" cols="60" name="sel_custom_fields"><?php echo $custom_fields; ?></textarea>
                </td>
		<td>The fields which will be scanned for external urls.Write each on a new line.</td>
	</tr>
</table>    

<input type="submit" name="submit" class="button" value="Save Changes" />
</form>