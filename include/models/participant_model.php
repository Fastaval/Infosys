<?php
/**
 * Copyright (C) 2009-2012 Peter Lind
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/gpl.html>.
 *
 * PHP version 5
 *
 * @category  Infosys
 * @package   Models
 * @author    Peter Lind <peter.e.lind@gmail.com>
 * @copyright 2009-2012 Peter Lind
 * @license   http://www.gnu.org/licenses/gpl.html GPL 3
 * @link      http://www.github.com/Fake51/Infosys
 */

/**
 * handles all data fetching for the participant MVC
 *
 * @category Infosys
 * @package  Models
 * @author   Peter Lind <peter.e.lind@gmail.com>
 * @license  http://www.gnu.org/licenses/gpl.html GPL 3
 * @link     http://www.github.com/Fake51/Infosys
 */
class ParticipantModel extends Model
{
    /**
     * loads a participant given correct hash
     *
     * @param string $hash - unique hash to identify participant by
     * @access public
     * @return object|bool
     */
    public function getParticipantFromHash($hash)
    {
        $deltager = $this->createEntity('Deltagere');
        $select = $deltager->getSelect();
        $select->setWhere('password','=',$hash);
        return $deltager->findBySelect($select);
    }

    /**
     * method docblock
     *
     * @param string $query Wildcard term for searching
     *
     * @access public
     * @return array
     */
    public function createWildcardSearchBase($query)
    {
        $session                = $this->dic->get('Session');
        $search                 = $session->search ? $session->search : array();
        $search['wildcardhash'] = md5($query);

        $search['ids'] = array();

        if ($result = $this->miniWildCardSearch(explode(' ', $query))) {
            $search['ids'] = array_map(create_function('$a', 'return $a->id;'), $result);
        }

        $session->search = $search;

        return $search['ids'];
    }

    /**
     * extracts ids and saves them to session so
     * future searches will use the same id set as base
     *
     * @param array $participants Array of participant objects
     *
     * @access public
     * @return void
     */
    public function setSearchBaseIds(array $participants)
    {
        $search = array('ids' => array());
        foreach ($participants as $participant) {
            if ($participant->id) {
                $search['ids'][] = $participant->id;
            }
        }

        $session         = $this->dic->get('Session');
        $session->search = $search;
    }

    /**
     * returns
     *
     * @access public
     * @return array
     */
    public function getSavedSearchResult()
    {
        $session = $this->dic->get('Session');
        $search  = $session->search;

        if (empty($search['ids']) || !is_array($search['ids'])) {
            return array();
        }

        $select = $this->createEntity('Deltagere')->getSelect()
            ->setWhere('id', 'IN', $search['ids']);

        return $this->createEntity('Deltagere')->findBySelectMany($select);
    }

    /**
     * fetches a single deltager object
     *
     * @param int $id Id of deltager to find
     *
     * @access public
     * @return bool|object
     */
    public function findDeltager($id) {
        $temp_id = EANToNumber($id) ? EANToNumber($id) : $id;

        return $this->createEntity('Deltagere')->findById($temp_id);
    }

    /**
     * fetches a single deltager object
     * wrapper for findDeltager
     *
     * @param int $id Id of deltager to find
     *
     * @access public
     * @return bool|object
     */
    public function findParticipant($id) {
        return $this->findDeltager($id);
    }

    /**
     * fetches data for a single deltager
     *
     * @param object $deltager - deltager object
     * @access public
     * @return bool|array
     */
    public function findDeltagerInfo($deltager)
    {
        if (!is_object($deltager) || !$deltager->isLoaded()) {
            return false;
        }

        $info = array();
        $info['pladser']         = (($results = $deltager->getPladser()) ? $results : null);
        $info['tilmeldinger']    = (($results = $deltager->getTilmeldinger()) ? $results : null);
        $info['gdstilmeldinger'] = (($results = $deltager->getGDSTilmeldinger()) ? $results : null);
        $info['wear']            = (($results = $deltager->getWear()) ? $results : null);
        $info['gds']             = (($results = $deltager->getGDSVagter()) ? $results : null);
        $info['mad']             = (($results = $deltager->getMadtider()) ? $results : null);
        $info['indgang']         = (($results = $deltager->getIndgang()) ? $results : null);
        $info['brugerkategori']  = (($results = $deltager->getBrugerKategori()) ?  $results : null);
        return $info;
    }

    /**
     * mini wild card search, only looks for a couple of fields
     *
     * @param array $terms
     * @access public
     * @return array
     */
    public function miniWildCardSearch($terms, $exclude_phone = false) {
        if (!is_array($terms)) {
            return array();
        }

        $select = $this->createEntity('Deltagere')->getSelect();
        foreach ($terms as $term) {
            if (0 != intval($term)) {
                if (!$exclude_phone) {
                    $select->setWhereOr('mobiltlf', 'like', "%{$term}%");
                }

                $id = EANToNumber($term) ? EANToNumber($term) : intval($term);

                $select->setWhereOr('id', '=', $id);

            } else {
                $select->setWhereOr('fornavn', 'like', "%{$term}%");
                $select->setWhereOr('email', 'like', "%{$term}%");
                $select->setWhereOr('efternavn', 'like', "%{$term}%");
                $select->setWhereOr('ungdomsskole', 'like', "%{$term}%");
                $select->setWhereOr('scenarie', 'like', "%{$term}%");
                $select->setWhereOr('arbejdsomraade', 'like', "%{$term}%");
                $select->setWhereOr('skills', 'like', "%{$term}%");
                $select->setWhereOr('deltager_note', 'like', "%{$term}%");
            }
        }

        $result       = $this->createEntity('Deltagere')->findBySelectMany($select);
        $participants = $full_matches = array();

        foreach ($result as $participant) {
            $name       = $participant->fornavn . ' ' . $participant->efternavn;
            $full_match = true;

            foreach ($terms as $term) {
                if ($term && mb_stripos($name, $term) === false) {
                    $full_match = false;
                }

            }

            if ($full_match) {
                $full_matches[] = $participant;
            }

            $participants[] = $participant;

        }

        return $full_matches ? $full_matches : $participants;
    }

    /**
     * returns all deltagere
     *
     * @access public
     * @return bool|array
     */
    public function findAll()
    {
        return $this->createEntity('Deltagere')->findAll();
    }

    /**
     * searches through all participants, given search and sort params
     *
     * @param RequestVars $search_vars search variables
     *
     * @access public
     * @return bool|array
     */
    public function searchParticipants(RequestVars $search_vars)
    {
        if (empty($search_vars)) {
            return $this->createEntity('Deltagere')->findAll();
        }

        $i       = 0;
        $array   = array();
        $select  = $this->createEntity('Deltagere')->getSelect();
        $bk_join = false;
        $du_join = false;
        $da_join = false;
        $logic   = $search_vars->logic == 'and' ? true : false;

        foreach ($search_vars->deltager_search as $dvar => $dval) {
            if (!is_array($dval) && trim($dval) == '') {
                continue;
            }

            $done = false;

            switch ($dvar) {
            case 'brugerkategori_id':
                $select->setTableWhere('brugerkategorier.id', 'deltagere.brugerkategori_id');
                $select->setFrom('brugerkategorier');
                $select->setWhere('brugerkategorier.id', '=', trim($dval));
                $bk_join = true;
                $done    = true;
                break;
            case 'tlf':
            case 'mobiltlf':
            case 'postnummer':
                $match = 'LIKE';
                $dval = "%" . trim($dval) . "%";
                break;
            case 'lang':
                foreach ($dval as $dvar => $dval) {
                    (($logic) ? $select->setWhere('sprog','LIKE',"%" . trim($dval) . "%") : $select->setWhereOr('sprog','LIKE',"%" . trim($dval) . "%"));
                }
                $done = true;
                break;
            case 'adresse':
                $select->setWhereParens(false, array('deltagere.adresse1 LIKE' => "%" . trim($dval) . "%", 'deltagere.adresse2 LIKE' => "%" . trim($dval) . "%"), $logic);
                $done = true;
                break;
            case 'id':
                $dval = EANToNumber($dval) ? EANToNumber($dval) : intval($dval);
                $match = '=';
                break;
            default:
                if (is_numeric($dval)) {
                    $match = '=';
                    $dval = trim($dval);
                } else {
                    $match ='LIKE';
                    $dval = "%" . trim($dval) . "%";
                }
            }

            if ($done) {
                continue;
            }

            if ($logic) {
                $select->setWhere('deltagere.' . $dvar, $match, $dval);
            } else {
                $select->setWhereOr('deltagere.' . $dvar, $match, $dval);
            }

        }

        if (isset($search_vars->mad_search)) {
            $this->handleExtraChoices($select, $search_vars->mad_search, $logic, 'DeltagereMadtider', 'madtid_id');
        }

        if (isset($search_vars->indgang_search)) {
            $this->handleExtraChoices($select, $search_vars->indgang_search, $logic, 'DeltagereIndgang', 'indgang_id');
        }

        $select->setField('distinct deltagere.*', false);

        if ($search_vars->search_combination) {
            $session = $this->dic->get('Session');

            if ($session->search && !empty($session->search['ids']) && is_array($session->search['ids'])) {

                if ($search_vars->search_combination === 'intersection') {
                    $select->setWhere('deltagere.id', 'IN', $session->search['ids']);
                }

                if ($search_vars->search_combination === 'union') {
                    $select->setWhereOr('deltagere.id', 'IN', $session->search['ids']);
                }

                if ($search_vars->search_combination === 'difference') {
                    $select->setWhere('deltagere.id', 'NOT IN', $session->search['ids']);
                }

            }

        }

        return $this->createEntity('Deltagere')->findBySelectMany($select);
    }

    /**
     * adds clauses for food and entry
     * extracted from main query
     *
     * @param object $select
     * @param array  $post        - POST data
     * @param string $entity
     * @param string $foreign_key
     *
     * @access protected
     * @return void
     */
    protected function handleExtraChoices($select, array $post, $logic, $entity, $foreign_key)
    {
        $in = array();
        $not_in = array();
        foreach ($post as $var => $val)
        {
            if (trim($val) == '')
            {
                continue;
            }
            list($throw_away, $id) = explode('_',$var);
            if ($id)
            {
                (($val == 'ja') ? $in[] = intval($id) : $not_in[] = intval($id));
            }
        }
        if (!empty($in) || !empty($not_in))
        {
            $this->addExtraClauses($select, $logic, $in, $not_in, $entity, $foreign_key);
        }
    }

    /**
     * adds clauses for food and entry
     * extracted from main query
     *
     * @param object $select
     * @param bool $logic
     * @param array $in
     * @param array $not_in
     * @param string $entity
     * @param string $foreign_key
     *
     * @access protected
     * @return void
     */
    protected function addExtraClauses($select, $logic, $in, $not_in, $entity, $foreign_key)
    {
        if ($logic)
        {
            foreach ($in as $i)
            {
                $i_select = $this->createEntity($entity)->getSelect();
                $i_select->setField('deltager_id');
                $i_select->setWhere($foreign_key,'=',$i);
                $select->setWhere("deltagere.id", 'IN',$i_select);
            }
            if (!empty($not_in))
            {
                $i_select = $this->createEntity($entity)->getSelect();
                $i_select->setField('deltager_id');
                $i_select->setWhere($foreign_key,'in',$not_in);
                $select->setWhere("deltagere.id", 'NOT IN',$i_select);
            }
        }
        else
        {
            if (!empty($in))
            {
                $i_select = $this->createEntity($entity)->getSelect();
                $i_select->setField('deltager_id');
                $i_select->setWhere($foreign_key,'in',$in);
                $select->setWhere("deltagere.id", 'IN',$i_select);
            }
            foreach ($not_in as $i)
            {
                $i_select = $this->createEntity($entity)->getSelect();
                $i_select->setField('deltager_id');
                $i_select->setWhere($foreign_key,'=',$i);
                $select->setWhereOr("deltagere.id", 'NOT IN',$i_select);
            }
        }
    }


