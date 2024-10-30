<?php

class MRTWEET_Share{

 /**
  * Initialize the plugin class object.
  *
  */
 function __construct() {
    
  add_action('admin_init', array($this, 'maxretweet_meta_init'));
  add_filter( 'the_content', array($this, 'maxretweet_show_share_buttons'));
  add_action('wp_head', array($this, 'maxretweet_add_share_style'), 9);
 }

 public function maxretweet_meta_init()
 {
     $options = get_option('maxretweet_options');

     if(!isset($options['posttypes']) || empty($options['posttypes']))
      return;

     $theme_post_types = $options['posttypes'];
     // add a meta box for each of  posts and pages
     foreach ($theme_post_types as $type) 
     {
         add_meta_box('maxretweet_all_meta', 'MaxRetweet Meta Box', array($this, 'maxretweet_meta_setup'), $type, 'normal', 'high');
     }
      
     // add a callback function to save any data a user enters in
     add_action('save_post', array($this, 'maxretweet_meta_save'));
 }
  
 public function maxretweet_meta_setup()
 {
     global $post;

     $_maxretweet_meta = get_post_meta($post->ID,'_maxretweet_meta',TRUE);

     ?>
     <div>
       <ul id="maxretweet_custom_messages">
        <?php if(isset($_maxretweet_meta['message'])) { ?>
         <?php foreach($_maxretweet_meta['message'] as $_maxretweet_meta_item){ ?>
          <li>
           <input type="text" name="_maxretweet_meta[message][]" value="<?php if(!empty($_maxretweet_meta_item)) echo $_maxretweet_meta_item; ?>" />
           <a href="javascript:" class="maxretweet_remove_message">Remove</a>
          </li>
         <?php } ?>
        <?php }else{ ?>
          <li>
           <input type="text" name="_maxretweet_meta[message][]" value="" />
           <a href="javascript:" class="maxretweet_remove_message">Remove</a>
          </li>
        <?php } ?>
       </ul>
       <div class="maxretweet_addlink">
        <a href="javascript:" id="maxretweet_addbutton">Add another</a>
        <input type="submit" class="button button-primary button-large maxretweet_metabox_submit" value="Update" />
       </div>
     </div>
<style>
#maxretweet_all_meta{
  padding:20px 0px;
}
#maxretweet_custom_messages input{
  width:80%;
  height:40px;
}
#maxretweet_custom_messages .maxretweet_remove_message{
  margin-left:20px;
}
#maxretweet_custom_messages .maxretweet_remove_message{
  padding:10px;
  background-color:#666;
  text-decoration:none;
  color:#fff;
}
#maxretweet_addbutton{
  background-color:#666;
  float:left;
  margin-right:10px;
  padding:0px 10px;
  height:30px;
  color:#fff;
  text-decoration:none;
  line-height: 29px;
  border-radius: 3px;
}
#maxretweet_custom_messages .maxretweet_remove_message:hover , #maxretweet_addbutton:hover{
  text-shadow:0px 0px 2px #000;
}
.maxretweet_addlink{
  margin-top:25px;
}
</style>
<script>
jQuery(document).ready(function() {
		jQuery("#maxretweet_addbutton").click(function() {
    jQuery("#maxretweet_custom_messages").append('<li><input type="text" name="_maxretweet_meta[message][]" value="" /><a href="javascript:" class="maxretweet_remove_message">Remove</a></li>');
  });
		jQuery(".maxretweet_remove_message").click(function() {
    jQuery(this).parents('li').remove();
   
  });
  jQuery('.maxretweet_metabox_submit').click(function(e) {
      e.preventDefault();
      jQuery('#publish').click();
  });
});
</script>  

