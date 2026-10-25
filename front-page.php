<?php
get_header();
$now=date('Y-m-d\TH:i');
$next=ava_getPosts('kampftag',['numberposts'=>1,'meta_key'=>'datum','orderby'=>'meta_value','order'=>'ASC','meta_query'=>[['key'=>'datum','value'=>$now,'compare'=>'>=','type'=>'CHAR']]]);
$past=ava_getPosts('kampftag',['numberposts'=>10,'meta_key'=>'datum','orderby'=>'meta_value','order'=>'DESC','meta_query'=>[['key'=>'datum','value'=>$now,'compare'=>'<','type'=>'CHAR']]]);
$last=null;foreach($past as $p){if(ava_getMeta($p->ID,'p_heim')!==''){$last=$p;break;}}
$club=ava_getOption('club_name');
$ig='<svg viewBox="0 0 30 30" aria-label="Instagram" role="img"><defs><radialGradient id="ig" cx="30%" cy="105%" r="120%"><stop offset="0" stop-color="#FFCC00"/><stop offset=".25" stop-color="#FE4A05"/><stop offset=".55" stop-color="#FF005F"/><stop offset="1" stop-color="#820BFF"/></radialGradient></defs><rect width="30" height="30" rx="8" fill="url(#ig)"/><rect x="6.5" y="6.5" width="17" height="17" rx="5" fill="none" stroke="#fff" stroke-width="2"/><circle cx="15" cy="15" r="4" fill="none" stroke="#fff" stroke-width="2"/><circle cx="20.2" cy="9.8" r="1.2" fill="#fff"/></svg>';
$logo=function($p,$cls='lg'){$u=ava_getPostImageUrl($p->ID,'medium');$l=ava_getMeta($p->ID,'link');
 $x=$u?'<div class="'.$cls.'" role="img" aria-label="'.esc_attr($p->post_title).'" style="background:url('.esc_url($u).') center/contain no-repeat"></div>':'<div class="logo-ph">LOGO</div>';
 return $l?'<a href="'.esc_url($l).'" rel="noopener" target="_blank">'.$x.'</a>':$x;};
?>
<main id="top">
<section class="hero">
 <?php if(ava_getOption('hero_mp4')):?>
 <video autoplay muted loop playsinline preload="auto" aria-hidden="true">
  <?php if(ava_getOption('hero_webm')):?><source src="<?php echo esc_url(ava_getOption('hero_webm'));?>" type="video/webm"><?php endif;?>
  <source src="<?php echo esc_url(ava_getOption('hero_mp4'));?>" type="video/mp4">
 </video>
 <?php endif;?>
 <div class="wrap">
  <div class="where geo"><?php echo esc_html(ava_getOption('hero_geo'));?></div>
  <h1><?php echo esc_html(ava_getOption('hero_l1'));?><span><?php echo esc_html(ava_getOption('hero_l2'));?></span><?php echo esc_html(ava_getOption('hero_l3'));?></h1>
  <p class="lead"><?php echo esc_html(ava_getOption('hero_lead'));?></p>
  <div class="cta">
   <a class="btn" href="#mitmachen"><?php echo esc_html(ava_getOption('btn1'));?></a>
   <a class="btn" href="<?php echo esc_attr(ava_getOption('btn2_link'));?>"><?php echo esc_html(ava_getOption('btn2'));?></a>
   <?php if(ava_getOption('video_link')):?><a class="vid" href="<?php echo esc_url(ava_getOption('video_link'));?>" target="_blank" rel="noopener"><i></i><?php ava_e('Video ansehen');?></a><?php endif;?>
  </div>
 </div>
</section>

<?php $sp=ava_getPosts('sponsor',['meta_key'=>'gruppe','meta_value'=>'leiste']);if($sp):?>
<div class="strip"><div class="wrap">
 <span class="t"><?php ava_e('Unsere Sponsoren & Partner');?></span>
 <?php foreach($sp as $p)echo $logo($p);?>
</div></div>
<?php endif;?>