    /**
     * returns all deltagere
     *
     * @access public
     * @return array
     */
    public function findSortedByField($field, $direction = 'asc')
    {
        $direction = ((in_array(strtolower($direction), array('asc','desc'))) ? $direction : 'asc');
        $select = $this->createEntity('Deltagere')->getSelect();
        $select->setOrder($field, $direction);
        return $this->createEntity('Deltagere')->findBySelectMany($select);
    }

    /**
     * returns all bruger kategorier
     *
     * @access public
     * @return bool|array
     */
    public function getAllBrugerKategorier()
    {
        return $this->createEntity('BrugerKategorier')->findAll();
    }

    /**
     * returns all bruger kategorier
     *
     * @access public
     * @return bool|array
     */
    public function getAllGDS()
    {
        return $this->createEntity('GDS')->findAll();
    }

    /**
     * returns all gds categories
     *
     * @access public
     * @return bool|array
     */
    public function getAllGDSCategories()
    {
        return $this->createEntity('GDSCategory')->findAll();
    }

    /**
     * returns all bruger kategorier
     *
     * @access public
     * @return bool|array
     */
    public function getAllIndgang()
    {
        return $this->createEntity('Indgang')->findAll();
    }


    /**
     * returns array of all food options
     *
     * @access public
     * @return array
     */
    public function getAllMad()
    {
        return $this->createEntity('Mad')->findAll();
    }

    /**
     * returns array of all days with a food option
     *
     * @access public
     * @return array
     */
    public function getAllFoodDays()
    {
        $DB = $this->db;
        $query = "SELECT DISTINCT dato FROM madtider ORDER BY dato";
        $answer = array();
        if ($results = $DB->query($query))
        {
            foreach ($results as $row)
            {
                $answer[] = $row['dato'];
            }
        }
        return $answer;
    }

    /**
     * returns array of all wear options
     *
     * @access public
     * @return array
     */
    public function getAllWear()
    {
        return $this->createEntity('Wear')->findAll();
    }

    /**
     * returns array of all wear options
     *
     * @access public
     * @return array
     */
    public function getAllAktiviteter()
    {
        return $this->createEntity('Aktiviteter')->findAll();
    }

    /**
     * updates a deltager, setting fields dependent upon POST vars
     *
     * @param object $deltager - the deltager entity to update
     * @param array $post - array of updated values to set
     *
     * @access public
     * @return bool
     */
    public function updateDeltager($deltager, $post)
    {
        if (!is_object($deltager) || !$deltager->isLoaded()) {
            return false;
        }

        $fields = $deltager->getColumns();

        $this->factory('IdTemplate')->cleanIdTemplateParticipantCache($deltager);

        foreach ($post as $key => $value) {
            if (in_array($key, $fields) && $key != 'karma') {
                $deltager->$key = $value;
            }
        }

        if (isset($post['lang']) && is_array($post['lang'])) {
            $deltager->setSprog($post['lang']);
        }

        if (isset($post['adresse'])) {
            if (preg_match('/^([^,]+),\s*(.*)$/', $post['adresse'], $matches)) {
                $deltager->adresse1 = $matches[1];
                $deltager->adresse2 = $matches[2];
            } else {
                $deltager->adresse1 = $post['adresse'];
                $deltager->adresse2 = '';
            }
        }

        if (isset($post['navn'])) {
            $nameparts = explode(' ',$post['navn']);
            $deltager->efternavn = array_pop($nameparts);
            $deltager->fornavn   = implode(' ',$nameparts);
        }

        if ($parsed = strtotime($deltager->birthdate)) {
            $deltager->birthdate = date('Y-m-d', $parsed);
        } else {
            $deltager->birthdate = '0000-00-00';
        }

        if (!$deltager->sovelokale_id) {
            $deltager->sovelokale_id = null;
        }

        return $deltager->update();
    }

    /**
     * wrapper function for Deltagere::getAvailableSprog()
     *
     * @access public
     * @return array
     */
    public function getAvailableSprog()
    {
        return $this->createEntity('Deltagere')->getAvailableSprog();
    }

    /**
     * creates a deltager, setting fields dependent upon POST vars
     *
     * @param RequestVars $post - array of updated values to set
     *
     * @access public
     * @return bool
     */
    public function createDeltager(RequestVars $post)
    {
        $deltager = $this->createEntity('Deltagere');
        $fields   = $deltager->getColumns();

        $ints = array(
            'alder',
            'betalt_beloeb',
            'deltaget_i_fastaval',
            'desired_activities',
            'desired_diy_shifts',
        );

        $regex_ints = array(
            'tlf',
            'mobiltlf',
            'brugerkategori_id',
        );

        $yes_no_fields = array(
            'international',
            'medbringer_mobil',
            'supergm',
            'tilmeld_scenarieskrivning',
            'arrangoer_naeste_aar',
            'supergds',
            'flere_gdsvagter',
            'sovesal',
            'sober_sleeping',
            'udeblevet',
            'rabat',
            'forfatter',
            'rig_onkel',
            'hemmelig_onkel',
            'oprydning_tirsdag',
            'ready_mandag',
            'ready_tirsdag',
            'may_contact',
            'interpreter',
            'annulled',
        );

        $data = $post->getRequestVarArray();
        foreach ($data['deltager'] as $key => $value) {
            if (in_array($key, $fields)) {
                $deltager->$key = $value;
            }
        }

        foreach ($ints as $field) {
            $deltager->$field = intval($deltager->$field);
        }

        foreach ($regex_ints as $field) {
            $deltager->$field = preg_replace('/[^0-9]/', '', $deltager->$field);
        }

        foreach ($yes_no_fields as $field) {
            if (!in_array(strtolower($deltager->$field), array('ja', 'nej'))) {
                $deltager->$field = 'nej';
            }
        }

        if (isset($post->lang) && is_array($post->lang)) {
            $deltager->setSprog($post->lang);
        }

        if (isset($post->sovesal)) {
            $deltager->sovesal = $post->sovesal;
        }

        if (isset($post->ungdomsskole)) {
            $deltager->ungdomsskole = $post->ungdomsskole;
        }

        if (isset($post->alea)) {
            if ($post->alea == 'Er medlem') {
                $deltager->er_alea = 'ja';
            } elseif ($post->alea == 'Vil være medlem') {
                $deltager->ny_alea = 'ja';
            }
        }

        if ($deltager->deltaget_i_fastaval == '') {
            $deltager->deltaget_i_fastaval = null;
        }

        $deltager->medical_note = $deltager->gcm_id = '';

        if (empty($deltager->birthdate) || !strtotime($deltager->birthdate)) {
            $deltager->birthdate = '0000-00-00 00:00:00';
        }

        $deltager->createPass();

        $deltager->created = $deltager->updated = $deltager->signed_up = date('Y-m-d H:i:s');

        try {
            $return = $deltager->insert();
            return (($return) ? $deltager : false);

        } catch (Exception $e) {
            if ($e instanceof FrameworkException) {
                $e->logException();
            }

            return false;
        }

    }


    /**
     * updates a deltagers indgang, mad and wear selections
     *
     * @param object $deltager - deltagere entity
     * @param RequestVars $post - POST vars
     *
     * @access public
     * @return bool
     */
    public function updateIMW($deltager, RequestVars $post)
    {
        if (!is_object($deltager) || !$deltager->isLoaded()) {
            return false;
        }

        $this->handleEntranceUpdate($deltager, $post);

        if ($this->getLoggedInUser()->hasRole('Infonaut') || $this->getLoggedInUser()->hasRole('admin')) {
            $this->handleFoodUpdate($deltager, $post);
        } else {
            $this->dic->get('Messages')->addError('Kun infonauter kan opdatere mad');
        }

        if ($this->getLoggedInUser()->hasRole('Infonaut') || $this->getLoggedInUser()->hasRole('admin')) {
            $this->handleWearUpdate($deltager, $post);
        } else {
            $this->dic->get('Messages')->addError('Kun infonauter kan opdatere wear');
        }

        return true;
    }

    protected function handleWearUpdate(Deltagere $deltager, RequestVars $post) {
        $query = "DELETE FROM deltagere_wear WHERE deltager_id = '{$deltager->id}'";
        $this->db->exec($query);

        if (!empty($post->wearpriser) && $post->wearantal && $post->wearsize) {
            $wearantal = $post->wearantal;
            $wearsize = $post->wearsize;
            foreach ($post->wearpriser as $wearpris) {
                $ent = $this->createEntity('DeltagereWear');
                $ent->deltager_id = $deltager->id;
                $ent->wearpris_id = $wearpris;
                $ent->antal = current($wearantal);
                $ent->size = current($wearsize);
                $ent->insert();
                next($wearantal);
                next($wearsize);
            }
        }
    }

    /**
     * takes care of updates to entrance info
     *
     * @param Deltagere   $deltager
     * @param RequestVars $post
     *
     * @access protected
     * @return void
     */
    protected function handleEntranceUpdate(Deltagere $deltager, RequestVars $post) {
        $query = "DELETE FROM deltagere_indgang WHERE deltager_id = '{$deltager->id}'";
        $this->db->exec($query);

        if (!empty($post->indgang)) {
            foreach ($post->indgang as $i) {
                if (intval($i)) {
                    $deltager->setIndgang($this->createEntity('Indgang')->findById($i));
                }
            }
        }
    }

    /**
     * takes care of updates to food info
     *
     * @param Deltagere   $deltager
     * @param RequestVars $post
     *
     * @access protected
     * @return void
     */
    protected function handleFoodUpdate(Deltagere $deltager, RequestVars $post) {
        $query = "DELETE FROM deltagere_madtider WHERE deltager_id = '{$deltager->id}'";
        $this->db->exec($query);

        if (!empty($post->madtider)) {
            foreach ($post->madtider as $mt) {
                if (intval($mt)) {
                    $deltager->setMad($this->createEntity('Madtider')->findById($mt));
                }
            }
        }
    }

    /**
     * updates a deltagers GDS aktiviteter
     *
     * @param object $deltager - deltagere entity
     * @param RequestVars $post - POST vars
     *
     * @access public
     * @return bool
     */
    public function updateGDS($deltager, RequestVars $post) {
        if (!is_object($deltager) || !$deltager->isLoaded()) {
            return false;
        }

        $query = "DELETE FROM deltagere_gdsvagter WHERE deltager_id = '{$deltager->id}'";
        $this->db->exec($query);

        if (!empty($post->gdsvagter)) {
            foreach ($post->gdsvagter as $i) {
                if (intval($i)) {
                    $deltager->setGDSVagt($this->createEntity('GDSVagter')->findById($i));
                }
            }
        }
        return true;
    }

