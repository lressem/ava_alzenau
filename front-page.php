<?php
/**
 * Front page: hero, news, league, training, club, membership, contact, partners.
 */
get_header();

// ---------------------------------------------------------------------------
// Data for the league section
// ---------------------------------------------------------------------------
$now = date('Y-m-d\TH:i');

$nextMatchDays = ava_getPosts('kampftag', [
    'numberposts' => 1,
    'meta_key'    => 'datum',
    'orderby'     => 'meta_value',
    'order'       => 'ASC',
    'meta_query'  => [['key' => 'datum', 'value' => $now, 'compare' => '>=', 'type' => 'CHAR']],
]);
$pastMatchDays = ava_getPosts('kampftag', [
    'numberposts' => 10,
    'meta_key'    => 'datum',
    'orderby'     => 'meta_value',
    'order'       => 'DESC',
    'meta_query'  => [['key' => 'datum', 'value' => $now, 'compare' => '<', 'type' => 'CHAR']],
]);

// Most recent match day that already has a result entered.
$lastMatchDay = null;
foreach ($pastMatchDays as $matchDay) {
    if (ava_getMeta($matchDay->ID, 'p_heim') !== '') {
        $lastMatchDay = $matchDay;
        break;
    }
}

$clubName = ava_getOption('club_name');

// ---------------------------------------------------------------------------
// Template helpers
// ---------------------------------------------------------------------------
$instagramIcon = '<svg viewBox="0 0 30 30" aria-label="Instagram" role="img">'
    . '<defs><radialGradient id="ig" cx="30%" cy="105%" r="120%">'
    . '<stop offset="0" stop-color="#FFCC00"/><stop offset=".25" stop-color="#FE4A05"/>'
    . '<stop offset=".55" stop-color="#FF005F"/><stop offset="1" stop-color="#820BFF"/>'
    . '</radialGradient></defs>'
    . '<rect width="30" height="30" rx="8" fill="url(#ig)"/>'
    . '<rect x="6.5" y="6.5" width="17" height="17" rx="5" fill="none" stroke="#fff" stroke-width="2"/>'
    . '<circle cx="15" cy="15" r="4" fill="none" stroke="#fff" stroke-width="2"/>'
    . '<circle cx="20.2" cy="9.8" r="1.2" fill="#fff"/>'
    . '</svg>';

/** Inline style that shows an image as a background. */
$backgroundStyle = function ($url, $size = 'cover') {
    return 'background:url(' . esc_url($url) . ') center/' . $size . ' no-repeat';
};

/** HTML of a sponsor logo (optionally wrapped in a link), or a placeholder without image. */
$renderSponsorLogo = function ($sponsor, $class = 'lg') use ($backgroundStyle) {
    $imageUrl = ava_getPostImageUrl($sponsor->ID, 'medium');
    $link     = ava_getMeta($sponsor->ID, 'link');

    if ($imageUrl) {
        $html = '<div class="' . $class . '" role="img" aria-label="' . esc_attr($sponsor->post_title) . '"'
            . ' style="' . $backgroundStyle($imageUrl, 'contain') . '"></div>';
    } else {
        $html = '<div class="logo-ph">LOGO</div>';
    }

    if (!$link) {
        return $html;
    }
    return '<a href="' . esc_url($link) . '" rel="noopener" target="_blank">' . $html . '</a>';
};
?>
<main id="top">

