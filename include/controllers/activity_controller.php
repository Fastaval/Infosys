<?php
/**
 * Copyright (C) 2009-2012  Peter Lind
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
 * handles all activity pages, including showing, editing and updating
 *
 * @category Infosys
 * @package  Controllers 
 * @author   Peter Lind <peter.e.lind@gmail.com>
 * @license  http://www.gnu.org/licenses/gpl.html GPL 3
 * @link     http://www.github.com/Fake51/Infosys
 */
class ActivityController extends Controller
{

    protected $prerun_hooks = array(
        array('method' => 'checkUser', 'exclusive' => true, 'methodlist' => array('gamestartQueue', 'gamestartQueueAjax', 'specifyVote', 'specifyVotePosted', 'confirmVote', 'castVote', 'votingDone')),
    );

    /**
     * default method, results in showing common functionality
     *
     * @access public
     * @return void
     */
    public function main()
    {
        $this->page->setTitle('Aktiviteter');
        $this->page->nextgamestart = $this->model->getNextGamestart();
        $this->page->gamestarts    = $this->model->getAllGamestarts();
    }

    /**
     * fetches and shows all aktiviteter 
     *
     * @access public
     * @return void
     */
    public function visAlle()
    {
        $this->page->setTitle('Vis alle - Aktiviteter');
        $aktiviteter = $this->model->findAll();

        if (empty($aktiviteter)) {
            $this->page->setTemplate('noresults');
        } else {
            $this->page->aktiviteter = $aktiviteter;
        }
    }

    /**
     * displays info on an aktivitet
     * 
     * @access public
     * @return void
     */
    public function visAktivitet()
    {
        if (empty($this->vars['id']) || !($activity = $this->model->findEntity('Aktiviteter', $this->vars['id']))) {
            $this->page->setTitle('Intet resultat');
            $this->page->setTemplate('noresults');

        } else {
            $this->page->includeCss('fontello-ebe72605/css/idtemplate.css');
            $this->page->setTitle(e($activity->navn) . ' - Aktiviteter');
            $time_array = array();

            for ($i = 0; $i < 24; $i++) {
                $string = (($i < 10) ? "0{$i}:00" : "{$i}:00");
                $time_array[$string] = $i;
            }

            $this->page->time_array  = $time_array;
            $this->page->activity    = $activity;
            $this->page->afviklinger = $activity->getCompleteScheduling();
            $this->page->lokaler     = $this->model->getAllRooms();

            $this->page->double_booked_gms = $this->model->getDoubleBookings($activity);

            $this->page->karma_stats = $this->model->getKarmaStats();

            $this->page->desired_activity_stats = $this->model->getDesiredActivityStats($activity);

        }
    }


    /**
     * updates an activity or shows the update page
     *
     * @access public
     * @return void
     */
    public function editAktivitet()
    {
        $user = $this->model->getLoggedInUser();

        if (empty($this->vars['id']) || !($aktivitet = $this->model->findEntity('Aktiviteter', $this->vars['id']))) {
            $this->page->setTemplate('noresults');
            return;
        }
        if ($this->page->request->isPost()) {
            $post = $this->page->request->post;

            if (!empty($post->fortryd) || !is_array($post->aktivitet)) {
                $this->errorMessage('Aktiviteten blev ikke opdateret.');
                $this->hardRedirect($this->url('visaktivitet', array('id' => $aktivitet->id)));
            } else if (!empty($post->slet_aktivitet)) {
                $this->page->aktivitet = $aktivitet;
                $this->page->setTemplate('confirmDelete');
            } else {
                (($this->model->updateActivity($aktivitet, $post)) ? $this->successMessage('Aktiviteten blev opdateret.') : $this->errorMessage('Kunne ikke opdatere aktiviteten.')); 
                $this->log("Aktivitet #{$aktivitet->id} blev opdateret af {$this->model->getLoggedInUser()->user}", 'Aktivitet', $this->model->getLoggedInUser());
                $this->hardRedirect($this->url('visaktivitet', array('id' => $aktivitet->id)));
            }
        } else {
            $this->page->setTitle(e($aktivitet->navn) . ' - Rediger');
            $this->page->aktivitet = $aktivitet;
            $this->page->model     = $this->model;
        }
    }

