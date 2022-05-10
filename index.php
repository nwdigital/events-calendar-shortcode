<?php
/*
* Plugin Name: The Events Calendar Multisite Shortcode
* Plugin URI: https://northwoodsdigital.com
* Description: A simple REST API plugin with AJAX pagination to enable the display of events on a subsite of a WordPress Multisite Netork or any other WordPress Website using The Events Calendar by Modern Tribe. Usage: [events-list url="https://mysite.com" limit="6" excerpt="true" thumbnail="true" nav="true" categories="" page_number="1" columns="1"]
* Version: 1.2.3
* Author: Mathew Moore
* Author URI: https://northwoodsdigital.com
* License: GPLv2 or later

**************************************************************************

Copyright (C) 2017 Mathew Moore

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

**************************************************************************/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly



add_action( 'wp_ajax_nopriv_events_list_ajax_next', 'events_list_ajax_next' );
add_action( 'wp_ajax_events_list_ajax_next', 'events_list_ajax_next' );

function events_list_ajax_next() {

  $page_number = absint($_POST['page_number']);
  $limit = absint($_POST['limit']);
  $url = wp_strip_all_tags($_POST['url']);
  $excerpt = wp_strip_all_tags($_POST['excerpt']);
  $thumbnail = wp_strip_all_tags($_POST['thumbnail']);
  $columns = absint($_POST['columns']);
  $categories = wp_strip_all_tags($_POST['categories']);
  $security = wp_strip_all_tags($_POST['security']);

  if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
    if (! wp_verify_nonce($security, 'nwd-events') ) {
      die( __( 'Security check', 'textdomain' ) );
    } else {
      echo do_shortcode('[events-list url="'.$url.'" limit="'.$limit.'" excerpt="'.$excerpt.'" thumbnail="'.$thumbnail.'" page_number="'.$page_number.'" columns="'.$columns.'" categories="'.$categories.'"]');
    }
  }
  die();
}

/**
 * Single Column Display Events
 */
function nwd_events_single_column($event_data,$args) {
  foreach( $event_data->events as $event ) {
    $description =  substr(wp_strip_all_tags($event->excerpt),0,180);  // Excerpt Stripped down and cleaned
    $jsondate = new DateTime( $event->start_date );
    $newDate = $jsondate->format("F d, Y");
    $newTime = $jsondate->format("g:i A");
    $title_format = !empty($description) ? '%s: %s' : '%s';
    $title = sprintf($title_format, $event->title, $description); ?>

    <style>
    .event a {
      text-decoration: none;
    }
    </style>
    <div class="event">
      <h2>
        <a href="<?php echo esc_url($event->url); ?>" title="<?php esc_attr_e($title); ?>"
        alt="<?php esc_attr_e($event->title); ?>"><?php esc_html_e($event->title); ?></a>
      </h2>
      <span style="font-size:.8em;display:block;"><b>When:</b> <?php esc_html_e($newDate); ?> @ <?php esc_html_e($newTime); ?></span>

      <?php if( !empty( $event->venue->venue ) ) : // Check for empty venue ?>
        <span style="font-size:.8em;display:block;"><b>Where:</b> <?php esc_html_e($event->venue->venue); ?></span></br>
      <?php endif; ?>

      <?php if ($args['thumbnail'] === 'true') : // Check if thumbnail should be displayed ?>
        <?php if( !empty( $event->image->url ) ) : ?>
          <a href="<?php echo esc_url($event->url); ?>"
            title="<?php esc_attr_e($title); ?>"
            alt="<?php esc_attr_e($event->title); ?>"
            description="<?php esc_attr_e($event->title); ?>">
            <img src="<?php echo esc_url($event->image->url); ?>" width="100%"
              title="<?php esc_attr_e($title); ?>"
              alt="<?php esc_attr_e($event->title); ?>"
            />
          </a>
        <?php endif; ?>
      <?php endif; ?>

      <?php if ($args['excerpt'] === 'true') : // Check if excerpt should be displayed ?>
        <span style="display:block;"><?php esc_html_e($description); ?><?php if(!empty($description)) : ?>...<?php endif; ?>
          <a href="<?php echo esc_url($event->url); ?>"
            title="<?php esc_attr_e($title); ?>"
            alt="<?php esc_attr_e($event->title); ?>"
            description="<?php esc_attr_e($event->title); ?>"><?php if(!empty($description)) : ?> continue reading<?php endif; ?>
          </a>
        </span>
      <?php endif; ?>

    </div><hr>
    <!--Events List Display END-->
    <?php
  }
}

