<?php

/**
 * 
 * Determine if the plugin should be installed or upgraded
 * 
 * Returns 0 if the plugin is not yet installed
 * Returns 1 if the plugin is already installed
 * 
 * @since 1.3
 * 
 * @return number
 */
function plugin_rtntestalex_currentVersion() {

    // Saves the current version to not re-detect it on multiple calls
    static $currentVersion = null;

    if ($currentVersion === null) {
        // result not cached
        if (
                !TableExists('glpi_plugin_rtntestalex_tasks_items') &&
                !TableExists('glpi_plugin_rtntestalex_configs')
        ) {
            // the plugin seems not installed
            $currentVersion = 0;
        } else {
            if (TableExists('glpi_plugin_rtntestalex_configs')) {
                // The plugin is at least 1.3
                // Get the current version in the plugin's configuration
                $PluginRtntestalexConfig = new PluginRtntestalexConfig();
                $currentVersion = $PluginRtntestalexConfig->getValue('Version');
            }
            if ($currentVersion == '') {
                // The plugin is older than 1.3
                $currentVersion = '1.0';
            }
        }
    }
    return $currentVersion;
}

//Va executer toute les installations de tables dans la bdd
function plugin_rtntestalex_install() {
    include_once (GLPI_ROOT . "/plugins/rtntestalex/inc/profile.class.php");
    include_once (GLPI_ROOT . "/plugins/rtntestalex/inc/config.class.php");

    include_once (GLPI_ROOT . "/plugins/rtntestalex/inc/planning.class.php");
    
    include_once (GLPI_ROOT . "/plugins/rtntestalex/inc/tasktype.class.php");
    include_once (GLPI_ROOT . "/plugins/rtntestalex/inc/archivetype.class.php");
    include_once (GLPI_ROOT . "/plugins/rtntestalex/inc/statetype.class.php");
    include_once (GLPI_ROOT . "/plugins/rtntestalex/inc/veriftype.class.php");

    include_once (GLPI_ROOT . "/plugins/rtntestalex/inc/cartridge.class.php");
    include_once (GLPI_ROOT . "/plugins/rtntestalex/inc/task.class.php");
    include_once (GLPI_ROOT . "/plugins/rtntestalex/inc/task_item.class.php");
    include_once (GLPI_ROOT . "/plugins/rtntestalex/inc/report.class.php");
    include_once (GLPI_ROOT . "/plugins/rtntestalex/inc/report_item.class.php");
    include_once (GLPI_ROOT . "/plugins/rtntestalex/inc/archive.class.php");
    include_once (GLPI_ROOT . "/plugins/rtntestalex/inc/archive_item.class.php");

    $migration = new Migration(PLUGIN_RTNTESTALEX_VERSION);

    if (plugin_rtntestalex_currentVersion() == 0) {
        // Installation of the plugin
        PluginRtntestalexConfig::install($migration);
        PluginRtntestalexProfile::install($migration);

        PluginRtntestalexPlanning::install($migration);
        
        PluginRtntestalexTaskType::install($migration);
        PluginRtntestalexArchiveType::install($migration);
        PluginRtntestalexStateType::install($migration);
        PluginRtntestalexVerifType::install($migration);

        PluginRtntestalexCartridge::install($migration);
        PluginRtntestalexTask::install($migration);
        PluginRtntestalexTask_Item::install($migration);
        PluginRtntestalexReport::install($migration);
        PluginRtntestalexReport_Item::install($migration);
        PluginRtntestalexArchive::install($migration);
        PluginRtntestalexArchive_Item::install($migration);
    } else {
        PluginRtntestalexConfig::upgrade($migration);
        PluginRtntestalexProfile::upgrade($migration);

        PluginRtntestalexPlanning::upgrade($migration);
        
        PluginRtntestalexTaskType::upgrade($migration);
        PluginRtntestalexArchiveType::upgrade($migration);
        PluginRtntestalexStateType::upgrade($migration);
        PluginRtntestalexVerifType::upgrade($migration);
        
        PluginRtntestalexCartridge::upgrade($migration);
        PluginRtntestalexTask::upgrade($migration);
        PluginRtntestalexTask_Item::upgrade($migration);
        PluginRtntestalexReport::upgrade($migration);
        PluginRtntestalexReport_Item::upgrade($migration);
        PluginRtntestalexArchive::upgrade($migration);
        PluginRtntestalexArchive_Item::upgrade($migration);
    }
    return true;
}