    /**
     * updates the schedule for an activity or shows the update page
     *
     * @access public
     * @return void
     */
    public function editAfvikling()
    {
        if (empty($this->vars['id']) || !($afvikling = $this->model->findEntity('Afviklinger', $this->vars['id']))) {
            $this->page->setTemplate('noresults');
            return;
        }

        $user = $this->model->getLoggedInUser();

        if ($this->page->request->isPost()) {
            $post = $this->page->request->post;
            if (!empty($post->delete_schedule)) {
                $this->page->afvikling = $afvikling;
                $this->page->setTemplate('confirmDeleteAfvikling');

            } elseif (!empty($post->update_schedule)) {
                (($this->model->updateAfvikling($afvikling, $post)) ? $this->successMessage('Afviklingen blev opdateret.') : $this->errorMessage('Kunne ikke opdatere afviklingen.')); 

                $this->log("Afvikling #{$afvikling->id} ({$afvikling->getAktivitet()->navn}) blev opdateret af {$this->model->getLoggedInUser()->user}", 'Aktivitet', $this->model->getLoggedInUser());
                $this->hardRedirect($this->url('visaktivitet', array('id' => $afvikling->getAktivitet()->id)));
            } else {
                $this->errorMessage('Afviklingen blev ikke opdateret.');
                $this->hardRedirect($this->url('visaktivitet', array('id' => $afvikling->getAktivitet()->id)));
            }
        } else {
            $this->page->afvikling = $afvikling;
            $this->page->lokaler   = $this->model->getAllRooms();
            $this->page->model     = $this->model;
        }
    }


    /**
     * creates an activity or shows the create page
     *
     * @access public
     * @return void
     */
    public function opretAktivitet()
    {
        $user = $this->model->getLoggedInUser();

        if ($this->page->request->isPost()) {
            $post = $this->page->request->post;
            if (!empty($post->fortryd) || !is_array($post->aktivitet)) {
                $this->errorMessage('Aktiviteten blev ikke opdateret.');
                $this->hardRedirect($this->url('aktiviteterhome'));
            } else {
                try {
                    (($aktivitet = $this->model->opretAktivitet($post)) ? $this->successMessage('Aktiviteten blev oprettet.') : $this->errorMessage('Kunne ikke oprette aktiviteten.')); 
                    if ($aktivitet) {
                        $this->log("Aktivitet #{$aktivitet->id} blev oprettet af {$this->model->getLoggedInUser()->user}", 'Aktivitet', $this->model->getLoggedInUser());
                        $this->hardRedirect($this->url('visaktivitet', array('id' => $aktivitet->id)));
                    } else {
                        $this->hardRedirect($this->url('aktiviteterhome'));
                    }
                } catch (Exception $e) {
                    $this->errorMessage('Kunne ikke oprette aktiviteten.');
                    $this->hardRedirect($this->url('aktiviteterhome'));
                }
            }
        }

        $this->page->model = $this->model;
    }
	
	public function importActivities()
	{
		$user = $this->model->getLoggedInUser();
		
		if ($this->page->request->isPost()) {
            $post = $this->page->request->post;
			
			/*
			Nothing in $post...
			foreach ($post as $key => $value) {
				echo "Field ".htmlspecialchars($key)." is ".htmlspecialchars($value)."<br>";
			}
			exit;
			*/
			
            if (empty($post->importactivities)) { // skal jeg bruge file eller importactivities ?
                $this->errorMessage('Ingen Excel fil valgt.');
                $this->hardRedirect($this->url('aktiviteterhome'));
            }
			else {
                try {
                    if ($this->model->importActivities()) {
						$this->successMessage('Aktiviteter blev importeret.');
						$this->log("Aktiviter blev importeret af {$this->model->getLoggedInUser()->user}", 'Aktivitet', $this->model->getLoggedInUser());
                        $this->hardRedirect($this->url('aktiviteterhome'));
					} else
					{
						$this->errorMessage('Kunne ikke importere aktiviteter.'); 
						$this->hardRedirect($this->url('aktiviteterhome'));
					}
                } catch (Exception $e) {
                    $this->errorMessage('Kunne ikke importere aktiviteter.');
                    $this->hardRedirect($this->url('aktiviteterhome'));
                }
            }
        }
		
		$this->page->model = $this->model;
	}

