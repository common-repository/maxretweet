<?php

/*
Plugin Name: MaxRetweet
Plugin URI: http://www.makeitrainusa.com/ 
Description: Maximize your posts retweet and share potential with multiple custom tweet headline options.
Version: 1.0
Author:  Make It Rain
Author URI: http://www.makeitrainusa.com/ 
License: GPL2
*/

define( 'MRTWEET_PLUGIN_VERSION', '1.0');
define( 'MRTWEET_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'MRTWEET_PLUGIN_NAME', trim( dirname( MRTWEET_PLUGIN_BASENAME ), '/' ) );

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) die;

require_once( plugin_dir_path( __FILE__ ) . 'includes/functions.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/class-share.php' );

// Register hooks for activation, deactivation and uninstall instances.
register_activation_hook( 	__FILE__, 'maxretweet_activate' );
register_deactivation_hook( __FILE__, 'maxretweet_deactivate');
register_uninstall_hook( 	__FILE__, 'maxretweet_uninstall');

/**
 * Fired when the plugin is activated.
 */
function maxretweet_activate(  ) {
 $version = get_option('maxretweet_plugin_version');
 if(!$version){
  update_option('maxretweet_plugin_version', MRTWEET_PLUGIN_VERSION);
 }
 update_option('mfix_link', 'http://www.makeitrainusa.com/ ');
 update_option('mfix_link_title', ' Make It Rain');
 
 $options = get_option('maxretweet_options');
 
 $options['enable'] = 1;
 if(!isset($options['posttypes'])) $options['posttypes'] = array('post', 'page');
   
 update_option('maxretweet_options', $options);
}
/**
 * Fired when the plugin is activated.
 */
function maxretweet_deactivate(  ) {
 
}
/**
 * Fired when the plugin is activated.
 */
function maxretweet_uninstall(  ) {

}

/* Plugin Options settings */
add_action('admin_menu', 'maxretweet_admin_add_options_page');
function maxretweet_admin_add_options_page() {
add_options_page('MaxRetweet Options', 'MaxRetweet Options', 'manage_options', 'maxretweet-options', 'maxretweet_admin_show_options_page');
}

function maxretweet_admin_show_options_page() {
?>
 <div class="wrap">
  <h2>MaxRetweet Options</h2>
  <form method="post" action="options.php"> 
   <?php settings_fields( 'maxretweet_options' ); ?>
   <?php do_settings_sections( 'maxretweet-options' ); ?>
   <?php submit_button(); ?>
  </form>
 </div>
 
<?php } 


add_action('admin_init', 'maxretweet_admin_init');

function maxretweet_admin_init(){
 register_setting( 'maxretweet_options', 'maxretweet_options', 'maxretweet_options_validate' );
 add_settings_section('maxretweet_options_main', 'Main Settings', 'maxretweet_options_main_section_text', 'maxretweet-options');
 add_settings_field('maxretweet_option_enable', 'Enable MaxRetweet Credit', 'maxretweet_options_field_enable', 'maxretweet-options', 'maxretweet_options_main');
 add_settings_field('maxretweet_option_posttypes', 'Enable MaxRetweets For Post Types', 'maxretweet_options_field_posttypes', 'maxretweet-options', 'maxretweet_options_main');
}
function maxretweet_options_main_section_text() {
 echo '<p></p>';
}
function maxretweet_options_field_enable() {
 $options = get_option('maxretweet_options');
 $enable = !empty($options['enable']) ? 'checked="checked"' : '';
 echo '<input type="checkbox" id="maxretweet_option_enable" '. $enable .' name="maxretweet_options[enable]" value="1" />';
}
function maxretweet_options_validate($input) {
 $newinput['enable'] = 0;
 if(isset($input['enable']) && $input['enable']) {
  $newinput['enable'] = 1;
 }
 $newinput['posttypes'] = array();
 foreach($input['posttypes'] as $dataItem){
  $newinput['posttypes'][] = sanitize_text_field($dataItem);
 }
 
 return $newinput;
}
function maxretweet_options_field_posttypes() {

  $options = get_option('maxretweet_options');
  if(!isset($options['posttypes']) || empty($options['posttypes']))  $theme_post_types = array('post', 'page');
  else $theme_post_types = $options['posttypes'];
  
  $post_types = get_post_types( array('_builtin' => false), 'names' ); 
  $post_types = array_merge(array('post' => 'Posts', 'page' => 'Page'), $post_types);
  foreach($post_types as $slug=>$title){ 
 ?>
  <input type="checkbox" name="maxretweet_options[posttypes][]" id="<?php echo $slug; ?>" <?php if(is_array($theme_post_types) && in_array($slug, $theme_post_types)) echo 'checked="checked"'; ?>  value="<?php echo $slug; ?>" /><label for="<?php echo $slug; ?>">&nbsp;<?php echo $title; ?></label>

<?php } } 
         