    /**
     * updates a deltagers GDS aktiviteter
     *
     * @param object $deltager - deltagere entity
     * @param RequestVars $post - POST vars
     *
     * @access public
     * @return bool
     */
    public function updateGDSTilmeldinger(Deltagere $deltager, RequestVars $post) {
        if (!$deltager->isLoaded()) {
            return false;
        }

        $query = "DELETE FROM deltagere_gdstilmeldinger WHERE deltager_id = '{$deltager->id}'";
        $this->db->exec($query);

        if (!empty($post->period)) {
            foreach ($post->period as $key => $period) {
                if (!empty($post->gds[$key]) && ($gds = $this->createEntity('GDSCategory')->findById($post->gds[$key]))) {
                    $deltager->setGDSTilmelding($gds, $period);
                }
            }
        }

        return true;
    }

    /**
     * updates a deltagers aktiviteter
     *
     * @param object $deltager - deltagere entity
     * @param RequestVars $post - POST vars
     *
     * @access public
     * @return bool
     */
    public function updateAktiviteter($deltager, RequestVars $post)
    {
        if (!is_object($deltager) || !$deltager->isLoaded()) {
            return false;
        }

        $pladser = $deltager->getPladser();

        if ($post->hold_id && $post->type) {
            $merged = array();

            for ($i = 0; $i < count($post->hold_id); $i++) {
                $merged[] = array('hold_id' => $post->hold_id[$i], 'type' => $post->type[$i]);
            }

            if (!empty($pladser)) {
                foreach ($pladser as $plads) {
                    $delete = true;

                    for ($i = 0; $i < count($merged); $i++) {
                        if ($plads->hold_id == $merged[$i]['hold_id']) {
                            $delete = false;
                            $merged[$i] = null;
                        }
                    }

                    if ($delete) {
                        $plads->delete();
                    }

                }

            }

            if (!empty($merged)) {
                foreach ($merged as $new) {
                    if ($new) {
                        $deltager->setPlads($this->createEntity('Hold')->findById($new['hold_id']), $new['type']);
                    }
                }
            }
        } else {
            foreach ($pladser as $plads)
            {
                $plads->delete();
            }
        }

        return true;
    }


    /**
     * updates a deltagers aktivitets tilmeldinger
     *
     * @param object $deltager - deltagere entity
     * @param RequestVars $post - POST vars
     *
     * @access public
     * @return bool
     */
    public function updateTilmeldinger($deltager, RequestVars $post)
    {
        if (!is_object($deltager) || !$deltager->isLoaded()) {
            return false;
        }

        $tilmeldinger = $deltager->getTilmeldinger();

        if ($post->afvikling_id && $post->type && $post->prioritet) {
            $merged = array();

            for ($i = 0; $i < count($post->afvikling_id); $i++) {
                $merged[] = array('afvikling_id' => $post->afvikling_id[$i], 'type' => $post->type[$i], 'prioritet' => $post->prioritet[$i]);
            }

            if (!empty($tilmeldinger)) {
                foreach ($tilmeldinger as $tilmelding) {
                    $delete = true;

                    for ($i = 0; $i < count($merged); $i++) {
                        if ($tilmelding->afvikling_id == $merged[$i]['afvikling_id']  && $tilmelding->tilmeldingstype == $merged[$i]['type']) {
                            $delete = false;
                            $merged[$i] = null;

                        }

                    }

                    if ($delete) {
                        $tilmelding->delete();
                    }

                }

            }

            if (!empty($merged)) {
                foreach ($merged as $new) {
                    if ($new) {
                        $deltager->setAktivitetTilmelding($this->createEntity('Afviklinger')->findById($new['afvikling_id']), $new['prioritet'], $new['type']);
                    }

                }

            }

        } else {
            foreach ($tilmeldinger as $tilmelding) {
                $tilmelding->delete();
            }
        }

        return true;
    }


    /**
     * checks updates for indgang/mad, adds new entities, returns
     * array of entities that need to be deleted
     *
     * @param string $array_name
     * @param string $entity_name
     * @param string $field_name
     * @param RequestVars $post
     * @param array $entity_array
     * @param object $deltager
     *
     * @return array
     * @access protected
     */
    protected function indgangMadDeletes($array_name, $entity_name, $field_name, RequestVars $post, $entity_array, $deltager)
    {
        $deletes = array();
        if (array_key_exists($array_name, $post))
        {
            $entity_ids = array();
            foreach ($entity_array as $entity)
            {
                $entity_ids[] = $entity->id;
            }
            $to_delete = array_diff($entity_ids, $post->$array_name);
            if (!empty($to_delete))
            {
                $select = $this->createEntity($entity_name)->getSelect();
                $select->setWhere('deltager_id', '=', $deltager->id);
                $select->setWhere($field_name, 'in', $to_delete);
                $deletes = $this->createEntity($entity_name)->findBySelectMany($select);
            }
            $to_enter = array_diff($post->$array_name, $entity_ids);
            if (!empty($to_enter))
            {
                foreach ($to_enter as $val)
                {
                    $ent = $this->createEntity($entity_name);
                    $ent->deltager_id = $deltager->id;
                    $ent->$field_name = $val;
                    $ent->insert();
                }
            }
        }
        else
        {
            $select = $this->createEntity($entity_name)->getSelect();
            $select->setWhere('deltager_id', '=', $deltager->id);
            $deletes = $this->createEntity($entity_name)->findBySelectMany($select);
        }
        return $deletes;
    }


    /**
     * updates a deltagers note field
     *
     * @param object $deltager - deltager object to update
     * @param string $field - the specific field to update
     * @param RequestVars $post - updated value to set
     *
     * @access public
     * @return bool
     */
    public function updateDeltagerNote($deltager, $field, RequestVars $post)
    {
        if (preg_match("/deltager_note_(\w+)/", $field, $matches)) {
            $deltager->setNote($matches[1], $post->$field);
        } else{
            if (!in_array($field, array('deltager_note', 'admin_note', 'beskeder', 'paid_note', 'medical_note'))) {
                return false;
            }

            $deltager->$field = $post->$field;
        }
        return $deltager->update();
    }

    /**
     * returns an array of the fields in the deltagere table
     *
     * @access public
     * @return array
     */
    public function getDeltagerFields()
    {
        return $this->createEntity('Deltagere')->getColumns();
    }

    /**
     * returns an array of the fields in the deltagere table
     *
     * @access public
     * @return array
     */
    public function getDeltagerFieldsWithNames()
    {
        $deltager = $this->createEntity('Deltagere');
        $field_keys = $deltager->getColumns();
        $field_names = $deltager->getHumanReadableFieldNames();

        $fields = [];
        foreach($field_keys as $key) {
            $name = isset($field_names[$key]) ? $field_names[$key] : $key;
            $fields[$key] = $name;
        }
        return $fields;
    }

    
    /**
     * returns an array with all the GMs (signed up + assigned) for all the activities
     *
     * @access public
     * @return array
     */
    public function getGMList()
    {
        $activities = (($result = $this->createEntity('Aktiviteter')->findAll()) ? $result : array());
        $result = array();

        foreach ($activities as $activity) {
            if (!$activity->needsSpilleder()) {
                continue;
            }

            foreach ($activity->getAfviklinger() as $afvikling) {
                $result[$activity->id][$afvikling->id]['signups']  = $afvikling->getSignupGMs();
                $result[$activity->id][$afvikling->id]['assigned'] = $afvikling->getAssignedGMs();

            }
        }

        return $result;
    }

    /**
     * finds users signed up for a given schedule
     *
     * @param object $afvikling - Afviklinger entity
     * @access public
     * @return array
     */
    public function getSignupsForSchedule($afvikling)
    {
        if (!is_object($afvikling) || !$afvikling->isLoaded())
        {
            return array();
        }
        $tilmeldinger = $afvikling->getTilmeldinger();
        $ids = array();
        foreach ($tilmeldinger as $tilmelding)
        {
            $ids[] = $tilmelding->deltager_id;
        }
        $d = $this->createEntity('Deltagere');
        $select = $d->getSelect();
        $select->setWhere('id','in',$ids);
        return $d->findBySelectMany($select);
    }

    /**
     * gathers economic figures from participants
     *
     * @access public
     * @return array
     */
    public function participantFigures()
    {
        $figures = array(
            'Tilmelding total' => 0,
            'break',
            'Indgang'          => 0,
            'Mad'              => 0,
            'Wear'             => 0,
            'Aktiviteter'      => 0,
            'Onkler'           => 0,
            'Alea'             => 0,
            'break',
            'Reel total'       => 0,
            'break',
            'Forudbetalt'      => 0,
            'Difference'       => 0,
        );

        $deltagere = (($result = $this->createEntity('Deltagere')->findAll()) ? $result : array());

        foreach ($deltagere as $deltager) {
            $figures['Forudbetalt'] += $deltager->betalt_beloeb;
            if ($deltager->udeblevet == 'ja') {
                continue;
            }

            $figures['Indgang']          += $deltager->calcEntry();
            $figures['Mad']              += $deltager->calcFood();
            $figures['Wear']             += $deltager->calcWear();
            $figures['Aktiviteter']      += $deltager->calcActivities();
            $figures['Onkler']           += $deltager->calcRichBastard();
            $figures['Alea']             += $deltager->calcAlea();
            $figures['Reel total']       += $deltager->calcRealTotal();
            $figures['Tilmelding total'] += $deltager->original_price;
        }

        $figures['Difference'] = $figures['Reel total'] - $figures['Forudbetalt'];

        return $figures;
    }

    /**
     * finds gamers who bought a given type of food, given array of food ids
     *
     * @param array $madtider - array of Madtider ids
     * @access public
     * @return array
     */
    public function findGamersForMadtidId($madtider)
    {
        if (!is_array($madtider))
        {
            return array();
        }
        $deltagere = array();
        foreach ($madtider as $id)
        {
            if ($dm = $this->findEntity('Madtider',$id))
            {
                $result = $dm->getDeltagere();
                foreach ($result as &$res)
                {
                    $res->madtype = $dm;
                }
                $deltagere = array_merge($deltagere, $result);
            }
        }
        return $deltagere;
    }

    /**
     * finds participants given an array of ids
     *
     * @param array $ids - array of ints
     * @access public
     * @return array
     */
    public function getDeltagereUsingArray($ids)
    {
        $return = array();
        if (!is_array($ids))
        {
            return $return;
        }
        foreach ($ids as $id)
        {
            if ($d = $this->findEntity('Deltagere', $id))
            {
                $return[] = $d;
            }
        }
        return $return;
    }


    /**
     * finds the current karma average among users that signed up for stuff
     *
     * @access public
     * @return array
     */
    public function getKarmaAvg()
    {
        $karma = $this->buildKarma();

        $stats = $karma->calculate($this->createEntity('Deltagere')->findAll());

        return [
                'count' => count($stats),
                'avg'   => array_sum($stats) / count($stats),
                'max'   => max($stats),
                'min'   => min($stats),
               ];
    }

    public function getSMSLog()
    {
        return $this->createEntity('SMSLog');
    }

    /**
     * returns all rooms marked as sleeping room
     *
     * @access public
     * @return array
     */
    public function getSleepingRooms()
    {
        $ids = array();
        $query = <<<SQL
SELECT id
FROM lokaler
LEFT JOIN (SELECT sovelokale_id, count(id) sleepers
           FROM deltagere
           GROUP BY sovelokale_id)
    AS temp ON temp.sovelokale_id = lokaler.id
WHERE lokaler.sovelokale = 'ja'
    AND (temp.sleepers < lokaler.sovekapacitet
    OR temp.sovelokale_id IS NULL)
    AND lokaler.sovekapacitet > 0
SQL;
        foreach ($this->db->query($query) as $result)
        {
            $ids[] = $result['id'];
        }
        if (empty($ids)) return array();
        $select = $this->createEntity('Lokaler')->getSelect();
        $select->setWhere('id', 'in', $ids);
        return $this->createEntity('Lokaler')->findBySelectMany($select);
    }

