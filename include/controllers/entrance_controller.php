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
 * handles entrance related stuff
 *
 * @category Infosys
 * @package  Controllers 
 * @author   Peter Lind <peter.e.lind@gmail.com>
 * @license  http://www.gnu.org/licenses/gpl.html GPL 3
 * @link     http://www.github.com/Fake51/Infosys
 */
class EntranceController extends Controller
{
    protected $prerun_hooks = array(
                                array('method' => 'checkUser','exclusive' => true),
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
     * shows a list of all the wear types
     *
     * @access public
     * @return void
     */
    public function entryTypes()
    {
        if ($results = $this->model->getAllEntries()) {
            $results = $this->model->getNumbersSold($results);

            usort($results, function ($a, $b) {
                return strcmp($a->type, $b->type);
            });

            $this->page->entry_types = $results;

        } else {
            $this->page->setTemplate('noResults');
        }
    }

    /**
     * shows the details of one wear type
     *
     * @access public
     * @return void
     */
    public function showType()
    {
        if (empty($this->vars['id']) || !($indgang = $this->model->findEntity('Indgang', $this->vars['id'])))
        {
            $this->page->setTemplate('noResults');
        }
        else
        {
            $this->page->indgang = $indgang;
        }
    }

    /**
     * deletes a wear type
     *
     * @access public
     * @return void
     */
    public function deleteEntry()
    {
        if (empty($this->vars['id']) || !($indgang = $this->model->findEntity('Indgang', $this->vars['id'])))
        {
            $this->page->setTemplate('noResults');
            return;
        }
        if (!$this->page->request->isPost() || !$this->page->request->post->delete_entry)
        {
            $this->hardRedirect($this->url('show_entry', array('id' => $indgang->id)));
        }
        $id = $indgang->id;
        $type = $indgang->type;
        if ($indgang->delete())
        {
            $user = $this->model->getLoggedInUser();
            $this->successMessage('Indgangs-typen blev slettet');
            $this->log("Indgang Id #{$id} ({$type}) blev slettet af {$user->user}", 'Indgang', $user);
            $this->hardRedirect($this->url('indganghome'));
        }
        else
        {
            $this->errorMessage('Kunne ikke slette indgangs-typen');
            $this->hardRedirect($this->url('show_entry', array('id' => $id)));
        }
    }

    /**
     * creates a wear type given POST vars or displays a form for creating a type
     *
     * @access public
     * @return void
     */
    public function createEntry()
    {
        $this->page->con_start = $this->config->get('con.start');
        $this->page->con_end   = $this->config->get('con.end');

        if (!$this->page->request->isPost()) {
            $this->page->setTemplate('editEntry');
            return;
        }

        if ($indgang = $this->model->createEntry($this->page->request->post)) {
            $this->successMessage('Indgangs-typen blev oprettet.');
            $this->log("Indgang Id #{$indgang->id} ({$indgang->navn}) oprettet af {$this->model->getLoggedInUser()->user}", 'indgang', $this->model->getLoggedInUser());
            $this->hardRedirect($this->url('show_entry', array('id' => $indgang->id)));
        } else {
            $this->errorMessage('Indgangs-typen kunne ikke oprettes.');
            $this->hardRedirect($this->url('indganghome'));
        }
    }

    /**
     * updates a wear item or shows the edit form
     *
     * @access public
     * @return void
     */
    public function editEntry()
    {
        if (empty($this->vars['id']) || !($indgang = $this->model->findEntity('Indgang', $this->vars['id'])))
        {
            $this->page->setTemplate('noResults');
            return;
        }
        $this->page->indgang = $indgang;

        $this->page->con_start = $this->config->get('con.start');
        $this->page->con_end   = $this->config->get('con.end');

        if (!$this->page->request->isPost())
        {
            $this->page->setTemplate('editEntry');
        }
        else
        {
            $post = $this->page->request->post;
            if (!empty($post->update_entry))
            {
                if ($this->model->updateEntry($indgang, $post))
                {
                    $this->successMessage('Indgangs-typen blev opdateret.');
                    $this->log("Indgang Id #{$indgang->id} ({$indgang->type}) blev opdateret af {$this->model->getLoggedInUser()->user}", 'Indgang', $this->model->getLoggedInUser());
                }
                else
                {
                    $this->errorMessage('Kunne ikke opdatere indgangs-typen.');
                }
            }
            elseif (!empty($post->delete_entry))
            {
                $this->page->setTemplate('displayDelete');
                return;
            }
            else
            {
                $this->errorMessage('indgangs-typen blev ikke opdateret.');
            }
            $this->hardRedirect($this->url('show_entry', array('id'=>$indgang->id)));
        }
    }

    /**
     * displays stats for the individual entry types
     *
     * @access public
     * @return void
     */
    public function entryStats() {
        $this->page->stats = $this->model->getEntryStats();
    }
}
