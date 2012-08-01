<?php
if (!current_user_can("manage_options")) {
    die("Accessed Denied");
}
global $sel_data;
global $title;
global $pagination;
wp_enqueue_script("jquery");
?>
<div class="wrap">
    <h2><?php echo $title; ?></h2>
</div><!--.wrap-->
<div id="" style="float:left;margin-bottom: 10px;width:100%;">
    Filter Results: <input type="text" id="txtSearch"></input><input type="button" id="btn_filter" class="button" value="Filter" />
    <br/>
    <?php if($pagination): 
        $pg = 1;
        if(isset($_REQUEST['pageno'])):
            $pg = $_REQUEST['pageno'];
        endif;
        global $total;
        global $max_per_page;
        $all_page_count = get_pages_count($total, $max_per_page);
        
    ?>
    Jump to page: 
    <select id="select_page">
        <?php for($page_index=1;$page_index<$all_page_count+1; $page_index++): ?>
            <option value="<?php echo $page_index ?>" <?php echo $pg == $page_index ? "selected" :"" ?>><?php echo $page_index; ?></option>
        <?php endfor; ?>
    </select>
    <script type="text/javascript">
        jQuery(document).ready(function(){           
           jQuery("#select_page").change(function(){               
               window.location.href ="admin.php?page=scan-external-links/customfields.php&pageno=" + jQuery(this).val();
           });
        });
    </script>
    <?php endif;?>
</div>

<script type="text/javascript">
    jQuery(document).ready(function(){
       
        jQuery("#txtSearch").keypress(function(e){
                     var code = (e.keyCode ? e.keyCode : e.which);
                        if(code == 13) { //Enter keycode
                            jQuery("#btn_filter").trigger("click");
                        }       
        });
        jQuery("#btn_filter").click(function(){           
            jQuery(".widefat tbody tr").css("display","");                
            jQuery(".widefat tbody tr").each(function(){
                var title = jQuery(this).attr("title").toLowerCase();
                var url = jQuery(this).attr("url").toLowerCase();
                var search = jQuery("#txtSearch").val();
                if(title.indexOf(search) == -1 && url.indexOf(search) == -1)
                {
                    jQuery(this).css("display", "none");
                }           
            });
        }) ;
    });
</script>
<?php if($pagination): ?>
<h2>Showing page <?php echo $pg ?> of total <?php echo $all_page_count ?> page(s) @ <?php echo $max_per_page ?> records per page.</h2>
<?php
else:
?>
<h2>Showing links for <?php echo count($sel_data) ?> post(s)</h2>
<?php
endif; ?>

<table class="widefat">
    <thead>
        <tr>
            <th width="10%">ID</th>
            <th width="10%">Action</th>
            <th width="50%">Post Title & External Link</th>            
            <th width="30%">Categories</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($sel_data) == 0): ?>
            <tr>
                <td colspan="4">
                    <i>No external links found</i>         
                </td>          
            </tr>
            <?php
        else:
            
            foreach ($sel_data as $id => $data):
                foreach ($data as $d):
                    if (empty($d['post_title'])):
                        $d['post_title'] = "(No Title)";
                    endif;
                    ?>
                    <tr id="tr_<?php echo $id ?>" url="<?php echo $d['url'] ?>" title="<?php echo addslashes($d['post_title']) ?>">
                        <td width="10%"><?php echo $id ?> </td>
                        <td width="10%"><a href="<?php bloginfo('wpurl') ?>/wp-admin/post.php?post=<?php echo $id ?>&action=edit" target="_blank">Edit</a>&middot;<a href="<?php echo get_permalink($id) ?>" target="_blank">View</a></td>
                        <td width="50%"><b><?php echo $d['post_title']; ?></b><br/>
                            <a href="<?php echo $d['url'] ?>" target="_blank"><?php echo substr($d['url'], 0, 100);
            if (strlen($d['url']) > 100): echo "...";
            endif; ?></a></td>
                        <td width="30%">
                            <ul>
                            <?php foreach($d['categories'] as $cat):
                            ?>
                                <li><a target="_blank" href="<?php echo get_category_link($cat['id']) ?>"><?php echo $cat['name'] ?></a></li>
                            <?php
                                  endforeach;
                            ?>
                            </ul>
                        </td>
                    </tr>
                    <?php
                endforeach;
            endforeach;
        endif;
        ?>
    </tbody>
</table>