    /**
     * returns budget details for all participants
     *
     * @access public
     * @return array
     */
    public function getDetailedBudget()
    {
        $query = <<<SQL
SELECT
    d.id,
    d.fornavn,
    d.efternavn,
    case when d.rabat = 'ja' then 225 else 0 end as arr_rabat,
    bk.navn AS brugerkategori,
    d.udeblevet,
    d.betalt_beloeb,
    case when i_p.pris is not null then i_p.pris else 0 end as partout,
    case when i_o.pris is not null then i_o.pris else 0 end as overnatning,
    case when i_d.pris is not null then i_d.pris else 0 end as dagsbilletter,
    case when w.pris is not null then w.pris else 0 end as wearbestillinger,
    case when a.pris is not null then a.pris else 0 end as aktivitetsbestillinger,
    case when (d.ny_alea = 'ja' OR d.er_alea = 'ja') AND i_p.pris is not null THEN 50 WHEN (d.ny_alea = 'ja' OR d.er_alea = 'ja') AND i_d.pris is not null THEN (i_d.pris / 40) * 10 else 0 END AS Alea_rabat,
    case when m.pris is not null then m.pris else 0 end as madbestillinger,
    case when d.hemmelig_onkel = 'ja' or d.rig_onkel = 'ja' then 300 else 0 end AS onkel,
    case when d.ny_alea = 'ja' then 10 else 0 end AS Alea_kontingent
FROM
    deltagere AS d
    LEFT JOIN (
        SELECT di.deltager_id, i.pris FROM deltagere_indgang AS di JOIN indgang AS i ON i.id = di.indgang_id WHERE i.type = 'Partout'
    ) AS i_p ON i_p.deltager_id = d.id
    LEFT JOIN (
        SELECT di.deltager_id, i.pris FROM deltagere_indgang AS di JOIN indgang AS i ON i.id = di.indgang_id WHERE i.type = 'Overnatningsgebyr'
    ) AS i_o ON i_o.deltager_id = d.id
    LEFT JOIN (
        SELECT di.deltager_id, sum(i.pris) AS pris FROM deltagere_indgang AS di JOIN indgang AS i ON i.id = di.indgang_id WHERE i.type LIKE 'Dagsbillet%' GROUP BY di.deltager_id
    ) AS i_d ON i_d.deltager_id = d.id
    LEFT JOIN (
        SELECT deltager_id, SUM(dw.antal * wp.pris) AS pris FROM deltagere_wear AS dw JOIN wearpriser AS wp ON wp.id = dw.wearpris_id GROUP BY deltager_id
    ) AS w ON w.deltager_id = d.id
    LEFT JOIN (
        SELECT p.deltager_id, SUM(case when p.type = 'spilleder' then -30 else ak.pris end) AS pris FROM pladser AS p JOIN hold AS h ON h.id = p.hold_id JOIN afviklinger AS af ON af.id = h.afvikling_id JOIN aktiviteter AS ak ON ak.id = af.aktivitet_id GROUP BY deltager_id
    ) AS a ON a.deltager_id = d.id
    LEFT JOIN (
        SELECT deltager_id, SUM(m.pris) AS pris FROM deltagere_madtider AS dmt JOIN madtider AS mt ON mt.id = dmt.madtid_id JOIN mad AS m ON m.id = mt.mad_id GROUP BY deltager_id
    ) AS m ON m.deltager_id = d.id

    JOIN brugerkategorier AS bk ON bk.id = d.brugerkategori_id
ORDER BY
    d.id ASC
SQL;
        return $this->db->query($query);
    }

    public function findFromNameOrID($query) {
        $return = array();
        if ($results = $this->miniWildCardSearch(explode(' ', $query), true)) {
            foreach ($results as $result) {
                $return[] = array(
                    'id' => $result->id,
                    'name' => $result->getName(),
                    'paid' => $result->betalt_beloeb,
                    'paid_note' => !$result->paid_note ? '' : e($result->paid_note),
                );
            }
        }
        return $return;
    }

    public function generateParticipantBarcodes(array $participants) {
        require_once 'PEAR.php';
        require_once 'Image/Barcode.php';

        $barcode = new Image_Barcode;

        foreach ($participants as $participant) {
            $img = @$barcode->draw(numberToEAN13($participant->id), 'ean13', 'png', false);
            $width = imagesx($img);
            $height = imagesy($img);
            $new_width = round($width * 1.7);
            $new_height = round(($height * 0.7) * 1.7);
            $resized = imagecreatetruecolor($new_width, $new_height);
            imagecopyresampled($resized, $img, 0, 0, 0, 0, $new_width, $new_height, $width, ($height * 0.7));
            imagepng($resized, PUBLIC_PATH . 'barcodes/' . md5($participant->id) . '.png');
        }
    }

    /**
     * generates an EAN8 barcode
     *
     * @param int $participant_id Id of participants
     *
     * @access public
     * @return string
     */
    public function generateSmallEan8Barcode($participant_id)
    {
        $hash = md5('EAN8Small_' . $participant_id);
        $filename = PUBLIC_PATH . 'barcodes/' . $hash . '.png';

        if (file_exists(PUBLIC_PATH . 'barcodes/' . $hash)) {
            return $filename;
        }

        if (!($participant = $this->createEntity('Deltagere')->findById($participant_id))) {
            throw new FrameworkException('No such participant');
        }

        require_once 'PEAR.php';
        require_once 'Image/Barcode.php';

        $barcode    = new Image_Barcode;
        $img        = $barcode->draw($participant->getEan8Number(), 'ean8', 'png', false);
        $width      = imagesx($img);
        $height     = imagesy($img);
        $new_width  = 536;
        $new_height = 74;
        $resized    = imagecreatetruecolor($new_width, $new_height);
        $border     = imagecreatetruecolor($new_width + 8, $new_height + 8);
        $white      = imagecolorallocate($border, 255, 255, 255);
        imagefill($border, 0, 0, $white);
        imagecopyresampled($resized, $img, 0, 0, 0, 0, $new_width, $new_height, $width, ($height * 0.7));
        imagecopyresampled($border, $resized, 4, 4, 0, 0, $new_width, $new_height, $new_width, $new_height);
        imagepng(imagerotate($border, 90, $white), $filename);

        return $filename;
    }

    /**
     * generates an EAN8 barcode
     *
     * @param int $participant_id Id of participants
     *
     * @access public
     * @return string
     */
    public function generateEan8SheetBarcode($participant_id)
    {
        $hash = md5('EAN8Sheet_' . $participant_id);
        $filename = PUBLIC_PATH . 'barcodes/' . $hash . '.png';

        if (file_exists($filename)) {
            return $filename;
        }

        if (!($participant = $this->createEntity('Deltagere')->findById($participant_id))) {
            throw new FrameworkException('No such participant');
        }

        require_once 'PEAR.php';
        require_once 'Image/Barcode.php';

        $barcode    = new Image_Barcode;
        $img        = @$barcode->draw($participant->getEan8Number(), 'ean8', 'png', false);
        $width      = imagesx($img);
        $height     = imagesy($img);
        $new_width  = 134;
        $new_height = 74;
        $resized    = imagecreatetruecolor($new_width, $new_height);
        $border     = imagecreatetruecolor($new_width + 8, $new_height + 8);
        $white      = imagecolorallocate($border, 255, 255, 255);
        imagefill($border, 0, 0, $white);
        imagecopyresampled($resized, $img, 0, 0, 0, 0, $new_width, $new_height, $width, ($height * 0.7));
        imagecopyresampled($border, $resized, 4, 4, 0, 0, $new_width, $new_height, $new_width, $new_height);
        imagepng($border, $filename);

        return $filename;
    }

    /**
     * generates an EAN8 barcode
     *
     * @param int $participant_id Id of participants
     *
     * @access public
     * @return string
     */
    public function generateEan8Barcode($participant_id)
    {
        $hash = md5('EAN8_' . $participant_id);
        $filename = PUBLIC_PATH . 'barcodes/' . $hash . '.png';
        if (file_exists(PUBLIC_PATH . 'barcodes/' . $hash)) {
            return $filename;
        }

        if (!($participant = $this->createEntity('Deltagere')->findById($participant_id))) {
            throw new FrameworkException('No such participant');
        }

        require_once 'PEAR.php';
        require_once 'Image/Barcode.php';

        $barcode    = new Image_Barcode;
        $img        = $barcode->draw($participant->getEan8Number(), 'ean8', 'png', false);
        $width      = imagesx($img);
        $height     = imagesy($img);
        $new_width  = round($width * 2);
        $new_height = round(($height * 0.7) * 1.7);
        $resized    = imagecreatetruecolor($new_width, $new_height);
        imagecopyresampled($resized, $img, 0, 0, 0, 0, $new_width, $new_height, $width, ($height * 0.7));
        imagepng($resized, $filename);

        return $filename;
    }

    /**
     * generates the image for the participants badges
     *
     * @param int $participant_id Id of participants
     *
     * @access public
     * @return string
     */
    public function generateParticipantBadge($participant_id)
    {
        $hash = md5('EAN8Badge_' . $participant_id);
        $filename = PUBLIC_PATH . 'barcodes/' . $hash . '.png';
        if (file_exists(PUBLIC_PATH . 'barcodes/' . $hash)) {
            return $filename;
        }

        if (!($participant = $this->createEntity('Deltagere')->findById($participant_id))) {
            throw new FrameworkException('No such participant');
        }

        $parts = explode(' ', $participant->fornavn);
        $participant->fornavn = implode(' ', array_map(create_function('$a', 'return ucfirst($a);'), $parts));

        $parts = explode(' ', $participant->efternavn);
        $participant->efternavn = implode(' ', array_map(create_function('$a', 'return ucfirst($a);'), $parts));

        $name = $participant->fornavn . ' ' . $participant->efternavn;
        $name = mb_strlen($name) > 25 ? $participant->fornavn . "\n" . $participant->efternavn : $name;

        $lineheight = 14;

        $bbox1        = imagettfbbox($lineheight, 0, "/usr/share/fonts/truetype/ttf-liberation/LiberationMono-Regular.ttf", $name);
        $bbox1_width  = abs($bbox1[2] - $bbox1[0]) + 8;
        $bbox1_height = abs($bbox1[7] - $bbox1[1]) + 8;
        $text1        = imagecreatetruecolor($bbox1_width, $bbox1_height);
        $white        = imagecolorallocatealpha($text1, 255, 255, 255, 30);
        imagefill($text1, 0, 0, $white);
        $black = imagecolorallocate($text1, 0, 0, 0);
        imagettftext($text1, $lineheight, 0, 4, $lineheight + 4, $black, "/usr/share/fonts/truetype/ttf-liberation/LiberationMono-Regular.ttf", $name);

        $bbox2        = imagettfbbox($lineheight, 0, "/usr/share/fonts/truetype/ttf-liberation/LiberationMono-Regular.ttf", "#" . $participant_id);
        $bbox2_width  = abs($bbox2[2] - $bbox2[0]) + 8;
        $bbox2_height = abs($bbox2[7] - $bbox2[1]) + 8;
        $text2        = imagecreatetruecolor($bbox2_width, $bbox2_height);
        $white        = imagecolorallocatealpha($text2, 255, 255, 255, 30);
        imagefill($text2, 0, 0, $white);
        $black = imagecolorallocate($text2, 0, 0, 0);
        imagettftext($text2, $lineheight, 0, 4, $bbox2_height - 4, $black, "/usr/share/fonts/truetype/ttf-liberation/LiberationMono-Regular.ttf", "#" . $participant_id);

        require_once 'PEAR.php';
        require_once 'Image/Barcode.php';

        $barcode    = new Image_Barcode;
        $img        = $barcode->draw($participant->getEan8Number(), 'ean8', 'png', false);
        $width      = imagesx($img);
        $height     = imagesy($img);
        $new_width  = round($width * 4.60);
        $new_height = round(($height * 0.7) * 3.5);
        $resized    = imagecreatetruecolor($new_width + 8, $new_height);
        $final      = imagecreatetruecolor($new_width + 10, $new_height + 2);
        $white      = imagecolorallocate($resized, 255, 255, 255);
        imagefill($resized, 0, 0, $white);
        imagecopyresampled($resized, $img, 4, 0, 0, 0, $new_width, $new_height, $width, ($height * 0.9));
        imagecopyresampled($resized, $text1, 20, 10, 0, 0, $bbox1_width, $bbox1_height, $bbox1_width, $bbox1_height);
        imagecopyresampled($resized, $text2, 20, $bbox1_height + 20, 0, 0, $bbox2_width, $bbox2_height, $bbox2_width, $bbox2_height);
        imagecopyresampled($final, $resized, 1, 1, 0, 0, $new_width + 8, $new_height, $new_width + 8, $new_height);

        imagepng($final, $filename);

        return $filename;
    }