    /**
     * creates a scheduling for an activity
     *
     * @access public
     * @return void
     */
    public function opretAfvikling()
    {
        if (empty($this->vars['aktivitet_id']) || !($aktivitet = $this->model->findEntity('Aktiviteter', $this->vars['aktivitet_id']))) {
            $this->errorMessage('Kunne ikke finde aktivitet at oprette afvikling for.');
            $this->hardRedirect($this->url('aktiviteterhome'));
        }

        $user = $this->model->getLoggedInUser();

        if ($this->page->request->isPost()) {
            $post = $this->page->request->post;

            if (!empty($post->opret)) {
                (($afvikling = $this->model->opretAfvikling($aktivitet, $post)) ? $this->successMessage('Afviklingen blev oprettet.') : $this->errorMessage('Kunne ikke oprette afviklingen.')); 

                if ($afvikling) {
                    $this->log("Afvikling ({$afvikling->start} -> {$afvikling->slut}) for aktivitet #{$aktivitet->id} blev oprettet af {$this->model->getLoggedInUser()->user}", 'Aktivitet', $this->model->getLoggedInUser());
                    $this->hardRedirect($this->url('visaktivitet', array('id' => $aktivitet->id)));
                } else {
                    $this->hardRedirect($this->url('visaktivitet', array('id' => $aktivitet->id)));
                }
            }

        } else {
            $this->hardRedirect($this->url('visaktivitet', array('id' => $aktivitet->id)));
        }
    }

    /**
     * deletes an activity
     *
     * @access public
     * @return void
     */
    public function sletAktivitet()
    {
        if (empty($this->vars['id']) || !($aktivitet = $this->model->findEntity('Aktiviteter', $this->vars['id']))) {
            $this->page->setTemplate('noresults');
            return;
        }

        $user = $this->model->getLoggedInUser();

        if ($this->page->request->isPost() && !empty($this->page->request->post->slet_aktivitet)) {
            (($this->model->deleteActivity($aktivitet)) ? $this->successMessage('Aktiviteten blev slettet.') : $this->errorMessage('Aktiviteten kunne ikke slettes.'));
            $this->log("Aktivitet #{$this->vars['id']} blev slettet af {$this->model->getLoggedInUser()->user}", 'Aktivitet', $this->model->getLoggedInUser());
        }

        $this->hardRedirect($this->url('vis_alle_aktiviteter'));
    }

    /**
     * deletes a scheduling
     *
     * @access public
     * @return void
     */
    public function sletAfvikling()
    {
        if (empty($this->vars['id']) || !($afvikling = $this->model->findEntity('Afviklinger', $this->vars['id']))) {
            $this->page->setTemplate('noresults');
            return;
        }

        $user = $this->model->getLoggedInUser();

        $akt_id   = $afvikling->aktivitet_id;
        $akt_navn = $afvikling->getAktivitet()->navn;

        if ($this->page->request->isPost() && !empty($this->page->request->post->slet_afvikling)) {
            (($this->model->deleteAfvikling($afvikling)) ? $this->successMessage('Afviklingen blev slettet.') : $this->errorMessage('Afviklingen kunne ikke slettes.'));
            $this->log("Afvikling #{$this->vars['id']} for aktivitet {$akt_navn} (ID{$akt_id}) blev slettet af {$this->model->getLoggedInUser()->user}", 'Aktivitet', $this->model->getLoggedInUser());
        }

        $this->hardRedirect($this->url('visaktivitet', array('id' => $akt_id)));
    }

    /**
     * shows a detailed list of signups and participants for activities
     *
     * @access public
     * @return void
     */
    public function gameActivity()
    {
        if (!empty($this->vars['day']) && preg_match('/(\d{4}-\d{2}-\d{2})/', $this->vars['day'], $matches)) {
            $days = array($matches[1]);
        } else {
            $days = $this->model->getAllDates();
        }

        $this->page->afviklinger = $this->model->getActivityForDates($days);
        $this->page->model       = $this->model;
    }

