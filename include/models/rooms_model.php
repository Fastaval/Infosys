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
     * @subpackage Models
     * @author     Peter Lind <peter.e.lind@gmail.com>
     * @copyright  2009 Peter Lind
     * @license    http://www.gnu.org/licenses/gpl.html GPL 3
     * @link       http://www.github.com/Fake51/Infosys
     */

    /**
     * handles all data fetching for the rooms controller
     *
     * @package MVC
     * @subpackage Models
     */

class RoomsModel extends Model
{

    /**
     * creates a Lokaler entity and fills it with POSTed data
     *
     * @param RequestVars $post
     *
     * @access public
     * @return false|Lokaler - false on fail or the created lokaler object
     */
    public function create(RequestVars $post)
    {
        $lokale = $this->createEntity('Lokaler');
        foreach ($post->getRequestVarArray() as $key => $val)
        {
            $lokale->$key = $val;
        }
        if (!$lokale->sovekapacitet) $lokale->sovekapacitet = 0;
        try
        {
            return (($lokale->insert()) ? $lokale : false);
        }
        catch (DBException $e)
        {
            return false;
        }
    }

    /**
     * modifies a rooms details
     *
     * @param Lokaler     $lokale - lokaler entity
     * @param RequestVars $post   - post vars
     *
     * @access public
     * @return bool
     */
    public function edit(Lokaler $lokale, RequestVars $post)
    {
        if (!$lokale->isLoaded() || !is_array($post->lokale))
        {
            return false;
        }
        foreach ($post->lokale as $key => $val)
        {
            $lokale->$key = $val;
        }
        if (!$lokale->sovekapacitet) $lokale->sovekapacitet = 0;
        try
        {
            return $lokale->update();
        }
        catch (DBException $e)
        {
            $e->logException();
            return false;
        }
    }

    /**
     * deletes a room
     *
     * @param Lokaler $lokale - Lokaler entity
     *
     * @access public
     * @return bool
     */
    public function deleteRoom(Lokaler $lokale)
    {
        if (!$lokale->isLoaded())
        {
            return false;
        }
        try
        {
            return $lokale->delete();
        }
        catch (DBException $e)
        {
            $e->logException();
            return false;
        }
    }

    /**
     * returns all rooms
     *
     * @access public
     * @return array
     */
    public function getAll()
    {
        return $this->createEntity('Lokaler')->findAll();
    }

    /**
     * wrapper for call to Afviklinger->getAllDates()
     *
     * @access public
     * @return array
     */
    public function getAllDates()
    {
        return $this->createEntity('Afviklinger')->getAllDates();
    }

    /**
     * returns array of scheduled activities for the room
     *
     * @param object $lokale - Lokaler entity
     *
     * @access public
     * @return array
     */
    public function getLokaleAfviklinger(Lokaler $lokale)
    {
        if (!$lokale->isLoaded())
        {
            return array();
        }
        $results = array();
        $hold = $this->createEntity('Hold');
        $select = $hold->getSelect();
        $select->setWhere('lokale_id','=',$lokale->id);
        $hold = $hold->findBySelectMany($select);
        $ids = $this->getIds($hold, 'afvikling_id');
        if (empty($ids))
        {
            $afviklinger = $multi = array();
        }
        else
        {
            $afviklinger = $this->createEntity('Afviklinger');
            $select = $afviklinger->getSelect();
            $select->setWhere('id', 'in', $ids);
            $afviklinger = $afviklinger->findBySelectMany($select);
            $multi = $this->createEntity('AfviklingerMultiblok');
            $select = $multi->getSelect();
            $select->setWhere('afvikling_id', 'in', $ids);
            $multi = $multi->findBySelectMany($select);
        }
        $strategy = new Strategy;
        return $strategy->sortSchedules(array_merge($afviklinger, $multi));
    }

