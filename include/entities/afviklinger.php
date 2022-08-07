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
     * handles the afviklinger table
     *
     * @package MVC
     * @subpackage Entities
     */
class Afviklinger extends DBObject
{
    /**
     * Name of database table
     *
     * @var string
     */
    protected $tablename = 'afviklinger';

    /** 
     * maximum number of hours you can play at the con
     *
     * @var int
     * @todo move somewhere better
     */
    public $max_playtime = 70;

    /**
     * cache for getHold method
     *
     * @var array
     */
    protected $teams;

    /**
     * returns the linked aktivitet object
     *
     * @access public
     * @return bool|object
     */
    public function getAktivitet()
    {
        if (isset($this->activity)) {
            return $this->activity;
        }

        if (!$this->isLoaded()) {
            return false;
        }

        return $this->activity = $this->createEntity('Aktiviteter')->findById($this->aktivitet_id);
    }

    /**
     * wrapper for getAktivitet to move away from danish in code
     *
     * @access public
     * @return bool|object
     */
    public function getActivity()
    {
        return $this->getAktivitet();
    }

    /**
     * returns an array of groups running the activity at this time
     *
     * @access public
     * @return array
     */
    public function getHold()
    {
        if (isset($this->teams)) {
            return $this->teams;
        }

        if (!$this->isLoaded()) {
            return array();
        }

        $select = $this->createEntity('Hold')->getSelect();
        $select->setWhere('afvikling_id','=',$this->id);
        return $this->teams = $this->createEntity('Hold')->findBySelectMany($select);
    }

    /**
     * returns the room the participants will meet in
     *
     * @access public
     * @return bool|array
     */
    public function getLokale()
    {
        if ($lokale = $this->getRoomObject()) {
            return "{$lokale->beskrivelse}";
        } else {
            return 'Mødested: spørg venligst i informationen.';
        }
    }

    public function getRoomObject()
    {
        if (!$this->isLoaded()) {
            return false;
        }
        return $this->lokale_id ? $this->createEntity('Lokaler')->findById($this->lokale_id) : false;
    }

    /**
     * returns the room the participants will meet in
     *
     * @access public
     * @return bool|array
     */
    public function getRoom()
    {
        if ($lokale = $this->getRoomObject()) {
            return "{$lokale->beskrivelse}";
        } else {
            return 'Ask at the information for room.';
        }
    }

    /**
     * returns array of tilmeldinger for the afvikling
     *
     * @access public
     * @return array
     */
    public function getTilmeldinger()
    {
        return $this->getSignupByType();
    }


    /**
     * returns array of tilmeldinger for the afvikling
     *
     * @access public
     * @return array
     */
    public function getSignupGamers()
    {
        return $this->getSignupByType('spiller');
    }

    /**
     * returns array of tilmeldinger for the afvikling
     *
     * @access public
     * @return array
     */
    public function getSignupGmCount()
    {
		$query = '
SELECT
    COUNT(*) AS count,
    "entire" AS type
FROM
    deltagere_tilmeldinger AS p
WHERE
    p.tilmeldingstype = "spilleder"
    AND p.afvikling_id = ?

UNION

SELECT
    COUNT(*) AS count,
    "doubles" AS type
FROM
    deltagere_tilmeldinger AS p
    JOIN afviklinger AS pa ON pa.id = p.afvikling_id
    JOIN deltagere_tilmeldinger AS s ON s.deltager_id = p.deltager_id AND s.afvikling_id != p.afvikling_id
    JOIN afviklinger AS sa ON sa.id = s.afvikling_id
WHERE
    p.tilmeldingstype = "spilleder"
    AND pa.id = ?
    AND s.tilmeldingstype = "spilleder"
    AND (
        (pa.start > sa.start AND pa.start < sa.slut)
        OR (pa.start <= sa.start AND pa.slut >= sa.slut)
        OR (pa.slut > sa.start AND pa.slut < sa.slut)
    )
';

        $max = $min = 0;

        foreach ($this->db->query($query, [$this->id, $this->id]) as $row) {
            if ($row['type'] === 'entire') {
                $max += $row['count'];
                $min += $row['count'];

            } elseif ($row['type'] === 'doubles') {
                $min -= $row['count'];
            }

        }

        if ($min === $max) {
            return $min;
        }

        return $min . ' - ' . $max;
    }

