<?php
/**
 * Copyright (C) 2011-2012  Peter Lind
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
 * PHP version 5.3+
 *
 * @category  Infosys
 * @package   Controllers 
 * @author    Peter Lind <peter.e.lind@gmail.com>
 * @copyright 2009-2012 Peter Lind
 * @license   http://www.gnu.org/licenses/gpl.html GPL 3
 * @link      http://www.github.com/Fake51/Infosys
 */

/**
 * handles api calls
 *
 * @category Infosys
 * @package  Controllers 
 * @author   Peter Lind <peter.e.lind@gmail.com>
 * @license  http://www.gnu.org/licenses/gpl.html GPL 3
 * @link     http://www.github.com/Fake51/Infosys
 */
class ApiController extends Controller
{
    protected $prerun_hooks = array(
        array('method' => 'checkData', 'exclusive' => false, 'methodlist' => array('addWear', 'addGDS', 'addActivity', 'addEntrance', 'parseSignup', 'requestPasswordReminder', 'getConfirmationData')),
    );

    /**
     * checks that the user connecting is authenticated
     *
     * @access public
     * @return void
     */
    public function checkAuth()
    {
        $session = $this->dic->get('Session');
        if (empty($session->api_user) || empty($session->api_key)) {
            header('HTTP/1.1 403 Not authorized for this method');
            exit;
        }
    }

    /**
     * method to create a participant
     *
     * @access public
     * @return void
     */
    public function createParticipant()
    {
        if ($this->page->request->isPost()) {
            $post = $this->page->request->post;

        } else {
            $post = new StdClass();
        }

        $output = $this->model->createParticipant($post);

        $this->jsonOutput($output);
    }

    /**
     * checks that data has been posted right in the request
     *
     * @access public
     * @return void
     */
    public function checkData()
    {
        if (!$this->page->request->isPost()) {
            header('HTTP/1.1 400 Not post request');
            exit;
        }

        $post = $this->page->request->post;
        if (empty($post->data)) {
            if (!($data = file_get_contents('php://input'))) {
                header('HTTP/1.1 400 Bad data');
                exit;
            }
        } else {
            $data = $post->data;
        }

        if (!($json = json_decode($data, true))) {
            header('HTTP/1.1 400 Bad data');
            exit;
        }

        if (isset($json['nameValuePairs'])) {
            $json = $json['nameValuePairs'];
        }

        $this->json = $json;
    }

    /**
     * adds wear to a participants entry
     *
     * @access public
     * @return void
     */
    public function addWear()
    {
        $this->jsonOutput($this->model->addWear($this->json));
    }

    /**
     * adds a chore to a participants entry
     *
     * @access public
     * @return void
     */
    public function addGDS()
    {
        $this->jsonOutput($this->model->addGDS($this->json));
    }

    /**
     * adds an activity to a participants entry
     *
     * @access public
     * @return void
     */
    public function addActivity()
    {
        $this->jsonOutput($this->model->addActivity($this->json));
    }

    /**
     * adds an entry form to a participants entry
     *
     * @access public
     * @return void
     */
    public function addEntrance()
    {
        $this->jsonOutput($this->model->addEntrance($this->json));
    }

    /**
     * parses a json blob from the signup
     * essentially all details in one go
     *
     * @access public
     * @return void
     */
    public function parseSignup()
    {
        // save signup data
        file_put_contents(__DIR__ . '/../signup-data/parse-' . date('Y-m-d_H:i:s'), print_r($this->json, true));

        list($json_output, $participant) = $this->model->parseSignup($this->json);

        if (!empty($json_output['status']) && $json_output['status'] === 'ok') {
            $participant_controller = new ParticipantController($this->route, $this->config, $this->dic);

            $participant_controller->sendEmailFromSignup($participant);
        }

        $this->jsonOutput($json_output);
    }

    /**
     * handles authentication against the API
     *
     * @access public
     * @return void
     */
    public function auth()
    {
        if (!$this->page->request->isPost()) {
            $session            = $this->dic->get('Session');
            $session->api_token = md5(uniqid());
            $session->save();
            $this->jsonOutput(array('token' => $session->api_token));
        } else {
            $session = $this->dic->get('Session');
            $post    = $this->page->request->post;
            $this->checkData();

            if (!$this->model->authenticate($this->json)) {
                header('HTTP/1.1 403 Access denied');
                exit;
            }

            $this->jsonOutput($this->model->generateApiKey());
        }
    }

