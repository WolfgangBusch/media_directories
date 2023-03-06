<?php
/*
 * Media Directories AddOn
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version März 2023
 */
#
class media_pages {
#
#  Backend-Seiten
#     page_media_folders()
#     get_rex_value($slice_id,$rexval)
#     page_system_file_search()
#     page_file_selection()
#     editor_plugin($editor,$EDITOR,$descr)
#     page_editor_plugins()
#
# --- Konstanten
const this_addon=media_directories::this_addon;   // self::this_addon
#
# --------------------------------------------------- Backend-Seiten
public static function page_media_folders() {
   #   Ausgabe aller Unterordner des Medienordners sowie des Medienordners selbst
   #   zusammen mit dem zugehoerigen Medientyp (fuer den ein Effekt 'mediapath' mit
   #   dem Pfad des Unterordners definiert ist).
   #   benutzte functions:
   #      $addon::sample_media_dirs()
   #      $addon::get_mediatype($mediapath)
   #      $addon::find_media_category($mediatype)
   #      $addon::set_media_category($mediatype)
   #      $addon::delete_media_category($mediatype)
   #
   $addon=self::this_addon;
   $stra1='';
   $stra2='';
   $stra3='
</table>';
   $str='';
   #
   # ---------- Code zur Nutzung Top-Medienkategorien
   $ACC='access_control';
   $acc=FALSE;
   if(rex_addon::get($ACC)->isAvailable()) $acc=TRUE;
   if($acc):
     $checked='checked';
     $stra1='Top-Mediakategorie';
     $stra2='
<div>Für jeden Medienordner kann pro forma eine Top-Medienkategorie definiert werden,
um seine Mediendateien mittels <i>'.$ACC.'</i> vor öffentlichem Zugriff schützen zu
zu können (Bezeichnung: <tt>\''.$addon::cat_marker.'Medientyp\'</tt>).</div>';
     $stra3='
    <tr valign="top">
        <td colspan="3"></td>
        <td class="medi_pl">
            <input type="submit" class="btn btn-apply" name="sent" value="Kategorien einrichten"></td></tr>
</table>
</form>';
     $str='<form method="post">';
     #
     # --- Aktualisieren-Button geklickt?
     $sent='';
     if(isset($_POST['sent'])) $sent=$_POST['sent'];
     endif;
   # ------------------------------------------------
   #
   $str=$str.'
<table class="medi_btable">
    <tr valign="top">
        <td></td>
        <td class="medi_pl">
            <b>Pfad des Medienordners</b> (mediapath)</td>
        <td class="medi_pl" align="left">
            <b>zugehöriger Medientyp</b></td>
        <td class="medi_pl" align="left">
            <b>'.$stra1.'</b></td></tr>
    <tr valign="top">
        <td colspan="3"></td></tr>';
   #
   # --- Ausfiltern der Medienordner
   $dirs=$addon::sample_media_dirs();
   $m=0;
   for($i=1;$i<=count($dirs);$i=$i+1):
      $mediapath=$dirs[$i];
      $mtype=$addon::get_mediatype($mediapath);
      $mediatype=$mtype['name'];
      $inp='';
      if(empty($mediatype) or strpos($mtype['status'],'+++ ')>0):
        $mediatype='-';
        $nr='';
        if($acc) $inp='-';
        $class=' medi_small';
        else:
        $m=$m+1;
        $nr=$m.')';
        $class='';
        #
        # ---------- Code zur Nutzung Top-Medienkategorien
        if($acc):
          #     Checkbox auslesen
          if(isset($_POST[$mediatype])):
            $chk=$_POST[$mediatype];
            $topmedcat=$addon::set_media_category($mediatype);
            else:
            $chk='';
            if(!empty($sent)):
              $topmedcat=$addon::delete_media_category($mediatype);
              else:
              $topmedcat=$addon::find_media_category($mediatype);
              if($topmedcat['id']>0) $chk=$checked;
              endif;
            endif;
          $inp='<input type="checkbox" name="'.$mediatype.'" value="'.$checked.'" '.$chk.'>';
          if($mediatype==$addon::def_type)
            $inp='<span class="medi_small">(hat keine Medienkategorie)</span>';
          endif;
        # ------------------------------------------------
        #
        endif;
      $str=$str.'
    <tr valign="top">
        <td class="medi_pl" align="right">'.$nr.'</td>
        <td class="medi_pl'.$class.'">'.$mediapath.'</td>
        <td class="medi_pl'.$class.'">'.$mediatype.'</td>
        <td class="medi_pl'.$class.'">'.$inp.'</td></tr>';
      endfor;
   #
   # --- Ausgaben
   $base=basename(rex_url::media());
   echo '
<div>Das sind die derzeit definierten <b>'.$m.' Medienordner</b> (\''.$base.
'\' und Unterordner von \''.$base.'\').<br>
Fehlende Medientypen können nötigenfalls mit dem Medienmanager ergänzt werden.</div>'.
   $stra2.'<br>'.$str.$stra3.'
<p>&nbsp;</p>
<p>&nbsp;</p>
';
   }
public static function get_rex_value($slice_id,$rexval) {
   #   Rueckgabe des Wertes einer REX-Variablen aus der Tabelle rex_article_slice.
   #   Der aktuelle Slice wird ueber die Variablen REX_SLICE_ID ermittelt.
   #   Sollte REX_SLICE_ID noch keinen Wert haben (Slice noch nicht gespeichert),
   #   so wird ein leerer String zurueck gegeben.
   #   $slice_id           Slice-Id (Wert der Variablen REX_SLICE_ID)
   #   $rexval             Nummer der REX-Varablen fuer den Medien-URL
   #                       (z.B.: 3 fuer REX_VALUE[3])
   #
   if(intval($slice_id)<=0) return '';
   $sql=rex_sql::factory();
   $slices=$sql->getArray('SELECT * FROM rex_article_slice WHERE id='.$slice_id);
   if(!isset($slices[0])) return '';
   $value='value'.$rexval;
   return $slices[0][$value];
   }
public static function page_system_file_search() {
   #   Ausgabe des HTML-Codes fuer die Select-Auswahl eines Medienordners sowie die
   #   Ausgabe der Liste der im gewaehlten Ordner befindlichen Dateien, ggf. gemaess
   #   gefilterten Dateinamen. Die Auswahl startet mit einem Ordner, in dem eine
   #   bereits vorher gewaehlte und in einem Slice abgespeicherte Mediendatei liegt.
   #   ++++++ Quellcode der Backend-Seite 'system_file_search.php' (Mediendateien)
   #   ++++++ Die Seite liegt im Hauptmenue und ist als verborgen definiert
   #   benutzte functions:
   #      self::get_rex_value($slice_id,$rexval)
   #      $addon::file_from_url($medurl)
   #      $addon::mediapath_from_url($medurl)
   #      $addon::menue_ordner($mediapath,$filter)
   #      $addon::menue_datei($rexval,$mediapath,$selfile,$filter)
   #
   $addon=self::this_addon;
   #
   # --- REX_VALUE-Id und Slice-Id aus Seiten-URL auslesen
   $rexval='';
   if(isset($_GET[$addon::rexval_id])) $rexval=$_GET[$addon::rexval_id];
   $slice_name=$addon::slice_id;
   $slice_id='';
   if(isset($_GET[$slice_name])) $slice_id=$_GET[$slice_name];
   #
   # --- im Slice gespeicherten URL auslesen
   $selurl=self::get_rex_value($slice_id,$rexval);
   if(empty($selurl)) $selurl=rex_media_manager::getUrl($addon::def_type,'');
   #
   # --- Ausfiltern des Dateinnamens aus dem Medien-URL
   $selfile=$addon::file_from_url($selurl);
   #
   # --- Medienordner aus $_POST, falls leer: aus URL ableiten, Default 'media'
   $mediapath='';
   if(isset($_POST[$addon::sel_name])) $mediapath=$_POST[$addon::sel_name];
   if(empty($mediapath)) $mediapath=$addon::mediapath_from_url($selurl);
   if(empty($mediapath)) $mediapath=basename(rex_url::media());   // 'media', zum Start
  #
   # --- Dateinamen-Filter aus $_POST
   $filter='';
   if(isset($_POST[$addon::filt_name])) $filter=$_POST[$addon::filt_name];
   #
   # --- Auswahlmenue Ordner und Dateinamenfilter
   echo $addon::menue_ordner($mediapath,$filter);
   #
   # --- Auswahlmenue fuer die Mediendateien im gewaehlten Ordner
   $addon::menue_datei($rexval,$mediapath,$selfile,$filter);
   }
public static function page_file_selection() {
   #   Ausgabe des HTML-Codes fuer einen Ausloese-Button zur Mediendatei-Auswahl
   #   und zur Kontrollanzeige der erfolgten Auswahl.
   #   benutzte functions:
   #      $addon::file_selection($slice_id,$rexval,$medurl,$widtar,$text)
   #
   $addon=self::this_addon;
   echo '
<h4 align="center">Formular zur Auswahl und zur Konfigurierung der Darstellung von Mediendateien</h4>
<div align="center" class="medi_error">zur rein exemplarischen Verwendung</div>
<div class="medi_td medi_tdbg"><br>'.
   $addon::file_selection('',$addon::xample_val,'','','').'
<br></div>
<br><br>
<button type="submit" class="btn btn-success" onclick="show_example(); return false;">
Darstellung gemäß obiger Konfigurierung erneuern
</button> &nbsp; (Anzeige unten)
<br><br>
<div id="'.$addon::xample_id.'"></div>
<div>&nbsp;</div>';
   }
public static function editor_plugin($editor,$EDITOR,$descr) {
   #   Teilausgaben des HTML-Codes fuer die Einrichtung eines Plugins fuer
   #   einen der HTML-Editoren.
   #   $editor             Ordnername/Addon-Id des Editors
   #   $EDITOR             Bezeichnung des Editors
   #   $descr              Beschreibung zum Editor, falls dieser nicht verfuegbar
   #                       ist, ueberschreiben mit '... nicht verfuegbar...'
   #   benutzte functions:
   #      media_plugins::write_cke()
   #      media_plugins::write_tinymce()
   #      media_plugins::write_redactor()
   #
   $addon=self::this_addon;
   #
   # --- Editor-Parameter
   $edvers='';
   if($editor==$addon::plug_cke):
     $vers=$addon::vers_cke;
     if(rex_addon::get($editor)->isAvailable())
       $edvers=rex_addon::get($editor)->getAddon()->getProperty('version');
     endif;
   if($editor==$addon::plug_tiny):
     $vers=$addon::vers_tiny;
     if(rex_addon::get($editor)->isAvailable())
       $edvers=rex_addon::get($editor)->getAddon()->getProperty('vendor');
     endif;
   if($editor==$addon::plug_redac):
     $vers=$addon::vers_redac;
     if(rex_addon::get($editor)->isAvailable())
       $edvers=rex_addon::get($editor)->getAddon()->getProperty('vendor_versions')[$editor];
     endif;
   $edvers=explode('.',$edvers)[0];   // Verkuerzung auf Hauptversion (1 Ziffer)
   #
   # --- Plugin verfuegbar?
   if($edvers==$vers):
     $da='';
     $cl='';
     else:
     $da=' disabled';
     $cl=' medi_silver';
     $descr='<span class="'.$cl.'">'.$EDITOR.' '.$vers.' nicht verfügbar</span>';
     endif;
   #
   # --- Plugin-Formular
   echo '
    <tr valign="top">
        <td class="medi_pl medi_center'.$cl.'">
            <form method="post"><br>
            <button type="submit"'.$da.' name="'.$editor.'" value="yes" class="btn btn-apply">
            '.$EDITOR.'
            </button><br>(Vers. '.$vers.')</form></td>
        <td class="medi_pl"><br>
            '.$descr.'</td></tr>';
   $plugin='';
   if(isset($_POST[$editor])) $plugin=$_POST[$editor];
   #
   # --- Plugin einrichten
   if($plugin=='yes'):
     if($editor==$addon::plug_cke)   $bool=media_plugins::write_cke();
     if($editor==$addon::plug_tiny)  $bool=media_plugins::write_tinymce();
     if($editor==$addon::plug_redac) $bool=media_plugins::write_redactor();
     if($bool):
       $stview=rex_view::info('Plugin eingerichtet: &nbsp; <b>'.$EDITOR.' '.$vers.'</b>');
       else:
       $stview=rex_view::warning('Plugin konnte nicht eingerichtet werden: &nbsp; <b>'.$EDITOR.' '.$version.'</b>');
       endif;
     echo '<div>'.$stview.'</div>';
     endif;
   }
public static function page_editor_plugins() {
   #   Ausgabe des HTML-Codes fuer die wahlweise Einrichtung je eines Plugins
   #   fuer: CKEditor (Vers. 4), TinyMCE (Vers. 5), Redactor (Vers. 3).
   #   benutzte functions:
   #      $addon::plugin_paths($editor)
   #      self::editor_plugin($editor,$EDITOR,$descr)
   #
   $addon=self::this_addon;
   $rexpos=strlen(rex_path::base())-1;
   $icon=rex_url::addonAssets($addon,$addon.'.svg');
   $pos=strpos($icon,DIRECTORY_SEPARATOR);
   if($pos>0) $icon=substr($icon,$pos);
   echo '
<h4><br>Installation eines Plugins für ausgewählte HTML-Editoren</h4>
<div class="medi_pl">
<div>Das Verfahren zum Einfügen von Mediendateien kann auch in ausgewählte HTML-Editoren
eingebunden werden. Das geschieht durch Einrichtung entsprechender <code>Plugins</code>
für die Editoren. Die Plugins müssen anschließend noch in den Profil-Konfigurationen
des jeweiligen Editors aktiviert werden.<br>
Auswahl und Einfügen der Mediendateien erfolgen dann über ein zusätzliches klickbares
Icon &nbsp; <img src="'.$icon.'"> &nbsp; in der Toolbar.<br>
Die Ordner und Dateien der eingerichteten Plugins werden mit der De-Installation wieder
entfernt.</div>';
   #
   echo '
<br>
<table class="medi_table">
    <tr><td colspan="2">
        <u>Plugin einrichten für:</u></td></tr>';
   #
   # --- CKEditor 4
   $editor=$addon::plug_cke;
   $EDITOR='CKEditor';
   $ordner=substr($addon::plugin_paths($editor)['path'],$rexpos);
   $descr ='Die Dateien des Plugins werden angelegt im Ordner:
            <div class="medi_pl medi_small">'.$ordner.'</div>
            <u>Aktivierung in jedem '.$EDITOR.'-Profil durch:</u>
            <div>- zusätzlich benutztes Plugin einfügen:</div>
            <div class="medi_pl"><tt class="medi_td medi_tdbg">
            extraPlugins:&nbsp;[\'rex_help\',&nbsp;<code>\''.$addon.'\'</code>],</tt></div>
            <div>- zusätzlicher Toolbar-Eintrag (an passender Position):</div>
            <div class="medi_pl"><tt class="medi_td medi_tdbg">[\'rex_help\', <code>\''.$addon.'\'</code>],</tt></div>';
   self::editor_plugin($editor,$EDITOR,$descr);
   #
   # --- TinyMCE 5
   $editor=$addon::plug_tiny;
   $EDITOR='TinyMCE';
   $ordner=substr($addon::plugin_paths($editor)['path'],$rexpos);
   $descr ='Die Dateien des Plugins werden angelegt im Ordner:
            <div class="medi_pl"><span class="medi_small">'.$ordner.'</span></div>
            <div><u>Aktivierung in jedem '.$EDITOR.'-Profil durch:</u></div>
            <div>- zusätzlich benutztes Plugin einfügen:</div>
            <div class="medi_pl"><tt class="medi_td medi_tdbg">plugins: \'... <code>'.$addon.'</code> ...\',</tt></div>
            <div>- zusätzlicher Toolbar-Eintrag (an passender Position):
            <div class="medi_pl"><tt class="medi_td medi_tdbg">toolbar: \'... <code>'.$addon.'</code> ...\',</tt></div>
            <div>- zusätzliche Zeile:</div>
            <div class="medi_pl"><tt class="medi_td medi_tdbg">icons: <code>\''.$addon.'\'</code>,</tt></div>';
   self::editor_plugin($editor,$EDITOR,$descr);
   #
   # --- Redactor 3
   $editor=$addon::plug_redac;
   $EDITOR='Redactor';
   $ordner=substr($addon::plugin_paths($editor)['path'],$rexpos);
   $descr ='Diese Plugin-Datei wird angelegt:
            <div class="medi_pl"><span class="medi_small">'.$ordner.DIRECTORY_SEPARATOR.$addon.'.js</span></div>
            <div><u>Aktivierung in jedem '.$EDITOR.'-Profil durch:</u></div>
            <div>- zusätzlicher Toolbar-Eintrag (an passender Position):
            <div class="medi_pl"><tt class="medi_td medi_tdbg">... ,image, <code>'.$addon.'</code>, ...,</tt><br>
            (hier hinter dem Eintrag für den Medienpool)</div>';
   self::editor_plugin($editor,$EDITOR,$descr);
   #
   echo '
</table>
</div>';
   #
   echo '
<div>&nbsp;</div>';
   }
}
?>