<section id="aktuelles" class="news"><div class="wrap">
 <p class="eyebrow"><?php ava_e('News aus dem Verein');?><?php echo $ig;?></p>
  <h2><?php ava_e('Aktuelles & Kampftage');?></h2>
 <?php if(ava_getOption('news_shortcode')):?>
  <div class="insta-wrap"><?php echo do_shortcode(wp_kses_post(ava_getOption('news_shortcode')));?></div>
 <?php else:?>
 <div class="grid g3">
  <?php foreach([['19. September 2026','Fight Day gegen Großzimmern','Erster Auswärtskampf gegen den AV 05 Großzimmern am Samstag, den 19.09.2026 um 19:00 Uhr.'],['08. August 2026','Jugendzeltlager','Drei Tage voller Spaß, Sport und Teambuilding. Anmeldungen für Kinder von 6 bis 16 Jahren sind ab sofort freigeschaltet.'],['12.–16. Mai 2026','Europameisterschaft','Irma Neumann wurde vom Deutschen Ringer-Bund für die Teilnahme an der Europameisterschaft nominiert.']] as $i=>$n):?>
  <article class="card"><div class="img" role="img" aria-label="<?php echo esc_attr(ava_t('Beitragsbild'));?>" style="background:url(<?php echo esc_url(ava_getAssetUrl('img/news-'.($i+1).'.webp'));?>) center/cover"></div><div class="date"><?php echo esc_html(ava_t($n[0]));?></div><h3><?php echo esc_html(ava_t($n[1]));?></h3><p><?php echo esc_html(ava_t($n[2]));?></p></article>
  <?php endforeach;?>
 </div>
 <p class="note"><?php ava_e('Platzhalter. Zum Aktivieren des Instagram-Feeds den Shortcode des Plugins unter „Vereinsdaten“ eintragen.');?></p>
 <?php endif;?>
</div></section>

<section id="liga"><div class="wrap">
 <p class="eyebrow"><?php ava_e('Sei hautnah dabei');?></p>
  <h2><?php ava_e('Liga & Ergebnisse');?></h2>
 <div class="liga">
  <div class="card tbl">
   <h3 style="margin-bottom:12px"><?php echo esc_html(ava_getOption('liga_titel'));?></h3>
   <table><thead><tr><th><?php ava_e('Pl.');?></th><th><?php ava_e('Verein');?></th><th><?php ava_e('Diff.');?></th><th><?php ava_e('Punkte');?></th></tr></thead><tbody>
   <?php foreach(ava_getPosts('tabelle',['meta_key'=>'platz','orderby'=>'meta_value_num','order'=>'ASC']) as $p):?>
    <tr<?php echo ava_getMeta($p->ID,'eigener')?' class="me"':'';?>><td><?php echo esc_html(ava_getMeta($p->ID,'platz'));?></td><td><?php echo esc_html($p->post_title);?></td><td><?php echo esc_html(ava_getMeta($p->ID,'diff'));?></td><td><?php echo esc_html(ava_getMeta($p->ID,'punkte'));?></td></tr>
   <?php endforeach;?>
   </tbody></table>
  </div>
  <div class="card match">
   <?php if($next):$n=$next[0];$ts=strtotime(ava_getMeta($n->ID,'datum'));$h=ava_getMeta($n->ID,'heim')==='1';$g=ava_getMeta($n->ID,'gegner');?>
   <span class="tag"><?php ava_e('Nächster Kampftag');?></span>
   <div class="vs"><div><?php echo esc_html($h?ava_getOption('club_kurz'):$g);?><small><?php ava_e($h?'Heim':'Gast');?></small></div><i>VS</i><div><?php echo esc_html($h?$g:ava_getOption('club_kurz'));?><small><?php ava_e($h?'Gast':'Heim');?></small></div></div>
   <div><?php echo esc_html(wp_date(ava_t('l, j. F Y'),$ts).' · '.trim(wp_date(ava_t('H:i'),$ts).' '.ava_t('Uhr')));?><br><span style="color:var(--mute)"><?php ava_e('Ort:');?> <?php echo esc_html(ava_getMeta($n->ID,'ort'));?></span></div>
   <?php if(ava_getMeta($n->ID,'ticket')):?><p style="margin:18px 0 0"><a class="btn gold" href="<?php echo esc_url(ava_getMeta($n->ID,'ticket'));?>"><?php ava_e('Tickets');?></a></p><?php endif;?>
   <?php else:?><span class="tag"><?php ava_e('Nächster Kampftag');?></span><p style="color:var(--mute);margin-top:24px"><?php ava_e('Der nächste Kampftag wird bald bekanntgegeben.');?></p><?php endif;?>
   <?php if($last):$h=ava_getMeta($last->ID,'heim')==='1';$g=ava_getMeta($last->ID,'gegner');$a=$h?$club:$g;$b=$h?$g:$club;?>
   <div class="last"><?php ava_e('Letztes Ergebnis');?><br><?php echo esc_html($a);?> <b><?php echo esc_html(ava_getMeta($last->ID,'p_heim').' : '.ava_getMeta($last->ID,'p_gast'));?></b> <?php echo esc_html($b);?></div>
   <?php endif;?>
  </div>
 </div>