    /**
     * marks a participant as checked in
     *
     * @param RequestVars $post
     *
     * @throws Exception
     * @access public
     * @return string
     */
    public function markCheckedin(RequestVars $post) {
        $deltager = $this->ajaxWearCrud($post);

        if ($deltager->checkin_time !== '0000-00-00 00:00:00') {
            throw new FrameworkException("<strong>Fejl:</strong> deltageren (ID: {$deltager->id}) er allerede tjekket ind.");
        }

        $deltager->checkin_time = date('Y-m-d H:i:s');
        $deltager->udeblevet = 'nej';

        $this->log("Deltager #{$deltager->id} blev checked ind af {$this->getLoggedInUser()->user}", 'Deltager', $this->getLoggedInUser());
        $text = e($deltager->getName()) . " (ID: {$deltager->id}) er nu tjekket ind - hvis det er en fejl, så tryk på Undo-knappen.";

        $bill = $deltager->calcRealTotal();

        $vouchers = $this->checkParticipantsForVouchers($deltager->id);

        if ($deltager->betalt_beloeb != $bill) {
            if ($deltager->betalt_beloeb < $bill) {
                $text .= '<br/><span style="background-color: #00f; color: #fff;">Deltageren skylder at betale <strong>' . ($bill - $deltager->betalt_beloeb) . ' kr.</strong><input type="hidden" class="previously-paid" value="' . intval($deltager->betalt_beloeb) . '"/></span>';

            } else {
                $text .= '<br/><span style="background-color: #00f; color: #fff;">Deltageren skal have <strong>' . ($deltager->betalt_beloeb - $bill) . ' kr. tilbage.</strong><input type="hidden" class="previously-paid" value="' . intval($deltager->betalt_beloeb) . '"/></span>';
            }

            $deltager->betalt_beloeb = $bill;

        }

        if ($vouchers) {
            $text .= '<br/><span style="background-color: #00f; color: #fff;">Deltageren skal have <strong>' . $vouchers . ' voucher(s).</strong></span>';

        }

        if ($deltager->sleepsOnPremises()) {
            $text .= '<br/><span style="background-color: aqua; color: #000;">Deltageren sover på Fastaval.</span>';

        }

        if ($deltager->getAge(new DateTime($this->config->get('con.start'))) < 18) {
            $text .= '<br/><span style="background-color: coral; color: #000;">Deltageren <strong>er under 18 år.</strong></span>';
        }

        $deltager->update();

        return $text;
    }

    /**
     * checks input vars for markFoodReceived/markFoodNotReceived
     * and returns the relevant participant
     *
     * @param RequestVars $post
     *
     * @throws Exception
     * @access protected
     * @return Deltagere
     */
    protected function ajaxWearCrud(RequestVars $post) {
        if (empty($post->user_id)) {
            throw new FrameworkException ("<strong>Fejl:</strong> bruger id mangler");
        }
        $temp_id = EANToNumber($post->user_id) ? EANToNumber($post->user_id) : $post->user_id;
        if (!($deltager = $this->createEntity('Deltagere')->findById($temp_id))) {
            throw new FrameworkException ("<strong>Fejl:</strong> ingen bruger med det id");
        }
        return $deltager;
    }

    /**
     * marks a participant-fooditem relationship
     * as undone (i.e. undoes it)
     *
     * @param RequestVars $post
     *
     * @throws Exception
     * @access public
     * @return string
     */
    public function undoCheckedin(RequestVars $post) {
        $deltager = $this->ajaxWearCrud($post);

        if (!strtotime($deltager->checkin_time)) {
            throw new FrameworkException("<strong>Fejl:</strong> deltageren (ID: {$deltager->id}) er ikke tjekket ind.");
        }

        if (strlen($post->previously_paid)) {
            $deltager->betalt_beloeb = intval($post->previously_paid);
        }

        $deltager->checkin_time = '0000-00-00 00:00:00';
        $deltager->update();
        $this->log("Deltager #{$deltager->id} blev markeret ikke-checked ind af {$this->getLoggedInUser()->user}", 'Deltager', $this->getLoggedInUser());
        return e($deltager->getName()) . " (ID: {$deltager->id}) er nu markeret ikke-tjekket-ind - hvis det er en fejl, så tryk på Undo-knappen.";
    }

    /**
     * fetches data for the log table to display
     *
     * @param RequestVars $get Object with get vars
     *
     * @access public
     * @return array
     */
    public function getAjaxListData(RequestVars $get)
    {
        $columns = array('id', 'navn');

        if ($get->no_search_term) {
            $session         = $this->dic->get('Session');
            $session->search = null;
        }

        if (isset($get->extra_columns)) {
            $extra_columns = explode(',', $get->extra_columns);

            $valid_columns = $this->getDisplayColumns();

            foreach ($extra_columns as $column) {
                if (isset($valid_columns[$column])) {
                    array_push($columns, $column);
                }

            }

        }

        $limit = "";

        if (isset($get->iDisplayStart) && $get->iDisplayLength != '-1') {
            $limit = "LIMIT " . intval($get->iDisplayStart) . ", " .
            intval($get->iDisplayLength);
        }

        $order = '';

        if (isset($get->iSortCol_0)) {
            $order = "ORDER BY ";

            for ($i = 0, $max = intval($get->iSortingCols); $i < $max ; $i++ ) {
                if ($get->{'bSortable_' . intval($get->{'iSortCol_' . $i})} == "true" ) {
                    $sort_dir = strtolower($get->{'sSortDir_' . $i}) == 'desc' ? 'DESC' : 'ASC';
                    $order .= $columns[intval($get->{'iSortCol_' . $i})] . "
                        " . $sort_dir . ", ";
                }
            }

            $order = substr_replace($order, "", -2);
            if ($order == "ORDER BY") {
                $order = "";
            }
        }

        $where = '';

        if ($get->sSearch != "") {
            $where = "WHERE (";
            for ($i = 0, $max = count($columns); $i < $max; $i++) {
                if ($columns[$i] == 'navn') {
                    $where .= 'fornavn LIKE ' . $this->db->sanitize('%' . $get->sSearch . '%') . " OR ";
                    $where .= 'efternavn LIKE ' . $this->db->sanitize('%' . $get->sSearch . '%') . " OR ";
                } else {
                    $where .= '`' . $columns[$i] . "` LIKE " . $this->db->sanitize('%' . $get->sSearch . '%') . " OR ";
                }
            }

            $where = substr_replace($where, "", -3);
            $where .= ')';
        }

        $session = $this->dic->get('Session');

        if ($session->search) {
            if (!empty($session->search['ids']) && is_array($session->search['ids'])) {
                $id_clause = 'deltagere.id IN (' . implode(', ', array_map(create_function('$a', 'return intval($a);'), $session->search['ids'])) . ')';

                $where .= ($where ? ' AND ' : ' WHERE ') . $id_clause;
            } else {
                return array(0, 0, array());
            }
        }

        foreach ($columns as $id => $column) {
            if ($column == 'navn') {
                $columns[$id] = 'CONCAT(fornavn, " ", efternavn) AS navn';
            } else {
                $columns[$id] = '`' . $column . '`';
            }
        }

        $query = "
            SELECT SQL_CALC_FOUND_ROWS " . implode(', ', $columns) . "
            FROM deltagere
            {$where}
            {$order}
            {$limit}
        ";

        $result = $this->db->query($query);

        foreach ($result as $id => $row) {
            $result[$id][0] = '<a href="' . $this->url('visdeltager', array('id' => $row['id'])) . '">' . e($row['id']) . '</a>';
            $result[$id][1] = '<a href="' . $this->url('visdeltager', array('id' => $row['id'])) . '">' . e($row['navn']) . '</a>';
        }

        $query = 'SELECT FOUND_ROWS() AS rows';

        $result_length = $this->db->query($query);
        $result_length = $result_length[0][0];

        return array($this->getRowCount(), $result_length, $result);
    }

    /**
     * returns the total number of records in the log
     *
     * @access public
     * @return int
     */
    public function getRowCount()
    {
        $session = $this->dic->get('Session');
        if ($session->search && !empty($session->search['ids']) && is_array($session->search['ids'])) {
            return count($session->search['ids']);
        }

        $total_length = $this->db->query('SELECT COUNT(*) FROM deltagere');
        return $total_length[0][0];
    }

    /**
     * makes a participant update based on a
     * jeditable request from the jquery plugin
     *
     * @param Deltagere   $participant Participant to update
     * @param RequestVars $post        Array of updated values to set
     *
     * @throws Exception
     * @access public
     * @return string
     */
    public function makeJeditableUpdate(Deltagere $participant, RequestVars $post)
    {
        if (empty($post->id) || !isset($post->value)) {
            throw new FrameworkException('Values are lacking');
        }

        $return_value = $post->value;

        $this->factory('IdTemplate')->cleanIdTemplateParticipantCache($participant);

        if (preg_match("/deltager_note_(\w+)/",$post->id, $matches)) {
            $participant->setNote($matches[1], $post->value);
        } else {
            switch ($post->id) {
            case 'birthdate':
                $parsed = strtotime($post->value);
                $participant->birthdate = $parsed ? date('Y-m-d', $parsed) : '0000-00-00';
                break;
            case 'address':
                $participant->adresse1 = $post->value;
                break;
            case 'name':
                $parts = explode(' ', $post->value);
                if (count($parts) > 1) {
                    $participant->efternavn = array_pop($parts);
                    $participant->fornavn   = implode(' ', $parts);
                } else {
                    $participant->fornavn   = $parts[0];
                    $participant->efternavn = '';
                }
                break;
            case 'brugerkategori_id':
                $category     = $this->createEntity('BrugerKategorier')->findById($post->value);
                $return_value = $category->navn;
                $participant->brugerkategori_id = $category->id;
                break;

            case 'participant-template':
                $this->updateParticipantIdTemplate($participant, intval($post->value));
                break;

            default:
                $participant->{$post->id} = $post->value;
            }
        }

        $this->log("Deltager #{$participant->id} fik opdateret " . $post->id . " af {$this->getLoggedInUser()->user}", 'Deltager', $this->getLoggedInUser());

        $participant->update();
        return $return_value;
    }

