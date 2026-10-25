<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<header>
  <div class="nav">
    <a class="logo" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php echo esc_attr(ava_t('Startseite')); ?>">
      <span class="crest" style="background:url(<?php echo esc_url(ava_getAssetUrl('img/logo.webp')); ?>) center/cover"></span>
      <span><b>AVA</b><small>Athleten-Vereinigung Alzenau 1899 e.V.</small></span>
    </a>
    <nav aria-label="<?php echo esc_attr(ava_t('Hauptnavigation')); ?>">
      <?php wp_nav_menu(['theme_location' => 'main', 'container' => false, 'fallback_cb' => 'ava_nav_fallback']); ?>
    </nav>
    <div class="nav-actions">
      <?php ava_renderLanguageSwitcher(); ?>
      <a class="btn" href="<?php echo esc_url(home_url('/#mitmachen')); ?>"><?php ava_e('Mitglied werden'); ?></a>
    </div>
  </div>
</header>
