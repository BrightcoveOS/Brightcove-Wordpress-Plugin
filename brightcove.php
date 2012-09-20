<?php
/**
 * @package Brightcove Video Cloud
 * @version 1.0
 */
/*
Plugin Name: Brightcove Video Cloud
Plugin URL: 
Description: An easy to use plugin that inserts Brightcove Video into your Wordpress site. 
Author: Brightcove
Version: 1.0
Author URI: 
*/

require dirname( __FILE__ ) . '/admin/brightcove_admin.php';
require dirname( __FILE__ ) . '/brightcove_shortcode.php';

//Nessesary to fix wordpress bug where wp_get_current_user is undefined
require_once( ABSPATH . "wp-includes/pluggable.php" );

/************************Upload Media Tab ***************************/

function brightcove_media_menu( $tabs ) {
	//TODO Check for isset or empty instead
	if ( get_option( 'bc_api_key' ) != NULL or get_option( 'bc_api_key' ) != '' ) {
		$tabs['brightcove_api'] = 'Brightcove';
	} else {
		$tabs['brightcove'] = 'Brightcove';
	}
	return $tabs;
}

function brightcove_enqueue() {
	wp_enqueue_script( 'media-upload' );

	$myStyleUrl = plugins_url( 'brightcove.css', __FILE__ );
	wp_register_style( 'myStyleSheets', $myStyleUrl );
	wp_enqueue_style( 'myStyleSheets' );
}

add_action( 'admin_enqueue_scripts', 'brightcove_enqueue' );

add_filter( 'media_upload_tabs', 'brightcove_media_menu' );
add_action( 'media_upload_brightcove', 'brightcove_menu_handle' );
add_action( 'media_upload_brightcove_api', 'brightcove_api_menu_handle' );

function brightcove_menu_handle() {
	//TODO check to see what $errors is being used for
	//TODO check to see if parameters can be passed in here
	//if not then have bc_media_upload_form call function
	return wp_iframe( 'bc_media_upload_form' );
}

function brightcove_api_menu_handle() {
	return wp_iframe( 'bc_media_api_upload_form' );
}

//Adds all the scripts nessesary for plugin to work
function add_all_scripts() {
	add_brightcove_script();
	add_jquery_scripts();
	add_validation_scripts();
	add_dynamic_brightcove_api_script();
}

function add_brightcove_script() {
	wp_deregister_script( 'brightcove_script' );
	wp_register_script( 'brightcove_script', 'http://admin.brightcove.com/js/BrightcoveExperiences.js' );
	wp_enqueue_script( 'brightcove_script' );
}

