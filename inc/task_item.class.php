<?php

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

// Relation between Tasks and Items
class PluginRtntestalexTask_Item extends CommonDBRelation {

    // From CommonDBRelation
    static public $itemtype_1 = 'PluginRtntestalexTask';
    static public $items_id_1 = 'plugin_rtntestalex_tasks_id';
    static public $itemtype_2 = 'itemtype';
    static public $items_id_2 = 'items_id';
    // Items li�s � une t�che
    static protected $linkableClasses = array('Computer', 'Calendar', 'User');
    static protected $linkableComputer = array('Computer');
    static protected $linkableCalendar = array('Calendar');
    static protected $linkableUser = array('User');

    /**
     * Retourne le nom de la classe
     */
    static function getTypeName($nb = 0) {
        global $LANG;
        return __s('Direct Connections');
    }
    
    /**
     * Gestion des droits
     */
    function can($ID, $right, array &$input = NULL) {
        if ($ID < 0) {
            // Ajout
            if (!($item = new $input['itemtype'])) {
                return false;
            }

            if (!$item->getFromDB($input['items_id'])) {
                return false;
            }

            if ($item->getField('is_global') == 0 && self::countForItem($ID) > 0) {
                return false;
            }
        }
        return parent::can($ID, $right, $input);
    }

    /**
     * Retourne le nombre d'�l�ments li�s
     */
    static function countForItem($id) {
        return countElementsInTable(getTableForItemType(__CLASS__), "`plugin_rtntestalex_tasks_id`='$id'");
    }

    /**
     * Retourne le nombre d'�l�ments li�s (en fonction du type d'�l�ment
     */
    static function countForItemByItemtype(CommonDBTM $item) {
        $id = $item->getField('id');
        $itemtype = $item->getType();
        return countElementsInTable(getTableForItemType(__CLASS__), "`items_id`='$id' AND `itemtype`='$itemtype'");
    }

    /**
     * Fonction execut�e apr�s la suppression ou la purge d'une �l�ment l�
     */
    static function cleanForItem(CommonDBTM $item) {
        $temp = new self();
        $temp->deleteByCriteria(
                array('itemtype' => $item->getType(), 'items_id' => $item->getField('id')));
    }

    /**
     * Retourne les classes li�es aux t�ches
     */
    static function getClasses() {
        return self::$linkableClasses;
    }

    /**
     * D�claration d'un type d'�l�ment li� (Ajout un nouvel onglet � l'�l�ment li�)
     */
    static function registerItemtype($itemtype) {
        if (!in_array($itemtype, self::$linkableClasses)) {
            array_push(self::$linkableClasses, $itemtype);
            Plugin::registerClass('PluginRtntestalexTask_Item', array('addtabon' => $itemtype));
        }
    }

    /**
     * Fonction execut�e � l'installation du plugin (pour la gestion des t�ches)
     */
    static function install(Migration $migration) {
        global $DB;
        $table = getTableForItemType(__CLASS__);
        if (!TableExists($table)) {
            $query = "CREATE TABLE IF NOT EXISTS `$table` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `items_id` int(11) NOT NULL DEFAULT '0' COMMENT 'RELATION to various table, according to itemtype (id)',
                    `plugin_rtntestalex_tasks_id` int(11) NOT NULL DEFAULT '0',
                    `itemtype` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
                    PRIMARY KEY (`id`),
                    KEY `plugin_rtntestalex_tasks_id` (`plugin_rtntestalex_tasks_id`),
                    KEY `item` (`itemtype`,`items_id`)
                    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
            $DB->query($query) or die($DB->error());
        }
    }

    /**
     * Fonction execut�e � la mise � jour du plugin (pour la gestion des t�ches)
     */
    static function upgrade(Migration $migration) {
        global $DB;
    }

    /**
     * Fonction execut�e � la d�sinstallation du plugin (pour la gestion des t�ches)
     */
    static function uninstall() {
        global $DB;
        $table = getTableForItemType(__CLASS__);
        $DB->query("DROP TABLE IF EXISTS `$table`");
    }

