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
     * handles all data fetching for the food controller
     *
     * @package    MVC
     * @subpackage Models
     * @author     Peter Lind <peter.e.lind@gmail.com>
     */
class FoodModel extends Model
{

    /**
     * fetches stats for ordered food
     *
     * @access public
     * @return array
     */
/*
    public function getFoodStats()
    {
        if (!($madtider = $this->createEntity('Madtider')->findAll()))
        {
            return array();
        }
        $return = array();
        foreach ($madtider as $madtid)
        {
            $select = $this->createEntity('DeltagereMadtider')->getSelect();
            $select->setWhere('madtid_id','=',$madtid->id);
            $return[$madtid->id] = $this->createEntity('DeltagereMadtider')->selectCount($select);
        }
        foreach (range(0, 50) as $idx)
        {
            if (empty($return[$idx]))
            {
                $return[$idx] = 0;
            }
        }
        return $return;
    }
    */

    /**
     * returns all Mad entities
     *
     * @access public
     * @return array
     */
    public function getAllFood()
    {
        return (($result = $this->createEntity('Mad')->findAll()) ? $result : array());
    }

    /**
     * tries to create a food type
     *
     * @param RequestVars $post - POST vars
     *
     * @access public
     * @return bool
     */
    public function createFood(RequestVars $post)
    {
        if (empty($post->kategori) || empty($post->pris) || !is_numeric($post->pris) || !is_string($post->kategori))
        {
            return false;
        }
        $food = $this->createEntity('Mad');
        $food->kategori = $post->kategori;
        $food->pris = $post->pris;
        return (($food->insert() && $this->updateFoodDates($food, $post)) ? $food : false);
    }

    /**
     * tries to update a mad type
     *
     * @param Mad         $food - Mad entity
     * @param RequestVars $post - POST vars
     *
     * @access public
     * @return bool
     */
    public function updateFood(Mad $food, RequestVars $post)
    {
        if (empty($post->kategori) || empty($post->pris) || !is_numeric($post->pris) || !is_string($post->kategori) || !$food->isLoaded())
        {
            return false;
        }
        $food->kategori = $post->kategori;
        $food->pris = $post->pris;
        if (!$food->update())
        {
            return false;
        }
        return $this->updateFoodDates($food, $post);

    }

    /**
     * inserts and deletes times for food
     *
     * @param Mad         $food - Mad entity
     * @param RequestVars $post - POST vars
     *
     * @access protected
     * @return bool
     */
    protected function updateFoodDates(Mad $food, RequestVars $post)
    {
        $tider = $food->getMadtider();
        $tider_id = $this->extractIds($tider);
        $success = true;
        if (!empty($post->foodtime_id) && !empty($post->foodtime_date))
        {
            $i = 0;
            foreach ($post->foodtime_id as $id)
            {
                if (in_array($id, $tider_id))
                {
                    for ($ii = 0; $ii < count($tider_id); $ii++)
                    {
                        if ($tider_id[$ii] == $id)
                        {
                            unset($tider_id[$ii]);
                            sort($tider_id);
                        }
                        break;
                    }
                    $foodtime = $this->findEntity('Madtider', $id);
                    $foodtime->dato = $post->foodtime_date[$i];
                    $foodtime->update();
                    $i++;
                    continue;
                }
                elseif ($id > 0)
                {
                    $i++;
                    continue;
                }
                $new_foodtime = $this->createEntity('Madtider');
                $new_foodtime->mad_id = $food->id;
                $new_foodtime->dato = $post->foodtime_date[$i];
                if (!$new_foodtime->insert())
                {
                    $success = false;
                }
                $i++;
            }
        }
        if (!$success)
        {
            return false;
        }
        foreach ($tider_id as $tid_id)
        {
            foreach ($tider as $tid)
            {
                if ($tid->id == $tid_id)
                {
                    $tid->delete();
                    continue;
                }
            }

        }
        return $success;
    }