<?php
 }
 
 public function maxretweet_meta_save($post_id) 
 {
     global $post;
     
     if ( empty( $post_id ) || empty( $post ) || empty( $_POST ) ) return;
     if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
     if ( is_int( wp_is_post_revision( $post ) ) ) return;
     if ( is_int( wp_is_post_autosave( $post ) ) ) return;
 
 
     // check user permissions
     if ($_POST['post_type'] == 'page') {
         if (!current_user_can('edit_page', $post_id)) return $post_id;
     }else{
         if (!current_user_can('edit_post', $post_id)) return $post_id;
     }

     // authentication passed, save data

     $current_data = get_post_meta($post_id, '_maxretweet_meta', TRUE);  
   
     $new_data = $_POST['_maxretweet_meta'];
     $this->maxretweet_meta_clean($new_data);
      
     $sanitized_data = array();
     if(is_array($new_data)){
      foreach($new_data as $key=>$data){
       if(is_array($data)) {
        $sanitized_row = array();
        foreach($data as $row){
         $sanitized_row[] = sanitize_text_field($row);
        }
        $sanitized_data[$key] = $sanitized_row;
       }else{
        $sanitized_data[$key] = sanitize_text_field($data);
       }
      }
     }
     update_post_meta($post_id, '_maxretweet_meta', $sanitized_data);

     return $post_id;
 }

 private function get_custom_titles($post_id){
   $sbs_data = get_post_meta($post_id, '_maxretweet_meta', TRUE);  
   if(isset($sbs_data['message']))
    return $sbs_data['message'];
   return array();
 }
 public function maxretweet_show_share_buttons($content){ ?>

 <?php
	   
				
	global $post;
   $messages = $this->get_custom_titles($post->ID);

   if(!$this->check_front_posttype() || count($messages) == 0) return $content;


   $url = get_permalink($post->ID);
 
   $html = '<div class="maxretweet-container">';
   $html .= '<div class="maxretweet-tweet-reweet"><div class="maxtweet-img"><img src="'.maxretweet_plugin_url('/images/maxretweet_logo.png').'" alt="maxretweet-Logo"/></div><div class="maxretweet-what-this"><span>What’s this?</span> <p class="maxretweet-popup"><span>When you see MaxReTweet on a story, that means multiple custom tweets are setup for the post. So choose your favorite and share!</span></p></div></div>';
   $html .= '<ul class="maxretweet-list">';

   foreach($messages as $message){
      $html .= '<li>'
                 .'<span class="maxretweet-message">'.$message .'</span>'
                 .'<a target="_blank" class="twitter-share-button" data-text="'. $message .'" href="http://twitter.com/share?">' 
                  . __("Tweet")
                  .'</a>'
                .'</li>';
   }
   $html .= '</ul>';
   
   if($this->check_if_enabled()) 
     $html .= '<div class="maxretweet-powered-by">Powered by <a href="http://www.makeitrainusa.com/"> Make It Rain </a></div>';           
   $html .= "<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>";
   $html .= '</div>';
   return $content. $html;
 }

 function maxretweet_add_share_style()
 {
 
   if(!$this->check_front_posttype()) return;

   ob_start();
?>
   <style>
    .maxretweet-container .maxretweet-list li {
      clear: both; 
      line-height: 50px;  
      
      list-style-type: none; 
      border-bottom: 1px solid #9b9b9b;
    } 
    .maxretweet-container .maxretweet-list li:first-child{
      border-top: 1px solid #9b9b9b;
    }
    .maxretweet-container .twitter-share-button{ 
      float: right; color:#414141; 
      text-decoration:none; 
      margin:15px 0; 
      width:80px !important;
    }
    .maxretweet-container .maxretweet-list span{ 
      font-size:13px; color:#5c7276;
    } 
    .maxretweet-container .maxretweet-list{ 
      margin:5px 0 0 0; 
    }
    .maxretweet-container .maxretweet-powered-by{
      float:right; 
      color:#9b9b9b;
      font-size:11px;
    }  
    .maxretweet-container .maxretweet-powered-by a{
      color:#00a3ea; 
      text-decoration:none; 
      cursor:pointer
    }
    .maxretweet-tweet-reweet .maxtweet-img{
       float:left;
       max-width: 120px;
    }
    .maxretweet-tweet-reweet .maxretweet-what-this{
      float:right;
      position:relative;
    } 
    .maxretweet-container .maxretweet-tweet-reweet{
     color:#9b9b9b; 
    } 
    .maxretweet-container .maxretweet-tweet-reweet strong{
      position:relative;
    } 
    
    .maxretweet-container .maxretweet-tweet-reweet .maxretweet-popup{ 
       display:none;  
       background: #fff; 
       border: 1px solid #a0c2c7;
       position: absolute;
       right: 0;
       top: 40px;
       width: 300px; 
       padding:10px; 
       border-radius:5px; color:#5c7276;
       margin-top: 5px;
    } 
    .maxretweet-container .maxretweet-tweet-reweet .maxretweet-what-this:hover > .maxretweet-popup{ 
      display:block;
    } 
    .maxretweet-container .maxretweet-message {
      display: inline-block; 
      width: 80%; 
      line-height:2;
    }
    .maxretweet-container .maxretweet-tweet-reweet .maxretweet-popup span{
      position:relative;
      display:block;
    }
   .maxretweet-popup span:after, .maxretweet-popup span:before{
     content: "";
     display: block;
     width:0;
     height:0;
     border-color: transparent transparent #a0c2c7 transparent;
     border-style: solid;
     border-width: 7px;
     right: 0px;
     position: absolute;
     top: -25px;
     z-index: 101;
   }
   .maxretweet-container .maxretweet-tweet-reweet .maxretweet-popup span:after{
     border-color: transparent transparent #fff transparent;  
     top: -23px;
     z-index: 102;
   }
   </style>
<?php
   $style = ob_get_contents();
   ob_end_clean();
   echo $style;
 }
 public function maxretweet_meta_clean(&$arr)
 {
     if (is_array($arr))
     {
         foreach ($arr as $i => $v)
         {
             if (is_array($arr[$i])) 
             {
                 $this->maxretweet_meta_clean($arr[$i]);
  
                 if (!count($arr[$i])) 
                 {
                     unset($arr[$i]);
                 }
             }
             else
             {
                 if (trim($arr[$i]) == '') 
                 {
                     unset($arr[$i]);
                 }
             }
         }
  
         if (!count($arr)) 
         {
             $arr = NULL;
         }
     }
 }
 
 function check_if_enabled(){
     $options = get_option('maxretweet_options');

     if(!isset($options['enable']) || empty($options['enable']))
      return false;
      
     return true;
 }
 function check_front_posttype(){
     global $post;
     
     $options = get_option('maxretweet_options');
     
     if(!isset($options['posttypes']) || empty($options['posttypes']))
      return false;
     
     $theme_post_types = $options['posttypes'];

     if(in_array($post->post_type, $theme_post_types)) {
     
       if($post->post_type == 'post'){
        //For posts show shares only on single page and not list page.
        if(is_single())return true;
        else return false;
       }
       return true;
     }
     return false;
 }
}


new MRTWEET_Share();