<section class="hero">
  <?php if (ava_getOption('hero_mp4')): ?>
  <video autoplay muted loop playsinline preload="auto" aria-hidden="true">
    <?php if (ava_getOption('hero_webm')): ?>
    <source src="<?php echo esc_url(ava_getOption('hero_webm')); ?>" type="video/webm">
    <?php endif; ?>
    <source src="<?php echo esc_url(ava_getOption('hero_mp4')); ?>" type="video/mp4">
  </video>
  <?php endif; ?>
  <div class="wrap">
    <div class="where geo"><?php echo esc_html(ava_getOption('hero_geo')); ?></div>
    <h1><?php echo esc_html(ava_getOption('hero_l1')); ?><span><?php echo esc_html(ava_getOption('hero_l2')); ?></span><?php echo esc_html(ava_getOption('hero_l3')); ?></h1>
    <p class="lead"><?php echo esc_html(ava_getOption('hero_lead')); ?></p>
    <div class="cta">
      <a class="btn" href="#mitmachen"><?php echo esc_html(ava_getOption('btn1')); ?></a>
      <a class="btn" href="<?php echo esc_attr(ava_getOption('btn2_link')); ?>"><?php echo esc_html(ava_getOption('btn2')); ?></a>
      <?php if (ava_getOption('video_link')): ?>
      <a class="vid" href="<?php echo esc_url(ava_getOption('video_link')); ?>" target="_blank" rel="noopener">
        <i></i><?php ava_e('Video ansehen'); ?>
      </a>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php $stripSponsors = ava_getPosts('sponsor', ['meta_key' => 'gruppe', 'meta_value' => 'leiste']); ?>
<?php if ($stripSponsors): ?>
<div class="strip"><div class="wrap">
  <span class="t"><?php ava_e('Unsere Sponsoren & Partner'); ?></span>
  <div class="carousel">
    <button type="button" class="car-btn prev" aria-label="<?php echo esc_attr(ava_t('Zurück')); ?>">&#8249;</button>
    <div class="car-track">
      <?php foreach ($stripSponsors as $sponsor): ?>
      <?php echo $renderSponsorLogo($sponsor); ?>
      <?php endforeach; ?>
    </div>
    <button type="button" class="car-btn next" aria-label="<?php echo esc_attr(ava_t('Weiter')); ?>">&#8250;</button>
  </div>
</div></div>
<?php endif; ?>

<section id="aktuelles" class="news"><div class="wrap">
  <p class="eyebrow"><?php ava_e('News aus dem Verein'); ?><?php echo $instagramIcon; ?></p>
  <h2><?php ava_e('Aktuelles & Kampftage'); ?></h2>

  <?php if (ava_getOption('news_shortcode')): ?>
  <div class="insta-wrap"><?php echo do_shortcode(wp_kses_post(ava_getOption('news_shortcode'))); ?></div>
  <?php else: ?>
  <?php
  // Placeholder cards: [date, title, text]. Image: assets/img/news-N.webp
  $placeholderNews = [
      ['19. September 2026', 'Fight Day gegen Großzimmern', 'Erster Auswärtskampf gegen den AV 05 Großzimmern am Samstag, den 19.09.2026 um 19:00 Uhr.'],
      ['08. August 2026', 'Jugendzeltlager', 'Drei Tage voller Spaß, Sport und Teambuilding. Anmeldungen für Kinder von 6 bis 16 Jahren sind ab sofort freigeschaltet.'],
      ['12.–16. Mai 2026', 'Europameisterschaft', 'Irma Neumann wurde vom Deutschen Ringer-Bund für die Teilnahme an der Europameisterschaft nominiert.'],
  ];
  ?>
  <div class="grid g3">
    <?php foreach ($placeholderNews as $index => $news): ?>
    <article class="card">
      <div class="img" role="img" aria-label="<?php echo esc_attr(ava_t('Beitragsbild')); ?>"
           style="<?php echo $backgroundStyle(ava_getAssetUrl('img/news-' . ($index + 1) . '.webp')); ?>"></div>
      <div class="date"><?php echo esc_html(ava_t($news[0])); ?></div>
      <h3><?php echo esc_html(ava_t($news[1])); ?></h3>
      <p><?php echo esc_html(ava_t($news[2])); ?></p>
    </article>
    <?php endforeach; ?>
  </div>
  <p class="note"><?php ava_e('Platzhalter. Zum Aktivieren des Instagram-Feeds den Shortcode des Plugins unter „Vereinsdaten“ eintragen.'); ?></p>
  <?php endif; ?>
</div></section>

