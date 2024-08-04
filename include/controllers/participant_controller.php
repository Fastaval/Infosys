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
 * @package   Controllers
 * @author    Peter Lind <peter.e.lind@gmail.com>
 * @copyright 2009-2012 Peter Lind
 * @license   http://www.gnu.org/licenses/gpl.html GPL 3
 * @link      http://www.github.com/Fake51/Infosys
 */

/**
 * handles all participant pages, including showing, editing and updating
 *
 * @category Infosys
 * @package  Controllers
 * @author   Peter Lind <peter.e.lind@gmail.com>
 * @license  http://www.gnu.org/licenses/gpl.html GPL 3
 * @link     http://www.github.com/Fake51/Infosys
 */

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ParticipantController extends Controller
{
    /**
     * pre run hooks
     * format of array is: an array of method (string), exclusive (bool), methodlist (array of strings) per hook
     * - method is method to run
     * - exclusive determines whether the next array consists of methods to be excluded or included in the prerun hook
     * - methodlist is the array of methods for which the prerun hook will either be run (inclusive) or not be run (exclusive)
     *
     * @var array
     */
    protected $prerun_hooks = array(
        array('method' => 'checkUser','exclusive' => true, 'methodlist' => array(
            'displayParticipantInfo',
            'showSignupDetails',
            'showSignupDetailsJson',
            'listAssignedGMs',
            'ean8SmallBarcode',
            'ean8Barcode',
            'ean8Badge',
            'resetParticipantPassword',
            'cronPaymentReminder',
            'cancelParticipantSignup',
        )),
    );

    /**
     * default method of controller, outputs common functions
     *
     * @access public
     * @return void
     */
    public function main() {
        $this->page->setTitle('Deltager');
    }

    /**
     * shows list of karma stats
     *
     * @access public
     * @return void
     */
    public function karmaStatus()
    {
        $this->page->karma_avg = $this->model->getKarmaAvg();
    }

    /**
     * fetches and displays info for a single deltager
     *
     * @access public
     * @return void
     */
    public function visDeltager()
    {
        if (!empty($this->vars['id']) && ($deltager = $this->model->findDeltager($this->vars['id']))) {
            $countries = [];
            foreach($this->model->getCountries() as $country) {
                $countries[$country['name_da']] = $country['name_da'];
            }            
            $this->page->countries = json_encode($countries);
            
            $work_areas = [0 => 'Intet arrangørområde'];
            foreach($this->model->getWorkAreas() as $area) {
                $work_areas[$area['name_da']] = $area['name_da'];
            }            
            $this->page->work_areas = json_encode($work_areas);

            $games = [ 0 => 'Intet scenarie/brætspil'];
            foreach($this->model->getGames() as $game) {
                $games[$game['navn']] = $game['navn'];
            }            
            $this->page->games = json_encode($games);

            $this->page->setTitle(e($deltager->getName() . ' - Deltager'));
            $this->page->deltager      = $deltager;
            $this->page->deltager_info = $this->model->findDeltagerInfo($deltager);
            $this->page->late_signup   = strtotime($this->config->get('con.signupend')) < strtotime($deltager->created);

            $this->page->banking_fee = $this->model->findBankingFee();

            $get = $this->page->request->get;

            if (!empty($get->payment_edit)) {
                $this->page->payment_edit = true;
            }

            $this->page->allow_checkin         = $this->model->getLoggedInUser()->hasRole('Infonaut') || $this->model->getLoggedInUser()->hasRole('Admin');
            $this->page->is_read_only          = $this->model->getLoggedInUser()->hasRole('Read-only') || $this->model->getLoggedInUser()->hasRole('Read-only activity');
            $this->page->is_read_only_activity = $this->model->getLoggedInUser()->hasRole('Read-only activity');

            $this->page->participant_karma = $this->model->getKarmaStatsForParticipant($this->page->deltager);

            $this->page->sleep_data = $this->model->getSleepDataForParticipant($this->page->deltager);

            $this->page->cropped_photo = $this->model->fetchCroppedPhoto($this->page->deltager);

            $this->page->participant_photo_upload_link = $this->model->getPhotoUploadLink($this->page->deltager);
            $this->page->participant_id_card_link      = $this->url('id_card_render') . '?ids=' . $this->page->deltager->id;

            $this->page->id_template      = $this->model->getParticipantIdTemplate($this->page->deltager);
            $this->page->default_template = $this->model->getCategoryIdTemplate($this->page->deltager->getUserCategory());

            $this->page->id_template_select_data = $this->model->fetchTemplateSelectData($this->page->default_template);

            if ($this->page->deltager->getAge(new DateTime($this->config->get('con.start'))) < 18) {
                $this->page->includeCSS('youngster.css');
            }

        } else {
            $this->page->setTemplate('noResults');
            $this->page->setTitle('Intet resultat');
        }
    }

    /**
     * fetches and displays all deltagere
     *
     * @access public
     * @return void
     */
    public function visAlle()
    {
        $session         = $this->dic->get('Session');
        $session->search = null;

        $this->page->available_columns = $this->model->getDisplayColumns();
        $get = $this->page->request->get;

        $this->page->model   = $this->model;
        $get->iDisplayLength = 25;
        $get->iDisplayStart  = 0;
        list($this->page->row_count, $result_length, $this->page->users) = $this->model->getAjaxListData($get);
    }

    /**
     * shows the result of a search
     *
     * @access public
     * @return void
     */
    public function showSearchResult() {
        $this->page->setTitle('Søgning');
        $this->page->setTemplate('visalle');

        $session = $this->dic->get('Session');
        $search = $session->search;

        if ($search && isset($search['wildcardsearch']) && (empty($search['wildcardhash']) || md5($search['wildcardsearch']) != $search['wildcardhash'])) {
            $ids = $this->model->createWildcardSearchBase($search['wildcardsearch']);

            if (count($ids) == 1) {
                $this->hardRedirect($this->url('visdeltager', array('id' => reset($ids))));
            }
        }

        $this->page->search_term = isset($search['wildcardsearch']) ? $search['wildcardsearch'] : null;

        $get = $this->page->request->get;

        $this->page->available_columns = $this->model->getDisplayColumns();
        $get->iDisplayLength = 25;
        $get->iDisplayStart  = 0;
        $this->page->model   = $this->model;

        list($this->page->row_count, $result_length, $this->page->users) = $this->model->getAjaxListData($get);
    }

    /**
     * looks for users for a given schedule, lists them using the search
     *
     * @access public
     * @return void
     */
    public function listForSchedule()
    {
        if (empty($this->vars['afvikling_id']) || !($afvikling = $this->model->findEntity('Afviklinger', $this->vars['afvikling_id']))) {
            $this->page->setTemplate('noResults');
            return;
        }

        //Do we only want to see people assigend to a group?
        if ($this->vars['assigned'] === 'assigned') {
            $ids = $afvikling->getParticipantsOnTeams();
            $session         = $this->dic->get('Session');
            $session->search = ['ids' => $ids];
        } else {
            $participants = $this->model->getSignupsForSchedule($afvikling);
            $this->model->setSearchBaseIds($participants);
        }

        $get = $this->page->request->get;

        $this->page->available_columns = $this->model->getDisplayColumns();
        $get->iDisplayLength           = 25;
        $get->iDisplayStart            = 0;
        $this->page->model             = $this->model;
        list($this->page->row_count, $result_length, $this->page->users) = $this->model->getAjaxListData($get);
        $this->page->setTemplate('visAlle');
    }

    /**
     * looks for users for a given group, lists them using the search
     *
     * @access public
     * @return void
     */
    public function listForGroup()
    {
        if (empty($this->vars['hold_id']) || !($hold = $this->model->findEntity('Hold', $this->vars['hold_id']))) {
            $this->page->setTemplate('noResults');
            return;
        }

        $participants = $hold->getDeltagere();;
        $this->model->setSearchBaseIds($participants);

        $get = $this->page->request->get;

        $this->page->available_columns = $this->model->getDisplayColumns();
        $get->iDisplayLength           = 25;
        $get->iDisplayStart            = 0;
        $this->page->model             = $this->model;
        list($this->page->row_count, $result_length, $this->page->users) = $this->model->getAjaxListData($get);
        $this->page->setTemplate('visAlle');

    }

    /**
     * stores found users in the session
     *
     * @param array $deltagere - array of Deltagere entities
     * @param array $columns   - array of columns to fetch details for
     *
     * @access protected
     * @return void
     */
    protected function saveFoundUsers($deltagere, $columns = null)
    {
        if (empty($deltagere) || !is_array($deltagere))
        {
            return;
        }
        $ids = array();
        foreach ($deltagere as $d)
        {
            $ids[] = $d->id;
        }
        if (!$columns)
        {
            $columns = array('id','navn');
        }
        $save_search = array();
        foreach ($deltagere as $d)
        {
            foreach ($columns as $key)
            {
                switch ($key)
                {
                    case 'navn':
                        $info = "{$d->fornavn} {$d->efternavn}";
                        break;
                    case 'brugerkategori':
                        $info = $d->getBrugerKategori()->navn;
                        break;
                    case 'madtype':
                        $info = $this->replaceDayNames($d->madtype->getFriendlyName());
                        break;
                    default:
                        $info = $d->$key;
            
                }
                $save_search[$d->id][$key] = $info;
            }
        }
        $session = $this->dic->get('Session');
        $session->search_result = serialize($save_search);
        $session->search_result_ids = $ids;
    }

    protected function saveSearchVars(array $search_vars) {
        $session = $this->dic->get('Session');
        $session->search_vars = $search_vars;
    }

    protected function saveSort($sort) {
        $session = $this->dic->get('Session');
        $session->search_sort = $sort;
    }

    protected function saveColumns($columns) {
        $session = $this->dic->get('Session');
        $session->search_columns = $columns;
    }

    protected function getPreviousColumns() {
        $session = $this->dic->get('Session');
        $result = $session->search_columns;
        return ((is_array($result)) ? $result : array());
    }

    protected function getPreviousSort() {
        $session = $this->dic->get('Session');
        $result = $session->search_sort;
        return ((is_array($result)) ? $result : null);
    }

    protected function getPreviousSearchVars() {
        $session = $this->dic->get('Session');
        $result = $session->search_vars;
        return ((is_array($result)) ? $result : array());
    }

    /**
     * fetches found users from the session - result of the prvevious search
     *
     * @access protected
     * @return array
     */
    protected function getPreviousResult()
    {
        $session = $this->dic->get('Session');
        $result = $session->search_result_ids;
        return ((is_array($result)) ? $result : array());
    }

    /**
     * fetches the serialized form of the previous result, unserializes and returns
     *
     * @access protected
     * @return array
     */
    protected function getPreviousResultArray()
    {
        $session = $this->dic->get('Session');
        $result = $session->search_result;
        return (($result) ? unserialize($result) : array());
    }

    /**
     * displays the participant searchbox
     *
     * @access public
     * @return void
     */
    public function showSearch()
    {
        $this->page->search_vars = $this->genSearchVars();
        $this->page->model = $this->model;
        $this->page->setTemplate('displaySearch');
    }

    /**
     * generates empty search vars for use by view
     *
     * @access protected
     * @return array
     */
    protected function genSearchVars()
    {
        return array('deltager_search' => array(
                                                'id' => '',
                                                'alder' => '',
                                                'brugerkategori_id' => '',
                                                'betalt_beloeb' => '',
                                                'fornavn' => '',
                                                'efternavn' => '',
                                                'nickname' => '',
                                                'email' => '',
                                                'international' => '',
                                                'adresse' => '',
                                                'postnummer' => '',
                                                'by' => '',
                                                'land' => '',
                                                'tlf' => '',
                                                'mobiltlf' => '',
                                                'medbringer_mobil' => '',
                                                'supergm' => '',
                                                'lang' => '',
                                                'knutepunkt' => '',
                                                'geekbookdrive' => '',
                                                'arrangoer_naeste_aar' => '',
                                                'deltaget_i_fastaval' => '',
                                                'supergds' => '',
                                                'flere_gdsvagter' => '',
                                                'sovesal' => '',
                                                'sober_sleeping',
                                                'udeblevet' => '',
                                                'rabat' => '',
                                                'knutepunkt_bil' => '',
                                                'forfatter' => '',
                                                'rig_onkel' => '',
                                                'hemmelig_onkel' => '',
                                                'financial_struggle' => '',
                                                'tilmeld_scenarieskrivning' => '',
                                                'krigslive_bus' => '',
                                                'ungdomsskole' => '',
                                                'arbejdsomraade' => '',
                                                'scenarie' => '',
                                                'admin_note' => '',
                                                'deltager_note' => '',
                                                'paid_note' => '',
                                                'beskeder' => '',
                                                'ny_alea' => '',
                                                'oprydning_tirsdag' => '',
                                                'ready_tirsdag' => '',
                                                'ready_mandag' => '',
                                                'may_contact' => '',
                                                'ny_alea' => '',
                                                'er_alea' => '',
                                                'package_gds' => '',
                                                ),
                        'mad_search' => array(),
                        'indgang_search' => array(),
                    );
    }

    /**
     * creates an edit page to modify details of a single deltager
     *
     * @access public
     * @return void
     */
    public function visEdit() {
        if (!empty($this->vars['id']) && ($deltager = $this->model->findDeltager($this->vars['id']))) {
            $this->page->setTitle(e($deltager->getName() . ' - Rediger'));
            $this->page->deltager = $deltager;
            $this->page->model = $this->model;
            $this->page->sleep_rooms = $this->model->getSleepingRooms();
        } else {
            $this->page->setTemplate('noResults');
        }
    }


    /**
     * creates a deltager with details posted through visOpret()
     *
     * @access public
     * @return void
     */
    public function createDeltager() {
        $this->page->setTitle('Opret deltager');

        if ($this->page->request->isPost()) {
            $post = $this->page->request->post;

            if ($post->create_deltager && ($deltager = $this->model->createDeltager($post))) {
                $this->successMessage('Deltageren blev oprettet.');
                $this->log("Deltager #{$deltager->id} blev oprettet af {$this->model->getLoggedInUser()->user}", 'Deltager', $this->model->getLoggedInUser());
                $this->hardRedirect($this->url('visdeltager', array('id' => $deltager->id)));
            } else {
                $this->errorMessage('Kunne ikke oprette deltageren.');
                $this->hardRedirect($this->url('deltagerehome'));
            }
        } else {
            $this->page->model = $this->model;
            $this->page->setTemplate('visOpret');
        }
    }



    /**
     * creates an edit page to modify details of a single deltager
     *
     * @access public
     * @return void
     */
    public function visTextedit()
    {
        if (empty($this->vars['textfield']) || empty($this->vars['id']) || !($deltager = $this->model->findDeltager($this->vars['id']))) {
            $this->page->setTemplate('noResults');
        } else {
            $this->page->textfield = $this->vars['textfield'];
            $this->page->deltager = $deltager;

            if (preg_match("/deltager_note_(\w+)/",$this->page->textfield,$matches)) {
                $var = $matches[1];
                $this->page->textcontent = $deltager->note->$var->content;
                $this->page->field = $deltager->note->$var->name;
            } else {
                $var = $this->page->textfield;
                $this->page->textcontent = $deltager->$var;
                switch ($var) {
                    case 'admin_note':
                        $this->page->field = 'noter <strong>om</strong> deltageren';
                        break;
                    case 'deltager_note':
                        $this->page->field = 'beskeder <strong>fra</strong> deltageren';
                        break;
                    case 'beskeder':
                        $this->page->field = 'beskeder <strong>til</strong> deltageren';
                        break;
                    case 'paid_note':
                        $this->page->field = 'økonomi-noter';
                        break;
                    default:
                        $this->page->field = e($var);
                }
            }
            $this->page->setTitle('Rediger ' . strip_tags($this->page->field));
        }
    }

    //{{{ update methods
    /**
     * updates a deltager with details posted through visEdit()
     *
     * @access public
     * @return void
     */
    public function updateDeltager()
    {
        if (empty($this->vars['id']) || !($deltager = $this->model->findDeltager($this->vars['id']))) {
            $this->page->setTemplate('noResults');
            return;
        }

        if ($this->page->request->isPost()) {
            $post = $this->page->request->post;
            if ($post->edit_deltager) {
                try {
                    if ($this->model->updateDeltager($deltager, $post->deltager)) {
                        $this->successMessage('Deltageren blev opdateret.');
                        $this->log("Deltager #{$deltager->id} blev opdateret af {$this->model->getLoggedInUser()->user}", 'Deltager', $this->model->getLoggedInUser());
                        $this->hardRedirect($this->url('visdeltager', array('id' => $deltager->id)));
                    } else {
                        throw new FrameworkException('Failed to update participant');
                    }
                } catch (Exception $e) {
                    $this->errorMessage('Der var et problem med opdateringen, deltageren blev ikke opdateret.');
                    $this->page->deltager = $deltager;
                    $this->page->model    = $this->model;
                    $this->page->setTemplate('visEdit');
                }
            } elseif ($post->delete_deltager) {
                $this->page->deltager = $deltager;
                $this->page->setTemplate('confirmDelete');
            }
            else
            {
                $this->errorMessage('Der var et problem med opdateringen, deltageren blev ikke opdateret.');
                $this->page->deltager = $deltager;
                $this->page->setTemplate('visEdit');
            }
        }
        else
        {
            $this->hardRedirect($this->url('visdeltager', array('id' => $deltager->id)));
        }
    }

    /**
     * updates note fields for deltager (admin_note, deltager_note, beskeder)
     *
     * @access public
     * @return void
     */
    public function updateDeltagerNote()
    {
        if (!$this->page->request->isPost() || !isset($this->vars['textfield']) || empty($this->vars['id']) || !($deltager = $this->model->findDeltager($this->vars['id'])))
        {
            $this->hardRedirect($this->url('deltagerehome'));
        }

        if ($this->model->updateDeltagerNote($deltager, $this->vars['textfield'], $this->page->request->post))
        {
            $this->successMessage('Deltageren blev opdateret.');
            $this->log("Deltager #{$deltager->id} blev opdateret af {$this->model->getLoggedInUser()->user}", 'Deltager', $this->model->getLoggedInUser());
            $this->hardRedirect($this->url('visdeltager', array('id' => $deltager->id)));
        }
        else
        {
            $this->errorMessage('Kunne ikke opdatere deltager.');
            $this->page->setTemplate('visTextedit');
            $this->page->deltager  = $deltager;
            $this->page->textfield = $this->vars['textfield'];
        }
    }

    /**
     * updates a deltagers mad/wear choices - or shows the page for doing it
     *
     * @access public
     * @return void
     */
    public function updateMadWear() {
        if (empty($this->vars['id']) || !($deltager = $this->model->findDeltager($this->vars['id']))) {
            $this->page->setTemplate('noResults');
            return;
        }
        if (!$this->page->request->isPost()) {
            $this->page->setTitle('Rediger indgang, mad og wear');
            $this->page->deltager      = $deltager;
            $this->page->deltager_info = $this->model->findDeltagerInfo($deltager);
            $this->page->model         = $this->model;
            $this->page->wear_sizes    = $this->model->getWearSizes();

            $this->page->setTemplate('visEditMadWear');

        } else {
            $this->model->updateIMW($deltager, $this->page->request->post) ? $this->successMessage('Deltageren blev opdateret.') : $this->errorMessage('Kunne ikke opdatere deltageren.');

            if (!($this->model->getLoggedInUser()->hasRole('Infonaut') || $this->model->getLoggedInUser()->hasRole('Admin'))) {
                $this->errorMessage('Mad og wear kan pt kun opdateres af den hoved-ansvarlige for området');
            }

            $this->log("Deltager #{$deltager->id}'s indgang/mad/wear valg blev opdateret af {$this->model->getLoggedInUser()->user}", 'Deltager', $this->model->getLoggedInUser());
            $this->hardRedirect($this->url('visdeltager', array('id' => $deltager->id)));
        }
    }

    /**
     * updates a deltagers gds activities
     *
     * @access public
     * @return void
     */
    public function updateGDS()
    {
        if (empty($this->vars['id']) || !($deltager = $this->model->findDeltager($this->vars['id']))) {
            $this->page->setTemplate('noResults');
            return;
        }

        if (!$this->page->request->isPost()) {
            $this->page->deltager = $deltager;
            $this->page->gds      = $this->model->getAllGDS();
            $this->page->setTemplate('editGDS');
        } else {
            $post = $this->page->request->post;
            if (!empty($post->update_gds)) {
                $this->model->updateGDS($deltager, $this->page->request->post) ? $this->successMessage('Deltageren blev opdateret.') : $this->errorMessage('Kunne ikke opdatere deltageren.');
                $this->log("Deltager #{$deltager->id}'s GDSVagter blev opdateret af {$this->model->getLoggedInUser()->user}", 'GDS', $this->model->getLoggedInUser());
            } else {
                $this->errorMessage('Deltagerens GDS vagter blev ikke opdateret.');
            }

            $this->hardRedirect($this->url('visdeltager', array('id' => $deltager->id)));
        }
    }

    /**
     * updates a deltagers gds activities
     *
     * @access public
     * @return void
     */
    public function updateGDSTilmeldinger()
    {
        if (empty($this->vars['id']) || !($deltager = $this->model->findDeltager($this->vars['id']))) {
            $this->page->setTemplate('noResults');
            return;
        }

        if (!$this->page->request->isPost()) {
            $this->page->deltager = $deltager;
            $this->page->categories = $this->model->getAllGDSCategories();
            $this->page->setTemplate('editGDSTilmeldinger');

        } else {
            $post = $this->page->request->post;

            if (!empty($post->update_gds))
            {
                $this->model->updateGDSTilmeldinger($deltager, $post) ? $this->successMessage('Deltageren blev opdateret.') : $this->errorMessage('Kunne ikke opdatere deltageren.');
                $this->log("Deltager #{$deltager->id}'s GDS tilmeldinger blev opdateret af {$this->model->getLoggedInUser()->user}", 'GDS', $this->model->getLoggedInUser());
            }
            else
            {
                $this->errorMessage('Deltagerens GDS vagt tilmeldinger blev ikke opdateret.');
            }
            $this->hardRedirect($this->url('visdeltager', array('id' => $deltager->id)));
        }
    }

    /**
     * updates a participants sign up choices
     *
     * @access public
     * @return void
     */
    public function updateTilmeldinger()
    {
        if (empty($this->vars['id']) || !($deltager = $this->model->findDeltager($this->vars['id']))) {
            $this->page->setTemplate('noResults');
            return;
        }

        if (!$this->page->request->isPost()) {
            $this->page->deltager = $deltager;
            $this->page->aktiviteter = $this->model->getAllAktiviteter();
            $this->page->setTemplate('editAktivitetTilmeldinger');

        } else {
            $this->model->updateTilmeldinger($deltager, $this->page->request->post) ? $this->successMessage('Deltageren blev opdateret.') : $this->errorMessage('Kunne ikke opdatere deltageren.');
            $this->log("Deltager #{$deltager->id}'s tilmeldingsvalg blev opdateret af {$this->model->getLoggedInUser()->user}", 'Deltager', $this->model->getLoggedInUser());
            $this->hardRedirect($this->url('visdeltager', array('id' => $deltager->id)));
        }
    }

    /**
     * updates a deltagers activities
     *
     * @access public
     * @return void
     */
    public function updateAktiviteter()
    {
        $user = $this->model->getLoggedInUser();
        if (!($user->hasRole('Activity-admin') || $user->hasRole('Infonaut') || $user->hasRole('admin') || $user->user == 'twobias@gmail.com')) {
            $this->hardRedirect($this->url('no_access'));
        }

        if (empty($this->vars['id']) || !($deltager = $this->model->findDeltager($this->vars['id']))) {
            $this->page->setTemplate('noResults');
            return;
        }

        if ($deltager->activity_lock === 'ja') {
            $this->errorMessage('Deltagerens aktiviteter kan ikke ændres.');
            $this->hardRedirect($this->url('visdeltager', array('id' => $deltager->id)));
        }

        if (!$this->page->request->isPost()) {
            $this->page->deltager = $deltager;
            $activities = $this->model->getAllAktiviteter();
            usort($activities, function($a, $b) { 
                return strcmp($a->navn, $b->navn);
            });
            $this->page->aktiviteter = $activities;

            $this->page->setTemplate('editAktiviteter');
        } else {
            $this->model->updateAktiviteter($deltager, $this->page->request->post) ? $this->successMessage('Deltageren blev opdateret.') : $this->errorMessage('Kunne ikke opdatere deltageren.');
            $this->log("Deltager #{$deltager->id}'s aktiviteter blev opdateret af {$this->model->getLoggedInUser()->user}", 'Deltager', $this->model->getLoggedInUser());
            $this->hardRedirect($this->url('visdeltager', array('id' => $deltager->id)));
        }
    }
    //}}}


    /**
     * deletes a deltager
     *
     * @access public
     * @return void
     */
    public function deleteDeltager() {
        if (empty($this->vars['id']) || !($deltager = $this->model->findDeltager($this->vars['id'])))
        {
            $this->page->setTemplate('noResults');
            return;
        }

        if ($this->page->request->isPost())
        {
            $post = $this->page->request->post;
            if ($post->delete_deltager)
            {
                $navn = "{$deltager->fornavn} {$deltager->efternavn}";
                if ($deltager->delete()) {
                    $this->log("Deltager #{$this->vars['id']} - {$navn} - blev slettet af {$this->model->getLoggedInUser()->user}", 'Deltager', $this->model->getLoggedInUser());
                    $this->hardRedirect($this->url('deltagerehome'));
                }
                else
                {
                    $this->errorMessage('Kunne ikke slette deltageren.');
                    $this->hardRedirect($this->url('visdeltager', array('id' => $deltager->id)));
                }
            }
        }
        else
        {
            $this->hardRedirect($this->url('visdeltager', array('id' => $deltager->id)));
        }
    }

    /**
     * provides a list of GMs
     *
     * @access public
     * @return void
     */
    public function listGMs()
    {
        $this->page->activity_list = $this->model->getAllAktiviteter();
        $this->page->gm_list = $this->model->getGMList();
        $this->page->model = $this->model;
    }

    /**
     * provides a list of GMs assigned to an activity
     *
     * @access public
     * @return void
     */
    public function listAssignedGMs()
    {
        $this->page->layout_template = "external.phtml";
        $this->page->setTemplate('external_pass');
        if (!($deltager = $this->model->findDeltager($this->vars['id'])) || $deltager->annulled === 'ja') {
            $this->page->setTemplate('noResults');
            return;
        }

        if ($deltager->getBrugerkategori()->navn != 'Forfatter') {
            $this->errorMessage("Kun forfattere har adgang til denne side.");
            return;
        }

        if ($this->externalLogin($deltager)) {
            $this->page->activity_list = $this->model->getAllAktiviteter();
            $this->page->gm_list = $this->model->getGMList();
            $this->page->model = $this->model;

            $this->page->setTemplate('listassignedgms');
            $this->log("Deltager #{$deltager->id} har tjekket fordelte spilledere på den eksternt tilgængelige side", "Deltager", null);
        }
    }

    /**
     * displays all deltagere, sorted after karma
     *
     * @access public
     * @return void
     */
    public function karmaList()
    {
        $this->page->data = $this->model->getKarmaSortedData();
    }

    /**
     * shows lists of people who purchased food
     *
     * @access public
     * @return void
     */
    public function showBoughtFood()
    {
        if (empty($this->vars['type']))
        {
            $this->page->setTemplate('noResults');
            return;
        }
        $madtider = explode('-', $this->vars['type']);
        $deltagere = $this->model->findGamersForMadtidId($madtider);
        $columns = array('id', 'navn', 'madtype', 'udeblevet');
        $this->saveFoundUsers($deltagere, $columns);
        $search_vars = $this->genSearchVars();
        foreach ($madtider as $m)
        {
            $search_vars['mad_search']['mt_' . $m] = 'ja';
        }
        $search_vars['incremental'] = true;
        if (empty($deltagere))
        {
            $this->page->setTemplate('noResults');
        }
        else
        {
            $this->page->columns = $columns;
            $this->page->sort_vars = array();
            $this->page->deltagere = $deltagere;
            $this->page->search_vars = $search_vars;
            $this->page->show_helpers = true;
            $this->page->form_url = $this->url('vis_alle_deltagere');
            $this->page->model = $this->model;
            $this->page->sorted = '';
            $this->page->setTemplate('visAlle');
        }

    }

    /**
     * displays a list of participants, for easy printing
     *
     * @access public
     * @return void
     */
    public function printList()
    {
        $this->page->layout_template = 'printlist.phtml';
        $result = $this->getPreviousResultArray();
        $this->page->result = $result;
        $this->page->headers = array_keys(reset($result));
        $this->page->setTemplate('visList');
    }

    /**
     * displays a character sheet for one or more participants
     *
     * @access public
     * @return void
     */
    public function spillerSedler()
    {
        if (empty($this->vars['id_range']))
        {
            $this->page->setTemplate('noResults');
            return;
        }

        $this->page->con_start = $this->config->get('con.start');
        $this->page->con_end   = $this->config->get('con.end');

        $this->page->layout_template = 'printsheet.phtml';
        $ids = explode('-', $this->vars['id_range']);
        $this->page->deltagere = $this->model->getDeltagereUsingArray($ids);
        $this->model->generateParticipantBarcodes($this->page->deltagere);
        $this->page->setTemplate('displayCharacterSheet');
    }

    /**
     * outputs an EAN8 badge as a png
     *
     * @access public
     * @return void
     */
    public function ean8Badge()
    {
        if (empty($this->vars['participant_id'])) {
            header('HTTP/1.1 400 Bad request');
            exit;
        }

        if ($filename = $this->model->generateParticipantBadge($this->vars['participant_id'])) {
            header('HTTP/1.1 200 Done');
            header('Content-Type: image/png');
            header('Content-Length: ' . filesize($filename));
            readfile($filename);
            exit;
        } else {
            header('HTTP/1.1 500 fail');
            exit;
        }
    }

    /**
     * outputs an EAN8 barcode as a png
     *
     * @access public
     * @return void
     */
    public function ean8Barcode()
    {
        if (empty($this->vars['participant_id'])) {
            header('HTTP/1.1 400 Bad request');
            exit;
        }

        if ($filename = $this->model->generateEan8Barcode($this->vars['participant_id'])) {
            header('HTTP/1.1 200 Done');
            header('Content-Type: image/png');
            readfile($filename);
            exit;
        } else {
            header('HTTP/1.1 500 fail');
            exit;
        }
    }

    /**
     * outputs an EAN8 barcode as a png
     * for the character sheets
     *
     * @access public
     * @return void
     */
    public function ean8Sheet()
    {
        if (empty($this->vars['participant_id'])) {
            header('HTTP/1.1 400 Bad request');
            exit;
        }

        if ($filename = $this->model->generateEan8SheetBarcode($this->vars['participant_id'])) {
            header('HTTP/1.1 200 Done');
            header('Content-Type: image/png');
            header('Content-Length: ' . filesize($filename));
            readfile($filename);
            exit;
        } else {
            header('HTTP/1.1 500 fail');
            exit;
        }
    }

    /**
     * outputs a small EAN8 barcode as a png
     *
     * @access public
     * @return void
     */
    public function ean8SmallBarcode()
    {
        if (empty($this->vars['participant_id'])) {
            header('HTTP/1.1 400 Bad request');
            exit;
        }

        if ($filename = $this->model->generateSmallEan8Barcode($this->vars['participant_id'])) {
            header('HTTP/1.1 200 Done');
            header('Content-Type: image/png');
            readfile($filename);
            exit;
        } else {
            header('HTTP/1.1 500 fail');
            exit;
        }
    }

    public function showSignupDetails()
    {
        if (!($deltager = $this->model->findDeltager($this->vars['id'])) || $deltager->annulled === 'ja') {
            $this->page->setTemplate('noResults');
            return;
        }

        // Set page layout without header and nice rounded boxes
        $this->page->layout_template = "external.phtml";

        // Login first
        if (!$this->externalLogin($deltager)) {
            $this->page->setTemplate(!$deltager->speaksDanish() ? 'external_pass_en' : 'external_pass');
            return;
        }
        
        $this->page->deltager       = $deltager;
        $this->page->deltager_info  = $this->model->findDeltagerInfo($deltager);
        $this->page->sleep_data     = $this->model->getSleepDataForParticipant($this->page->deltager);
        $this->page->setTemplate(!$deltager->speaksDanish() ? 'external_en' : 'external');
        $this->log("Deltager #{$deltager->id} har tjekket sine detaljer på den eksternt tilgængelige side", "Deltager", null);
    }

    /**
     * login check for external access to details
     *
     * @access protected
     * @return bool
     */
    protected function externalLogin(Deltagere $deltager)
    {
        $session    = $this->dic->get('Session');
        $login_time = $session->login_time ? $session->login_time : time();
        if ($this->page->request->isPost() && $login_time > time()) {
            $this->errorMessage("Ideen var at vente et stykke tid inden du prøvede at logge ind igen - timeren er blevet resat.");
            $session->login_time = $session->login_velocity + time();
            return false;
        } elseif ($this->page->request->isPost() && $login_time <= time()) {
            if ($this->page->request->post->password == $deltager->password) {
                $session->password = $this->page->request->post->password;
                return true;
            } else {
                $session->password       = null;
                $session->login_velocity = $session->login_velocity ? $session->login_velocity * 2 : 2;
                $session->login_time     = $session->login_velocity + time();
                $this->errorMessage("Forkert brugerkode for deltager. Du kan prøve at logge ind igen om " . $session->login_velocity . " sekunder.");
                return false;
            }
        } elseif ($session->password == $deltager->password) {
            return true;
        }

        return false;
    }

    public function showSignupDetailsJson()
    {
        if (!($deltager = $this->model->findDeltager($this->vars['id']))) {
            echo json_encode(array('error' => 'Ingen deltager med det id fundet'));
            exit;
        } elseif ($this->page->request->post->password == $deltager->password || 1 == 1) {
            $info = $this->model->findDeltagerInfo($deltager);
            $d_array = array();
            foreach ($deltager->getColumns() as $column) {
                if (in_array($column, array('admin_note', 'brugerkategori_id', 'rel_karma', 'abs_karma'))) {
                    continue;
                }

                $d_array[$column] = $deltager->$column;
            }

            $d_array['kategori']   = $deltager->getBrugerkategori()->navn;
            $d_array['sovelokale'] = $deltager->getSoveplads()->beskrivelse;
            $indgang = array();
            foreach ($deltager->getIndgang() as $i) {
                $indgang[] = $i->type;
            }

            $mad = array();
            foreach ($deltager->getMadtider() as $madtid) {
                $mad[] = array($madtid->getMad()->kategori => $madtid->dato);
            }

            $wear = array();
            foreach ($deltager->getWear() as $w) {
                $wear[$w->getWear()->navn] = array('size' => $w->getSizeName(), 'count' => $w->antal);
            }

            $aktiviteter = array();
            foreach ($deltager->getPladser() as $p) {
                $aktiviteter[] = array('start' => $p->getAfvikling()->start, 'slut' => $p->getAfvikling()->slut, 'afvikling_id' => $p->getAfvikling()->id, 'role' => $p->type, 'name' => $p->getAktivitet()->navn);
            }

            $gds = array();
            foreach ($deltager->getGDSVagter() as $g) {
                $gds[] = array('start' => $g->start, 'slut' => $g->slut, 'name' => $g->getGDSName());
            }

            echo json_encode(array(
                'deltager'      => $d_array,
                'wear'          => $wear,
                'indgang'       => $indgang,
                'mad'           => $mad,
                'aktiviteter'   => $aktiviteter,
                'gds'           => $gds,
            ));
            $this->log("Deltager #{$deltager->id} har hentet sine detaljer via JSON web service", "Deltager", null);
            exit;
        } else {
            echo json_encode(array('error' => 'Forkert password'));
            exit;
        }
    }

    /**
     * displays a dialogue with options for SMS sending
     *
     * @access public
     * @return void
     */
    public function displaySMSDialog()
    {
        if (!($this->model->getLoggedInUser()->hasRole('Admin') || $this->model->getLoggedInUser()->hasRole('SMS'))) {
            $this->errorMessage('Du har ikke adgang til at sende beskeder');
            $this->hardRedirect('deltagerehome');
        }

        $receivers = $this->model->getSavedSearchResult();
        foreach($receivers as $index => $reciever) {
            if (empty($reciever->gcm_id)) unset($receivers[$index]);
        }

        $this->page->receivers = $receivers;
    }

    /**
     * displays a dialogue with options for SMS sending
     *
     * @access public
     * @return void
     */
    public function displayEmailList()
    {
        $this->page->receivers = $this->model->getSavedSearchResult();
    }

    /**
     * sends SMSes
     *
     * @access public
     * @return void
     */
    public function sendSMSes()
    {
        $post = $this->page->request->post;
        if (empty($post->sms_afsender) || empty($post->sms_besked_da) || empty($post->sms_besked_en) || !($this->model->getLoggedInUser()->hasRole('Admin') || $this->model->getLoggedInUser()->hasRole('SMS'))) {
            $this->errorMessage("SMSerne blev ikke sendt - besked eller afsender manglede.");
            $this->hardRedirect($this->url('sms_dialog'));
        }

        $this->successMessage('Sender beskeder, se log for resultat.');

        header("HTTP/1.0 303 GO");
        header("Location: ".$this->url('show_search_result'));

        // Finish response before sending messages, to avoid timeout
        session_write_close();
        fastcgi_finish_request();
        
        $result = $this->model->sendSMSes($post);

        if (empty($result['success'])) {
            $this->errorMessage("SMSerne kunne ikke sendes.");
        } else {
            $this->successMessage('Sendte ' . $result['success'] . ' beskeder. ' . $result['failure'] . ' beskeder fejlede.');
        }

        $this->log("{$this->model->getLoggedInUser()->user} har sendt {$result['success']} beskeder, med {$result['failure']} fejlede", "Besked", $this->model->getLoggedInUser());
        exit;
    }

    /**
     * kicks off the automatic SMS messaging script
     * that messages people about upcoming activies
     *
     * @access public
     * @return void
     */
    public function runAutomaticSMSMessaging()
    {
    }

    /**
     * displays a summary of economy figures
     *
     * @access public
     * @return void
     */
    public function economyBreakdown()
    {
        $this->page->figures = $this->model->participantFigures();
        $this->page->setTemplate('displayEconomy');
    }

    /**
     * displays info to participants, based on a unique input hash
     *
     * @access public
     * @return void
     */
    public function displayParticipantInfo()
    {
        if (empty($this->vars['hash']) || !($deltager = $this->model->getParticipantFromHash($this->vars['hash'])))
        {
            $this->page->setTemplate('noResults');
            return;
        }
        $this->layout_template = 'external.html';
        $this->page->deltager = $deltager;
    }

    /**
     * displays a table with detailed budget info
     *
     * @access public
     * @return void
     */
    public function detailedBudget() {
        $this->page->participants = $this->model->findAll();
    }

    // {{{ ajax methods

    /**
     * method docblock
     *
     * @param
     *
     * @access public
     * @return void
     */
    public function ajaxParameterSearch()
    {
        $participants = $this->model->searchParticipants($this->page->request->get);
        $this->model->setSearchBaseIds($participants);
        header('HTTP/1.1 200 Done');
        exit;
    }

    /**
     * searches for users and outputs some details for found ones, json encoded
     *
     * @access public
     * @return void
     */
    public function ajaxUserSearch()
    {
        $this->ajaxHeader();
        if (empty($this->vars['term']) || empty($this->vars['vagt_id']) || !($vagt = $this->model->findEntity('GDSVagter', $this->vars['vagt_id'])))
        {
            exit;
        }
        $terms = explode('_', $this->vars['term']);
        $deltagere = $this->model->miniWildCardSearch($terms);
        $output = array();
        foreach ($deltagere as $d)
        {
            $disabled = 'false';
            $maxshifts = 'false';
            if ($d->isBusyBetween($vagt->start, $vagt->slut))
            {
                $disabled = 'true';
            }
            if ($d->hasMaxShifts())
            {
                $maxshifts = 'true';
            }
            $output[] = array(
                'id' => $d->id,
                'navn' => $d->fornavn . ' ' . $d->efternavn,
                'mobil' => $d->mobiltlf,
                'disabled' => $disabled,
                'maxshifts' => $maxshifts
                );
        }
        echo json_encode($output);
        exit;
    }
    // }}}


    public function smsTeamMembers()
    {
        if (!empty($this->vars['shift_id']) && ($shift = $this->model->findEntity('GDSVagter', $this->vars['shift_id'])))
        {
            $this->saveFoundUsers($shift->getParticipants());
            $this->hardRedirect($this->url('sms_dialog'));

        }
        else
        {
            $this->main();
        }
    }

    public function payment() {
        $this->page->setTitle('Betaling');
    }

    public function paymentAjax() {
        $get = $this->page->request->get;
        if (!empty($get->query)) {
            header('Content-Type: text/plain; charset=utf-8');
            $participants = $this->model->findFromNameOrID($get->query);
            echo json_encode($participants);
            exit;
        }
        $post = $this->page->request->post;
        if (!empty($post->participant)) {
            if ($participant = $this->model->findDeltager($post->participant)) {
                $participant->betalt_beloeb = intval($post->amount);
                $participant->paid_note = $post->note;
                $participant->update();
                $this->log("Deltager #{$participant->id} fik opdateret forudbetalt beløb af {$this->model->getLoggedInUser()->user}", 'Deltager', $this->model->getLoggedInUser());
                header('HTTP/1.1 200 Updated');
                exit;
            } else {
                header('HTTP/1.1 400 No such participant');
                echo "Kunne ikke finde deltageren";
                exit;
            }

        }
        header('HTTP/1.1 400 Bad action');
        echo "Aner ikke hvad det er du vil";
        exit;
    }

    public function checkin() {
        $this->page->setTitle('Check in');
    }

    public function checkinAjax() {
        $post = $this->page->request->post;
        if (empty($post->action)) {
            header('HTTP/1.1 400 No action specified');
            exit;
        }
        try {
            switch ($post->action) {
                case 'mark-checkedin':
                    $message = $this->model->markCheckedin($post);
                    break;
                case 'undo-checkedin':
                    $message = $this->model->undoCheckedin($post);
                    break;
                default:
                    throw new FrameworkException("Bad action specified");
            }
            echo $message;
            exit;
        } catch (Exception $e) {
            header('HTTP/1.1 500 Messed up');
            echo $e->getMessage();
            exit;
        }
    }

    /**
     * allows for editing participant types
     *
     * @access public
     * @return void
     */
    public function editTypes()
    {
        $this->page->setTitle('Rediger deltager kategorier');
    }

    /**
     * handles jeditable edits from the participant form
     *
     * @access public
     * @return void
     */
    public function doAjaxEdit()
    {
        if (!$this->page->request->isPost()) {
            header('HTTP/1.1 400 Use post');
            exit;
        }

        if (empty($this->vars['id']) || !($participant = $this->model->findDeltager($this->vars['id']))) {
            header('HTTP/1.1 400 No such participant');
            exit;
        }

        try {
            $output = $this->model->makeJeditableUpdate($participant, $this->page->request->post);
            header('HTTP/1.1 200 Updated');
            header('Content-Type: text/plain; charset: UTF-8');
            echo $output;

        } catch (Exception $e) {
            header('HTTP/1.1 500 Failed');
        }
        exit;
    }

    /**
     * returns the user types in json
     *
     * @access public
     * @return void
     */
    public function ajaxGetUserTypes()
    {
        header('HTTP/1.1 200 Served');
        header('Content-Type: text/plain; charset=UTF-8');
        echo json_encode($this->model->getUserTypeArray());
        exit;
    }

    /**
     * removes a signup activity from a participant
     *
     * @access public
     * @return void
     */
    public function ajaxRemoveSchedule()
    {
        if (!$this->page->request->isPost()) {
            header('HTTP/1.1 400 Bad request');
            exit;
        }

        $post = $this->page->request->post;
        if (empty($post->participant_id) || empty($post->schedule_id)) {
            header('HTTP/1.1 400 Bad request');
            exit;
        }

        $this->model->removeParticipantSchedule($post);

        header('HTTP/1.1 200 Done');
        exit;
    }

    /**
     * ajax method for updating a participants signup choices
     *
     * @access public
     * @return void
     */
    public function ajaxUpdateSchedule()
    {
        if (!$this->page->request->isPost()) {
            header('HTTP/1.1 400 Bad request');
            exit;
        }

        try {
            $new_schedule = $this->model->updateSchedule($this->page->request->post);

            $output = array('status' => 'fine');

            if ($conflicting_schedules = $new_schedule->findConflictingSchedules()) {
                $schedules = array();

                foreach ($conflicting_schedules as $schedule) {
                    $schedules[] = array(
                        'schedule_id' => $schedule->afvikling_id,
                        'name'        => $schedule->getActivity()->navn,
                    );
                }

                $output = array('status' => 'conflict', 'schedules' => $schedules);
            }

            header('HTTP/1.1 200 Done');
            header('Content-Type: text/plain; charset=UTF-8');
            echo json_encode($output);
        } catch (Exception $e) {
            header('HTTP/1.1 500 Failed');
            header('Content-Type: text/plain; charset=UTF-8');
            echo json_encode(array('status' => 'fail', 'message' => $e->getMessage()));
        }

        exit;
    }

    /**
     * ajax method for updating a participants signup choices
     *
     * @access public
     * @return void
     */
    public function ajaxUpdateSchedulePriorities()
    {
        if (!$this->page->request->isPost()) {
            header('HTTP/1.1 400 Bad request');
            exit;
        }

        try {
            $post = $this->page->request->post;
            if (empty($post->schedules)) {
                throw new FrameworkException("No schedules to update");
            }

            $this->model->updateSignupPriorities($post);

            header('HTTP/1.1 200 Done');
        } catch (Exception $e) {
            header('HTTP/1.1 500 Failed');
        }

        exit;
    }

    public function sendSignupEmail()
    {
        if (!($participant = $this->model->findDeltager($this->vars['id']))) {
            $this->hardRedirect('/');
        }

        $this->sendEmailFromSignup($participant);

        header('HTTP/1.1 200 Email sent');
        header('Content-Type: text/plain; charset=UTF-8');

        echo "Email sent";

        exit;
    }

    public function requestSignupEmails()
    {
        if ($this->page->request->isPost()) {
            $post = $this->page->request->post;

            $participant = $this->model->findParticipant($post->id);

            $this->sendEmailFromSignup($participant);

            header('HTTP/1.1 200 Email sent');

        } else {
            header('HTTP/1.1 400 Bad method');
        }

        exit;
    }

    public function sendEmailFromSignup(Deltagere $participant)
    {
        $this->model->setupSignupEmail($participant, $this->page);

        $year = date('Y', strtotime($this->config->get('con.start')));

        $lang = !empty($_GET['lang']) ? $_GET['lang'] : '';

        if ($lang === 'en' || !$participant->speaksDanish()) {
            $title = 'Signup for Fastaval ' . $year;
            $this->page->setTemplate('participant/sendsignupemailen');
        } else {
            $title = 'Tilmelding til Fastaval ' . $year;
            $this->page->setTemplate('participant/sendsignupemail');

        }

        $this->page->con_year  = $year;
        $this->page->next_year = $year + 1;

        $mail = new Mail($this->config);

        $mail->setFrom($this->config->get('app.email_address'), $this->config->get('app.email_alias'))
            ->setRecipient($participant->email)
            ->setSubject($title)
            ->setBodyFromPage($this->page);

        return $mail->send();
    }

    public function resetParticipantPassword()
    {
        if (!($participant = $this->model->getParticipantFromResetPasswordHash($this->vars['hash']))) {
            $this->hardRedirect('/');
        }

        $lang = !empty($_GET['lang']) ? $_GET['lang'] : '';

        $this->page->participant = $participant;
        $this->page->danish      = $participant->speaksDanish() && $lang !== 'en';

        $updated = false;

        if ($this->page->request->isPost()) {
            $post = $this->page->request->post;

            if (!empty($post->password) && !empty($post->password_copy) && mb_strlen($post->password) >= 6 && $post->password === $post->password_copy) {
                $participant->password = $post->password;
                $participant->update();

                $updated = true;

                $this->log("Deltager #{$participant->id} opdaterede sit password", 'Participant', null);
            }

        }

        $this->page->updated = $updated;
    }

    /**
     * Function for sending out payment reminders.
     * Meant to be called by a cron job requesting 'participant/payment-reminder/cron'
     */
    public function cronPaymentReminder() {
        $late = strtotime($this->config->get('con.signupend')) < strtotime('now');
        $this->sendAllPaymentReminders($late);
        exit;
    }

    public function sendAllPaymentReminders($late = false)
    {
        $participants = $this->model->getParticipantsForPaymentReminder(3);
        // echo ($late ? "Late" : "Not Late"). "<br>\n";
        echo "Sender betalings reminder mail til ".count($participants)." deltagere<br>\n";
die("Not actually sending reminders<br>\n");

        // Finish response before sending mails, to avoid timeout
        session_write_close();
        fastcgi_finish_request();

        $count = 0;
        $this->page->banking_fee = $this->model->findBankingFee()->pris;

        $template = $late ? "paymentreminderlate" : "paymentreminderregular";
        $late_text = $late ? "late " : "";
        foreach ($participants as $participant) {
            $this->sendPaymentReminder($participant, $template, $participant->speaksDanish());
            $this->log("System sent ${late_text}payment reminder to participant (ID: " . $participant->id . ')', 'Payment', null);
            $count++;
        }

        $this->log("$late_text payment reminder check done. Sent reminders to " . $count . ' participants', 'Payment', null);
        die();
    }

    public function sendSecondPaymentReminder()
    {
die('Not sending second payment reminders');
        $participants = $this->model->getParticipantsForPaymentReminder();

        $count = 0;
        $this->page->banking_fee = $this->model->findBankingFee()->pris;

        foreach ($participants as $participant) {
            $this->sendPaymentReminder($participant, 'secondpaymentreminder', $participant->speaksDanish());

            $this->log('System sent payment reminder to participant (ID: ' . $participant->id . ')', 'Payment', null);

            $count++;
        }

        $this->log('2 day payment reminder check done. Sent reminders to ' . $count . ' participants', 'Payment', null);

        exit;
    }

    public function sendFinalPaymentReminder() {

        $participants = $this->model->getParticipantsForPaymentReminder(0);
        echo "Sender betalings reminder mail til ".count($participants)." deltagere<br>\n";
die("Not actually sending final reminders<br>\n");

        $year = date('Y');
        $count = 0;
        $this->page->banking_fee = $this->model->findBankingFee()->pris;

        // Finish response before sending mails, to avoid timeout
        session_write_close();
        fastcgi_finish_request();

        foreach ($participants as $participant) {
            $this->sendPaymentReminder(
                $participant, 'finalpaymentreminder',
                $participant->speaksDanish(),
                "Sidste reminder om betaling for Fastaval $year",
                "Final reminder of payment for Fastaval $year",
            );
            $this->log('System sent final payment reminder to participant (ID: ' . $participant->id . ')', 'Payment', null);
            $count++;
        }

        $this->log('Last payment reminder check done. Sent reminders to ' . $count . ' participants', 'Payment', null);
        die();
    }

    protected function sendPaymentReminder($participant, $template, $danish, $danish_title = null, $english_title = null)
    {
        $this->model->setupPaymentReminderEmail($participant, $this->page);

        $year = date('Y', strtotime($this->config->get('con.start')));
        $this->page->year = $year;
        if ($danish) {
            $title = $danish_title ?? 'Reminder: betaling for tilmelding til Fastaval '.$year;
            $this->page->setTemplate('participant/' . $template . '-da');

        } else {
            $title = $english_title ?? 'Reminder: payment for Fastaval '.$year;
            $this->page->setTemplate('participant/' . $template . '-en');
        }

        $mail = new Mail($this->config);

        $mail->setFrom($this->config->get('app.email_address'), $this->config->get('app.email_alias'))
            ->setRecipient($participant->email)
            ->setSubject($title)
            ->setBodyFromPage($this->page);

        return $mail->send();
    }

    /**
     * participant/sendwelcomemail
     */
    public function sendWelcomeMail() {
        $participants = $this->model->getParticipantsForwelcomeMail();
        echo "Sending to ".count($participants)." participants<br>\n";
die("Not actually sending welcome mail\n");
        
        // Finish response before sending mails, to avoid timeout
        session_write_close();
        fastcgi_finish_request();
        set_time_limit(0); // setting this to avoid ending execution when sending a lot of mails

        $count = 0;
        foreach ($participants as $participant) {
            $this->page->id = $participant->id;
            $this->page->code = $participant->password;
            $this->page->name = $participant->getName();

            $year = date('Y', strtotime($this->config->get('con.start')));
            if ($participant->speaksDanish()) {
                $title = $danish_title ? $danish_title : "Fastaval $year - Dit Fastaval Program";
                $this->page->setTemplate('participant/welcomemailda');
            } else {
                $title = $english_title ? $english_title : "Fastaval $year - Your Fastaval Program";
                $this->page->setTemplate('participant/welcomemailen');
            }
    
            $mail = new Mail($this->config);
    
            $mail->setFrom($this->config->get('app.email_address'), $this->config->get('app.email_alias'))
                ->setRecipient($participant->email)
                ->setSubject($title)
                ->setBodyFromPage($this->page);
    
            $mail->send();
    
            $this->log('System sent welcome mail to participant (ID: ' . $participant->id . ')', 'Mail', null);

            $count++;
        }

        $this->log("Welcome mail sent to $count participants", 'Mail', null);

        exit;
    }

    public function sendReviewMail() {
        $participants = $this->model->getParticipantsForwelcomeMail();
        echo "Sending to ".count($participants)." participants<br>\n";
die("Not actually sending review mail\n");
        ignore_user_abort(true);
        flush();
        session_write_close();

        $count = 0;
        foreach ($participants as $participant) {
            if(!$participant->email) continue;

            $this->page->name = $participant->getName();

            $year = date('Y', strtotime($this->config->get('con.start')));
            $this->page->year = $year;
            if ($participant->speaksDanish()) {
                $title = $danish_title ? $danish_title : "Evaluering af Fastaval $year";
                $this->page->setTemplate('participant/reviewda');
            } else {
                $title = $english_title ? $english_title : "Evaluation of Fastaval $year";
                $this->page->setTemplate('participant/reviewen');
            }
    
            $mail = new Mail($this->config);
    
            $mail->setFrom($this->config->get('app.email_address'), $this->config->get('app.email_alias'))
                ->setRecipient($participant->email)
                ->setSubject($title)
                ->setBodyFromPage($this->page);
    
            $mail->send();
    
            $this->log('System sent evaluation mail to participant (ID: ' . $participant->id . ')', 'Mail', null);

            $count++;
        }

        $this->log("Evaluation mail sent to $count participants", 'Mail', null);

        exit;
    }

    public function registerBankTransfer()
    {
        if (!$this->page->request->isPost()) {
            header('HTTP/1.1 400 Bad method');
            exit;

        }

        try {
            $this->model->registerBankTransfer($this->vars['id'], $this->page->request->post);

            header('HTTP/1.1 200 Registered');

        } catch (Exception $e) {
            error_log($e->getMessage());
            header('HTTP/1.1 500 Uh-oh');
        }

        exit;
    }

    public function cancelParticipantSignup()
    {
        $participants = $this->model->getParticipantsForPaymentReminderNoVolunteers($this->model->getParticipantsForPaymentReminder());

        $count = 0;
exit;
        foreach ($participants as $participant) {
            $this->sendPaymentReminder($participant, 'paymentreminderannulled', $participant->speaksDanish(), 'Din tilmelding til Fastaval 2017', 'Your sign up for Fastaval 2017');

            $participant->annulled = 'ja';
            $participant->update();

            $this->log('System sent payment reminder (annulment) to participant (ID: ' . $participant->id . ')', 'Payment', null);
            $this->log('Participant (ID: ' . $participant->id . ') signup marked annulled', 'Payment', null);

            $count++;

        }

        $this->log('Annulment check done. Sent emails to ' . $count . ' participants', 'Payment', null);

        exit;
    }

    /**
     * updates the participants sleeping arrangement for fastaval
     *
     * @access public
     * @return void
     */
    public function updateParticipantSleeping()
    {
        $this->page->sleeping_rooms = $this->model->findAvailableSleepingRooms();
        $this->page->participant_id = $this->vars['id'];

        $this->page->starts = date('Y-m-d 22:00:00', strtotime($this->config->get('con.start')));
        $this->page->ends   = date('Y-m-d 10:00:00', strtotime($this->config->get('con.end')));
    }

    /**
     * handles updates to participants sleeping arrangements
     *
     * @access public
     * @return void
     */
    public function updateParticipantSleepingData()
    {
        if (!$this->page->request->isPost()) {
            $this->hardRedirect($this->url('visdeltager', ['id' => $this->vars['id']]));
        }

        $this->model->removeParticipantSleepData($this->vars['id']);

        if ($this->page->request->post->data) {
            $this->model->updateSleepingData($this->vars['id'], $this->page->request->post->data);
        }

        $this->hardRedirect($this->url('visdeltager', ['id' => $this->vars['id']]));
    }

    /**
     * returns number of vouchers participant should get
     *
     * @access public
     * @return void
     */
    public function checkForVouchers()
    {
        $vouchers = $this->model->checkParticipantsForVouchers($this->vars['participant_id']);

        header('HTTP/1.1 200 done');
        header('Content-Type: application/json; charset=UTF-8');

        echo json_encode(['vouchers' => $vouchers]);

        exit;
    }

    /**
     * check for double bookings
     *
     * @access public
     * @return void
     */
    public function checkForDoubleBookings()
    {
        $this->page->double_booked_participants = $this->model->findDoubleBookedParticipants();
    }

    /**
     * List participants that need a refund
     *
     * @access public
     * @return void
     */
    public function showRefund(){
        $this->page->rfundees = $this->model->findPeopleNeedingRefund();
    }


    /**
     * List participants with the information needed for name tags
     *
     * @access public
     * @return void
     */
    public function nameTagList(){
        $participants = $this->model->findAll();
        $this->model->generateParticipantBarcodes($participants);

        if ($this->page->request->isPost()){
            $post = $this->page->request->post;
        }

        // Handle POST request to download spreadsheet
        if (isset($post->download_tag_list)) {
            // Handle CVS file
            if ($post->filetype === "csv") {
                $data = [ 0 => ["ID", "Kaldenavn", "Pronomen", "Område"]];
                foreach ($participants as $participant) {
                    $work_area = $participant->work_area ? $participant->arbejdsomraade : "";
                    $data[] = [
                        $participant->id,
                        $participant->getNickname(),
                        $participant->getPronoun(),
                        $work_area,
                    ];
                }
                $this->returnCSV($data, "nametag-list");
                exit;
            }

            // Handle Excel spreadsheet
            if ($post->filetype === "xlsx") {
                $file_dir = PUBLIC_PATH .'sheets';
                $file_path = $file_dir .'/nametag_sheet.xlsx';

                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();
                $row = 1;
                foreach ($participants as $participant) {
                    $sheet->setCellValue('A'.$row, $participant->id);
                    
                    $sheet->setCellValue('B'.$row, $participant->getNickname());
                    $sheet->setCellValue('C'.$row, $participant->getPronoun());

                    $barcode = $this->model->generateEan8SheetBarcode($participant->id);

                    $barcode_drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                    $barcode_drawing->setName('Barcode'.$participant->id);
                    $barcode_drawing->setDescription('Barcode'.$participant->id);
                    $barcode_drawing->setPath($barcode); /* put your path and image here */
                    $barcode_drawing->setCoordinates('D'.$row);
                    $barcode_drawing->setHeight(82);
                    $barcode_drawing->setWorksheet($spreadsheet->getActiveSheet());
                    $spreadsheet->getActiveSheet()->getRowDimension($row)->setRowHeight(83,'px');

                    // Extra stuff for organizers
                    if($participant->isArrangoer()){
                        $sheet->setCellValue('E'.$row, $participant->arbejdsomraade);

                        $photo = $this->model->fetchCroppedPhoto($participant);
                        if($photo != '') {
                            $photo_drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                            $photo_drawing->setName('Photo'.$participant->id);
                            $photo_drawing->setDescription('Photo'.$participant->id);
                            $photo_drawing->setPath(PUBLIC_PATH.$photo); /* put your path and image here */
                            $photo_drawing->setCoordinates('F'.$row);
                            $photo_drawing->setHeight(300,'px');
                            $photo_drawing->setWorksheet($spreadsheet->getActiveSheet());
                            $spreadsheet->getActiveSheet()->getRowDimension($row)->setRowHeight(300,'px');
                        }
                        
                    }
                    $row++;
                }

                // Set autosize for collumns
                $spreadsheet->getActiveSheet()->getColumnDimension('A')->setAutoSize(TRUE);
                $spreadsheet->getActiveSheet()->getColumnDimension('B')->setAutoSize(TRUE);
                $spreadsheet->getActiveSheet()->getColumnDimension('C')->setAutoSize(TRUE);
                $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(140,'px');
                $spreadsheet->getActiveSheet()->getColumnDimension('E')->setAutoSize(TRUE);
                $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(210,'px');
                
                $writer = new Xlsx($spreadsheet);
                if (!file_exists($file_dir)) {
                    mkdir($file_dir, 0777, true);
                }
                $writer->save($file_path);

                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="name-tag-list.xlsx"');
                header('Cache-Control: max-age=0');
                readfile($file_path);
                exit;
            }

            $this->errorMessage('Ukendt filtype '. $post->filetype);
        }

        // Handle POST request to download images
        if (isset($post->download_tag_images)) {
            // Path and file names
            $path = PUBLIC_PATH . 'uploads/';
            $archive = $path.'nametag-images.zip';

            // create new zip archive
            $zip = new ZipArchive();
            $zip->open($archive, ZIPARCHIVE::CREATE );
            
            if ($post->images === "both" || $post->images === "photos") {
                $dir = "";
                if ($post->images === "both") {
                    $zip->addEmptyDir('fotos');
                    $dir = "fotos/";
                }

                // Get all croped photos
                $photos = glob($path.'photo-cropped-*');
                foreach ($photos as $photo){
                    // Get identifier and file type from photo
                    preg_match("/photo-cropped-(.+)\.(.+)/", $photo, $matches);
                    $identifier = $matches[1];
                    $filetype = $matches[2];

                    // Find the participant(organizer) the photo belongs to 
                    if ($participant = $this->model->getParticipantFromPhotoidentifier($identifier)){
                        // Add photo to zip file, with a more useful name
                        $name = preg_replace("/\s+/","_", $participant->getName() );
                        $filename = "{$participant->id}_$name.$filetype";
                        $zip->addFile($photo,$dir.$filename);
                    }
                }
            }

            if ($post->images === "both" || $post->images === "barcodes") {
                $dir = "";
                if ($post->images === "both") {
                    $zip->addEmptyDir('stregkoder');
                    $dir = "stregkoder/";
                }

                foreach ($participants as $participant) {
                    // Get barcode filename
                    $barcode = $this->model->generateEan8SheetBarcode($participant->id);
                    // Get filetype
                    preg_match("/\.(.+)$/", $barcode, $matches);
                    $filetype = $matches[1];
                    // Create useful filename for barcode
                    $name = preg_replace("/\s+/","_", $participant->getName() );
                    $filename = "{$participant->id}_$name.$filetype";
                    // Add barcode to zip file
                    $zip->addFile($barcode,$dir.$filename);
                } 
            }

            // Close the archive
            $zip->close();
            $attachement = "nametag_images_{$post->images}_".date("Ymd").".zip";
            // Set headers for downloading the zip file
            header("Content-type: application/zip"); 
            header("Content-Disposition: attachment; filename=$attachement"); 
            header("Pragma: no-cache"); 
            header("Expires: 0");
            // Add zip file to the response
            readfile($archive);
            // Delete the zip file afterwards
            unlink($archive);
            exit;
        }
        
        $photos = [];
        foreach ($participants as $participant) {
            $photos[$participant->id] = $this->model->fetchCroppedPhoto($participant);
        }

        $this->page->participants = $participants;
        $this->page->photos = $photos;
    }

    /**
     * Page for automatically registering payments from mobilepay
     *
     * @access public
     * @return void
     */
    function registerMobilepay() {
        if (!$this->model->getLoggedInUser()->hasRole('Admin')) {
            $this->errorMessage('Kun admin kan lave batch registrering af betalinger');
            $this->hardRedirect($this->url('deltagerehome'));
        }

        // if it's not a post request, don't do anything
        if (!$this->page->request->isPost()){
            return;
        }
        $session = $this->dic->get('Session');
        $post = $this->page->request->post;

        if (isset($post->importpayments)) {
            // Did the user submit a file
            $file = isset($_FILES['payments']) ? $_FILES['payments'] : null;
            if($file == null || $file['error'] == UPLOAD_ERR_NO_FILE) {
                $this->errorMessage('Ingen fil valgt.');
                return;
            }

            $sheet_data = $this->model->parsePaymentSheet($file);
            $session->payment_data = $this->model->matchPayments($sheet_data);
        }

        $this->page->registerEarlyLoadJS('register_mobilepay.js');
        $this->page->payment_data = $session->payment_data;
    }

    public function ajaxConfirmPayment(){
        // if it's not a post request, don't do anything
        if (!$this->page->request->isPost()){
            header("HTTP/1.0 400 Only accepts POST requests");
            exit;
        }

        $list = $this->page->request->post->list;
        if (!is_array($list) && empty($list)){
            header("HTTP/1.0 400 list is empty or wrong format");
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode($list);
            exit;
        }

        $result = [];
        foreach($list as $entry) {
            $pid = $entry['participant'];
            $tid = $entry['transaction'];
            $status = $this->model->confirmPayement($pid,$tid);
            $result[] = [
                'participant' => $pid,
                'transaction' => $tid,
                'status' => $status
            ];
        }
        
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($result);
        exit;
    }

    public function downloadSpreadSheet() {
        
        if($this->page->request->isGet()) {
            if(!isset($this->page->request->get->ids)) return;
            $id_ranges = $this->page->request->get->ids;
        }

        if($this->page->request->isPost()) {
            if(!isset($this->page->request->post->ids)) return;
            $id_ranges = $this->page->request->post->ids;
        }
        
        $participants = $this->model->findParticipantsByIds(explode("-",$id_ranges));

        if (isset($this->page->request->post->exportparticipantlist)) {
            header('Content-Type: text/csv;charset=utf-8');
            header('Content-Disposition: attachment;filename="deltagere.csv"');
            header('Cache-Control: max-age=0');
            
            echo chr(0xEF).chr(0xBB).chr(0xBF); // UTF8 BOM
            echo "\"ID\";";
            echo "\"Navn\";";
            echo "\"Kaldenavn\";";
            echo "\"Fødselsdato\";";
            echo "\"Kontakt navn\";";
            echo "\"Kontakt telefon\";";
            echo "\"Kontakt mail\";";
            echo "\"Kommentar\";";
            echo "\"Wear\";";
            echo "\"Aktiviteter (tilmeldt)\";";
            echo "\n";

            foreach($participants as $p) {
                echo "\"{$p->id}\";";
                echo "\"{$p->getName()}\";";
                echo "\"{$p->nickname}\";";
                $birth = preg_replace("/ \d{2}:\d{2}:\d{2}/", "", $p->birthdate);
                echo "\"{$birth}\";";
                $contact = explode("\n", ($p->note->junior_ward->content ?? ""));
                echo "\"".($contact[0] ?? "")."\";";
                echo "\"".($contact[1] ?? "")."\";";
                echo "\"".($contact[2] ?? "")."\";";
                echo "\"".preg_replace("/\R/", " ",($p->note->junior_comment->content ?? ""))."\";";
                $wear = "";
                foreach($p->getWear() as $w) {
                    $wear.= $w->getWearName().($w->size != 1 ? " - ".$w->getSizeName() : "").", ";
                }
                echo "\"{$wear}\";";

                $tilmeldinger = $p->getTilmeldinger();
                uasort($tilmeldinger, function($a, $b) {
                  return $a->prioritet - $b->prioritet;
                });

                $signup = "";
                foreach($tilmeldinger as $t) {
                    $activity = $t->getAktivitet();
                    $afvikling = $t->getAfvikling();
                    preg_match("/\d{4}-\d{2}-\d{2} (\d{2}:\d{2}):\d{2}/", $afvikling->start,$start);
                    preg_match("/\d{4}-\d{2}-\d{2} (\d{2}:\d{2}):\d{2}/", $afvikling->slut,$end);
                    $signup = "$t->prioritet. prio $activity->navn ($start[1] - $end[1])";
                    echo "\"{$signup}\";";
                }
                
                echo "\n";
            }
            exit;
        }

        $this->page->id_ranges = $id_ranges;
        $this->page->participants = $participants;
    }

    public function anonymizeParticipants() {
die ("This page has to be enabled by someone with access to the server code");

        if($this->page->request->isPost()) {
            $this->page->post = true;
            $keep = array_keys($this->page->request->post->getRequestVarArray());
            $this->page->result = $this->model->anonymizeParticipants($keep);

            // Deleting files related to participants
            $cache = CACHE_FOLDER;
            // $badges = glob($cache."/<something>");
            
            $signups = glob(SIGNUP_FOLDER."data/*.json");

            $uploads = PUBLIC_PATH."uploads/";
            $photos = glob($uploads."photo*");

            $deleted = [];
            $skipped = [];
            $files = array_merge($signups, $photos);
            foreach($files as $file){ // iterate files
                if(is_file($file) && unlink($file)) { // delete file
                    $deleted[] = $file;
                } else {
                    $skipped[] = $file;
                }
            }
            $this->page->result->files = $deleted;
            if (!empty($skipped)) {
                $this->page->result->success = false;
                $this->page->result->errors[] = [
                    'id' => 'files',
                    'desc' => 'There was an error deleting files',
                    $data => $skipped,
                ];
            }

            return;
        }

        $con_end = new DateTime($this->config->get("con.end"));
        $difference = $con_end->diff(new DateTime('now'));

        // Always keep these as default
        $keep = [
            'postnummer',
            'by',
            'birthdate',
            'country',
        ];

        // Keep these for membership database
        if ($difference->y < 6 ) {
            $keep = array_merge($keep, [
                'fornavn',
                'efternavn',
                'email',
                'adresse1',
                'adresse2',
            ]);
        }

        // Keep these for the last con
        if ($difference->y < 1 ) {
            $keep = array_merge($keep, [
                'skills',
                'arrangoer_naeste_aar',
            ]);
        }
        
        $this->page->default_keep = $keep;
        $this->page->fields = $this->model->getDeltagerFieldsWithNames();
    }

}