    /**
     * Affichage des �l�ments li�s � la t�che
     */
    static function showForTask(PluginRtntestalexTask $task) {
        global $DB, $LANG;

        if (!$task->canView()) {
            return false;
        }
        // On filtre en fonction de l'onglet o� l'on se trouve
        switch ($_GET['_glpi_tab']) {
            // Computer
            case 'PluginRtntestalexTask_Item$1' :
                $restrict = "`plugin_rtntestalex_tasks_id` = '" . $task->getID() . "' AND `itemtype` = 'Computer'";
                break;
            // Calendar              
            case 'PluginRtntestalexTask_Item$2' :
                $restrict = "`plugin_rtntestalex_tasks_id` = '" . $task->getID() . "' AND `itemtype` = 'Calendar'";
                break;
            // User                
            case 'PluginRtntestalexTask_Item$3' :
                $restrict = "`plugin_rtntestalex_tasks_id` = '" . $task->getID() . "' AND `itemtype` = 'User'";
                break;
            // Onglet Tous                
            case '-1' :
                $restrict = "`plugin_rtntestalex_tasks_id` = '" . $task->getID();
                break;
            default :
                $restrict = "`plugin_rtntestalex_tasks_id` = '" . $task->getID();
                break;
        }
        $results = getAllDatasFromTable(getTableForItemType(__CLASS__), $restrict);

        echo "<div class='spaced'>";
        echo "<form id='items' name='items' method='post' action='" . Toolbox::getItemTypeFormURL(__CLASS__) . "'>";
        echo "<table class='tab_cadre_fixehov'>";
        switch ($_GET['_glpi_tab']) {
            // Computer
            case 'PluginRtntestalexTask_Item$1' :
                echo "<tr><th colspan='6'>Ordinateurs associes</th></tr>";       // LIBELLE A MODIFIER 
                break;
            // Calendar               
            case 'PluginRtntestalexTask_Item$2' :
                echo "<tr><th colspan='6'>Calendriers associes</th></tr>";       // LIBELLE A MODIFIER
                break;
            // User                
            case 'PluginRtntestalexTask_Item$3' :
                echo "<tr><th colspan='6'>Utilisateurs associes</th></tr>";       // LIBELLE A MODIFIER
                break;
            // Onglet Tous                
            case '-1' :
                echo "<tr><th colspan='6'>Aucun affichage pour l'onglet Tous</th></tr>";       // LIBELLE A MODIFIER
                break;
            default :
                echo "<tr><th colspan='6'>" . __("Associated item") . "</th></tr>";
        }

        if (!empty($results)) {
            echo "<tr><th></th>";
            echo "<th>" . __s("Type") . "</th>";
            echo "<th>" . __s("Entity") . "</th>";
            echo "<th>" . __s("Name") . "</th>";
            echo "</tr>";
            foreach ($results as $data) {
                $item = new $data['itemtype'];
                $item->getFromDB($data['items_id']);
                echo "<tr>";
                echo "<td>";
                if (PluginRtntestalexTask::canUpdate()) {
                    echo "<input type='checkbox' name='todelete[" . $data['id'] . "]'>";
                }
                echo "</td>";
                echo "<td>";
                echo call_user_func(array($data['itemtype'], 'getTypeName'));
                echo "</td>";
                echo "<td>";
                echo Dropdown::getDropdownName('glpi_entities', $item->fields['entities_id']);
                echo "</td>";
                echo "<td>";
                echo $item->getLink();
                echo "</td>";
            }
        }

        if (PluginRtntestalexTask::canUpdate()) {
            echo "<tr class='tab_bg_1'><td colspan='4' class='center'>";
            if (empty($results)) {
                echo "<input type='hidden' name='plugin_rtntestalex_tasks_id' value='" . $task->getID() . "'>";
                switch ($_GET['_glpi_tab']) {
                    // Onglet Tous                
                    case '-1' :
                        break;
                    default :
                        echo "<td>" . __s('Categories') . "</td>";
                }
                echo "<td>";
                // Param�tres communs pour le Dropdown
                $params['itemtype_name'] = 'itemtype';
                $params['items_id_name'] = 'items_id';
                $params['itemtypes'] = self::$linkableComputer;
                // At least, if not forced by user, 'checkright' == true
                if (!isset($options['checkright'])) {
                    $params['checkright'] = true;
                }
                switch ($_GET['_glpi_tab']) {
                    // Computer
                    case 'PluginRtntestalexTask_Item$1' :
                        $params['itemtypes'] = self::$linkableComputer;
                        Dropdown::showSelectItemFromItemtypes($params);
                        //Dropdown::showAllItems("items_id",0,0,$task->fields['entities_id'], self::$linkableComputer);
                        break;
                    // Calendar
                    case 'PluginRtntestalexTask_Item$2' :
                        $params['itemtypes'] = self::$linkableCalendar;
                        Dropdown::showSelectItemFromItemtypes($params);
                        //Dropdown::showAllItems("items_id",0,0,$task->fields['entities_id'], self::$linkableCalendar);
                        break;
                    // User
                    case 'PluginRtntestalexTask_Item$3' :
                        $params['itemtypes'] = self::$linkableUser;
                        Dropdown::showSelectItemFromItemtypes($params);
                        //Dropdown::showAllItems("items_id",0,0,$task->fields['entities_id'], self::$linkableUser);
                        break;
                    // Onglet Tous                
                    case '-1' :
                        break;
                    default :
                        $params['itemtypes'] = self::getClasses();
                        Dropdown::showSelectItemFromItemtypes($params);
                    //Dropdown::showAllItems("items_id",0,0,$task->fields['entities_id'], self::getClasses());
                }
                echo "</td>";
                switch ($_GET['_glpi_tab']) {
                    // Onglet Tous                
                    case '-1' :
                        break;
                    default :
                        echo "<tr><td colspan='2' class='center' class='tab_bg_2'>";
                        echo "<input type='submit' name='additem' value=\"" . _sx('button', 'Add') . "\" class='submit'>";
                        echo "</td></tr>";
                }
            }

            if (!empty($results)) {
                Html::openArrowMassives('items', true);
                Html::closeArrowMassives(array('delete_items' => _sx('button', 'Disconnect')));
                echo "<tr class='tab_bg_1'>";
                echo "<input type='hidden' name='plugin_rtntestalex_tasks_id' value='" . $task->getID() . "'>";
                echo "<td>" . __s('Categories') . "</td>";
                echo "<td>";
                // Param�tres pour le Dropdown
                $params['itemtype_name'] = 'itemtype';
                $params['items_id_name'] = 'items_id';
                $params['itemtypes'] = self::$linkableComputer;
                // At least, if not forced by user, 'checkright' == true
                if (!isset($options['checkright'])) {
                    $params['checkright'] = true;
                }
                switch ($_GET['_glpi_tab']) {
                    // Computer
                    case 'PluginRtntestalexTask_Item$1' :
                        $params['itemtypes'] = self::$linkableComputer;
                        Dropdown::showSelectItemFromItemtypes($params);
                        //Dropdown::showAllItems("items_id",0,0,$task->fields['entities_id'], self::$linkableComputer);
                        break;
                    // Calendar
                    case 'PluginRtntestalexTask_Item$2' :
                        $params['itemtypes'] = self::$linkableCalendar;
                        Dropdown::showSelectItemFromItemtypes($params);
                        //Dropdown::showAllItems("items_id",0,0,$task->fields['entities_id'], self::$linkableCalendar);
                        break;
                    // User
                    case 'PluginRtntestalexTask_Item$3' :
                        $params['itemtypes'] = self::$linkableUser;
                        Dropdown::showSelectItemFromItemtypes($params);
                        //Dropdown::showAllItems("items_id",0,0,$task->fields['entities_id'], self::$linkableUser);
                        break;
                    // Onglet Tous                
                    case '-1' :
                        break;
                    default :
                        $params['itemtypes'] = self::getClasses();
                        Dropdown::showSelectItemFromItemtypes($params);
                    //Dropdown::showAllItems("items_id",0,0,$task->fields['entities_id'], self::getClasses());
                }
                echo "</td></tr>";
                echo "<tr><td colspan='2' class='center' class='tab_bg_2'>";
                echo "<input type='submit' name='additem' value=\"" . _sx('button', 'Add') . "\" class='submit'>";
                echo "</td></tr>";
            }
        }
        echo "</table>";
        Html::closeForm();
        echo "</div>";
    }

