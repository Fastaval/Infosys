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
 * handles all data fetching for the economy MVC
 *
 * @category Infosys
 * @package  Models
 * @author   Peter Lind <peter.e.lind@gmail.com>
 * @license  http://www.gnu.org/licenses/gpl.html GPL 3
 * @link     http://www.github.com/Fake51/Infosys
 */
class EconomyModel extends Model
{

    /**
     * returns detailed numbers for
     * the conventions budget, grouped by participants
     *
     * @access public
     * @return array
     */
    public function computeDetailedBudget()
    {
        $query = "
SELECT
    d.id,
    CONCAT(d.fornavn, ' ', d.efternavn) AS name,
    bk.navn AS category,
    d.betalt_beloeb AS paid_amount,
    d.udeblevet,
    IFNULL(e.entrance_cost, 0) AS entrance_cost,
    IFNULL(s.sleep_cost, 0) AS sleep_cost,
    IFNULL(w.wear_cost, 0) AS wear_cost,
    IFNULL(f.food_cost, 0) AS food_cost,
    IFNULL(a.activity_cost, 0) AS activity_cost,
    CASE WHEN d.hemmelig_onkel = 'ja' AND d.rig_onkel = 'ja' THEN 600 WHEN d.hemmelig_onkel = 'ja' OR d.rig_onkel = 'ja' THEN 300 ELSE 0 END as uncle_cost
FROM
    deltagere AS d
    JOIN brugerkategorier AS bk ON bk.id = d.brugerkategori_id

    LEFT JOIN (SELECT
        deltager_id,
        SUM(i.pris) AS entrance_cost
    FROM
        deltagere_indgang AS di
        JOIN indgang AS i ON i.id = di.indgang_id
    WHERE
        i.type NOT LIKE '%OVERNATNING%'
    GROUP BY
        deltager_id
    ) AS e ON e.deltager_id = d.id

    LEFT JOIN (SELECT
        deltager_id,
        SUM(i.pris) AS sleep_cost
    FROM
        deltagere_indgang AS di
        JOIN indgang AS i ON i.id = di.indgang_id
    WHERE
        i.type LIKE '%OVERNATNING%'
    GROUP BY
        deltager_id
    ) AS s ON s.deltager_id = d.id

    LEFT JOIN (SELECT
        deltager_id,
        SUM(wp.pris) AS wear_cost
    FROM
        deltagere_wear_order AS dwp
        JOIN wearpriser AS wp ON wp.id = dwp.wearpris_id
    GROUP BY
        deltager_id
    ) AS w ON w.deltager_id = d.id
    
    LEFT JOIN (SELECT
        dm.deltager_id,
        SUM(f.pris) AS food_cost
    FROM
        deltagere_madtider AS dm
        JOIN madtider AS m ON m.id = dm.madtid_id
        JOIN mad AS f ON f.id = m.mad_id
    GROUP BY
        deltager_id
    ) AS f ON f.deltager_id = d.id

    LEFT JOIN (SELECT
        p.deltager_id,
        SUM(ak.pris) AS activity_cost
    FROM
        pladser AS p
        JOIN hold AS h ON h.id = p.hold_id
        JOIN afviklinger AS af ON af.id = h.afvikling_id
        JOIN aktiviteter AS ak ON ak.id = af.aktivitet_id
    GROUP BY
        deltager_id
    ) AS a ON a.deltager_id = d.id
ORDER BY
    d.id;
";
        return $this->db->query($query);
    }

    /**
     * returns overview of consumables ordered
     * at the convention
     *
     * @access public
     * @return array
     */
    public function computeAccountingData()
    {
        return array(
            'Entrance'   => $this->calculateEntranceDetails(),
            'Activities' => $this->calculateActivityDetails(),
            'Food'       => $this->calculateFoodDetails(),
            'Support'    => $this->calculateSupportDetails(),
            'Wear'       => $this->calculateWearDetails(),
            'Sponsors'   => $this->calculateSponsorDetails(),
        );
    }

    /**
     * returns economy details for entrance
     *
     * @access protected
     * @return array
     */
    protected function calculateEntranceDetails()
    {
        $query = "
SELECT
    i.type AS name,
    MIN(i.pris) AS price,
    SUM(i.pris) AS cost,
    COUNT(*) AS amount
FROM
    deltagere_indgang AS di
    JOIN indgang AS i ON i.id = di.indgang_id
GROUP BY
    i.type
ORDER BY
    i.type
";

        return $this->db->query($query);
    }