    /**
     * outputs the total hours of play time available at the convention
     *
     * @access public
     * @return void
     */
    public function getTotalPlaytime()
    {
        $this->page->playtime = $this->model->getTotalPlaytime();
    }

    /**
     * shows details for a specific game-start
     *
     * @access public
     * @return void
     */
    public function gameStartDetails()
    {
        $time = str_replace('_', ' ', $this->vars['time']);

        if (!($this->page->gamestart_details = $this->model->getGameStartDetails($time))) {
            $this->page->setTemplate('noresults');
            return;
        }

        $this->page->time = $time;

        $this->page->gamestart         = $this->model->getGameStartByTime($time);
        $this->page->can_run_gamestart = $this->model->canRunGameStart($time);

        $this->page->gamestart_votes_already_printed = $this->model->haveVotesBeenPrinted($this->page->gamestart);
    }

    /**
     * starts, restarts or shows a gamestart session
     *
     * @access public
     * @return void
     */
    public function gameStartMaster()
    {
        $this->page->layout_template = 'minimal.phtml';
        $time = str_replace('_', ' ', $this->vars['time']);

        if (!($this->page->gamestart_details = $this->model->getGameStartDetails($time))) {

            $this->errorMessage('Der er ingen spilstart på det tidspunkt.');
            $this->hardRedirect($this->url('aktiviteterhome'));
        }

        $this->page->gamestart = $this->model->getGameStartByTime($time);

        if ($this->model->canRunGameStart($time)) {
            if (!$this->page->gamestart->status || $this->page->gamestart->status == Gamestart::CLOSED) {
                $this->page->gamestart->status = Gamestart::OPEN;

                if (!$this->page->gamestart->isLoaded()) {
                    $this->page->gamestart->insert();

                    $this->log("Spilstart {$time} startet af {$this->model->getLoggedInUser()->user}", 'Spilstart', $this->model->getLoggedInUser());

                } else {
                    $this->page->gamestart->user_id = $this->getLoggedInUser()->id;

                    $this->page->gamestart->update();
                    $this->log("Spilstart {$time} genstartet af {$this->model->getLoggedInUser()->user}", 'Spilstart', $this->model->getLoggedInUser());
                }
            }
        }

        $this->page->owns_gamestart = $this->model->ownsGameStart($this->page->gamestart);
    }

    /**
     * starts, restarts or shows a gamestart session
     *
     * @access public
     * @return void
     */
    public function gameStartMinion()
    {
        $this->page->layout_template = 'minimal.phtml';

        if (!($schedule = $this->model->getSchedule($this->vars['id']))) {
            $this->errorMessage('Der er ingen afvikling med det ID.');
            $this->hardRedirect($this->url('aktiviteterhome'));
        }

        $groups = $schedule->getAssignedByType('spiller');
        $assigned = array_sum(array_map(function($x) {return count($x);}, $groups));

        $this->page->schedule           = $schedule;
        $this->page->activity           = $schedule->getActivity();
        $this->page->teams              = $schedule->getHold();
        $this->page->gamestart          = $this->model->getGameStartByTime($schedule->start);
        $this->page->gamestart_schedule = $this->model->getGameStartSchedule($schedule);
        $this->page->gamers_on_team     = $this->page->gamestart_schedule->gamers_present;
        $this->page->assigned_players   = $assigned;
        $this->page->groups_lacking_gms = $schedule->countLackingGMs();

        if (!$this->page->gamestart_schedule->status) {
            $this->page->gamestart_schedule->status = GamestartSchedule::OPEN;

            if (!$this->page->gamestart_schedule->isLoaded()) {
                $this->page->gamestart_schedule->insert();

            }
        }

        $this->page->owns_gamestart_schedule = $this->model->ownsGameStartSchedule($this->page->gamestart_schedule);
    }