<section id="liga"><div class="wrap">
  <p class="eyebrow"><?php ava_e('Sei hautnah dabei'); ?></p>
  <h2><?php ava_e('Liga & Ergebnisse'); ?></h2>

  <div class="liga">

    <div class="card tbl">
      <h3><?php echo esc_html(ava_getOption('liga_titel')); ?></h3>
      <table>
        <thead>
          <tr>
            <th><?php ava_e('Pl.'); ?></th>
            <th><?php ava_e('Verein'); ?></th>
            <th><?php ava_e('Diff.'); ?></th>
            <th><?php ava_e('Punkte'); ?></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach (ava_getLeagueTable(1257) as $row): ?>
          <tr<?php echo ((int) ($row['OrgID'] ?? 0) === 2221) ? ' class="me"' : ''; ?>>
            <td><?php echo esc_html($row['Platzierung'] ?? ''); ?></td>
            <td><?php echo esc_html($row['TeamName'] ?? ''); ?></td>
            <td><?php echo esc_html(sprintf('%+d', (int) ($row['Differenz'] ?? 0))); ?></td>
            <td><?php echo esc_html(($row['WertungPlus'] ?? 0) . ':' . ($row['WertungMinus'] ?? 0)); ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="card match">
      <span class="tag"><?php ava_e('Nächster Kampftag'); ?></span>

      <?php if ($nextMatchDays): ?>
      <?php
      $nextMatchDay = $nextMatchDays[0];
      $timestamp    = strtotime(ava_getMeta($nextMatchDay->ID, 'datum'));
      $isHome       = ava_getMeta($nextMatchDay->ID, 'heim') === '1';
      $opponent     = ava_getMeta($nextMatchDay->ID, 'gegner');
      $shortName    = ava_getOption('club_kurz');
      $homeTeam     = $isHome ? $shortName : $opponent;
      $guestTeam    = $isHome ? $opponent : $shortName;
      $dateTime     = wp_date(ava_t('l, j. F Y'), $timestamp) . ' · '
                    . trim(wp_date(ava_t('H:i'), $timestamp) . ' ' . ava_t('Uhr'));
      ?>
      <div class="vs">
        <div><?php echo esc_html($homeTeam); ?><small><?php ava_e($isHome ? 'Heim' : 'Gast'); ?></small></div>
        <i>VS</i>
        <div><?php echo esc_html($guestTeam); ?><small><?php ava_e($isHome ? 'Gast' : 'Heim'); ?></small></div>
      </div>
      <div>
        <?php echo esc_html($dateTime); ?><br>
        <span class="venue"><?php ava_e('Ort:'); ?> <?php echo esc_html(ava_getMeta($nextMatchDay->ID, 'ort')); ?></span>
      </div>
      <?php if (ava_getMeta($nextMatchDay->ID, 'ticket')): ?>
      <p class="ticket"><a class="btn gold" href="<?php echo esc_url(ava_getMeta($nextMatchDay->ID, 'ticket')); ?>"><?php ava_e('Tickets'); ?></a></p>
      <?php endif; ?>

      <?php else: ?>
      <p class="tbd"><?php ava_e('Der nächste Kampftag wird bald bekanntgegeben.'); ?></p>
      <?php endif; ?>

      <?php if ($lastMatchDay): ?>
      <?php
      $wasHome   = ava_getMeta($lastMatchDay->ID, 'heim') === '1';
      $lastGuest = ava_getMeta($lastMatchDay->ID, 'gegner');
      $leftTeam  = $wasHome ? $clubName : $lastGuest;
      $rightTeam = $wasHome ? $lastGuest : $clubName;
      $score     = ava_getMeta($lastMatchDay->ID, 'p_heim') . ' : ' . ava_getMeta($lastMatchDay->ID, 'p_gast');
      ?>
      <div class="last">
        <?php ava_e('Letztes Ergebnis'); ?><br>
        <?php echo esc_html($leftTeam); ?> <b><?php echo esc_html($score); ?></b> <?php echo esc_html($rightTeam); ?>
      </div>
      <?php endif; ?>
    </div>

  </div>
</div></section>

