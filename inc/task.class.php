<?php

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

//      Class PluginRtntestalexTask
class PluginRtntestalexTask extends CommonDBTM {

    // From CommonDBTM
    public $dohistory = true;
    static $rightname = PluginRtntestalexProfile::RIGHT_RTNTESTALEX_TASK;
    protected $usenotepad = true;

    /**
     * Retourne le nom de la classe
     */
    static function getTypeName($nb = 0) {
        global $LANG;
        return _n('task_task', 'task_tasks', $nb, 'rtntestalex');
    }

    /**
     * Gestion des droits (pour la gestion des t�ches)
     */
    function getRights($interface = 'central') {
        $rights = parent::getRights();
        return $rights;
    }

    /**
    * Actions permettant de savoir si il n'y a pas une tâche qui porte déjà le même nom 
    */
    static function getSameNameTask($input) {
        global $DB, $CFG_GLPI;

        //Requete SQL permettant de chercher une tache portant le nom ecrit dans le formulaire
        $query = "SELECT *
              FROM `glpi_plugin_rtntestalex_tasks`";

        //On transforme le champ entré dans le formulaire pour le tester si il n'existe pas déjà
        //Les transformations qui vont avoir lieu sont la supression des caractères spéciaux et la non diféreciation des majuscules et minuscules
        //Mais cette transformation n'impact pas sont entré dans la base 
        $champform = $input;
        $champform = preg_replace('#Ç#', 'C', $champform);
        $champform = preg_replace('#ç#', 'c', $champform);
        $champform = preg_replace('#è|é|ê|ë#', 'e', $champform);
        $champform = preg_replace('#È|É|Ê|Ë#', 'E', $champform);
        $champform = preg_replace('#à|á|â|ã|ä|å#', 'a', $champform);
        $champform = preg_replace('#@|À|Á|Â|Ã|Ä|Å#', 'A', $champform);
        $champform = preg_replace('#ì|í|î|ï#', 'i', $champform);
        $champform = preg_replace('#Ì|Í|Î|Ï#', 'I', $champform);
        $champform = preg_replace('#ð|ò|ó|ô|õ|ö#', 'o', $champform);
        $champform = preg_replace('#Ò|Ó|Ô|Õ|Ö#', 'O', $champform);
        $champform = preg_replace('#ù|ú|û|ü#', 'u', $champform);
        $champform = preg_replace('#Ù|Ú|Û|Ü#', 'U', $champform);
        $champform = preg_replace('#ý|ÿ#', 'y', $champform);
        $champform = preg_replace('#Ý#', 'Y', $champform);

        $result = $DB->query($query);
        if ($DB->numrows($result)) {
            while($data = $DB->fetch_array($result)){
                //On vérifie que le nom de la tache rentré ne soit pas le même que celui en base
                //On ne diférencie pas un nom mis en majuscule et un minuscule car le test se fait en transformant des deux cotes en majuscule
                //On transforme le champ recupére en base pour le tester si il n'existe pas déjà
                //Les transformations qui vont avoir lieu sont la supression des caractères spéciaux et la non diféreciation des majuscules et minuscules
                //Mais cette transformation n'impact pas sont entré dans la base 
                $bdd = $data['name'];
                $bdd = preg_replace('#Ç#', 'C', $bdd);
                $bdd = preg_replace('#ç#', 'c', $bdd);
                $bdd = preg_replace('#è|é|ê|ë#', 'e', $bdd);
                $bdd = preg_replace('#È|É|Ê|Ë#', 'E', $bdd);
                $bdd = preg_replace('#à|á|â|ã|ä|å#', 'a', $bdd);
                $bdd = preg_replace('#@|À|Á|Â|Ã|Ä|Å#', 'A', $bdd);
                $bdd = preg_replace('#ì|í|î|ï#', 'i', $bdd);
                $bdd = preg_replace('#Ì|Í|Î|Ï#', 'I', $bdd);
                $bdd = preg_replace('#ð|ò|ó|ô|õ|ö#', 'o', $bdd);
                $bdd = preg_replace('#Ò|Ó|Ô|Õ|Ö#', 'O', $bdd);
                $bdd = preg_replace('#ù|ú|û|ü#', 'u', $bdd);
                $bdd = preg_replace('#Ù|Ú|Û|Ü#', 'U', $bdd);
                $bdd = preg_replace('#ý|ÿ#', 'y', $bdd);
                $bdd = preg_replace('#Ý#', 'Y', $bdd);                  
                if(strtoupper($bdd) == strtoupper($champform)){
                    $same = true;
                    break;
                }else{
                    $same = false;
                }
            }
        }else{
            $same = false;
        }

        return $same;
    }