//Va executer la desinstallation de toutes les tables 
function plugin_rtntestalex_uninstall() {
    include_once (GLPI_ROOT . "/plugins/rtntestalex/inc/profile.class.php");
    include_once (GLPI_ROOT . "/plugins/rtntestalex/inc/config.class.php");

    include_once (GLPI_ROOT . "/plugins/rtntestalex/inc/planning.class.php");
    
    include_once (GLPI_ROOT . "/plugins/rtntestalex/inc/tasktype.class.php");
    include_once (GLPI_ROOT . "/plugins/rtntestalex/inc/archivetype.class.php");
    include_once (GLPI_ROOT . "/plugins/rtntestalex/inc/statetype.class.php");
    include_once (GLPI_ROOT . "/plugins/rtntestalex/inc/veriftype.class.php");

    include_once (GLPI_ROOT . "/plugins/rtntestalex/inc/cartridge.class.php");
    include_once (GLPI_ROOT . "/plugins/rtntestalex/inc/task.class.php");
    include_once (GLPI_ROOT . "/plugins/rtntestalex/inc/task_item.class.php");
    include_once (GLPI_ROOT . "/plugins/rtntestalex/inc/report.class.php");
    include_once (GLPI_ROOT . "/plugins/rtntestalex/inc/report_item.class.php");
    include_once (GLPI_ROOT . "/plugins/rtntestalex/inc/archive.class.php");
    include_once (GLPI_ROOT . "/plugins/rtntestalex/inc/archive_item.class.php");

    PluginRtntestalexProfile::uninstall();
    PluginRtntestalexConfig::uninstall();
    
    PluginRtntestalexPlanning::uninstall();
    
    PluginRtntestalexTaskType::uninstall();
    PluginRtntestalexArchiveType::uninstall();
    PluginRtntestalexStateType::uninstall();
    PluginRtntestalexVerifType::uninstall();
    
    PluginRtntestalexCartridge::uninstall();
    PluginRtntestalexTask::uninstall();
    PluginRtntestalexTask_Item::uninstall();
    PluginRtntestalexReport::uninstall();
    PluginRtntestalexReport_Item::uninstall();
    PluginRtntestalexArchive::uninstall();
    PluginRtntestalexArchive_Item::uninstall();
    return true;
}

// Define dropdown relations
function plugin_rtntestalex_getDatabaseRelations() {

    $plugin = new Plugin();


    if ($plugin->isActivated("rtntestalex")) {          
         return array(
          "glpi_plugin_rtntestalex_archives"
          => array("glpi_plugin_rtntestalex_archives"=>"plugin_rtntestalex_archives_id"),
          "glpi_plugin_rtntestalex_archivetypes"
          => array("glpi_plugin_rtntestalex_archivetypes"=>"plugin_rtntestalex_archivetypes_id"),
          "glpi_plugin_rtntestalex_cartridges"
          => array("glpi_plugin_rtntestalex_cartridges"=>"plugin_rtntestalex_cartridges_id"),
          "glpi_plugin_rtntestalex_reports"
          => array("glpi_plugin_rtntestalex_reports"=>"plugin_rtntestalex_reports_id"),
          "glpi_users" => array("glpi_plugin_rtntestalex_reports"=>"users_id_tech"),
          "glpi_users" => array("glpi_plugin_rtntestalex_archives"=>"users_id_tech"),
          "glpi_profiles" => array ("glpi_plugin_rtntestalex_reports" => "profiles_id"),
          "glpi_profiles" => array ("glpi_plugin_rtntestalex_archives" => "profiles_id")); 
    } else {
        return array();
    }
}

