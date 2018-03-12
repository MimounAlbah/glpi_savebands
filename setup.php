<?php

define("PLUGIN_RTNTESTALEX_VERSION", "1.0.0");

// Minimal GLPI version, inclusive
define("PLUGIN_RTNTESTALEX_GLPI_MIN_VERSION", "0.91");

// Init the hooks of the plugins -Needed
function plugin_init_rtntestalex() {
    global $PLUGIN_HOOKS, $CFG_GLPI, $LANG;

    $PLUGIN_HOOKS['csrf_compliant']['rtntestalex'] = true;

    //On instancie un nouvel objet 
    $plugin = new Plugin();

    //On test si le plugin est installé et activé dans GLPI grâce à des fonctions existantes 
    if ($plugin->isInstalled('rtntestalex') && $plugin->isActivated('rtntestalex')) {


        $PLUGIN_HOOKS['add_css']['rtntestalex'] = array('rtntestalex.css');

        $PLUGIN_HOOKS['item_purge']['rtntestalex'] = array();

        foreach (PluginRtntestalexTask_Item::getClasses() as $type) {
            $PLUGIN_HOOKS['item_purge']['rtntestalex'][$type] = 'plugin_item_purge_rtntestalexTask';
        }

        foreach (PluginRtntestalexReport_Item::getClasses() as $type) {
            $PLUGIN_HOOKS['item_purge']['rtntestalex'][$type] = 'plugin_item_purge_rtntestalexReport';
        }

        foreach (PluginRtntestalexArchive_Item::getClasses() as $type) {
            $PLUGIN_HOOKS['item_purge']['rtntestalex'][$type] = 'plugin_item_purge_rtntestalexArchive';
        }

        //Permet d'executer la fonction profileRightUpdate lors de l'update d'un item
        $PLUGIN_HOOKS['item_update']['rtntestalex']['ProfileRight'] = 'plugin_rtntestalex_profileRightUpdate';

        //Permet d'executer la fonction profileRightUpdate lors de l'ajout d'un item 
        $PLUGIN_HOOKS['item_add']['rtntestalex']['ProfileRight'] = 'plugin_rtntestalex_profileRightUpdate';

        //L'ajout d'un élément dans le menu vertical du détail d'une tâche                     
        Plugin::registerClass('PluginRtntestalexTask_Item', array('addtabon' => PluginRtntestalexTask_Item::getClasses()));
        Plugin::registerClass('PluginRtntestalexReport_Item', array('addtabon' => PluginRtntestalexReport_Item::getClasses()));
        Plugin::registerClass('PluginRtntestalexArchive_Item', array('addtabon' => PluginRtntestalexArchive_Item::getClasses()));

        //Ajout d'un élement dans la partie profile pour configuration supplémentaire des droits de modification ou d'ajout
        Plugin::registerClass('PluginRtntestalexProfile', array('addtabon' => 'Profile'));

        // Params : plugin name - string type - number - class - table - form page
        Plugin::registerClass('PluginRtntestalexTask', array('linkgroup_types' => true,
            'linkuser_types' => true
        ));

        Plugin::registerClass('PluginRtntestalexReport', array('linkgroup_types' => true,
            'linkuser_types' => true
        ));

        Plugin::registerClass('PluginRtntestalexArchive', array('linkgroup_types' => true,
            'linkuser_types' => true
        ));

        //if glpi is loaded
        if (Session::getLoginUserID()) {

            //Pour ajouter un onglet dans le menu Outils
            $PLUGIN_HOOKS['menu_toadd']['rtntestalex'] = array('tools' => 'PluginRtntestalexMenuTools');

            //Permet de rajouter les entêtes de navigation l'arrivée sur le plugin
            $PLUGIN_HOOKS['headings']['rtntestalex'] = 'plugin_get_headings_rtntestalex';

            //L'action executé lors du clic sur l'un des items du menu horizontal à l'arrivée sur le plugin
            $PLUGIN_HOOKS['headings_action']['rtntestalex'] = 'plugin_headings_actions_rtntestalex';
        }
    }
}

// Get the name and the version of the plugin - Needed
function plugin_version_rtntestalex() {
    global $LANG;

    $author = "<a href='http://www.routin.com'>ROUTIN</a>";
    $author .= ", Mimoun Al Bahraoui";
    $author .= ", Alexandre Sornay";
    return array('name' => __s('gen_plugin_name', 'rtntestalex'),
        'version' => PLUGIN_RTNTESTALEX_VERSION,
        'author' => $author,
        'license' => '',
        'homepage' => 'http://www.routin.com',
        'minGlpiVersion' => PLUGIN_RTNTESTALEX_GLPI_MIN_VERSION);
}

// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_rtntestalex_check_prerequisites() {
    if (version_compare(GLPI_VERSION, PLUGIN_RTNTESTALEX_GLPI_MIN_VERSION, 'lt')) {
        echo "This plugin requires GLPI >= " . PLUGIN_RTNTESTALEX_GLPI_MIN_VERSION;
        return false;
    }
    return true;
}

// Uninstall process for plugin : need to return true if succeeded : may display messages or add to message after redirect
function plugin_rtntestalex_check_config() {
    return true;
}

?>