    /**
     * Gestion de l'affichage des differents onglets verticaux
     */
    function defineTabs($options = array()) {
        global $LANG;
        $ong = array();
        $this->addDefaultFormTab($ong);
        if ($this->fields['id'] > 0) {
            if (!isset($options['withtemplate']) || empty($options['withtemplate'])) {
                $this->addStandardTab('PluginRtntestalexTask_Item', $ong, $options);    // Items li�s aux t�ches
                $this->addStandardTab('Notepad', $ong, $options);
                $this->addStandardTab('Log', $ong, $options);
                $this->addStandardTab('Event', $ong, $options);
            } else {
                $this->addStandardTab('Log', $ong, $options);
                $this->addStandardTab('Event', $ong, $options);
            }
        } else {
            $ong[1] = __s('Main');
        }
        return $ong;
    }

    /**
     * Affichage du formulaire li� � une t�che
     */
    function showForm($ID, $options = array()) {
        global $CFG_GLPI, $DB, $LANG;

        if (!$this->canView())
            return false;

        // Gestion des valeurs par d�faut
        $default_values = array('name' => '',
            'plugin_rtntestalex_tasktypes_id' => 0,
            'cartridges' => 0,
            'description' => '',
            'comment' => '');

        // Get default values from posted values on reload form
        // On get because of tabs
        // we use REQUEST because method differ with layout (lefttab : GET, vsplit: POST)
        if (isset($_REQUEST)) {
            $values = Html::cleanPostForTextArea($_REQUEST);
        }

        // On restaure les donn�es sauvegard�s par la session precedente
        $saved = $this->restoreInput();
        foreach ($default_values as $name => $value) {
            if (!isset($values[$name])) {
                if (isset($saved[$name])) {
                    $values[$name] = $saved[$name];
                } elseif ((isset($this->fields[$name])) && (!empty($ID))) {
                    $values[$name] = $this->fields[$name];
                } else {
                    $values[$name] = $value;
                }
            }
        }

        $this->showFormHeader($options);


        echo "<tr class='tab_bg_1'>";
        // Champ Nom
        echo "<th>" . __s('Name') . "<span class='red'>*</span></th>";
        echo "<td>";
        $TMPfields = $this->fields['name'];
        if (isset($values['name'])) {
            $TMPfields = $values['name'];
        }
        echo "<input type='text' size='30' maxlength=250 name='name' value=\"" . Html::cleanInputText($TMPfields) . "\">";
        echo "</td>";

        // Champ Type de t�ches
        echo "<th>" . __s('Type of tasks', 'rtntestalex') . "<span class='red'>*</span></th>";
        echo "<td>";
        $TMPfields = $this->fields['plugin_rtntestalex_tasktypes_id'];
        if (isset($values['plugin_rtntestalex_tasktypes_id'])) {
            $TMPfields = $values['plugin_rtntestalex_tasktypes_id'];
        }
        Dropdown::show('PluginRtntestalexTaskType', array('value' => $TMPfields));
        echo "</td></tr>\n";

        echo "<tr class='tab_bg_1'>";
        // Champ Bandes
        echo "<th>" . __s('Cartridges', 'rtntestalex') . "</th><td>";
        $TMPfields = $this->fields['cartridges'];
        if (empty($ID)) {     // Ajout
            $TMPfields = $values['cartridges'];
        }
        Dropdown::showYesNo('cartridges', $TMPfields);
        echo "</td></tr>\n";

        echo "<tr class='tab_bg_1'>";
        // Champ Description      
        echo "<th width='20%'>" . __s('Description') . "</th>";
        echo "<td>";
        $TMPfields = $this->fields['description'];
        if (isset($values['description'])) {
            $TMPfields = $values['description'];
        }
        echo "<textarea cols='30' rows='10' name='description' >" . $TMPfields . "</textarea></td>";
        echo "</td>";
        // Champ Commentaire
        echo "<th width='20%'>" . __s('Comments') . "</th>";
        echo "<td>";
        $TMPfields = $this->fields['comment'];
        if (isset($values['comment'])) {
            $TMPfields = $values['comment'];
        }
        echo "<textarea cols='30' rows='10' name='comment' >" . $TMPfields . "</textarea>";
        echo "</td></tr>\n\n";

        $this->showFormButtons($options);

        return true;
    }