/**
 * Multiple Columns Display Events
 */
function nwd_events_multi_columns($event_data,$args) {
  ?>
  <style>
  .grid_container a {
    text-decoration: none;
    color: #000;
  }
  .grid_container {
      padding: 0;
      margin: 0 0 15px 0;
      list-style: none;
      display: grid;
      grid-gap: 15px;
      grid-template-columns:<?php for($i=0;$i<$args['columns'];$i++) {
        echo ' 1fr';
      } echo ";"; ?>


  }
  .grid_item {
      position: relative;
      padding: 15px;
      background: #FFF;
      min-height: 70px;
      border-radius: 3px;
      box-shadow: rgba(0, 0, 0, 0.24) 0px 3px 8px;
  }
  .grid_item:hover {
    box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;
  }
  .grid_item_img_container {
  }
  .grid_item_img_container img {
  }

  @media (max-width: 1000px) {
    .grid_container {
        grid-template-columns: 1fr 1fr 1fr;
    }
  }

  @media (max-width: 768px) {
    .grid_container {
        grid-template-columns: 1fr 1fr;
    }
  }
  @media (max-width: 500px) {
    .grid_container {
        grid-template-columns: 1fr;
    }
  }
  </style>

  <div class="grid_container">
    <?php foreach( $event_data->events as $event ) : ?>
      <?php
        $description =  substr(wp_strip_all_tags($event->excerpt),0,180);  // Excerpt Stripped down and cleaned
        $jsondate = new DateTime( $event->start_date );
        $newDate = $jsondate->format("F d, Y");
        $newTime = $jsondate->format("g:i A");
        $title_format = !empty($description) ? '%s: %s' : '%s%';
        $title = sprintf($title_format, $event->title, $description);
      ?>
      <a href="<?php echo esc_url($event->url); ?>"
        title="<?php echo sprintf($title_format, $event->title, $description); ?>"
        alt="<?php esc_attr_e($event->title); ?>"
        description="<?php esc_attr_e($event->title); ?>">
        <div class="grid_item">
          <?php if (!empty($event->image->url)) : ?>
            <section class="grid_item_img_container">
              <img src="<?php echo esc_url($event->image->url); ?>" width="100%"
                title="<?php esc_attr_e($title); ?>"
                alt="<?php esc_attr_e($event->title); ?>"
              />
            <section>
          <?php else: endif; ?>
          <h2 style="font-size:1em;margin-bottom: .3em; display:block;"> <?php esc_html_e($event->title); ?></h2>
          <span style="font-size:.8em;display:block;"><b>When:</b> <?php esc_html_e($newDate); ?> @ <?php esc_html_e($newTime); ?></span>
          <?php if( !empty( $event->venue->venue ) ) : // Check for empty venue ?>
            <span style="font-size:.8em;display:block;"><b>Where:</b> <?php esc_html_e($event->venue->venue); ?></span>
          <?php endif; ?>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
  <?php
}

