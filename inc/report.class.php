<?php

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

/// Class Report
class PluginRtntestalexReport extends CommonDBTM {

    // From CommonDBTM
    public $dohistory = true;
    //On récupère si l'utilisateur connecté possède les droits à partir du fichier profile.class.php et on le stock dans la variable $rightname
    static $rightname = PluginRtntestalexProfile::RIGHT_RTNTESTALEX_REPORT;
    protected $usenotepad = true;
    static $types = array('Phone', 'Entity');
    //Constante utilisé pour le nom d'un nouveau rapport
    const NOM_RAPPORT = "Rapport du ";

    /**
     * Action permettant de récupérer le nom 
     *
     * @param $nb  integer  number of item in the type (default 0)
     * */
    static function getTypeName($nb = 0) {
        global $LANG;
        return __s('Reports', 'rtntestalex'); //LIBELLE A MODIFIER
    }

    /**
     * Actions permettant de recuperer les droits situe dans profil pour la parite rapport
     *
     * @see commonDBTM::getRights()
     * */
    function getRights($interface = 'central') {
        $rights = parent::getRights();
        return $rights;
    }

    /**
     * Actions permettant de récupérer les bandes stockés en base 
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
     * Actions permettant la configuration de liste déroulante pour affichage dans le formulaire 
     *
     * @param $options array of possible options:
     *    - name : string / name of the select 
     *    - values : array of values
     * */
    static function dropdownCartridges($options, $idtask) {
        global $CFG_GLPI;

        $p['name'] = 'cartridgeslist_' . $idtask;
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
        // Utiliser lors de la modification d'un rapport pour integrer les bandes du rapport
        foreach ($p['values'] as $value) {
            $values[$value] = $value;
        }

        $p['multiple'] = true;
        $p['size'] = 3;
        $p['width'] = "50%";
        return Dropdown::showFromArray($p['name'], $values, $p);
    }

    /**
     * Actions permettant de récupérer le nom de l'etat de la tache
     *
     */
    static function getState($id) {
        global $DB, $CFG_GLPI;

        $value = "";

        //Requete sql permettant de récupérer tous les états
        $query = "SELECT * FROM `glpi_plugin_rtntestalex_statetypes` WHERE `glpi_plugin_rtntestalex_statetypes`.`id` = '" . $id . "'";

        $result = $DB->query($query);
        if ($DB->numrows($result)) {
            $data = $DB->fetch_array($result);
            $value = $data['name'];
        }

        //On renvoi le libellé de l'état
        return $value;
    }

    /**
     * Actions permettant de récupérer le nom de l'utilisateur courant
     *
     */
    static function getUser() {
        global $DB, $CFG_GLPI;

        $value = "";

        //On récupère l'ID de l'utilisateur connecté
        $id = Session::getLoginUserID();

        //Requete sql permettant de récupérer toutes les informations de l'utilsateur connecté
        $query = "SELECT * FROM `glpi_users` WHERE `glpi_users`.`id` = '" . $id . "'";

        $result = $DB->query($query);
        if ($DB->numrows($result)) {
            $data = $DB->fetch_array($result);
            //On récupère le nom de l'utilisateur connecté et on le stock dans la variable $value
            $value = $data['name'];
        }

        //On renvoi le nom de l'utilsateur 
        return $value;
    }
    
