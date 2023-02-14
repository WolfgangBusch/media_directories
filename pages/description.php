<?php
/*
 * Media Directories AddOn
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version Februar 2023
 */
echo '
<div><b>Medienordner:</b>
<div class="medi_pl">Mediendateien können per Auswahlmenü in Artikel
eingefügt werden, wenn sie in \'Medienordnern\' liegen. Das sind alle
Unterordner des Ordners \''.basename(rex_url::media()).'\', für die
vorher ein Medientyp mit dem Effekt \'mediapath\' (Datei: Pfad anpassen)
eingerichtet wurde, sowie der Ordner \''.basename(rex_url::media()).'\'
selbst. Die Ordner können - wie Redaxo-Systemordner - gegen direkten
Zugriff geschützt werden, z.B. über  eine .htaccess-Datei mit den Zeilen
\'<tt>Order deny,allow</tt>\' und \'<tt>Deny from all</tt>\'.</div>
</div>

<div><br><b>Einfügen von Mediendateien:</b>
<div class="medi_pl">Eine Bilddatei wird mittels img-tag eingefügt, eine
sonstige Datei als Link (a-tag) auf dieselbe. Die Adressierung erfolgt
jeweils mittels Mediamanager-URL, basierend auf dem Medientyp des
jeweiligen Medienordners. Zusätzlich können Bildbreite und Untertitel
bzw. Zielseite (gleicher oder neuer Browser-Tab) und Linktext als
Darstellungsformate ergänzt werden.</div>
</div>
';
?>
