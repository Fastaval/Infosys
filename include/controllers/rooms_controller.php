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
 * rooms controller
 *
 * @category Infosys
 * @package  Controllers 
 * @author   Peter Lind <peter.e.lind@gmail.com>
 * @license  http://www.gnu.org/licenses/gpl.html GPL 3
 * @link     http://www.github.com/Fake51/Infosys
 */
class RoomsController extends Controller
{
    protected $prerun_hooks = array(
        array('method' => 'checkUser','exclusive' => true, 'methodlist' => array('imageOverview')),
    );

    /**
     * Default method of the class
     *
     * @access public
     * @return void
     */
    public function main()
    {
    }

    /**
     * displays details for a given room
     *
     * @access public
     * @return void
     */
    public function visLokale()
    {
        if (empty($this->vars['id']) || !($lokale = $this->model->findEntity('Lokaler', $this->vars['id']))) {
            $this->page->setTemplate('noResults');
        } else {
            $this->page->lokale = $lokale;
            $this->page->lokale_afviklinger = $this->model->getLokaleAfviklinger($lokale);
        }
    }


    /**
     * displays details for a given room
     *
     * @access public
     * @return void
     */
    public function visAlle()
    {
        $this->page->lokaler = $this->model->getAll();
    }

    /**
     * creates a new room
     *
     * @access public
     * @return void
     */
    public function create()
    {
        if ($this->page->request->isPost()) {
            $post = $this->page->request->post;
            if ($lokale = $this->model->create($post)) {
                $this->successMessage('Lokalet blev oprettet.');
                $this->log("Lokale #{$lokale->id} blev oprettet af {$this->model->getLoggedInUser()->user}", 'Lokale', $this->model->getLoggedInUser());
                $this->hardRedirect($this->url('vis_lokale', array('id' => $lokale->id)));
            } else {
                $this->errorMessage('Lokalet kunne ikke oprettes.');
                $this->hardRedirect($this->url('lokalerhome'));
            }
        }
        $this->page->registerLateLoadJS('rooms.js');
    }


    /**
     * edits a room
     *
     * @access public
     * @return void
     */
    public function edit()
    {
        if (empty($this->vars['id']) || !($lokale = $this->model->findEntity('Lokaler', $this->vars['id']))) {
            $this->page->setTemplate('noResults');
        } else {
            if (!$this->page->request->isPost()) {
                $this->page->lokale = $lokale;
                $this->page->registerLateLoadJS('rooms.js');
            } elseif (!empty($this->page->request->post->lokale_edit)) {
                (($this->model->edit($lokale, $this->page->request->post)) ? $this->successMessage('Lokalet blev opdateret.') : $this->errorMessage('Lokalet kunne ikke opdateres.'));
                $this->log("Lokale #{$lokale->id} blev modificeret af {$this->model->getLoggedInUser()->user}", 'Lokale', $this->model->getLoggedInUser());
                $this->hardRedirect($this->url('vis_lokale', array('id' => $lokale->id)));
            } elseif (!empty($this->page->request->post->lokale_slet)) {
                $this->page->lokale = $lokale;
                $this->page->setTemplate('confirmDelete');
            } else {
                $this->hardRedirect($this->url('vis_lokale', array('id' => $lokale->id)));
            }
        }
    }

    /**
     * deletes a specified room
     *
     * @access public
     * @return void
     */
    public function deleteRoom()
    {
        if (empty($this->vars['id']) || !($lokale = $this->model->findEntity('Lokaler', $this->vars['id']))) {
            $this->page->setTemplate('noResults');
        }
        if ($this->page->request->isPost() && !empty($this->page->request->post->lokale_slet)) {
            if (count($this->model->getLokaleAfviklinger($lokale)) > 0) {
                $this->errorMessage('Der er stadig tilknyttet hold til lokalet - de skal fjernes før lokalet kan slettes.');
                $this->hardRedirect($this->url('vis_lokale', array('id' => $lokale->id)));
            } else {
                $this->model->deleteRoom($lokale) ? $this->successMessage('Lokalet blev slettet.') : $this->errorMessage('Lokalet kunne ikke slettes.');
                $this->log("Lokale #{$this->vars['id']} blev slettet af {$this->model->getLoggedInUser()->user}", 'Lokale', $this->model->getLoggedInUser());
            }
        }
        $this->hardRedirect($this->url('vis_alle_lokaler'));
    }

    /**
     * shows a detailed list of the use of rooms
     *
     * @access public
     * @return void
     */
    public function roomUse()
    {
        if (!empty($this->vars['day']) && preg_match('/(\d{4}-\d{2}-\d{2})/', $this->vars['day'], $matches))
        {
            $days = array($matches[1]);
        }
        else
        {
            $days = $this->model->getAllDates();
        }
        $this->page->lokaler = $this->model->getAll();
        $this->page->room_use = $this->model->getRoomUseForDates($days);
    }

    public function uploadImages()
    {
        $this->model->handleFileUploads($this->vars['id']);

        header('HTTP/1.1 200 Done');
        exit;
    }

    public function imageOverview()
    {
        $this->page->room_images = $this->model->getRoomImageOverview();
        $this->page->public_uri  = $this->config->get('app.public_uri');
    }

    /**
     * prints room sleep capacity statistics
     *
     * @access public
     * @return void
     */
    public function sleepStatistics()
    {
        list($this->page->nights, $this->page->statistics) = $this->model->gatherSleepingStatistics();
    }

    //{{{ ajax methods
    /**
     * get available rooms for a given time slot
     *
     * @access public
     * @return void
     */
    public function ajaxGetLokaler()
    {
        if (empty($this->vars['afvikling_id']) || !($afvikling = $this->model->findEntity('Afviklinger', $this->vars['afvikling_id']))) {
            return '';
        } else {
            $result = $this->model->getAll();
            if (!empty($result)) {
                header('Content-Type: text/plain; encoding: UTF-8');
                echo '{"pairs": [{"value": "", "text": "Vælg"}';
                foreach ($result as $lokale) {
                    $disabled = 'false';
                    if ($lokale->isOccupiedBetween($afvikling->start, $afvikling->slut, (($afvikling->getAktivitet()->lokale_eksklusiv == 'ja') ? true : false))) {
                        $disabled = 'true';
                    }

                    if ($afvikling->hasMultiBlok()) {
                        foreach ($afvikling->getMultiBlok() as $multi) {
                            if ($lokale->isOccupiedBetween($multi->start, $multi->slut, (($multi->getAktivitet()->lokale_eksklusiv == 'ja') ? true : false))) {
                                $disabled = 'true';
                            }
                        }
                    }

                    echo ',{"value": "' . $lokale->id . '", "disabled": ' . $disabled . ', "text": "' . $lokale->beskrivelse . '"}';
                }

                echo "]}";
            }
        }

        exit;
    }
    //}}}
}