    /**
     * returns json encoded array of activities
     * as requested through a variable
     *
     * @access public
     * @return void
     */
    public function allActivities()
    {
        $this->vars['all'] = true;
        $this->activities();
    }

    /**
     * returns json encoded array of activities
     * as requested through a variable,
     * - output suited for a mobile app
     *
     * @access public
     * @return void
     */
    public function activitiesForAppV2()
    {
        return $this->activitiesForApp(2);
    }

    /**
     * returns json encoded array of activities
     * as requested through a variable,
     * - output suited for a mobile app
     *
     * @access public
     * @return void
     */
    public function activitiesForAppVersioned()
    {
        return $this->activitiesForApp($this->vars['version']);
    }

    /**
     * returns json encoded array of activities
     * as requested through a variable,
     * - output suited for a mobile app
     *
     * @access public
     * @return void
     */
    public function activitiesForApp($version = 1)
    {
        if (empty($this->vars)) {
            $this->vars['id'] = '*';
        }

        $timestamp = 0;
        if ($this->page->request->get->since) {
            //$timestamp = intval($this->page->request->get->since);
        }

        if (preg_match('/\\d{4}-\\d{2}-\\d{2}/', $this->vars['id'])) {
            $this->jsonOutput($this->model->getActivityDataForDay($this->vars['id'], !empty($this->vars['all']), true, $timestamp, $version));
        } elseif ($this->vars['id'] === '*') {
            $ids = array();
        } elseif (!intval($this->vars['id'])) {
            $this->jsonOutput($this->model->getActivityDataForType($this->vars['id'], !empty($this->vars['all']), true, $timestamp, $version));
        } else {
            $ids = explode(',', $this->vars['id']);
        }

        $this->jsonOutput($this->model->getActivityData($ids, !empty($this->vars['all']), true, $timestamp, $version));
    }

    /**
     * returns json encoded array of activities
     * as requested through a variable
     *
     * @access public
     * @return void
     */
    public function activities()
    {
        if ($this->page->request->isPost()) {
            $this->checkAuth();

            try {
                if (empty($this->vars['id'])) {
                    $this->model->createActivity($_POST);

                } else {
                    $this->model->updateActivity($_POST, $this->vars['id']);

                }
            } catch (Exception $e) {
                header('HTTP/1.1 500 Fail');
                exit;
            }
        }

        if (empty($this->vars)) {
            $this->vars['id'] = '*';
        }

        $timestamp = 0;

        if ($this->page->request->get->since) {
            //$timestamp = intval($this->page->request->get->since);
        }

        $birthdate_timestamp = null;

        if ($this->page->request->get->birthdate) {
            $birthdate_timestamp = strtotime($this->page->request->get->birthdate);
        }

        $participant_type = null;

        if ($this->page->request->get->brugertype) {
            $participant_type = $this->model->parseParticipantType($this->page->request->get->brugertype);
        }

        if (preg_match('/\\d{4}-\\d{2}-\\d{2}/', $this->vars['id'])) {
            $this->jsonOutput($this->model->getActivityDataForDay($this->vars['id'], !empty($this->vars['all']), false, $timestamp));
        } elseif ($this->vars['id'] === '*') {
            $ids = array();
        } elseif (!intval($this->vars['id'])) {
            $this->jsonOutput($this->model->getActivityDataForType($this->vars['id'], !empty($this->vars['all']), false, $timestamp));
        } else {
            $ids = explode(',', $this->vars['id']);
        }

        $this->jsonOutput($this->model->getActivityData($ids, !empty($this->vars['all']), false, $timestamp, 1, $birthdate_timestamp, $participant_type));
    }

    /**
     * returns json encoded array of activity schedules
     * as requested through a variable
     *
     * @access public
     * @return void
     */
    public function schedules() {
        if (preg_match('/\\d{4}-\\d{2}-\\d{2}/', $this->vars['id'])) {
            $this->jsonOutput($this->model->getScheduleStructureForDay($this->vars['id']));

        } elseif ($this->vars['id'] === '*') {
            $ids = array();

        } elseif (!intval($this->vars['id'])) {
            $this->jsonOutput($this->model->getScheduleStructureForType($this->vars['id']));

        } else {
            $ids = explode(',', $this->vars['id']);
        }

        $this->jsonOutput($this->model->getScheduleStructure($ids));
    }

