<?php

/*
 * Scans posts for possible external links
 */

function scan_posts($options = array(), $exclude = array()) {
    global $wpdb;
    $default = array(
        'post_type' => 'any',
        'numberposts' => -1,
        'orderby' => 'post_title',
        'order' => 'ASC',
        'offset' => '0',
        'search' => '',
        'post_status' => 'publish'
    );


    $options = array_merge($default, $options);

    $query = "
        SELECT $wpdb->posts.* 
        FROM $wpdb->posts
        WHERE $wpdb->posts.post_content LIKE '%" . $options['search'] . "%'";

    if ($options['post_type'] != 'any'):
        $query .= " AND $wpdb->posts.post_type = '" . $options['post_type'] . "'";
    endif;
    if ($options['post_status'] != 'any'):
        $query .= " AND $wpdb->posts.post_status = '" . $options['post_status'] . "'";
    endif;
    $query .= " ORDER BY $wpdb->posts." . $options['orderby'] . " " . $options['order'];
    if ($options['numberposts'] != '-1'):
        $query .= " LIMIT " . $options['numberposts'] . " OFFSET " . $options['offset'];
    endif;
    

    $posts = $wpdb->get_results($query, OBJECT);
    
    $links = array();
    /* Turn Off some weired error for parsing...obviously content is not gonna contain real data */
    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    //ob_implicit_flush(true);
    //ob_end_flush();

    ini_set('max_execution_time', '0'); // 0 = no limit.
    $wp_url = get_bloginfo('wpurl');

    foreach ($posts as $post):
        if (empty($post->post_content)):
            continue;
        endif;
        $dom->loadHTML($post->post_content);
        $anchors = $dom->getElementsByTagName("a");

        foreach ($anchors as $anchor):

            if (!$anchor->hasAttribute('href')):
                continue;
            endif;
            $href = $anchor->getAttribute("href");

            if (substr($href, 0, 1) == '/' || substr($href, 0, 1) == '#' || substr($href, 0, 6) == 'mailto' || substr($href, 0, strlen($wp_url)) == $wp_url):
                continue;
            endif;

            if ($options['search'] != '' && strpos($href, $options['search']) === FALSE):
                continue;
            endif;
            $skip = false;
            foreach ($exclude as $ex):
                if ($ex != '' && strpos($href, $ex) !== FALSE):
                    $skip = true;
                    break;
                endif;
            endforeach;
            if ($skip):
                continue;
            endif;
            $post_categories = wp_get_post_categories($post->ID);
            $cats = array();

            foreach ($post_categories as $c) {
                $cat = get_category($c);

                $cats[] = array('name' => $cat->name,
                    'slug' => $cat->slug,
                    'id' => $cat->cat_ID);
            }

            $links[$post->ID][] = array(
                'url' => $href,
                'post_title' => $post->post_title,
                'post_type' => $post->post_type,
                'categories' => $cats
            );
            unset($cats);
        endforeach;
    endforeach;
    
    return $links;
}

function scan_custom_fields($fields = array(), $options = array()) {
    global $wpdb;
    $fields = "'" . implode("','", $fields) . "'";
    $default = array(
        'post_type' => 'any',
        'numberposts' => -1,
        'orderby' => 'post_title',
        'order' => 'ASC',
        'offset' => '0',
        'search' => '',
        'post_status' => 'publish'
    );


    $options = array_merge($default, $options);
    
    $query = "
        SELECT count(*) AS ResultCount 
        FROM $wpdb->posts, $wpdb->postmeta
        WHERE $wpdb->posts.ID = $wpdb->postmeta.post_id 
        AND $wpdb->postmeta.meta_key in ($fields)";
    
    if ($options['post_type'] != 'any'):
        $query .= " AND $wpdb->posts.post_type = '" . $options['post_type'] . "'";
    endif;
    if ($options['post_status'] != 'any'):
        $query .= " AND $wpdb->posts.post_status = '" . $options['post_status'] . "'";
    endif;
    $posts_count = $wpdb->get_results($query, OBJECT);
    
    $total = $posts_count[0]->ResultCount;
    
    $query = "
        SELECT * 
        FROM $wpdb->posts, $wpdb->postmeta
        WHERE $wpdb->posts.ID = $wpdb->postmeta.post_id 
        AND $wpdb->postmeta.meta_key in ($fields)";
    
    if ($options['post_type'] != 'any'):
        $query .= " AND $wpdb->posts.post_type = '" . $options['post_type'] . "'";
    endif;
    if ($options['post_status'] != 'any'):
        $query .= " AND $wpdb->posts.post_status = '" . $options['post_status'] . "'";
    endif;
    $query .= " ORDER BY $wpdb->posts." . $options['orderby'] . " " . $options['order'];
    if ($options['numberposts'] != '-1'):
        $query .= " LIMIT " . $options['numberposts'] . " OFFSET " . $options['offset'];
    endif;
    
    $posts = $wpdb->get_results($query, OBJECT);
    

    $links = array();
    /* Turn Off some weired error for parsing...obviously content is not gonna contain real pure html data */
    libxml_use_internal_errors(true);
    //ob_implicit_flush(true);
    //ob_end_flush();

    ini_set('max_execution_time', '0'); // 0 = no limit.
    $wp_url = get_bloginfo('wpurl');
    $p_cat = array(); // TOO STORE CATEGORIES FOR A POST
    foreach ($posts as $post):

        $href = $post->meta_value;

        if (substr($href, 0, 1) == '/' || substr($href, 0, 1) == '#' || substr($href, 0, 6) == 'mailto' || substr($href, 0, strlen($wp_url)) == $wp_url):
            continue;
        endif;

        if ($options['search'] != '' && strpos($href, $options['search']) === FALSE):
            continue;
        endif;
        $cats = array();
        if (!array_key_exists($post->ID, $p_cat)):
            $post_categories = wp_get_post_categories($post->ID);
            foreach ($post_categories as $c) {
                $cat = get_category($c);
                $cats[] = array('name' => $cat->name,
                    'slug' => $cat->slug,
                    'id' => $cat->cat_ID);
            }
            $p_cat[$post->ID] = $cats;
        else:
            $cats = $p_cat[$post->ID];
        endif;


        $links[$post->ID][] = array(
            'url' => $href,
            'post_title' => $post->post_title,
            'post_type' => $post->post_type,
            'categories' => $cats
        );
        unset($cats);
    endforeach;
    return array('links'=>$links, 'total'=>$total);
}

function get_pages_count($total_count, $per_page_count)
{
    $pages = ceil($total_count / $per_page_count);
    return $pages;
}
?>