</div></section>

<section id="training"><div class="wrap">
 <p class="eyebrow"><?php ava_e('Ab auf die Matte');?></p>
  <h2><?php ava_e('Trainingszeiten & Trainer*innen');?></h2>
 <?php $by=[];foreach(ava_getPosts('training') as $p)$by[ava_getMeta($p->ID,'tag')][]=$p;?>
 <div class="grid g3 days">
  <?php foreach(ava_getWeekDays() as $d){if(empty($by[$d]))continue;$l=$by[$d];usort($l,function($x,$y){return strcmp(ava_getMeta($x->ID,'von'),ava_getMeta($y->ID,'von'));});?>
  <div class="card"><h3><?php ava_e($d);?></h3>
   <?php foreach($l as $p):?><div class="slot"><b><?php echo esc_html($p->post_title);?></b><time><?php echo esc_html(ava_getMeta($p->ID,'von').'–'.ava_getMeta($p->ID,'bis'));?></time><span><?php ava_e('Coach:');?> <?php echo esc_html(ava_getMeta($p->ID,'coach'));?></span></div><?php endforeach;?>
  </div>
  <?php }?>
 </div>
 <div class="card place" style="margin-top:20px"><p><b style="color:var(--tx)"><?php echo esc_html(ava_getOption('trainingsort'));?></b><br><?php echo esc_html(ava_getOption('trainingshinweis'));?></p></div>
 <div class="grid g6" style="margin-top:36px">
  <?php foreach(ava_getPosts('trainer') as $p):?>
  <div class="card coach"><div class="ph" role="img" aria-label="<?php echo esc_attr($p->post_title);?>" style="background:url(<?php echo esc_url(ava_getPostImageUrl($p->ID,'large'));?>) center/contain no-repeat"></div><div class="t"><small><?php echo esc_html(ava_getMeta($p->ID,'rolle'));?></small><h3><?php echo esc_html($p->post_title);?></h3><?php if(ava_getMeta($p->ID,'zitat')):?><p><?php echo esc_html(ava_getMeta($p->ID,'zitat'));?></p><?php endif;?></div></div>
  <?php endforeach;?>
 </div>
</div></section>

<section id="verein"><div class="wrap about">
 <div>
  <p class="eyebrow"><?php ava_e('Wir machen Kinder stark');?></p>
    <h2><?php ava_e('Unser Verein');?></h2>
  <p><?php echo esc_html(ava_getOption('verein_p1'));?></p>
  <p><?php echo esc_html(ava_getOption('verein_p2'));?></p>
  <div class="stats">
   <?php foreach([1,2,3,4] as $i):?><div class="card stat<?php echo $i>2?' gold':'';?>"><b><?php echo esc_html(ava_getOption('z'.$i));?></b><span><?php echo esc_html(ava_getOption('z'.$i.'l'));?></span></div><?php endforeach;?>
   <?php foreach(['v1','v2'] as $v):?><div class="card stat"><b style="font-size:26px;padding-top:8px"><?php echo esc_html(ava_getOption($v));?></b><span><?php echo esc_html(ava_getOption($v.'r'));?></span></div><?php endforeach;?>
  </div>
 </div>
 <div class="team" role="img" aria-label="<?php echo esc_attr(ava_t('Teamfoto'));?>" style="background:url(<?php echo esc_url(ava_getOption('team_img')?:ava_getAssetUrl('img/team.webp'));?>) center/cover"></div>
</div></section>