    /**
     * Actions permettant de définir les éléments qu vont être affichés dans le menu vertical
     * @param $option array 
     *
     */
    function defineTabs($options = array()) {
        global $LANG;
        $ong = array();
        $this->addDefaultFormTab($ong);
        if ($this->fields['id'] > 0) {
            if (!isset($options['withtemplate']) || empty($options['withtemplate'])) {
                // A MODIFIER ajouter les items liés
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
     * Action permettant d'afficher le formulaire pour les rapports
     *
     * @param $ID entier id de l'objet
     * @param $options   array
     *     - cible pour le formulaire
     *     - withtemplate template 
     *
     * @return Nothing (display)
     * */
    function showForm($ID, $options = array()) {
        global $CFG_GLPI, $DB, $LANG;

        //On récupére true si l'utilisateur possède les droits et false si il ne les possède pas
        if (!$this->canView())
            return false;

        $target = $this->getFormURL();
        $withtemplate = '';

        if (isset($options['target'])) {
            $target = $options['target'];
        }

        //Gestion des valeurs par défaut
        $default_values = array('name' => self::NOM_RAPPORT . date("Y-m-d"), //LIBELLE A MODIFIER
            'date' => date("Y-m-d H:i:s"),
            'users_id_tech' => self::getUser(),
            'plugin_rtntestalex_states_id' => 0,
            'comment' => '',
            'description' => '');

        //Obtention des valeurs par défaut pour l'affichage des valeurs sur le formulaire lors du rechargement
        //Utilisation du $REQUEST pour ne pas se soucier du POST ou GET
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

        echo "<th></th>";
        echo "<tr class='tab_bg_1'>";
        echo "<th>" . __s('Name') . "<span class='red'>*</span></th>";
        echo "<td>";
        $TMPfields = $this->fields['name'];
        if (isset($values['name'])) {
            $TMPfields = $values['name'];
        }
        echo "<input type='text' size='30' maxlength=250 name='name' value='" . Html::cleanInputText($TMPfields) . "'>";
        echo "</td>";

        echo "<th>" . __s('Opening date') . "<span class='red'>*</span></th>";
        echo "<td>";
        if (empty($ID)) {
            //Mode ajout on bloque la modification de la date
            $TMPfields = $this->fields["date"];
            if (isset($values['date'])) {
                $TMPfields = $values['date'];
            }
            Html::showDateTimeField("date", array('value' => $TMPfields,
                'timestep' => 1,
                'maybeempty' => false,
                'canedit' => false));
        } else {
            //Mode modification on bloque la modification de la date
            $TMPfields = $this->fields["date"];
            if (isset($values['date'])) {
                $TMPfields = $values['date'];
            }
            Html::showDateTimeField("date", array('value' => $TMPfields,
                'timestep' => 1,
                'maybeempty' => false,
                'canedit' => false));
        }

        echo "</td></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<th>" . __s('Technician in charge of the hardware') . "<span class='red'>*<span></th>";
        echo "<td>";
        //On récupère le nom de l'utilisateur pour pouvoir l'ajouter ensuite dans la base
        $TMPfields = $this->fields['users_id_tech'];
        //On check si il n'y a pas la valeur déjà renseigné en base ou stocke car erreur dans la saisie
        if (isset($values['users_id_tech'])) {
            $TMPfields = $values['users_id_tech'];
        }
        echo "<input type='text' size='30' maxlength=250 name='users_id_tech' value='" . Html::cleanInputText($TMPfields) . "' readonly >";
        echo "</td>";
        echo "</td></tr>";

        echo "<tr class='tab_bg_5'>";
        echo "<th width='5'>N°</th>";                               // LIBELLE A MODIFIER
        echo "<th>Tâche effectuée</th>";                            // LIBELLE A MODIFIER
        echo "<th>Tâche<span class='red'>*</span></th>";            // LIBELLE A MODIFIER
        echo "<th>Résultat<span class='red'>*</span></th>";         // LIBELLE A MODIFIER
        echo "<th>Date<span class='red'>*</span></th>";             // LIBELLE A MODIFIER
        echo "<th>Commentaire</th>";                                // LIBELLE A MODIFIER
        echo "</tr>";

        $iduser = Session::getLoginUserID();

        /*         * Cette requête sert à récupérer toutes les tâches qui sont associés à l'utilisateur connecté, qui ne sont pas à la corbeille. On exécute un DISTINCT dessus pour empêcher d'avoir des doublons.
         * Il y a une jointure sur la table de liaison des rapports avec les tâches, des rapports et des items des tâches concernées.
         * Cette requête sera utilisé pour l'ajout de rapports
         */
        $queryadd = "SELECT DISTINCT `glpi_plugin_rtntestalex_reports_taskslinks`.`id`,
    `glpi_plugin_rtntestalex_reports_taskslinks`.`plugin_rtntestalex_reports_id`,
    `glpi_plugin_rtntestalex_reports_taskslinks`.`is_realized`, 
    `glpi_plugin_rtntestalex_tasks`.`name`, 
    `glpi_plugin_rtntestalex_reports_taskslinks`.`plugin_rtntestalex_states_id`, 
    `glpi_plugin_rtntestalex_tasks`.`id`,
    `glpi_plugin_rtntestalex_tasks`.`cartridges`, 
    `glpi_plugin_rtntestalex_reports_taskslinks`.`cartridgeslist`, 
    `glpi_plugin_rtntestalex_reports_taskslinks`.`comment`, 
    `glpi_plugin_rtntestalex_reports_taskslinks`.`date_checktask` 
    FROM `glpi_plugin_rtntestalex_tasks` 
    LEFT JOIN `glpi_plugin_rtntestalex_reports_taskslinks` 
    ON `glpi_plugin_rtntestalex_tasks`.`id` = `glpi_plugin_rtntestalex_reports_taskslinks`.`id`
    LEFT JOIN `glpi_plugin_rtntestalex_reports` 
    ON `glpi_plugin_rtntestalex_reports_taskslinks`.`plugin_rtntestalex_reports_id` = `glpi_plugin_rtntestalex_reports`.`id`
    LEFT JOIN `glpi_plugin_rtntestalex_tasks_items`
    ON `glpi_plugin_rtntestalex_tasks`.`id` = `glpi_plugin_rtntestalex_tasks_items`.`plugin_rtntestalex_tasks_id`
    WHERE `glpi_plugin_rtntestalex_tasks_items`.`items_id` = " . $iduser . " AND `glpi_plugin_rtntestalex_tasks`.`is_deleted` = 0
    ORDER BY `glpi_plugin_rtntestalex_tasks`.`id`";

        /**
         * Cette requête sert à récupérer toutes les tâches qui sont associés au rapport correspondant lors de la création de celui-ci
         * Il y a une jointure avec la table de liaison des rapports avec les tâches, des rapports et des items liés au tâches correspondantes
         * Cette requête sera utilisé la modification des rapports
         */
        $querymod = "SELECT DISTINCT `glpi_plugin_rtntestalex_reports_taskslinks`.`id`,
    `glpi_plugin_rtntestalex_reports_taskslinks`.`plugin_rtntestalex_reports_id`,
    `glpi_plugin_rtntestalex_reports_taskslinks`.`is_realized`, 
    `glpi_plugin_rtntestalex_tasks`.`name`, 
    `glpi_plugin_rtntestalex_reports_taskslinks`.`plugin_rtntestalex_states_id`, 
    `glpi_plugin_rtntestalex_tasks`.`id`,
    `glpi_plugin_rtntestalex_tasks`.`cartridges`, 
    `glpi_plugin_rtntestalex_reports_taskslinks`.`cartridgeslist`, 
    `glpi_plugin_rtntestalex_reports_taskslinks`.`comment`, 
    `glpi_plugin_rtntestalex_reports_taskslinks`.`date_checktask` 
    FROM `glpi_plugin_rtntestalex_tasks` 
    JOIN `glpi_plugin_rtntestalex_reports_taskslinks` 
    ON `glpi_plugin_rtntestalex_tasks`.`id` = `glpi_plugin_rtntestalex_reports_taskslinks`.`plugin_rtntestalex_tasks_id`
    JOIN `glpi_plugin_rtntestalex_reports` 
    ON `glpi_plugin_rtntestalex_reports_taskslinks`.`plugin_rtntestalex_reports_id` = `glpi_plugin_rtntestalex_reports`.`id`
    JOIN `glpi_plugin_rtntestalex_tasks_items`
    ON `glpi_plugin_rtntestalex_tasks`.`id` = `glpi_plugin_rtntestalex_tasks_items`.`plugin_rtntestalex_tasks_id`
    WHERE `glpi_plugin_rtntestalex_reports_taskslinks`.`plugin_rtntestalex_reports_id` = " . $ID . "
    ORDER BY `glpi_plugin_rtntestalex_tasks`.`id`";


    if (empty($ID)) {
        //Mode Ajout 
        $result = $DB->query($queryadd);
        //Cette option permettra de ne pas afficher de button Ajouter lorsqu'il n'y aura aucune tâche 
        $options['canedit'] = false;
        if ($DB->numrows($result)) {
            $IndexTable = 0;
            $options['canedit'] = true;
            while ($data = $DB->fetch_array($result)) {
                $IndexTable = $IndexTable + 1;
                echo "</br>";
                echo "<tr class='tab_bg_2'>";

                //L'id de la tâche affiché qui sera utilisé pour l'ajout et la modification
                echo "<th>"; 
                echo "<input type='text' class='center' size='5' name='idtask_$IndexTable' value='" . $data['id'] . "' readonly>";
                echo "</th>";
                    
                echo "<td>";    

                //On check qu'il n'y a pas une valeur enregistré temporairerement dû à une erreur de saisie 
                //Ensuite on check si il n'y a pas une valuer par défaut à rentrer 
                //Enfin on remplace par un champ vide si l'on rentre dans aucun cas cités ci-dessus             
                if (isset($values['is_realized_' . $IndexTable])){
                    $TMPfields = $values['is_realized_' . $IndexTable];
                } else if (isset($saved['is_realized_' . $IndexTable])){
                     $TMPfields = $saved['is_realized_' . $IndexTable];
                } else {
                    $TMPfields ="";
                }
                Dropdown::showYesNo('is_realized_' . $IndexTable, $TMPfields);
                echo "</td>";

                echo "<td><input type='text' size='30' name='name_" . $IndexTable . "' value='" . $data['name'] . "' readonly></td>";

                echo "<td>";

                if (isset($values['state_type_' . $IndexTable])){
                    $TMPfields = $values['state_type_' . $IndexTable];
                } else if (isset($saved['state_type_' . $IndexTable])){
                    $TMPfields = $saved['state_type_' . $IndexTable];
                } else {
                    $TMPfields ="";
                }                   
                Dropdown::show('PluginRtntestalexStateType', array('name' => "state_type_" . $IndexTable, 'value' => $TMPfields, 'comments' => false));
                    
                echo "</td>";

                echo "<td>";
                if (isset($values['date_checktask_' . $IndexTable])){
                    $TMPfields = $values['date_checktask_' . $IndexTable];
                } else if (isset($saved['date_checktask_' . $IndexTable])){
                    $TMPfields = $saved['date_checktask_' . $IndexTable];
                } else {
                    $TMPfields ="";
                }
                Html::showDateTimeField("date_checktask_" . $IndexTable, array('value' => $TMPfields,
                                        'timestep' => 1,
                                        'maybeempty' => false));
                echo "</td>";

                echo "<td>";
                if (isset($values['comment_' . $IndexTable])){
                    $TMPfields = $values['comment_' . $IndexTable];
                } else if (isset($saved['comment_' . $IndexTable])){
                    $TMPfields = $saved['comment_' . $IndexTable];
                } else {
                    $TMPfields ="";
                }                    
                echo "<textarea cols='30' rows='3' name='comment_" . $IndexTable . "'>$TMPfields</textarea>";
                echo "</td>";

                echo "<tr class='tab_bg_1'>";
                if ($data['cartridges'] == true) {
                    echo "<th>" . __s('Cartridges', 'rtntestalex') . "<span class='red'>*</span></th>";
                    echo "<td>";                         
                    if (isset($values['cartridgeslist_' . $IndexTable])){
                        $TMPfields = $values['cartridgeslist_' . $IndexTable];
                    } else if (isset($saved['cartridgeslist_' . $IndexTable])){
                        $TMPfields = $saved['cartridgeslist_' . $IndexTable];
                    } else {
                        $TMPfields = array();
                    }
                    // Champ caché permettant de savoir si la tâche est avec ou sans bande
                    // Utilisé lors de la vérification de la saisie des champs obligatoires
                    echo "<input type='text' hidden='hidden' name='cartridges_" . $IndexTable . "' value='1'>";
                    //On affiche la liste déroulante avec les bandes stockés ou non
                    self::dropdownCartridges(array('values' => $TMPfields), $IndexTable);
                    echo "</td>";
                } else {
                    // Champ caché permettant de savoir si la tâche est avec ou sans bande
                    // Utilisé lors de la vérification de la saisie des champs obligatoires
                    echo "<th>";
                    echo "<input type='text' hidden='hidden' name='cartridges_" . $IndexTable . "' value='0'>";
                    echo "</th>";
                }

                echo "</td>";
                echo "</tr>\n";
                echo "<tr><th></th><th></th><th></th><th></th><th></th><th></th></tr>";
                echo "<tr class='tab_bg_1'>";

                //Permet d'envoyer le nombre de tâche présente sur la création de rapport
                echo "<td><input type='text' hidden='hidden' name='IndexTable' value='" . $IndexTable . "'></td>";
                //Permet d'envoyer l'id du rapport pour l'ajout
                echo "<td><input type='text' hidden='hidden' name='ID' value='" . $ID . "'></td>";
                echo "</tr>";
            }
        }
        $this->showFormButtons($options);
    } else {
        //Mode modification
        $IndexTable = 0;
        $result = $DB->query($querymod);
        if ($DB->numrows($result)) {
            while ($data = $DB->fetch_array($result)) {
                $IndexTable = $IndexTable + 1;
                echo "</br>";
                echo "<tr class='tab_bg_2'>";
                    
                //Permet de renvoyer l'id de la tâche
                echo "<th>"; 
                echo "<input type='text' class='center' size='4' name='idtask_$IndexTable' value='" . $data['id'] . "' readonly>";
                echo "</th>";
                    
                echo "<td><input hidden='hidden' type='text' size='4' name='is_realized_" . $IndexTable . "' value='" .  $data['is_realized'] . "' readonly>";
                echo "<input class='center' type='text' size='4' name='is_realizedRO_" . $IndexTable . "' value='Oui' readonly></td>";
                echo "</td>";

                echo "<td><input type='text' size='30' name='name_" . $IndexTable . "' value='" . $data['name'] . "' readonly></td>";
                echo "</td>";
                    
                echo "<td><input hidden='hidden' type='text' size='15' name='state_type_" . $IndexTable . "' value='" .  $data['plugin_rtntestalex_states_id'] . "' readonly>";
                echo "<input class='center' type='text' size='15' name='state_typeRO_" . $IndexTable . "' value='" . self::getState($data['plugin_rtntestalex_states_id']) . "' readonly></td>";
                echo "</td>";

                echo "<td>";
                $this->fields["date_checktask_" . $IndexTable] = $data['date_checktask'];
                $date = $this->fields["date_checktask_" . $IndexTable];
                //On bloque la date grâce au canedit => false car non modifiable
                Html::showDateTimeField("date_checktask_" . $IndexTable, array('value' => $date,
                            'timestep' => 1,
                            'maybeempty' => false,
                            'canedit' => false));
                echo "</td>";

                echo "<td>";
                echo "<textarea cols='30' rows='3' name='comment_" . $IndexTable . "'>" . $data['comment'] . "</textarea>";
                echo "</td>";

                echo "<tr class='tab_bg_1'>";
                if ($data['cartridges'] == true) {
                    echo "<th>" . __s('Cartridges', 'rtntestalex') . "<span class='red'>*</span></th>"; //LIBELLE A MODIFIER

                    echo "<td>";
                    // Champ caché permettant de savoir si la tâche est avec ou sans bande
                    // Utilisé lors de la vérification de la saisie des champs obligatoires
                    echo "<input type='text' hidden='hidden' name='cartridges_" . $IndexTable . "' value='1'>";
                    //On importe le tableau stocke en base 
                    $this->fields["cartridgeslist_" . $IndexTable] = importArrayFromDB($data['cartridgeslist']);
                    //On affiche la liste déroulante avec les bandes stockés ou non
                    self::dropdownCartridges(array('values' => $this->fields["cartridgeslist_" . $IndexTable]), $IndexTable);
                        echo "</td>";
                } else {
                    // Champ caché permettant de savoir si la tâche est avec ou sans bande
                    // Utilisé lors de la vérification de la saisie des champs obligatoires
                    echo "<th>";
                    echo "<input type='text' hidden='hidden' name='cartridges_" . $IndexTable . "' value='0'>";
                    echo "</th>";                        
                }
                echo "</td>";
                echo "</tr>\n";
                echo "<tr><th></th><th></th><th></th><th></th><th></th><th></th></tr>";
                //Permet de renvoyer le nombre de tâche afficher sur l'écran de modification d'un rapport
                echo "<td><input type='text' hidden='hidden' name='IndexTable' value='" . $IndexTable . "'></td>";
                //Permet de renvoyer l'id du rapport
                echo "<td><input type='text' hidden='hidden' name='ID' value='" . $ID . "'></td>";
            }
        }
        $this->showFormButtons($options);
    }
    return true;
    }

    /**
     * Vérification de la saisie des champs obligatoires
     */
    function checkMandatoryFields($input, $options) {
        $msg = array();
        $checkKo = false;

        $array_fields = array();

        $compteur = $input['IndexTable'];
        for ($i = 1; $i <= $compteur; $i++) {
            if ($input['is_realized_' . $i] == 1) {
                $array_fields = array('name_' . $i => "Nom de la tâche n°" . $i,
                    'state_type_' . $i => "Etat de la tâche n°" . $i,
                    'date_checktask_' . $i => "Date de la tâche n°" . $i) + $array_fields;
                // Si la tache est avec bandes, on ajoute le champ Liste des bandes
                if ($input['cartridges_' . $i] == 1) {
                    $array_fields = array('cartridgeslist_' . $i => "Bande de la tâche n°" . $i) + $array_fields;    
                    // Si la liste des bandes est vide, le champ cartridgeslist_ n'est pas passé dans la variable $input
                    // On l'ajout manuellement pour pouvoir faire le test des champs obligatoires
                    if (!isset($input['cartridgeslist_' . $i])) {
                        $input = $input + array('cartridgeslist_' . $i => "") ;
                    }
                }
            }
        }

        $fields_tasks = array();
        $mandatory_fields = array('name' => "Nom",
            'date' => 'Date',
            'users_id_tech' => 'Responsable technique');

        //On fusionne le précedent tableau avec celui ci car le premier change en fonction du nombre de tache affiche lors d'un rapport
        $mandatory_fields = array_merge($mandatory_fields, $array_fields);
        
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
            } else {                // Mode Mise à jour
                Session::addMessageAfterRedirect(sprintf("Les champs obligatoires ne sont pas correctement d&eacutefinis (%s). Le formulaire a &eacutet&eacute r&eacuteinitailis&eacute.", implode(', ', $msg)), false, ERROR, true);
            }
            return false;
        }
        return true;
    }

    /**
     * Actions à réaliser après l'ajout d'un item dans la base 
     *
     */
    function post_addItem() {
        global $DB, $CFG_GLPI;

        // Manage add from template
        if (isset($this->input["_oldID"])) {
            Infocom::cloneItem($this->getType(), $this->input["_oldID"], $this->fields['id']);
            Contract_Item::cloneItem($this->getType(), $this->input["_oldID"], $this->fields['id']);
            Document_Item::cloneItem($this->getType(), $this->input["_oldID"], $this->fields['id']);
        }

        if (isset($this->input['_itemtype']) && isset($this->input['_items_id'])) {
            $report_item = new PluginRtntestalexReport_Item();
            $tmp['plugin_rtntestalex_reports_id'] = $this->getID();
            $tmp['itemtype'] = $this->input['_itemtype'];
            $tmp['items_id'] = $this->input['_items_id'];
            $report_item->add($tmp);
        }

        //On stock le nombre de lignes qui correspond au nombre de taches presents dans le formulaire d'ajout d'un rapport
        $compteur = $this->input['IndexTable'];
        for ($i = 1; $i <= $compteur; $i++) {
            //On check si la tache est passe à OUI 
            if ($this->input['is_realized_' . $i] == true) {
                //On check si le champ bandes est vide ou pas 
                //Si il est vide on lui assigne comme valeur par défaut N/A
                if (empty($this->input['cartridgeslist_' . $i])) {
                    $bandes = "N/A";
                } else {    
                    $bandes = exportArrayToDB($this->input['cartridgeslist_' . $i]);
                }
                $query = "INSERT INTO `glpi_plugin_rtntestalex_reports_taskslinks` (plugin_rtntestalex_reports_id,
        plugin_rtntestalex_tasks_id,
        plugin_rtntestalex_states_id,
        is_realized,
        cartridgeslist,
        date_checktask,
        comment
        ) VALUES (" . $this->getID() . ", " . $this->input['idtask_' . $i] . "," . $this->input['state_type_' . $i] . "," . $this->input['is_realized_' . $i] . ", '" . $bandes . "', '" . $this->input['date_checktask_' . $i] . "', '" . $this->input['comment_' . $i] . "')";

            $DB->query($query) or die($DB->error());
            }
        }
    }