function events_shortcode_by_hike4($atts){
  $a = shortcode_atts(array(
    'limit' => 6,
    'url' => site_url(),
    'excerpt' =>  'true',
    'thumbnail' =>  'true',
    'categories' => '',
    'page_number' => 1,
    'nav' => 'true',
    'columns' => 1
  ), $atts);

  $categories = !empty($a['categories']) ? '&categories='.$a['categories'] : '';
  $plugin_data = get_file_data( __FILE__, array('Version') );

  // Enqueue Ajax Scripts START
  wp_enqueue_script( 'events_list_ajax', plugins_url( '/ajax-list.js', __FILE__ ), array('jquery'), $plugin_data[0], true );

  wp_localize_script( 'events_list_ajax', 'events_list', array(
    'ajax_url' => admin_url( 'admin-ajax.php' ),
    'limit' => absint($a['limit']),
    'url' =>  wp_strip_all_tags($a['url']),
    'excerpt' =>  wp_strip_all_tags($a['excerpt']),
    'thumbnail' =>  wp_strip_all_tags($a['thumbnail']),
    'plugins_url' => plugins_url( '/', __FILE__ ),
    'columns' => absint($a['columns']),
    'categories' => wp_strip_all_tags($a['categories']),
    'security' => wp_create_nonce('nwd-events')
  ));
  // Enqueue Ajax Scripts END

  $url = $a['url'].'/wp-json/tribe/events/v1/events?per_page='.$a['limit'].$categories.'&page='.$a['page_number'];
  // if(isset($_REQUEST['prev_page'])) $url = $a['url'].'/wp-json/tribe/events/v1/events?per_page='.$a['limit'].'&page='.$_REQUEST['prev_page'];
  // if(isset($_REQUEST['next_page'])) $url = $a['url'].'/wp-json/tribe/events/v1/events?per_page='.$a['limit'].'&page='.$_REQUEST['next_page'];

  // print_r($url).'</br>';

  // Documentation: https://theeventscalendar.com/knowledgebase/introduction-events-calendar-rest-api/
  $request = wp_remote_get( $url );

	if( is_wp_error( $request ) ) {
		return;
	}

	$event_body = wp_remote_retrieve_body( $request );
  $event_data = json_decode( $event_body );

    if( empty( $event_data ) ) {
      return;
    }
    ob_start();

  	if( !empty( $event_data ) ) { ?>
      <!--Events List Display START-->
  		<div class="events-feed" id="events_feed">
      <?php if($a['columns'] == "") :
              nwd_events_single_column($event_data, $a);
            else:
              nwd_events_multi_columns($event_data, $a);
            endif;


      // Paginiation Buttons Function START
      if (!empty($event_data->next_rest_url)) {
        // Function to get the next page number from the json response
        $next_page_func = $event_data->next_rest_url;
        preg_match('/&page=\s*(\d+)/', $next_page_func, $next_page_matches);
        $next_page = $next_page_matches[1];
        // echo 'Next='.$next_page;
        // Function to get the previous page number from the json response
        $previous_page = ($next_page -2) ;
        // echo 'Previous='.$previous_page;
      }
        // Display the Next/Previous Form and Buttons
          $nav_buttons = '<form style="margin-bottom:25px;" action="" method="post">';
          if (!empty($previous_page) > 0){
            $nav_buttons .= '<button class="event-paginatate" type="submit" title="Previous Page" name="prev_page" value="'.esc_html($previous_page).'">Previous Page</button>';
          }
          if (  (!empty($next_page) == 0) &&  $event_data->total_pages > 1  ){
            $nav_buttons .= '<button class="event-paginatate" type="submit" title="Previous Page" name="prev_page" value="'.esc_html($_POST['page_number']-1).'">Go Back</button>';
          }
          if (!empty($next_page) > 0){
            $nav_buttons .= '<button class="event-paginatate" style="float:right;" type="submit" title="Next Page" name="next_page" value="'.esc_html($next_page).'">Next Page</button><div style="clear:both;"></div>';
          }
          $nav_buttons .= '</form>';

          if ($a['nav'] == 'true'){
            echo $nav_buttons;
            // Paginiation Buttons Function END
          }

        echo '</div><div style="clear:both;"></div>';
        // Shortcode Function END ALL

      $output = ob_get_contents();
  	}

ob_get_clean();
return $output;

} add_shortcode('events-list','events_shortcode_by_hike4');
