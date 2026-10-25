<?php
if(!defined('ABSPATH'))exit;
define('AVA_V','1.0.0');
require get_template_directory().'/inc/core.php';
add_action('after_setup_theme',function(){
 add_theme_support('title-tag');add_theme_support('post-thumbnails');
 add_theme_support('html5',['style','script']);
 register_nav_menus(['main'=>'Hauptmenü','footer'=>'Footer (Rechtliches)']);
 add_image_size('ava-card',864,440,true);
});
add_action('wp_enqueue_scripts',function(){
 wp_enqueue_style('ava',get_stylesheet_uri(),[],AVA_V);
 $bg=ava_getOption('hero_poster')?:ava_getAssetUrl('img/hero.webp');
 wp_add_inline_style('ava',':root{--hero-bg:url("'.esc_url($bg).'")}');
});
function ava_nav_fallback(){
 $i=['aktuelles'=>'Aktuelles','liga'=>'Liga','training'=>'Training','verein'=>'Verein','mitmachen'=>'Mitmachen','kontakt'=>'Standort & Kontakt','partner'=>'Partner'];
 echo '<ul>';foreach($i as $k=>$l)echo '<li><a href="'.esc_url(home_url('/#'.$k)).'">'.esc_html($l).'</a></li>';echo '</ul>';
}
function ava_footer_fallback(){
 echo '<ul>';foreach(['impressum'=>'Impressum','datenschutz'=>'Datenschutz','vereinssatzung'=>'Vereinssatzung'] as $s=>$l){
  $p=get_page_by_path($s);echo '<li><a href="'.esc_url($p?get_permalink($p):home_url('/'.$s.'/')).'">'.esc_html($l).'</a></li>';}
 echo '</ul>';
}