<section id="mitmachen"><div class="wrap">
 <p class="eyebrow"><?php ava_e('Deine Ringer-Karriere startet hier');?></p>
  <h2><?php ava_e('Werde Teil des Teams');?></h2>
 <div class="grid g3 plans">
  <?php   foreach(ava_getPosts('mitgliedschaft') as $p):?>
  <div class="card" tabindex="0" aria-pressed="false"><?php if(ava_getMeta($p->ID,'badge')):?><span class="badge"><?php echo esc_html(ava_getMeta($p->ID,'badge'));?></span><?php endif;?>
   <h3><?php echo esc_html($p->post_title);?></h3>
   <div class="price"><?php echo esc_html(ava_getMeta($p->ID,'preis'));?> <small>/ <?php echo esc_html(ava_getMeta($p->ID,'intervall'));?></small></div>
   <ul><?php foreach(array_filter(array_map('trim',explode("\n",ava_getMeta($p->ID,'vorteile')))) as $li)echo '<li>'.esc_html($li).'</li>';?></ul>
   <a class="btn ghost" href="#kontakt"><?php echo esc_html(ava_getMeta($p->ID,'button')?:ava_t('Anmelden'));?></a>
  </div>
  <?php endforeach;?>
 </div>
 <script>
 document.querySelectorAll('.plans .card').forEach(function(c,_,all){
  function pick(){all.forEach(function(o){o.classList.toggle('sel',o===c);o.setAttribute('aria-pressed',o===c);});}
  c.addEventListener('click',pick);
  c.addEventListener('keydown',function(e){if(e.target===c&&(e.key==='Enter'||e.key===' ')){e.preventDefault();pick();}});
 });
 </script>
 <p style="font-size:12px;color:var(--mute)"><?php echo esc_html(ava_getOption('plans_note'));?></p>
 <div class="card probe"><div><h3><?php echo esc_html(ava_getOption('probe_titel'));?></h3><p><?php echo esc_html(ava_getOption('probe_text'));?></p></div><a class="btn" href="#kontakt"><?php ava_e('Probetraining vereinbaren');?></a></div>
</div></section>

<section id="kontakt"><div class="wrap">
 <p class="eyebrow"><?php ava_e('Adresse & Kontakt');?></p>
  <h2><?php ava_e('Schreibe uns eine Nachricht');?></h2>
 <div class="contact">
  <div class="card">
   <h3><?php echo esc_html(ava_getOption('k_halle'));?></h3>
   <p style="color:var(--mute);margin:6px 0 0"><?php echo esc_html(ava_getOption('k_adresse'));?></p>
   <p style="margin:20px 0 0;color:#c4cad4"><?php echo esc_html(ava_getOption('k_text'));?></p>
   <dl>
    <div><dt><?php ava_e('Telefon');?></dt><dd><a href="tel:<?php echo esc_attr(preg_replace('/[^+0-9]/','',ava_getOption('k_tel')));?>"><?php echo esc_html(ava_getOption('k_tel'));?></a></dd></div>
    <div><dt><?php ava_e('E-Mail');?></dt><dd><a href="mailto:<?php echo esc_attr(ava_getOption('k_mail'));?>"><?php echo esc_html(ava_getOption('k_mail'));?></a></dd></div>
    <?php if(ava_getOption('k_insta')||ava_getOption('k_fb')):?><div><dt><?php ava_e('Social Media');?></dt><dd>
     <?php if(ava_getOption('k_insta'))echo '<a href="'.esc_url(ava_getOption('k_insta')).'" target="_blank" rel="noopener">Instagram</a>';?>
     <?php if(ava_getOption('k_insta')&&ava_getOption('k_fb'))echo ' · ';?>
     <?php if(ava_getOption('k_fb'))echo '<a href="'.esc_url(ava_getOption('k_fb')).'" target="_blank" rel="noopener">Facebook</a>';?>
    </dd></div><?php endif;?>
   </dl>
  </div>
  <a class="map" href="<?php echo esc_url(ava_getOption('map_link'));?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr(ava_t('Karte öffnen'));?>" style="background:url(<?php echo esc_url(ava_getOption('map_img')?:ava_getAssetUrl('img/map.webp'));?>) center/cover"></a>
 </div>
</div></section>

<section id="partner"><div class="wrap spons">
 <p class="eyebrow"><?php ava_e('Unterstütze uns und werde Teil des Teams');?></p>
  <h2><?php ava_e('Unsere Sponsoren & Partner');?></h2>
 <div class="grid g3">
  <?php foreach(ava_getPosts('sponsor',['meta_key'=>'gruppe','meta_value'=>'partner']) as $p):?>
  <div class="card"><div style="display:flex;justify-content:center"><?php echo $logo($p);?></div><h3><?php echo esc_html($p->post_title);?></h3><small><?php echo esc_html(ava_getMeta($p->ID,'untertitel'));?></small></div>
  <?php endforeach;?>
 </div>
 <div class="card sp-cta" style="text-align:left"><div><h3><?php echo esc_html(ava_getOption('sp_titel'));?></h3><p><?php echo esc_html(ava_getOption('sp_text'));?></p></div><a class="btn" href="#kontakt"><?php ava_e('Sponsor werden');?></a></div>
</div></section>
</main>
<?php get_footer();