    /**
     * V�rification de la saisie des champs obligatoires
     */
    function checkMandatoryFields($input, $options) {
        $msg = array();
        $checkKo = false;

        $mandatory_fields = array('name' => "Nom",
            'plugin_rtntestalex_tasktypes_id' => "Type de taches");

        foreach ($input as $key => $value) {
            if (isset($mandatory_fields[$key])) {
                if ((isset($value) && empty($value)) || !isset($value)) {
                    $msg[$key] = $mandatory_fields[$key];
                    $checkKo = true;
                }
            }
        }

        if ($checkKo) {
            if ($options == "add") { // Mode Ajout
                //Session::addMessageAfterRedirect(sprintf(__("Mandatory fields are not filled. Please correct: %s"), implode(', ', $msg)), false, ERROR);
                Session::addMessageAfterRedirect(sprintf("Les champs obligatoires ne sont pas d&eacutefinis. Merci de corriger: %s", implode(', ', $msg)), false, ERROR, true);
            } else {                // Mode Mise � jour
                Session::addMessageAfterRedirect(sprintf("Les champs obligatoires ne sont pas correctement d&eacutefinis (%s). Le formulaire a &eacutet&eacute r&eacuteinitailis&eacute.", implode(', ', $msg)), false, ERROR, true);
            }
            return false;
        }
        return true;
    }

    /**
     * Fonction extecut�e avant l'ajout de la t�che dans la base
     */
    function prepareInputForAdd($input) {
        global $CFG_GLPI;

        $task = strtoupper($input['name']);

        $same = self::getSameNameTask($task);

        if ($same == true) {
            Session::addMessageAfterRedirect(sprintf("Nom de t&acircche d&eacutej&agrave existante"), false, ERROR, true);
            return false;
        }

        if (!$this->checkMandatoryFields($input, "add")) {
            return false;
        }
        return $input;
    }

    /**
     * Fonction extecut�e avant la mise � jour de la t�che dans la base
     */
    function prepareInputForUpdate($input) {
        global $CFG_GLPI;

        if (!$this->checkMandatoryFields($input, "update")) {
            return false;
        }
        return $input;
    }

    /**
     * Fonction extecut�e apr�s l'ajout de la t�che dans la base
     */
    function post_addItem() {
        global $DB, $CFG_GLPI;

        if (isset($this->input['_itemtype']) && isset($this->input['_items_id'])) {
            $task_item = new PluginRtntestalexTask_Item();
            $tmp['plugin_rtntestalex_tasks_id'] = $this->getID();
            $tmp['itemtype'] = $this->input['_itemtype'];
            $tmp['items_id'] = $this->input['_items_id'];
            $task_item->add($tmp);
        }
    }

