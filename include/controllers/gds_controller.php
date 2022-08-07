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
 * handles participant duties
 *
 * @category Infosys
 * @package  Controllers 
 * @author   Peter Lind <peter.e.lind@gmail.com>
 * @license  http://www.gnu.org/licenses/gpl.html GPL 3
 * @link     http://www.github.com/Fake51/Infosys
 */
class GdsController extends Controller
{
    protected $prerun_hooks = array(
        array('method' => 'checkUser','exclusive' => true, 'methodlist' => ['listShiftsExternal']),
    );

    /**
     * Default method of the class
     *
     * @access public
     * @return void
     */
    public function main() {
        $this->viewDay(0);
    }

    /**
     * allows for editing a diy category
     *
     * @access public
     * @return void
     */
    public function editCategory()
    {
        if (!($diy = $this->model->findGDS($this->vars['gds_id']))) {
            $this->errorMessage("Kunne ikke finde GDS kategorien");
            $this->hardRedirect($this->url('gdshome'));
        }

        if ($this->page->request->isPost()) {
            $post = $this->page->request->post;

            $messages = $this->model->updateDIY($diy, $post);

            $this->hardRedirect($this->url('gds_category', array('gds_id' => $diy->id)));
        }


        $this->page->diy = $diy;
    }

    /**
     * displays gds dutities for a given day
     *
     * @param string $date - date to show shifts for, defaults to first available day
     *
     * @access public
     * @return void
     */
    public function viewDay($date = null)
    {
        $dates = $this->model->getGDSDates();

        if (empty($this->vars['date']) && !$date) {
            $date = $dates[0];

        } else {
            $date = (($date) ? $date : $this->vars['date']);

            if (!in_array($date, $dates)) {
                $date = $dates[0];
            }
        }

        $this->page->gds_dates = $dates;
        $this->page->selected_date = $date;
        $this->page->gds_categories = $this->model->getGDSCategories();
        $this->page->shifts = $this->model->getGDSShiftsForDate($date);
        $this->page->setTemplate('calendar');
        $this->page->con_start = new DateTime($this->config->get('con.start'));
    }

    /**
     * displays all gds categories and
     * allows for creation of new
     *
     * @access public
     * @return void
     */
    public function categories()
    {
        $this->page->categories = $this->model->getGDSCategories();
    }

    /**
     * outputs a string of <option> elements containing groups running an activity
     *
     * @access public
     * @return void
     */
    public function ajaxGetGDSTider()
    {
        $this->ajaxHeader();
        if (empty($this->vars['gds_id']) || !($category = $this->model->findEntity('GDS', $this->vars['gds_id']))) {
            exit;
        } else {
            $deltager = false;
            if (!empty($this->vars['deltager_id'])) {
                $deltager = $this->model->findEntity('Deltagere', $this->vars['deltager_id']);
            }

            $result = $category->getShifts();

            usort($result, function ($a, $b) {
                return strtotime($a->start) < strtotime($b->start) ? -1 : 1;
            });

            if (!empty($result)) {
                echo "{\"pairs\":[";

                $results = array();

                foreach ($result as $vagt) {
                    $disabled = 'false';

                    if ($deltager && $deltager->isBusyBetween($vagt->start, $vagt->slut)) {
                        $disabled = 'true';
                    }

                    $results[] = '{"value": "' . $vagt->id . '", "disabled": ' . $disabled . ', "text": "' . danishDayNames(date('D H:i', strtotime($vagt->start))) . "-" . date('H:i', strtotime($vagt->slut)) . '"}';
                }

                echo implode(',', $results) . "]}";
            }
        }
        exit;
    }

    /**
     * outputs a string of <option> elements containing groups running an activity
     *
     * @access public
     * @return void
     */
    public function ajaxGetGDSPeriods()
    {
        $this->ajaxHeader();
        if (empty($this->vars['gds_id']) || !($category = $this->model->findEntity('GDSCategory', $this->vars['gds_id']))) {
            exit;
        } else {
            $deltager = false;
            if (!empty($this->vars['deltager_id'])) {
                $deltager = $this->model->findEntity('Deltagere', $this->vars['deltager_id']);
            }

            $result  = $category->getShifts();

            usort($result, function ($a, $b) {
                return strtotime($a->start) < strtotime($b->start) ? -1 : 1;
            });

            $periods = array();
            if (!empty($result)) {
                echo '{"pairs": [{"text": "Vælg", "value": ""}';
                foreach ($result as $vagt) {
                    $period   = $vagt->getPeriod();

                    if (in_array($period, $periods)) {
                        continue;
                    }

                    $disabled = 'false';
                    if ($deltager && $deltager->isBusyBetween($vagt->start, $vagt->slut)) {
                        $disabled = 'true';
                    }

                    echo ',{"value": "' . $vagt->getPeriod() . '", "disabled": ' . $disabled . ', "text": "' . danishDayNames($vagt->getMeaningfulPeriod()) . '"}';

                    $periods[] = $period;
                }

                echo "]}";
            }
        }
        exit;
    }

    /**
     * outputs json of participants signed up for a GDS shift
     *
     * @access public
     * @return void
     */
    public function ajaxGetSignups()
    {
        $this->ajaxHeader();

        if (empty($this->vars['vagt_id'])) {
            exit;
        }

        $output = $this->model->fetchDiyShiftSignups($this->vars['vagt_id']);

        echo json_encode($output);

        exit;
    }


