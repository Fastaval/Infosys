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
 * PHP version 5.3+
 *
 * @package    Infosys
 * @subpackage Controllers 
 * @author     Peter Lind <peter.e.lind@gmail.com>
 * @copyright  2009-2012 Peter Lind
 * @license    http://www.gnu.org/licenses/gpl.html GPL 3
 * @link       http://www.github.com/Fake51/Infosys
 */

/**
 * default controller
 *
 * @package    Infosys
 * @subpackage Controllers 
 * @author     Peter Lind <peter.e.lind@gmail.com>
 */
class BoardgamesController extends Controller
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
        array('method' => 'checkUser', 'exclusive' => true),
    );

    /**
     * shows all data for shop overview
     *
     * @access public
     * @return void
     */
    public function overview()
    {
    }

    public function fetchData()
    {
        header('HTTP/1.1 200 done');
        header('Content-Type: application/json; charset=UTF-8');

        echo json_encode($this->model->fetchAllData());
        exit;
    }

    public function createGame()
    {
        if (!$this->page->request->isPost()) {
            header('HTTP/1.1 400 Bad method');
            exit;

        }

        header('HTTP/1.1 500 Error processing data');

        try {
            $game = $this->model->createBoardgame($this->page->request->post);

            header('HTTP/1.1 200 Done');
            header('Content-Type: application/json; charset=UTF-8');

            echo json_encode(array('id' => $game->id, 'log' => $game->getLog()));

        } catch (Exception $e) {
            echo $e->getMessage();
        }

        exit;
    }

    public function updateGame()
    {
        if (!$this->page->request->isPost()) {
            header('HTTP/1.1 400 Bad method');
            exit;

        }

        header('HTTP/1.1 500 Error processing data');

        try {
            $this->model->updateBoardgameStatus($this->page->request->post);

            header('HTTP/1.1 200 Done');

        } catch (Exception $e) {
            echo $e->getMessage();
        }

        exit;
    }

    public function editGame()
    {
        if (!$this->page->request->isPost()) {
            header('HTTP/1.1 400 Bad method');
            exit;

        }

        header('HTTP/1.1 500 Error processing data');

        try {
            $game = $this->model->editBoardgame($this->page->request->post);

            header('HTTP/1.1 200 Done');

        } catch (Exception $e) {
            echo $e->getMessage();
        }

        exit;
    }

    public function parseSpreadsheet()
    {
        if (!$this->page->request->isPost()) {
            header('HTTP/1.1 400 Bad method');
            exit;

        }

        header('HTTP/1.1 500 Error processing data');

        try {
            $this->model->parseSpreadsheetData($this->page->request->post);

            header('HTTP/1.1 200 Done');

        } catch (Exception $e) {
            echo $e->getMessage();
        }

        exit;
    }

    public function updateNote()
    {
        if (!$this->page->request->isPost()) {
            header('HTTP/1.1 400 Bad method');
            exit;

        }

        header('HTTP/1.1 500 Error processing data');

        try {
            $this->model->updateBoardgamesNote($this->page->request->post);

            header('HTTP/1.1 200 Done');

        } catch (Exception $e) {
            echo $e->getMessage();
        }

        exit;
    }

    /**
     * returns boardgame presence events since last check
     *
     * @access public
     * @return void
     */
    public function presenceCheck()
    {
        if (!$this->page->request->isGet()) {
            header('HTTP/1.1 405 Bad method');
            exit;

        }

        try {
            $updates = $this->model->getPresenceUpdates($this->page->request->get->time);

            header('HTTP/1.1 200 Done');
            header('Content-Type: application/json; charset=UTF-8');

            echo json_encode($updates);

        } catch (Exception $e) {
            header('HTTP/1.1 500 Error processing data');
            echo $e->getMessage();
        }

        exit;
    }

    /**
     * sets new presence state
     *
     * @access public
     * @return void
     */
    public function presenceUpdate()
    {
        if (!$this->page->request->isPost()) {
            header('HTTP/1.1 405 Bad method');
            exit;

        }

        try {
            $this->model->addPresenceEvent($this->page->request->post->id, $this->page->request->post->state);

            header('HTTP/1.1 200 Done');

        } catch (Exception $e) {
            header('HTTP/1.1 500 Error processing data');
            echo $e->getMessage();
        }

        exit;
    }

    /**
     * resets all games marked present
     *
     * @access public
     * @return void
     */
    public function resetPresence()
    {
        if (!$this->page->request->isPost()) {
            header('HTTP/1.1 405 Bad method');
            exit;

        }

        try {
            $this->model->resetPresence();

            header('HTTP/1.1 200 Done');

        } catch (Exception $e) {
            header('HTTP/1.1 500 Error processing data');
            echo $e->getMessage();
        }

        exit;
    }

    public function showReporting()
    {
        $this->page->data = $this->model->getReportingData();

        $this->page->layout_template = "contentonly.phtml";

        header('HTTP/1.1 200 Done');
        header('Content-Type: application/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="reporting.csv"');
    }
}
