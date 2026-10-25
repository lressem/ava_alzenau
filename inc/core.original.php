<?php
if(!defined('ABSPATH'))exit;
function ava_asset($f){return get_template_directory_uri().'/assets/'.$f;}
function ava_days(){return ['Montag','Dienstag','Mittwoch','Donnerstag','Freitag','Samstag','Sonntag'];}
function ava_types(){
 $d=array_combine(ava_days(),ava_days());
 return [
 'sponsor'=>['n'=>['Sponsor','Sponsoren'],'i'=>'dashicons-heart','s'=>['title','thumbnail','page-attributes'],'f'=>[
  'link'=>['Link (URL)','url'],'untertitel'=>['Untertitel (nur Bereich Partner)','text'],
  'gruppe'=>['Anzeige','select',['leiste'=>'Logoleiste oben','partner'=>'Bereich Partner']]]],
 'tabelle'=>['n'=>['Tabelleneintrag','Tabelle (Oberliga)'],'i'=>'dashicons-editor-ol','s'=>['title'],'f'=>[
  'platz'=>['Platz','number'],'diff'=>['Differenz (z. B. +32)','text'],'punkte'=>['Punkte (z. B. 14:4)','text'],'eigener'=>['Eigener Verein hervorheben','check']]],
 'kampftag'=>['n'=>['Kampftag','Kampftage'],'i'=>'dashicons-awards','s'=>['title'],'f'=>[
  'datum'=>['Datum und Uhrzeit','datetime-local'],'heim'=>['Heim oder Auswärts','select',['1'=>'Heimkampf','0'=>'Auswärtskampf']],
  'gegner'=>['Gegner','text'],'ort'=>['Ort','text'],'p_heim'=>['Punkte Heim-Mannschaft (nach dem Kampf)','number'],
  'p_gast'=>['Punkte Gast-Mannschaft (nach dem Kampf)','number'],'ticket'=>['Ticket-Link (optional)','url']]],
 'training'=>['n'=>['Trainingseinheit','Training'],'i'=>'dashicons-clock','s'=>['title'],'f'=>[
  'tag'=>['Wochentag','select',$d],'von'=>['Beginn','time'],'bis'=>['Ende','time'],'coach'=>['Coach(es)','text']]],
 'trainer'=>['n'=>['Trainer*in','Trainer*innen'],'i'=>'dashicons-groups','s'=>['title','thumbnail','page-attributes'],'f'=>[
  'rolle'=>['Rolle (z. B. Trainer Kinder & Jugend)','text'],'zitat'=>['Zitat','text']]],
 'mitgliedschaft'=>['n'=>['Mitgliedschaft','Mitgliedschaften'],'i'=>'dashicons-id','s'=>['title','page-attributes'],'f'=>[
  'preis'=>['Preis (z. B. 45,00 €)','text'],'intervall'=>['Intervall (z. B. jährlich)','text'],'badge'=>['Badge (optional)','text'],
  'vorteile'=>['Leistungen (eine pro Zeile)','textarea'],'button'=>['Button-Text','text'],'top'=>['Hervorgehoben','check']]],
 ];}