    /**
     * handles change requests from a gamestart minion
     *
     * @throws FrameworkException
     * @access public
     * @return void
     */
    public function gameStartMinionChange()
    {
        try {
            if (!$this->page->request->isPost()) {
                throw new FrameworkException('Only POST requests allowed for gamestart minion changes');
            }

            if (empty($this->page->request->post->type) || !in_array($this->page->request->post->type, array('gamer', 'gamemaster', 'accepted-reserve'))) {
                throw new FrameworkException('Lacking type or wrong type of change for gamestart minion changes');
            }

            if (!($schedule = $this->model->getSchedule($this->vars['id']))) {
                throw new FrameworkException('No schedule with that ID');
            }

            if (!($gamestart_schedule = $this->model->getGameStartSchedule($schedule)) || !$gamestart_schedule->status) {
                throw new FrameworkException('Gamestart schedule is not active');
            }

            if ($this->page->request->post->type == 'gamer') {
                $this->model->handleMinionGamerChange($gamestart_schedule, $this->page->request->post);

            } elseif ($this->page->request->post->type == 'gamemaster') {
                $this->model->handleMinionGamemasterChange($gamestart_schedule, $this->page->request->post);

            } else {
                $this->model->handleMinionReserveChange($gamestart_schedule, $this->page->request->post);

            }

        } catch (FrameworkException $e) {
            $e->logException();

            header('HTTP/1.1 400 Failed');
            echo $e->getMessage();
        }

        exit;
    }

    /**
     * handles change requests from a gamestart master
     *
     * @throws FrameworkException
     * @access public
     * @return void
     */
    public function gameStartMasterChange()
    {
        try {
            if (!$this->page->request->isPost()) {
                throw new FrameworkException('Only POST requests allowed for gamestart master changes');
            }

            $this->model->handleMasterChange($this->vars['id'], $this->page->request->post);

        } catch (FrameworkException $e) {
            $e->logException();

            header('HTTP/1.1 400 Failed');
            echo $e->getMessage();
        }

        exit;
    }


    /**
     * fetches info for the gamestart master page
     *
     * @access public
     * @return void
     */
    public function gameStartAjaxInfo()
    {
        try {
            if (!($gamestart = $this->model->getGameStart($this->vars['id']))) {
                throw new FrameworkException('No such gamestart');
            }

            $updated = $this->page->request->get->last_update ? $this->page->request->get->last_update : null;

            $info = $this->model->getGameStartInformation($gamestart, $updated, $this->page->request->get->schedule_id);

            header('HTTP/1.1 200 Done');
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode($info);

        } catch (Exception $e) {
            header('HTTP/1.1 500 Fail');
            header('Content-Type: text/plain; charset=UTF-8');
            echo $e->getMessage();
        }

        exit;
    }

    public function gamestartQueue()
    {
    }

    public function gamestartQueueAjax()
    {
        try {

            header('HTTP/1.1 200 Done');
            header('Content-Type: application/json; charset=UTF-8');

            echo json_encode($this->model->fetchGameStartQueueData());

        } catch (Exception $e) {
            header('HTTP/1.1 500 fail');
            echo $e->getMessage();
        }

        exit;
    }

    /**
     * displays a page with vote markers for
     *
     * @access public
     * @return void
     */
    public function prepareScheduleVotes()
    {
        $time = str_replace('_', ' ', $this->vars['time']);

        if (!($this->page->gamestart_details = $this->model->getGameStartDetails($time))) {
            $this->page->setTemplate('noresults');
            return;
        }

        if ($this->model->votesCast($this->page->gamestart_details)) {
            $this->page->setTemplate('novoteregeneration');
            return;
        }

        $this->page->time = $time;

        $this->page->layout_template = 'printlist.phtml';
    }

    /**
     * allows for inputting a code for voting
     *
     * @access public
     * @return void
     */
    public function specifyVote()
    {
        $this->page->layout_template = 'external.phtml';
    }

    /**
     * post from specifyVote
     *
     * @access public
     * @return void
     */
    public function specifyVotePosted()
    {
        if (!$this->page->request->isPost()) {
            $this->hardRedirect($this->url('activity_vote'));
        }

        if (!($code = $this->model->fetchVoteCode($this->page->request->post->code))) {
            $this->errorMessage('No such code found / Kunne ikke finde koden');
            $this->hardRedirect($this->url('activity_vote'));
        }

        $this->hardRedirect($this->url('activity_specific_vote', ['code' => $code]));
    }