    /**
     * adds participants to a gds shift
     *
     * @access public
     * @return void
     */
    public function ajaxAddToShift()
    {
        $this->ajaxHeader();
        if (empty($this->vars['vagt_id']) || !($vagt = $this->model->findEntity('GDSVagter', $this->vars['vagt_id'])) || empty($this->vars['id_string']))
        {
            exit;
        }
        $ids = explode('-', $this->vars['id_string']);
        $added = array();
        foreach ($ids as $id)
        {
            if (($d = $this->model->findEntity('Deltagere', $id)) && $vagt->addParticipant($d))
            {
                $this->log("Deltager #{$d->id} blev sat på GDS-Vagt #{$vagt->id} ({$vagt->getGDSName()}) af {$this->model->getLoggedInUser()->user}", 'GDS', $this->model->getLoggedInUser());
                $added[] = $d;
            }
            else
            {
                foreach ($added as $d)
                {
                    $this->log("Deltager #{$d->id} blev fjernet fra GDS-Vagt #{$vagt->id} ({$vagt->getGDSName()}) af {$this->model->getLoggedInUser()->user}", 'GDS', $this->model->getLoggedInUser());
                    $vagt->removeParticipant($d);
                }
                echo "Failed";
                exit;
            }
        }
        echo 'worked';
        exit;

    }


    /**
     * removes participants from a gds shift
     *
     * @access public
     * @return void
     */
    public function ajaxRemoveFromShift()
    {
        $this->ajaxHeader();
        if (empty($this->vars['vagt_id']) || !($vagt = $this->model->findEntity('GDSVagter', $this->vars['vagt_id'])) || empty($this->vars['id_string']))
        {
            exit;
        }
        $ids = explode('-', $this->vars['id_string']);
        $added = array();
        $status = true;
        foreach ($ids as $id)
        {
            if ($d = $this->model->findEntity('Deltagere', $id))
            {
                $vagt->removeParticipant($d);
                $this->log("Deltager #{$d->id} blev fjernet fra GDS-Vagt #{$vagt->id} ({$vagt->getGDSName()}) af {$this->model->getLoggedInUser()->user}", 'GDS', $this->model->getLoggedInUser());
            }
            else
            {
                $status = false;
            }
        }
        echo (($status) ?  'worked' : 'failed');
        exit;

    }

    /**
     * toggles the no-show status for a participant
     * and a given diy shift
     *
     * @access public
     * @return void
     */
    public function ajaxMarkNoshow()
    {
        if (!$this->page->request->isPost()) {
            header('HTTP/1.1 400 Wrong method');
        }

        $post = $this->page->request->post;
        if (!$post->participant_id || !$post->shift_id) {
            header('HTTP/1.1 400 Lacking post values');
        }

        try {
            $this->model->updateNoshowStatus($post);

            header('HTTP/1.1 200 Done');

        } catch (FrameworkException $e) {
            $e->logException();

            header('HTTP/1.1 500 Fail');
        }

        exit;
    }

    /**
     * ups the counter for how many times
     * a participant has been contacted about
     * extra diy shifts
     *
     * @access public
     * @return void
     */
    public function ajaxMarkContacted()
    {
        if (!$this->page->request->isPost()) {
            header('HTTP/1.1 400 Wrong method');
        }

        $post = $this->page->request->post;
        if (!$post->participant_id) {
            header('HTTP/1.1 400 Lacking post values');
        }

        try {
            $this->model->updateContactCount($post);

            header('HTTP/1.1 200 Done');

        } catch (FrameworkException $e) {
            $e->logException();

            header('HTTP/1.1 500 Fail');
        }

        exit;
    }

    public function listShifts() {
        if (empty($this->vars['id']) || !($gds = $this->model->findGDS($this->vars['id']))) {
            $this->errorMessage("Kunne ikke finde GDS kategorien");
            $this->hardRedirect($this->url('gdshome'));
        }

        $this->page->gds = $gds;
        $this->page->shifts = $gds->getShifts(true);
    }

    public function listShiftsExternal()
    {
        if (empty($this->vars['hash'])) {
            $this->hardRedirect($this->url('home'));
        }

        if (!($code = base64_decode($this->vars['hash'])) || mb_substr($code, 0, 4) !== 'GDS!') {
            $this->hardRedirect($this->url('home'));
        }

        $this->vars['id'] = mb_substr($code, 4);

        $this->page->layout_template = "external.phtml";
        $this->page->setTemplate('listShifts');

        $this->page->no_external_link = true;

        return $this->listShifts();
    }

    /**
     * fetches list of participants best suited for contact
     * for a diy duty shift
     *
     * @access public
     * @return void
     */
    public function getShiftSuggestions()
    {
        if (empty($this->vars['shift_id'])) {
            header('HTTP/1.1 400 Lacking shift id');
            exit;
        }

        try {
            $details = $this->model->getShiftSuggestions($this->vars['shift_id']);

            header('HTTP/1.1 200 Done');
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode($details);

        } catch (FrameworkException $e) {
            $e->logException();

            header('HTTP/1.1 500 Fail');
        }

        exit;
    }

    /**
     * picks out participants for shift and save as search
     * then redirect
     *
     * @access public
     * @return void
     */
    public function showShiftParticipants()
    {
        $participants = $this->model->getParticipantsForShift($this->vars['shift_id']);

        $participant_model = new ParticipantModel($this->dic->get('DB'), $this->config, $this->dic);
        $participant_model->setSearchBaseIds($participants);

        $this->hardRedirect($this->url('show_search_result'));
    }
}