    /**
     * finds any current food times
     * which means any an hour from now, till
     * two hours from now
     *
     * @access public
     * @return array
     */
    public function findCurrentFoodItems() {
        $datetime = date('Y-m-d H:i:s');
        $select = $this->createEntity('Madtider')->getSelect()
            ->setRawWhere('"' . $datetime . '" BETWEEN dato - INTERVAL 10 MINUTE AND dato + INTERVAL 130 MINUTE', 'and');

        return $this->createEntity('Madtider')->findBySelectMany($select);
    }

    /**
     * returns the next upcoming food period as a
     * date string
     *
     * @access public
     * @return null|string
     */
    public function findNextFoodTime() {
        $select = $this->createEntity('Madtider')->getSelect()
            ->setRawWhere('dato > NOW()', 'and')
            ->setOrder('dato', 'asc');
        if ($food = $this->createEntity('Madtider')->findBySelect($select)) {
            return $food->dato;
        }
        return null;
    }

    /**
     * marks a participant-fooditem relationship
     * as done
     *
     * @param RequestVars $post
     *
     * @throws Exception
     * @access public
     * @return string
     */
    public function markFoodReceived(RequestVars $post) {
        $deltager = $this->ajaxFoodCrud($post);
        if (!strtotime($deltager->checkin_time)) {
            throw new FrameworkException("<strong>Fejl:</strong> deltageren (ID: {$deltager->id}) er ikke tjekket ind.");
        }

        $madtider = $this->ajaxGetRelevantFood($deltager, $post);
        $received = array();

        foreach ($madtider as $madtid) {
            if (!$deltager->hasReceivedFood($madtid)) {
                $deltager->markFoodReceived($madtid);
                $received[] = $madtid;
            }
        }

        if (empty($received)) {
            throw new FrameworkException("<strong>Fejl:</strong> deltageren (ID: {$deltager->id}) har allerede fået udleveret mad");
        }

        $texts = array_map(function($x) {return '<strong>' . $x->getMad()->kategori . '</strong>';}, $received);

        $this->log("Deltager #{$deltager->id} har fået mad udleveret af {$this->getLoggedInUser()->user}", 'Mad', $this->getLoggedInUser());
        return htmlspecialchars($deltager->fornavn . " " . $deltager->efternavn, ENT_QUOTES) . " (ID: {$deltager->id}):  " . implode(' +++ ', $texts) . "<br/>Markeret som modtaget - hvis det er en fejl, så tryk på Undo-knappen.";
    }

    /**
     * checks input vars for markFoodReceived/markFoodNotReceived
     * and returns the relevant participant
     *
     * @param RequestVars $post
     *
     * @throws Exception
     * @access protected
     * @return Deltagere
     */
    protected function ajaxFoodCrud(RequestVars $post) {
        if (empty($post->user_id) || empty($post->fooditem_ids) || !is_array($post->fooditem_ids)) {
            throw new FrameworkException ("<strong>Fejl:</strong> bruger id eller mad info mangler");
        }
        $temp_id = EANToNumber($post->user_id) ? EANToNumber($post->user_id) : $post->user_id;
        if (!($deltager = $this->createEntity('Deltagere')->findById($temp_id))) {
            throw new FrameworkException ("<strong>Fejl:</strong> ingen bruger med det id");
        }
        if ($deltager->udeblevet == 'ja') {
            throw new FrameworkException ("<strong>Fejl:</strong> deltageren er ikke tjekket ind endnu");
        }
        return $deltager;
    }

    /**
     * returns relevant food item period for the participant
     * for marking as served/not served
     *
     * @param Deltagere   $deltager
     * @param RequestVars $post
     *
     * @throws Exception
     * @access protected
     * @return Madtid
     */
    protected function ajaxGetRelevantFood($deltager, $post) {
        $foods = array();
        foreach ($deltager->getMadtider() as $madtid) {
            if (in_array($madtid->id, $post->fooditem_ids)) {
                $foods[] = $madtid;
            }
        }

        if ($foods) {
            return $foods;
        }

        throw new FrameworkException("<strong>Fejl:</strong> deltageren (ID: {$deltager->id}) har ikke tilmeldt mad for denne periode");
    }

