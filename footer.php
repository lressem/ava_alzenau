<footer><div class="wrap">
 <div class="fgrid">
  <a class="logo" href="<?php echo esc_url(home_url('/'));?>"><span class="crest" style="background:url(<?php echo esc_url(ava_getAssetUrl('img/logo.webp'));?>) center/cover"></span><span><b>AVA</b><small>Athleten-Vereinigung Alzenau 1899 e.V.</small></span></a>
  <div><h4>Rechtliches</h4><?php wp_nav_menu(['theme_location'=>'footer','container'=>false,'fallback_cb'=>'ava_footer_fallback']);?></div>
 </div>
 <div class="copy"><span><?php echo esc_html(ava_getOption('copy'));?></span></div>
</div></footer>
<?php wp_footer();?>
</body></html>
