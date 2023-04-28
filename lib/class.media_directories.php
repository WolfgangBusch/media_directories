<?php
/*
 * Media Directories AddOn
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version April 2023
 */
#
class media_directories {
#
#  Basisfunktionen
#     glob_recursive($base)
#     sample_media_dirs()
#     get_mediatype($mediapath)
#     sample_media_mediadirs()
#     file_from_url($medurl)
#     get_mediapath($mediatype)
#     mediapath_from_url($medurl)
#     is_image($file)
#     is_image_url($medurl)
#     html_image($url,$width,$text)
#     html_link($url,$target,$text)
#     html_showlink($medurl,$widtar,$text)
#     file_selection($slice_id,$rexval,$medurl,$widtar,$text)
#  Editor-Plugins
#     plugin_paths($editor)
#  Medienkategorien
#     set_media_category($mediatype)
#     find_media_category($mediatype)
#     delete_media_category($mediatype)
#  Mediendatei-Auswahl
#     cache_image_path($file,$type_id)
#     cache_image($file,$type_id,$renew)
#     cache_images_for_deletion($file,$type_id)
#     menue_datei($rexval,$mediapath,$selfile,$filter)
#     menue_ordner($mediapath,$filter)
#  Module
#     insert_button()
#     select_form($slice_id,$rexval,$medurl,$widtar,$text)
#     show_link($medurl,$widtar,$text)
#
# --- Konstanten
const this_addon=__CLASS__;           // Name des AddOns
const def_type  ='default';           // Name des Medientyps zum Medienordner 'media'
const cache_type='CACHE:'.__CLASS__;  // Medientyp fuer die Cache-Bilder
const link_col  ='rgb(40,120,180)';   // Linkfarbe in Auswahlmenues
const inp_url   =100;                 // Input-Feld-Breite im Datei-Konfig-Menue (URL)
const inp_lnktxt=50;                  // Input-Feld-Breite ... (Untertitel/Linktext)
const cat_marker='ZZmedia:';          // Namensanfang einer erstellten Top-Medienkategorie
#     Parameternamen fuer das Datei-Konfigurationsmenue
const rexval_id ='value_id';          // Name des URL-Parameters fuer die REX-Variable
const slice_id  ='slice_id';          // Name des URL-Parameters fuer die Slice-Id
const medurl_id ='media_url';         // Id des <input>-Feldes zur Aufnahme des Medien-URLs
const widtar_id ='widtar_media_url';  // Id des <input>-Feldes zur Aufnahme von Bildbreite/Zielseite
const lnktxt_id ='lnktxt_media_url';  // Id des <input>-Feldes zur Aufnahme von Untertitel/Linktext
const shwlnk_id ='shwlnk_id';         // Id des inline-Blocks, der Bild bzw. Dateilink umfasst
const widtar_ti ='widtar_ti';         // Zellen_id 'Bildbreite/Zielseite'
const widtar_de ='widtar_de';         // Zellen-Id Beschreibung Bildbreite/Zielseite
const lnktxt_ti ='lnktxt_ti';         // Zellen-Id 'Untertitel/Linktext'
#     Parameternamen fuer das Beispiel-Menue
const xample_val=99;                  // "REX-Variable" fuer das Beispiel
const xample_id ='xample_id';         // Id des div-Containers zur Aufnahme des Beispiel-Ergebnisses
#     Parameternamen fuer das Datei-Suchmenue
const widpreview=300;                 // max. Bildbreite bei der Vorschau
const widover   =150;                 // Bildbreite bei 'onmouseover' im Auswahlmenue
const widout    =50;                  // Bildbreite bei 'onmouseout'  im Auswahlmenue
const sel_name  ='sel_name';          // Select-Menue-Name zur Medienordner-Auswahl
const filt_name ='filt_name';         // Input-Feld-Name zum Dateiname-Filter
#     Beschriftungen fuer das Eigenschaften-Formular
const button_text ='Einfügen';        // Beschriftung Einfuegen-Button
const cancel_text ='Abbrechen';       // Beschriftung Abbrechen-Button
const button_title='Einfügen eines Bildes bzw. eines Links auf eine Datei';
const prop_title  ='Bild/Mediendatei: Eigenschaften';            // Title-Bar
const prop_button ='Auswahl eines Bildes / einer Mediendatei';   // Auswahl-Button
const prop_width  ='Bildbreite';      // fuer Bilder
const prop_pix    ='Pixel';
const prop_tab    ='Zielseite';       // fuer Mediendateien
const prop_target ='Anzeige in einem neuen Browser-Tab';
const prop_subtit ='Untertitel';      // fuer Bilder
const prop_lnktxt ='Linktext';        // fuer Mediendateien
const prop_ansicht='Ansicht (Bilder ggf. verkleinert auf max. '.self::widpreview.' Pixel):';
#     Plugin-Ordnernamen / AddOn-Id und HTML-Editoren-Versionen
const plug_cke    ='ckeditor';
const vers_cke    =4;
const plug_tiny   ='tinymce5';
const vers_tiny   =5;
const plug_redac  ='redactor';
const vers_redac  =3;
#
# --------------------------------------------------- Basisfunktionen
public static function glob_recursive($base) {
   #   Rueckgabe aller Unterordner eines Unterordners des Medienordners bzw. aller
   #   Unterordner des Medienordners selbst in Form eines nummerierten Arrays
   #   (Nummerierung ab 0). Der Basisordner selbst wird mit zurueck gegeben und hat
   #   immer die Nummer 0. Jeder Unterordnung hat die Form 'ordn0/ordn1/...' bzw.
   #   'ordn0', d.h. er endet nicht mit einem '/'.
   #   Die function wird rekursiv aufgerufen.
   #   $base           Name des Basisordner,
   #                   im Falle des Medienordners selbst: basename(rex_url::media())
   #   benutzte functions:
   #      self::glob_recursive($base)
   #
   $dirs=glob($base,GLOB_NOCHECK);
   if(!is_array($dirs)) $dirs=array();
   #
   # --- damit glob auch im Backend funktioniert: Pfadanfang = rex_path::base()
   $BASE=rex_path::base();
   $len=strlen($BASE);
   $subdirs=glob($BASE.$base.DIRECTORY_SEPARATOR.'*',GLOB_ONLYDIR);
   if(!is_array($subdirs)) return $dirs;
   #
   # --- Pfadanfang wieder abschneiden
   for($i=0;$i<count($subdirs);$i=$i+1) $subdirs[$i]=substr($subdirs[$i],$len);
   foreach($subdirs as $dir):
      $subsubdirs=self::glob_recursive($dir);
      $dirs=array_merge($dirs,$subsubdirs);
      endforeach;
   return $dirs;
   }
public static function sample_media_dirs() {
   #   Rueckgabe aller Unterordner des Medienordners sowie des Medienordners selbst
   #   in Form eines nummerierten Arrays (Nummerierung ab 1). Der Medienordner hat
   #   die Nummer 1. Jeder Unterordner hat eine dieser Formen:
   #      'media'                   (Medienordner selbst)
   #      'media/ordn1'             (erster Ordnung)
   #      'media/ordn1/ordn12'      (zweiter Ordnung)
   #      . . .
   #   benutzte functions:
   #      self::glob_recursive($base)
   #
   $base=basename(rex_url::media());
   $dirs=self::glob_recursive($base);
   $seldirs=array();
   for($i=0;$i<count($dirs);$i=$i+1) $seldirs[$i+1]=$dirs[$i];
   return $seldirs;
   }
public static function get_mediatype($mediapath) {
   #   Zu einem eingegebenen Medienpfad wird ein zugehoeriger Medientyp
   #   zurueck gegeben, in Form eines assoziativen Arrays mit diesen Schluesseln
   #      ['name']     Namen de Medientyps
   #      ['id']       Id des Medientyps
   #      ['status']   Hinweis/Fehler
   #   $mediapath      Medienpfad, z.B. in der Form 'media/ordner1/ordner2'
   #                   (endet der Pfad mit einem '/', so wird er nicht gefunden)
   #   benutzte functions:
   #      self::glob_recursive($base)
   #
   # --- Sonderfall: Medienordner selbst
   if($mediapath==basename(rex_url::media()))
     return array('name'=>self::def_type,'id'=>0,'status'=>'');
   #
   # --- existiert der vorgegebene Pfad ueberhaupt?
   $base=explode(DIRECTORY_SEPARATOR,$mediapath)[0];
   $dirs=self::glob_recursive($base);
   $ex=FALSE;
   for($i=0;$i<count($dirs);$i=$i+1)
      if($mediapath==$dirs[$i]):
        $ex=TRUE;
        break;
        endif;
   if(!$ex)
     return array('name'=>'','id'=>'',
                  'status'=>'+++++ Pfad \''.$mediapath.'\' nicht gefunden');
   #
   # --- verknuepfte Effekte finden (es sollte eigentlich nur einen geben)
   $sql=rex_sql::factory();
   $table2=rex::getTablePrefix().'media_manager_type_effect';
   $query='SELECT * FROM '.$table2.' WHERE effect=\'mediapath\'';
   $effects=$sql->getArray($query);
   #
   # --- Medienpfad und type_id auslesen
   for($i=0;$i<count($effects);$i=$i+1):
      #     Medienpfad auslesen
      $params=json_decode($effects[$i]['parameters'],TRUE);
      $mpath=$params['rex_effect_mediapath']['rex_effect_mediapath_mediapath'];
      #     Medientyp auslesen
      if($mpath==$mediapath):
        $type_id=$effects[$i]['type_id'];
        $name='';
        $table1=rex::getTablePrefix().'media_manager_type';
        $query='SELECT * FROM '.$table1.' WHERE id='.$type_id;
        $typen=$sql->getArray($query);
        if(!empty($typen)):
          return array('name'=>$name=$typen[0]['name'],'id'=>$type_id,'status'=>'');
          else:
          return array('name'=>'','id'=>$type_id,
                       'status'=>'+++++ Effekt ohne zugehörigem Medientyp???');
          endif;
        endif;
      endfor;
   return array('name'=>'','id'=>'','status'=>'+++++ keinen zugehörigen Medientyp gefunden');
   }
public static function sample_media_mediadirs() {
   #   Rueckgabe aller Unterordner des Medienordners, fuer die ein Medientyp mit
   #   Effekt 'mediapath' definiert ist, sowie des Medienordners selbst in Form eines
   #   nummerierten Arrays (Nummerierung ab 1). Der Medienordner hat die Nummer 1.
   #   benutzte functions:
   #      self::sample_media_dirs()
   #      self::get_mediatype($mediapath)
   #
   $dirs=self::sample_media_dirs();
   $mediadirs=array();
   $m=0;
   for($i=1;$i<=count($dirs);$i=$i+1):
      $dir=$dirs[$i];
      $mtype=self::get_mediatype($dir);
      if(!empty($mtype['name']) and strpos($mtype['status'],'+++ ')<=0):
        $m=$m+1;
        $mediadirs[$m]=$dir;
        endif;
      endfor;
   return $mediadirs;
   }
public static function file_from_url($medurl) {
   #   Bestimmung und Rueckgabe des Namens einer Mediendatei (basename) bei
   #   vorgegebenem Medien-URL.
   #   $medurl             Medien-URL gemaess rex_media_manager::getUrl($mediatype,$file)
   #                       wird intern vor Benutzung url-decodiert, d.h. in der Form
   #                       '/index.php?rex_media_type=TYPE&amp;rex_media_file=FILE' bzw.
   #                       '/index.php?rex_media_file=FILE&amp;rex_media_type=TYPE'
   #
   if(empty($medurl)) return '';
   #
   $url=urldecode($medurl);
   $str='rex_media_file=';
   $len=strlen($str);
   $pos=strpos($url,$str);
   if($pos>0):   // ...?rex_media_type=TYPE&amp;rex_media_file=FILE
     $file=substr($url,$pos+$len);
     $pos=strpos($file,'&');
     if($pos<=0):
       return $file;
       else:
       return substr($file,0,$pos);
       endif;
     else:       // ...?rex_media_file=FILE&amp;rex_media_type=TYPE
     $str='?rex_media_file=';
     $len=strlen($str);
     $pos=strpos($url,$str);
     if($pos<=0) return '';
     $file=substr($url,$pos+$len);
     $pos=strpos($file,'&');
     return substr($file,0,$pos);
     endif;
   }
public static function get_mediapath($mediatype) {
   #   Rueckgabe des Medienpfades zu einem Medientyp bzw. ggf. einer Fehlermeldung.
   #   $mediatype          gegebener Medientyp
   #
   if($mediatype==self::def_type) return basename(rex_path::media());
   #
   $mediapath='';
   $sql=rex_sql::factory();
   #
   # --- type_id bestimmen
   $table1=rex::getTablePrefix().'media_manager_type';
   $query='SELECT * FROM '.$table1.' WHERE name=\''.$mediatype.'\'';
   $typen=$sql->getArray($query);
   if(!empty($typen)):
     $type_id=$typen[0]['id'];
     else:
     return '+++++ Medientyp \''.$mediatype.'\' ist nicht definiert';
     endif;
   #
   # --- Medienpfad bestimmen
   $table2=rex::getTablePrefix().'media_manager_type_effect';
   $query='SELECT * FROM '.$table2.' WHERE effect=\'mediapath\' AND type_id='.$type_id;
   $effects=$sql->getArray($query);
   if(!empty($effects)):
     $params=json_decode($effects[0]['parameters'],TRUE);
     return $params['rex_effect_mediapath']['rex_effect_mediapath_mediapath'];
     else:
     return '+++++ kein Effekt \'mediapath\' zum Medientyp \''.$mediatype.'\' definiert???';
     endif;
   }
public static function mediapath_from_url($medurl) {
   #   Rueckgabe des Medienpfades zu einem Medien-URL (Hinweis/Fehlermeldung bei
   #   ungueltigem URL).
   #   $medurl             Medien-URL gemaess rex_media_manager::getUrl($mediatype,$file)
   #                       wird intern vor Benutzung url-decodiert, d.h. in der Form
   #                       '/index.php?rex_media_type=TYPE&rex_media_file=FILE' bzw.
   #                       '/index.php?rex_media_file=FILE&rex_media_type=TYPE'
   #   benutzte functions:
   #      self::get_mediapath($mediatype)
   #
   if(empty($medurl)) return '+++++ kein URL angegeben';
   #
   # --- Medientyp ausfiltern
   $url=urldecode($medurl);
   $str='?rex_media_type=';
   $len=strlen($str);
   $pos=strpos($url,$str);
   if($pos<=0):    // ...?rex_media_file=FILE&rex_media_type=TYPE
     $str='rex_media_type=';
     $pos=strpos($url,$str);
     $mediatype=substr($url,$pos+$len);
     else:        // ...?rex_media_type=TYPE&rex_media_file=FILE
     $mediatype=substr($url,$pos+$len);
     $pos=strpos($mediatype,'&');
     if($pos>0) $mediatype=substr($mediatype,0,$pos);
     if(substr($mediatype,0,1)=='&') $mediatype='';
     endif;
   if(empty($mediatype)) return '+++++ kein Medientyp angegeben';
   #
   # --- Medienpfad aus dem Medientyp
   return self::get_mediapath($mediatype);
   }
public static function is_image($file) {
   #   Entscheidung, ob eine vorgegebene Mediendatei eine Bilddatei ist.
   #   $file               vollstaendiger Pfad der zu untersuchenden Mediendatei
   #
   $cmime='';
   if(file_exists($file)) $cmime=mime_content_type($file);
   if(substr($cmime,0,5)=='image' and
      strpos($cmime,'tga')<=0 and strpos($cmime,'targa')<=0):
   #     mp2- und mp3-Dateien haben z.B. den Mime-Typ 'image/x-tga' bzw. 'image/x-targa'
     return TRUE;
     else:
     return FALSE;
     endif;
   }
public static function is_image_url($medurl) {
   #   Entscheidung, ob ein vorgegebener Medien-URL auf eine Bilddatei verweist.
   #   $medurl             zu untersuchender Medien-URL (wird intern vor der
   #                       Benutzung url-decodiert)
   #   benutzte functions:
   #      self::mediapath_from_url($medurl)
   #      self::file_from_url($medurl)
   #      self::is_image($file)
   #
   $url=urldecode($medurl);
   $mediapath=self::mediapath_from_url($url);
   $file=self::file_from_url($url);
   $file=rex_path::base().$mediapath.DIRECTORY_SEPARATOR.$file;
   return self::is_image($file);
   }
public static function html_image($url,$width,$text) {
   #   Rueckgabe eines HTML-Codes fuer die Darstellung einer Mediendatei als Bild.
   #   $url                URL der Mediendatei (in urldecodierter Form)
   #   $width              Breite der Darstellung
   #   $text               img-title
   #
   $stwidth='';
   if($width>0) $stwidth=' width="'.$width.'" style="max-width:'.$width.'px;"';
   return '<img src="'.$url.'"'.$stwidth.' title="'.$text.'"><br>'.$text;
 }
public static function html_link($url,$target,$text) {
   #   Rueckgabe eines HTML-Codes fuer die Darstellung einer Mediendatei als Link
   #   auf die Mediendatei.
   #   $url                URL der Mediendatei (in urldecodierter Form)
   #   $target             Zielfenster bei Klick auf den Link
   #                          leer:   Ausgabe im aktuellen Browser-Tab
   #                          sonst:  Ausgabe in einem neuen Browser-Tab
   #   $text               Linktext
   #
   return '<a href="'.$url.'" target="'.$target.'" title="Anzeige bzw. Download">'.$text.'</a>';
   }
public static function html_showlink($medurl,$widtar,$text) {
   #   Rueckgabe eines HTML-Codes fuer die Darstellung einer Mediendatei als Bild
   #   bzw. als Link auf die Mediendatei.
   #   $medurl             URL der Mediendatei (wird intern noch url-decodiert)
   #   $widtar             Breite der Darstellung (Bild)
   #                       Zielfenster bei Klick auf den Link (sonstige Mediendatei)
   #                          leer:   Ausgabe im aktuellen Browser-Tab
   #                          sonst:  Ausgabe in einem neuen Browser-Tab
   #   $text               img-title (Bild)
   #                       Linktext  (sonstige Mediendatei)
   #   Darstellung als Bild:     vergl. html_image()
   #   Darstellung als Link:     vergl. html_link()
   #   benutzte functions:
   #      self::is_image($medurl)
   #      self::mediapath_from_url($medurl)
   #      self::file_from_url($medurl)
   #      self::html_image($url,$width,$text)
   #      self::html_link($url,$target,$text)
   #
   if(empty($medurl)) return '+++++ kein Medien-URL angegeben';
   #
   $url=urldecode($medurl);
   if(self::is_image_url($url)):
     return self::html_image($url,$widtar,$text);
     else:
     $mediapath=self::mediapath_from_url($url);
     if(strpos($mediapath,'+++ ')>0) return $mediapath;
     $file=self::file_from_url($url);
     $absfile=rex_path::base().$mediapath.DIRECTORY_SEPARATOR.$file;
     if(file_exists($absfile) and !empty($file)):
       return self::html_link($url,$widtar,$text);
       else:
       return '+++++ Datei \''.$file.'\' nicht gefunden';
       endif;
     endif;
   }
public static function file_selection($slice_id,$rexval,$medurl,$widtar,$text) {
   #   Rueckgabe des HTML-Codes des Konfigurations-Formulars zur Darstellung
   #   eines Bildes bzw. eines Links auf eine Mediendatei. Der Code wird als
   #   Javascript-Code in entsprechende Fenster eines HTML-Editors eingebettet
   #   (document.writeln(' ... Code ...')). Er dient auch als Quellcode fuer
   #   der Seite 'system_file_search()'.
   #   Zu konfigurieren sind Bildbreite bzw. Zielseite des Links und Untertext
   #   und Titel bei Bildern bzw. der Linktext. Wenn Slice-Id und REX-Variable
   #   vorgegeben sind, werden diese beiden Daten sowie der Mediendatei-URL
   #   zusaetzlich in REX-Variablen gespeichert.
   #   $slice_id           Slice-Id = Wert der REX-Variablen REX_SLICE_ID
   #   $rexval             >0:  Nummer der REX-Varablen fuer die Speicherung des
   #                            Medien-URLs (z.B.: 3 fuer REX_VALUE[3])
   #                       <=0: es werden keine REX-Variablen gespeichert
   #   $medurl             Medien-URL = Wert des zugehoerigen Input-Feldes:
   #                       zunaechst: gemaess Setzung durch 'onclick' ueber die Feld-Id
   #                       danach:    Wert der REX-Variablen REX_VALUE[$rexval]
   #   $widtar             Bildbreite (bei Bildern)
   #                       =''/'_self'/'_blank' (Zielseite bei Link auf Mediendatei)
   #                       Wert der REX-Variablen REX_VALUE[$rexval+1]
   #   $text               Untertext und title (bei Bildern)
   #                       Linktext (bei sonstigen Mediendateien)
   #                       Wert der REX-Variablen REX_VALUE[$rexval+2]
   #   benutzte functions:
   #      self::is_image_url($medurl)
   #      self::html_showlink($medurl,$widtar,$text)
   #
   $medurl_id=self::medurl_id.$rexval;
   $widtar_id=self::widtar_id.$rexval;
   $lnktxt_id=self::lnktxt_id.$rexval;
   $widtar_ti=self::widtar_ti.$rexval;
   $widtar_de=self::widtar_de.$rexval;
   $lnktxt_ti=self::lnktxt_ti.$rexval;
   $shwlnk_id=self::shwlnk_id.$rexval;
   $preview  =self::widpreview;
   #
   # --- Bild- oder Link-Formular
   $type='text';
   if(!empty($medurl) and !self::is_image_url($medurl)) $type='checkbox';
   #
   if($rexval<=0):
     #
     # --- alle Javascript-basierten Auswahlmenues fuer (HTML-)Editoren
     $lanf='\'';
     $lend='\n\' +';
     $send='\n\'';
     $strnam1='';
     $strnam2='';
     $strnam3='';
     $strval2=' value="'.$widtar.'" checked=""';
     #     Formate und Anzeige des URLs der ausgewaehlten Datei
     $clastx='style="min-height:2em; padding:2px 10px; color:white; border-color:rgb(60,77,96); '.
             'background-color:rgb(60,77,96); cursor:pointer;"';
     $clasty='style="min-height:2em; padding:2px 10px; border:solid 1px silver; '.
             'background-color:inital; overflow:auto;"';
     $clastz='style="min-height:2em; padding:2px 10px; border:solid 1px silver; '.
             'text-align:right; vertical-align:bottom; width:5em; display:inline;"';
     $returl='
'.$lanf.'<div><br>URL:<br>'.$lend.'
'.$lanf.'    <div id="'.$medurl_id.'"'.$lend.'
'.$lanf.'         '.$clasty.'>'.$medurl.'</div></div>'.$lend;
     else:
     #
     # --- fuer den Modul zum Einfuegen einer Mediendatei und das exemplarische Auswahlmenue
     $lanf='';
     $lend='';
     $send='';
     if($rexval>=1 and $rexval<=20):
       #     nur fuer den Modul zum Einfuegen einer Mediendatei
       $strnam1='name="REX_INPUT_VALUE['.$rexval.']"';
       $strnam2='name="REX_INPUT_VALUE['.intval($rexval+1).']"';
       $strnam3='name="REX_INPUT_VALUE['.intval($rexval+2).']"';
       $strval2=' value="'.$widtar.'"';
       if($type=='checkbox'):
         if(strtolower($widtar)=='on' or $widtar==1):
           $strval2=' checked="checked"';
           else:
           $strval2='';
           endif;
         endif;
       else:
       #     fuer das exemplarische Auswahlmenue
       $strnam1='';
       $strnam2='';
       $strnam3='';
       $strval2=' value="'.$widtar.'" checked=""';
       endif;
     #     Formate und Anzeige des URLs der ausgewaehlten Datei
     $clastx='class="btn btn-view"';
     $clasty='class="medi_box"';
     $clastz='class="medi_box medi_right"';
     $returl='
'.$lanf.'<div><br>URL:<br>'.$lend.'
'.$lanf.'    <input id="'.$medurl_id.'" size="'.self::inp_url.'"'.$lend.'
'.$lanf.'           '.$clasty.$lend.'
'.$lanf.'           '.$strnam1.' value="'.$medurl.'" readonly></div>'.$lend;
     endif;
   #
   # --- Formular-Beschriftungen
   $aimg=self::prop_width;
   $bimg=self::prop_subtit;
   $dimf=self::prop_pix;
   $atxt=self::prop_tab;
   $btxt=self::prop_lnktxt;
   $dtxu='gleicher bzw. neuer Browser-Tab';
   $dtxt=self::prop_target;
   if(empty($medurl)):
     $shwlnk='';
     $line1=$aimg.' / '.$atxt;
     $desc1=$dimf.' / '.$dtxu;
     $line2=$bimg.' / '.$btxt;
     else:
     $shwlnk=self::html_showlink($medurl,$widtar,$text);
     if(str_contains($shwlnk,'<img ')):
       $line1=$aimg;
       $desc1=$dimf;
       $line2=$bimg;
       else:
       $line1=$atxt;
       $desc1=$dtxt;
       $line2=$btxt;
       endif;
     endif;
   #
   # --- Rueckgabe-String
   $preview_style='padding-left:10px; text-align:left; min-width:'.$preview.'px; max-width:'.$preview.'px;';
   $ret=$lanf.'<br>'.$lend.'
'.$lanf.'<!---------- '.self::prop_title.' ------>'.$lend.'
'.$lanf.'<button type="submit"'.$lend.'
'.$lanf.'        '.$clastx.$lend.'
'.$lanf.'        onclick="choose_file('.intval($slice_id).','.intval($rexval).');">'.$lend.'
'.$lanf.self::prop_button.'</button>'.$lend.
        $returl.'
'.$lanf.'<div><br>'.$lend.'
'.$lanf.'    <span id="'.$widtar_ti.'">'.$line1.':</span><br>'.$lend.'
'.$lanf.'    <input type="'.$type.'" id="'.$widtar_id.'"'.$lend.'
'.$lanf.'           '.$clastz.$lend.'
'.$lanf.'           '.$strnam2.$strval2.'> &nbsp;'.$lend.'
'.$lanf.'    <span id="'.$widtar_de.'">'.$desc1.'</span></div>'.$lend.'
'.$lanf.'<div><br><span id="'.$lnktxt_ti.'">'.$line2.':</span><br>'.$lend.'
'.$lanf.'    <input id="'.$lnktxt_id.'" size="'.self::inp_lnktxt.'"'.$lend.'
'.$lanf.'           '.$clasty.$lend.'
'.$lanf.'           '.$strnam3.' value="'.$text.'" autofocus></div>'.$lend.'
'.$lanf.'<div><br>'.self::prop_ansicht.'</div>'.$lend.'
'.$lanf.'    <div id="'.$shwlnk_id.'"'.$lend.'
'.$lanf.'         style="'.$preview_style.'">'.$shwlnk.'</div>'.$lend.'
'.$lanf.'<!------------------------------------------------->'.$send;
   return $ret;
   }
#
# --------------------------------------------------- Editor-Plugins
public static function plugin_paths($editor) {
   #   Rueckgabe von Pfad-Daten zu einem gegebenen HTML-Editor-Plugins in Form
   #   eines assoziativen Arrays:
   #   - Pfad des Ordners, der die Javascript-Datei(en) zum Plugin enthaelt
   #   - Pfad des Assets-Ordners dieses AddOns, der u.a. die Plugins-Icons enthaelt
   #   - URL des Plugin-Icons
   #   $editor             Ordnername des HTML-Editors im addon-Ordner
   #
   $addon=self::this_addon;
   #
   $file1='plugin.js';
   $file2='';
   $file3='';
   if($editor==$addon::plug_cke):
     $path=rex_path::addon($editor,'install/plugins/'.$addon);
     $file2=$addon.'.js';
     endif;
   if($editor==$addon::plug_tiny):
     $path=rex_path::addon($editor,'assets/vendor/tinymce/plugins/'.$addon);
     $file2='plugin.min.js';
     $file3='index.js';
     endif;
   if($editor==$addon::plug_redac):
     $path=rex_path::addon($editor,'assets/plugins');
     $file1=$addon.'.js';
     endif;
   $icon   =rex_path::addonAssets($addon,$addon.'.svg');
   $iconurl=rex_url::addonAssets($addon,$addon.'.svg');
   $pos=strpos($iconurl,'/');
   if($pos>0) $iconurl=substr($iconurl,$pos);
   $files=array(1=>$file1, 2=>$file2, 3=>$file3);
   #
   return array('path'=>$path, 'icon'=>$icon, 'icon_url'=>$iconurl, 'files'=>$files);
   }
#
# --------------------------------------------------- Medienkategorien
public static function set_media_category($mediatype) {
   #   Erstellen einer Top-Medienkategorie zu einem gegebenen Medientyp,
   #   sofern die Kategorie nicht schon existiert. Name und Id der Kategorie
   #   werden als assoziatives Array zurueck gegeben.
   #   $mediatype          gegebener Medientyp
   #   benutzte functions:
   #      self::find_media_category($mediatype)
   #
   # --- Top-Medienkategorie schon definiert?
   $topmedcat=self::find_media_category($mediatype);
   $topcat=$topmedcat['name'];
   if($topmedcat['id']>0 or strpos($topcat,'+++ ')>0) return $topmedcat;
   #
   # --- falls noch nicht, erstelle sie jetzt
   rex_media_category_service::addCategory($topcat,null);
   #
   # --- jetzt sollte sie vorhanden sein 
   return self::find_media_category($mediatype);
   }
public static function find_media_category($mediatype) {
   #   Suche nach einer Top-Medienkategorie zu einem gegebenen Medientyp.
   #   Sofern die Kategorie existiert, werden ihre Id und ihr Name als
   #   assoziatives Array zurueck gegeben.
   #   $mediatype          gegebener Medientyp
   #   benutzte functions:
   #      self::get_mediapath($mediatype)
   #
   if($mediatype==self::def_type)
     return array('id'=>0,'name'=>'+++++ '.self::def_type.' hat keine Medienkategorie');
   #
   # --- $mediatype gueltige Medienkategorie?
   $mediapath=self::get_mediapath($mediatype);
   if(strpos($mediapath,'+++ ')>0) return array('id'=>0,'name'=>$mediapath);
   #
   # --- Auslesen der Tabelle rex_media_category
   $topdir=self::cat_marker.$mediatype;
   $sql=rex_sql::factory();
   $table=rex::getTablePrefix().'media_category';
   $query='SELECT id FROM '.$table.' WHERE parent_id=0 AND name=\''.$topdir.'\'';
   $ids=$sql->getArray($query);
   $id=0;
   if(!empty($ids)) $id=$ids[0]['id'];
   return array('id'=>$id,'name'=>$topdir);
   }
public static function delete_media_category($mediatype) {
   #   Loeschen einer Top-Medienkategorie zu einem gegebenen Medientyp, sofern
   #   sie tatsaechlich existiert. Ggf. wird eine Fehlermeldung zurueck gegeben.
   #   $mediatype          gegebener Medientyp
   #   benutzte functions:
   #      self::find_media_category($mediatype)
   #
   # --- Top-Medienkategorie ueberhaupt definiert?
   $topmedcat=self::find_media_category($mediatype);
   $topcat=$topmedcat['name'];
   $topid =$topmedcat['id'];
   if($topid<=0 or strpos($topcat,'+++ ')>0) return $topmedcat;
   #
   # --- falls sie existiert, loesche sie jetzt
   rex_media_category_service::deleteCategory($topmedcat['id']);
   #
   # --- jetzt sollte sie nicht mehr vorhanden sein 
   return self::find_media_category($mediatype);
   }
#
# --------------------------------------------------- Mediendatei-Auswahl
public static function cache_image_path($file,$type_id) {
   #   Rueckgabe des vollstaendigen Pfades der zugehoerigen Cache-Datei aus
   #   einer gegebenen Bilddatei (unabhaengig davon, ob die Bilddatei existiert).
   #   $file               vollstaendiger Pfad der Bilddatei
   #   $type_id            Medientyp-Id des Bildes
   #   benutzte functions:
   #      media_install::cache_ordner()
   #
   media_install::cache_ordner();   // ggf. vorher noch Cache-Ordner erstellen
   #
   $thumb=$type_id.'.'.basename($file);
   $cachepath=rex_path::addonCache(self::this_addon);
   return $cachepath.$thumb;
  }
public static function cache_image($file,$type_id,$renew=FALSE) {
   #   Verkleinern eines gegebenen Bildes auf eine feste Breite und Abspeichern
   #   der Verkleinerung (Thumbnail, mit Medientyp-Id.'.'.basename($file) als
   #   basename) im AddOn-Cache. Zurueck gegeben wird der Pfad des Cache-Datei.
   #   $file               vollstaendiger Pfad der Bilddatei
   #   $type_id            Medientyp-Id des Bildes
   #   $renew              =FALSE: die verkleinerte Bilddatei im Cache wird 
   #                               ueberschreiben, wenn die Bilddatei neuer ist
   #                       =TRUE: die verkleinerte Bilddatei im Cache wird auf
   #                              jeden Fall ueberschrieben
   #   benutzte functions:
   #      cache_image_path($file,$type_id)
   #
   if(!file_exists($file))
     return '+++++ '.basename($file).' nicht gefunden';
   if(pathinfo($file,PATHINFO_EXTENSION)=='svg')
     return '+++++ '.basename($file).' ist svg-Datei';
   #
   # --- Originalbild zu klein?
   list($width,$height,$type)=getimagesize($file);
   if($width<=self::widover) return '+++++ kein Thumbnail für Bilder mit weniger als '.self::widover.' Pixel Breite';
   #
   # --- Cache-Datei in neuester Version schon vorhanden?
   $datefile=date('Y-m-d H:i:s',filemtime($file));
   $thumbfile=self::cache_image_path($file,$type_id);
   $datethumb='';
   if(file_exists($thumbfile)) $datethumb=date('Y-m-d H:i:s',filemtime($thumbfile));
   if(file_exists($thumbfile) and $datefile<=$datethumb and !$renew)
     return $thumbfile;
   #
   # --- Originalbild laden
   $thumbwidth=self::widover;
   $src='';
   if($type==IMAGETYPE_JPEG) $src=imagecreatefromjpeg($file);
   if($type==IMAGETYPE_PNG)  $src=imagecreatefrompng($file);
   if($type==IMAGETYPE_GIF)  $src=imagecreatefromgif($file);
   if($type==IMAGETYPE_BMP)  $src=imagecreatefrombmp($file);
   if($type==IMAGETYPE_WEBP) $src=imagecreatefromwebp($file);
   if(empty($src))
     return '+++++ Fehler imagecreate..., '.basename($thumbfile).' konnte nicht erzeugt werden';
   #
   # --- Verkleinerung (Thumbnail) herstellen
   $thumbheight=intval($height*$thumbwidth/$width);
   $dst=imagecreatetruecolor($thumbwidth,$thumbheight);
   #     Manipulationen zur Transparenz
   $transparent=imagecolorallocatealpha($dst,0,0,0,127);
   imagecolortransparent($dst,$transparent);
   imagealphablending($dst,FALSE);
   imagesavealpha($dst,TRUE);
   #     Kopieren auf die gewuenschte Breite
   imagecopyresampled($dst,$src,0,0,0,0,$thumbwidth,$thumbheight,$width,$height);
   #
   # --- Thumbnail im Cache abspeichern
   $handle=fopen($thumbfile,'w');
   if($type==IMAGETYPE_JPEG) imagejpeg($dst,$handle);
   if($type==IMAGETYPE_PNG)  imagepng($dst,$handle);
   if($type==IMAGETYPE_GIF)  imagegif($dst,$handle);
   if($type==IMAGETYPE_BMP)  imagebmp($dst,$handle);
   if($type==IMAGETYPE_WEBP) imagewebp($dst,$handle);
   fclose($handle);
   return $thumbfile;
   }
public static function cache_images_for_deletion($mediapath,$type_id) {
   #   Rueckgabe einer Liste der Cache-Dateien zu einem Medientyp, fuer die es
   #   keine Originaldatei im entsprechenden Medienordner mehr gibt, in Form
   #   eines nummerierten Arrays (Nummerierung ab 1).
   #   $mediapath          Medienpfad, z.B. in der Form 'media/ordner1/ordner2'
   #   $type_id            Medientyp-Id des Medienpfades
   #
   $base=rex_path::base().$mediapath;
   #
   $cachepath=rex_path::addonCache(self::this_addon);
   $cfiles=glob($cachepath.'*');
   $to_del=array();
   $m=0;
   for($i=0;$i<count($cfiles);$i=$i+1):
      $cfile=$cfiles[$i];
      if(is_dir($cfile)) continue;
      $bcfile=basename($cfile);
      $id=explode('.',$bcfile)[0];
      if($id!=$type_id) continue;
      $pos=strpos($bcfile,'.')+1;
      $bofile=substr($bcfile,$pos);
      $ofile=$base.'/'.$bofile;
      if(!file_exists($ofile)):
        $m=$m+1;
        $to_del[$m]=$cfile;
        endif;
      endfor;
   return $to_del;
   }
public static function menue_datei($rexval,$mediapath,$selfile,$filter) {
   #   Ausgabe des HTML-Codes fuer die Auswahl einer Datei aus einem Medienordner,
   #   ggf. unter Beruecksichtigung eines Filters fuer die Dateinamen.
   #   Die Auswahl erfolgt ueber einen 'onclick="..."'-Link, verbunden mit einer
   #   Wertzuweisung auf vier HTML-tags ('document.getElementById(...)...=...'):
   #   1) Der Medien-URL der ausgewaehlten Datei wird in ein (bereit zu stellendes)
   #      <input>-Feld mit der entsprechenden Id geschrieben
   #      ('document.getElementById(id1).value=...').
   #   2) Die Breite eines Bildes bzw. alternativ die Zielseite eines Links auf eine
   #      sonstige Mediendatei wird in ein (bereit zu stellendes) <input>-Feld mit
   #      der entsprechenden Id geschrieben
   #      ('document.getElementById(id2).value=...').
   #   3) Der Untertitel (Terxt) eines Bildes bzw. der Linktext eines Links auf eine
   #      sonstige Mediendatei wird in ein (bereit zu stellendes) <input>-Feld mit
   #      der entsprechenden Id geschrieben
   #      ('document.getElementById(id3).value=...').
   #   4) Der HTML-Code zur Darstellung eines Bildes bzw. eines Links auf eine
   #      sonstige Mediendatei wird in einen (bereit zu stellenden) <span>-Bereich
   #      ('document.getElementById(id4).innerHTML=...').
   #   $rexval             Nummer der REX-Varablen fuer den Medien-URL
   #                       (z.B.: 3 fuer REX_VALUE[3])
   #                       falls $rexval<=0, werden die geschriebenen Daten nicht in
   #                       einen Slice geschrieben.
   #   $mediapath          Medienpfad des gewaehlten Ordners
   #   $selfile            vorher ausgewaehlte Datei (basename)
   #   $filter             String des gewaehlten Dateien-Filters
   #   benutzte functions:
   #      self::get_mediatype($mediapath)
   #      self::cache_images_for_deletion($mediapath,$type_id)
   #      self::is_image($file)
   #      self::html_image($url,$width,$text)
   #      self::html_link($url,$target,$text)
   #
   $addon=self::this_addon;
   $medurl_id=self::medurl_id.$rexval;
   $widtar_id=self::widtar_id.$rexval;
   $lnktxt_id=self::lnktxt_id.$rexval;
   $shwlnk_id=self::shwlnk_id.$rexval;
   $Gslice_id=self::slice_id;
   $widtar_ti=self::widtar_ti.$rexval;
   $widtar_de=self::widtar_de.$rexval;
   $lnktxt_ti=self::lnktxt_ti.$rexval;
   #
   $lowfilter=strtolower($filter);
   $filter='<span class="medi_sel">'.$filter.'</span>';        // Filter hervorheben
   $ordner='<span class="medi_sel">/'.$mediapath.'</span>';    // gewaehlten Ordner hervorheben
   #
   # --- Beispiel oder Auswahl-Button?
   $slice_id='';                                               // Beispiel
   if(isset($_GET[$Gslice_id])) $slice_id=$_GET[$Gslice_id];   // Auswahl-Button
   #
   # --- alle Dateien des Medienordners auslesen
   $mtype=self::get_mediatype($mediapath);
   $mediatype=$mtype['name'];
   $type_id  =$mtype['id'];
   $dir=$_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.$mediapath;
   $fi=glob($dir.DIRECTORY_SEPARATOR.'*');
   $files=array();
   $m=0;
   for($i=0;$i<count($fi);$i=$i+1):
      $f=$fi[$i];
      if(is_dir($f)) continue;
      $files[$m]=$f;
      $m=$m+1;
      endfor;
   #
   # --- obsolete Cache-Dateien loeschen
   $cf=self::cache_images_for_deletion($mediapath,$type_id);
   for($i=1;$i<=count($cf);$i=$i+1) if(file_exists($cf[$i])) unlink($cf[$i]);
   #
   # --- Dateienliste
   $tabcont= '
    <tr><th class="medi_nr">Nr.</th>
        <th class="medi_nr">Symbol</th>
        <th class="medi_name medi_center">Mediendatei</th>
        <th class="medi_bu">Aktion</th></tr>';
   $m=0;
   $auswahl='';
   for($i=0;$i<count($files);$i=$i+1):
      #
      # --- Dateigroesse
      $absfile=$files[$i];
      $fsize=intval(filesize($absfile)/1000);
      if($fsize<=0) $fsize=1;
      $fsize='['.number_format($fsize,0,',','.').' KBytes]';
      $fname=basename($absfile);
      #
      # --- ueberspringen, wenn der Dateiname nicht dem Filter entspricht
      if($lowfilter!='' and strpos(strtolower($fname),$lowfilter)===FALSE) continue;
      #
      # --- Medien-URL
      $rexurl=rex_media_manager::getUrl($mediatype,$fname);
      $pos=strpos($rexurl,DIRECTORY_SEPARATOR);
      $absurl=substr($rexurl,$pos);
      $decurl=urldecode($absurl);
      #
      # --- Ausgabe einer Zeile pro Datei
      $klasse='';
      if($fname==$selfile):
        $klasse='medi_sel';
        $auswahl='<span class="medi_sel">'.$selfile.'</span>';   // vorherige Auswahl hervorheben
        endif;
      #
      # --- Bild
      if(self::is_image($absfile)):
        #     Im Falle eines Bildes das Thumbnail im Cache ggf. noch erzeugen
        $thumbfile=self::cache_image($absfile,$type_id);
        if(strpos($thumbfile,'+++ ')<=0):
          $rexurl=rex_media_manager::getUrl('CACHE:media_directories',$type_id.'.'.$fname);
          $pos=strpos($rexurl,DIRECTORY_SEPARATOR);
          $absurl=substr($rexurl,$pos);
          endif;
        $img='<img onmouseover="document[\''.$fname.'\'].width='.self::widover.';"
                 onmouseout ="document[\''.$fname.'\'].width='.self::widout.';"
                 src ="'.$absurl.'" border="1" class="medi_valign"
                 name="'.$fname.'" width="'.self::widout.'">';
        #     Bildbreite
        $gis=getimagesize($absfile);
        $wdt=self::widpreview;
        if(is_array($gis)) $wdt=$gis[0];
        if(!empty($wdt)) $fsize='['.$wdt.' Pixel breit]';
        $txt='';
        $wta=$wdt;
        if($wta>self::widpreview) $wta=self::widpreview;
        $typ='text';
        $chkval='value';
        #     Zellenbeschriftungen
        $line1=self::prop_width.':';
        $cold1=self::prop_pix;
        $line2=self::prop_subtit.':';
        #     Anzeige-Code im Menue
        $ret=htmlentities(self::html_image($decurl,'',$txt));
        else:
      #
      # --- Link
        $arr=explode('.',$fname);
        $last=count($arr)-1;
        $ext=strtolower($arr[$last]);
        $imgurl=rex_url::addonAssets($addon,'icon_download.gif');
        if($ext=='pdf') $imgurl=rex_url::addonAssets($addon,'icon_pdf.gif');
        if($ext=='txt') $imgurl=rex_url::addonAssets($addon,'icon_txt.gif');
        $pos=strpos($imgurl,DIRECTORY_SEPARATOR);
        $imgurl=substr($imgurl,$pos);
        #     Zielseite
        $wdt='_blank';
        $img='<a  href="'.$absurl.'" title="Anzeige bzw. Download" target="'.$wdt.'">
            <img src="'.$imgurl.'" class="medi_valign"></a>';
        $txt=$fname;
        $wta=$wdt;
        $typ='checkbox';
        $chkval='checked';
        #     Zellenbeschriftungen
        $line1=self::prop_tab.':';
        $cold1=self::prop_target;
        $line2=self::prop_lnktxt.':';
        #     Anzeige-Code im Menue
        $ret=htmlentities(self::html_link($decurl,$wta,$txt));
        endif;
      #     Bild-/Linkanzeige
      #     URL in div-Container bzw. input-Feld schreiben:  innerHTML bzw. value
      $inhval='innerHTML';
      if($rexval>0) $inhval='value';
      $m=$m+1;
      $tabcont=$tabcont. '
    <tr><td class="medi_nr medi_small medi_valign">
            '.$m.'</td>
        <td class="medi_name medi_small medi_valign '.$klasse.'" align="left" colspan="2">
            '.$img.
            '&nbsp;&nbsp;'.$fname.' &nbsp; '.$fsize.'&nbsp;&nbsp;</td>
        <td class="medi_bu medi_valign">
            <a href="#" class="medi_sel medi_small medi_valign"
               title="Datei übernehmen"
               onclick="opener.document.getElementById(\''.$medurl_id.'\').'.$inhval.'=\''.$decurl.'\';
                        opener.document.getElementById(\''.$shwlnk_id.'\').innerHTML=\''.$ret.'\';
                        opener.document.getElementById(\''.$widtar_ti.'\').innerHTML=\''.$line1.'\';
                        opener.document.getElementById(\''.$widtar_id.'\').type=\''.$typ.'\';
                        opener.document.getElementById(\''.$widtar_id.'\').'.$chkval.'=\''.$wdt.'\';
                        opener.document.getElementById(\''.$widtar_de.'\').innerHTML=\''.$cold1.'\';
                        opener.document.getElementById(\''.$lnktxt_ti.'\').innerHTML=\''.$line2.'\';
                        opener.document.getElementById(\''.$lnktxt_id.'\').value=\''.$txt.'\';
                        opener.document.getElementById(\''.$lnktxt_id.'\').focus();
                        window.close();
                        return false;">
            <b><i>auswählen</i></b></a></td></tr>';
      endfor;
   #
   # --- Ausgaben
   #     Ueberschrift
   echo '
<!---------- Dateiauswahl im Ordner /'.$mediapath.' -->
<h4 align="center"><br>Auswahl einer Mediendatei aus dem Ordner: &nbsp; '.$ordner.'</h4>
<table class="medi_table">
    <tr valign="top">
        <td>Gespeicherte Datei:</td>
        <td class="medi_pl">'.$auswahl.'</td></tr>
    <tr valign="top">
        <td>Dateinamen-Filter:</td>
        <td class="medi_pl medi_sel">'.$filter.'</td></tr>
</table>';
   #     Auswahlmenue
   echo '
<div align="center"><i>Fahre mit dem Mauszeiger über die Darstellung, um sie zu vergrößern!</i></div>
<br>
<table class="medi_tableb">
'.$tabcont.'
</table>
<br>
<!---------- Ende der Dateiauswahl -->
';
   }
public static function menue_ordner($mediapath,$filter) {
   #   Rueckgabe des HTML-Codes fuer die Select-Auswahl eines Medienordners
   #   mit einem Namensfilter fuer die enthaltenen Dateien.
   #   $mediapath          Medienpfad des vorher gewaehlten Ordners
   #   $filter             String des vorher gewaehlten Datei-Namensfilters
   #   benutzte functions:
   #      self::sample_media_mediadirs()
   #
   # --- Array der Medienordner
   $dirs=self::sample_media_mediadirs();
   #
   # --- Auswahlmenue
   $str='
<div align="center">
<form method="post">
<h3>Suche von Mediendateien in Unterordnern des Medienordners</h3>
<br>
<table class="medi_table">
    <tr><th class="medi_pl medi_center">Medienordner</th>
        <th class="medi_pl medi_center">Dateinamen-Filter</th>
        <th class="medi_pl medi_center">Suche</th></tr>
    <tr><td class="medi_pl">
            <select class="form-control" name="'.self::sel_name.'">';
   for($i=1;$i<=count($dirs);$i=$i+1):
      $dir=$dirs[$i];
      $sel='';
      if($dir==$mediapath) $sel=' selected';
      $str=$str.'
                <option value="'.$dir.'"'.$sel.'>'.$dir.'</option>';
      endfor;
   $str=$str.'
            </select></td>
        <td class="medi_pl">
            <input class="form-control" name="'.self::filt_name.'" value="'.$filter.'"></td>
        <td class="medi_pl">
            <button type="submit" class="btn btn-success">
            Dateien anzeigen
            </button></td></tr>
</table>
</form>
<br>
</div>';
   return $str;
   }
#
# --------------------------------------------------- Module
public static function insert_button() {
   #   Ausgabe eines Buttons zum Einfuegen eines HTML-Codes zur Darstellung eines
   #   Bildes oder einer sonstigen Mediendatei in eine Textarea.
   #   ++++++ nur zur Ergaenzung eines Quelltext-Editors ++++++
   #   Dafuer wird zunaechst ein Fenster zur Auswahl der Mediendatei geoeffnet. Die
   #   Daten der Mediendatei koennen in entsprechenden Input-Feldern angepasst werden:
   #      bei Bildern:       Beite sowie ein Untertitel
   #      bei Mediendateien: Zielfenster des Links auf die Datei sowie Linktext
   #
   $addon=self::this_addon;
   $image=rex_url::addonAssets($addon,$addon.'.svg');
   $pos=strpos($image,DIRECTORY_SEPARATOR);
   if($pos>0) $image=substr($image,$pos);
   echo '
<button type="submit" class="btn btn-default" style="cursor:pointer;"
   title="'.self::button_title.'"
   onclick="source_code_editor_formular();">
<img src="'.$image.'">
</button>';
   }
public static function select_form($slice_id,$rexval,$medurl,$widtar,$text) {
   #   Rueckgabe des HTML-Codes des Konfigurations-Formulars zur Darstellung
   #   eines Bildes bzw. eines Links auf eine Mediendatei. Wird nur im Input-Teil
   #   des Moduls 'Einfuegen einer Mediendatei' benutzt.
   #   Parameter:          (siehe aufgerufene function)
   #   benutzte functions:
   #      self::file_selection($slice_id,$rexval,$medurl,$widtar,$text)
   #
   $str=self::file_selection($slice_id,$rexval,$medurl,$widtar,$text);
   $lanf='\'';     // Zeilenanfaenge
   $lend='\n\' +'; // Zeilenenden
   $send='\n\'';   // Zeilenende letzte Zeile
   $str=str_replace($lend,'',$str);
   $str=str_replace($send,'',$str);
   $str=str_replace($lanf,'',$str);
   echo $str."\n";
   }
public static function show_link($medurl,$widtar,$text) {
   #   Ausgabe des HTML-Codes, den die unten benutzte function zurueck gibt.
   #   benutzte functions:
   #      self::html_showlink($medurl,$widtar,$text)
   #
   echo self::html_showlink($medurl,$widtar,$text)."\n";
   }
}
?>