    /**
     * Actions executés après la modification d'un item dans la base
     * @param $history on stock s'il y a un changement par défaut 1
     * @return rien 
     */
    function post_updateItem($history = 1) {
        global $DB;

        //On stock le nombre de lignes qui correspond au nombre de taches presents dans le formulaire d'ajout d'un rapport
        $compteur = $this->input['IndexTable'];
        for ($i = 1; $i <= $compteur; $i++) {
            //On check si la tache est passe à OUI 
            if ($this->input['is_realized_' . $i] == true) {
                //On check si le champ bandes est vide ou pas 
                //Si il est vide on lui assigne comme valeur par défaut N/A
                if (empty($this->input['cartridgeslist_' . $i])) {
                    $bandes = "N/A";
                } else {
                     $bandes = exportArrayToDB($this->input['cartridgeslist_' . $i]);
                }
                $query = "UPDATE `glpi_plugin_rtntestalex_reports_taskslinks` 
        SET cartridgeslist = '" . $bandes . "',
        comment = '" . $this->input['comment_' . $i] . "'
        WHERE plugin_rtntestalex_reports_id = " . $this->input['ID'] . " AND plugin_rtntestalex_tasks_id = " . $this->input['idtask_' . $i];
                $DB->query($query) or die($DB->error());
            }
        }

        //On va modifier la date de modif lorsqu'il y aura une modification du rapport pour garder une trace dans l'historique
        $query = "UPDATE `glpi_plugin_rtntestalex_reports` SET date_mod = '" . date("Y-m-d H:i:s") . "' 
        WHERE id = " . $this->input['ID'] . "";
        $DB->query($query) or die($DB->error());
        $changes[0] = 0;
        $changes[2] = "Modification du rapport"; //LIBELLE A MODIFIER
        $changes[1] = "";
        Log::history($this->input['ID'], 'PluginRtntestalexReport', $changes, 0, Log::HISTORY_LOG_SIMPLE_MESSAGE);

        // To avoid log out and login when rights change (very useful in debug mode)
        if (isset($_SESSION['glpiactiveprofile']['id']) && $_SESSION['glpiactiveprofile']['id'] == $this->input['id']) {
            if (in_array('cartridgeslist', $this->updates)) {
                $_SESSION['glpiactiveprofile']['cartridgeslist'] = importArrayFromDB($this->input['cartridgeslist']);
            }
        }
    }

