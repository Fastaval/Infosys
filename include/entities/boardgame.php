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
     * @subpackage Entities
     * @author     Peter Lind <peter.e.lind@gmail.com>
     * @copyright  2009 Peter Lind
     * @license    http://www.gnu.org/licenses/gpl.html GPL 3
     * @link       http://www.github.com/Fake51/Infosys
     */

    /**
     * handles the hold table
     *
     * @package MVC
     * @subpackage Entities
     */
class Boardgame extends DBObject
{
    /**
     * Name of database table
     *
     * @var string
     */
    protected $tablename = 'boardgames';

    private $events;

    protected function getEvents()
    {
        if (!empty($this->events)) {
            return $this->events;
        }

        $query = '
SELECT
    id,
    type,
    data,
    timestamp
FROM
    boardgameevents
WHERE
    boardgame_id = ?
ORDER BY
    timestamp
';

        return $this->events = $this->db->query($query, array($this->id));
    }

    protected function createEvent($type, array $data = array())
    {
        $this->events = null;

        $query = '
INSERT INTO boardgameevents (type, data, timestamp, boardgame_id) VALUES
(?, ?, NOW(), ?)
';

        $this->db->exec($query, array($type, json_encode($data), $this->id));
    }

    public function insert()
    {
        $result = parent::insert();

        $this->createEvent('created');

        return $result;
    }

    public function setStatus($status, $data = array())
    {
        switch ($status) {
        case 'borrowed':
        case 'finished':
        case 'returned':
            $this->createEvent($status, $data);
            break;

        default:
            throw new Exception('Unrecognized status: ' . $status);
        }
    }

    public function checkStatus($type)
    {
        $events = $this->getEvents();

        if (empty($events)) {
            return false;
        }

        $end = end($events);

        return !(empty($end['type']) || $end['type'] !== $type);
    }

    public function isBorrowed()
    {
        return $this->checkStatus('borrowed');
    }

    public function getBorrowedDetails()
    {
        if (!$this->isBorrowed()) {
            return array();
        }

        $events = $this->getEvents();

        $end = end($events);

        $data = json_decode($end['data'], true);

        return array(
            'participant_id' => isset($data['participant_id']) ? $data['participant_id'] : '',
            'name'           => isset($data['participant']) ? $data['participant'] : '',
            'comment'        => isset($data['comment']) ? $data['comment'] : '',
            'timestamp'      => $end['timestamp'],
        );
    }


    public function getFinishedDetails()
    {
        if (!$this->isFinished()) {
            return '';
        }

        $events = $this->getEvents();

        $end = end($events);

        return $end['timestamp'];
    }

    public function isFinished()
    {
        return $this->checkStatus('finished');
    }

    public function getLog()
    {
        $log = array();

        foreach ($this->getEvents() as $event) {
            $log[] = array(
                'status'    => $event['type'],
                'timestamp' => $event['timestamp'],
                'data'      => json_decode($event['data']),
            );
        }

        return $log;
    }
}