    /**
     * returns array with data on which rooms are in use at what time during the given days
     *
     * @param array $dates - array of dates as strings
     *
     * @access public
     * @return array
     */
    public function getRoomUseForDates(array $dates)
    {
        $DB = $this->db;
        $results = array();
        $temp = array();
        foreach ($dates as $date)
        {
            $results[trim($date, "'")] = array();
            if ($result = $DB->query("
SELECT
    temp.id AS afvikling_id,
    temp.start,
    temp.slut,
    afviklinger.aktivitet_id,
    hold.id AS hold_id,
    hold.holdnummer,
    hold.lokale_id,
    aktiviteter.navn
FROM
    (SELECT id, start, slut FROM afviklinger WHERE date(start - interval 4 hour) = ? UNION SELECT afvikling_id, start, slut FROM afviklinger_multiblok WHERE date(start - interval 4 hour) = ? order by id) AS temp
    JOIN hold ON hold.afvikling_id = temp.id
    JOIN lokaler ON lokaler.id = hold.lokale_id
    JOIN afviklinger ON afviklinger.id = temp.id
    JOIN aktiviteter ON aktiviteter.id = afviklinger.aktivitet_id
ORDER BY temp.start;", array($date, $date)))
            {
                foreach ($result as $row)
                {
                    $temp[$row['lokale_id']][] = $row;
                }
            }
            ksort($temp);
            foreach ($temp as $room_id => $row_array)
            {
                if (($room = $this->createEntity('Lokaler')->findById($room_id)) && !empty($row_array))
                {
                    $results[$date][$room_id]['room'] = $room;
                    // all days wrap at 04.00
                    $start = $end = date('Y-m-d H:i:s', strtotime($date) + 4 * 3600);
                    foreach ($row_array as $row)
                    {
                        if ((strtotime($row['start']) >= strtotime($start) && strtotime($row['start']) < strtotime($end)) || (strtotime($row['slut']) >= strtotime($start) && strtotime($row['slut']) < strtotime($end)) || (strtotime($row['start']) < strtotime($start) && strtotime($row['slut']) > strtotime($end)) || (strtotime($row['start']) > strtotime($start) && strtotime($row['slut']) < strtotime($end)))
                        {
                            $use_array['start'] = strtotime($use_array['start']) < strtotime($row['start']) ? $use_array['start'] : $row['start'];
                            $use_array['end'] = strtotime($use_array['end']) > strtotime($row['slut']) ? $use_array['end'] : $row['slut'];
                            $activity = $this->createEntity('Aktiviteter')->findById($row['aktivitet_id']);
                            $team = $this->createEntity('Hold')->findById($row['hold_id']);
                            $use_array['activities'][] = array('activity' => $activity, 'team' => $team);
                        }
                        else
                        {
                            // no overlap with previous activity, store previous activity/ies
                            if (isset($use_array))
                            {
                                $results[$date][$room_id]['use'][] = $use_array;
                            }
                            $start = $row['start'];
                            $end = $row['slut'];
                            $activity = $this->createEntity('Aktiviteter')->findById($row['aktivitet_id']);
                            $team = $this->createEntity('Hold')->findById($row['hold_id']);
                            $use_array = array('start' => $start, 'end' => $end, 'activities' => array(array('activity' => $activity, 'team' => $team)));
                        }
                    }
                    if (isset($use_array))
                    {
                        $results[$date][$room_id]['use'][] = $use_array;
                    }
                    $use_array = null;
                }
            }
            $temp = array();
        }
        return $results;
    }

    public function handleFileUploads($room_id)
    {
        $options = array(
            'script_url' => $this->url('image_upload', array('id' => $room_id)),
            'upload_dir' => realpath(__DIR__ . '/../../public/uploads/rooms/') . '/' . $room_id . '/',
            'upload_url' => $this->config->get('app.public_uri') . 'uploads/rooms/' . $room_id . '/',
        );

        $uploader = new UploadHandler($options);
    }

    public function getRoomImageOverview()
    {
        $directory = new DirectoryIterator(__DIR__ . '/../../public/uploads/rooms/');

        $rooms = array();

        foreach ($directory as $file) {
            if ($file->isDot() || !$file->isDir()) {
                continue;
            }

            if ($room = $this->createEntity('Lokaler')->findById($file->getFilename())) {
                $sub_dirname = __DIR__ . '/../../public/uploads/rooms/' . $room->id;

                $images = array();

                if (is_dir($sub_dirname)) {
                    $sub_dir = new DirectoryIterator($sub_dirname);

                    foreach ($sub_dir as $sub_file) {
                        if (!$sub_file->isDot() && $sub_file->isFile()) {
                            $images[] = $sub_file->getFilename();
                        }
                    }
                }

                $rooms[$file->getFilename()] = array(
                    'room'   => $room,
                    'images' => $images,
                );
            }
        }

        ksort($rooms);

        return $rooms;
    }

    /**
     * returns detailed information about sleep capacities
     *
     * @access public
     * @return array
     */
    public function gatherSleepingStatistics()
    {
        $queries = [];

        $start = date('Y-m-d 22:00:00', strtotime($this->config->get('con.start')));
        $ends  = date('Y-m-d 10:00:00', strtotime($start) + 86400);

        $nights = 0;
        $starts = [];

        $rooms = $this->createEntity('Lokaler')->findAll();

        $map = function ($x) {
            return $x->id;
        };

        $rooms = array_combine(array_map($map, $rooms), $rooms);

        while (strtotime($start) < strtotime($this->config->get('con.end'))) {
            $starts[] = $start;
            $middle   = date('Y-m-d 23:59:00', strtotime($start));

            $query = '
SELECT
    r.id,
    r.beskrivelse,
    r.sovekapacitet AS maximum,
    CASE WHEN temp.in_use IS NULL THEN r.sovekapacitet ELSE r.sovekapacitet - temp.in_use END AS capacity,
    "' . $start . '" AS starts,
    "' . $ends . '" AS ends
FROM
    lokaler AS r
    LEFT JOIN (
        SELECT
            ps.room_id,
            COUNT(*) AS in_use
        FROM
            participants_sleepingplaces AS ps
        WHERE
            ps.starts <= "' . $middle . '" AND ps.ends >= "' . $middle . '"
        GROUP BY
            ps.room_id
    ) AS temp ON temp.room_id = r.id
WHERE
    r.sovekapacitet > 0
';

            $queries[] = $query;

            $start = date('Y-m-d 22:00:00', strtotime($start) + 86400);
            $ends  = date('Y-m-d 10:00:00', strtotime($start) + 86400);

            $nights++;
        }

        $empty_rooms = $places = [];

        foreach ($this->db->query(implode(' UNION ', $queries)) as $row) {
            $empty_rooms[$row['id']][$row['beskrivelse']][$row['maximum']][$row['starts']] = $row['capacity'];
        }

        return [
                $starts,
                $empty_rooms,
               ];
    }
}