<section id="training"><div class="wrap">
  <p class="eyebrow"><?php ava_e('Ab auf die Matte'); ?></p>
  <h2><?php ava_e('Trainingszeiten & Trainer*innen'); ?></h2>

  <?php
  $slotsByDay = [];
  foreach (ava_getPosts('training') as $slot) {
      $slotsByDay[ava_getMeta($slot->ID, 'tag')][] = $slot;
  }
  ?>
  <div class="grid g3 days">
    <?php foreach (ava_getWeekDays() as $day): ?>
    <?php
    if (empty($slotsByDay[$day])) {
        continue;
    }
    $slots = $slotsByDay[$day];
    usort($slots, function ($a, $b) {
        return strcmp(ava_getMeta($a->ID, 'von'), ava_getMeta($b->ID, 'von'));
    });
    ?>
    <div class="card">
      <h3><?php ava_e($day); ?></h3>
      <?php foreach ($slots as $slot): ?>
      <div class="slot">
        <b><?php echo esc_html($slot->post_title); ?></b>
        <time><?php echo esc_html(ava_getMeta($slot->ID, 'von') . '–' . ava_getMeta($slot->ID, 'bis')); ?></time>
        <span><?php ava_e('Coach:'); ?> <?php echo esc_html(ava_getMeta($slot->ID, 'coach')); ?></span>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="card place">
    <p>
      <b><?php echo esc_html(ava_getOption('trainingsort')); ?></b><br>
      <?php echo esc_html(ava_getOption('trainingshinweis')); ?>
    </p>
  </div>

  <div class="grid g6 coaches">
    <?php foreach (ava_getPosts('trainer') as $trainer): ?>
    <div class="card coach">
      <div class="ph" role="img" aria-label="<?php echo esc_attr($trainer->post_title); ?>"
           style="<?php echo $backgroundStyle(ava_getPostImageUrl($trainer->ID, 'large'), 'contain'); ?>"></div>
      <div class="t">
        <small><?php echo esc_html(ava_getMeta($trainer->ID, 'rolle')); ?></small>
        <h3><?php echo esc_html($trainer->post_title); ?></h3>
        <?php if (ava_getMeta($trainer->ID, 'zitat')): ?>
        <p><?php echo esc_html(ava_getMeta($trainer->ID, 'zitat')); ?></p>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div></section>

<section id="verein"><div class="wrap about">
  <div>
    <p class="eyebrow"><?php ava_e('Wir machen Kinder stark'); ?></p>
    <h2><?php ava_e('Unser Verein'); ?></h2>
    <p><?php echo esc_html(ava_getOption('verein_p1')); ?></p>
    <p><?php echo esc_html(ava_getOption('verein_p2')); ?></p>

    <div class="stats">
      <?php foreach ([1, 2, 3, 4] as $n): ?>
      <div class="card stat<?php echo $n > 2 ? ' gold' : ''; ?>">
        <b><?php echo esc_html(ava_getOption('z' . $n)); ?></b>
        <span><?php echo esc_html(ava_getOption('z' . $n . 'l')); ?></span>
      </div>
      <?php endforeach; ?>
      <?php foreach (['v1', 'v2'] as $key): ?>
      <div class="card stat">
        <b class="stat-text"><?php echo esc_html(ava_getOption($key)); ?></b>
        <span><?php echo esc_html(ava_getOption($key . 'r')); ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <?php $teamImage = ava_getOption('team_img') ?: ava_getAssetUrl('img/team.webp'); ?>
  <div class="team" role="img" aria-label="<?php echo esc_attr(ava_t('Teamfoto')); ?>"
       style="<?php echo $backgroundStyle($teamImage); ?>"></div>
</div></section>

