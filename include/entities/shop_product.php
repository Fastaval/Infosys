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
class ShopProduct extends DBObject
{
    /**
     * Name of database table
     *
     * @var string
     */
    protected $tablename = 'shopproducts';

    private $events;

    private $updates = array();

    public function getEvents($type)
    {
        return array_filter($this->getAllEvents(), function ($x) use ($type) {
            return $x['type'] === $type;
        });
    }

    protected function getAllEvents()
    {
        if (isset($events)) {
            return $this->events;
        }

        $query = '
SELECT
    id,
    type,
    amount,
    timestamp
FROM
    shopevents
WHERE
    shopproduct_id = ?
ORDER BY
    timestamp ASC,
    CASE WHEN type = "cost" OR type = "price" THEN 0 ELSE 1 END ASC
';

        return $this->events = $this->db->query($query, array($this->id));
    }

    protected function getValue($type)
    {
        return array_sum(array_map(function ($x) {return $x['amount'];}, $this->getEvents($type)));
    }

    public function getCost()
    {
        return $this->getValue('cost');
    }

    public function getPrice()
    {
        return $this->getValue('price');
    }

    public function getStock()
    {
        return $this->getValue('stock');
    }

    public function getSold()
    {
        return $this->getValue('sold');
    }

    public function updateValues(array $updates)
    {
        $this->updates = array();

        foreach ($updates as $type => $value) {
            switch ($type) {
            case 'cost':
                $this->handleUpdate($this->getCost(), $value, $type);
                break;

            case 'price':
                $this->handleUpdate($this->getPrice(), $value, $type);
                break;

            case 'stock':
                $this->handleUpdate($this->getStock(), $value, $type);
                break;

            case 'sold':
                $this->handleUpdate($this->getSold(), $value, $type);
                break;

            default:
                throw new Exception('Unknown type to update');
            }
        }

        $this->runUpdates();
    }

    protected function handleUpdate($current, $new, $type)
    {
        if (floatval($current) === floatval($new)) {
            return;
        }

        $this->updates[] = array('
INSERT INTO shopevents (type, shopproduct_id, amount, timestamp) VALUES (?, ?, ?, NOW())
', array($type, $this->id, $new - $current));
    }

    protected function runUpdates()
    {
        if (empty($this->updates)) {
            return;
        }

        foreach ($this->updates as $update) {
            $this->db->exec($update[0], $update[1]);
        }

        $this->updates = array();
        $this->events  = array();
    }

    public function remove()
    {
        $this->status = 0;
        $this->update();
    }

    public function getProfit()
    {
        $cost = $price = $profit = 0;

        foreach ($this->getAllEvents() as $event) {
            switch ($event['type']) {
            case 'cost':
                $cost += $event['amount'];
                break;

            case 'price':
                $price += $event['amount'];
                break;

            case 'sold':
                $profit += ($price - $cost) * $event['amount'];
                break;
            }
        }

        return $profit;
    }
}
