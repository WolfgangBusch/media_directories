<?php
/*
 * Media Directories AddOn
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version März 2023
 */
#
class media_install {
#
#  Basisfunktionen
#     system_page_url()
#  Stylesheet-Datei, Javascript-Datei
#     write_stylesheet()
#     write_javascript()
#  Installation
#     cache_ordner()
#     cache_mediatype()
#     install()
#     delete_cache()
#     uninstall()
#
# --- Konstanten
const this_addon=media_directories::this_addon;   // self::this_addon
#
# --------------------------------------------------- Basisfunktionen
public static function system_page_url() {
   #   Rueckgabe des URLs der (verborgenen) Backend-Seite 'system_file_search.php'
   #   im Hauptmenue: /redaxo/index.php?page=system_file_search
   #   (vergl. package.yml: pages: system_file_search).
   #   Sie enthaelt die Select-Auswahl eines Medienordners sowie die
   #   Ausgabe der Liste der im gewaehlten Ordner befindlichen Dateien.
   #
   $addon=rex_addon::get(self::this_addon)->getAddon();
   $pages=$addon->getProperty('pages');   // vergl. package.yml)
   $keys=array_keys($pages);
   $page=$keys[0];                        // = 'system_page_search'
   $path=rex_path::backendController();
   $redaxo=basename(dirname($path));
   $index =basename($path);
   $url=DIRECTORY_SEPARATOR.$redaxo.DIRECTORY_SEPARATOR.$index.'?page='.$page;
   return $url;
   }
#
# --------------------------------------------------- Stylesheet-Datei, Javascript-Datei
public static function write_stylesheet() {
   #   Definition und Schreiben des Stylesheets. Falls die Datei danach
   #   existiert, wird TRUE zurueck gegeben.
   #
   $addon   =self::this_addon;
   $link_col=$addon::link_col;
   $borcol='rgb(220,220,220)';
   $bgcol ='rgb(240,240,240)';
   $buffer='/*   S t y l e s h e e t    m e d i a _ d i r e c t o r i e s   */
             /*   Konfiguration   */
.medi_btable { padding:0px; margin:0px; background-color:inherit; border-collapse:collapse; }
.medi_tableb { padding:0px; margin:0px; width:100%; background-color:inherit; border-collapse:collapse; }
.medi_td     { padding:1px 5px 1px 5px; border:solid 1px '.$borcol.'; }
.medi_error  { color:red; }
.medi_tdbg   { background-color:'.$bgcol.'; }
.medi_nowrap { white-space:nowrap; }
.medi_silver { color:silver; }
.medi_bottom { vertical-align:bottom; }
             /*   Input-Felder Konfiguration Bild/Link   */
.medi_box    { min-height:2em; padding:2px 10px; vertical-align:bottom; border:solid 1px silver; }
.medi_right  { text-align:right; }
.medi_hidden { display:none; visibility:hidden; }
             /*   Datei-Auswahlbox   */
.medi_table  { padding:3px; margin:3px; background-color:inherit; border-style:none; }
.medi_valign { vertical-align:middle; }
.medi_nr     { padding:5px; min-width:30px; border:solid 1px '.$borcol.'; text-align:right; }
.medi_name   { padding:5px; width:100%;     border:solid 1px '.$borcol.'; }
.medi_bu     { padding:5px 15px 5px 15px;   border:solid 1px '.$borcol.'; text-align:center; }
.medi_sel    { color:'.$link_col.'; text-align:left; }
.medi_pl     { padding-left:20px; }
.medi_small  { font-size:smaller; }
.medi_center { text-align:center; }
.medi_body   { padding:30px; }
';
   #
   # --- (Ueber-)Schreiben der Stylesheet-Datei
   $dir=rex_path::addonAssets($addon);
   if(!file_exists($dir)) mkdir($dir);
   $file=$dir.$addon.'.css';
   $handle=fopen($file,'w');
   fwrite($handle,$buffer);
   fclose($handle);
   return file_exists($file);
   }
public static function write_javascript() {
   #   Definition und Schreiben der Javascript-Funktionen. Falls die Datei danach
   #   existiert, wird TRUE zurueck gegeben.
   #   benutzte functions:
   #      self::system_page_url()
   #      $addon::file_selection($slice_id,$rexval,$medurl,$widtar,$text)
   #
   $addon=self::this_addon;
   $link_col =$addon::link_col;
   $slice_id =$addon::slice_id;
   $rexval_id=$addon::rexval_id;
   $medurl_id=$addon::medurl_id;
   $widtar_id=$addon::widtar_id;
   $lnktxt_id=$addon::lnktxt_id;
   $shwlnk_id=$addon::shwlnk_id;
   $xampleval=$addon::xample_val;
   $xample_id=$addon::xample_id;
   #
   $assets=rex_url::assets();
   $pos=strpos($assets,DIRECTORY_SEPARATOR);
   if($pos>0) $assets=substr($assets,$pos);
   $pfad=$assets.'addons'.DIRECTORY_SEPARATOR.$addon.DIRECTORY_SEPARATOR.$addon;
   #
   $page_url =self::system_page_url();
   $form_page=$addon::file_selection('','','','','');
   #
   $x='XXXXXXXXXX';
   $y='YYYYYYYYYY';
   $z='ZZZZZZZZZZ';
   $imgform=$addon::html_image($x,$y,$z);
   $linkform=$addon::html_link($x,$y,$z);
   #
   $buffer='/*   J a v a s c r i p t - F u n k t i o n e n    m e d i a _ d i r e c t o r i e s   */
function choose_file(slice_id,rexval) {
    //   Menue im Popup-Fenster zur Auswahl einer Mediendatei.
    //      slice_id    >0: Wert der REX-Variablen REX_SLICE_ID
    //                      nur fuer den Modul zum Einfuegen einer Mediendatei
    //                  =0: sonst
    //      rexval      >0: Nummer der REX-Varablen zur Medien-URL-Speicherung
    //                      nur fuer den Modul zum Einfuegen einer Mediendatei
    //                      und fuer die exemplarische Dateiauswahl
    //                  =0: sonst
    //      Die Eingabewerte werden per URL-Parameter weiter gereicht.
    //
    var width=0.7*screen.height; var height=0.8*screen.height;
    var extra=\'width=\' + width + \',height=\' + height + \',top=40,left=40\';
    var page=\''.$page_url.'\';
    if(rexval>0) {
      page=page + \'&\' + \''.$slice_id.'\' + \'=\' + slice_id +
                  \'&\' + \''.$rexval_id.'\' + \'=\' + rexval; }
    window.open(page,\'\',extra);
    }
function plugin_formular() {
    //   Rueckgabe des HTML-Codes eines Konfigurations-Formulars fuer die
    //   Eigenschaften eines Bildes bzw. des Links auf eine Mediendatei.
    //
    return '.$form_page.';
    }
function build_imglnk() {
    //   Rueckgabe des HTML-Codes des konfigurierten Eigenschaften
    //   eines Bildes bzw. des Links auf eine Mediendatei
    //   fuer HTML-Editor-Plugins und den Quelltext-Editor.
    //
    var medurl=document.getElementById(\''.$medurl_id.'\').innerHTML;
    var lnktxt=document.getElementById(\''.$lnktxt_id.'\').value;
    var widtar=document.getElementById(\''.$widtar_id.'\').value;
    var   type=document.getElementById(\''.$widtar_id.'\').type;
    if(type==\'checkbox\') {
      var chcked=document.getElementById(\''.$widtar_id.'\').checked;
      if(chcked) { widtar=\'_blank\'; } else { widtar=\'\'; } }
    //
    // --- Bild/Mediendatei-Link auslesen (urspruengliche Version)
    var oldlnk=document.getElementById(\''.$shwlnk_id.'\').innerHTML;
    var imglnk=\'\';
    //
    // --- Bild/Mediendatei-Link aus den input-Feldern nachkorrigieren
    if(oldlnk.includes(\'<img src=\')) {
      imglnk=\''.$imgform.'\';    // Bild
      } else {
      if(medurl.length>0) {
        imglnk=\''.$linkform.'\'; // Mediendatei-Link
        }
      }
    if(imglnk!=\'\') {
      imglnk=imglnk.replace(/'.$x.'/g,medurl);
      imglnk=imglnk.replace(/'.$y.'/g,widtar);
      imglnk=imglnk.replace(/'.$z.'/g,lnktxt); }
    return imglnk;
    }
/* */
/* ==================== fuer den Quellcode-Editor-Plugin ============== */
function insert_html() {
    //   Bild-/Mediendatei-Link in die Textarea des Quellcode-Editors schreiben.
    //   benutzte functions:
    //      build_imglnk()
    //
    // --- Bild- bzw. Mediendatei-Link zusammensetzen
    //     -------------- AddOn \''.$addon.'\'
    imglnk=build_imglnk();
    //     -------------- Auslesen der Eingabefelder
    //
    // --- Textarea-Id und aktuelle Cursor-Position aus div-Containern auslesen
    var area_id   =document.getElementById(\''.$addon.'\').innerHTML;
    var pos=Number(document.getElementById(\'CURPOS\').innerHTML);
    //
    // --- Bild/Mediendatei-Link an die aktuelle Cusor-Position schreiben
    var textarea=opener.document.getElementById(area_id);
    var part1=textarea.value.substring(0,pos);
    var part2=textarea.value.substring(pos);
    var alles=part1 + imglnk + part2;
    textarea.value=alles;
    //
    // --- Markierung der Einfuegung
    textarea.selectionStart=pos;
    textarea.selectionEnd=pos + imglnk.length;
    textarea.focus();
    window.close();
   }
function source_code_editor_formular() {
    //   Oeffenen eines Fensters zur Suche einer Mediendatei und
    //   zur Konfigurierung von deren Eigenschaften
    //   benutzte functions:
    //      plugin_formular()
    //      choose_file(sice_id,rexval)
    //      insert_html()
    //
    // --- Textarea-Id und aktuelle Cursor-Position auslesen
    //     und im Quellcode des Popup-Fensters in div-Container schreiben
    var area_id=\'\';;
    var areas=document.getElementsByTagName(\'textarea\');
    for(let i=0; i<areas.length; i=i+1) {
       if(areas[i].id==\''.$addon.'\') {
         area_id=areas[i].id;
         break; }
       }
    if(area_id==\'\') {
      alert(\'keine Textarea mit Id "'.$addon.'" gefunden\');
      return; }
    var textarea=document.getElementById(area_id);
    var pos=0;
    if(\'selectionStart\' in textarea) { pos=textarea.selectionStart; }
    //
    // --- HTML-Code des Plugin-Formulars
    //           ----------------- AddOn \''.$addon.'\'
    var formular=plugin_formular();
    //           ----------------- Eigenschaftenmenue fuer Bild/Mediendatei
    //
    // --- Fenster-Daten
    var extra=\'width=600,height=600,top=20,left=20\';
    var newWin=window.open(\'\',\'\',extra);
    //
    // --- Fenster-Quellcode schreiben
    newWin.document.writeln(
\'<!doctype html>\n\' +
\'<html>\n\' +
\'<head>\n\' +
\'   <title>'.$addon::prop_title.'</title>\n\' +
\'   <link rel="stylesheet" href="'.$pfad.'.css">\n\' +
\'   <link rel="stylesheet" href="'.$assets.'addons/be_style/css/styles.css">\n\' +
\'   <script src="'.$pfad.'.js"></script>\n\' +
\'</head>\n\' +
\'<body class="medi_body">\n\' +
\'<!--------- Textarea-Id und Cursor-Position ------->\n\' +
\'<div id="'.$addon.'" class="medi_hidden">\' + area_id + \'</div>\n\' +
\'<div id="CURPOS" class="medi_hidden">\' + pos + \'</div>\n\' +
\'<!------------------------------------------------->\n\' +
formular +
\'<!--------- Einfuegen-/Abbrechen-Button ----------->\n\' +
\'<div align="right"><br>\n\' +
\'<button type="submit" class="btn btn-success"\n\' +
\'        title="'.$addon::button_text.'"\n\' +
\'        onclick="insert_html();">'.$addon::button_text.'</button>\n\' +
\'<button type="submit" class="btn btn-default" title="'.$addon::cancel_text.'"\n\' +
\'        onclick="window.close();">'.$addon::cancel_text.'</button>\n\' +
\'</div>\n\' +
\'<!------------------------------------------------->\n\' +
\'</body>\n\' +
\'</html>\');
    }
/* */
/* ==================== fuer die Beispieldarstellung im Backend ======= */
function show_example() {
    //   Beispieldarstellung Bild/Mediendatei nach der Konfigurierung
    //
    var rexval='.$xampleval.';
    var url=\''.$medurl_id.'\' + rexval;
    var lnt=\''.$lnktxt_id.'\' + rexval;
    var wdt=\''.$widtar_id.'\' + rexval;
    var medurl=document.getElementById(url).value;
    var lnktxt=document.getElementById(lnt).value;
    var widtar=\'\';
    var target=\'\';
    var inputtype=\'text\';
    var el_widtar=document.getElementById(wdt);
    if(el_widtar!=null) {
      var chk=el_widtar.checked;
      widtar=el_widtar.value;
      if(chk) { target=\'_blank\'; }
      inputtype=el_widtar.type;
      }
    if(inputtype==\'checkbox\') {
      var str=\'<a href="\' + medurl + \'" target="\' + target + \'">\';
      str=str + lnktxt + \'</a>\';
      } else {
      var str=\'<img src="\' + medurl + \'" width="\' + widtar + \'">\';
      if(lnktxt!=\'\') { str=str + \'<br>\' + lnktxt; }
      }
    document.getElementById(\''.$xample_id.'\').innerHTML=str;
    }
';
   #
   # --- (Ueber-)Schreiben der Javascript-Datei
   $dir=rex_path::addonAssets($addon);
   if(!file_exists($dir)) mkdir($dir);
   $file=$dir.$addon.'.js';
   $handle=fopen($file,'w');
   fwrite($handle,$buffer);
   fclose($handle);
   return file_exists($file);
   }
#
# --------------------------------------------------- Installation
public static function cache_ordner() {
   #   Erzeugen des AddOn-Cache-Ordners '/.../redaxo/src/cache/addons/'.self::this_addon.
   #   Dessen Pfad 'redaxo/src/cache/addons/'.self::this_addon wird zurueck gegeben.
   #
   $addon=self::this_addon;
   $cachedir=rex_path::addonCache($addon);
   $cachedir=substr($cachedir,0,strlen($cachedir)-1);
   if(!file_exists($cachedir)) mkdir($cachedir);
   if(file_exists($cachedir))
     return substr($cachedir,strlen(rex_path::base()));
   }
public static function cache_mediatype() {
   #   Einrichten des Cache-Medientyps self::cache_type samt Effekt 'mediapath'
   #   fuer den Cache-Pfad 'redaxo/src/cache/addons/'.self::this_addon.
   #   Zurueck gegebenen werden der Cache-Medientyp, dessen Id und die Id des
   #   zugehoerigen 'mediapath'-Effekts in Form eines assoziativen Arrays.
   #   benutzte functions:
   #      $addon::get_mediatype($mediapath)
   #      $addon::get_mediapath($mediatype)
   #
   $addon=self::this_addon;
   #
   # --- Cache-Medientyp schon vorhanden?
   $cachepath=rex_path::addonCache($addon);
   $cachepath=substr($cachepath,strlen(rex_path::base()));
   $cachepath=substr($cachepath,0,strlen($cachepath)-1);
   $mtype=$addon::get_mediatype($cachepath);
   $type_id=$mtype['id'];
   $cachetype=$mtype['name'];
   $effect_id=0;
   #
   # --- Datenbank-Abfragen
   $table1=rex::getTablePrefix().'media_manager_type';
   $table2=rex::getTablePrefix().'media_manager_type_effect';
   $sql=rex_sql::factory();
   #
   # --- Effekt 'mediapath' auch schon vorhanden?
   if($type_id>0):
     $effpath=$addon::get_mediapath($cachetype);
     $effect_id==0;
     if($effpath==$cachepath):
       $query='SELECT * FROM '.$table2.' WHERE effect=\'mediapath\' AND type_id='.$type_id;
       $effects=$sql->getArray($query);
       if(!empty($effects)) $effect_id=$effects[0]['id'];
       endif;
     if($effect_id>0)
       return array('name'=>$cachetype, 'type_id'=>$type_id, 'effect_id'=>$effect_id);
     endif;
   #
   # --- Cache-Medientyp neu anlegen
   if($type_id<=0):
     $name =$addon::cache_type;
     $descr=$cachepath;
     $admin=rex_backend_login::createUser()->getLogin();   // creatuser = Admin-User
     $jetzt=date('Y-m-d H:i:s',time());
     $query='INSERT INTO '.$table1.' (name,description,createdate,createuser,updatedate,updateuser) '.
            'VALUES (\''.$name.'\',\''.$descr.'\',\''.$jetzt.'\',\''.$admin.'\',\''.$jetzt.'\',\''.$admin.'\')';
     $sql->setQuery($query);
     #     und gleich auslesen: type_id
     $type_id=0;
     $query='SELECT * FROM '.$table1.' WHERE name=\''.$name.'\'';
     $typen=$sql->getArray($query);
     if(!empty($typen)):
       $type_id  =$typen[0]['id'];
       $cachetype=$typen[0]['name'];
       endif;
     endif;
   #
   # --- zugehoerigen 'mediapath'-Effekt neu anlegen
   if($type_id>0 and $effect_id<=0):
     $query='SELECT * FROM '.$table2.' WHERE type_id=1'; // systemseitiger Medientyp (status=1)
     $effects=$sql->getArray($query);
     $params=json_decode($effects[0]['parameters'],TRUE);
     $params['rex_effect_mediapath']['rex_effect_mediapath_mediapath']=$cachepath;
     $params=json_encode($params);
     $query='INSERT INTO '.$table2.' (type_id,effect,parameters,priority,createdate,createuser,updatedate,updateuser) '.
            'VALUES (\''.$type_id.'\',\'mediapath\',\''.$params.'\',1,\''.$jetzt.'\',\''.$admin.'\',\''.$jetzt.'\',\''.$admin.'\')';
     $sql->setQuery($query);   
     #     und gleich auslesen: effect_id
     $query='SELECT * FROM '.$table2.' WHERE effect=\'mediapath\' AND type_id='.$type_id;
     $effects=$sql->getArray($query);
     if(!empty($effects))
       return array('name'=>$cachetype, 'type_id'=>$type_id, 'effect_id'=>$effects[0]['id']);
     endif;
   return array('name'=>$cachetype, 'type_id'=>$type_id, 'effect_id'=>$effect_id);
   }
public static function install() {
   #   - (Ueber-)Schreiben der Stylesheet-Datei und der Javascript-Datei.
   #   - Erzeugen des AddOn-Cache-Ordners.
   #   - Einrichten des Cache-Medientyps zur Darstellung der Cache-Bilder.
   #   Rueckgabe eines nummerierten Ergebnis-Arrays (Nummerierung ab 1).
   #   benutzte functions:
   #      self::write_stylesheet()
   #      self::write_javascript()
   #      self::cache_ordner()
   #      self::cache_mediatype()
   #
   $addon=self::this_addon;
   $erg=array();
   #
   # --- Stylesheet und Javascript-Datei schreiben
   $erg[1]=self::write_stylesheet();
   $erg[2]=self::write_javascript();
   #
   # --- AddOn-Cache-Ordner einrichten
   $erg[3]=FALSE;
   $ordner=self::cache_ordner();
   if(!empty($ordner)) $erg[3]=TRUE;
   #
   # --- Einrichten des Cache-Medientyps zur Darstellung der Cache-Bilder
   $erg[4]=FALSE;
   $mt=self::cache_mediatype();
   if(!empty($mt['name']) and $mt['type_id']>0 and $mt['effect_id']>0) $erg[4]=TRUE;
   return $erg;
   }
public static function delete_cache() {
   #   Loeschen aller Cache-Dateien und des AddOn-Cache-Ordners. Wenn der
   #   Cache-Ordner nicht mehr existiert, wird TRUE zurueck gegeben.
   #
   $path=rex_path::addonCache(self::this_addon);
   $cachedir=substr($path,0,strlen($path)-1);
   $str=$cachedir.'/*';
   $files=glob($str);
   for($i=0;$i<count($files);$i=$i+1) unlink($files[$i]);
   if(file_exists($cachedir)):
     $bool=rmdir($cachedir);
     else:
     $bool=TRUE;
     endif;
     return $bool;
   }
public static function uninstall() {
   #   Loeschen des AddOn-Cache samt allen Cache-Dateien und Loeschen der
   #   Plugin-Dateien der HTML-Editoren CKEditor, TinyMCE, Redactor.
   #   Rueckgabe eines nummerierten Ergebnis-Arrays (Nummerierung ab 1).
   #   benutzte functions:
   #      self::delete_cache()
   #      $addon::plugin_paths($editor)
   #
   $erg=array();
   #
   # --- AddOn-Cache loeschen
   $erg[1]=self::delete_cache();
   #
   # --- HTML-Editor-Plugins loeschen
   $addon=self::this_addon;
   $paths=array();
   $paths[1]=$addon::plugin_paths($addon::plug_cke);
   $paths[2]=$addon::plugin_paths($addon::plug_tiny);
   $paths[3]=$addon::plugin_paths($addon::plug_redac);
   #
   for($k=1;$k<=count($paths);$k=$k+1):
      $kk=$k+1;
      $path=$paths[$k]['path'];
      $erg[$kk]['path']=$path;
      $files=$paths[$k]['files'];
      $arr=array();
      $brr=array();
      for($i=1;$i<=3;$i=$i+1):
         $file=$files[$i];
         if(empty($file)) continue;
         $arr[$i]['file']=$file;
         $absfile=$path.DIRECTORY_SEPARATOR.$file;
         if(file_exists($absfile)):
           $brr[$i]['del']=unlink($absfile);
           else:
           $brr[$i]['del']=FALSE;
           endif;
         endfor;
      $erg[$kk]['files']    =$arr;
      $erg[$kk]['files_del']=$brr;
      $erg[$kk]['path_del'] =FALSE;
      if(strpos($path,$addon::plug_redac)<=0):
        if(file_exists($path)):
          $erg[$kk]['path_del']=rmdir($path);
          else:
          $erg[$kk]['path_del']=FALSE;
          endif;
        endif;
      endfor;
   return $erg;
   }
}
?>