// Va permettre de gérer les listes déroulantes dans la partie intitulés 
function plugin_rtntestalex_getDropdown() {
    global $LANG;

    $plugin = new Plugin();
    if ($plugin->isActivated("rtntestalex")) {
        return array('PluginRtntestalexStateType' => __('List of states', 'rtntestalex'),   //LIBELLE A MODIFIER
            'PluginRtntestalexTaskType' => __('Type of tasks', 'rtntestalex'),              //LIBELLE A MODIFIER
            'PluginRtntestalexArchiveType' => __('Type of archives', 'rtntestalex'),        //LIBELLE A MODIFIER
            'PluginRtntestalexVerifType' => __('Type of verification', 'rtntestalex'),      //LIBELLE A MODIFIER
            'Calendar' => 'Calendriers (standard GLPI)');                                   //LIBELLE A MODIFIER
    } else {
        return array();
    }
}

//force groupby for multible links to items
function plugin_rtntestalex_forceGroupBy($type) {

    return true;
    switch ($type) {
        case 'PluginRtntestalexTask':
            return true;
        case 'PluginRtntestalexReport':
            return true;
        case 'PluginRtntestalexArchive':
            return true;
        case 'PluginRtntestalexCartridge':
            return true;
            break;
    }
    return false;
}

//Permet de gérer la recherche 
//La recherche sera faite sur les tables présentes ci-dessous
function plugin_rtntestalex_getAddSearchOptions($itemtype) {
    global $LANG;

    $sopt = array();

    $reservedTypeIndex = PluginRtntestalexConfig::RESERVED_TYPE_RANGE_MIN;

    //if (in_array($itemtype,PluginRtntestalexSimcard_Item::getClasses())) {
    //if (PluginRtntestalexSimcard::canView()) {
    /* $sopt[$reservedTypeIndex]['table']         = 'glpi_plugin_rtntestalex_simcards';
      $sopt[$reservedTypeIndex]['field']         = 'name';
      $sopt[$reservedTypeIndex]['name']          = _sn('SIM card', 'SIM cards', 2, 'rtntestalex')." - ".__s('Name');
      $sopt[$reservedTypeIndex]['forcegroupby']  = true;
      $sopt[$reservedTypeIndex]['massiveaction'] = false;
      $sopt[$reservedTypeIndex]['datatype']      = 'itemlink';
      $sopt[$reservedTypeIndex]['itemlink_type'] = 'PluginRtntestalexSimcard';
      $sopt[$reservedTypeIndex]['joinparams']    = array('beforejoin'
      => array('table'      => 'glpi_plugin_rtntestalex_simcards_items',
      'joinparams' => array('jointype' => 'itemtype_item'))); */
    /* $reservedTypeIndex++;
      $sopt[$reservedTypeIndex]['table']         = 'glpi_plugin_rtntestalex_simcards';
      $sopt[$reservedTypeIndex]['field']         = 'phonenumber';
      $sopt[$reservedTypeIndex]['name']          = _sn('SIM card', 'SIM cards', 2, 'rtntestalex')." - ".__s('Phone number', 'rtntestalex');
      $sopt[$reservedTypeIndex]['massiveaction'] = false;
      $sopt[$reservedTypeIndex]['forcegroupby']  = true;
      $sopt[$reservedTypeIndex]['joinparams']    = array('beforejoin'
      => array('table'      => 'glpi_plugin_rtntestalex_simcards_items',
      'joinparams' => array('jointype' => 'itemtype_item'))); */
    /* $reservedTypeIndex++;
      $sopt[$reservedTypeIndex]['table']         = 'glpi_plugin_rtntestalex_simcards';
      $sopt[$reservedTypeIndex]['field']         = 'serial';
      $sopt[$reservedTypeIndex]['name']          = _sn('SIM card', 'SIM cards', 2, 'rtntestalex')." - ".__s('IMSI', 'rtntestalex');
      $sopt[$reservedTypeIndex]['massiveaction'] = false;
      $sopt[$reservedTypeIndex]['forcegroupby']  = true;
      $sopt[$reservedTypeIndex]['joinparams']    = array('beforejoin'
      => array('table'      => 'glpi_plugin_rtntestalex_simcards_items',
      'joinparams' => array('jointype' => 'itemtype_item'))); */
    //}
    //}
    //return $sopt;
}

