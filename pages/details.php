<?php
/*
 * Media Directories AddOn
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version März 2023
 */
   $addon=media_directories::this_addon;
   $width=$addon::widover;
   $img=rex_url::addonAssets($addon,$addon.'.svg');
   $pos=strpos($img,DIRECTORY_SEPARATOR);
   if($pos>0) $img=substr($img,$pos);
   $button='<button class="btn btn-default medi_bottom"><img src="'.$img.'"></button>';
   #
   # --- Javascript-Funktionen
   echo '
<div><b>Javascript-Funktionen:</b></div>
   <div class="medi_pl">Die Funktionen für Auswahlmenüs und das Einfügen von
   HTML-Quellcode werden mit der Installation im AddOn-Assets-Ordner abgelegt.
   Damit stehen bereits die Tools für einen Quellcode-Editor und eine
   (exemplarische) Dateiauswahl zur Verfügung.</div>';
   #
   # --- AddOn-Cache
   echo '
<div><br><b>AddOn-Cache</b></div>
   <div class="medi_pl">Mit der Installation wird der Ordner für den
   AddOn-Cache angelegt. In diesem werden verkleinerte Kopien der Bilder
   aus den Medienordnern abgelegt, um eine Ressourcen schonende Anzeige
   in Auswahlmenüs zu ermöglichen. Das gilt für Dateien der MIME-Typen
   <i>jpeg, png, gif, bmp, webp</i>, die breiter als '.$width.' Pixel
   sind. - Zur Darstellung der Cache-Bilder wird ein eigener Medientyp
   erzeugt.</div>';
   #
   # --- Zugriffskontrolle
   echo '
<div><br><b>Kontrolle des Zugriffs auf die Mediendateien:</b></div>
   <div class="medi_pl">Das AddOn <i>access_control</i> bietet die Möglichkeit,
   Besuchern im Frontend für den Zugriff auf Mediendateien eine
   Authentifizierung abzuverlangen. Der Zugriffsschutz von Dateien basiert
   dabei auf deren Zugehörigkeit zu einer Top-Medienkategorie. Eine solche
   kann hier (pro forma) für jeden Medienordner eingerichtet werden. -
   Die konkrete Einrichtung des Zugriffsschutz erfolgt dann, wie in
   <i>access_control</i> beschrieben.</div>';
   #
   # --- Quellcode-Editor mit Insert-Button
   echo '
<div><br><b>Quellcode-Editor mit einem Insert-Button &nbsp; '.$button.'</b></div>
   <div class="medi_pl">
   Mit dem <code>Insert-Button</code> kann eine Mediendatei aus den
   Medienordnern ausgewählt und an der Stelle des Cursors im Eingabefeld
   des Editors als HTML-Code eingefügt werden. Der folgende Modul stellt einen
   entsprechend ausgestatteten Quellcode-Editor dar. Dessen Eingabefeld muss
   durch eine <code>vorgeschriebene Id</code> qualifiziert sein.';
   echo '
   <div><b>Eingabeteil:</b></div>
      <div class="medi_pl"><div class="medi_td medi_tdbg"><tt>
      &lt;?php <code>'.$addon.'::insert_button();</code> ?&gt;<br>
      &lt;br&gt;<br>
      &lt;textarea
      <div class="medi_pl">
         class="form-control" rows="10"<br>
         <code>id="'.$addon.'"</code><br>
         name="REX_INPUT_VALUE[1]"&gt;REX_VALUE[1]&lt;/textarea&gt;</div>
      </tt></div></div>';
   echo '
   <div><b>Ausgabeteil:</b></div>
      <div class="medi_pl"><div class="medi_td medi_tdbg"><tt>
      REX_VALUE[id=1 output=html]
      </tt></div></div></div>
';
   #
   # --- Einfuegen einer Mediendatei
   echo '
<div><br><b>Einfügen einer Mediendatei als eigener Block in einem Artikel</b></div>
   <div class="medi_pl">
   In einem entsprechenden Modul werden der Mediamanager-URL der Datei und
   die Darstellungsformate (Bildbreite und Untertitel bzw. Zielseite und Linktext)
   in drei aufeinander folgenden REX-Variablen gespeichert. <code>Die erste
   REX-Variable ist frei wählbar</code> und enthält den Mediamanager-URL der
   Datei. Die nächste und die übernächste REX-Variable enthalten die
   Darstellungsformate.';
   echo '
   <div><b>Eingabeteil:</b></div>
      <div class="medi_pl"><div class="medi_td medi_tdbg"><tt>
      &lt;?php<br>
      $sid="REX_SLICE_ID";<br>
      $rex=<code><b>1</b></code>;<br>
      <code>$url</code>="REX_VALUE[<code><b>1</b></code>]";<br>
      $wta="REX_VALUE[2]";<br>
      $txt="REX_VALUE[3]";<br>
      <code>'.$addon.'::select_form($sid,$rex,$url,$wta,$txt);</code><br>
      ?&gt;
      </tt></div></div>';
   echo '
   <div><b>Ausgabeteil:</b></div>
      <div class="medi_pl"><div class="medi_td medi_tdbg"><tt>
      &lt;?php<br>
      <code>$url</code>="REX_VALUE[<code><b>1</b></code>]";<br>
      $wta="REX_VALUE[2]";<br>
      $txt="REX_VALUE[3]";<br>
      <code>'.$addon.'::show_link($url,$wta,$txt);</code><br>
      ?&gt;
      </tt></div></div></div>';
   #
   echo '
<div>&nbsp;</div>';
?>