<?php

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

/// Class StateType
class PluginRtntestalexStateType extends CommonDropdown {

    static $rightname = PluginRtntestalexProfile::RIGHT_RTNTESTALEX_STATETYPE;

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
        return __s('gen_list_states', 'rtntestalex');
    }

    static function install(Migration $migration) {
        global $DB;

        //Récupération du nom de la class pour création dans la base
        $table = getTableForItemType(__CLASS__);
        if (!TableExists($table)) {
            $query = "CREATE TABLE IF NOT EXISTS `$table` (
           `id` int(11) NOT NULL AUTO_INCREMENT,           
           `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
           `comment` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
           PRIMARY KEY (`id`),
           KEY `name` (`name`)
         ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
            $DB->query($query) or die("Error adding table $table");

            $query = "INSERT INTO `$table`(`name`) VALUES ('OK')";
            $DB->query($query) or die("Error adding in table $table (1)");
            $query = "INSERT INTO `$table`(`name`) VALUES ('Warning')";
            $DB->query($query) or die("Error adding in table $table (2)");
            $query = "INSERT INTO `$table`(`name`) VALUES ('Echec')";
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
        $dropdownTranslation->deleteByCriteria(array("itemtype = 'PluginRtntestalexState'"), 1);

        $table = getTableForItemType(__CLASS__);
        $DB->query("DROP TABLE IF EXISTS `$table`");
    }

    /**
     * Permet de définir sur quels champs sera faite la recherche
     * @param rien
     * @return un tableau des options de recherche
     * Le numéro permet de définir son emplacement dans la liste et dans le tableau
     */
    function getSearchOptions() {
        global $CFG_GLPI, $LANG;

        $tab = array();
        $tab['common'] = __s('List of states', 'rtntestalex');

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