    /**
     * returns json encoded array of activities
     * as requested through a variable
     *
     * @access public
     * @return void
     */
    public function gds()
    {
        if ($this->vars['id'] === '*') {
            $ids = array();
        } else {
            $ids = explode(',', $this->vars['id']);
        }
        $this->jsonOutput($this->model->getGDSStructure($ids));
    }

    /**
     * returns json encoded array of DIY categories
     * as requested through a variable
     *
     * @access public
     * @return void
     */
    public function gdsCategories()
    {
        if ($this->vars['id'] === '*') {
            $ids = array();
        } else {
            $ids = explode(',', $this->vars['id']);
        }
        $this->jsonOutput($this->model->getGDSCategoryStructure($ids));
    }

    /**
     * returns json encoded array of gds shifts
     * as requested through a variable
     *
     * @access public
     * @return void
     */
    public function gdsShift()
    {
        if ($this->vars['id'] === '*') {
            header('HTTP/1.1 400 Bad data');
            exit;
        } else {
            $ids = explode(',', $this->vars['id']);
        }
        $this->jsonOutput($this->model->getGDSShiftStructure($ids));
    }

    /**
     * returns json encoded array of food
     * as requested through a variable
     *
     * @access public
     * @return void
     */
    public function food()
    {
        if ($this->vars['id'] === '*') {
            $ids = array();
        } else {
            $ids = explode(',', $this->vars['id']);
        }
        $this->jsonOutput($this->model->getFoodStructure($ids));
    }

    /**
     * returns json encoded array of entrance info
     * as requested through a variable
     *
     * @access public
     * @return void
     */
    public function entrance()
    {
        if ($this->vars['id'] === '*') {
            $ids = array();
        } else {
            $ids = explode(',', $this->vars['id']);
        }
        $this->jsonOutput($this->model->getEntranceStructure($ids));
    }

    /**
     * returns json encoded array of wear info
     * as requested through a variable
     *
     * @access public
     * @return void
     */
    public function wear()
    {
        if ($this->vars['id'] === '*') {
            $ids = array();
        } else {
            $ids = explode(',', $this->vars['id']);
        }

        $this->jsonOutput($this->model->getWearStructure($ids, $this->page->request->get->brugertype));
    }

    /**
     * outputs json data and sets headers accordingly
     *
     * @param string $data        Data to output
     * @param string $http_status HTTP status code
     *
     * @access protected
     * @return void
     */
    protected function jsonOutput($data, $http_status = '200 Awesome', $content_type = 'text/plain')
    {
        $string = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        header('HTTP/1.1 ' . $http_status);
        header('Content-Type: ' . $content_type . '; charset=UTF-8');
        header('Content-Length: ' . strlen($string));
        echo $string;
        exit;
    }

    /**
     * method docblock
     *
     * @param
     *
     * @access public
     * @return void
     */
    public function fetchGraphData()
    {
        $this->page->layout_template = 'minimal.phtml';
        $data = $this->model->fetchGraphData($this->vars['name']);

        try {
            $this->page->data = json_encode($data);

        } catch (Exception $e) {
            die($e->getMessage());
            header('HTTP/1.1 500 Failed');
            echo "Failed to gather data";
            exit;
        }
    }

    /**
     * outputs the data structure for activities
     *
     * @access public
     * @return void
     */
    public function activityStructure()
    {
        $this->jsonOutput($this->model->getActivityStructure(), '200 Awesome', 'application/json');
    }

    /**
     * sets the proper header to allow cross site
     * access to the api
     *
     * @access public
     * @return void
     */
    public function allowCrossSiteAccess()
    {
        header('Access-Control-Allow-Origin: *');
    }

    /**
     * searches for an activity given a field and a value
     *
     * @access public
     * @return void
     */
    public function activitiesByField()
    {
        $entity = $this->model->findActivityByField($this->vars['field'], $this->vars['value']);
        $output = $entity ? $this->model->formatEntityForJson($entity) : array();
        $output['schedules'] = $entity ? $this->model->getScheduleInfo($entity) : array();
        $this->jsonOutput($output, '200 Awesome', 'application/json');
    }

    /**
     * outputs the schedule for a given user
     *
     * @access public
     * @return void
     */
    public function getUserScheduleV2()
    {
        return $this->getUserSchedule(2);
    }

    /**
     * outputs the schedule for a given user
     *
     * @access public
     * @return void
     */
    public function getUserScheduleVersioned()
    {
        return $this->getUserSchedule($this->vars['version']);
    }

