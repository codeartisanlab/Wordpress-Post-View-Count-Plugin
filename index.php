<?php
/*
Plugin Name: Post View Count (CAL PVC)
Description: Count total views of post. You can control where you want to show total counts.
Author: CodeArtisanLab
Author URI: http://codeartisanlab.com
Version:1.0
Plugin URI:http://codeartisanlab.com
*/

// Specify Hooks/Filters
register_activation_hook(__FILE__, 'add_defaults_fn');
add_action('admin_init', 'cal_pvc_plugin_init');
add_action('admin_menu', 'cal_pvc_plugin_page_fun');

// Define default option settings
function add_defaults_fn() {
	$tmp = get_option('cal_pvc_plugin_options');
    if(($tmp['chkbox1']=='on')||(!is_array($tmp))) {
		$arr = array("show_everywhere" => "on", "only_single_page" => "");
		update_option('cal_pvc_plugin_options', $arr);
	}
}

// Register our settings. Add the settings section, and settings fields
function cal_pvc_plugin_init(){
	register_setting('cal_pvc_plugin_options', 'cal_pvc_plugin_options', 'cal_pvc_plugin_options_validate' );
	add_settings_section('cal_pvc_section', 'Display Settings', 'section_text_fn', __FILE__);
	add_settings_field('plugin_chk1', 'Archive', 'setting_chk1_fn', __FILE__, 'cal_pvc_section');
	add_settings_field('plugin_chk2', 'Detail', 'setting_chk2_fn', __FILE__, 'cal_pvc_section');
	add_settings_field('plugin_chk3', 'Home', 'setting_chk3_fn', __FILE__, 'cal_pvc_section');
}

// Add sub page to the Settings Menu
function cal_pvc_plugin_page_fun() {
	add_options_page('Post View Count', 'Post View Count', 'administrator', __FILE__, 'cal_pvc_options_page_fn');
}

// ************************************************************************************************************

function section_text_fn(){

}

// Callback functions

// CHECKBOX - Archive
function setting_chk1_fn() {
	$options = get_option('cal_pvc_plugin_options');
	if(isset($options['show_everywhere']) && $options['show_everywhere']=='on') { $checked = ' checked="checked" '; }
	echo "<input ".$checked." id='plugin_chk1' name='cal_pvc_plugin_options[show_everywhere]' type='checkbox' />";
	echo "<p>Archive pages include category, tag, author, date, custom post type, and custom taxonomy based archives.</p>";
}

// CHECKBOX - Single
function setting_chk2_fn() {
	$options = get_option('cal_pvc_plugin_options');
	if(isset($options['only_single_page']) && $options['only_single_page']=='on') { $checked = ' checked="checked" '; }
	echo "<input ".$checked." id='plugin_chk2' name='cal_pvc_plugin_options[only_single_page]' type='checkbox' />";
	echo "<p>Detail Page (For all post types)</p>";
}

// CHECKBOX - Home
function setting_chk3_fn() {
	$options = get_option('cal_pvc_plugin_options');
	if(isset($options['pvc_home']) && $options['pvc_home']=='on') { $checked = ' checked="checked" '; }
	echo "<input ".$checked." id='plugin_chk2' name='cal_pvc_plugin_options[pvc_home]' type='checkbox' />";
	echo "<p>For Home Page</p>";
}

// Display the admin options page
function cal_pvc_options_page_fn() {
?>
	<div class="wrap">
		<div class="icon32" id="icon-options-general"><br></div>
		<h2>Post Count Settings</h2>
		<form action="options.php" method="post">
		<?php settings_fields('cal_pvc_plugin_options'); ?>
		<?php do_settings_sections(__FILE__); ?>
		<p class="submit">
			<input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />
		</p>
		</form>
	</div>
<?php
}

// Validate user data for some/all of your input fields
function plugin_options_validate($input) {
	// Check our textbox option field contains no HTML tags - if so strip them out
	$input['text_string'] =  wp_filter_nohtml_kses($input['text_string']);	
	return $input; // return validated input
}


// Show post view count
add_filter('the_content','cal_pvc_show_total_views_func');
function cal_pvc_show_total_views_func($content){
	global $post;
	$viewEle='';

	// Get PVC Total Count
	$totalViews=0;
	$totalViews=get_post_meta($post->ID,'pvc_total',true);
	if(is_single()){
		if($totalViews=='' || $totalViews==0){
			$totalViews=1;
			update_post_meta($post->ID,'pvc_total',$totalViews);
		}else{
			$totalViews=$totalViews+1;
			update_post_meta($post->ID,'pvc_total',$totalViews);
		}
		$totalViews=get_post_meta($post->ID,'pvc_total',true);
	}
	// End

	// Fetch Plugin Setting
	$pvc_settings = get_option('cal_pvc_plugin_options');

	// For Home Page
	if(is_home()){
		if(isset($pvc_settings['pvc_home']) && $pvc_settings['pvc_home']=='on'){
			// Element
			$viewEle.='<div class="cal_pvc_wrap">';
			$viewEle.='<span class="cal_pvc_label">Total Views</span>';
			$viewEle.='<span class="cal_pvc_sep">=</span>';
			$viewEle.='<span class="cal_pvc_numbers">'.$totalViews.'</span>';
			$viewEle.='</div>';
			// End
		}
	}
	// For Archive Page
	if(is_archive()){
		// If on for single page
		if(isset($pvc_settings['show_everywhere']) && $pvc_settings['show_everywhere']=='on'){
			// Element
			$viewEle.='<div class="cal_pvc_wrap">';
			$viewEle.='<span class="cal_pvc_label">Total Views</span>';
			$viewEle.='<span class="cal_pvc_sep">=</span>';
			$viewEle.='<span class="cal_pvc_numbers">'.$totalViews.'</span>';
			$viewEle.='</div>';
			// End
		}
		// End
	}
	// For Single Page
	if(is_single()){
		// If on for single page
		if(isset($pvc_settings['only_single_page']) && $pvc_settings['only_single_page']=='on'){
			// Element
			$viewEle.='<div class="cal_pvc_wrap">';
			$viewEle.='<span class="cal_pvc_label">Total Views</span>';
			$viewEle.='<span class="cal_pvc_sep">=</span>';
			$viewEle.='<span class="cal_pvc_numbers">'.$totalViews.'</span>';
			$viewEle.='</div>';
			// End
		}
		// End
	}

	// Return Content
	$viewCount=$content.$viewEle;
	return $viewCount;
}