function ava_fields(){return [
 'hero_geo'=>['Zeile über der Überschrift','text','Alzenau - Franken - Bayern','Startseite: Hero'],
 'hero_l1'=>['Überschrift Zeile 1','text','Seit 1899.','Startseite: Hero'],
 'hero_l2'=>['Überschrift Zeile 2 (blau)','text','Ringen','Startseite: Hero'],
 'hero_l3'=>['Überschrift Zeile 3','text','in Alzenau.','Startseite: Hero'],
 'hero_lead'=>['Einleitungstext','textarea','Wir machen Kinder stark — und Sportler stärker. Vom Einsteiger bis zur Verbandsrunde. Trainiere mit uns in der Prischoßhalle.','Startseite: Hero'],
 'hero_mp4'=>['Hero-Video MP4','media',ava_asset('video/hero.mp4'),'Startseite: Hero'],
 'hero_webm'=>['Hero-Video WebM (optional)','media',ava_asset('video/hero.webm'),'Startseite: Hero'],
 'hero_poster'=>['Hero-Standbild','media','','Startseite: Hero'],
 'btn1'=>['Button 1 (führt zu Mitmachen)','text','Jetzt mitmachen','Startseite: Hero'],
 'btn2'=>['Button 2 Text','text','Kampftag-Tickets','Startseite: Hero'],
 'btn2_link'=>['Button 2 Link','text','#liga','Startseite: Hero'],
 'video_link'=>['Link für „Video ansehen“ (leer = ausblenden)','url','','Startseite: Hero'],
 'news_shortcode'=>['Instagram-Plugin Shortcode (z. B. [instagram-feed])','text','','News aus dem Verein'],
 'liga_titel'=>['Titel der Tabelle','text','Tabelle (Oberliga)','Liga'],
 'club_name'=>['Vereinsname (für Ergebnisse)','text','Ringer Verein Alzenau','Liga'],
 'club_kurz'=>['Kurzname (nächster Kampftag)','text','RVA','Liga'],
 'trainingsort'=>['Trainingsort','text','Trainingsort: Prischoßhalle, Prischoßstraße 42, 63755 Alzenau.','Training'],
 'trainingshinweis'=>['Hinweis','textarea','Interessierte sind jederzeit zu einem kostenlosen Probetraining eingeladen. Bitte saubere Sportsachen und Hallenschuhe mitbringen.','Training'],
 'verein_p1'=>['Text Absatz 1','textarea','Seit über einem Jahrhundert steht die Athleten-Vereinigung Alzenau 1899 e.V. für fairen Kampfsport, diszipliniertes Training und eine unschlagbare Gemeinschaft. Gegründet 1899, haben wir Generationen von Athleten geformt, von kleinen Bambinis bis hin zu Bundesliga-Athleten.','Verein'],
 'verein_p2'=>['Text Absatz 2','textarea','Unsere Philosophie verbindet klassisches Freistil- und Griechisch-Römisch-Ringen mit modernem Athletiktraining. Doch der sportliche Erfolg ist nur die halbe Medaille: Respekt, Teamgeist und die Förderung der Jugend stehen bei uns an erster Stelle.','Verein'],
 'z1'=>['Zahl 1','text','320+','Verein'],'z1l'=>['Beschriftung 1','text','Mitglieder','Verein'],
 'z2'=>['Zahl 2','text','1899','Verein'],'z2l'=>['Beschriftung 2','text','Gründungsjahr','Verein'],
 'z3'=>['Zahl 3','text','12','Verein'],'z3l'=>['Beschriftung 3','text','Titel','Verein'],
 'z4'=>['Zahl 4','text','100','Verein'],'z4l'=>['Beschriftung 4','text','Kinder & Jugendliche','Verein'],
 'v1'=>['1. Vorstand (Name)','text','Alexander Staab','Verein'],'v1r'=>['1. Vorstand (Rolle)','text','1. Vorstand','Verein'],
 'v2'=>['2. Vorstand (Name)','text','Sebastian Amreihn','Verein'],'v2r'=>['2. Vorstand (Rolle)','text','2. Vorstand','Verein'],
 'team_img'=>['Teamfoto','media','','Verein'],
 'plans_note'=>['Fußnote Mitgliedschaften','text','* Bedingungen siehe Hallenordnung.','Mitmachen'],
 'probe_titel'=>['Probetraining Überschrift','text','Noch unsicher? Komm zum Probetraining!','Mitmachen'],
 'probe_text'=>['Probetraining Text','textarea','Egal welches Alter, ob Junge oder Mädchen, absoluter Anfänger oder Wiedereinsteiger. Vereinbare jetzt dein unverbindliches Probetraining.','Mitmachen'],
 'k_halle'=>['Name der Halle','text','Prischoßhalle Alzenau','Kontakt'],
 'k_adresse'=>['Adresse','text','Prischoßstraße 42, 63755 Alzenau','Kontakt'],
 'k_text'=>['Text','textarea','Du hast Fragen zur Mitgliedschaft, zu den Trainingszeiten oder zu unseren Kampftagen? Melde dich direkt bei uns. Vorstand und Trainerteam helfen gerne weiter.','Kontakt'],
 'k_tel'=>['Telefon','text','+49 (0) 6023 987654','Kontakt'],
 'k_mail'=>['E-Mail','text','kontakt@ringerverein-alzenau.de','Kontakt'],
 'k_insta'=>['Instagram-Link','url','','Kontakt'],'k_fb'=>['Facebook-Link','url','','Kontakt'],
 'map_img'=>['Kartenbild','media','','Kontakt'],
 'map_link'=>['Link der Karte','url','https://www.openstreetmap.org/search?query=Prischo%C3%9Fstra%C3%9Fe%2042%20Alzenau','Kontakt'],
 'sp_titel'=>['Sponsoring Überschrift','text','Interessiert an reichweitenstarken Werbeflächen?','Partner'],
 'sp_text'=>['Sponsoring Text','text','Bei Interesse an einem Sponsoring melden Sie sich gerne jederzeit bei uns.','Partner'],
 'copy'=>['Copyright-Zeile','text','© 2026 Ringer Verein Alzenau e.V. Alle Rechte vorbehalten.','Footer'],
];}
function ava_o($k){static $o=null;if($o===null){$o=get_option('ava_opts',[]);if(!is_array($o))$o=[];}
 if(isset($o[$k]))return $o[$k];$f=ava_fields();return $f[$k][2]??'';}