    /**
     * Actions à réaliser avant la modification d'un item dans la base
     * @param $input les données utilisées pour la modfication d'un item
     * @return Envoi les données modifiés sous forme d'un tableau
     *
     * */
    function prepareInputForUpdate($input) {

        //On test si le champ bande est vide ou si bien un tableau que l'on renvoi
        if ((!isset($input["cartridgeslist"])) || (!is_array($input["cartridgeslist"]))) {
            
        } else {
            //On converti le tableau de bande en chaîne de caractère
            $input["cartridgeslist"] = exportArrayToDB($input["cartridgeslist"]);
        }

        if (!$this->checkMandatoryFields($input, "update")) {
            return false;
        }
        return $input;
    }

    /**
     * Actions à réaliser avant l'ajout d'un item dans la base
     * @param $input les données utilisées pour l'ajout d'un item
     * @return Envoi les données modifiés sous forme d'un tableau
     */
    function prepareInputForAdd($input) {
        // On vérifie qu'il y a au moins une tache de définit à Oui

        $compteur = $this->input['IndexTable'];
        $i = 1;

        do {
            //On test si la tâche est bien à Oui
            if ($this->input['is_realized_' . $i] == 1) {
                $test = true;
                break;
            } else {
                $test = false;
            }
            $i++;
            //On boucle tant qu'une tache n'a pas ete passé à OUI ou qu'on l'on est pas checke toutes les taches presentes dans le rapport
        } while ($i <= $compteur && $test == false);
        if ($test == false) {
            Session::addMessageAfterRedirect("Aucune tâche renseignée.", false, ERROR, true);       // LIBELLE A MODIFIER
            return false;
        }
        
        if (!$this->checkMandatoryFields($input, "add")) {
            return false;
        }

        return $input;
    }

