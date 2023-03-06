<?php
/*
 * Media Directories AddOn
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version März 2023
 */
#
class media_plugins {
#
#  Plugin-Dateien
#     write_cke()
#     write_tinymce()
#     write_redactor()
#
# --- Konstanten
const this_addon=media_directories::this_addon;   // self::this_addon
#
# --------------------------------------------------- Plugin-Dateien
public static function write_cke() {
   #   Definition und Schreiben der Javascript-Funktionen fuer das CKEditor-Plugin.
   #   Falls die Dateien anschliessend existieren, wird TRUE zurueck gegeben.
   #   benutzte functions:
   #      $addon::plugin_paths($editor)
   #
   # --- CKEditor in Version 4 installiert?
   $addon =self::this_addon;
   $editor=$addon::plug_cke;
   if(!rex_addon::get($editor)->isInstalled()):
     rex_view::warning('CKEditor 4 ist nicht verfügbar');
     return;
     endif;
   $paths=$addon::plugin_paths($editor);
   #
   # --- Ordnerpfad fuer die CKEditor-Plugin-Dateien
   $dir=$paths['path'];
   if(!file_exists($dir)) mkdir($dir);
   $relfile1=$paths['files'][1];
   $relfile2=$paths['files'][2];
   #
   # --- URL des CKEditor-Toolbox-Icons
   $icon    =$paths['icon'];
   $icon_url=$paths['icon_url'];
   #
   # --- Plugin-Definitions-Datei
   $buffer='/**
 * CKEditor 4 Plugin Definition
 * for Redaxo 5 AddOn \''.$addon.'\'
 */
CKEDITOR.plugins.add(\''.$addon.'\',{
    init: function(editor) {
        editor.addCommand(\''.$addon.'\',new CKEDITOR.dialogCommand(\''.$addon.'Dialog\'));
        editor.ui.addButton(\''.$addon.'\',{
            label: \''.$addon::button_title.'\',
            command: \''.$addon.'\',
            toolbar: \''.$addon.'\',
            icon:    \''.$icon_url.'\'
            });
        CKEDITOR.dialog.add(\''.$addon.'Dialog\',this.path + \''.$addon.'.js\');
        }
    });';
   #     (Ueber-)Schreiben der Definitions-Datei
   $file1=$dir.DIRECTORY_SEPARATOR.$relfile1;
   $handle=fopen($file1,'w');
   fwrite($handle,$buffer);
   fclose($handle);
   #
   # --- Plugin-Dialog-Datei
   $buffer='/**
 * CKEditor 4 Plugin Dialog Definition
 * for Redaxo 5 AddOn \''.$addon.'\'
 */
CKEDITOR.dialog.add(\''.$addon.'Dialog\',function(editor){
    return {
        title:\''.$addon::prop_title.'\',
        minWidth:  400,
        minHeight: 400,
        contents: [{
            elements: [{
                type: \'html\',
                   // ----------------- AddOn \''.$addon.'\'
                html: plugin_formular()}]
                   // ----------------- Eigenschaftenmenue fuer Bild/Mediendatei
                }],
        buttons: [
            CKEDITOR.dialog.okButton.override({label: \''.$addon::button_text.'\'}),
            CKEDITOR.dialog.cancelButton.override({label: \''.$addon::cancel_text.'\'})
            ],
        onOk: function() {
                     // -------------- AddOn \''.$addon.'\'
            var sInsert=build_imglnk();
                     // -------------- Auslesen der Eingabefelder
            if(sInsert.length>0) editor.insertHtml(sInsert);
            }
        };
    });';
   #     (Ueber-)Schreiben der Dialog-Datei
   $file2=$dir.DIRECTORY_SEPARATOR.$relfile2;
   $handle=fopen($file2,'w');
   fwrite($handle,$buffer);
   fclose($handle);
   #
   if(file_exists($file1) and file_exists($file2) and file_exists($icon)) return TRUE;
   }
public static function write_tinymce() {
   #   Definition und Schreiben der Javascript-Funktionen fuer das TinyMCE-Plugin.
   #   Falls die Dateien anschliessend existieren, wird TRUE zurueck gegeben.
   #   benutzte functions:
   #      $addon::plugin_paths($editor)
   #
   # --- TinyMCE in Version 5 installiert?
   $addon =self::this_addon;
   $editor=$addon::plug_tiny;
   if(!rex_addon::get($editor)->isInstalled()):
     rex_view::warning('TinyMCE 5 ist nicht verfügbar');
     return;
     endif;
   $paths=$addon::plugin_paths($editor);
   #
   # --- svh-Tag des TinyMCE-Toolbox-Icons
   $icon=$paths['icon'];
   $contents=file_get_contents($icon);
   $arr=explode('<svg ',$contents);
   $svg='<svg '.$arr[1];
   $svg=str_replace("\n",'',$svg);
   #
   # --- Ordnerpfad fuer die TinyMCE-Plugin-Dateien
   $dir=$paths['path'];
   if(!file_exists($dir)) mkdir($dir);
   $relfile1=$paths['files'][1];
   $relfile2=$paths['files'][2];
   $relfile3=$paths['files'][3];
   #
   # --- Plugin-Definitions-Datei
   $buffer='/**
 * TinyMCE 5 Plugin Dialog Definition
 * for Redaxo 5 AddOn \''.$addon.'\'
 */ 
(function() {
    \'use strict\';
    var register$1=function(editor) {
        editor.addCommand(\'InsertMediaFile\', function() {
            editor.execCommand(\'mceInsertContent\', false, \'\');
            });
        };
    var register=function(editor) {
        var onAction=function() { return editor.execCommand(\'InsertMediaFile\'); };
        editor.ui.registry.addIcon(\''.$addon.'\',
            \''.$svg.'\'
            ),
        editor.ui.registry.addButton(\''.$addon.'\', {
            icon:      \''.$addon.'\',
            tooltip:   \''.$addon::button_title.'\',
            onAction:  onAction
            });
        };
    function Plugin() {
        tinymce.util.Tools.resolve(\'tinymce.PluginManager\').add(\''.$addon.'\', function(editor) {
            register$1(editor);
            register(editor);
            });
        }
    Plugin();
    }());';
   #     (Ueber-)Schreiben der Definitions-Datei
   $file1=$dir.DIRECTORY_SEPARATOR.$relfile1;
   $handle=fopen($file1,'w');
   fwrite($handle,$buffer);
   fclose($handle);
   #
   # --- Plugin-Dialog-Datei
   $buffer='/**
 * TinyMCE 5 Plugin Dialog Definition
 * for Redaxo 5 AddOn \''.$addon.'\'
 */
!function(){
    \'use strict\';
    tinymce.util.Tools.resolve(\'tinymce.PluginManager\').add(\''.$addon.'\', function(editor) {
        var onAction=function() { return editor.execCommand(\'InsertMediaFile\') };
        var search=editor;
        return editor.addCommand(\'InsertMediaFile\', function() {
            search.windowManager.open({
                title:  \''.$addon::prop_title.'\',
                resize: \'both\',
                body: {
                    type:   \'panel\',
                    items:  [
                        {name:  \'code\',
                         type:  \'htmlpanel\',
                             // ----------------- AddOn \''.$addon.'\'
                         html:  plugin_formular()}
                             // ----------------- Eigenschaftenmenue fuer Bild/Mediendatei
                        ]
                    },
                buttons: [
                    {type:  \'submit\', text: \''.$addon::button_text.'\', primary: !0},
                    {type:  \'cancel\', text: \''.$addon::cancel_text.'\'}
                    ],
                onSubmit:  function(editor) {
                             // -------------- AddOn \''.$addon.'\'
                    var sInsert=build_imglnk();
                             // -------------- Auslesen der Eingabefelder
                    search.execCommand(\'mceInsertContent\', !1, sInsert);
                    editor.close()
                    }
                });
            }),
        editor.ui.registry.addIcon(\''.$addon.'\',
            \''.$svg.'\'
            ),
        editor.ui.registry.addButton(\''.$addon.'\', {
            icon:      \''.$addon.'\',
            tooltip:   \''.$addon::button_title.'\',
            onAction:  onAction
            })
        })
    }();';
   #     (Ueber-)Schreiben der Dialog-Datei
   $file2=$dir.DIRECTORY_SEPARATOR.$relfile2;
   $handle=fopen($file2,'w');
   fwrite($handle,$buffer);
   fclose($handle);
   #
   # --- Plugin-Index-Datei
   $buffer='// Exports the \''.$addon.'\' plugin for usage with module loaders
// Usage:
//   CommonJS:  require(\'tinymce/plugins/'.$addon.'\')
//   ES2015:    import \'tinymce/plugins/'.$addon.'\'
require(\'./plugin.js\');';
   #     (Ueber-)Schreiben der Index-Datei
   $file3=$dir.DIRECTORY_SEPARATOR.$relfile3;
   $handle=fopen($file3,'w');
   fwrite($handle,$buffer);
   fclose($handle);
   if(file_exists($file1) and file_exists($file2) and
      file_exists($file3) and file_exists($icon)) return TRUE;
   }
public static function write_redactor() {
   #   Definition und Schreiben der Javascript-Funktionen fuer das Redactor-Plugin.
   #   Falls die Dateien anschliessend existieren, wird TRUE zurueck gegeben.
   #   benutzte functions:
   #      $addon::plugin_paths($editor)
   #
   # --- Redactor in Version 3 installiert?
   $addon =self::this_addon;
   $editor=$addon::plug_redac;
   if(!rex_addon::get($editor)->isInstalled()):
     rex_view::warning('Redactor 3 ist nicht verfügbar');
     return;
     endif;
   $paths=$addon::plugin_paths($editor);
   #
   # --- Ordnerpfad der Redactor-Plugin-Datei
   $dir=$paths['path'];
   $relfile1=$paths['files'][1];
   #
   # --- URL des Toolbox-Icons fuer Redactor
   $icon    =$paths['icon'];
   $icon_url=$paths['icon_url'];
   #
   # --- Plugin-Datei
   $buffer='/**
 * Redactor 3 Plugin
 * for Redaxo 5 AddOn \''.$addon.'\'
 */
(function($R) {
    $R.add(\'plugin\', \''.$addon.'\', {
        //
        // benoetigte Redactor-Tools
        init: function(app) {
            this.app       = app;
            this.toolbar   = app.toolbar;
            this.selection = app.selection;
            this.insertion = app.insertion;
        },
        //
        // Einfuegen des Toolbar-Buttons
        start: function() {
            var buttonData = {
                title: \''.$addon::button_title.'\',
                api:   \'plugin.'.$addon.'.open\'
            };
            let button = this.toolbar.addButton(\''.$addon.'\', buttonData);
            button.setIcon(\'<img src="'.$icon_url.'">\');
        },
        //
        // Oeffnen des Eigenschaftenfensters
        open: function() {
            var options = {
                title:  \''.$addon::prop_title.'\',
                width:  \'700px\',
                height: \'600px\',
                name:   \'property_window\',
                handle: \'insert\',
                commands: {
                    insert: {title: \''.$addon::button_text.'\' },
                    cancel: {title: \''.$addon::cancel_text.'\' }
                }
            };
            this.app.api(\'module.modal.build\', options);
        },
        //
        // Aktionen im Eigenschaftenfenster
        modals: {
                            // ----------------- AddOn \''.$addon.'\'
            \'property_window\': plugin_formular()
                            // ----------------- Eigenschaftenmenue fuer Bild/Mediendatei
        },
        onmodal: {
            \'property_window\': {
                insert: function($modal, $form) {
                             // -------------- AddOn \''.$addon.'\'
                    let sInsert=build_imglnk();
                             // -------------- Auslesen der Eingabefelder
                    this._insert(sInsert);
                },
            }
        },
        //
        // Einfuegen des HTML-Codes in die Textarea
        _insert: function(data) {
            // Schliessen des Eigenschaftenfensters
            this.app.api(\'module.modal.close\');
            this.insertion.insertRaw(data);
        }
    });
})(Redactor);';
   #     (Ueber-)Schreiben der Datei
   $file1=$dir.DIRECTORY_SEPARATOR.$relfile1;
   $handle=fopen($file1,'w');
   fwrite($handle,$buffer);
   fclose($handle);
   #
   if(file_exists($file1) and file_exists($icon)) return TRUE;
   }
}
?>
