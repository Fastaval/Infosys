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
 * @package   Models
 * @author    Peter Lind <peter.e.lind@gmail.com>
 * @copyright 2009-2012 Peter Lind
 * @license   http://www.gnu.org/licenses/gpl.html GPL 3
 * @link      http://www.github.com/Fake51/Infosys
 */

/**
 * handles all data fetching for the loans controller
 *
 * @category Infosys
 * @package  Models
 * @author   Peter Lind <peter.e.lind@gmail.com>
 * @license  http://www.gnu.org/licenses/gpl.html GPL 3
 * @link     http://www.github.com/Fake51/Infosys
 */
class LoansModel extends Model
{
    public function fetchAllData()
    {
        $data = array(
            'loanData'        => $this->fetchLoanData(),
            'participantData' => $this->fetchPartipantData(),
            'activityData'    => $this->fetchActivityData(),
            'notes'           => $this->fetchNote(),
            'stats'           => $this->fetchStats(),
        );

        return $data;
    }

    public function fetchStats()
    {
        $return = array();

        $query = '
SELECT COUNT(*) AS count FROM loanevents WHERE TYPE = "borrowed"
';

        foreach ($this->db->query($query) as $row) {
            $return['Udlån samlet'] = $row['count'];
        }

        $query = '
SELECT DATE(timestamp) AS date, COUNT(*) AS count FROM loanevents WHERE TYPE = "borrowed" GROUP BY DATE(timestamp) ORDER BY date
';

        foreach ($this->db->query($query) as $row) {
            $return['Udlån ' . $row['date']] = $row['count'];
        }

        $query = '
SELECT b.name, COUNT(*) AS count FROM loanitems AS b JOIN loanevents AS be ON be.loanitem_id = b.id WHERE type = "borrowed" GROUP BY b.name ORDER BY count DESC LIMIT 3;
';

        $index = 1;

        foreach ($this->db->query($query) as $row) {
            $return['Top ' . $index++] = $row['name'] . ' (' . $row['count'] . ')';
        }

        $loanevents = array();
        $time       = 0;

        $query = '
SELECT be.type, be.loanitem_id, be.timestamp FROM loanevents AS be WHERE be.type IN ("borrowed", "returned") ORDER BY be.loanitem_id, be.timestamp
';

        foreach ($this->db->query($query) as $row) {
            if ($row['type'] === 'borrowed') {
                $loanevents[$row['loanitem_id']] = $row['timestamp'];
                continue;
            }

            if ($row['type'] === 'returned' && !empty($loanevents[$row['loanitem_id']])) {
                $time += round((strtotime($row['timestamp']) - strtotime($loanevents[$row['loanitem_id']])) / 3600, 2);
            }
        }

        $return['Samlet udlånstid i timer'] = $time;

        $query = '
SELECT
    COUNT(*) as count
FROM
    loanitems
';

        foreach ($this->db->query($query) as $row) {
            $return['Samlet antal ting'] = $row['count'];
        }

        $query = '
SELECT
    bge.loanitem_id,
    COUNT(*) AS count
FROM
    loanevents AS bge
    JOIN (
        SELECT
            loanitem_id,
            type
        FROM (
            SELECT
                loanitem_id,
                type
            FROM
                loanevents
            WHERE
                type IN ("borrowed", "returned")
            ORDER BY
                loanitem_id,
                timestamp DESC
        ) AS temped
        GROUP BY
            loanitem_id
    ) AS temp ON temp.loanitem_id = bge.loanitem_id
WHERE
    bge.loanitem_id NOT IN (SELECT loanitem_id FROM loanevents WHERE type = "finished")
    AND temp.type = "borrowed"
GROUP BY
    bge.loanitem_id
';

        $return['Udlån lige nu'] = count($this->db->query($query));

        return $return;
    }

    /**
     * fetch all loan details
     *
     * @access protected
     * @return array
     */
    public function fetchLoanData()
    {
        $loanitems = $this->createEntity('Loanitem')->findAll();

        $output = array();

        $borrowed_stats = $this->fetchBorrowingStats();

        foreach ($loanitems as $loanitem) {
            $details = array(
                'id'             => intval($loanitem->id),
                'barcode'        => $loanitem->barcode,
                'name'           => $loanitem->name,
                'owner'          => $loanitem->owner,
                'comment'        => $loanitem->comment,
                'log'            => $loanitem->getLog(),
                'borrowed_count' => isset($borrowed_stats[$loanitem->id]) ? $borrowed_stats[$loanitem->id] : 0,
            );

            if ($loanitem->isBorrowed()) {
                $details['borrowed'] = $loanitem->getBorrowedDetails();

            } elseif ($loanitem->isFinished()) {
                $details['returned'] = $loanitem->getFinishedDetails();

            }

            $output[] = $details;

        }

        return $output;
    }

    /**
     * fetches stats on loanitem borrowing
     *
     * @access protected
     * @return array
     */
    protected function fetchBorrowingStats()
    {
        $query = '
SELECT
    loanitem_id,
    COUNT(*) AS stat
FROM
    loanevents
WHERE
    type = "borrowed"
GROUP BY
    loanitem_id';

        $stats = [];

        foreach ($this->db->query($query) as $row) {
            $stats[$row['loanitem_id']] = $row['stat'];
        }

        return $stats;
    }

    public function fetchPartipantData()
    {
        $select = $this->createEntity('Deltagere')->getSelect();

        $select->setWhere('annulled', '=', 'nej')
            ->setWhere('signed_up', '>', '0000-00-00');

        $participants = array();

        foreach ($this->createEntity('Deltagere')->findBySelectMany($select) as $participant) {
            if (trim($participant->getName() == "")) {
                continue;
            }

            $participants[] = array(
                'id'      => intval($participant->id),
                'name'    => $participant->getName(),
                'barcode' => $participant->getEan8Number(),
            );
        }

        return $participants;
    }

