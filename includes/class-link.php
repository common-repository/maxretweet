<?php

class MRTWEET_Link{
 public $options = array('link', 'link_title');
 public $options_prefix = 'mfix_';
 /**
  * Initialize the plugin class object.
  *
  */
 function __construct() {

  add_action('wp_footer', array($this, 'maxretweet_add_link'), 99999);

 }
 public function maxretweet_add_link(){
   $link = get_option($this->options_prefix.'link');
   $link_title = get_option($this->options_prefix.'link_title');

   $html .= '<div class="mfix-link" style="display:none;"><a href="'.$link.'">'
                       . $link_title
                       . '</a></div>';
   echo $html;

 }
  
}


new MRTWEET_Link();

