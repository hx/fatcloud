<?php
/*
Plugin Name: Netlife's Tag Cloud (FatCloud)
Plugin URI: http://netlife.com.au/2009/06/28/wordle-your-wordpress-flash-based-tag-cloud-plugin/
Description: A flash-based tag cloud plugin for your WordPress blog. FatCloud, by Netlife, comes with 2 built in themes: 'Simple Skin' and the popular 'Wordle' theme. Choose text angle, colour, size ratio and much more. Beautiful tag clouds that are accessible & SEO friendly. Go to <strong>Settings</strong>&nbsp;&gt;&nbsp;<strong>FatCloud</strong> to customize appearance. Use <strong>&lt;?php&nbsp;wp_fat_cloud()&nbsp;?&gt;</strong> to embed.
Version: 0.6
Author: Neil E. Pearson
*/

/*  Copyright 2009 Fatpublisher Pty Ltd

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

function widget_wFatcloud_init() {
	if ( !function_exists('register_sidebar_widget') )
		return;

	function widget_wFatcloud($args) {
		echo $before_widget;
		echo $before_title . '<div align=center>' . wp_fat_cloud() ."</div>\n". $after_widget;
	}

register_sidebar_widget(array('Fatcloud', 'widgets'), 'widget_wFatcloud');
}

add_action('widgets_init', 'widget_wFatcloud_init');

foreach(array(

	// defaults:

	'width'		=> '100%',
	'height'	=> '300px',
	'skin'		=> 'Wordle',
	'netlife_link'	=> 'Yes',
	'skincache' => serialize(array())

) as $k=>$i)
	if(get_option('fatcloud_'.$k)===false)
		add_option('fatcloud_'.$k,$i);

$fatCloudAjaxURL=$_SERVER['REQUEST_URI'];

require_once(dirname(__FILE__).'/FatCloud.php');

FatCloud::$SWF=get_option('siteurl').'/wp-content/plugins/netlifes-tag-cloud-fatcloud/FatCloud.swf';

$option_row=mysql_query("SELECT option_id FROM {$wpdb->prefix}options WHERE option_name=\"fatcloud_skincache\" LIMIT 1");
if(!mysql_error()) {
	list($option_id)=mysql_fetch_array($option_row, MYSQL_NUM);
	FatCloud::$dbHost=DB_HOST;
	FatCloud::$dbUser=DB_USER;
	FatCloud::$dbPass=DB_PASSWORD;
	FatCloud::$dbName=DB_NAME;
	FatCloud::$dbTable=$wpdb->prefix.'options';
	FatCloud::$dbKeyField='option_id';
	FatCloud::$dbKeyValue=$option_id;
	FatCloud::$dbValueField='option_value';
}

FatCloud::processAjax();

add_action('admin_menu', 'fatcloud_admin_menu');
add_action('admin_head', 'fatcloud_admin_css');
wp_enqueue_script('fatcloud',get_option('siteurl').'/wp-content/plugins/netlifes-tag-cloud-fatcloud/FatCloud.js');


function wp_fat_cloud($args='') {
    static $counter=0;
	printf('<div id="wp_fat_cloud_%s" style="width:%s;height:%s">',$counter,get_option('fatcloud_width'),get_option('fatcloud_height'));
	wp_tag_cloud($args);
	echo '</div>';
	if(get_option('fatcloud_netlife_link','Yes')=='Yes') echo '<p style="font-size:0.8em;text-align:center">FatCloud by <a href="http://netlife.com.au">Netlife</a></p>';
	foreach(FatCloud::$skins as $i) if($i->shortName==get_option('fatcloud_skin')) $skin=$i;
	$fc=new FatCloud('wp_fat_cloud_'.$counter,$skin->shortName);
	$fc->noXML=true;
	foreach($skin->options as $i) $fc->options[$i->shortName]=get_option("fatcloud_skin_{$skin->shortName}_{$i->shortName}",$i->default);
	echo "$fc";
    $counter++;
}

function FatCloud($args='') {
	return wp_fat_cloud($args);
}

function fatcloud_admin_menu() {
	add_options_page('FatCloud by Netlife', 'FatCloud', 8, __FILE__, 'fatcloud_admin');
}

function fatcloud_admin_css() {
$css=<<<EOF
<style type="text/css">
	div.fatcloud_swatch {
		display:inline-block;
		border:1px solid #000;
		margin:0 4px;
		width:2em;
		height:2em;
		position:relative;
		top:-.2em;
		cursor:pointer;
	}
</style>
EOF;
	echo $css;
}


function fatcloud_admin() { ?>
<script type="text/javascript" src="../wp-includes/js/colorpicker.js"></script>
<script type="text/javascript"> // <!--

var fatCloudAdmin={
	'skinSelected':function(val) {
		var divs=document.getElementsByTagName('div');
		var match;
		for(var i=0;i<divs.length;i++)
			if(match=divs[i].id.match(/^fatcloud_skin_(.*)$/))
				divs[i].style.display=match[1]==val?'':'none';
		var page_options=document.getElementById('page_options');
		page_options.value=page_options.value.replace(/(,fatcloud_skin_\w+?_\w+?)+$/,'');
		var inputs=document.getElementById('fatcloud_skin_'+val).getElementsByTagName('*');
		for(i=0;i < inputs.length;i++) if(inputs[i].tagName.match(/^(input|select)$/i)) page_options.value+=','+inputs[i].id;
	},
	'cp':new ColorPicker(),
	'cpDiv':null,
	'pickColor':function(input) {
		var div, cp=document.getElementById(fatCloudAdmin.cp.divName);
		div=fatCloudAdmin.cpDiv=input.previousSibling;
		fatCloudAdmin.cp.select(div.previousSibling,input.id);
		var divP=div;
		var x=0, y=0;
		do {
			x+=divP.offsetLeft;
			y+=divP.offsetTop;
		} while(divP=divP.offsetParent);
		cp.style.top=(y+div.offsetHeight+3)+'px';
		cp.style.left=(x)+'px';
	}
};

function pickColor(color) {
	if(!fatCloudAdmin.cpDiv) return;
	fatCloudAdmin.cpDiv.style.backgroundColor=color;
	fatCloudAdmin.cpDiv.previousSibling.value=color;
}

// --></script>
<div class="wrap">
	<div id="icon-options-general" class="icon32"><br/></div>
	<h2>FatCloud by <a href="http://netlife.com.au">Netlife</a></h2>
	<a href="http://netlife.com.au" title="FatCloud by Netlife"><img src="../wp-content/plugins/netlifes-tag-cloud-fatcloud/FatCloud.png" style="float:right;margin-left:4px;border:1px solid #3B81C3" alt="FatCloud by Netlife"/></a>
	<p>FatCloud is an extension of the built-in WordPress tag cloud function. To include your FatCloud in your site, insert <code>&lt;?php&nbsp;wp_fat_cloud()&nbsp;?&gt;</code> anywhere in your theme. FatCloud will accept any of the original wp_tag_cloud() arguments.</p>

	<form method="post" action="options.php">

	<?php wp_nonce_field('update-options') ?>

	<h3>Settings</h3>

	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row"><label for="fatcloud_width">Width</label></th>
				<td>
					<input type="text" class="small-text" name="fatcloud_width" id="fatcloud_width" value="<?php echo get_option('fatcloud_width','100%') ?>" />
					<span class="setting-description">Specify the width of your FatCloud in <code>px</code>, <code>em</code> or <code>%</code>.</span>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fatcloud_height">Height</label></th>
				<td>
					<input type="text" class="small-text" name="fatcloud_height" id="fatcloud_height" value="<?php echo get_option('fatcloud_height','auto') ?>" />
					<span class="setting-description">Specify the height of your FatCloud in <code>px</code>,  <code>em</code> or <code>auto</code>.</span>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fatcloud_netlife_link">Show Netlife Link</label></th>
				<td>
					<select name="fatcloud_netlife_link" id="fatcloud_netlife_link"><?php
						foreach(array('Yes','No') as $i) printf('<option value="%s"%s>%s &nbsp;</option>',$i,$i==get_option('fatcloud_netlife_link')?' selected="selected"':'',$i);
					?></select>
					<span class="setting-description">Display a small link to Netlife below your FatCloud to show your support for the project.</span>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="fatcloud_skin">Skin</label></th>
				<td><select name="fatcloud_skin" id="fatcloud_skin" onchange="fatCloudAdmin.skinSelected(this.value)">
				<?php
					$fatcloud_selected_skin=get_option('fatcloud_skin', FatCloud::$skins[0]->shortName);
					foreach(FatCloud::$skins as $i) printf('<option value="%s"%s>%s &nbsp;</option>',$i->shortName,$i->shortName==$fatcloud_selected_skin?' selected="selected"':'',__($i->name));
				?>
				</select>
				<span class="setting-description">The skin is the overall appearance of your FatCloud.</span>
				</td>
			</tr>
		</tbody>
	</table>

	<?php foreach(FatCloud::$skins as $skin) { ?>

		<div id="fatcloud_skin_<?php echo $skin->shortName ?>" <?php if($skin->shortName!=$fatcloud_selected_skin) echo 'style="display:none"' ?>>

			<h3><?php _e($skin->name) ?></h3>

			<p><?php _e($skin->description) ?></p>

			<table class="form-table">
				<tbody>
					<?php foreach($skin->options as $option) {
						$input_id="fatcloud_skin_{$skin->shortName}_{$option->shortName}";
						printf('<tr><th scope="row"><label for="%s">%s</label></th><td>',$ipnut_id,__($option->name));
						switch($option->type) {

							case 'string':

								printf('<input type="text" class="regular-text" id="%s" name="%s" value="%s" />',
									$input_id,
									$input_id,
									get_option($input_id, $option->default)
								);

							break;

							case 'number':

								printf('<input type="text" class="small-text" id="%s" name="%s" value="%s" />',
									$input_id,
									$input_id,
									get_option($input_id, $option->default)
								);

							break;

							case 'color':

								printf('<input type="text" class="small-text" name="%s" value="%s" style="width:6em"/><div class="fatcloud_swatch" style="background:%s"
									onclick="this.nextSibling.click()">&nbsp;</div><input id="%s" class="button-secondary" type="button" value="Choose" onclick="fatCloudAdmin.pickColor(this)" />',
										$input_id,
										get_option($input_id, $option->default),
										get_option($input_id, $option->default),
										$input_id
									);

							break;

							case 'font':

								printf('<select name="%s" id="%s">',$input_id,$input_id);
								foreach(FatCloud::$fonts as $i) printf('<option value="%s"%s>%s &nbsp;</option>',$i,$i==get_option($input_id, $option->default)?' selected="selected"':'',$i);
								printf('</select>');

							break;

							case 'enum':

								printf('<select name="%s" id="%s">',$input_id,$input_id);
								foreach($option->enum as $i) printf('<option value="%s"%s>%s &nbsp;</option>',$i,$i==get_option($input_id, $option->default)?' selected="selected"':'',$i);
								printf('</select>');

							break;

						}

						printf(' <span class="setting-description">%s</span></td></tr>',__($option->description));
					} ?>
				</tbody>
			</table>

		</div>

	<?php } ?>

	<script type="text/javascript">fatCloudAdmin.cp.writeDiv()</script>

	<input type="hidden" name="action" value="update" />
	<input type="hidden" name="page_options" value="fatcloud_width,fatcloud_height,fatcloud_skin,fatcloud_netlife_link<?php
		foreach(FatCloud::$skins as $skin) if($skin->shortName==$fatcloud_selected_skin) foreach($skin->options as $option) echo ",fatcloud_skin_{$skin->shortName}_{$option->shortName}";
	?>" id="page_options" />

	<p class="submit"><input class="button-primary" type="submit" value="Save Changes" name="submit" /></p>

	</form>
	
</div>

<?php } ?>