    /**
     * Supprime les droits inutiles du helpdesk
     * */
    function cleanProfile() {
        // decode array
        if (isset($this->fields["cartridgeslist"]) && !is_array($this->fields["cartridgeslist"])) {
            $this->fields["cartridgeslist"] = importArrayFromDB($this->fields["cartridgeslist"]);
        }

        // Empty/NULL case
        if (!isset($this->fields["cartridgeslist"]) || !is_array($this->fields["cartridgeslist"])) {
            $this->fields["cartridgeslist"] = array();
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
        $tab['common'] = __s('Reports', 'rtntestalex'); //LIBELLE A MODIFIER

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
        $tab[3]['field'] = 'date';
        $tab[3]['name'] = __('Date');
        $tab[3]['massiveaction'] = false; // implicit field is id
        $tab[3]['injectable'] = false;

        $tab[4]['table'] = $this->getTable();
        $tab[4]['field'] = 'users_id_tech';
        $tab[4]['name'] = "Responsable Technique";
        $tab[4]['datatype'] = 'itemlink';
        $tab[4]['itemlink_type'] = $this->getType();
        $tab[4]['massiveaction'] = false; // implicit key==1
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
        $table = getTableForItemType(__CLASS__);

        if (!TableExists($table)) {
            $query = "CREATE TABLE IF NOT EXISTS `$table` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `entities_id` int(11) NOT NULL DEFAULT '0',
      `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
      `users_id_tech` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
      `comment` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL,
      `date` datetime DEFAULT NULL,
      `date_mod` datetime DEFAULT NULL,
      `is_global` tinyint(1) NOT NULL DEFAULT '0',
      `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
      PRIMARY KEY (`id`),
      KEY `name` (`name`),
      KEY `entities_id` (`entities_id`),
      KEY `is_deleted` (`is_deleted`),
      KEY `is_global` (`is_global`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;";
            $DB->query($query) or die("Error adding table $table");

            // Table de liaison
            $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_rtntestalex_reports_taskslinks` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `plugin_rtntestalex_reports_id` int(11) NOT NULL DEFAULT '0',
      `plugin_rtntestalex_tasks_id` int(11) NOT NULL DEFAULT '0',
      `plugin_rtntestalex_states_id` int(11) NOT NULL DEFAULT '0',
      `is_realized` tinyint(1) NOT NULL DEFAULT '0',
      `cartridgeslist` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL,
      `date_checktask` datetime DEFAULT NULL,
      `comment` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL,
      PRIMARY KEY (`id`),
      KEY `plugin_rtntestalex_reports_id` (`plugin_rtntestalex_reports_id`),
      KEY `plugin_rtntestalex_tasks_id` (`plugin_rtntestalex_tasks_id`),
      KEY `plugin_rtntestalex_states_id` (`plugin_rtntestalex_states_id`),
      KEY `is_realized` (`is_realized`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;";
            $DB->query($query) or die("Error adding table glpi_plugin_rtntestalex_reports_taskslinks");
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

    static function uninstall() {
        global $DB;

        foreach (array('Notepad', 'DisplayPreference', 'Contract_Item', 'Infocom', 'Fieldblacklist', 'Document_Item', 'Bookmark', 'Log') as $itemtype) {
            $item = new $itemtype();
            $item->deleteByCriteria(array('itemtype' => __CLASS__));
        }

        $plugin = new Plugin();
        if ($plugin->isActivated('datainjection') && class_exists('PluginDatainjectionModel')) {
            PluginDatainjectionModel::clean(array('itemtype' => __CLASS__));
        }

        if ($plugin->isInstalled('customfields') && $plugin->isActivated('customfields')) {
            PluginCustomfieldsItemtype::unregisterItemtype('PluginRtntestalexReport');
        }

        $table = getTableForItemType(__CLASS__);
        $DB->query("DROP TABLE IF EXISTS `$table`");

        // Suppression de la table de liaison
        $DB->query("DROP TABLE IF EXISTS `glpi_plugin_rtntestalex_reports_taskslinks`");
    }

    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
        global $LANG;

        if (in_array(get_class($item), PluginRtntestalexReport_Item::getClasses()) || get_class($item) == 'Profile') {
            return __s('Report', 'rtntestalex');
        } elseif (get_class($item) == __CLASS__) {
            return __s('Report', 'rtntestalex');
        }
        return '';
    }

    /**
     *  Show tab content for a report item
     * 
     * @param CommonGLPI $item
     * @param number $tabnum
     * @param number $withtemplate
     */
    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
        $self = new self();
        if ($item->getType() == 'PluginRtntestalexReport') {
            $self->showtotal($item->getField('id'));
        }
        return true;
    }

    /**
     * Type than could be linked to a Rack
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
     * Add menu entries the plugin needs to show
     * 
     * @return array
     */
    static function getMenuContent() {
        global $CFG_GLPI;

        $menu = array();
        $menu['title'] = __s('Report', 'rtntestalex'); //LIBELLE A MODIFIER
        $menu['page'] = self::getSearchURL(false);
        $menu['links']['search'] = self::getSearchURL(false);
        if (self::canCreate()) {
            $menu['links']['add'] = '/front/setup.templates.php?itemtype=PluginRtntestalexReport&add=1';
        }
        return $menu;
    }

    /**
     * Actions done when item is deleted from the database
     *
     * @return nothing
     * */
    function cleanDBonPurge() {
        $link = new PluginRtntestalexReport_Item();
        $link->cleanDBonItemDelete($this->getType(), $this->getID());
    }

    /**
     * Delete an item in the database. 
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
        $deleteSuccessful = parent::delete($input, $force, $history);
        if ($deleteSuccessful != false) {
            if ($force == 1) {
                $notepad = new Notepad();
                $notepad->deleteByCriteria(array('itemtype' => 'PluginRtntestalexReport',
                    'items_id' => $input['id']));
            }
        }
        return $deleteSuccessful;
    }

}
?>  