<section id="mitmachen"><div class="wrap">
  <p class="eyebrow"><?php ava_e('Deine Ringer-Karriere startet hier'); ?></p>
  <h2><?php ava_e('Werde Teil des Teams'); ?></h2>

  <div class="grid g3 plans">
    <?php foreach (ava_getPosts('mitgliedschaft') as $plan): ?>
    <?php $benefits = array_filter(array_map('trim', explode("\n", ava_getMeta($plan->ID, 'vorteile')))); ?>
    <div class="card" tabindex="0" aria-pressed="false">
      <?php if (ava_getMeta($plan->ID, 'badge')): ?>
      <span class="badge"><?php echo esc_html(ava_getMeta($plan->ID, 'badge')); ?></span>
      <?php endif; ?>
      <h3><?php echo esc_html($plan->post_title); ?></h3>
      <div class="price">
        <?php echo esc_html(ava_getMeta($plan->ID, 'preis')); ?>
        <small>/ <?php echo esc_html(ava_getMeta($plan->ID, 'intervall')); ?></small>
      </div>
      <ul>
        <?php foreach ($benefits as $benefit): ?>
        <li><?php echo esc_html($benefit); ?></li>
        <?php endforeach; ?>
      </ul>
      <a class="btn ghost" href="#kontakt"><?php echo esc_html(ava_getMeta($plan->ID, 'button') ?: ava_t('Anmelden')); ?></a>
    </div>
    <?php endforeach; ?>
  </div>

  <script>
  // Selecting a membership card highlights it (click, or Enter/Space when focused).
  document.querySelectorAll('.plans .card').forEach(function (card, _, cards) {
    function select() {
      cards.forEach(function (other) {
        other.classList.toggle('sel', other === card);
        other.setAttribute('aria-pressed', other === card);
      });
    }
    card.addEventListener('click', select);
    card.addEventListener('keydown', function (e) {
      if (e.target === card && (e.key === 'Enter' || e.key === ' ')) {
        e.preventDefault();
        select();
      }
    });
  });
  </script>

  <p class="plans-note"><?php echo esc_html(ava_getOption('plans_note')); ?></p>

  <div class="card probe">
    <div>
      <h3><?php echo esc_html(ava_getOption('probe_titel')); ?></h3>
      <p><?php echo esc_html(ava_getOption('probe_text')); ?></p>
    </div>
    <a class="btn" href="#kontakt"><?php ava_e('Probetraining vereinbaren'); ?></a>
  </div>
</div></section>

<section id="kontakt"><div class="wrap">
  <p class="eyebrow"><?php ava_e('Adresse & Kontakt'); ?></p>
  <h2><?php ava_e('Schreibe uns eine Nachricht'); ?></h2>

  <div class="contact">
    <div class="card">
      <h3><?php echo esc_html(ava_getOption('k_halle')); ?></h3>
      <p class="addr"><?php echo esc_html(ava_getOption('k_adresse')); ?></p>
      <p class="intro"><?php echo esc_html(ava_getOption('k_text')); ?></p>
      <dl>
        <div>
          <dt><?php ava_e('Telefon'); ?></dt>
          <dd><a href="tel:<?php echo esc_attr(preg_replace('/[^+0-9]/', '', ava_getOption('k_tel'))); ?>"><?php echo esc_html(ava_getOption('k_tel')); ?></a></dd>
        </div>
        <div>
          <dt><?php ava_e('E-Mail'); ?></dt>
          <dd><a href="mailto:<?php echo esc_attr(ava_getOption('k_mail')); ?>"><?php echo esc_html(ava_getOption('k_mail')); ?></a></dd>
        </div>
        <?php
        $socialLinks = [];
        if (ava_getOption('k_insta')) {
            $socialLinks[] = '<a href="' . esc_url(ava_getOption('k_insta')) . '" target="_blank" rel="noopener">Instagram</a>';
        }
        if (ava_getOption('k_fb')) {
            $socialLinks[] = '<a href="' . esc_url(ava_getOption('k_fb')) . '" target="_blank" rel="noopener">Facebook</a>';
        }
        ?>
        <?php if ($socialLinks): ?>
        <div>
          <dt><?php ava_e('Social Media'); ?></dt>
          <dd><?php echo implode(' · ', $socialLinks); ?></dd>
        </div>
        <?php endif; ?>
      </dl>
    </div>

    <?php if (ava_getOption('map_img')): ?>
    <a class="map" href="<?php echo esc_url(ava_getOption('map_link')); ?>" target="_blank" rel="noopener"
       aria-label="<?php echo esc_attr(ava_t('Karte öffnen')); ?>"
       style="<?php echo $backgroundStyle(ava_getOption('map_img')); ?>"></a>
    <?php else: ?>
    <?php
    // Interactive OpenStreetMap embed centred on the club's address.
    $lat    = 50.0828453;
    $lon    = 9.0613336;
    $bbox   = ($lon - 0.006) . '%2C' . ($lat - 0.0035) . '%2C' . ($lon + 0.006) . '%2C' . ($lat + 0.0035);
    $mapUrl = 'https://www.openstreetmap.org/export/embed.html?bbox=' . $bbox
            . '&layer=mapnik&marker=' . $lat . '%2C' . $lon;
    ?>
    <div class="map">
      <iframe title="<?php echo esc_attr(ava_t('Karte öffnen')); ?>" loading="lazy"
              referrerpolicy="no-referrer-when-downgrade" src="<?php echo esc_url($mapUrl); ?>"></iframe>
      <a class="map-open" href="<?php echo esc_url(ava_getOption('map_link')); ?>" target="_blank" rel="noopener"><?php ava_e('Karte öffnen'); ?></a>
    </div>
    <?php endif; ?>
  </div>