    public function fetchActivityData()
    {
        $select = $this->createEntity('Hold')->getSelect();

        $select->setWhere('lokale_id', '=', 64);

        $groups = $this->createEntity('Hold')->findBySelectMany($select);

        usort($groups, function ($a, $b) {
            return strtotime($a->getSchedule()->start) < strtotime($b->getSchedule()->start) ? -1 : 1;
        });

        $return = array();

        foreach ($groups as $group) {
            $data = array(
                'timestamp' => $group->getSchedule()->start,
                'name'      => $group->getActivity()->navn,
                'attendees' => array(),
            );

            foreach ($group->getPladser() as $spot) {
                $data['attendees'][] = $spot->getParticipant()->getName();
            }

            $return[] = $data;
        }

        return $return;
    }

    public function createLoanItem(RequestVars $post)
    {
        if (!$post->name || !$post->owner || !$post->barcode) {
            throw new Exception('Missing data for loan item creation');
        }

        $loanitem = $this->createEntity('Loanitem');

        $loanitem->name    = $post->name;
        $loanitem->owner   = $post->owner;
        $loanitem->barcode = $post->barcode;
        $loanitem->comment = isset($post->comment) ? $post->comment : '';

        $loanitem->insert();

        $this->log('Udlånsenhed (' . $loanitem->id . ', ' . $post->name . ') oprettet af ' . $this->getLoggedInUser()->user, 'Loans', $this->getLoggedInUser());

        return $loanitem;
    }

    public function updateLoansStatus(RequestVars $post)
    {
        if (!$post->loanitemId || !$post->status) {
            throw new Exception('Missing data for loanitem update');
        }

        $select = $this->createEntity('Loanitem')->getSelect();

        $select->setWhere('id', '=', $post->loanitemId);

        if (!($loanitem = $this->createEntity('Loanitem')->findBySelect($select))) {
            throw new Exception('No loanitem with id: ' . $post->loanitemId);
        }

        $data = array();

        if ($post->status === 'borrowed') {
            $data['participant_id'] = isset($post->participantId) ? $post->participantId : '';
            $data['participant']    = isset($post->participant) ? $post->participant : '';
            $data['comment']        = isset($post->comment) ? $post->comment : '';
        }

        $loanitem->setStatus($post->status, $data);

        $this->log('Udlånsenhed (' . $loanitem->id . ', ' . $loanitem->name . ') status rettet til ' . $post->status . ' af ' . $this->getLoggedInUser()->user, 'Loans', $this->getLoggedInUser());
    }

    public function editLoanitem(RequestVars $post)
    {
        if (!$post->loanitemId || !$post->name || !$post->owner) {
            throw new Exception('Missing data for loanitem update');
        }

        $select = $this->createEntity('Loanitem')->getSelect();

        $select->setWhere('id', '=', $post->loanitemId);

        if (!($loanitem = $this->createEntity('Loanitem')->findBySelect($select))) {
            throw new Exception('No loanitem with id: ' . $post->loanitemId);
        }

        $loanitem->name = $post->name;
        $loanitem->owner = $post->owner;
        $loanitem->barcode = !empty($post->barcode) ? $post->barcode : '';
        $loanitem->comment = !empty($post->comment) ? $post->comment : '';

        $loanitem->update();

        $this->log('Udlånsenhed (' . $loanitem->id . ', ' . $post->name . ') redigeret af ' . $this->getLoggedInUser()->user, 'Loans', $this->getLoggedInUser());

        return $loanitem;
    }

    public function parseSpreadsheetData(RequestVars $post)
    {
        if (empty($post->input)) {
            throw new Exception('No input from request');
        }

        $required_headers = array(
                             'Navn',
                             'Ejer',
                             'Stregkode',
                             'Kommentar',
                            );

        $header_index = array();

        $data = explode("\n", str_replace(array("\r\n", "\r"), "\n", $post->input));

        $headers = array_flip(explode("\t", array_shift($data)));

        foreach ($required_headers as $header) {
            if (!isset($headers[$header])) {
                throw new Exception('Lacking header ' . $header);
            }

            $header_index[$header] = $headers[$header];
        }

        $query = 'DELETE FROM loanevents;';
        $this->db->exec($query);

        $query = 'DELETE FROM loanitems;';
        $this->db->exec($query);

        foreach ($data as $row) {
            if (strlen(trim($row)) === 0) {
                continue;
            }

            $columns = explode("\t", $row);

            $loanitem = $this->createEntity('Loanitem');

            $loanitem->name = $columns[$header_index['Navn']];
            $loanitem->owner = $columns[$header_index['Ejer']];
            $loanitem->barcode = $columns[$header_index['Stregkode']];
            $loanitem->comment = $columns[$header_index['Kommentar']];

            $loanitem->insert();
        }

        $this->log('Brætspils data blev upload og resat af ' . $this->getLoggedInUser()->user, 'Loans', $this->getLoggedInUser());

        return true;
    }

    protected function fetchNote()
    {
        $query = '
SELECT
    note
FROM notes
WHERE area = "loans"';

        foreach ($this->db->query($query) as $row) {
            return $row['note'];
        }

        return '';
    }

    public function updateLoansNote(RequestVars $post)
    {
        $query = '
INSERT INTO notes
SET note = ?, updated = NOW(), area = "loans"
ON DUPLICATE KEY UPDATE note = ?';

        $this->db->exec($query, array($post->note, $post->note));

        return $this;
    }
}
