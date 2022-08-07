<?php
    /**
     * Copyright (C) 2009  Peter Lind
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
     * @package    MVC
     * @subpackage Controllers 
     * @author     Peter Lind <peter.e.lind@gmail.com>
     * @copyright  2009 Peter Lind
     * @license    http://www.gnu.org/licenses/gpl.html GPL 3
     * @link       http://www.github.com/Fake51/Infosys
     */

    /**
     * food controller
     *
     * @package    MVC
     * @subpackage Controllers 
     * @author     Peter Lind <peter.e.lind@gmail.com>
     */
class FoodController extends Controller
{
    protected $prerun_hooks = array(
                                array('method' => 'checkUser','exclusive' => true, 'methodlist' => array('displayHandout', 'ajaxHandout')),
                               );

    /**
     * Default method of the class
     *
     * @access public
     * @return void
     */
    public function main()
    {
        //$this->page->food_stats = $this->_model->getFoodStats();
    }

    /**
     * shows a list of all the food types
     *
     * @access public
     * @return void
     */
    public function showTypes()
    {
        if ($results = $this->model->getAllFood())
        {
            $this->page->food_types = $results;
        }
        else
        {
            $this->page->setTemplate('noResults');
        }
    }

    /**
     * shows the details of one food type
     *
     * @access public
     * @return void
     */
    public function showFood()
    {
        if (empty($this->vars['id']) || !($food = $this->model->findEntity('Mad', $this->vars['id'])))
        {
            $this->page->setTemplate('noResults');
        }
        else
        {
            $this->page->mad = $food;
            $this->page->tider = $food->getMadTider();
        }
    }

    /**
     * deletes a food type
     *
     * @access public
     * @return void
     */
    public function deleteFood()
    {
        if (!($this->model->getLoggedInUser()->hasRole('Infonaut') || $this->model->getLoggedInUser()->hasRole('admin'))) {
            $this->hardRedirect($this->url('no_access'));
        }

        if (empty($this->vars['id']) || !($food = $this->model->findEntity('Mad', $this->vars['id'])))
        {
            $this->page->setTemplate('noResults');
            return;
        }
        if (!$this->page->request->isPost() || !$this->page->request->post->delete_food)
        {
            $this->hardRedirect($this->url('show_food', array('id' => $food->id)));
        }
        $id = $food->id;
        $kategori = $food->kategori;
        if ($food->delete())
        {
            $user = $this->model->getLoggedInUser();
            $this->successMessage('Mad-typen blev slettet');
            $this->log("Mad Id #{$id} ({$name}) blev slettet af {$user->user}", 'Mad', $user);
            $this->hardRedirect($this->url('madhome'));
        }
        else
        {
            $this->errorMessage('Kunne ikke slette mad-typen');
            $this->hardRedirect($this->url('show_food', array('id' => $id)));
        }
    }

    /**
     * creates a food type given POST vars or displays a form for creating a type
     *
     * @access public
     * @return void
     */
    public function createFood()
    {
        if (!($this->model->getLoggedInUser()->hasRole('Infonaut') || $this->model->getLoggedInUser()->hasRole('admin'))) {
            $this->hardRedirect($this->url('no_access'));
        }

        if (!$this->page->request->isPost())
        {
            $this->page->setTemplate('editFood');
            return;
        }
        if ($food = $this->model->createFood($this->page->request->post))
        {
            $this->successMessage('Mad-typen blev oprettet.');
            $this->log("Mad Id #{$food->id} ({$food->kategori}) oprettet af {$this->model->getLoggedInUser()->user}", 'Mad', $this->model->getLoggedInUser());
            $this->hardRedirect($this->url('show_food', array('id' => $food->id)));
        }
        else
        {
            $this->errorMessage('Mad-typen kunne ikke oprettes.');
            $this->hardRedirect($this->url('madhome'));
        }
    }

