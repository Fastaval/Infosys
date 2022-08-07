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
     * handles all data fetching for the index controller
     *
     * @package MVC
     * @subpackage Models
     */

class GdsModel extends Model
{
    /**
     * returns all dates that have GDS shifts
     *
     * @access public
     * @return array
     */
    public function getGDSDates()
    {
        $gds = $this->createEntity('GDSVagter');
        $select = $gds->getSelect();
        $select->setField('DISTINCT DATE(start)',false)->
                 setOrder('start','asc');
        $results = $this->db->query($select);
        $result = array();
        foreach ($results as $row)
        {
            $result[] = $row[0];
        }
        return $result;
    }

    /**
     * returns all shifts for a given date
     *
     * @param string $date - date to get shifts for
     * @access public
     * @return array
     */
    public function getGDSShiftsForDate($date)
    {
        $gds = $this->createEntity('GDSVagter');
        $select = $gds->getSelect();
        $select->setFrom('gds')->
                 setTableWhere('gds.id','gdsvagter.gds_id')->
                 setOrder('gdsvagter.start','asc')->
                 setOrder('gds.navn','asc')->
                 setWhere('DATE(start)','=',$date, false);
        return $gds->findBySelectMany($select);
    }

    /**
     * returns all gds categories, ordered alphabetically
     *
     * @access public
     * @return array
     */
    public function getGDSCategories()
    {
        $select = $this->createEntity('GDS')->getSelect();
        $select->setOrder('navn','asc');
        return $this->createEntity('GDS')->findBySelectMany($select);
    }

    public function findGDS($id) {
        return $this->createEntity('GDS')->findById($id);
    }

    /**
     * update a participants no show status for a diy shift
     *
     * @param RequestVars $post POST vars from request
     *
     * @throws FrameworkException
     * @access public
     * @return void
     */
    public function updateContactCount(RequestVars $post)
    {
        if (!($participant = $this->createEntity('Deltagere')->findById($post->participant_id))) {
            throw new FrameworkException('Cannot locate participant (' . $post->participant_id . ')');
        }

        $participant->contacted_about_diy += 1;
        $participant->update();
    }

    /**
     * update a participants no show status for a diy shift
     *
     * @param RequestVars $post POST vars from request
     *
     * @throws FrameworkException
     * @access public
     * @return void
     */
    public function updateNoshowStatus(RequestVars $post)
    {
        if (
            !($participant = $this->createEntity('Deltagere')->findById($post->participant_id))
            || !($shift = $this->createEntity('GDSVagter')->findById($post->shift_id))
        ) {
            throw new FrameworkException('Cannot locate participant (' . $post->participant_id . ') or shift (' . $post->shift_id . ')');
        }

        $relationship = $this->createEntity('DeltagereGDSVagter');
        $relationship = $relationship->findBySelect(
            $relationship->getSelect()
                ->setWhere('deltager_id', '=', $participant->id)
                ->setWhere('gdsvagt_id', '=', $shift->id)
            );

        if (!$relationship) {
            throw new FrameworkException('Cannot location participant/diy shift relationship');
        }

        $relationship->noshow = intval($post->state);
        $relationship->update();
    }

    /**
     * returns array of details for participants
     * that might take on a diy duty shift
     *
     * @param int $shift_id Id of diy duty shift to get suggestions for
     *
     * @throws FrameworkException
     * @access public
     * @return array
     */
    public function getShiftSuggestions($shift_id)
    {
        if (!($shift = $this->createEntity('GDSVagter')->findById(intval($shift_id)))) {
            throw new FrameworkException('Shift not found (' . intval($shift_id) . ') - cannot make suggestions');
        }

        $noshow_ids     = $this->getIdsOfNoshows();
        $multishift_ids = $this->getIdsOfMultiShifts();

        return $this->findShiftSuggestions($shift, $noshow_ids, $multishift_ids);
    }