    /**
     * outputs the schedule for a given user
     *
     * @access public
     * @return void
     */
    public function getUserSchedule($version = 1)
    {
        if (empty($this->vars['id']) || !($participant = $this->model->findParticipant($this->vars['id'])) || $participant->annulled === 'ja') {
            header('HTTP/1.1 400 No such user');
            exit;
        }

        $pass = isset($this->page->request->post->pass) ? $this->page->request->post->pass : $this->page->request->get->pass;

        if (!$pass || $participant->password != $pass) {
            header('HTTP/1.1 403 No access');
            exit;
        }

        $this->jsonOutput($this->model->getParticipantSchedule($participant, $version), '200 Awesome', 'application/json');
    }

    /**
     * outputs base data for a given user
     *
     * @access public
     * @return void
     */
    public function getUserData()
    {
        if (empty($this->vars['email']) || !$this->page->request->get->pass) {
            header('HTTP/1.1 400 No such user');
            exit;
        }

        if (!($participant = $this->model->getParticipantByEmailAndPassword($this->vars['email'], $this->page->request->get->pass)) || $participant->annulled === 'ja') {
            header('HTTP/1.1 403 No access');
            exit;
        }

        $this->jsonOutput($this->model->getParticipantBaseData($participant), '200 Awesome', 'application/json');
    }

    /**
     * registers an app for a participant
     *
     * @access public
     * @return void
     */
    public function registerApp()
    {
        if (empty($this->vars['id']) || !($participant = $this->model->findParticipant($this->vars['id'])) || $participant->annulled === 'ja') {
            header('HTTP/1.1 400 No such user');
            exit;
        }

        $this->checkData();

        try {
            $this->model->registerApp($participant, $this->json);

            $this->log("Deltager #{$participant->id} registrerede sin app", 'Api', null);

        } catch (FrameworkException $e) {
            header('HTTP/1.1 500 Fail');
        }

        exit;
    }

    /**
     * unregisters an app for a participant
     *
     * @access public
     * @return void
     */
    public function unregisterApp()
    {
        if (empty($this->vars['id']) || !($participant = $this->model->findParticipant($this->vars['id'])) || $participant->annulled === 'ja') {
            header('HTTP/1.1 400 No such user');
            exit;
        }

        try {
            $this->model->unregisterApp($participant);

            $this->log("Deltager #{$participant->id} afregistrerede sin app", 'Api', null);

        } catch (FrameworkException $e) {
            header('HTTP/1.1 500 Fail');
        }

        exit;
    }

    public function requestPasswordReminder()
    {
        if (!($participants = $this->model->findParticipantsByEmail($this->json['email']))) {
            header('HTTP/1.1 400 No such user');
            exit;
        }

        $title = 'Password reminder';

        foreach ($participants as $participant) {
            if ($participant->annulled === 'ja') {
                continue;
            }

            $this->page->participant = $participant;

            if ($participant->speaksDanish()) {
                $this->page->setTemplate('participant/sendpasswordemailda');

            } else {
                $this->page->setTemplate('participant/sendpasswordemailen');
            }

            $this->page->link = $this->url('participant_reset_password', array('hash' => md5('reset-pw-' . $participant->id . '-' . $participant->password)));

            $html_body = $this->page->render();
            $txt_body  = strip_tags($html_body);

            $mail = new Mail();

            $mail->setFrom($this->config->get('app.email_address'), $this->config->get('app.email_alias'))
                ->setRecipient($participant->email)
                ->setSubject($title)
                ->setBodyFromPage($this->page);

            $mail->send();

        }

        header('HTTP/1.1 200 Emails sent');

        exit;
    }

    public function getConfirmationData()
    {
        // parse input and fill participant like object
        // -- refactor parseSignup in api-model, to separate creation
        //    locating object from parsing data and filling it
        // -- convert all the add* methods in api-model to
        //    methods on the participant/dummy object
        // if parse went ok, render template

        $this->page->participant = $this->model->parseSignupConfirmation($this->json);

        $lang = !empty($_GET['lang']) ? $_GET['lang'] : '';

        if ($lang === 'en' || !$this->page->participant->speaksDanish()) {
            $this->page->setTemplate('confirmationdataen');

        } else {
            $this->page->setTemplate('confirmationdata');
        }

        $participant_model = new ParticipantModel($this->dic->get('DB'), $this->config, $this->dic);
        $participant_model->setupSignupEmail($this->page->participant, $this->page);
        $this->page->layout_template = 'contentonly.phtml';
    }
}