    /**
     * displays details for vote and yes/no form
     *
     * @access public
     * @return void
     */
    public function confirmVote()
    {
        if (!($vote = $this->model->fetchVote($this->vars['code']))) {
            $this->errorMessage('No such code found / Kunne ikke finde koden');
            $this->hardRedirect($this->url('activity_vote'));
        }

        $this->page->activity = $this->model->fetchVoteActivity($vote['schedule_id']);

        if (!$this->page->activity) {
            $this->errorMessage('No such code found / Kunne ikke finde koden');
            $this->hardRedirect($this->url('activity_vote'));
        }

        if ($vote['cast_at'] !== '0000-00-00 00:00:00') {
            $this->errorMessage('Vote already cast once / Stemme allerede brugt en gang');
            $this->hardRedirect($this->url('activity_vote'));
        }

        $this->page->layout_template = 'external.phtml';

        $this->page->vote = $vote;
    }

    /**
     * records casting of a vote
     *
     * @access public
     * @return void
     */
    public function castVote()
    {
        if (!$this->page->request->isPost()) {
            $this->hardRedirect($this->url('activity_vote'));
        }

        if (!($this->model->markVoteCast($this->page->request->post->id))) {
            $this->errorMessage('No such code found / Kunne ikke finde koden');
            $this->hardRedirect($this->url('activity_vote'));
        }

        $this->hardRedirect($this->url('voting_done'));
    }

    /**
     * shows thank you screen after cast vote
     *
     * @access public
     * @return void
     */
    public function votingDone()
    {
        $this->page->layout_template = 'external.phtml';
    }

    /**
     * shows voting statistics
     *
     * @access public
     * @return void
     */
    public function showVotingStats()
    {
        $user = $this->model->getLoggedInUser();

        if (!$user->hasRole('VotingStats')) {
            $this->hardRedirect($this->url('no_access'));
        }

        $this->page->stats = $this->model->collectVotingStats();
    }

    /**
     * creates a spreadsheet and outputs it,
     * containing information about GMs
     *
     * @access public
     * @return void
     */
    public function exportGameList()
    {
        $excelwriter = $this->model->getGmSpreadsheet();

        header('HTTP/1.1 200 Done');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename=spilledere.xlsx');

        $excelwriter->save('php://output');

        exit;
    }

    /**
     * creates GM briefings
     *
     * @access public
     * @return void
     */
    public function createGmBriefings()
    {
        $user = $this->model->getLoggedInUser();

        if (!($user->hasRole('Infonaut') || $user->hasRole('admin'))) {
            $this->hardRedirect($this->url('no_access'));

        }

        $this->model->createGmBriefings();

        $this->hardRedirect($this->url('vis_alle_aktiviteter'));
    }

    /**
     * shows distinct signups for activities
     *
     * @access public
     * @return void
     */
    public function showPrioritySignupStatistics()
    {
        $this->page->setTitle('Tilmeldings-statistik');
        $this->page->activity_data = $this->model->calculateSignupStatistics();
    }

    //{{{ ajax methods
    /**
     * outputs string of <option> elements containing times for an activity
     *
     * @access public
     * @return void
     */
    public function ajaxGetAfviklinger()
    {
        if (empty($this->vars['id']) || !($aktivitet = $this->model->findEntity('Aktiviteter', $this->vars['id']))) {
            exit;
        } else {
            $deltager = false;

            if (!empty($this->vars['deltager'])) {
                $deltager = $this->model->findEntity('Deltagere', $this->vars['deltager']);
            }

            $result = $this->model->getAfviklingerForAktivitet($aktivitet);
            if (!empty($result)) {
                $this->ajaxHeader();
                echo '{"pairs": [{"text": "Vælg", "value": ""}';
                foreach ($result as $afvikling) {
                    $disabled = '';
                    if ($aktivitet->tids_eksklusiv == 'ja' && $deltager && $deltager->isBusyBetween($afvikling->start, $afvikling->slut)) {
                        $disabled = 'true';
                    }

                    if ($afvikling->hasMultiBlok()) {
                        foreach ($afvikling->getMultiBlok() as $multi) {
                            if ($aktivitet->tids_eksklusiv == 'ja' && $deltager && $deltager->isBusyBetween($multi->start, $multi->slut)) {
                                $disabled = 'true';
                            }
                        }
                    }

                    echo ', {"value": "' . $afvikling->id . '", "disabled": "' . $disabled . '", "text": "' . $this->replaceDayNames(date('D H:i',strtotime($afvikling->start))) . '-' . $this->replaceDayNames(date('H:i',strtotime($afvikling->slut))) . '"}';
                }

                echo "]}";
            } else {
                echo '{"pairs": [{"text": "Ingen afviklinger", "value": ""}]}';
            }
        }

        exit;
    }

