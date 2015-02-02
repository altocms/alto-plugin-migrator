<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */

/**
 * Запрещаем напрямую через браузер обращение к этому файлу.
 */
if (!class_exists('Plugin')) {
    die('Hacking attempt!');
}

class PluginMigrator extends Plugin {

    protected $aInherits
        = array(
            'action' => array(
                'ActionMigrator',
            ),
        );


    /**
     * Активация плагина
     */
    public function Activate() {

        return true;
    }

    /**
     * Инициализация плагина
     */
    public function Init() {

        //E::ModuleViewer()->AppendStyle(Plugin::GetTemplateDir(__CLASS__) . 'css/style.css');
    }

}

// EOF