    /**
     * returns economy details about activities
     *
     * @access protected
     * @return array
     */
    protected function calculateActivityDetails()
    {
        $query = "
SELECT
    ak.navn AS name,
    ak.pris AS price,
    SUM(ak.pris) AS cost,
    COUNT(*) AS amount
FROM
    deltagere_tilmeldinger AS dt
    JOIN afviklinger AS af ON af.id = dt.afvikling_id
    JOIN aktiviteter AS ak ON ak.id = af.aktivitet_id
GROUP BY
    ak.navn,
    ak.pris
HAVING
    price > 0
ORDER BY
    ak.navn
";

        return $this->db->query($query);
    }

    /**
     * returns economy details about sponsors
     *
     * @access protected
     * @return array
     */
    protected function calculateSponsorDetails()
    {
        $query = "
SELECT
    'Rige onkler' AS name,
    300 AS price,
    COUNT(*) * 300 AS cost,
    COUNT(*) AS amount
FROM
    deltagere AS d
WHERE
    d.rig_onkel = 'ja'
GROUP BY
    name
UNION
SELECT
    'Hemmelige onkler' AS name,
    300 AS price,
    COUNT(*) * 300 AS cost,
    COUNT(*) AS amount
FROM
    deltagere AS d
WHERE
    d.hemmelig_onkel = 'ja'
GROUP BY
    name
";

        return $this->db->query($query);
    }

    /**
     * returns economy details for food
     *
     * @access protected
     * @return array
     */
    protected function calculateFoodDetails()
    {
        $query = "
SELECT
    m.kategori AS name,
    MIN(m.pris) AS price,
    SUM(m.pris) AS cost,
    COUNT(*) AS amount
FROM
    deltagere_madtider AS dm
    JOIN madtider AS mt ON mt.id = dm.madtid_id
    JOIN mad AS m ON m.id = mt.mad_id
GROUP BY
    m.kategori
HAVING
    price > 0
ORDER BY
    m.kategori
";

        return $this->db->query($query);
    }

    protected function calculateSupportDetails() {
        $food_query = "SELECT * FROM mad";
        $count_query = 
            "SELECT COUNT(*) AS participants, IFNULL(LEAST(fc.food_count, 2), 0) AS `food count` 
            FROM deltagere d 
            LEFT JOIN (
                SELECT deltager_id, COUNT(*) AS food_count 
                FROM deltagere_madtider dm 
                JOIN madtider mt ON dm.madtid_id = mt.id 
                WHERE mt.mad_id = ?
                GROUP BY deltager_id
            ) AS fc ON fc.deltager_id = d.id
            WHERE d.financial_struggle = 'ja'
            GROUP BY `food count`
            HAVING `food count` > 0";

        $categories = [];
        foreach($this->db->query($food_query) as $food_category) {
            if ($food_category['pris'] == 0) continue;

            $count = 0;
            foreach ($this->db->query($count_query, [$food_category['id']]) as $support_count) {
                $count += $support_count['participants'] * $support_count['food count'];
            }
            if ($count == 0) continue;

            $categories[] = [
                'name' => $food_category['kategori'],
                'price' => -$food_category['pris'],
                'cost' => -$count * $food_category['pris'],
                'amount' => $count,
            ];
        }

        return $categories;
    }

    /**
     * returns economy details for wear
     *
     * @param variable variable Description
     *
     * @access public
     * @return array
     */
    public function calculateWearDetails()
    {
        $query = "
SELECT
    CONCAT(w.navn, ' - ', bk.navn) AS name,
    MIN(wp.pris) AS price,
    SUM(wp.pris) AS cost,
    COUNT(*) AS amount
FROM
    deltagere_wear_order AS dw
    JOIN wearpriser AS wp ON wp.id = dw.wearpris_id
    JOIN brugerkategorier AS bk ON bk.id = wp.brugerkategori_id
    JOIN wear AS w ON w.id = wp.wear_id
GROUP BY
    name
ORDER BY
    name
";

        return $this->db->query($query);
    }
}