function add_jquery_scripts() {
	wp_deregister_script( 'jquery' );
	wp_register_script( 'jquery', 'http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js' );
	wp_enqueue_script( 'jquery' );

	wp_deregister_script( 'jquery-ui-core' );
	wp_register_script( 'jquery-ui-core', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.min.js' );
	wp_enqueue_script( 'jquery-ui-core' );

	wp_register_style( 'jqueryStyle', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/smoothness/jquery-ui.css' );
	wp_enqueue_style( 'jqueryStyle' );

}

function add_validation_scripts() {
	wp_deregister_script( 'jqueryPlaceholder' );
	wp_register_script( 'jqueryPlaceholder', '/wp-content/plugins/brightcove/jQueryPlaceholder/jQueryPlaceholder.js' );
	wp_enqueue_script( 'jqueryPlaceholder' );

	wp_deregister_script( 'jquery-validate' );
	wp_register_script( 'jquery-validate', '/wp-content/plugins/brightcove/jQueryValidation/jquery.validate.min.js' );
	wp_enqueue_script( 'jquery-validate' );

	wp_deregister_script( 'jquery-validat-addional' );
	wp_register_script( 'jquery-validate-addional', '/wp-content/plugins/brightcove/jQueryValidation/additional-methods.min.js' );
	wp_enqueue_script( 'jquery-validate-addional' );
}


function add_dynamic_brightcove_api_script() {
	wp_deregister_script( 'dynamic_brightcove_script' );
	wp_register_script( 'dynamic_brightcove_script', '/wp-content/plugins/brightcove/dynamic_brightcove.js' );
	wp_enqueue_script( 'dynamic_brightcove_script' );
}

//global variables 

GLOBAL $bcGlobalVariables;

$bcGlobalVariables = Array( 'playerID'               => null,
							'defaultHeight'          => null,
							'defaultWidth'           => null,
							'defaultKeyPlaylist'     => null,
							'defaultHeightPlaylist'  => null,
							'defaultWidthPlaylist'   => null,
							'defaultSet'             => null,
							'defaultSetErrorMessage' => null,
							'defaultsSection'        => null,
							'loadingImg'             => null,
							'publisherID'            => null );

//Publisher ID 
$bcGlobalVariables['publisherID'] = get_option( 'bc_pub_id' );

//Player ID for single videos
$bcGlobalVariables['playerID'] = get_option( 'bc_player_id' );
//Default height & width for single video players
$bcGlobalVariables['defaultHeight'] = get_option( 'bc_default_height' );
if ( $bcGlobalVariables['defaultHeight'] == '' ) {
	$bcGlobalVariables['defaultHeight'] = '270';
}
$bcGlobalVariables['defaultWidth'] = get_option( 'bc_default_width' );
if ( $bcGlobalVariables['defaultWidth'] == '' ) {
	$bcGlobalVariables['defaultWidth'] = '480';
}
//Player ID for playlists
$bcGlobalVariables['playerKeyPlaylist'] = get_option( 'bc_player_key_playlist' );

//Default height & width for playlist players
$bcGlobalVariables['defaultHeightPlaylist'] = get_option( 'bc_default_height_playlist' );
if ( $bcGlobalVariables['defaultHeightPlaylist'] == '' ) {
	$bcGlobalVariables['defaultHeightPlaylist'] = '400';
}
$bcGlobalVariables['defaultWidthPlaylist'] = get_option( 'bc_default_width_playlist' );
if ( $bcGlobalVariables['defaultWidthPlaylist'] == '' ) {
	$bcGlobalVariables['defaultWidthPlaylist'] = '940';
}
//Checks to see if both those variables are set
if ( $bcGlobalVariables['playerID'] == '' || $bcGlobalVariables['playerKeyPlaylist'] == '' || $bcGlobalVariables['publisherID'] == '' ) {
	$bcGlobalVariables['defaultSet'] = false;
} else {
	$bcGlobalVariables['defaultSet'] = true;
}

if ( current_user_can( 'administrator' ) ) {
	$bcGlobalVariables['defaultSetErrorMessage'] = "<div class='hidden error' id='defaults-not-set' data-defaultsSet='" . $bcGlobalVariables['defaultSet'] . "'>
     You have not set up your defaults for this plugin. Please click on the link to set your defaults.
  <a target='_top' href='admin.php?page=brightcove_menu'>Brightcove Settings</a>
  </div>";
} else {
	$bcGlobalVariables['defaultSetErrorMessage'] = "<div class='hidden error' id='defaults-not-set' data-defaultsSet='" . $bcGlobalVariables['defaultSet'] . "'>
    You have not set up your defaults for the Brightcove plugin. Please contact your site administrator to set these defaults.
  </div>";
}


$playerIdPlaylist  = isset( $bcGlobalVariables['playerIDPlaylist'] ) ? $bcGlobalVariables['playerIDPlaylist'] : '';
$playerKeyPlaylist = isset( $bcGlobalVariables['playerKeyPlaylist'] ) ? $bcGlobalVariables['playerKeyPlaylist'] : '';

$bcGlobalVariables['defaultsSection'] =
	"<div class='defaults'>
	<input type='hidden' id='bc-default-player' name='bc-default-player' value='" . $bcGlobalVariables['playerID'] . "' >
	<input type='hidden' id='bc-default-width' name='bc-default-width' value='" . $bcGlobalVariables['defaultWidth'] . "' >
	<input type='hidden' id='bc-default-height' name='bc-default-height' value='" . $bcGlobalVariables['defaultHeight'] . "' >
	<input type='hidden' id='bc-default-player-playlist' name='bc-default-player-playlist' value='" . $playerIdPlaylist . "' >
	<input type='hidden' id='bc-default-player-playlist-key' name='bc-default-player-playlist-key' value='" . $playerKeyPlaylist . "' >
	<input type='hidden' id='bc-default-width-playlist' name='bc-default-width-playlist' value='" . $bcGlobalVariables['defaultWidthPlaylist'] . "' >
	<input type='hidden' id='bc-default-height-playlist' name='bc-default-height-playlist' value='" . $bcGlobalVariables['defaultHeightPlaylist'] . "' >
	</div>";

$bcGlobalVariables['loadingImg'] = "<img class='loading-img' src='/wp-includes/js/thickbox/loadingAnimation.gif' />";


function set_shortcode_button( $playlistOrVideo, $buttonText ) {

	if ( $playlistOrVideo == 'playlist' ) {
		$id = 'playlist-shortcode-button';
	} else {
		$id = 'video-shortcode-button';
	}

	?>
<div class='media-item no-border insert-button-container'>
	<button disabled='disabled' id='<?php echo $id; ?>' class='aligncenter button'/>
		<?php echo $buttonText; ?></button>
</div> <?php

}

//TODO Pass in as map
function add_player_settings( $playlistOrVideo, $buttonText ) {
	GLOBAL $bcGlobalVariables;
	if ( $playlistOrVideo == 'playlist' ) {
		$setting    = '-playlist';
		$height     = $bcGlobalVariables['defaultHeightPlaylist'];
		$width      = $bcGlobalVariables['defaultWidthPlaylist'];
		$playerKey  = $bcGlobalVariables['playerKeyPlaylist'];
		$id         = 'playlist-settings';
		$class      = 'playlist-hide';
		$playerHTML = "<tr class='bc-width-row'>
            <th valign='top' scope='row' class='label'>
              <span class=;alignleft;><label for=bcPlaylistKey'>Playlist Key</label></span>
              <span class='alignright'></span>
            </th>
            <td>
             <input class='player-data' type='text' name='bcPlaylistKey' id='bc-player-playlist-key' value='${playerKey}' placeholder='Default is ${playerKey}' />
            </td>
          </tr>";
	} else {
		$setting    = '';
		$height     = $bcGlobalVariables['defaultHeight'];
		$width      = $bcGlobalVariables['defaultWidth'];
		$player     = $bcGlobalVariables['playerID'];
		$id         = 'video-settings';
		$class      = 'video-hide';
		$playerHTML = "<tr class='bc-player-row'>
            <th valign='top' scope='row' class='label'>
              <span class='alignleft'><label for='bcPlayer'>Player ID:</label></span>
              <span class='alignright'></span>
            </th>
            <td>
             <input class='digits player-data' type='text' name='bcPlayer' id='bc-player-${setting}' value='${player}' placeholder='Default ID is ${player}'/>
            </td>
          </tr>";
	}

	?>
<form class='<?php echo $class;?>' id='<?php echo $id; ?>'>
	<table>
		<tbody>
			<?php echo $playerHTML; ?>
		<tr class='bc-width-row'>
			<th valign='top' scope='row' class='label'>
				<span class="alignleft"><label for="bc-width<?echo $setting; ?>">Width:</label></span>
				<span class="alignright"></span>
			</th>
			<td>
				<input class='digits player-data' type='text' name='bcWidth' id='bc-width<?echo $setting; ?>'
					   placeholder='Default is <?php echo $width; ?> px'/>
			</td>
		</tr>
		<tr class='bc-height-row'>
			<th valign='top' scope='row' class='label'>
				<span class="alignleft"><label for="bc-height<?echo $setting; ?>">Height:</label></span>
				<span class="alignright"></span>
			</th>
			<td>
				<input class='digits player-data' type='text' name='bcHeight' id='bc-height<?echo $setting; ?>'
					   placeholder='Default is <?php echo $height; ?> px'/>
			</td>
		</tr>
		</tbody>
	</table>
	<?php set_shortcode_button( $playlistOrVideo, $buttonText ); ?>
</form>
<?php
}

function add_preview_area( $playlistOrVideo ) {

	if ( $playlistOrVideo == 'playlist' ) {
		$id         = 'dynamic-bc-placeholder-playlist';
		$class      = 'playlist-hide';
		$otherClass = 'playlist';
	} else {
		$id         = 'dynamic-bc-placeholder-video';
		$class      = 'video-hide';
		$otherClass = 'video';
	}

	?>
<div class='<?php echo $class; ?> media-item no-border player-preview preview-container hidden'>
	<h3 class='preview-header'>Video Preview</h3>
	<table>
		<tbody>
		<tr>
			<td>
				<div class='alignleft'>
					<h4 id='bc-title-<?php echo $otherClass; ?>' class='bc-title'></h4>

					<p id='bc-description-<?php echo $otherClass; ?>' class='bc-description'></p>

					<div id="<?php echo $id; ?>"></div>
				</div>
				<div class='alignleft'>
				</div>
			</td>
		</tr>
		</tbody>
	</table>
</div>
<?php
}

function bc_media_upload_form() {
	media_upload_header();
	add_all_scripts();
	?>
<div class="bc-container">
	<?php
	GLOBAL $bcGlobalVariables;
	echo $bcGlobalVariables['defaultSetErrorMessage'];
	echo $bcGlobalVariables['defaultsSection'];
	echo $bcGlobalVariables['loadingImg'];
	?>

	<div class='no-error'>
		<div id='tabs'>
			<ul>
				<li><a class='video-tab' href="#tabs-1">Videos</a></li>
				<li><a class='playlist-tab' href="#tabs-2">Playlists</a></li>
			</ul>
			<div class='tab clearfix video-tab' id='tabs-1'>
				<div class='media-item no-border'>
					<form id='validate-video'>
						<table>
							<tbody>
							<tr>
								<th valign='top' scope='row' class='label'>
									<span class="alignleft"><label for="bc-video">Video:</label></span>
									<span class="alignright"></span>
								</th>
								<td>
									<input class='id-field player-data' placeholder='Video ID' aria-required="true"
										   type='text' name='bcVideo' id='bc-video' placeholder='Video ID or URL'>
								</td>
							</tr>
							<tr>
								<th valign='top' scope='row' class='label'>
								</th>
								<td class='bc-check'>
									<input class='player-data alignleft' type='checkbox' name='bc-video-ref'
										   id='bc-video-ref'/>
									<span class="alignleft"><label for='bc-video-ref'>This is a reference ID, not a
										video ID </label></span>
								</td>
							</tr>
							</tbody>
						</table>
					</form>
				</div>
			</div>
			<div class='tab clearfix playlist-tab' id='tabs-2'>
				<div class='media-item no-border'>
					<form id='validate-playlist'>
						<table>
							<tbody>
							<tr>
								<th valign='top' scope='row' class='label'>
									<span class="alignleft"><label for="bc-playlist">Playlist:</label></span>
									<span class="alignright"></span>
								</th>
								<td>
									<input class='id-field player-data' type='text' name='bcPlaylist' id='bc-playlist'
										   placeholder='Playlist ID(s) separated by commas or spaces'/>
								</td>
							</tr>
							<tr>
								<th valign='top' scope='row' class='label'>
								</th>
								<td class='bc-check'>
									<input class='alignleft player-data' type='checkbox' name='bc-playlist-ref'
										   id='bc-playlist-ref'/>
									<span class="alignleft"><label for='bc-playlist-ref'>These are reference IDs, not
										playlist IDs </label></span>
								</td>
							</tr>
							</tbody>
						</table>
					</form>
				</div>
			</div>
		</div>
		<!-- End of tabs -->
		<div id='bc-error' class='hidden error'>An error has occured, please check to make sure that you have a valid
			video or playlist ID
		</div>

		<?php
		//TODO pass in map of defaults
		add_player_settings( 'video', 'Insert Shortcode' );?>

		<?php
		add_preview_area( 'video' );
		add_player_settings( 'playlist', 'Insert Shortcode' );
		add_preview_area( 'playlist' );

		?>
	</div> <?php
}

function add_mapi_script() {
	wp_deregister_script( 'mapi_script' );
	wp_register_script( 'mapi_script', '/wp-content/plugins/brightcove/bc-mapi.js' );
	wp_enqueue_script( 'mapi_script' );
}

function bc_media_api_upload_form() {
	GLOBAL $bcGlobalVariables;
	media_upload_header();
	add_all_scripts();
	add_mapi_script();
	$apiKey = get_option( 'bc_api_key' );
	?>
	<div class="bc-container">
	<?php
	echo $bcGlobalVariables['defaultSetErrorMessage'];
	echo $bcGlobalVariables['defaultsSection'];
	echo $bcGlobalVariables['loadingImg'];

	?>
	<input type='hidden' id='bc-api-key' name='bc-api-key' value='<?php echo $apiKey; ?>'>
	<div class='no-error'>
		<div id='tabs-api'>
			<ul>
				<li><a class='video-tab-api' href="#tab-1">Videos</a></li>
				<li><a class='playlist-tab-api' href="#tab-2">Playlists</a></li>
			</ul>
			<div class='tab-1' class='tabs clearfix video-tabs'>
				<form class='clearfix' id='search-form'>
					<div class='alignleft'>
						<input placeholder=' Search by name, description, tag or custom field' id='bc-search-field'
							   type='text'>
					</div>
					<div class='alignright'>
						<button class='button' type='submit' id='bc-search'>Search</button>
					</div>
				</form>
				<div class='bc-video-search clearfix' id='bc-video-search-video'></div>
				<?php add_player_settings( 'video', 'Insert Video' ); ?>
			</div>
			<div id='tab-2' class='tabs clearfix playlist-tab'>
				<div class='bc-video-search clearfix' id='bc-video-search-playlist'></div>
				<?php add_player_settings( 'playlist', 'Insert Playlists' );?>
			</div>
		</div>
	</div>
	<?php


}


?>