    /**
     * returns array of tilmeldinger for the afvikling
     *
     * @access public
     * @return array
     */
    public function getSignupGMs()
    {
        return $this->getSignupByType('spilleder');
    }

    /**
     * returns array of assigned gms for the scheduled activity
     *
     * @access public
     * @return array
     */
    public function getAssignedGMs()
    {
        return $this->getAssignedByType('spilleder');
    }

    /**
     * counts how many GMs the schedule lacks
     *
     * @access public
     * @return int
     */
    public function countLackingGMs()
    {
        if (isset($this->lacking_gms)) {
            return $this->lacking_gms;
        }

        if (!$this->isLoaded()) {
            return 0;
        }

        $needed_gms = count($this->getHold()) * $this->getActivity()->spilledere_per_hold;
        $gms = 0;

        foreach ($this->getAssignedGMs() as $group) {
            $gms += count($group);
        }

        return $this->lacking_gms = ($needed_gms - $gms);
    }

    /**
     * returns array of tilmeldinger for the afvikling
     *
     * @param string $type - spiller|spilleder
     * @access public
     * @return array
     */
    public function getSignupByType($type = null)
    {
        if (!$this->isLoaded()) {
            return array();
        }

        $tilmeldinger = $this->createEntity('DeltagereTilmeldinger');
        $select       = $tilmeldinger->getSelect();
        $select->setWhere('afvikling_id','=',$this->id);

        if ($type && in_array($type, array('spiller', 'spilleder'))) {
            $select->setWhere('tilmeldingstype','=',$type);
        }

        $select->setOrder('tilmeldingstype','desc');
        $select->setOrder('prioritet','asc');

        return $tilmeldinger->findBySelectMany($select);
    }

    /**
     * returns all priority 1 signups for the schedule
     *
     * @access public
     * @return array
     */
    public function getPrioritySignups()
    {
        if (!$this->isLoaded())
        {
            return array();
        }
        $tilmeldinger = $this->createEntity('DeltagereTilmeldinger');
        $select = $tilmeldinger->getSelect();
        $select->setWhere('afvikling_id','=',$this->id);
        $select->setWhere('tilmeldingstype','=','spiller');
        $select->setWhere('prioritet', '=', 1);
        return $tilmeldinger->findBySelectMany($select);
    }

    /**
     * returns array of pladser for the afvikling
     *
     * @param string $type - spiller|spilleder
     * @access public
     * @return array
     */
    public function getAssignedByType($type = null)
    {
        if (!$this->isLoaded())
        {
            return array();
        }
        $results = array();
        $hold = (($hold = $this->getHold()) ? $hold : array());
        foreach ($hold as $h)
        {
            $pladser = $this->createEntity('Pladser');
            $select = $pladser->getSelect();
            $select->setWhere('hold_id','=',$h->id);
            if ($type && in_array($type, array('spiller', 'spilleder'))) {
                $select->setWhere('type','=',$type);
            }

            $results[$h->id] = $pladser->findBySelectMany($select);
        }

        return $results;
    }

    /**
     * returns the amount of people that have signed up for an activity
     *
     * @access public
     * @return int
     */
    public function getSignupCount()
    {
        return count($this->getSignupByType());
    }

    /**
     * returns the first found group needing the type of deltager
     *
     * @param string $type - spiller|spilleder
     * @access public
     * @return bool|object
     */
    public function getGroupNeeding($type)
    {
        if (!$this->isLoaded() || !in_array($type, array('spiller', 'spilleder')))
        {
            return false;
        }
        foreach ($this->getHold() as $hold)
        {
            if (($type == 'spiller' && $hold->canUseGamers()) || ($type == 'spilleder' && $hold->needsGMs())) {
                return $hold;
            }
        }
        return false;
    }

