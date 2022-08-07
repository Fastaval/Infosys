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
 * PHP version 5
 *
 * @category  Infosys
 * @package   Models
 * @author    Peter Lind <peter.e.lind@gmail.com>
 * @copyright 2009-2012 Peter Lind
 * @license   http://www.gnu.org/licenses/gpl.html GPL 3
 * @link      http://www.github.com/Fake51/Infosys
 */

/**
 * handles all data fetching for the index controller
 *
 * @category Infosys
 * @package  Models
 * @author   Peter Lind <peter.e.lind@gmail.com>
 * @license  http://www.gnu.org/licenses/gpl.html GPL 3
 * @link     http://www.github.com/Fake51/Infosys
 */
class SmsModel extends Model
{
    /**
     * returns messages and details about them, for a given
     * time, without actually sending or logging the messages
     *
     * @param string $when Timestamp to check messages for
     *
     * @access public
     * @return array
     */
    public function getDryRunResults($when)
    {
        $log_sender  = $this->dic->get('LogSender');
        $index_model = $this->factory('Index');

        $activities  = $index_model->getActivitiesForAutoSend($when);
        $count       = $index_model->sendActivityMessages($activities, $log_sender);

        $diy   = $index_model->getDIYForAutoSend($when);
        $count = $index_model->sendDIYMessages($diy, $log_sender);

        $diy   = $index_model->getTomorrowsDiyForAutoSend($when);
        $count = $index_model->sendTomorrowsDiyMessages($diy, $log_sender);

        return $log_sender->getMessages();
    }

    public function getSmsStats()
    {
        $query = '
SELECT
    COUNT(*) AS messages_sent
FROM
    smslog
';

        $result = $this->db->query($query);

        return $result[0]['messages_sent'];
    }
}