    /**
     * returns array of suggestions for the given diy shift
     *
     * @param DBObject $shift          Shift to make suggestions for
     * @param array    $noshow_ids     Ids of duty noshows
     * @param array    $multishift_ids Ids of participants wanting more
     *
     * @access protected
     * @return array
     */
    protected function findShiftSuggestions(DBObject $shift, array $noshow_ids, array $multishift_ids)
    {
        $noshow_ids_clause     = implode(', ', $noshow_ids);
        $multishift_ids_clause = implode(', ', $multishift_ids);

        $query = <<<SQL
SELECT
    d.id,
    CONCAT(d.fornavn, ' ', d.efternavn) AS name,
    d.mobiltlf,
    d.medbringer_mobil,
    d.supergds,
    d.flere_gdsvagter,
    d.contacted_about_diy,
    CASE WHEN d.id IN ({$noshow_ids_clause}) THEN 'no-show' WHEN d.id IN ({$multishift_ids_clause}) THEN 'multi-gds' ELSE 'supergds' END AS cause,
    COUNT(temp.deltager_id) AS shifts_assigned
FROM
    deltagere AS d
    LEFT JOIN (
        SELECT dgc.deltager_id, COUNT(*)
        FROM deltagere_gdsvagter AS dgc
        WHERE noshow = 0
        GROUP BY dgc.deltager_id
    ) AS temp ON temp.deltager_id = d.id
WHERE
    d.id NOT IN (
    SELECT p.deltager_id
    FROM pladser AS p
        JOIN hold AS h ON h.id = p.hold_id
        JOIN afviklinger AS a ON a.id = h.afvikling_id
        JOIN aktiviteter AS ak ON ak.id = a.aktivitet_id
        LEFT JOIN afviklinger_multiblok AS am ON am.afvikling_id = a.id
    WHERE ((a.start <= ? AND a.slut >= ?) OR (a.start <= ? AND a.slut >= ?) OR (a.start >= ? AND a.slut <= ?))
        AND ak.tids_eksklusiv = 'ja'
    )
    AND d.id NOT IN (
    SELECT dg.deltager_id
    FROM deltagere_gdsvagter AS dg 
        JOIN gdsvagter AS g ON g.id = dg.gdsvagt_id
    WHERE ((g.start <= ? AND g.slut >= ?) OR (g.start <= ? AND g.slut >= ?) OR (g.start >= ? AND g.slut <= ?))
    )
    AND (d.id IN ({$noshow_ids_clause}) OR d.id IN ({$multishift_ids_clause}) OR d.supergds = 'ja')
    AND d.checkin_time > '0000-00-00'
    AND d.medbringer_mobil = 'ja'
    AND d.udeblevet = 'nej'
GROUP BY
    d.id,
    name,
    d.mobiltlf,
    d.medbringer_mobil,
    d.supergds,
    d.flere_gdsvagter,
    d.contacted_about_diy,
    cause
ORDER BY
    CASE WHEN cause = 'no-show' THEN 2 WHEN cause = 'multi-gds' THEN 1 ELSE 0 END,
    d.contacted_about_diy,
    RAND()
SQL;

        return $this->db->query($query, array($shift->start, $shift->start, $shift->slut, $shift->slut, $shift->start, $shift->slut, $shift->start, $shift->start, $shift->slut, $shift->slut, $shift->start, $shift->slut));
    }

    /**
     * gets ids of participants willing to take on
     * more duty shifts but only signed up for one
     * so far
     *
     * @access protected
     * @return array
     */
    protected function getIdsOfMultiShifts()
    {
        $query = <<<SQL
SELECT
    d.id,
    COUNT(temp.id) AS made
FROM
    deltagere AS d
LEFT JOIN (
    SELECT
        d.id
    FROM
        deltagere AS d
        JOIN deltagere_gdsvagter AS dg ON dg.deltager_id = d.id
    WHERE
        dg.noshow = 0
) AS temp ON d.id = temp.id
WHERE
    d.flere_gdsvagter = 'ja'
GROUP BY d.id
HAVING made < 2
SQL;

        $ids = array(0);
        foreach ($this->db->query($query) as $row) {
            $ids[] = $row['id'];
        }

        return $ids;
    }

    /**
     * returns array of participants not showing
     * up for duties and thus lacking duties
     *
     * @access protected
     * @return array
     */
    protected function getIdsOfNoshows()
    {
        $query = <<<SQL
SELECT
    dg.deltager_id,
    COUNT(temp1.id) AS missed,
    COUNT(temp2.id) AS made
FROM
    deltagere_gdsvagter AS dg
LEFT JOIN (
    SELECT
        d.id
    FROM
        deltagere AS d
        JOIN deltagere_gdsvagter AS dg ON dg.deltager_id = d.id
    WHERE
        dg.noshow = 1
) AS temp1 ON temp1.id = dg.deltager_id
LEFT JOIN (
    SELECT
        d.id
    FROM
        deltagere AS d
        JOIN deltagere_gdsvagter AS dg ON dg.deltager_id = d.id
    WHERE
        dg.noshow = 0
) AS temp2 ON dg.deltager_id = temp2.id
GROUP BY dg.deltager_id
HAVING missed > made
SQL;
        
        $ids = array(0);
        foreach ($this->db->query($query) as $row) {
            $ids[] = $row['deltager_id'];
        }

        return $ids;
    }

