<?php

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

/// Class Archive
class PluginRtntestalexArchive extends CommonDBTM {

    // From CommonDBTM
    public $dohistory = true;
    //On récupère si l'utilisateur connecté possède les droits à partir du fichier profile.class.php et on le stock dans la variable $rightname
    static $rightname = PluginRtntestalexProfile::RIGHT_RTNTESTALEX_ARCHIVE;
    protected $usenotepad = true;
    //Constante utilisé pour le nom d'un nouvel Archivage
    const NOM_ARCHIVAGE = "Archivage du "; //LIBELLE A MODIFIER

    /**
     * Retourne le nom de la classe
     *
     * @param $nb entier nombre d'item dans le type (par défaut 0)
     * */
    static function getTypeName($nb = 0) {
        global $LANG;
        return __s('Archives', 'rtntestalex'); //LIBELLE A MODIFIER
    }

    /**
     * Gestion des droits (gestion des archivages)
     *
     * */
    function getRights($interface = 'central') {
        $rights = parent::getRights();
        return $rights;
    }

    /**
     * Fonction permettant de récupérer les bandes qui ne sont pas à la corbeille
     * 
     * */
    static function getCartridges() {
        global $DB, $CFG_GLPI;

        $values = array();

        //On récupère les bandes qui ne sont pas archivées et qui ne sont pas supprimées
        $query = "SELECT * FROM `glpi_plugin_rtntestalex_cartridges` WHERE `is_archived`='0' AND `is_deleted`='0'";

        $result = $DB->query($query);
        //On check si la requete nous renvoi des lignes
        if ($DB->numrows($result)) {
            while ($data = $DB->fetch_array($result)) {
                //On affecte comme clef le nom de la bande et comme valeur associé le nom de la bande
                //On fait ceci car la clef sera ce qui sera insérer dans la base et la valeur associé sera affiché dans le formulaire
                $values[$data['name']] = $data['name'];
            }
        }
        return $values;
    }

    /**
     * Actions permettant de configurer la liste déroulante qui sera affiché dans le formulaire
     *
     * @param $options array of possible options:
     *    - name : nom de la sélection
     *    - values : Tableau des valeurs
     *
     * */
    static function dropdownCartridges($options = array()) {
        global $CFG_GLPI;

        $p['name'] = 'cartridgeslist';
        $p['values'] = array();
        $p['display'] = true;

        //On test si c'est bien un tableau et on compte le nombre de résultat
        if (is_array($options) && count($options)) {
            foreach ($options as $key => $val) {
                $p[$key] = $val;
            }
        }
        $values = self::getCartridges();

        // On affecte le même clef à la même valeur et on stock dans le tableau values
        // Utiliser lors de la modification d'une archive pour integrer les bandes de l'archive
        foreach ($p['values'] as $value) {
            $values[$value] = $value;
        }


        $p['multiple'] = true;
        $p['size'] = 3;
        $p['width'] = "50%";
        return Dropdown::showFromArray($p['name'], $values, $p);
    }

    /**
     * Actions permettant de récupérer le nom de l'utilisateur courant
     *
     */
    static function getUser() {
        global $DB, $CFG_GLPI;

        $value = "";

        //On récupère l'id de l'utilisateur connecté
        $id = Session::getLoginUserID();
        //On  récupère les informations de l'utilisateur connecté
        $query = "SELECT * FROM `glpi_users` WHERE `glpi_users`.`id` = '" . $id . "'";

        $result = $DB->query($query);
        //On check si la requête nous renvoi des une ligne
        if ($DB->numrows($result)) {
            $data = $DB->fetch_array($result);
            //On récupère le nom de l'utilisateur connecté et on le stock dans la variable $value
            $value = $data['name'];
        }

        //On renvoi le nom de l'utilisateur
        return $value;
    }

    /**
    * Actions permettant de récupérer la date du jour 
    */
    static function getDate() {
        $date = '';
        $date = date("Y-m-d 00:00");
        return $date;
    }

