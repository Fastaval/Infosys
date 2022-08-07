<?php

    /**
     * Copyright (C) 2014 Peter Lind
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
class SmsController extends Controller
{
    protected $prerun_hooks = array(
        array('method' => 'checkUser','exclusive' => true, 'methodlist' => array()),
    );

    /**
     * allows for dry running an auto-mated sending
     *
     * @access public
     * @return void
     */
    public function autoDryRun()
    {
        $this->page->setTitle('SMS');

        if (!$this->page->request->isPost()) {
            return;
        }

        $post = $this->page->request->post;

        if (empty($post->when)) {
            return;
        }

        $this->page->dryrun_results = $this->model->getDryRunResults($post->when);
        $this->page->when           = $post->when;
    }

    public function showStats()
    {
        $this->page->stats = $this->model->getSmsStats();
    }
}