    /**
     * updates a participants template override
     *
     * @param Deltagere $participant Participant to update
     * @param int       $template_id Id of template to set, zero to remove
     *
     * @access protected
     * @return self
     */
    protected function updateParticipantIdTemplate(Deltagere $participant, $template_id)
    {
        if (!$template_id) {
            $query = '
DELETE FROM participantidtemplates WHERE participant_id = ?
';
            $args = [$participant->id];

        } else {
            $query = '
INSERT INTO participantidtemplates SET template_id = ?, participant_id = ? ON DUPLICATE KEY UPDATE template_id = ?
';
            $args = [$template_id, $participant->id, $template_id];

        }

        $this->db->exec($query, $args);

        return $this;
    }

    /**
     * method docblock
     *
     * @param
     *
     * @access public
     * @return void
     */
    public function getUserTypeArray()
    {
        $result = array();
        foreach ($this->createEntity('BrugerKategorier')->findAll() as $category) {
            $result[$category->id] = $category->navn;
        }

        return $result;
    }

    /**
     * returns set of columns that can be used to display
     * in datatables
     *
     * @access public
     * @return array
     */
    public function getDisplayColumns()
    {
        $filter_out = array('id', 'krigslive_bus', 'password', 'ovelokale_id', 'ny_alea', 'er_alea', 'adresse1', 'adresse2', 'alder', 'rabat', 'deltaget_i_fastaval', 'sovelokale_id');
        $columns    = array_diff($this->createEntity('Deltagere')->getColumns(), $filter_out);

        $readable_columns = $this->createEntity('Deltagere')->getHumanReadableFieldNames();
        $result           = array();

        foreach ($columns as $column) {
            if (isset($readable_columns[$column])) {
                $result[$column] = $readable_columns[$column];
            }

        }

        asort($result);

        return $result;
    }

    /**
     * removes a schedule from a participant
     *
     * @param RequestVars $post Post vars
     *
     * @throws Exception
     * @access public
     * @return void
     */
    public function removeParticipantSchedule($post)
    {
        if (!($participant = $this->createEntity('Deltagere')->findById($post->participant_id))) {
            throw new FrameworkException('Participant does not exist');
        }

        if (!($signup = $this->getSignup($participant, $post->schedule_id))) {
            throw new FrameworkException('Participant is not signed up for schedule');
        }

        $signup->delete();
    }

    /**
     * updates a participants signup
     *
     * @param RequestVars $post Post vars
     *
     * @throws Exception
     * @access public
     * @return DeltagereTilmeldinger
     */
    public function updateSchedule(RequestVars $post)
    {
        if (!($participant = $this->createEntity('Deltagere')->findById($post->participant_id))) {
            throw new FrameworkException('Kan ikke finde deltager');
        }

        if (!($old_signup = $this->getSignup($participant, $post->old_schedule_id))) {
            throw new FrameworkException('Kan ikke finde afvikling');
        }

        $priority = $post->role == 'spilleder' ? 1 : $post->priority;

        if ($post->schedule_id != $post->old_schedule_id) {
            if ($signup = $this->getSignup($participant, $post->schedule_id)) {
                throw new FrameworkException('Kan ikke flytte afvikling - deltageren er allerede tilmeldt afviklingen der flyttes til');
            }

            $query = 'DELETE FROM deltagere_tilmeldinger WHERE deltager_id = ? AND afvikling_id = ?';
            $this->db->exec($query, array($participant->id, $post->old_schedule_id));

            $query = 'INSERT INTO deltagere_tilmeldinger (deltager_id, afvikling_id, prioritet, tilmeldingstype) VALUES (?, ?, ?, ?)';
            $this->db->exec($query, array($participant->id, $post->schedule_id, $priority, $post->role));
        } else {
            $query = 'UPDATE deltagere_tilmeldinger SET prioritet = ?, tilmeldingstype = ? WHERE deltager_id = ? AND afvikling_id = ?';
            $this->db->exec($query, array($priority, $post->role, $participant->id, $post->schedule_id));
        }

        $this->log("Deltager #{$participant->id} fik rettet aktivitets-tilmeldinger af {$this->getLoggedInUser()->user}", 'Deltager', $this->getLoggedInUser());

        return $this->getSignup($participant, $post->schedule_id);
    }

    /**
     * returns a signup if it can be found
     *
     * @param Deltagere $participant Participant to find signup for
     * @param int       $schedule_id Schedule id
     *
     * @access protected
     * @return null|DeltagereTilmeldinger
     */
    protected function getSignup(Deltagere $participant, $schedule_id)
    {
        $signup = $this->createEntity('DeltagereTilmeldinger');
        return $signup->findBySelect($signup->getSelect()->setWhere('afvikling_id', '=', $schedule_id)->setWhere('deltager_id', '=', $participant->id));
    }

    /**
     * updates a block of signups
     *
     * @param RequestVars $post Object with post data
     *
     * @throws Exception
     * @access public
     * @return void
     */
    public function updateSignupPriorities(RequestVars $post)
    {
        if (!$post->participant_id || !($participant = $this->findEntity('Deltagere', $post->participant_id))) {
            throw new FrameworkException('No such participant');
        }

        if (empty($post->schedules)) {
            throw new FrameworkException("No schedules to order");
        }

        $i = 1;
        foreach ($post->schedules as $id) {
            $query = 'UPDATE deltagere_tilmeldinger SET prioritet = ? WHERE deltager_id = ? AND afvikling_id = ?';
            $this->db->exec($query, array($i, $participant->id, $id));

            $i++;
            $i = $i > 4 ? 4 : $i;
        }

        $this->log("Deltager #{$participant->id} fik opdateret aktivitets-tilmelding af {$this->getLoggedInUser()->user}", 'Deltager', $this->getLoggedInUser());
    }

    /**
     * sends sms'es to previously saved participants
     *
     * @param RequestVars $post Post vars from request
     *
     * @access public
     * @return void
     */
    public function sendSMSes(RequestVars $post)
    {
        $status = array();

        foreach ($this->getSavedSearchResult() as $receiver) {
            try {
                if ($receiver->apple_id) {
                    $result = $receiver->sendIosMessage($this->config->get('ios.certificate_path'), $post->sms_besked, 'Fastaval message');
                    $this->log('Sent iOS notification to participant #' . $receiver->id . '. Result: ' . $result, 'App', null);

                    $status[] = intval($result === IosPushMessage::SEND_SUCCESS);

                } elseif ($receiver->gcm_id) {
                    $result = $receiver->sendFirebaseMessage($this->config->get('firebase.server_api_key'), $post->sms_besked, 'Fastaval message');
                    $this->log('Sent android notification to participant #' . $receiver->id . '. Result: ' . $result, 'App', null);

                    $status[] = intval($result === FirebaseMessage::SEND_SUCCESS);

                } elseif (empty($post->app_only)) {
                    $status[] = intval(!!$receiver->sendSMS($this->dic->get('SMSSender'), $post->sms_besked));
                }

            } catch (Exception $e) {
                $this->log('Failed notification to participant #' . $receiver->id . '. Error: ' . $e->getMessage(), 'App', null);
                $status[] = 0;
            }

        }

        $grouped = array_count_values($status);

        $success = empty($grouped[1]) ? 0 : $grouped[1];
        $failed  = empty($grouped[0]) ? 0 : $grouped[0];

        return array('success' => $success, 'failure' => $failed);
    }

    /**
     * fetches a participant from a payment hash, that is
     * an md5 hash of id and password
     *
     * @param string $hash Hash to search by
     *
     * @access public
     * @return null|Deltagere
     */
    public function getParticipantFromPaymentHash($hash)
    {
        $query = "SELECT participant_id FROM participantpaymenthashes WHERE hash = ?";

        $participant = null;

        if ($results = $this->db->query($query, array($hash))) {
            $participant = $this->createEntity('Deltagere')->findById($results[0]['participant_id']);
        }

        return $participant;
    }

    /**
     * fetches a participant from a payment hash, that is
     * an md5 hash of id and password
     *
     * @param string $hash Hash to search by
     *
     * @access public
     * @return null|Deltagere
     */
    public function getParticipantFromResetPasswordHash($hash)
    {
        $query = "SELECT id FROM deltagere WHERE MD5(CONCAT('reset-pw-', id, '-', password)) = ?";

        $participant = null;

        if ($results = $this->db->query($query, array($hash))) {
            $participant = $this->createEntity('Deltagere')->findById($results[0]['id']);
        }

        return $participant;
    }

    /**
     * checks if the participant has outstanding payments on their ticket
     *
     * @param Deltagere $participant Participant to check
     *
     * @access public
     * @return bool
     */
    public function participantHasOutstandingPayment(Deltagere $participant)
    {
        return !($participant->calcRealTotal() <= 0 || $participant->calcRealTotal() <= $participant->betalt_beloeb);
    }

    /**
     * generates a url for accepting payment from user
     *
     * @param Deltagere $participant Participant to take payment for
     *
     * @access public
     * @return string
     */
    public function generatePaymentUrl(Deltagere $participant)
    {
        $factory = $this->dic->get('PaymentFactory');

        $payment_module = $factory->build();

        $price = ceil($participant->calcSignupTotal() - $participant->betalt_beloeb) * 100;

        $hash = $this->factory('Api')->getParticipantPaymentHash($participant);

        $links = [
            'callback_url' => $this->url('participant_register_payment', array('hash' => $hash)),
            'success_url'  => $this->url('participant_post_payment'),
            'cancel_url'   => 'http://www.fastaval.dk/',
        ];

        try {
            $url = $payment_module->generateOutput($participant, $price, $links);

            $participant->paid_note = $participant->paid_note . PHP_EOL . 'Payment: ' . $hash;
            $participant->update();

            return $url;

        } catch (FrameworkException $e) {
            return '';
        }
    }

    /**
     * checks the posted vars from the online payment
     *
     * @param Deltagere $participant Participant to register payment for
     * @param Request   $request     Request data
     *
     * @access public
     * @return bool
     */
    public function registerParticipantPayment(Deltagere $participant, Request $request)
    {
        $factory = $this->dic->get('PaymentFactory');

        $parsed_data = $factory->build()->parseCallbackRequest($request);

        if (!$parsed_data) {
            return false;
        }

        $participant->betalt_beloeb = $participant->betalt_beloeb ? $participant->betalt_beloeb + ($parsed_data['amount'] / 100) : $parsed_data['amount'] / 100;

        $participant->paid_note .= PHP_EOL . 'Online payment of ' . ($parsed_data['amount'] / 100) . ' on ' . date('Y-m-d H:i:s');

        $participant->update();

        $query = '
INSERT INTO paymentfritidlog
SET participant_id = ?, amount = ?, cost = ?, fees = ?, timestamp = NOW()
';

        $this->db->exec($query, [$participant->id, $parsed_data['amount'], $parsed_data['cost'], $parsed_data['fees']]);

        return true;
    }

