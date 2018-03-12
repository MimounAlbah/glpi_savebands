
<?php

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

/// Class Cartridge
class PluginRtntestalexCartridge extends CommonDropdown {

    // From CommonDBTM
    public $dohistory = true;
    //On récupère si l'utilisateur connecté possède les droits à partir du fichier profile.class.php et on le stock dans la variable $rightname
    static $rightname = PluginRtntestalexProfile::RIGHT_RTNTESTALEX_CARTRIDGE;
    protected $usenotepad = true;
    //Constante utilisé pour le nom d'une nouvelle bande
    const NOM_BANDE = "LTO";

    /**
     * Permet de récupérer le nom pour l'affichage
     */
    static function getTypeName($nb = 0) {
        global $LANG;
        return __s('Cartridge', 'rtntestalex'); //LIBELLE A MODIFIER
    }

    /**
    * Actions permettant de récupèrer la date du jour
    */
    static function getDate() {
        $date = '';
        $date = date("Y-m-d 00:00");
        return $date;
    }

    /**
     * Actions permettant de regarder en base si il n'y a pas une bande portant le même nom
     * Cette fonction sera executé dans le prepareInputForAdd et Update
     * @param $input string le nom de la bande tapé dans le formulaire de saisie
     */
    static function getSameNameCartridge($input) {
        global $DB, $CFG_GLPI;

        //On recupère toutes les bandes
        $query = "SELECT *
              FROM `glpi_plugin_rtntestalex_cartridges`";


        $result = $DB->query($query);
        //On vérifie que le nom de la tache rentré ne soit pas le même que celui en base
        //On ne diférencie pas un nom mis en majuscule et un minuscule car le test se fait en transformant des deux cotes en majuscule
        //On transforme le champ recupére en base pour le tester si il n'existe pas déjà  
        if ($DB->numrows($result)) {
            while($data = $DB->fetch_array($result)){
                if(strtoupper($data['name']) == strtoupper($input)){
                    $same = true;
                    break;
                }else{
                    $same = false;
                }
            }
        }
            

        return $same;
    }

    /**
     * Affichage du formulaire lié à une bande
     */
    function showForm($ID, $options = array()) {
        global $CFG_GLPI, $DB, $LANG;

        //On regarde si l'utilisateur connecté possède les droits de visualisation sinon on bloque l'affichage 
        if (!$this->canView())
            return false;

        // Gestion des valeurs par défaut
        $default_values = array('name' => self::NOM_BANDE,
            'date_mes' => self::getDate(),
            'comment' => '');

        // Permet d'obtenir l'affichage des valeurs par défaut ou celles précédemment saisies
        // L'utilisation du REQUEST permet de ne pas différencier du POST ou du GET
        if (isset($_REQUEST)) {
            $values = Html::cleanPostForTextArea($_REQUEST);
        }

        // On restaure les données sauvegardés par la session precedente
        $saved = $this->restoreInput();
        //On déroule le tableau de valeurs par défaut et on associe une clef et ça valeur associé 
        foreach ($default_values as $name => $value) {
            //On check si la clef est existante
            if (!isset($values[$name])) {
                //On check si il n'y a pas une clef qui porte déjà ce nom
                //Si c'est le cas alors on réaffiche la valeur précédente sinon on affiche la valuer présente en base sinon on affiche la valeur par défaut
                if (isset($saved[$name])) {
                    $values[$name] = $saved[$name];
                } elseif ((isset($this->fields[$name])) && (!empty($ID))) {
                    $values[$name] = $this->fields[$name];
                } else {
                    $values[$name] = $value;
                }
            }
        }

        /**
         * Affichage de l'en-tête de glpi
         * @param $options array
         *      - utilisateur cible pour affichage d'un menu selon les droits
         */
        $this->showFormHeader($options);

        echo "<tr class='tab_bg_1'>";
        // Champ Nom
        echo "<th>" . __s('Name') . "<span class='red'>*</span></th>"; // LIBELLE A MODIFIER
        echo "<td>";

        //On check si il n'y a pas la valeur déjà renseigné en base
        $TMPfields = $this->fields['name'];
        if (isset($values['name'])) {
            $TMPfields = $values['name'];
        }
        echo "<input type='text' size='30' maxlength=250 name='name' value=\"" . Html::cleanInputText($TMPfields) . "\"";
        if (empty($ID)) {
            //Mode Ajout on ne bloque pas la modification
            echo ">";
        } else {
            //Mode modification on bloque la modification
            echo "readonly>";
        }
        echo "</td>";

        // Champ Date de mise en service
        echo "<th>Date de mise en service<span class='red'>*</span></th>";  // LIBELLE A MODIFIER
        echo "<td>";
        if (empty($ID)) {
            //Mode Ajout on autorise la modification de la date
            //On check si il n'y a pas la valeur déjà renseigné en base
            $TMPfields = $this->fields["date_mes"];
            if (isset($values['date_mes'])) {
                $TMPfields = $values['date_mes'];
            }
            Html::showDateTimeField("date_mes", array('value' => $TMPfields,
                'timestep' => 1,
                'maybeempty' => false));
        } else {
            //Mode modification on bloque cette date
            $TMPfields = $this->fields["date_mes"];
            if (isset($values['date_mes'])) {
                $TMPfields = $values['date_mes'];
            }
            Html::showDateTimeField("date_mes", array('value' => $TMPfields,
                'timestep' => 1,
                'maybeempty' => false,
                'canedit' => false));
        }


        echo "</td></tr>";

        echo "<tr class='tab_bg_1'>";
        // Champ Commentaire      
        echo "<th width='20%'>" . __s('Comments') . "</th>"; //LIBELLE A MODIFIER
        echo "<td>";
        $TMPfields = $this->fields['comment'];
        if (isset($values['comment'])) {
            $TMPfields = $values['comment'];
        }
        echo "<textarea cols='30' rows='10' name='comment' >" . $TMPfields . "</textarea></td>";

        // Si la bande est archivée
        if ($this->fields['is_archived'] == 1) {
            echo "<th><img src='/glpi/pics/warning.png'/><span class='red'>Bande archivée</span></th>";  // LIBELLE A MODIFIER
            echo "<td></span></td>";
            // On cache le bouton supprimer
            $options['candel'] = false;
        }
        echo "</tr>";

        $this->showFormButtons($options);

        return true;
    }