function ava_m($id,$k){return get_post_meta($id,$k,true);}
function ava_img($id,$size='ava-card'){$u=get_the_post_thumbnail_url($id,$size);if($u)return $u;$d=ava_m($id,'_demo');return $d?ava_asset('img/'.$d):'';}
function ava_posts($t,$a=[]){return get_posts(array_merge(['post_type'=>$t,'numberposts'=>-1,'orderby'=>['menu_order'=>'ASC','title'=>'ASC'],'suppress_filters'=>false],$a));}
function ava_field($name,$f,$v){$t=$f[1];$n=esc_attr($name);
 if($t==='textarea')echo '<textarea name="'.$n.'" rows="4" class="large-text">'.esc_textarea($v).'</textarea>';
 elseif($t==='select'){echo '<select name="'.$n.'">';foreach($f[2] as $k=>$l)echo '<option value="'.esc_attr($k).'"'.selected((string)$v,(string)$k,false).'>'.esc_html($l).'</option>';echo '</select>';}
 elseif($t==='check')echo '<input type="hidden" name="'.$n.'" value=""><label><input type="checkbox" name="'.$n.'" value="1"'.checked($v,'1',false).'> Ja</label>';
 elseif($t==='media')echo '<input type="url" class="large-text" name="'.$n.'" value="'.esc_attr($v).'"> <button type="button" class="button ava-media">Aus Mediathek wählen</button>';
 else echo '<input type="'.esc_attr($t).'" name="'.$n.'" value="'.esc_attr($v).'" class="regular-text">';}
function ava_clean($f,$v){$t=$f[1];
 if($t==='url'||$t==='media')return esc_url_raw($v);
 if($t==='textarea')return sanitize_textarea_field($v);
 if($t==='check')return $v==='1'?'1':'';
 if($t==='select'){$k=array_map('strval',array_keys($f[2]));return in_array((string)$v,$k,true)?(string)$v:$k[0];}
 return sanitize_text_field($v);}