    /**
     * marks a participant-fooditem relationship
     * as undone (i.e. undoes it)
     *
     * @param RequestVars $post
     *
     * @throws Exception
     * @access public
     * @return string
     */
    public function undoReceiveFood(RequestVars $post) {
        $deltager = $this->ajaxFoodCrud($post);
        $madtider = $this->ajaxGetRelevantFood($deltager, $post);

        $unreceived = array();
        foreach ($madtider as $madtid) {
            if ($deltager->hasReceivedFood($madtid)) {
                $unreceived[] = $madtid;
                $deltager->markFoodNotReceived($madtid);
            }
        }

        if (empty($unreceived)) {
            throw new FrameworkException("<strong>Fejl:</strong> deltageren " . e($deltager->getName()) . " (ID: {$deltager->id}) er ikke markeret som udleveret for denne periode");
        }

        $texts = array_map(function($x) {return '<strong>' . $x->getMad()->kategori . '</strong>';}, $unreceived);

        $this->log("Deltager #{$deltager->id} har fået mad markeret ikke-udleveret af {$this->getLoggedInUser()->user}", 'Mad', $this->getLoggedInUser());
        return htmlspecialchars($deltager->fornavn . " " . $deltager->efternavn, ENT_QUOTES) . " (ID: {$deltager->id}) har fået slettet markeringen for " . implode(', ', $texts) . ".";
    }

    public function retrieveHandoutStats(RequestVars $post) {
        if (empty($post->fooditem_ids)) {
            throw new FrameworkException("Ingen mad periode at tjekke stats for");
        }
        $return = array();
        foreach ($post->fooditem_ids as $id) {
            if (!($madtid = $this->createEntity('Madtider')->findById($id))) {
                throw new FrameworkException("Mad perioden med id {$id} eksisterer ikke");
            }
            $return[] = array(
                'name'          => $madtid->getMad()->kategori,
                'stats'         => array(
                    'Serveret'      => $madtid->getServedCount(),
                    'Ikke serveret' => $madtid->getLeftCount(),
                ),
            );
        }
        return $return;
    }

    /**
     * fetches statistics regarding food
     *
     * @access public
     * @return array
     */
    public function getFoodStats() {
        $return = array();
        $foods = $this->createEntity('Mad')->findAll();
        foreach ($foods as $food) {
            $return[$food->kategori] = array('total' => 0);
            foreach ($food->getMadtider() as $madtid) {
                $return[$food->kategori][$madtid->dato]['total'] = 0;
                $return[$food->kategori][$madtid->dato]['udleveret'] = 0;
                $return[$food->kategori]['total'] += 0;

                $query = "SELECT received, COUNT(*) FROM deltagere_madtider WHERE madtid_id = ? GROUP BY received ORDER BY received";
                foreach ($res = $this->db->query($query, array($madtid->id)) as $part) {
                    if ($part[0] == '1') {
                        $return[$food->kategori][$madtid->dato]['total'] += $part[1];
                        $return[$food->kategori][$madtid->dato]['udleveret'] = $part[1];
                        $return[$food->kategori]['total'] += $part[1];
                    } else {
                        $return[$food->kategori][$madtid->dato]['total'] += $part[1];
                        $return[$food->kategori]['total'] += $part[1];
                    }
                }
            }
        }
        $entry = $this->createEntity('Indgang')->findByType('banquet');
        if ($entry) {
            $return["Jubilæumsfest"][$entry->start]['total'] = $entry->getParticipantCount();
            $return["Jubilæumsfest"]['total'] = $return["Jubilæumsfest"][$entry->start]['total'];
        }
        return $return;
    }