    /**
     * updates a diy category and shifts
     *
     * @param GDS         $diy  DIY category
     * @param RequestVars $post POST object
     *
     * @access public
     * @return array
     */
    public function updateDIY(GDS $diy, RequestVars $post)
    {
        $diy_updated = false;

        if (isset($post->name) && $post->name !== $diy->navn) {
            $diy_updated = true;
            $diy->navn   = $post->name;
        }

        if (isset($post->title_en) && $post->title_en !== $diy->title_en) {
            $diy_updated   = true;
            $diy->title_en = $post->title_en;
        }

        if ($diy_updated) {
            $diy->update();
        }

        $actual_shifts = $diy->getShifts();
        $form_shifts   = isset($post->gds_shifts) ? $post->gds_shifts : array();

        foreach ($actual_shifts as $shift) {
            if (!isset($form_shifts[$shift->id])) {
                $shift->delete();
                continue;
            }

            $form_shift = $form_shifts[$shift->id];

            $update = false;
            if (strtotime($form_shift['start']) !== strtotime($shift->start)) {
                $shift->start = date('Y-m-d H:i:s', strtotime($form_shift['start']));
                $update = true;
            }

            if (strtotime($form_shift['slut']) !== strtotime($shift->slut)) {
                $shift->slut = date('Y-m-d H:i:s', strtotime($form_shift['slut']));
                $update = true;
            }

            if (intval($form_shift['antal_personer']) !== intval($shift->antal_personer)) {
                $shift->antal_personer = intval($form_shift['antal_personer']);
                $update = true;
            }

            if ($update) {
                $shift->update();
            }

            unset($form_shifts[$shift->id]);
        }

        foreach ($form_shifts as $id => $new_shift) {
            if (intval($id) === 0 || empty($new_shift['start']) || empty($new_shift['slut']) || empty($new_shift['antal_personer'])) {
                continue;
            }

            $shift                 = $this->createEntity('GDSVagter');
            $shift->start          = date('Y-m-d H:i:s', strtotime($new_shift['start']));
            $shift->slut           = date('Y-m-d H:i:s', strtotime($new_shift['slut']));
            $shift->antal_personer = intval($new_shift['antal_personer']);
            $shift->gds_id         = $diy->id;

            $shift->insert();
        }

        return array();
    }

    /**
     * returns participants for a gds shift 
     *
     * @param int $shift_id ID of shift to get participants for
     *
     * @access public
     * @return array
     */
    public function getParticipantsForShift($shift_id)
    {
        $shift = $this->createEntity('GDSVagter')->findById($shift_id);

        return $shift->getParticipants();
    }

    /**
     * fethches signups for a diy shift
     *
     * @param $shift_id Id of shift to fetch signups for
     *
     * @access public
     * @return array
     */
    public function fetchDiyShiftSignups($shift_id)
    {
        $vagt = $this->findEntity('GDSVagter', $shift_id);

        if (!$vagt) {
            return [];
        }

        $deltagere = $vagt->getSignups();
        $on_shift  = $vagt->getParticipants();

        for ($i = 0; $i < count($on_shift); $i++) {
            $on_shift[$i] = $on_shift[$i]->id;
        }

        $output = array();

        $date = new DateTime($this->config->get('con.start'));

        foreach ($deltagere as $d) {
            if (in_array($d->id, $on_shift)) {
                continue;
            }

            if ($d->isSingleDayParticipant()) {
                continue;
            }

            $disabled  = 'false';
            $maxshifts = 'false';

            if ($d->isBusyBetween($vagt->start, $vagt->slut)) {
                $disabled = 'true';
            }

            if ($d->hasMaxShifts()) {
                $maxshifts = 'true';
            }

            $age = $d->getAge($date);

            $output[] = [
                'id'             => $d->id,
                'navn'           => e($d->getName()) . ($age < 18 ? ' (u18)' : ''),
                'mobil'          => e($d->mobiltlf),
                'disabled'       => $disabled,
                'maxshifts'      => $maxshifts,
                'assignedShifts' => count($d->getGDSVagter()),
                'email'          => e($d->email),
                'note'           => e($d->deltager_note),
                'medical_note'   => e($d->medical_note),
                'age'            => $age,
                'isGamemaster'   => $d->isGamemaster(),
               ];

        }

        return $output;
    }
}