add_action('init',function(){foreach(ava_types() as $t=>$c)register_post_type($t,['labels'=>['name'=>$c['n'][1],'singular_name'=>$c['n'][0],'add_new_item'=>$c['n'][0].' hinzufügen','edit_item'=>$c['n'][0].' bearbeiten','all_items'=>'Alle '.$c['n'][1]],'public'=>false,'show_ui'=>true,'menu_icon'=>$c['i'],'supports'=>$c['s'],'rewrite'=>false,'show_in_rest'=>false]);});
add_action('add_meta_boxes',function(){foreach(ava_types() as $t=>$c)add_meta_box('ava_f','Details',function($p)use($c){
 wp_nonce_field('ava_save','ava_n');echo '<table class="form-table">';
 foreach($c['f'] as $k=>$f){echo '<tr><th>'.esc_html($f[0]).'</th><td>';ava_field('ava_m['.$k.']',$f,ava_m($p->ID,$k));echo '</td></tr>';}
 echo '</table>';},$t);});
add_action('save_post',function($id){
 if(!isset($_POST['ava_n'])||!wp_verify_nonce($_POST['ava_n'],'ava_save')||(defined('DOING_AUTOSAVE')&&DOING_AUTOSAVE)||!current_user_can('edit_post',$id))return;
 $ty=ava_types();$t=get_post_type($id);if(!isset($ty[$t]))return;
 $in=isset($_POST['ava_m'])?wp_unslash($_POST['ava_m']):[];
 foreach($ty[$t]['f'] as $k=>$f)update_post_meta($id,$k,ava_clean($f,isset($in[$k])?$in[$k]:''));});
add_action('admin_menu',function(){add_menu_page('Vereinsdaten','Vereinsdaten','manage_options','ava','ava_page','dashicons-admin-home',3);});
add_action('admin_init',function(){register_setting('ava','ava_opts',['sanitize_callback'=>'ava_clean_opts']);});
add_action('admin_enqueue_scripts',function($h){if($h==='toplevel_page_ava')wp_enqueue_media();});
function ava_clean_opts($in){$o=[];foreach(ava_fields() as $k=>$f)$o[$k]=ava_clean($f,isset($in[$k])?$in[$k]:'');return $o;}
function ava_page(){$g='';
 echo '<div class="wrap"><h1>Vereinsdaten</h1><p>Texte, Zahlen und Links der Startseite. Listen (Trainer, Training, Tabelle, Kampftage, Mitgliedschaften, Sponsoren) pflegst du in den eigenen Menüpunkten links.</p><form method="post" action="options.php">';
 settings_fields('ava');
 foreach(ava_fields() as $k=>$f){
  if($f[3]!==$g){if($g)echo '</table>';$g=$f[3];echo '<h2>'.esc_html($g).'</h2><table class="form-table">';}
  echo '<tr><th>'.esc_html($f[0]).'</th><td>';ava_field('ava_opts['.$k.']',$f,ava_o($k));echo '</td></tr>';}
 echo '</table>';submit_button();echo '</form></div>';
 echo '<script>jQuery(function($){$(".ava-media").on("click",function(e){e.preventDefault();var i=$(this).prev();var f=wp.media({multiple:false});f.on("select",function(){i.val(f.state().get("selection").first().toJSON().url)});f.open();});});</script>';}