// Va permettre de supprimer les éléments selon deux paramètres (itemtype et l'id)
function plugin_item_purge_rtntestalexTask($item) {

    $temp = new PluginRtntestalexTask_Item();
    $temp->deleteByCriteria(array('itemtype' => get_class($item),
        'items_id' => $item->getField('id')));
    return true;
}

// Va permettre de supprimer les éléments selon deux paramètres (itemtype et l'id)
function plugin_item_purge_rtntestalexReport($item) {

    $temp = new PluginRtntestalexReport_Item();
    $temp->deleteByCriteria(array('itemtype' => get_class($item),
        'items_id' => $item->getField('id')));
    return true;
}

// Va permettre de supprimer les éléments selon deux paramètres (itemtype et l'id)
function plugin_item_purge_rtntestalexArchive($item) {

    $temp = new PluginRtntestalexArchive_Item();
    $temp->deleteByCriteria(array('itemtype' => get_class($item),
        'items_id' => $item->getField('id')));
    return true;
}

//??? ALEX
function plugin_datainjection_populate_rtntestalex() {
    global $INJECTABLE_TYPES;
    $INJECTABLE_TYPES['PluginRtntestalexSimcardInjection'] = 'rtntestalex';
}

/**
 * 
 * Determine if the plugin should be installed or upgraded
 * 
 * Returns 0 if the plugin is not yet installed
 * Returns 1 if the plugin is already installed
 * 
 * @since 1.3
 */
//??? ALEX
function plugin_rtntestalex_postinit() {
    global $UNINSTALL_TYPES, $ORDER_TYPES, $ALL_CUSTOMFIELDS_TYPES, $DB;
    $plugin = new Plugin();

    if ($plugin->isInstalled('customfields') && $plugin->isActivated('customfields')) {
        PluginCustomfieldsItemtype::registerItemtype('PluginRtntestalexTask');
        PluginCustomfieldsItemtype::registerItemtype('PluginRtntestalexReport');
        PluginCustomfieldsItemtype::registerItemtype('PluginRtntestalexArchive');
    }
}

/**
 * Update helpdesk_item_type in a profile if a ProfileRight changes or is created
 * 
 * Add or remove simcard item type to match the status of "associable to tickets" in simcard's right
 * 
 * @since 1.4.1
 */
function plugin_rtntestalex_profileRightUpdate($item) {
//Test si l'id du profil sur lequel l'utilisateur est connecté correspond bien au profil accepté sur la partie du plugin où sera l'utilisateur
    if ($_SESSION['glpiactiveprofile']['id'] == $item->fields['profiles_id']) {
        if ($item->fields['name'] == PluginRtntestalexProfile::RIGHT_RTNTESTALEX_TASK) {
            $profile = new Profile();
            $profile->getFromDB($item->fields['profiles_id']);
            $helpdeskItemTypes = json_decode($profile->fields['helpdesk_item_type'], true);
            if (!is_array($helpdeskItemTypes)) {
                $helpdeskItemTypes = array();
            }
            $index = array_search('PluginRtntestalexSimcard', $helpdeskItemTypes);
            if ($item->fields['rights'] & PluginRtntestalexProfile::SIMCARD_ASSOCIATE_TICKET) {
                if ($index === false) {
                    $helpdeskItemTypes[] = 'PluginRtntestalexSimcard';
                    if ($_SESSION['glpiactiveprofile']['id'] == $profile->fields['id']) {
                        $_SESSION['glpiactiveprofile']['helpdesk_item_type'][] = 'PluginRtntestalexSimcard';
                    }
                }
            } else {
                if ($index !== false) {
                    unset($helpdeskItemTypes[$index]);
                    if ($_SESSION['glpiactiveprofile']['id'] == $profile->fields['id']) {
                        // Just in case this is not the same index in the session vars
                        $index = array_search('PluginRtntestalexSimcard', $_SESSION['glpiactiveprofile']['helpdesk_item_type']);
                        if ($index !== false) {
                            unset($_SESSION['glpiactiveprofile']['helpdesk_item_type'][$index]);
                        }
                    }
                }
            }
            $tmp = array(
                'id' => $profile->fields['id'],
                'helpdesk_item_type' => json_encode($helpdeskItemTypes)
            );
            $profile->update($tmp, false);
        }
    }
}

?>
