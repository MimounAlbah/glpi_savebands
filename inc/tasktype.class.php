<?php

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

/// Class TaskType
class PluginRtntestalexTaskType extends CommonDropdown {

    static $rightname = PluginRtntestalexProfile::RIGHT_RTNTESTALEX_TASKTYPE;

    /**
     * Definition des permissions de visualisation
     *
     */
    static function canView() {
        return Session::haveRight(self::$rightname, READ);
    }

    /**
     * Definition des permissions de création
     *
     */
    static function canCreate() {
        return Session::haveRight(self::$rightname, CREATE);
    }

    /**
     * Definition des permissions de mise à jour
     *
     */
    static function canUpdate() {
        return Session::haveRight(self::$rightname, UPDATE);
    }

    /**
     * Definition des permissions de suppression
     *
     */
    static function canDelete() {
        return Session::haveRight(self::$rightname, DELETE);
    }

    /**
     * Permet de récupérer le nom de la classe
     *
     */
    static function getTypeName($nb = 0) {
        global $LANG;
        return __s('gen_type_tasks', 'rtntestalex');
    }

    static function install(Migration $migration) {
        global $DB;

        $table = getTableForItemType(__CLASS__);
        if (!TableExists($table)) {
            $query = "CREATE TABLE IF NOT EXISTS `$table` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `entities_id` int(11) NOT NULL DEFAULT '0',
                `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                `comment` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                `is_global` tinyint(1) NOT NULL DEFAULT '0',                
                PRIMARY KEY (`id`),
                KEY `name` (`name`),
                KEY `entities_id` (`entities_id`),
                KEY `is_global` (`is_global`)            
         ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
            $DB->query($query) or die("Error adding table $table");

            $query = "INSERT INTO `$table`(`name`) VALUES ('Sauvegarde Veeam')";
            $DB->query($query) or die("Error adding in table $table (1)");
            $query = "INSERT INTO `$table`(`name`) VALUES ('Sauvegarde Backup Exec')";
            $DB->query($query) or die("Error adding in table $table (2)");
            $query = "INSERT INTO `$table`(`name`) VALUES ('Replication Veeam')";
            $DB->query($query) or die("Error adding in table $table (3)");
        }
    }

    /**
     * 
     *
     * @since 1.3
     * */
    static function upgrade(Migration $migration) {
        global $DB;

        switch (plugin_rtntestalex_currentVersion()) {
            case '1.0':
                self::install($migration);
                break;
        }
    }

    static function uninstall() {
        global $DB;

        foreach (array('DisplayPreference', 'Bookmark') as $itemtype) {
            $item = new $itemtype();
            $item->deleteByCriteria(array('itemtype' => __CLASS__));
        }

        // Remove dropdowns localization
        $dropdownTranslation = new DropdownTranslation();
        $dropdownTranslation->deleteByCriteria(array("itemtype = 'PluginRtntestalexTaskType'"), 1);

        $table = getTableForItemType(__CLASS__);
        $DB->query("DROP TABLE IF EXISTS `$table`");
    }

    function getSearchOptions() {
        global $CFG_GLPI, $LANG;

        $tab = array();
        $tab['common'] = __s('Type of tasks', 'rtntestalex');

        $tab[1]['table'] = $this->getTable();
        $tab[1]['field'] = 'name';
        $tab[1]['name'] = __('Name');
        $tab[1]['datatype'] = 'itemlink';
        $tab[1]['itemlink_type'] = $this->getType();
        $tab[1]['massiveaction'] = false; // implicit key==1
        $tab[1]['injectable'] = true;
        $tab[1]['checktype'] = 'text';
        $tab[1]['displaytype'] = 'text';

        $tab[2]['table'] = $this->getTable();
        $tab[2]['field'] = 'id';
        $tab[2]['name'] = __('ID');
        $tab[2]['massiveaction'] = false; // implicit field is id
        $tab[2]['injectable'] = false;

        $tab[3]['table'] = $this->getTable();
        $tab[3]['field'] = 'comment';
        $tab[3]['name'] = __('Comments');
        $tab[3]['massiveaction'] = false; // implicit field is id
        $tab[3]['injectable'] = false;
        return $tab;
    }

}

?>