    /**
     * Affichage des t�ches li�es � l'�l�ment
     */
    static function showForItem(CommonDBTM $item) {
        global $DB, $LANG;

        if (!$item->canView()) {
            return false;
        }
        $results = getAllDatasFromTable(getTableForItemType(__CLASS__), "`items_id` = '" . $item->getID() . "' AND `itemtype`='" . get_class($item) . "'");
        echo "<div class='spaced'>";
        echo "<form id='items' name='items' method='post' action='" . Toolbox::getItemTypeFormURL(__CLASS__) . "'>";
        echo "<table class='tab_cadre_fixehov'>";
        echo "<tr><th colspan='6'>Liste des taches rattachees</th></tr>";        // LIBELLE A MODIFIER
        if (!empty($results)) {
            echo "<tr><th></th>";
            echo "<th>" . __s('Entity') . "</th>";
            echo "<th>" . __s('Name') . "</th>";
            echo "</tr>";
            foreach ($results as $data) {
                $tmp = new PluginRtntestalexTask();
                $tmp->getFromDB($data['plugin_rtntestalex_tasks_id']);
                echo "<tr>";
                echo "<td>";
                if (PluginRtntestalexTask::canDelete()) {
                    echo "<input type='checkbox' name='todelete[" . $data['id'] . "]'>";
                }
                echo "</td>";
                echo "<td>";
                echo Dropdown::getDropdownName('glpi_entities', $tmp->fields['entities_id']);
                echo "</td>";
                echo "<td>";
                echo $tmp->getLink();
                echo "</td>";
                echo "</tr>";
            }
        }

        if (PluginRtntestalexTask::canUpdate()) {
            echo "<tr class='tab_bg_1'><td colspan='4' class='center'>";
            echo "<input type='hidden' name='items_id' value='" . $item->getID() . "'>";
            echo "<input type='hidden' name='itemtype' value='" . $item->getType() . "'>";
            $used = array();
            $query = "SELECT `id`
                FROM `glpi_plugin_rtntestalex_tasks`
                WHERE `is_template`='0'
                AND `id` IN (SELECT `plugin_rtntestalex_tasks_id`
                FROM `glpi_plugin_rtntestalex_tasks_items`)";
            foreach ($DB->request($query) as $use) {
                $used[] = $use['id'];
            }
            Dropdown::show('PluginRtntestalexTask', array('name' => "plugin_rtntestalex_tasks_id", 'entity' => $item->fields['entities_id'], 'used' => $used));
            echo "</td>";
            echo "<td colspan='2' class='center' class='tab_bg_2'>";
            echo "<input type='submit' name='additem' value=\"" . _sx('button', 'Save') . "\" class='submit'>";
            echo "</td></tr>";

            if (!empty($results)) {
                Html::openArrowMassives('items', true);
                Html::closeArrowMassives(array('delete_items' => _sx('button', 'Disconnect')));
            }
        }
        echo "</table>";
        Html::closeForm();
        echo "</div>";
    }