    /**
     * returns array with all dates that have activities starting on them
     *
     * @access public
     * @return array
     */
    public function getAllDates()
    {
        $select = $this->getSelect();
        $select->setField('DISTINCT (DATE (start))', false);
        $db = $this->getDB();
        $result = $db->query($select);
        $select = $this->createEntity('AfviklingerMultiblok')->getSelect();
        $select->setField('DISTINCT (DATE (start))', false);
        if ($results = $db->query($select))
        {
            $result = array_merge($result,$results);
        }
        $return = array();
        foreach ($result as $row)
        {
            $return[] = $row[0];
        }
        $return = array_unique($return);
        sort($return);
        return $return;
    }

    /**
     * returns array of scheduled activies for the given date
     *
     * @param string $date - the date to search for
     * @access public
     * @return array
     */
    public function getAfviklingerForDate($date, $order = 'id')
    {
        if (!in_array($order,array('id', 'navn')))
        {
            $order = 'id';
        }
        $select = $this->getSelect();
        $select->setWhere('DATE(start - INTERVAL 4 HOUR)','=',$date, false);
        $select->setTableWhere('afviklinger.aktivitet_id','aktiviteter.id');
        $select->setFrom('aktiviteter');
        $select->setField('afviklinger.id');
        $select->setField('afviklinger.aktivitet_id');
        $select->setField('afviklinger.start');
        $select->setField('afviklinger.slut');
        $select->setOrder('aktiviteter.navn','asc');
        $select->setOrder('afviklinger.start','asc');
        $result = $this->createEntity('Afviklinger')->findBySelectMany($select);
        $select = $this->createEntity('AfviklingerMultiblok')->getSelect();
        $select->setTableWhere('afviklinger.aktivitet_id','aktiviteter.id');
        $select->setTableWhere('afviklinger.id','afviklinger_multiblok.afvikling_id');
        $select->setFrom('aktiviteter');
        $select->setFrom('afviklinger');
        $select->setField('afviklinger_multiblok.id');
        $select->setField('afviklinger_multiblok.afvikling_id');
        $select->setField('afviklinger_multiblok.start');
        $select->setField('afviklinger_multiblok.slut');
        $select->setOrder('aktiviteter.navn','asc');
        $select->setOrder('afviklinger_multiblok.start','asc');
        $select->setWhere('DATE(afviklinger_multiblok.start - INTERVAL 4 HOUR)','=',$date, false);
        return array_merge($result,$this->createEntity('AfviklingerMultiblok')->findBySelectMany($select));
    }

    /**
     * checks if the current scheduled activity is chained for several times
     *
     * @access public
     * @return int
     */
    public function hasMultiBlok()
    {
        if (!$this->isLoaded())
        {
            return false;
        }
        $select = $this->createEntity('AfviklingerMultiblok')->getSelect();
        $select->setWhere('afvikling_id','=',$this->id);
        return $this->selectCount($select);
    }


    /**
     * returns chained events in an array, if the current schedule is chained
     *
     * @access public
     * @return array
     */
    public function getMultiBlok()
    {
        if (!$this->isLoaded() || !$this->hasMultiBlok())
        {
            return array();
        }
        $select = $this->createEntity('AfviklingerMultiblok')->getSelect();
        $select->setWhere('afvikling_id','=',$this->id);
        return $this->createEntity('AfviklingerMultiblok')->findBySelectMany($select);
    }

    /**
     * returns all schedules, sorted by time
     *
     * @access public
     * @return array
     */
    public function getAllSchedules()
    {
        $strategy = new Strategy;
        $afv = (($result = $this->findAll()) ? $result : array());
        $multi = (($result = $this->createEntity('AfviklingerMultiblok')->findAll()) ? $result : array());
        return $strategy->sortSchedules($this->getPlayableSchedules(array_merge($afv, $multi)));
    }

    private function getPlayableSchedules(array $schedules)
    {
        $result = array();
        $a = $this->createEntity('Aktiviteter');
        $s = $a->getSelect();
        $s->setWhere('max_deltagere_per_hold', '>', 0);
        $activities = $a->findBySelectMany($s);
        $ids = array();
        foreach ($activities as $activity)
        {
            $ids[] = $activity->id;
        }
        foreach ($schedules as $schedule)
        {
            if (in_array($schedule->aktivitet_id, $ids))
            {
                $result[] = $schedule;
            }
        }

        return $result;
    }