    /**
     *
     * Permet de définir les éléments qui vont être affichés dans le menu vertical d'un archivage
     * @param $options array 
     */
    function defineTabs($options = array()) {
        global $LANG;
        $ong = array();
        $this->addDefaultFormTab($ong);
        if ($this->fields['id'] > 0) {
            if (!isset($options['withtemplate']) || empty($options['withtemplate'])) {
                $this->addStandardTab('PluginRtntestalexArchive_Item', $ong, $options);
                $this->addStandardTab('PluginRtntestalexReport_Item', $ong, $options);
                //Eléments reliés aux archives
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
     * Affiche le formulaire de création et de modification d'un archivage
     *
     * @param $ID entier id de l'élement
     * @param $options   array
     *     - cible du formulaire
     *     - withtemplate template 
     *
     * @return Nothing (display)
     * */
    function showForm($ID, $options = array()) {
        global $CFG_GLPI, $DB, $LANG;

        //On récupére true si l'utilisateur possède les droits et false si il ne les possède pas
        $canupdate = Session::haveRight(self::$rightname, CREATE);

        if (!$this->canView())
            return false;

        $target = $this->getFormURL();
        //Récupération de la date du jour
        $datenom = date("Y-m-d");

        if (isset($options['target'])) {
            $target = $options['target'];
        }

        //Gestion des valeurs par défaut
        $default_values = array('name' => self::NOM_ARCHIVAGE . $datenom, //LIBELLE A MODIFIER
            'date' => self::getDate(),
            'users_id_tech' => self::getUser(),
            'plugin_rtntestalex_archivetypes_id' => 0,
            'cartridgeslist' => '',
            'comment' => '',
            'description' => '');

        //Obtention des valeurs par défaut pour l'affichage des valeurs sur le formulaire lors du rechargement
        if (isset($REQUEST)) {
            $values = Html::cleanPostForTextArea($_REQUEST);
        }

        //Restauration des données sauvegardées par la session précédente
        $saved = $this->restoreInput();
        //On déroule le tableau de valeurs par défaut et on associe une clef et ça valeur associé 
        foreach ($default_values as $name => $value) {
            //On check si la clef est existante
            if (!isset($values[$name])) {
                //On check si il n'y a pas une clef qui porte déjà ce nom
                //Si c'est le cas alors on réaffiche la valeur précédente sinon on affiche la valuer présente en base sinon on affiche la valeur par défaut
                if (isset($saved[$name])) {
                    $values[$name] = $saved[$name];
                } elseif (isset($this->fields[$name]) && (!empty($ID))) {
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
        if (isset($options['itemtype']) && isset($options['items_id'])) {
            echo "<tr class='tab_bg_1'>";
            echo "<td>" . __s('Associated element') . "</td>";
            echo "<td>";
            $item = new $options['itemtype'];
            $item->getFromDB($options['items_id']);
            echo $item->getLink(1);
            echo "</td>";
            echo "<td colspan='2'></td></tr>\n";
            echo "<input type='hidden' name='_itemtype' value='" . $options['itemtype'] . "'>";
            echo "<input type='hidden' name='_items_id' value='" . $options['items_id'] . "'>";
        }

        echo "<tr class='tab_bg_1'>";
        echo "<th>" . __s('Name') . "<span class='red'>*</span></th>";
        echo "<td>";

        //On check si il n'y a pas la valeur déjà renseigné en base
        $TMPfields = $this->fields['name'];
        if (isset($values['name'])) {
            $TMPfields = $values['name'];
        }
        echo "<input type='text' size='30' maxlength=250 name='name' value='".Html::cleanInputText($TMPfields)."' readonly>";
        echo "</td>";

        echo "<th>" . __s('Opening date') . "<span class='red'>*</span></th>";
        echo "<td>";
        if (empty($ID)) { //Mode Ajout
            $TMPfields = $this->fields["date"];
            //On check si il n'y a pas la valeur déjà renseigné en base ou stocke car erreur dans la saisie
            if (isset($values['date'])) {
                $TMPfields = $values['date'];
            }
            //On bloque la modification car prérempli
            Html::showDateTimeField("date", array('value' => $TMPfields,
                'timestep' => 1,
                'maybeempty' => false));
        } else {
            $TMPfields = $this->fields["date"];
            //On check si il n'y a pas la valeur déjà renseigné en base ou stocke car erreur dans la saisie
            if (isset($values['date'])) {
                $TMPfields = $values['date'];
            }
            //On bloque la modification car prérempli
            Html::showDateTimeField("date", array('value' => $TMPfields,
                'timestep' => 1,
                'maybeempty' => false,
                'canedit' => false));
        }

        echo "</td></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<th width='20%'>" . __s('Technician in charge of the hardware') . "<span class='red'>*</span></th>";
        echo "<td>";
        $TMPfields = $this->fields['users_id_tech'];
        //On check si il n'y a pas la valeur déjà renseigné en base ou stocke car erreur dans la saisie
        if (isset($values['users_id_tech'])) {
            $TMPfields = $values['users_id_tech'];
        }
        echo "<input type='text' size='30' maxlength=250 name='users_id_tech' value='" . Html::cleanInputText($TMPfields) . "' readonly >";
        echo "</td>";

        // Champ Type d'e tâches'archives
        echo "<th>" . __s('Type of archives', 'rtntestalex') . "<span class='red'>*</span></th>";
        echo "<td>";
        $TMPfields = $this->fields['plugin_rtntestalex_archivetypes_id'];
        //On check si il n'y a pas la valeur déjà renseigné en base ou stocke car erreur dans la saisie
        if (isset($values['plugin_rtntestalex_archivetypes_id'])) {
            $TMPfields = $values['plugin_rtntestalex_archivetypes_id'];
        }
        Dropdown::show('PluginRtntestalexArchiveType', array('value' => $TMPfields));
        echo "</td></tr>\n";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<th>" . __s('Cartridges', 'rtntestalex') . "<span class='red'>*</span></th>"; // LIBELLE A MODIFIER
        echo "<td>";
        //On test si il n'y a pas des bandes qui sont déjà sauvegardés en base et s'il existe bien un tableau dans la base
        if (isset($this->fields["cartridgeslist"]) && !is_array($this->fields["cartridgeslist"])) {

            //On importe le tableau stocke en base 
            $this->fields["cartridgeslist"] = importArrayFromDB($this->fields["cartridgeslist"]);

            /*if (isset($saved['cartridgeslist'])){
                $TMPfields = $saved['cartridgeslist'];
            } else {
                $TMPfields = $this->fields["cartridgeslist"];
            }*/

            //On affiche la liste déroulante avec les bandes stockés ou non
            self::dropdownCartridges(array('values' => $this->fields["cartridgeslist"]));
            //self::dropdownCartridges(array('values' => $TMPfields));
            
        }

        echo "</td></tr>\n";

        echo "<tr class='tab_bg_1'>";
        // Champ Description      
        echo "<th width='20%'>" . __s('Description') . "</th>";
        echo "<td>";
        $TMPfields = $this->fields['description'];
        //On check si il n'y a pas la valeur déjà renseigné en base ou stocke car erreur dans la saisie
        if (isset($values['description'])) {
            $TMPfields = $values['description'];
        }
        echo "<textarea cols='30' rows='10' name='description' >" . $TMPfields . "</textarea></td>";
        echo "</td>";
        // Champ Commentaire
        echo "<th width='20%'>" . __s('Comments') . "</th>";
        echo "<td>";
        $TMPfields = $this->fields['comment'];
        //On check si il n'y a pas la valeur déjà renseigné en base ou stocke car erreur dans la saisie
        if (isset($values['comment'])) {
            $TMPfields = $values['comment'];
        }
        echo "<textarea cols='30' rows='10' name='comment' >" . $TMPfields . "</textarea>";
        echo "</td></tr>\n\n";

        if ($this->fields['is_deleted'] == 1) {
            // On cache tous les boutons
            // Une bande supprimée ne doit pas etre restaurée
            // On fera une purge manuelle pour supprimer definitivement une bande
            echo "<tr class='tab_bg_1 footerRow'>";
            echo "<th colspan='2'><img src='/glpi/pics/warning.png'/></th>";
            echo "<th colspan='2'><span class='red'>Une archive à la corbeille ne peut pas être restaurée</span></th></tr>\n\n";
            $options['canedit'] = false;
        }
        

        $this->showFormButtons($options);

        return true;
    }

    /**
     * Vérification de la saisie des champs obligatoires
     */
    function checkMandatoryFields($input, $options) {
        $msg = array();
        $checkKo = false;

        $mandatory_fields = array('name' => "Nom",                          // LIBELLE A MODIFIER
            'plugin_rtntestalex_archivetypes_id' => "Type d'archives",      // LIBELLE A MODIFIER
            'date' => "Date",                                               // LIBELLE A MODIFIER
            'users_id_tech' => "Responsable technique",                     // LIBELLE A MODIFIER
            'cartridgeslist' => "Bandes");                                  // LIBELLE A MODIFIER

        // Si la liste des bandes est vide, le champ cartridgeslist n'est pas passé dans la variable $input
        // On l'ajout manuellement pour pouvoir faire le test des champs obligatoires
        if (!isset($input['cartridgeslist'])) {
            $input = $input + array('cartridgeslist' => "") ;
        }
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
            } else {   // Mode Mise à jour
                Session::addMessageAfterRedirect(sprintf("Les champs obligatoires ne sont pas correctement d&eacutefinis (%s). Le formulaire a &eacutet&eacute r&eacuteinitailis&eacute.", implode(', ', $msg)), false, ERROR, true);
            }
            return false;
        }
        return true;
    }

    /**
     * Actions à réaliser avant la modification d'un item dans la base
     * @param $input les données utilisées pour la modfication d'un item
     * @return Envoi les données modifiés sous forme d'un tableau
     *
     */
    function prepareInputForUpdate($input) {
        global $DB;
        
        //On effectue tous les test de la fonction checkMandatoryFields et si il y a une erreur alors on empeche l'insertion dans la base
        if (!$this->checkMandatoryFields($input, "update")) {
            return false;
        }
        
        //Il y a une conversion qui est faite pour pouvoir insérer les bandes sélectionnées 
        //Cette liste contient les bandes avant modification  
        //Le but de cette conversion est de convertir un tableau qui serait impossible à insérer dans la base en une chaine de caractère  
        $input["cartridgeslist"] = exportArrayToDB($input["cartridgeslist"]);

        //On récupère l'archive sur laquel on se situe
        $query = "SELECT * FROM `glpi_plugin_rtntestalex_archives` WHERE id = " . $input["id"] . "";

        if ($result = $DB->query($query)) {
            if ($DB->numrows($result)) {
                $data = $DB->fetch_array($result);
                //On récupère la chaîne de caractère qui est stocké en base qu'on converti en tableau 
                $tab = importArrayFromDB($data["cartridgeslist"]);
                foreach ($tab as $value) {
                    //On passe toutes les bandes récupérées à non archivé
                    $query = "UPDATE `glpi_plugin_rtntestalex_cartridges` 
                SET is_archived = 0
                WHERE name = '" . $value . "'";
                    $DB->query($query) or die($DB->error());
                }
            }
        }

        //Il y a une conversion qui est faite pour pouvoir insérer les bandes sélectionnées 
        //Cette liste contient les bandes avant modification  
        //Le but de cette conversion est de convertir un tableau qui serait impossible à insérer dans la base en une chaine de caractère  
        $tmp2 = importArrayFromDB($input["cartridgeslist"]);
        foreach ($tmp2 as $value) {
            //On passe toutes les bandes à archivés
            $query = "UPDATE `glpi_plugin_rtntestalex_cartridges` 
                SET is_archived = 1
                WHERE name = '" . $value . "'";
            $DB->query($query) or die($DB->error());
        }
        
        return $input;
    }

    /**
     * Actions à réaliser avant l'ajout d'un item dans la base
     * @param $input les données utilisées pour l'ajout d'un item
     * @return Envoi les données modifiés sous forme d'un tableau
     */
    function prepareInputForAdd($input) {
        global $CFG_GLPI, $DB, $tmp;

        // Modification du champ Nom
        $input["name"] = self::NOM_ARCHIVAGE . date("Y-m-d", strtotime($input["_date"]));   //LIBELLE A MODIFIER

        //On effectue tous les test de la fonction checkMandatoryFields et si il y a une erreur alors on empeche l'insertion dans la base
        if (!$this->checkMandatoryFields($input, "add")) {
            return false;
        }
        
        // On ajoute des bandes dans la base
        $input["cartridgeslist"] = exportArrayToDB($input["cartridgeslist"]);

        return $input;
    }

    /**
     * Supprime les droits inutiles du helpdesk
     * */

    //A SUPPRIMER
    function cleanProfile() {
        
        if (isset($this->fields["cartridgeslist"]) && !is_array($this->fields["cartridgeslist"])) {
            $this->fields["cartridgeslist"] = importArrayFromDB($this->fields["cartridgeslist"]);
        }

        // Empty/NULL case
        if (!isset($this->fields["cartridgeslist"]) || !is_array($this->fields["cartridgeslist"])) {
            $this->fields["cartridgeslist"] = array();
        }
    }

    /**
     * Actions à réaliser après l'ajout d'un item dans la base
     */
    function post_addItem() {
        global $DB, $CFG_GLPI, $tmp;

        if (isset($this->input['_itemtype']) && isset($this->input['_items_id'])) {
            $rtnarchive_item = new PluginRtntestalexArchive_Item();
            $tmp['plugin_rtntestalex_archives_id'] = $this->getID();
            $tmp['itemtype'] = $this->input['_itemtype'];
            $tmp['items_id'] = $this->input['_items_id'];
            $rtnarchive_item->add($tmp);
        }

        $tmp = importArrayFromDB($this->input["cartridgeslist"]);
        foreach ($tmp as $value) {
            $query = "UPDATE `glpi_plugin_rtntestalex_cartridges` 
                SET is_archived = 1
                WHERE name = '" . $value . "'";
            $DB->query($query) or die($DB->error());
        }

        return $tmp;
    }

    /**
     * Actions executés après la modification d'un item dans la base
     * @param $history on stock s'il y a un changement par défaut 1
     * @return rien 
     */
    function post_updateItem($history = 1) {
        global $DB, $tmp;

        // To avoid log out and login when rights change (very useful in debug mode)
        if (isset($_SESSION['glpiactiveprofile']['id']) && $_SESSION['glpiactiveprofile']['id'] == $this->input['id']) {

            if (in_array('cartridgeslist', $this->updates)) {
                $_SESSION['glpiactiveprofile']['cartridgeslist'] = importArrayFromDB($this->input['cartridgeslist']);
            }
        }
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
        $tab['common'] = __s('Archives', 'rtntestalex');

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
        $tab[2]['massiveaction'] = false;
        $tab[2]['injectable'] = false;

        $tab[3]['table'] = $this->getTable();
        $tab[3]['field'] = 'date';
        $tab[3]['name'] = __('Date');
        $tab[3]['massiveaction'] = false;
        $tab[3]['injectable'] = false;

        $tab[4]['table'] = $this->getTable();
        $tab[4]['field'] = 'cartridgeslist';
        $tab[4]['name'] = __('Cartridges');
        $tab[4]['massiveaction'] = false;
        $tab[4]['injectable'] = true;
        $tab[4]['checktype'] = 'text';
        $tab[4]['displaytype'] = 'text';
        return $tab;
    }

    /**
     * Installation dans la base 
     * 
     * @param Migration $migration migration helper instance
     */
    static function install(Migration $migration) {
        global $DB;
        //On récupere le nom de la table
        $table = getTableForItemType(__CLASS__);
        //On check si la table existe déjà dans la base
        if (!TableExists($table)) {
            $query = "CREATE TABLE IF NOT EXISTS `$table` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `entities_id` int(11) NOT NULL DEFAULT '0',
            `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
            `users_id_tech` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
            `plugin_rtntestalex_archivetypes_id` int(11) NOT NULL DEFAULT '0',
            `cartridgeslist` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL,
            `date` datetime DEFAULT NULL,
            `comment` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL,
            `description` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL,
            `date_mod` datetime DEFAULT NULL,
            `is_global` tinyint(1) NOT NULL DEFAULT '0',
            `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`),
            KEY `name` (`name`),
            KEY `entities_id` (`entities_id`),
            KEY `plugin_rtntestalex_archivetypes_id` (`plugin_rtntestalex_archivetypes_id`),
            KEY `is_global` (`is_global`),
            KEY `is_deleted` (`is_deleted`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;";
            $DB->query($query) or die("Error adding table $table");
        }
    }

    /**
     * Permet la mise à jour du plugin
     * @param Migration $migration migration helper instance
     *
     */
    static function upgrade(Migration $migration) {
        global $DB;

        switch (plugin_rtntestalex_currentVersion()) {
            case '1.0':
            case '1.x':
        }
    }

    /**
     * Permet la désinstallation du plugin
     *
     */
    static function uninstall() {
        global $DB;

        foreach (array('Notepad', 'DisplayPreference', 'Bookmark', 'Log') as $itemtype) {
            $item = new $itemtype();
            $item->deleteByCriteria(array('itemtype' => __CLASS__));
        }

        //On crée une nouvelle instance de plugin
        $plugin = new Plugin();
        // on check si le plugin est activé et si la classe datainjectionsmodels existe
        if ($plugin->isActivated('datainjection') && class_exists('PluginDatainjectionModel')) {
            PluginDatainjectionModel::clean(array('itemtype' => __CLASS__));
        }

        if ($plugin->isInstalled('customfields') && $plugin->isActivated('customfields')) {
            PluginCustomfieldsItemtype::unregisterItemtype('PluginRtntestalexArchive');
        }

        //On récupère le nom pour la table pour pouvoir la supprimé de la base
        $table = getTableForItemType(__CLASS__);
        $DB->query("DROP TABLE IF EXISTS `$table`");
    }

    /**
     * Permet l'ajout d'une ligne dans le profil pour la partie archivage permettant ainsi de gérer les droits sur cette liste
     * @param  $item utilse pour l'affichage
     *         $withtemplate permet l'utilsation d'un template par défaut 0
     *
     */
    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

        if (in_array(get_class($item), PluginRtntestalexArchive_Item::getClasses()) || get_class($item) == 'Profile') {
            return __s('Archive', 'rtntestalex');
        } elseif (get_class($item) == __CLASS__) {
            return __s('Archive', 'rtntestalex');
        }
        return '';
    }

    /**
     *  Affichage du contenu pour la liste des archivages
     * 
     * @param CommonGLPI $item
     * @param number $tabnum
     * @param number $withtemplate
     */
    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

        $self = new self();
        if ($item->getType() == 'PluginRtntestalexArchive') {
            $self->showtotal($item->getField('id'));
        }
        return true;
    }

    /**
     * Types qui peuvent être reliés à un archivage
     *
     * @param $all boolean, all type, or only allowed ones
     *
     * @return array of types
     * */
    static function getTypes($all = false) {

        if ($all) {
            return self::$types;
        }

        // Only allowed types
        $types = self::$types;

        foreach ($types as $key => $type) {
            if (!class_exists($type)) {
                continue;
            }

            $item = new $type();
            if (!$item->canView()) {
                unset($types[$key]);
            }
        }
        return $types;
    }

    /**
     * Permet l'affichage du bouton d'ajout d'un archivage
     * 
     * @return array
     */
    static function getMenuContent() {

        $menu = array();
        $menu['title'] = __s('Archive', 'rtntestalex'); //LIBELLE A MODIFIER
        $menu['page'] = self::getSearchURL(false);
        $menu['links']['search'] = self::getSearchURL(false);
        if (self::canCreate()) {
            //Permet d'ajouter le "+" dans le fil d'Ariane pour ajouter une archive
            $menu['links']['add'] = '/front/setup.templates.php?itemtype=PluginRtntestalexArchive&add=1';
        }
        return $menu;
    }

    /**
     * Permet de supprimer les relations entre un type d'archivage et un archivage
     *
     * @return nothing
     * */
    function cleanDBonPurge() {
        $link = new PluginRtntestalexArchive_Item();
        $link->cleanDBonItemDelete($this->getType(), $this->getID());
    }

    /**
     * Suppresion d'un item dans la base
     *
     * @see CommonDBTM::delete()
     *
     * @param $input     array    the _POST vars returned by the item form when press delete
     * @param $force     boolean  force deletion (default 0)
     * @param $history   boolean  do history log ? (default 1)
     *
     * @return boolean : true on success
     * */
    function delete(array $input, $force = 0, $history = 1) {
        global $DB;
        
        // On supprime la bande de l'archivage pour la rendre dispo pour les rapports ou d'autres archivages
        // On boucle sur l'ensemble des bandes de l'archivage
        foreach ($input["cartridgeslist"] as $value) {
            $query = "UPDATE `glpi_plugin_rtntestalex_cartridges` 
                SET is_archived = 0
                WHERE name = '" . $value . "'";
            $DB->query($query) or die($DB->error());
        }
        
        $deleteSuccessful = parent::delete($input, $force, $history);
        if ($deleteSuccessful != false) {
            if ($force == 1) {
                $notepad = new Notepad();
                $notepad->deleteByCriteria(array(
                    'itemtype' => 'PluginRtntestalexArchive',
                    'items_id' => $input['id']
                ));
            }
        }
        return $deleteSuccessful;
    }

}

?>
    