    /**
     * returns json of an activity's schedules
     *
     * @access public
     * @return void
     */
    public function ajaxActivitySchedules()
    {
        if (empty($this->vars['id']) || !($aktivitet = $this->model->findEntity('Aktiviteter', $this->vars['id']))) {
            exit;
        } else {
            $result = $this->model->getAfviklingerForAktivitet($aktivitet);
            $output = array();

            foreach ($result as $schedule) {
                $output[] = array(
                    'id'   => $schedule->id,
                    'text' => $this->replaceDayNames(date('D H:i',strtotime($schedule->start))) . '-' . $this->replaceDayNames(date('H:i',strtotime($schedule->slut)))
                );
            }

            echo json_encode($output);
            exit;
        }
    }

    /**
     * outputs a string of <option> elements containing groups running an activity
     *
     * @access public
     * @return void
     */
    public function ajaxGetHold()
    {
        if (empty($this->vars['id']) || !($afvikling = $this->model->findEntity('Afviklinger', $this->vars['id']))) {
            exit;
        } else {
            $result = $afvikling->getHold();
            if (!empty($result)) {
                $this->ajaxHeader();
                echo '{"pairs": [{"text": "Vælg", "value": ""}';
                foreach ($result as $hold) {
                    $disabled = 'false';

                    if (!$hold->canUseGamers() && !$hold->needsGMs()) {
                        $disabled = 'true';
                    }

                    echo ',{"value": "' . $hold->id . '", "disabled": "' . $disabled . '", "text": "' . $hold->holdnummer . '"}';
                }

                echo "]}";
            } else {
                echo '{"pairs": [{"text": "Ingen hold", "value": "", "disabled": "true"}]}';
            }
        }

        exit;
    }

    /**
     * outputs the first available hold for the afvikling
     *
     * @access public
     * @return void
     */
    public function ajaxGetRandomHold()
    {
        if (empty($this->vars['id']) || !($afvikling = $this->model->findEntity('Afviklinger', $this->vars['id'])) || empty($this->vars['type'])) {
            exit;
        } else {
            $result = $afvikling->getHold();

            if (!empty($result)) {

                $this->ajaxHeader();

                foreach ($result as $hold) {
                    if (($this->vars['type'] == 'spiller' && $hold->canUseGamers()) || ($this->vars['type'] == 'spilleder' && $hold->needsGMs())) {
                        break;
                    }

                    $hold = null;
                }

                if ($hold) {
                    echo <<<html
<input type='hidden' name='hold_id[]' value='{$hold->id}'/><input type='hidden' name='type[]' value='{$this->vars['type']}'/>{$hold->getAktivitet()->navn} &mdash; {$this->vars['type']}, hold {$hold->holdnummer}, {$this->replaceDayNames(date('D H:i', strtotime($hold->getAfvikling()->start)) . '-' . date('H:i', strtotime($hold->getAfvikling()->slut)))}</td>
html;
                }
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
    public function ajaxGetHoldNeeds()
    {
        if (empty($this->vars['id']) || !($hold = $this->model->findEntity('Hold', $this->vars['id']))) {
            exit;
        } else {
            $this->ajaxHeader();
            echo '{"pairs": [{"value": "", "text": "Vælg"}';
            echo ',{"value": "spiller", "text": "Spiller"' . ((!$hold->canUseGamers()) ? ',"disabled": "true"' : '') . '}';
            echo ',{"value": "spilleder", "text": "Spilleder"' . ((!$hold->needsGMs()) ? ',"disabled": "true"' : '') . '}]}';
        }

        exit;
    }

    //}}}
}
