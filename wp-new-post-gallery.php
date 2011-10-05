<?php
/*
Plugin Name: New Post Gallery
Plugin URI: #
Description: Replaces the old gallery shortcode with a javascript-driven gallery. Requires the NerdyIsBack Plugin Framework.
Version: 0.1
Author: David Beveridge
Author URI: http://www.nerdyisback.com
License: MIT
*/
/*	Copyright (c) 2010 David Beveridge, Studio DBC

	Permission is hereby granted, free of charge, to any person obtaining a copy
	of this software and associated documentation files (the "Software"), to deal
	in the Software without restriction, including without limitation the rights
	to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
	copies of the Software, and to permit persons to whom the Software is
	furnished to do so, subject to the following conditions:

	The above copyright notice and this permission notice shall be included in
	all copies or substantial portions of the Software.

	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
	IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
	AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
	LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
	OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
	THE SOFTWARE.
*/

// Create Options pages:

$fields = array();
$fields[] = array('id' => 'width','name' => 'Width', 'type' => 'text', 'desc' => 'Gallery width, in pixels');
$fields[] = array('id' => 'height','name' => 'Height', 'type' => 'text', 'desc' => 'Gallery height, in pixels');
$fields[] = array('id' => 'thumbstyle', 'name' => 'Thumbs','type' => 'radio', 'options' => array(
	'Numbers' => 'number',
	'Names' => 'name',
	'Images' => 'image'
));
$fields[] = array('id' => 'thumbDimParagraph', 'name' => 'The following are used in conjunction with the "Images" option for Thumbnails.', 'type' => 'paragraph');
$fields[] = array('id' => 'thumbwidth', 'name' => 'Thumb Width', 'type' => 'text', 'desc' => 'Thumbnail width, in pixels');
$fields[] = array('id' => 'thumbheight', 'name' => 'Thumb Height', 'type' => 'text', 'desc' => 'Thumbnail height, in pixels');

$newPostGalleryOpts = new CustomOptionsPage('options','newgalleryshortcode','Gallery Shortcode','manage_options',$fields);

// Options have been set up.

$thumbwidth = intval($newPostGalleryOpts->getOption('thumbwidth'));
if(!$thumbwidth)	{
	$thumbwidth = 50;
}
$thumbheight = intval($newPostGalleryOpts->getOption('thumbheight'));
if(!$thumbheight)	{
	$thumbheight = 50;
}

add_image_size('new-post-gallery',$thumbwidth,$thumbheight,TRUE);


// Remove old gallery shortcode:
remove_shortcode('gallery');

// define new gallery shortcode function:
function new_post_gallery_shortcode($atts,$content,$code)	{
	global $post,$newPostGalleryOpts,$thumbwidth,$thumbheight;
	
	$getAttachmentArgs = array('post_type' => 'attachment','numberposts' => -1,'post_status' => NULL,'post_parent'=>$post->ID, 'orderby' => 'menu_order', 'order' => 'ASC');
	
	if($attachements = get_posts($getAttachmentArgs)) {
		$output = '<ul class="postGallery">';
		$count = 0;
		foreach($attachements as $attachment) {
			if(wp_attachment_is_image($attachment->ID)) {
				$count++;
				$attachmentThumb = wp_get_attachment_image_src($attachment->ID,'new-post-gallery');
				$attachmentImage = wp_get_attachment_image_src($attachment->ID,'full');
				$thumbstyle = $newPostGalleryOpts->getOption('thumbstyle');
				if($thumbstyle == 'image')	{
					$output .=
					"<li>
						<a href=\"$attachmentImage[0]\" class=\"backgroundThumbnail\">
							<img src=\"$attachmentThumb[0]\" width=\"$attachmentThumb[1]\" height=\"$attachmentThumb[2]\" />
						</a>
					</li>";
				}
				elseif($thumbstyle == 'name')	{
					$output .= "<li><a href=\"$attachmentImage[0]\" class=\"backgroundThumbnail\">{$attachment->post_title}</a></li>";
				}
				else	{
					$output .= "<li><a href=\"$attachmentImage[0]\" class=\"backgroundThumbnail\">$count</a></li>";
				}
			}
		}
		$output .= '</ul>';
		$output .= get_new_post_gallery_javascript();
		return $output;
	}
}

function add_new_post_gallery_resources()	{
	$upload = wp_upload_dir();
	$dir = $upload['basedir'];
	$url = $upload['baseurl'];
	$customCSSfile = $dir.'/css/jquery.simpleGallery.css';

	if(file_exists($customCSSfile))	{
		wp_enqueue_style('new-post-gallery',$url.'/css/jquery.simpleGallery.css');
	}
	else	{
		@mkdir($dir.'/css');
		@mkdir($dir.'/images');
		@copy($customCSSfile,dirname(__FILE__).'/css/jquery.simpleGallery.css');
		@copy($dir.'/images/next.gif',$dir.'/images/next.gif');
		@copy($dir.'/images/prev.gif',$dir.'/images/prev.gif');
		wp_enqueue_style('new-post-gallery',plugins_url().'/wp-new-post-gallery/css/jquery.simpleGallery.css');
	}
	wp_enqueue_script('jquery.simpleGallery',plugins_url().'/wp-new-post-gallery/js/jquery.simpleGallery.js',array('jquery'),0.1);
}

function get_new_post_gallery_javascript()	{
	global $newPostGalleryOpts;
	$w = intval($newPostGalleryOpts->getOption('width'));
	if(!$w)	{
		$w = 640;
	}
	$h = intval($newPostGalleryOpts->getOption('height'));
	if(!$h)	{
		$h = 480;
	}
	?>
<script type="text/javascript">
(function($){
	$(document).ready(function(){
		$('ul.postGallery').simpleGallery({
			width: <?php echo $w; ?>,
			height: <?php echo $h; ?>
		});
	});
})(jQuery.noConflict());
</script>
	<?php
}

function new_post_gallery_javascript()	{
	echo get_new_post_gallery_javascript();
}

// Register new gallery shortcode:
add_shortcode('gallery','new_post_gallery_shortcode');

// Register stylesheet & script
add_action('get_header','add_new_post_gallery_resources');

// Load JavaScript implementation into header
//add_action('wp_head','new_post_gallery_javascript');