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
 * handles economy related pages
 *
 * @category Infosys
 * @package  Controllers
 * @author   Peter Lind <peter.e.lind@gmail.com>
 * @license  http://www.gnu.org/licenses/gpl.html GPL 3
 * @link     http://www.github.com/Fake51/Infosys
 */
class EconomyController extends Controller
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
        array('method' => 'checkUser','exclusive' => true, 'methodlist' => array()),
    );

    /**
     * displays a table with detailed budget info
     *
     * @access public
     * @return void
     */
    public function detailedBudget() {
        $this->page->budget_details = $this->model->computeDetailedBudget();
    }

    /**
     * returns accounting overview of the convention
     *
     * @access public
     * @return void
     */
    public function accountingOverview()
    {
        $this->page->accounting_data = $this->model->computeAccountingData();
    }
}
