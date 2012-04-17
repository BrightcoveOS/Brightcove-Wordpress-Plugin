<?php

add_shortcode('brightcove','add_brightcove');

function add_brightcove($atts) {
add_brightcove_script();

$html;

$html = '<div style="display:none"></div>
<object id="'.rand().'" class="BrightcoveExperience">
  <param name="bgcolor" value="#FFFFFF" />
  <param name="wmode" value="transparent" />
  <param name="width" value="' . $atts['width'] . '" />
  <param name="height" value="'. $atts['height'] .'" />';
 if ($atts['playerid'] != NULL) {   
    $html = $html . '<param name="playerID" value="'.$atts['playerid'].'" />';
  }

  if ($atts['playerkey'] != NULL) {   
    $html = $html . '<param name="playerKey" value="'.$atts['playerkey'].'"/>';
  }
  $html = $html .' <param name="isVid" value="true" />
  <param name="isUI" value="true" />
  <param name="dynamicStreaming" value="true" />';

  if ($atts['videoid'] != NULL)
  { 
    $html = $html . '<param name="@videoPlayer" value="'.$atts['videoid'].'" />';
  }
  if ($atts['playlistid'] != NULL)
  {   
    $html = $html . '<param name="@playlistTabs" value="'.$atts['playlistid'].'" />';
    $html = $html . '<param name="@videoList" value="'.$atts['playlistid'].'" />';
    $html = $html . '<param name="@playlistCombo" value="'.$atts['playlistid'].'" />';
  } 
$html = $html . '</object>';

return $html;
}