    /**
     * returns total possible time of play at the convention
     *
     * @access public
     * @return int - time in seconds
     */
    public function getMaxPlaytime()
    {

        $strategy = new Strategy;
        return $strategy->getMaxTimeForSchedules($this->getAllSchedules());
    }

    /**
     * returns all participants assigned to a team
     *
     * @access public
     * @return array
     */
    public function getParticipantsOnTeams($player_type = null)
    {
        if (!$this->isLoaded()) {
            return array();
        }
        $participants_on_teams = array();
        foreach ($this->getHold() as $hold)
        {
            foreach ($hold->getPladser() as $plads) {
                if ($player_type && $player_type != $plads->type) {
                    continue;
                }

                $participants_on_teams[] = $plads->deltager_id;
            }
        }
        return $participants_on_teams;
    }

    /**
     * returns all scheduled activities concurrent
     * with this one, which the participant has signed
     * up for
     *
     * @param DeltagereTilmeldinger $signup Participant signup
     *
     * @access public
     * @return array
     */
    public function getConcurrentSchedulesForSignup(DeltagereTilmeldinger $signup)
    {
        if (!$this->id) {
            return array();
        }

        $query = '
SELECT
    ' . implode(', ', $this->getColumns()) . '
FROM
    afviklinger
WHERE
    id IN (SELECT afvikling_id FROM deltagere_tilmeldinger WHERE deltager_id = ?)
    AND (
        start BETWEEN ? AND ?
        OR slut BETWEEN ? AND ?
        OR (start < ? AND slut > ?)
    )
';

        return $this->loadObjects($this->db->query($query, array($signup->deltager_id, $this->start, $this->slut, $this->start, $this->slut, $this->start, $this->slut)));
    }

    /**
     * shows the object as being not multiblock
     *
     * @access public
     * @return bool
     */
    public function isMultiBlock()
    {
        return false;
    }

    /**
     * creates votes for the schedule
     *
     * @access public
     * @return array
     */
    public function createVotes(Page $page)
    {
        require_once LIB_FOLDER . 'phpqrcode/phpqrcode.php';

        mt_srand(time() . $this->id);

        $query = '
DELETE FROM
    schedules_votes
WHERE
    schedule_id = ?
';

        $this->db->exec($query, [$this->id]);

        $query     = 'INSERT INTO schedules_votes (schedule_id, code, cast_at) VALUES ';
        $values    = [];
        $arguments = [];
        $votes     = [];

        $activity = $this->getActivity();
        $teams    = $this->getHold();

        for ($i = 0; $i < ($activity->max_deltagere_per_hold + $activity->spilledere_per_hold) * count($teams); $i++) {
            $values[] = '(?, ?, "0000-00-00 00:00:00")';

            $code = $this->makeVoteCode();
            
            array_push($arguments, $this->id, $code);

        }

        if ($values) {
            $this->db->exec($query . implode(', ', $values), $arguments);

            $query = 'SELECT id, code FROM schedules_votes WHERE schedule_id = ?';

            foreach ($this->db->query($query, [$this->id]) as $row) {
                $votes[] = [
                            'id'   => $row['id'],
                            'code' => $row['code'],
                           ];

                QRcode::png($page->url('activity_specific_vote', ['code' => $row['code']]), PUBLIC_PATH . 'vote-barcodes/' . $row['id'] . '.png', 'M', 3);

            }

        }

        return $votes;
    }

    /**
     * creates a voting code - a random 8 character string
     *
     * @access public
     * @return string
     */
    public function makeVoteCode()
    {
        $base = 'abcdefghjkmnpqrstuvwxyz23456789';
        $length = strlen($base);

        $code = '';

        while (strlen($code) < 8) {
            $code .= $base[mt_rand(0, $length - 1)];
        }

        return $code;
    }
}