    /**
     * Fonction permettant de d�finir les options de recherche
     */
    function getSearchOptions() {
        global $CFG_GLPI, $LANG;

        $tab = array();
        $tab['common'] = __s('Tasks', 'rtntestalex');

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
        $tab[3]['field'] = 'cartridges';
        $tab[3]['name'] = "Bandes";            //LIBELLE A MODIFER
        $tab[3]['datatype'] = 'bool';
        $tab[3]['massiveaction'] = false;
        $tab[3]['injectable'] = false;

        $tab[4]['table'] = 'glpi_plugin_rtntestalex_tasktypes';
        $tab[4]['field'] = 'name';
        $tab[4]['name'] = "Type de tache";            //LIBELLE A MODIFER
        $tab[4]['datatype'] = 'dropdown';
        $tab[4]['massiveaction'] = false;

        $tab[5]['table'] = $this->getTable();
        $tab[5]['field'] = 'comment';
        $tab[5]['name'] = "Commentaire"; //LIBELLE A MODIFER
        $tab[5]['massiveaction'] = false;
        $tab[5]['injectable'] = true;
        $tab[5]['checktype'] = 'text';
        $tab[5]['displaytype'] = 'text';

        $tab[6]['table'] = $this->getTable();
        $tab[6]['field'] = 'description';
        $tab[6]['name'] = "Description"; //LIBELLE A MODIFER
        $tab[6]['massiveaction'] = false;
        $tab[6]['injectable'] = true;
        $tab[6]['checktype'] = 'text';
        $tab[6]['displaytype'] = 'text';

        return $tab;
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
             `entities_id` int(11) NOT NULL DEFAULT '0',
             `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
             `plugin_rtntestalex_tasktypes_id` int(11) NOT NULL DEFAULT '0',
             `description` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL,
             `comment` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL,
             `cartridges` boolean NOT NULL,
             `date_mod` datetime DEFAULT NULL,
             `is_global` tinyint(1) NOT NULL DEFAULT '0',
             `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
             PRIMARY KEY (`id`),
             KEY `name` (`name`),
             KEY `plugin_rtntestalex_tasktypes_id` (`plugin_rtntestalex_tasktypes_id`),
             KEY `entities_id` (`entities_id`),
             KEY `is_deleted` (`is_deleted`),
             KEY `is_global` (`is_global`)
           ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;";
            $DB->query($query) or die("Error adding table $table");


            // On ajoute les préferences d'affichage (Voir les n° dans la fonction getSearchOptions())
            $query = "INSERT IGNORE INTO `glpi_displaypreferences` (`id`, `itemtype`, `num`, `rank`, `users_id`) VALUES
                 (NULL, '" . __CLASS__ . "', 3, 2, 0),
                 (NULL, '" . __CLASS__ . "', 4, 1, 0);";
            $DB->query($query) or die($DB->error());
        }
    }

    /**
     * Fonction execut�e � la mise � jour du plugin (pour la gestion des t�ches)
     */
    static function upgrade(Migration $migration) {
        global $DB;

        switch (plugin_rtntestalex_currentVersion()) {
            case '1.0':
            case '1.x':
        }
    }

    /**
     * Fonction execut�e � la d�sinstallation du plugin (pour la gestion des t�ches)
     */
    static function uninstall() {
        global $DB;

        // Remove unicity constraints on tasks
        FieldUnicity::deleteForItemtype("RtntestalexTask"); // ?????
        // Suppression des liens qui peuvent exister  dans les differentes tables
        foreach (array('Notepad', 'DisplayPreference', 'Fieldblacklist', 'Bookmark', 'Log') as $itemtype) {
            $item = new $itemtype();
            $item->deleteByCriteria(array('itemtype' => __CLASS__));
        }

        $plugin = new Plugin();
        if ($plugin->isInstalled('customfields') && $plugin->isActivated('customfields')) {
            PluginCustomfieldsItemtype::unregisterItemtype('PluginRtntestalexTask');
        }

        $table = getTableForItemType(__CLASS__);
        $DB->query("DROP TABLE IF EXISTS `$table`");
    }

    /**
     * Fonction execut�e � la suppression d'une t�che
     */
    function cleanDBonPurge() {
        $link = new PluginRtntestalexTask_Item();
        $link->cleanDBonItemDelete($this->getType(), $this->getID());
    }

    /**
     * Suppression d'une t�che
     */
    /* function delete(array $input, $force=0, $history=1) {
      $deleteSuccessful = parent::delete($input, $force, $history);
      if ($deleteSuccessful != false) {
      if ($force == 1) {
      $notepad = new Notepad();
      $notepad->deleteByCriteria(array(
      'itemtype' => 'PluginRtntestalexTask',
      'items_id' => $input['id']
      ));
      }
      }
      return $deleteSuccessful;
      } */
}

?>