    /**
     * returns statistics for food that can be resold
     *
     * @access public
     * @return array
     */
    public function getTradeableFood()
    {
        $query = '
SELECT
    m.kategori,
    mt.id,
    mt.dato,
    COUNT(*) AS tradeable
FROM
    mad AS m
    JOIN madtider AS mt ON mt.mad_id = m.id
    JOIN deltagere_madtider AS dmt ON dmt.madtid_id = mt.id
    JOIN deltagere AS d ON d.id = dmt.deltager_id
WHERE
    betalt_beloeb    = 0
    AND checkin_time = "0000-00-00 00:00:00"
    AND mt.dato > NOW() - INTERVAL 2 HOUR
GROUP BY
    mt.id,
    mt.dato,
    m.kategori
ORDER BY
    mt.dato,
    m.kategori
';

        $tradeable_food = array();
        foreach ($this->db->query($query) as $row) {
            $tradeable_food[$row['dato']][$row['kategori']] = $row['tradeable'];
        }

        return $tradeable_food;
    }

    /**
     * returns statistics for food that can be resold
     *
     * @access public
     * @return array
     */
    public function getMaybeTradeableFood()
    {
        $query = '
SELECT
    m.kategori,
    mt.dato,
    CONCAT(d.fornavn, " ", d.efternavn) AS name,
    d.id AS participant_id
FROM
    mad AS m
    JOIN madtider AS mt ON mt.mad_id = m.id
    JOIN deltagere_madtider AS dmt ON dmt.madtid_id = mt.id
    JOIN deltagere AS d ON d.id = dmt.deltager_id
WHERE
    checkin_time = "0000-00-00 00:00:00"
    AND mt.dato > NOW() - INTERVAL 2 HOUR
    AND d.betalt_beloeb > 0
ORDER BY
    mt.dato,
    m.kategori,
    name
';

        $maybe_tradeable_food = array();
        foreach ($this->db->query($query) as $row) {
            $maybe_tradeable_food[$row['dato']][$row['kategori']][] = array('participant_id' => $row['participant_id'], 'name' => $row['name']);
        }

        return $maybe_tradeable_food;
    }

    public function updateFoodHandoutTimes()
    {
        $participants = $this->createEntity('Deltagere')->findAll();

        $usage = array();

        foreach ($this->createEntity('Mad')->findAll() as $food) {
            foreach ($food->getMadtider() as $madtid) {
                if ($madtid->isDinner()) {
                    $usage[$madtid->dato] = array(1 => 0, 2 => 0, 3 => 0);
                }
            }
        }

        ksort($usage);

        $early_categories = array('Vegetar');

        foreach ($participants as $participant) {
            foreach ($participant->getMadtider() as $madtid) {
                $link = $participant->getFoodItemLink($madtid);
                $food = $madtid->getMad();

                if (!$food || $madtid->isBreakfast()) {
                    continue;
                }

                if (in_array($food->kategori, $early_categories) || $participant->isBusyBetween($madtid->dato, date('Y-m-d H:i:s', strtotime($madtid->dato . ' + 2 hour')), 'spilleder')) {
                    $link->time_type = 1;
                    $link->update();
                    $usage[$madtid->dato][1]++;

                    continue;
                }

                $time_type       = $this->getTimeType($usage, $madtid);
                $link->time_type = $time_type;
                $link->update();
                $usage[$madtid->dato][$time_type]++;
            }
        }

        $this->log("Der er opsat madtids-valg for " . count($participants) . " deltagere.", "Mad", $this->getLoggedInUser());

    }

    public function getTimeType(array $usage, MadTider $madtid) {
        $type = 0;
        $min  = 1000;

        foreach ($usage[$madtid->dato] as $id => $people) {
            if ($people < $min) {
                $type = $id;
                $min  = $people;
            }
        }

        return $type;
    }
}