add_action('after_switch_theme','ava_seed');
function ava_seed(){
 if(get_option('ava_seeded'))return;
 $mk=function($t,$title,$meta,$order=0){wp_insert_post(['post_type'=>$t,'post_title'=>$title,'post_status'=>'publish','menu_order'=>$order,'meta_input'=>$meta]);};
 foreach(['Vita Sports','Hofladen Erdbeer Ranch','David Stelter KFZ','Kost Bar','Gasthaus Krone','Stadtwerke Alzenau','Sponsor 7'] as $i=>$n)
  $mk('sponsor',$n,['gruppe'=>'leiste','_demo'=>'logos/logo-'.($i+1).'.webp'],$i);
 foreach([['Hessischer Ringer-Verband','Landesverband'],['Deutscher Ringer-Bund','Bundesverband'],['Sponsoren','Unternehmen']] as $i=>$p)
  $mk('sponsor',$p[0],['gruppe'=>'partner','untertitel'=>$p[1]],10+$i);
 foreach([['RSC Mömbris',1,'+48','16:2',''],['RV Alzenau',2,'+32','14:4','1'],['SC Kleinostheim II',3,'+12','12:6',''],['KSC Goldbach',4,'+4','10:8',''],['ASV Schaafheim 18',5,'-6','6:12',''],['SG Arheilgen',6,'-78','2:16','']] as $r)
  $mk('tabelle',$r[0],['platz'=>$r[1],'diff'=>$r[2],'punkte'=>$r[3],'eigener'=>$r[4]]);
 $mk('kampftag','Nächster Kampftag',['datum'=>date('Y-m-d\T19:30',strtotime('next saturday')),'heim'=>'1','gegner'=>'KSC Goldbach','ort'=>'Prischoßhalle Alzenau']);
 $mk('kampftag','Letzter Kampftag',['datum'=>date('Y-m-d\T19:30',strtotime('last saturday')),'heim'=>'0','gegner'=>'SG Arheilgen 06','ort'=>'Arheilgen','p_heim'=>'6','p_gast'=>'24']);
 $k='Heiko, Matze, Sebi & Sebastian';
 foreach([['Kinder & Jugendtraining','Dienstag','17:30','19:00',$k],['Training 1. Mannschaft RWG','Dienstag','19:00','21:00','Tim & Ralf'],
  ['Jugendintensiv-Training','Mittwoch','18:00','19:00',$k],['Athletik-Training','Mittwoch','19:00','21:00','Tim'],
  ['Bambini-Training','Freitag','17:00','18:15','Tamina'],['Kinder & Jugendtraining','Freitag','17:30','19:00',$k],['Training 1. Mannschaft RWG','Freitag','19:30','21:30','Tim & Ralf']] as $t)
  $mk('training',$t[0],['tag'=>$t[1],'von'=>$t[2],'bis'=>$t[3],'coach'=>$t[4]]);
 foreach([['Tim Heiniger','Trainer 1. Mannschaft','Jede Übung kann auch durch Burpees ersetzt werden!'],['Heiko Bork','Trainer Kinder & Jugend','Zitat folgt'],['Sebastian Amreihn','Trainer Kinder & Jugend','Zitat folgt'],['Sebastian','Trainer Kinder & Jugend','Zitat folgt'],['Matthias Amreihn','Trainer Kinder & Jugend','Zitat folgt'],['Tamina Staab','Trainerin Bambinis','Zitat folgt']] as $i=>$t)
  $mk('trainer',$t[0],['rolle'=>$t[1],'zitat'=>$t[2],'_demo'=>'coach-'.($i+1).'.webp'],$i);
 $mk('mitgliedschaft','Jugend / Schüler',['preis'=>'45,00 €','intervall'=>'jährlich','vorteile'=>"Zutritt zum Jugendtraining\nLizenzen für offizielle Wettkämpfe\nFreitickets für Heimkämpfe der 1. Mannschaft\nFreizeitaktivitäten wie Zeltlager und Wandern",'button'=>'Nachwuchs anmelden'],1);
 $mk('mitgliedschaft','Aktive Ringer',['preis'=>'45,00 €','intervall'=>'monatlich','badge'=>'Wettkämpfer','top'=>'1','vorteile'=>"Voller Zutritt zu allen Trainingseinheiten\nProfessionelle Wettkampfbetreuung\nNutzung des Fitness- & Kraftraums*\nTrainingsanzüge inklusive",'button'=>'Jetzt anmelden'],2);
 $mk('mitgliedschaft','Familienmitgliedschaft',['preis'=>'100,00 €','intervall'=>'jährlich','vorteile'=>"Unterstützung der Nachwuchsarbeit\nEinladung zu allen Vereinsevents\nZugang zum Athletik-Training\nNutzung des Fitness- & Kraftraums*",'button'=>'Mitglied werden'],3);
 foreach(['Impressum','Datenschutz','Vereinssatzung'] as $p)if(!get_page_by_path(sanitize_title($p)))wp_insert_post(['post_type'=>'page','post_title'=>$p,'post_status'=>'publish','post_content'=>'Bitte Inhalt ergänzen.']);
 update_option('ava_seeded',1);
}