    /**
     * Gestion du nom des onglets pour une t�che
     * */
    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
        global $CFG_GLPI;

        if (PluginRtntestalexTask::canView()) {
            switch ($item->getType()) {
                // Onglet pour les t�ches
                case 'PluginRtntestalexTask' :
                    $ong = array();
                    if ($_SESSION['glpishow_count_on_tabs']) {
                        $ong[1] = self::createTabEntry("Ordinateurs", self::countForTaskComputer($item));       // LIBELLE A MODIFER
                        $ong[2] = self::createTabEntry("Planning", self::countForTaskCalendar($item));          // LIBELLE A MODIFER
                        $ong[3] = self::createTabEntry("Utilisateurs", self::countForTaskUser($item));          // LIBELLE A MODIFER
                    } else {
                        $ong[1] = "Ordinateurs";     // LIBELLE A MODIFER                              
                        $ong[2] = "Planning";        // LIBELLE A MODIFER  
                        $ong[3] = "Utilisateurs";    // LIBELLE A MODIFER 
                    }
                    return $ong;
                // Onglet pour les items li�s
                default :
                    if ($_SESSION['glpishow_count_on_tabs']) {
                        //return self::createTabEntry(__s('Task', 'rtntestalex'), self::countForItemByItemtype($item));
                        return self::createTabEntry("Taches ROUTIN", self::countForItemByItemtype($item));       // LIBELLE A MODIFER
                    }
                    //return __s('Task', 'rtntestalex');
                    return "Taches ROUTIN";       // LIBELLE A MODIFER
            }
        }
        return '';
    }

    /**
     * Compte le nombre de 'Computer' li� � la t�che
     * */
    static function countForTaskComputer(PluginRtntestalexTask $item) {
        $restrict = "`glpi_plugin_rtntestalex_tasks_items`.`plugin_rtntestalex_tasks_id` = '" . $item->getField('id') . "' AND `itemtype` = 'Computer'";
        return countElementsInTable(array('glpi_plugin_rtntestalex_tasks_items'), $restrict);
    }

    /**
     * Compte le nombre de 'Calendar' li� � la t�che
     * */
    static function countForTaskCalendar(PluginRtntestalexTask $item) {
        $restrict = "`glpi_plugin_rtntestalex_tasks_items`.`plugin_rtntestalex_tasks_id` = '" . $item->getField('id') . "' AND `itemtype` = 'Calendar'";
        return countElementsInTable(array('glpi_plugin_rtntestalex_tasks_items'), $restrict);
    }

    /**
     * Compte le nombre de 'User' li� � la t�che
     * */
    static function countForTaskUser(PluginRtntestalexTask $item) {
        $restrict = "`glpi_plugin_rtntestalex_tasks_items`.`plugin_rtntestalex_tasks_id` = '" . $item->getField('id') . "' AND `itemtype` = 'User'";
        return countElementsInTable(array('glpi_plugin_rtntestalex_tasks_items'), $restrict);
    }

    /**
     * Gestion de l'affichage en fonction de l'item li�
     * */
    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
        if (get_class($item) == 'Computer') {
            self::showForItem($item);
        } elseif (get_class($item) == 'Calendar') {
            self::showForItem($item);
        } elseif (get_class($item) == 'User') {
            self::showForItem($item);
        } elseif (get_class($item) == 'PluginRtntestalexTask') {
            self::showForTask($item);
        }
        return true;
    }

}

?>
