<?php
/**
 * Copyright (C) 2015 Peter Lind
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
 * sets up the environment, with various defines
 * contains autoload function too
 *
 * @category  Infosys
 * @package   Framework
 * @author    Peter Lind <peter.e.lind@gmail.com>
 * @copyright 2015 Peter Lind
 * @license   http://www.gnu.org/licenses/gpl.html GPL 3
 * @link      http://www.github.com/Fake51/Infosys
 */

/**
 * responsible for autoloading classes
 *
 * @category Infosys
 * @package  Framework
 * @author   Peter Lind <peter.e.lind@gmail.com>
 * @license  http://www.gnu.org/licenses/gpl.html GPL 3
 * @link     http://www.github.com/Fake51/Infosys
 */
class Karma
{
    /**
     * DB instance
     *
     * @var DB
     */
    private $db;

    /**
     * karma rules stash
     *
     * @var array
     */
    private $rules;

    /**
     * public constructor
     *
     * @param DB    $db    DB connection
     * @param array $rules Karma rules
     *
     * @access public
     */
    public function __construct(DB $db, array $rules)
    {
        $this->db    = $db;
        $this->rules = $rules;
    }

    /**
     * returns array of participant indexed data
     *
     * @param array|DBObject $input Particpant(s) to get data for
     *
     * @access public
     * @return array
     */
    public function getParticipantData($input)
    {
        if (is_object($input)) {
            $input = [$input];
        }

        $sanitizer = function ($x) {
            return intval($x->id);
        };

        $id_string = implode(', ', array_map($sanitizer, $input));

        if (!$id_string) {
            return [];
        }

        $query = '
SELECT
    p.deltager_id,
    akt.karmatype,
    "factual" AS type
FROM
    pladser AS p
    JOIN hold AS h ON p.hold_id = h.id
    JOIN afviklinger AS afv ON afv.id = h.afvikling_id
    JOIN aktiviteter AS akt ON akt.id = afv.aktivitet_id
WHERE
    p.deltager_id IN (' . $id_string . ')
    AND p.type != "spilleder"
UNION ALL
SELECT
    t.deltager_id,
    CASE WHEN t.tilmeldingstype = "spilleder" THEN 0 ELSE t.prioritet END,
    "potential" AS type
FROM
    deltagere_tilmeldinger AS t
WHERE
    t.deltager_id IN (' . $id_string . ')
';

        $data = [];

        foreach ($this->db->query($query) as $row) {
            $data[$row['deltager_id']][$row['type']][] = $row['karmatype'];
        }

        return $data;
    }

    /**
     * calculates karma for a participant - given array of participant data
     *
     * @param array $data Signup/activity data for participant
     *
     * @access public
     * @return float
     */
    public function calculateForParticipant(array $data)
    {
        $calculate = function ($x) use ($data) {
            return $x->calculate($data);
        };

        return array_sum(array_map($calculate, $this->rules));
    }

    /**
     * calculates the karma for one or more
     * participants
     *
     * @param DBObject|array $input Participant or array of participants
     *
     * @throws Exception
     * @access public
     * @return float|array
     */
    public function calculate($blob)
    {
        if (is_a($blob, 'DBObject')) {
            $participants = [$blob];

        } elseif (is_array($blob)) {
            $participants = $blob;

        } else {
            throw new Exception('Provided argument is neither array nor DBObject');
        }

        $output = array_map([$this, 'calculateForParticipant'], $this->getParticipantData($participants));

        return count($output) <= 1 ? array_pop($output) : $output;
    }
}