</div></section>

<section id="partner"><div class="wrap spons">
  <p class="eyebrow"><?php ava_e('Unterstütze uns und werde Teil des Teams'); ?></p>
  <h2><?php ava_e('Unsere Sponsoren & Partner'); ?></h2>

  <div class="carousel">
    <button type="button" class="car-btn prev" aria-label="<?php echo esc_attr(ava_t('Zurück')); ?>">&#8249;</button>
    <div class="car-track">
      <?php foreach (ava_getPosts('sponsor', ['meta_key' => 'gruppe', 'meta_value' => 'partner']) as $sponsor): ?>
      <div class="card">
        <div class="partner-logo"><?php echo $renderSponsorLogo($sponsor); ?></div>
        <h3><?php echo esc_html($sponsor->post_title); ?></h3>
        <small><?php echo esc_html(ava_getMeta($sponsor->ID, 'untertitel')); ?></small>
      </div>
      <?php endforeach; ?>
    </div>
    <button type="button" class="car-btn next" aria-label="<?php echo esc_attr(ava_t('Weiter')); ?>">&#8250;</button>
  </div>
  <script>
  // Partner carousel: arrows scroll one card at a time and are hidden when everything fits.
  document.querySelectorAll('.carousel').forEach(function (carousel) {
    var track = carousel.querySelector('.car-track');
    var prev = carousel.querySelector('.prev');
    var next = carousel.querySelector('.next');

    function step() {
      var item = track.firstElementChild;
      return item ? item.offsetWidth + parseFloat(getComputedStyle(track).columnGap || 0) : track.clientWidth;
    }
    function update() {
      var overflow = track.scrollWidth > track.clientWidth + 1;
      carousel.classList.toggle('has-overflow', overflow);
      prev.disabled = track.scrollLeft <= 1;
      next.disabled = track.scrollLeft + track.clientWidth >= track.scrollWidth - 1;
    }
    prev.addEventListener('click', function () { track.scrollBy({ left: -step(), behavior: 'smooth' }); });
    next.addEventListener('click', function () { track.scrollBy({ left: step(), behavior: 'smooth' }); });
    track.addEventListener('scroll', update, { passive: true });
    window.addEventListener('resize', update);
    update();
  });
  </script>

  <div class="card sp-cta">
    <div>
      <h3><?php echo esc_html(ava_getOption('sp_titel')); ?></h3>
      <p><?php echo esc_html(ava_getOption('sp_text')); ?></p>
    </div>
    <a class="btn" href="#kontakt"><?php ava_e('Sponsor werden'); ?></a>
  </div>
</div></section>

</main>
<?php get_footer();