    /**
     * Vérification de la saisie des champs obligatoires
     */
    function checkMandatoryFields($input, $options) {
        $msg = array();
        $checkKo = false;

        $mandatory_fields = array('name' => "Nom", // LIBELLE A MODIFIER
            'date_mes' => "Date de mise en service"); // LIBELLE A MODIFIER

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
                Session::addMessageAfterRedirect(sprintf("Les champs obligatoires ne sont pas d&eacutefinis. Merci de corriger: %s", implode(', ', $msg)), false, ERROR, true);
            } else {                // Mode Mise à jour
                Session::addMessageAfterRedirect(sprintf("Les champs obligatoires ne sont pas correctement d&eacutefinis (%s). Le formulaire a &eacutet&eacute r&eacuteinitailis&eacute.", implode(', ', $msg)), false, ERROR, true);
            }
            return false;
        }
        return true;
    }

    /**
     * Fonction extecutée avant l'ajout de la tâche dans la base
     */
    function prepareInputForAdd($input) {
        global $CFG_GLPI;

        //On appelle la fonction qui permet de savoir si il n'y a pas une bande du même nom en base
        $same = self::getSameNameCartridge($input['name']);

        //Si il existe une bande du même nom on annule l'insertion et on affiche un message d'erreur
        if ($same == true) {
            Session::addMessageAfterRedirect(sprintf("Bande déjà existante"), false, ERROR, true); //LIBELLE A MODIFIER
            return false;
        }

        //On check si la vérification des champs c'est bien passé sinon on annule l'insertion
        if (!$this->checkMandatoryFields($input, "add")) {
            return false;
        }
        return $input;
    }

    /**
     * Fonction extecutée avant la mise à jour de la tâche dans la base
     */
    function prepareInputForUpdate($input) {
        global $CFG_GLPI;

        //On modifie le nom de la bande pour la mettre en majuscule
        $bande = strtoupper($input['name']);

        //On appelle la fonction qui permet de savoir si il n'y a pas une bande du même nom en base
        $same = self::getSameNameCartridge($bande);

        //Si il existe une bande du même nom on annule l'insertion et on affiche un message d'erreur
        if ($same == 1) {
            Session::addMessageAfterRedirect(sprintf("Bande déjà existante"), false, ERROR, true);
            return false;
        }

        //On check si la vérification des champs c'est bien passé sinon on annule l'insertion
        if (!$this->checkMandatoryFields($input, "update")) {
            return false;
        }
        return $input;
    }

    /**
     * Fonction executée à l'installation du plugin (pour la gestion des bandes)
     */
    static function install(Migration $migration) {
        global $DB;
        //On récupère le nom pour la table
        $table = getTableForItemType(__CLASS__);
        //On check si la table existe déjà dans la base
        if (!TableExists($table)) {
            $query = "CREATE TABLE IF NOT EXISTS `$table` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `entities_id` int(11) NOT NULL DEFAULT '0',         
                `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                `comment` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                `date_mes` datetime DEFAULT NULL,
                `is_global` tinyint(1) NOT NULL DEFAULT '0',
                `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
                `is_archived` tinyint(1) NOT NULL DEFAULT '0',
                PRIMARY KEY (`id`),
                KEY `name` (`name`),
                KEY `entities_id` (`entities_id`),
                KEY `is_deleted` (`is_deleted`),
                KEY `is_global` (`is_global`),
                KEY `is_archived` (`is_archived`)         
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
            $DB->query($query) or die("Error adding table $table");

            //Insertion d'éléments pour test
            $query = "INSERT INTO `$table`(`name`) VALUES ('LTO123')";
            $DB->query($query) or die("Error adding in table $table (1)");
            $query = "INSERT INTO `$table`(`name`) VALUES ('LTO124')";
            $DB->query($query) or die("Error adding in table $table (2)");
            $query = "INSERT INTO `$table`(`name`) VALUES ('LTO125')";
            $DB->query($query) or die("Error adding in table $table (3)");
            $query = "INSERT INTO `$table`(`name`) VALUES ('LTO126')";
            $DB->query($query) or die("Error adding in table $table (4)");
            $query = "INSERT INTO `$table`(`name`) VALUES ('LTO127')";
            $DB->query($query) or die("Error adding in table $table (5)");

            // On ajoute les préferences d'affichage (Voir les n° dans la fonction getSearchOptions())
            $query = "INSERT IGNORE INTO `glpi_displaypreferences` (`id`, `itemtype`, `num`, `rank`, `users_id`) VALUES
               (NULL, '" . __CLASS__ . "', 4, 3, 0),
               (NULL, '" . __CLASS__ . "', 3, 2, 0);";
            $DB->query($query) or die($DB->error());
        }
    }

    /**
     * Fonction executée à la mise à jour du plugin (pour la gestion des bandes)
     */
    static function upgrade(Migration $migration) {
        global $DB;

        switch (plugin_rtntestalex_currentVersion()) {
            case '1.0':
                self::install($migration);
                break;
        }
    }

    /**
     * Fonction executée à la désinstallation du plugin (pour la gestion des bandes)
     */
    static function uninstall() {
        global $DB;

        foreach (array('DisplayPreference', 'Bookmark') as $itemtype) {
            $item = new $itemtype();
            $item->deleteByCriteria(array('itemtype' => __CLASS__));
        }

        // Remove dropdowns localization
        $dropdownTranslation = new DropdownTranslation();
        $dropdownTranslation->deleteByCriteria(array("itemtype = 'PluginRtntestalexCartridge'"), 1);

        //On récupère le nom pour la table
        $table = getTableForItemType(__CLASS__);
        //On supprime la table de la base
        $DB->query("DROP TABLE IF EXISTS `$table`");
    }

    static function transfer($ID, $entity) {
        global $DB;

        $cartridge = new self();

        if ($ID > 0) {
            $query = "SELECT *
                   FROM `" . $cartridge->getTable() . "`
                   WHERE `id` = '$ID'";

            if ($result = $DB->query($query)) {
                if ($DB->numrows($result)) {
                    $data = $DB->fetch_assoc($result);
                    $data = Toolbox::addslashes_deep($data);
                    $input['name'] = $data['name'];
                    $input['entities_id'] = $entity;
                    $newID = $cartridge->getID($input);

                    if ($newID < 0) {
                        $newID = $cartridge->import($input);
                    }
                    return $newID;
                }
            }
        }
        return 0;
    }

    /**
     * Permet de définir sur quels champs sera faite la recherche
     * @param rien
     * @return un tableau des options de recherche
     */
    function getSearchOptions() {
        global $CFG_GLPI, $LANG;

        $tab = array();
        $tab['common'] = __s('List of cartridges', 'rtntestalex');

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
        $tab[3]['field'] = 'date_mes';
        $tab[3]['name'] = "Date de mise en service";             // LIBELLE A MODIFIER

        $tab[4]['table'] = $this->getTable();
        $tab[4]['field'] = 'is_archived';
        $tab[4]['name'] = "En archive";                          // LIBELLE A MODIFIER
        $tab[4]['datatype'] = 'bool';

        $tab[5]['table'] = $this->getTable();
        $tab[5]['field'] = 'comment';
        $tab[5]['name'] = __('Comments');

        return $tab;
    }

}

?>
