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
 * @category  Infosys
 * @package   Controllers 
 * @author    Peter Lind <peter.e.lind@gmail.com>
 * @copyright 2009-2012 Peter Lind
 * @license   http://www.gnu.org/licenses/gpl.html GPL 3
 * @link      http://www.github.com/Fake51/Infosys
 */

/**
 * default controller
 *
 * @category Infosys
 * @package  Controllers 
 * @author   Peter Lind <peter.e.lind@gmail.com>
 * @license  http://www.gnu.org/licenses/gpl.html GPL 3
 * @link     http://www.github.com/Fake51/Infosys
 */
class GraphController extends Controller
{
    protected $prerun_hooks = array(
        array('method' => 'checkUser','exclusive' => true),
    );

    /**
     * returns data for the signups graph
     *
     * @access public
     * @return void
     */
    public function ajaxSignups()
    {
        $this->crud('getSignupData');
    }

    /**
     * returns data for the total signups graph
     *
     * @access public
     * @return void
     */
    public function ajaxTotalSignups()
    {
        $this->crud('getTotalSignupData');
    }

    /**
     * returns data for the participant type shares graph
     *
     * @access public
     * @return void
     */
    public function ajaxShares()
    {
        $this->crud('getShareData');
    }

    /**
     * returns data for the participant type shares graph
     *
     * @access public
     * @return void
     */
    public function ajaxFoodShares()
    {
        $this->crud('getFoodShareData');
    }

    /**
     * method docblock
     *
     * @param
     *
     * @access protected
     * @return void
     */
    protected function crud($method)
    {
        try {
            $output = json_encode(call_user_func(array($this->model, $method)));

            header('HTTP/1.1 200 Done');
        } catch (Exception $e) {
            header('HTTP/1.1 500 Failed');
            $output = "Failed to gather data";
        }
        header('Content-Type: text/plain; charset=UTF-8');

        echo $output;
        exit;
    }
}