    /**
     * preps all the variables needed for
     * the payment reminder email
     *
     * @param Deltagere $participant Participant for the email
     * @param Page      $page        Output object instance
     *
     * @access public
     * @return Deltagere
     */
    public function setupPaymentReminderEmail(Deltagere $participant, Page $page)
    {
        $pay_by_time  = strtotime($this->config->get('con.paymentlimit'));
        $signup_time = strtotime($participant->signed_up) + 86400;

        $paytime = $pay_by_time > $signup_time ? $pay_by_time : $signup_time;

        if ($paytime < time()) {
            $paytime = time() + 86400;
        }

        $api = $this->factory('Api');

        try {
            $hash = $api->getParticipantPaymentHash($participant);

        } catch (FrameworkException $e) {
            $hash = $api->setParticipantPaymentHash($participant);
        }

        $page->participant = $participant;
        $page->payment_remainder = $participant->calcSignupTotal() - $participant->betalt_beloeb;
        $page->payment_url = $this->url('participant_payment', array('hash' => $hash));
        $page->payment_day = date('d/m-Y', $paytime);

        return $participant;
    }

    /**
     * preps all the variables needed for
     * the signup email
     *
     * @param Deltagere $participant Participant for the email
     *
     * @access public
     * @return Deltagere
     */
    public function setupSignupEmail(DBObject $participant, Page $page)
    {
        $pay_by_time  = strtotime($this->config->get('con.paymentlimit'));
        $signup_time = strtotime($participant->signed_up) + 86400;
        $paytime = $pay_by_time > $signup_time ? $pay_by_time : $signup_time;

        if ($paytime < time()) {
            $paytime = time() + 86400;
        }
        
        $lang = !empty($_GET['lang']) ? $_GET['lang'] : '';
        if ($participant->speaksDanish() && $lang !== 'en') {
            $page->payment_day = date('d/m-Y', $paytime);
        } else {
            $page->payment_day = date('M d, Y', $paytime);
        }

        $page->participant = $participant;
        $page->wear        = $participant->getWear();
        $page->activities  = $participant->getTilmeldinger();
        $page->gds         = $participant->getGDSTilmeldinger();
        $page->food        = $participant->getMadtider();

        $api = $this->factory('Api');
        if ($participant->id) {
            try {
                $hash = $api->getParticipantPaymentHash($participant);
            } catch (FrameworkException $e) {
                $hash = $api->setParticipantPaymentHash($participant);
            }

            $page->payment_url = $this->url('participant_payment', array('hash' => $hash));
        }

        $entrance = array();
        $prices   = array(
            'alea'        => 0,
            'sleeping'    => 0,
            'entrance'    => 0,
            'food'        => 0,
            'activities'  => 0,
            'wear'        => 0,
            'other-stuff' => 0,
            'fees'        => 0,
            'total'       => 0,
        );

        foreach ($participant->getIndgang() as $indgang) {
            if (!$indgang) {
                continue;
            }

            if ($indgang->isAleaMembership()) {
                $entrance['alea-membership'] = true;
                $prices['alea']              = $indgang->pris;

            } elseif ($indgang->isPartout()) {
                if ($indgang->isSleepTicket()) {
                    $entrance['sleeping-partout']  = true;
                    $prices['sleeping']           += $indgang->pris;

                } else {
                    $entrance['entrance-partout']  = true;
                    $prices['entrance']           += $indgang->pris;
                }

            } elseif ($indgang->isDayTicket()) {
                $entrance['entrance-day'][strtotime($indgang->start)] = true;

                $prices['entrance'] += $indgang->pris;

            } elseif ($indgang->isSleepTicket()) {
                $entrance['sleeping-day'][strtotime($indgang->start)] = true;

                $prices['sleeping'] += $indgang->pris;

            } elseif ($indgang->isParty()) {
                $entrance['ottofest'] = true;
                $entrance['otto']     = true;

                $prices['food'] += $indgang->pris;

            } elseif ($indgang->isFee()) {
                $prices['fees'] += $indgang->pris;

            } elseif ($indgang->isRich()) {
                if ($indgang->isSecret()){
                    $page->hemmelig_onkel = $indgang->pris;
                } else {
                    $page->rig_onkel = $indgang->pris;
                }

                $prices['other-stuff'] += $indgang->pris;
            } else {
                $entrance[$indgang->type] = true;

                if (strtolower(substr($indgang->type, 0, 4)) === 'otto') {
                    $entrance['otto'] = true;

                    $prices['food'] += $indgang->pris;

                } else {
                    $prices['other-stuff'] += $indgang->pris;
                }
            }
        }
        ksort($entrance['entrance-day']);
        ksort($entrance['sleeping-day']);

        foreach ($page->food as $item) {
            if ($item) {
                $prices['food'] += $item->getMad()->pris;
            }
        }

        foreach ($page->wear as $item) {
            $prices['wear'] += $item->antal * $item->getWearpris()->pris;
        }

        foreach ($page->activities as $signup) {
            $prices['activities'] += $signup->getActivity()->pris;
        }

        $prices['total'] = array_sum($prices);

        $page->entrance = $entrance;
        $page->prices   = $prices;

        if ($participant->id && $participant->isArrangoer()) {
            $page->participant_photo_upload_link = $this->getPhotoUploadLink($participant);
        }

        $page->end_signup_changes_date = $page->payment_day;

        $constart = strtotime($this->config->get('con.start'));
        $page->next_year = date('Y', strtotime('+1 year',$constart));

        return $participant;
    }

    public function findParticipantsByEmail($email)
    {
        $select = $this->createEntity('Deltagere')->getSelect();
        $select->setWhere('email', '=', $email);
        return $this->createEntity('Deltagere')->findBySelectMany($select);
    }

    public function findBankingFee()
    {
        $select = $this->createEntity('Indgang')->getSelect();
        $select->setWhere('type', '=', 'Bankoverførselsgebyr');
        return $this->createEntity('Indgang')->findBySelect($select);
    }

    public function getParticipantsSignedupDaysAgo($days_ago)
    {
        $select = $this->createEntity('Deltagere')->getSelect();

        $select->setRawWhere('DATE(signed_up) = DATE(NOW() - INTERVAL ' . intval($days_ago) . ' DAY)', 'AND')
            ->setOrder('id', 'ASC');

        return $this->createEntity('Deltagere')->findBySelectMany($select);
    }

    public function filterOutPaidSignups(array $participants)
    {
        return array_filter($participants, function ($x) {
            $signup_total = $x->calcSignupTotal();

            return !(intval($signup_total) === 0 || $x->betalt_beloeb > 0);
        });
    }

    /**
     * filters out big groups that pay together, late
     *
     * @param array $participants Participants to filter out
     *
     * @access public
     * @return array
     */
    public function filterOutGroups(array $participants)
    {
        return array_filter($participants, function ($x) {
            return !(trim($x->email) === 'viborg.ungdomsskole.rollespil@gmail.com' || trim($x->ungdomsskole) === 'Viborg Ungdomsskole');
        });
    }

    public function filterSignedUpToday(array $participants)
    {
        return array_filter($participants, function ($x) {
            return strtotime($x->created) < strtotime('today');
        });
    }

    public function filterOutAnnulled(array $participants)
    {
        return array_filter($participants, function ($x) {
            return $x->annulled === 'nej';
        });
    }

    public function filterOutTodaysReminders(array $participants)
    {
        $select = $this->createEntity('LogItem')->getSelect();

        $select->setWhere('message', 'LIKE', '%sent payment reminder%')
            ->setRawWhere('DATE(created) = DATE(NOW())', 'AND');

        foreach ($this->createEntity('LogItem')->findBySelectMany($select) as $log_item) {
            foreach ($participants as $id => $participant) {
                if (stripos($log_item->message, '(ID: ' . $participant->id . ')') !== false) {
                    unset($participants[$id]);
                }
            }
        }

        return $participants;
    }

    /**
     * returns participants for cancellation/payment reminders
     * with volunteers filtered out
     *
     * @param array $participants Participants to filter
     *
     * @access public
     * @return array
     */
    public function getParticipantsForPaymentReminderNoVolunteers(array $participants)
    {
        return array_filter($participants, function ($x) {
            return !$x->isArrangoer();
        });
    }

    public function getParticipantsForPaymentReminder()
    {
        $participants = $this->filterOutPaidSignups($this->createEntity('Deltagere')->findAll());
        $participants = $this->filterOutGroups($participants);
        $participants = $this->filterOutTodaysReminders($participants);
        $participants = $this->filterOutAnnulled($participants);
        $participants = $this->filterSignedUpToday($participants);

        return $participants;
    }

    /**
     * registeres a bank transfer and optionally a bank transfer entrance
     *
     * @param int         $participant_id ID of participant to register for
     * @param RequestVars $post           Post vars to register
     *
     * @access public
     * @return $this
     */
    public function registerBankTransfer($participant_id, RequestVars $post)
    {
        $participant = $this->findParticipant($participant_id);

        if (!$participant->id) {
            throw new Exception('No participant available');
        }

        if (!$post->amount) {
            throw new Exception('No amount to register');
        }

        $fee = null;

        if ($post->fee) {
            $select = $this->createEntity('Indgang')->getSelect();
            $select->setWhere('id', '=', $post->fee);

            $fee = $this->createEntity('Indgang')->findBySelect($select);
        }

        if ($fee && $fee->id) {
            if (!$participant->setIndgang($fee)) {
                throw new Exception('Could not add bank registration fee');
            }
        }

        $participant->betalt_beloeb += intval($post->amount);

        $participant->paid_note .= PHP_EOL . 'Bank transfer of ' . floatval($post->amount) . 'DKK transferred on ' . $post->date;

        if ($post->payment_id) {
            $participant->paid_note .= ' with ID: ' . $post->payment_id;
        }

        if (!$participant->update()) {
            throw new Exception('Could not update participant payment to register bank transfer');
        }

        $this->log("Deltager #{$participant->id} har fået registreret bankoverførsel på {$post->amount} af {$this->getLoggedInUser()->user}", 'Payment', $this->getLoggedInUser());
    }

    /**
     * returns stats on participants karma
     *
     * @access public
     * @return array
     */
    public function getKarmaStatsForParticipant(Deltagere $participant)
    {
        $karma = $this->buildKarma();

        $stats = $karma->calculate($participant);

        return $stats;
    }

    /**
     * returns data on karma for all participants
     *
     * @access public
     * @return array
     */
    public function getKarmaSortedData()
    {
        $participants = $this->createEntity('Deltagere')->findAll();

        $karma = $this->buildKarma();

        $stats = $karma->calculate($this->createEntity('Deltagere')->findAll());

        $mapper = function ($x) use ($stats) {
            return [
                    'id'    => $x->id,
                    'name'  => $x->fornavn . ' ' . $x->efternavn,
                    'karma' => isset($stats[$x->id]) ? $stats[$x->id] : 0,
                   ];
        };

        return array_map($mapper, $participants);
    }