    /**
     * updates a food item or shows the edit form
     *
     * @access public
     * @return void
     */
    public function editFood()
    {
        if (!($this->model->getLoggedInUser()->hasRole('Infonaut') || $this->model->getLoggedInUser()->hasRole('admin'))) {
            $this->hardRedirect($this->url('no_access'));
        }

        if (empty($this->vars['id']) || !($food = $this->model->findEntity('Mad', $this->vars['id'])))
        {
            $this->page->setTemplate('noResults');
            return;
        }
        $this->page->mad = $food;
        if ($this->page->request->isPost())
        {
            $post = $this->page->request->post;
            if (!empty($post->update_food))
            {
                if ($this->model->updateFood($food, $post))
                {
                    $user = $this->model->getLoggedInUser();
                    $this->successMessage('Mad-typen blev opdateret.');
                    $this->log("Mad Id #{$food->id} ({$food->kategori}) blev opdateret af {$user->user}", 'Mad', $user);
                }
                else
                {
                    $this->errorMessage('Kunne ikke opdatere mad-typen.');
                }
            }
            elseif (!empty($post->delete_food))
            {
                $this->page->setTemplate('displayDelete');
                return;
            }
            else
            {
                $this->errorMessage('Mad-typen blev ikke opdateret.');
            }
            $this->hardRedirect($this->url('show_food', array('id'=>$food->id)));
        }
    }

    /**
     * displays stats regarding food
     *
     * @access public
     * @return void
     */
    public function foodStats() {
        $this->page->stats = $this->model->getFoodStats();
    }

    /**
     * outputs a string of <option> elements containing groups running an activity
     *
     * @access public
     * @return void
     */
    public function ajaxGetMadTider()
    {
        if (empty($this->vars['id']) || !($mad = $this->model->findEntity('Mad', $this->vars['id']))) {
            exit;
        } else {
            $result = $mad->getMadtider();
            if (!empty($result)) {
                header('Content-Type: text/plain; encoding: UTF-8');
                echo '{"pairs": [{"text": "Vælg", "value": ""}';
                foreach ($result as $madtid) {
                    echo ',{"value": "' . $madtid->id . '", "text": "' . $this->replaceDayNames(date('D', strtotime($madtid->dato))) . '"}';
                }
                echo ']}';
            }
        }
        exit;
    }

    /**
     * displays the food handout registration page
     *
     * @access public
     * @return void
     */
    public function displayHandout() {
        if (!$this->model->getLoggedInUser()) {
            exit;
        }
        $food_items = $this->model->findCurrentFoodItems();
        $this->page->food_items = $food_items;
        $item = current($food_items);
        $this->page->current_food_time = $item ? $item->dato : null;
        $this->page->next_food_time = $this->model->findNextFoodTime();
        $this->page->layout_template = 'external.phtml';
    }

    /**
     * handles ajax calls from the food handout interface
     *
     * @throws Exception
     * @access public
     * @return void
     */
    public function ajaxHandout() {
        if (!$this->model->getLoggedInUser()) {
            exit;
        }
        $this->refreshSession();
        $this->ajaxHeader();
        if (!$this->page->request->isPost()) {
            header('HTTP/1.1 400 Not using post');
            exit;
        }
        $post = $this->page->request->post;
        if (empty($post->action)) {
            header('HTTP/1.1 400 No action specified');
            exit;
        }
        try {
            switch ($post->action) {
                case 'mark-received':
                    $message = $this->model->markFoodReceived($post);
                    break;
                case 'undo-received':
                    $message = $this->model->undoReceiveFood($post);
                    break;
                case 'get-food-stats':
                    $message = json_encode($this->model->retrieveHandoutStats($post));
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
     * shows food that is or is likely tradeable
     *
     * @access public
     * @return void
     */
    public function showTradeable()
    {
        $user = $this->model->getLoggedInUser();

        if (!($user->hasRole('Infonaut') || $user->hasRole('Food-admin'))) {
            $this->hardRedirect($this->url('no_access'));
        }

        $this->page->tradeable_food       = $this->model->getTradeableFood();
        $this->page->maybe_tradeable_food = $this->model->getMaybeTradeableFood();
        $this->page->headings             = array_merge(array_keys($this->page->tradeable_food));
    }

    public function resetParticipantHandoutTimes()
    {
        $user = $this->model->getLoggedInUser();

        if (!($user->hasRole('admin'))) {
            $this->hardRedirect($this->url('no_access'));
        }

        try {
            $this->model->updateFoodHandoutTimes();

            $this->successMessage('Uddelingstiderne blev opdateret');

            $this->log("Uddelingstiderne blev opdateret af {$user->user}", 'Mad', $user);
            $this->hardRedirect($this->url('home'));

        } catch (Exception $e) {
            $this->errorMessage('Uddelingstiderne kunne ikke opdateres');

            $this->log("Uddelingstiderne kunne ikke opdateres af {$user->user}", 'Mad', $user);
            $this->hardRedirect($this->url('home'));
        }
    }
}
