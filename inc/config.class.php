<?php

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

/**
 * Classe de configuration 
 * @since 1.3
 *
 */
class PluginRtntestalexConfig extends CommonDBTM {

    // Reserved range   : [10126, 10135]
    const RESERVED_TYPE_RANGE_MIN = 10126;
    const RESERVED_TYPE_RANGE_MAX = 10135;

    static $config = array();

    /**
     * Permet l'installation dans la base de la configuration du plugin
     *
     * 
     * */
    static function install(Migration $migration) {
        global $DB;

        $table = getTableForItemType(__CLASS__);
        $query = "CREATE TABLE `" . $table . "` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                `value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `unicity` (`type`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1";
        $DB->query($query) or die($DB->error());
        $query = "INSERT INTO `" . $table . "` 
                (`type`,`value`)
               VALUES ('Version', '" . PLUGIN_RTNTESTALEX_VERSION . "')";
        $DB->query($query) or die($DB->error());
    }

    /**
     * Permet la mise à jour du plugin
     *
     * 
     * */
    static function upgrade(Migration $migration) {
        global $DB;
        //On test le numéro de version actuelle du plugin 
        switch (plugin_rtntestalex_currentVersion()) {
            case '1.2':
                self::install($migration);
                break;

            default:
                //Récupération du nom
                $table = getTableForItemType(__CLASS__);
                $query = "UPDATE `" . $table . "`
                      SET `value`= '" . PLUGIN_RTNTESTALEX_VERSION . "'
                      WHERE `type`='Version'";
                $DB->query($query) or die($DB->error());
        }
    }

    /**
     * Permet la desinstallation du plugin
     *
     * 
     * */
    static function uninstall() {
        global $DB;

        $displayPreference = new DisplayPreference();
        $displayPreference->deleteByCriteria(array("`num` >= " . self::RESERVED_TYPE_RANGE_MIN . " AND `num` <= " . self::RESERVED_TYPE_RANGE_MAX));
        //Récuoération du nom
        $table = getTableForItemType(__CLASS__);
        //Suppression de la table
        $query = "DROP TABLE IF EXISTS `" . $table . "`";

        $DB->query($query) or die($DB->error());
    }

    /**
     * 
     *
     * 
     * */
    static function loadCache() {
        global $DB;

        $table = getTableForItemType(__CLASS__);
        self::$config = array();
        $query = "SELECT * FROM `" . $table . "`";
        $result = $DB->query($query);
        while ($data = $DB->fetch_array($result)) {
            self::$config[$data['type']] = $data['value'];
        }
    }

    /**
     * Ajoute la configuration d'une valeur, si elle n'est pas présente
     *
     * @param $name field name
     * @param $value field value
     *
     * @return integer the new id of the added item (or FALSE if fail)
     * */
    function addValue($name, $value) {
        $existing_value = $this->getValue($name);
        if (!is_null($existing_value)) {
            return false;
        } else {
            return $this->add(array('type' => $name,
                        'value' => $value));
        }
    }

    /**
     * Get configuration value
     *
     * @param $name field name
     *
     * @return field value for an existing field, FALSE otherwise
     * */
    function getValue($name) {
        if (isset(self::$config[$name])) {
            return self::$config[$name];
        }

        $config = current($this->find("`type`='" . $name . "'"));
        if (isset($config['value'])) {
            return $config['value'];
        }
        return NULL;
    }

    /**
     * Update configuration value
     *
     * @param $name field name
     * @param $value field value
     *
     * @return boolean : TRUE on success
     * */
    function updateValue($name, $value) {
        $config = current($this->find("`type`='" . $name . "'"));
        if (isset($config['id'])) {
            return $this->update(array('id' => $config['id'], 'value' => $value));
        } else {
            return $this->add(array('type' => $name, 'value' => $value));
        }
    }

}