    /**
     * returns array of available rooms
     *
     * @access public
     * @return array
     */
    public function findAvailableSleepingRooms()
    {
        $queries = [];

        $start = date('Y-m-d 22:00:00', strtotime($this->config->get('con.start')));
        $ends  = date('Y-m-d 10:00:00', strtotime($start) + 86400);

        $nights = 0;
        $starts = [];

        $rooms = $this->createEntity('Lokaler')->findAll();

        $map = function ($x) {
            return $x->id;
        };

        $rooms = array_combine(array_map($map, $rooms), $rooms);

        while (strtotime($start) < strtotime($this->config->get('con.end'))) {
            $starts[] = $start;
            $middle   = date('Y-m-d 23:59:00', strtotime($start));

            $query = '
SELECT
    r.id,
    r.sovekapacitet - COUNT(*) AS capacity,
    "' . $start . '" AS starts,
    "' . $ends . '" AS ends
FROM
    lokaler AS r
    LEFT JOIN participants_sleepingplaces AS ps ON ps.room_id = r.id AND ps.starts <= "' . $middle . '" AND ps.ends >= "' . $middle . '"
GROUP BY
    r.id,
    r.sovekapacitet
HAVING
    capacity > 0
';

            $queries[] = $query;

            $start = date('Y-m-d 22:00:00', strtotime($start) + 86400);
            $ends  = date('Y-m-d 10:00:00', strtotime($start) + 86400);

            $nights++;
        }

        $empty_rooms = $places = [];

        foreach ($this->db->query(implode(' UNION ', $queries)) as $row) {
            $empty_rooms[$row['id']][$row['starts']] = $row['capacity'];
        }

        $filter = function ($x) use ($nights) {
            return count($x) === $nights;
        };

        $places['allthrough'] = array_filter($empty_rooms, $filter);

        foreach ($starts as $start) {
            $filter = function ($x) use ($start) {
                return isset($x[$start]);
            };

            $places[$start] = array_filter($empty_rooms, $filter);

        }

        foreach ($places as $id => $place) {
            foreach (array_keys($place) as $room_id) {
                $places[$id][$room_id] = $rooms[$room_id];
            }

        }

        return $places;
    }

    /**
     * removes all participants sleep data
     *
     * @param int $participant_id Id of participant to update
     *
     * @access public
     * @return ParticipantModel
     */
    public function removeParticipantSleepData($participant_id)
    {
        $query = '
DELETE FROM participants_sleepingplaces WHERE participant_id = ?
';

        $this->db->exec($query, [$participant_id]);

        return $this;
    }

    /**
     * updates participants sleeping data
     *
     * @param int   $participant_id Id of participant to update
     * @param array $data           Data to use for update
     *
     * @access public
     * @return ParticipantModel
     */
    public function updateSleepingData($participant_id, array $data)
    {
        foreach ($data as $row) {
            if (empty($row['room_id'])) {
                continue;
            }

            $query = '
INSERT INTO participants_sleepingplaces SET participant_id = ?, room_id = ?, starts = ?, ends = ?
';

            $this->db->exec($query, [$participant_id, $row['room_id'], $row['starts'], $row['ends']]);

        }

        return $this;
    }

    /**
     * returns data on sleep areas for the participant
     *
     * @param DBObject $participant Participant to get data for
     *
     * @access public
     * @return array
     */
    public function getSleepDataForParticipant(DBObject $participant)
    {
        $query = '
SELECT
    room_id,
    starts,
    ends
FROM
    participants_sleepingplaces
WHERE
    participant_id = ?
ORDER BY
    starts
';

        $data = [];

        foreach ($this->db->query($query, [$participant->id]) as $row) {
            $data[] = [
                       'room'   => $this->createEntity('Lokaler')->findById($row['room_id']),
                       'starts' => date('l, j/n', strtotime($row['starts'])),
                       'ends'   => date('l, j/n', strtotime($row['ends'])),
                      ];
        }

        return $data;
    }

    /**
     * returns number of vouchers the participant should get
     *
     * @param int $participant_id ID of participant to check
     *
     * @access public
     * @return int
     */
    public function checkParticipantsForVouchers($participant_id)
    {
        $participant = $this->createEntity('Deltagere')->findById($participant_id);

        $vouchers = 0;

        foreach ($participant->getPladser() as $spot) {
            if ($spot->type === 'spilleder') {
                $vouchers++;
            }

        }

        return $vouchers + $participant->extra_vouchers;
    }

    /**
     * fetches or creates a photo upload link
     *
     * @param \Deltagere $participant Participant to generate link for
     *
     * @access public
     * @return string
     */
    public function getPhotoUploadLink(DBObject $participant)
    {
        $query = '
SELECT identifier
FROM participantphotoidentifiers
WHERE participant_id = ?
';

        $result = $this->db->query($query, [$participant->id]);

        if (empty($result)) {
            $identifier = makeRandomString(32);

            $query = '
INSERT INTO participantphotoidentifiers SET participant_id = ?, identifier = ?
';

            $this->db->exec($query, [$participant->id, $identifier]);

        } else {
            $identifier = $result[0]['identifier'];
        }

        return $this->url('photo_upload_form', ['identifier' => $identifier]);
    }

    /**
     * returns public uri for participants cropped photo, if it exists
     *
     * @param Deltagere $participant Participant to find cropped photo
     *
     * @access public
     * @return string
     */
    public function fetchCroppedPhoto(DBObject $participant)
    {
        $query = '
SELECT identifier
FROM participantphotoidentifiers
WHERE participant_id = ?
';

        $result = $this->db->query($query, [$participant->id]);

        if (empty($result)) {
            return '';
        }

        $photo_model = $this->factory('Photo');

        return $photo_model->getExistingCroppedImage($result[0]['identifier']);
    }

    /**
     * returns the id template set for the participant, if
     * one is set to override the default
     *
     * @param Deltagere $participant Participant to fetch template for
     *
     * @access public
     * @return IdTemplate|false
     */
    public function getParticipantIdTemplate(Deltagere $participant)
    {
        $query = '
SELECT
    template_id
FROM
    participantidtemplates
WHERE
    participant_id = ?
';

        $result = $this->db->query($query, [$participant->id]);

        if (!$result) {
            return false;
        }

        return $this->createEntity('IdTemplate')->findById($result[0]['template_id']);
    }

    /**
     * returns the template for the category, if one is set
     *
     * @param BrugerKategorier $category Category to fetch for
     *
     * @access public
     * @return IdTemplate|false
     */
    public function getCategoryIdTemplate(BrugerKategorier $category)
    {
        $query = '
SELECT
    template_id
FROM
    brugerkategorier_idtemplates
WHERE
    category_id = ?
';

        $result = $this->db->query($query, [$category->id]);

        if (!$result) {
            return false;
        }

        return $this->createEntity('IdTemplate')->findById($result[0]['template_id']);
    }

    /**
     * returns data suitable for an editable array for
     * selecting id template override
     *
     * @param IdTemplate $category_template Template default for user category, if available
     *
     * @access public
     * @return array
     */
    public function fetchTemplateSelectData($category_template)
    {
        $data = [
            '0' => 'Default (' . ($category_template ? e($category_template->name) : 'ingen') . ')'
        ];

        foreach ($this->createEntity('IdTemplate')->findAll() as $template) {
            $data[(string) $template->id] = e($template->name);
        }

        return $data;
    }

    /**
     * returns available wear sizes
     *
     * @access public
     * @return array
     */
    public function getWearSizes()
    {
        return $this->createEntity('Wear')->getWearSizes();
    }

    /**
     * returns double booked participants, if any are found
     *
     * @access public
     * @return array
     */
    public function findDoubleBookedParticipants()
    {
        $query = '
SELECT
    DISTINCT d.id
FROM (
    SELECT
        p.deltager_id AS id,
        COUNT(*) AS activities
	FROM
        pladser AS p
    GROUP BY
        p.deltager_id
    HAVING activities >= 2
    ) AS d
JOIN pladser AS p1 ON p1.deltager_id = d.id
JOIN hold AS h1 ON h1.id = p1.hold_id
JOIN afviklinger AS a1 ON a1.id = h1.afvikling_id
JOIN aktiviteter AS ak1 ON ak1.id = a1.aktivitet_id
JOIN pladser AS p2 ON p1.deltager_id = p2.deltager_id AND p1.hold_id != p2.hold_id
JOIN hold AS h2 ON h2.id = p2.hold_id
JOIN afviklinger AS a2 ON a2.id = h2.afvikling_id
JOIN aktiviteter AS ak2 ON ak2.id = a2.aktivitet_id
WHERE (
    (a1.start >= a2.start AND a1.start < a2.slut)
    OR (a1.slut > a2.start AND a1.slut <= a2.slut)
    OR (a1.start <= a2.start AND a1.slut >= a2.slut)
    )
    AND ak1.tids_eksklusiv = "ja"
    AND ak2.tids_eksklusiv = "ja"
';

        $participants = [];

        foreach ($this->db->query($query) as $row) {
            $participants[] = $row['id'];
        }

        $mapper = function ($id) {
            return $this->createEntity('Deltagere')->findById($id);
        };

        $participants = array_map($mapper, $participants);

        return $participants;
    }

    /**
     * fetches a participant from a photo identifier
     *
     * @param string $identifier identifier to search by
     *
     * @access public
     * @return null|Deltagere
     */
    public function getParticipantFromPhotoidentifier($identifier)
    {
        $query = "SELECT participant_id FROM participantphotoidentifiers WHERE LOWER(identifier) = LOWER(?)";

        $participant = null;

        if ($results = $this->db->query($query, array($identifier))) {
            $participant = $this->createEntity('Deltagere')->findById($results[0]['participant_id']);
        }

        return $participant;
    }

    public function findPeopleNeedingRefund(){
        $participants = $this->createEntity('Deltagere')->findAll();

        foreach($participants as $participant) {
            $participant->difference = $participant->calcRealTotal() - $participant->betalt_beloeb;
            if ($participant->difference < 0) {
                $refundees[] = $participant;
            }
        }

        return $refundees;
    }

    public function anonymizeParticipants($keep) {
        // Always keep id
        if (!in_array('id', $keep)) $keep[] = 'id';

        $fields = [];
        $unknown = [];
        $deltager = $this->createEntity('Deltagere');
        $columns = $deltager->getColumnInfo();
        foreach($columns as $name => $type ) {
            switch(true) {
                case in_array($name, $keep):
                    break;
                case $deltager->isFieldNullable($name):
                    $fields[$name] = null;
                    break;
                case $name == "brugerkategori_id": // Avoid error on foreign key constraint 
                    $fields[$name] = 1;
                    break;
                case preg_match("/int\(\d+\)/", $type) || $type == "float":
                    $fields[$name] = 0;
                    break;
                case $type == "enum('ja','nej')":
                    $fields[$name] = 'nej';
                    break;
                case $type == "enum('m','k','a')":
                    $fields[$name] = 'a';
                    break;
                case strpos($type, "set(") !== false || strpos($type, "char(") !== false || $type == "text": // strpos = 0 is still a match
                    $fields[$name] = '';
                    break;
                case $type == "datetime";
                    $fields[$name] = '0000-00-00 00:00:00';
                    break;
                default:
                    $unknown[$name] = $type;
                    $this->dic->get('Log')->logToFile(
                        "Error in anonymizeParticipants()\n".
                        "Unknown data type for field $name : $type"
                    );
            }
        }

        $errors = [];

        if (!empty($unknown)) {
            $errors[] = [
                'id' => 'unknown',
                'desc' => "There are fields with unknown datatypes that we don't know how to reset",
                'data' => $unknown
            ];
        }

        if(!$deltager->updateAll($fields)){
            $errors[] = [
                'id' => 'db',
                'desc' => "Failed to execute the update query",
            ];
        }

        $res = (object)[
            'success' => empty($errors),
            'fields' => $fields,
            'errors' => $errors,
        ];

        return $res;
    }
}
