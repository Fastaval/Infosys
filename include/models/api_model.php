<?php
    /**
     * Copyright (C) 2011  Peter Lind
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
     * @copyright  2011 Peter Lind
     * @license    http://www.gnu.org/licenses/gpl.html GPL 3
     * @link       http://www.github.com/Fake51/Infosys
     */

    /**
     * handles all data fetching for the activity MVC
     *
     * @package    MVC
     * @subpackage Models
     * @author     Peter Lind <peter.e.lind@gmail.com>
     */
class ApiModel extends Model {

    // massively hackish
    public function authenticate(array $json) {
        $session = $this->dic->get('Session');

        if (empty($json['user']) || empty($json['token']) || !$session->api_token) {
            return false;
        }

        $api_users = $this->db->query("SELECT name, pass FROM api_auth WHERE name = ?", $json['user']);
        if (empty($api_users)) {
            return false;
        }

        if (md5($api_users[0]['pass'] . $session->api_token) === $json['token']) {
            $session = $this->dic->get('Session');
            $session->api_user = $json['user'];
            $session->save();
            return true;
        } else {
            return false;
        }
    }

    public function generateApiKey() {
        $session = $this->dic->get('Session');
        if (!($api_user = $session->api_user)) {
            throw new FrameworkException("No api user");
        }
        $key = md5($api_user . uniqid());
        $session->api_key = $key;
        $session->save();
        return array('api_key' => $key);
    }

    public function getActivityDataForDay($day, $all = false, $app_output = false, $timestamp = 1, $version = 1) {
        $ids = array();
        foreach ($this->db->query("SELECT aktivitet_id FROM afviklinger WHERE DATE(start) = ? OR id IN (SELECT afvikling_id FROM afviklinger_multiblok WHERE DATE(start) = ?)", array($day, $day)) as $row) {
            $ids[] = $row['aktivitet_id'];
        }
        if (empty($ids)) {
            return array();
        }
        return $this->getActivityData($ids, $all, $app_output, 0, $version);
    }

    public function getActivityDataForType($type, $all = false, $app_output = false, $timestamp = 1, $version = 1) {
        $ids = array();
        foreach ($this->db->query("SELECT id FROM aktiviteter WHERE type = ?", array($type)) as $row) {
            $ids[] = $row['id'];
        }
        if (empty($ids)) {
            return array();
        }
        return $this->getActivityData($ids, $all, $app_output, 0, $version);
    }

    /**
     * build the activity structure for the
     * JSON api call to get details of activities
     *
     * @param array    $ids              IDs of activities to return data for
     * @param bool     $all              True to include activities that cant be signed up for
     * @param bool     $app_output       True if creating output for mobile app
     * @param int      $timestamp        Timestamp to fetch changes after
     * @param DBObject $participant_type Type of participant to find activities for
     *
     * @access public
     * @return array
     */
    public function getActivityData(array $ids, $all = false, $app_output = false, $timestamp = 0, $version = 1, $birthdate_timestamp = null, $participant_type = null) {
        $select = $this->createEntity('Aktiviteter')
            ->getSelect();

        if ($ids) {
            $select->setWhere('id', 'in', $ids);
        }

        if ($timestamp) {
            $select->setWhere('updated', '>', date('Y-m-d H:i:s', $timestamp));
        }

        if (!$participant_type || $participant_type->navn !== 'Juniordeltager') {
            $select->setWhere('type', '!=', 'junior');
        }

        $result = $this->createEntity('Aktiviteter')->findBySelectMany($select);
        $return = array();
        $multiblok = $this->createEntity('AfviklingerMultiblok')->findAll();
        $multi_ids = array();

        foreach ($multiblok as $multi) {
            $multi_ids[] = $multi->afvikling_id;
        }

        if ($birthdate_timestamp) {
            $time_diff = (new DateTime($this->config->get('con.start')))->diff(new DateTime(date('Y-m-d', $birthdate_timestamp)));

            $age_at_con_start = $time_diff->y;

            $filter = function ($x) use ($age_at_con_start) {
                return !(
                    ($x->getMinAge() && $age_at_con_start < $x->getMinAge())
                    || ($x->getMaxAge() && $x->getMaxAge() < $age_at_con_start)
                    || ($x->getMaxAge() && $x->getMaxAge() < $age_at_con_start)
                    || ($age_at_con_start < 13 && !($x->type === 'junior' || intval($x->id) === 96 || intval($x->id) === 50))
                    || ($age_at_con_start >= 15 && $x->type === 'junior')
                );
            };

            $result = array_filter($result, $filter);

        }

        if ($result) {
            foreach ($result as $res) {
                if (($version == 1 && $res->type == 'system') || $res->hidden === 'ja') {
                    continue;
                }

                $text_da = strip_tags(mb_detect_encoding($res->foromtale, 'UTF-8', true) ? $res->foromtale : iconv("ISO-8859-1", "UTF-8", $res->foromtale));
                $text_en = strip_tags(mb_detect_encoding($res->description_en, 'UTF-8', true) ? $res->description_en : iconv("ISO-8859-1", "UTF-8", $res->description_en));

                if ($version == 2) {
                    $text_da = nl2br($text_da);
                    $text_en = nl2br($text_en);
                }

                $act = array(
                    'aktivitet_id' => intval($res->id),
                    'afviklinger' => array(),
                    'info' => array(
                        'title_da'       => $res->navn,
                        'text_da'        => $text_da,
                        'description_da' => '',
                        'title_en'       => $res->title_en,
                        'text_en'        => $text_en,
                        'description_en' => '',
                        'author'         => explode(',', $res->author),
                        'price'          => intval($res->pris),
                        'min_player'     => intval($res->min_deltagere_per_hold),
                        'max_player'     => intval($res->max_deltagere_per_hold),
                        'gms'            => intval($res->spilledere_per_hold),
                        'type'           => $res->type,
                        'play_hours'     => floatval($res->varighed_per_afvikling),
                        'language'       => $res->sprog,
                        'wp_id'          => $res->wp_link,
                        'can_sign_up'    => $res->kan_tilmeldes === 'ja' ? 1 : 0,
                    ),
                );

                if ($app_output) {
                    foreach ($act['info'] as $key => $value) {
                        $act[$key] = $value;
                    }

                    $act['author'] = implode(',', $act['author']);

                    unset($act['info']);
                }

                foreach ($res->getAfviklinger() as $afvikling) {
                    if ($res->kan_tilmeldes == 'nej' && !$all || (strtotime($afvikling->end) - strtotime($afvikling->start) > 86400)) {
                        //continue;
                    }

                    $lokale = $this->createEntity('Lokaler')->findById($afvikling->lokale_id);
                    $time = array(
                        'afvikling_id' => intval($afvikling->id),
                        'aktivitet_id' => intval($res->id),
                        'lokale_id'    => $lokale ? $lokale->id : '',
                        'lokale_navn'  => $lokale ? $lokale->beskrivelse : '',
                        'start'        => $this->makeJsonTimestamp($afvikling->start, $app_output),
                        'end'          => $this->makeJsonTimestamp($afvikling->slut, $app_output),
                        'linked'       => 0,
                        'length'       => round((strtotime($afvikling->slut) - strtotime($afvikling->start)) / 3600, 1),
                    );

                    if ($app_output) {
                        $time['stop'] = $time['end'];
                        unset($time['end']);
                    }

                    $act['afviklinger'][] = $time;

                    if (in_array($afvikling->id, $multi_ids)) {
                        foreach ($multiblok as $multi) {
                            if ($multi->afvikling_id == $afvikling->id) {
                                $time = array(
                                    'afvikling_id' => intval($multi->id) . '00' . intval($afvikling->id),
                                    'aktivitet_id' => intval($res->id),
                                    'lokale_id'    => $lokale ? $lokale->id : '',
                                    'lokale_navn'  => $lokale ? $lokale->beskrivelse : '',
                                    'start'        => $this->makeJsonTimestamp($multi->start, $app_output),
                                    'end'          => $this->makeJsonTimestamp($multi->slut, $app_output),
                                    'linked'       => $afvikling->id,
                                    'length'       => round((strtotime($multi->slut) - strtotime($multi->start)) / 3600, 1),
                                );

                                if ($app_output) {
                                    $time['stop'] = $time['end'];
                                    unset($time['end']);
                                }

                                $act['afviklinger'][] = $time;
                            }
                        }
                    }
                }
                $return[] = $act;
            }
        }
        return $return;
    }

    /**
     * build the activity structure for the
     * JSON api call to get details of activities
     *
     * @param array $ids
     *
     * @access public
     * @return array
     */
    public function getScheduleStructure(array $ids) {
        $select = $this->createEntity('Aktiviteter')
            ->getSelect();

        $result    = $this->createEntity('Aktiviteter')->findBySelectMany($select);
        $return    = array();

        $multiblok = $this->createEntity('AfviklingerMultiblok')->findAll();
        $multi_ids = array();

        foreach ($multiblok as $multi) {
            $multi_ids[] = $multi->afvikling_id;
        }

        if ($result) {
            foreach ($result as $res) {
                if ($res->kan_tilmeldes == 'nej') {
                    //continue;
                }

                foreach ($res->getAfviklinger() as $afvikling) {
                    if (count($ids) && !in_array($afvikling->id, $ids)) {
                        continue;
                    }

                    $lokale = $this->createEntity('Lokaler')->findById($afvikling->lokale_id);

                    $time = array(
                        'afvikling_id' => intval($afvikling->id),
                        'aktivitet_id' => intval($res->id),
                        'lokale_id'    => $lokale ? $lokale->id : '',
                        'lokale_navn'  => $lokale ? $lokale->beskrivelse : '',
                        'start'        => $this->makeJsonTimestamp($afvikling->start),
                        'end'          => $this->makeJsonTimestamp($afvikling->slut),
                        'linked'       => 0,
                    );

                    $return[] = $time;

                    if (in_array($afvikling->id, $multi_ids)) {
                        foreach ($multiblok as $multi) {
                            if ($multi->afvikling_id == $afvikling->id) {
                                $time = array(
                                    'afvikling_id' => intval($multi->id) . '00' . intval($afvikling->id),
                                    'aktivitet_id' => intval($res->id),
                                    'lokale_id'    => $lokale ? $lokale->id : '',
                                    'lokale_navn'  => $lokale ? $lokale->beskrivelse : '',
                                    'start' => $this->makeJsonTimestamp($multi->start),
                                    'end' => $this->makeJsonTimestamp($multi->slut),
                                    'linked' => $afvikling->id,
                                );

                                $return[] = $time;
                            }
                        }
                    }
                }
            }
        }

        usort($return, create_function('$a, $b', 'return $a["afvikling_id"] - $b["afvikling_id"];'));

        return $return;
    }

    public function getGDSShiftStructure(array $ids) {
        $select = $this->createEntity('GDSVagter')
            ->getSelect()
            ->setWhere('id', 'in', $ids);
        $result = $this->createEntity('GDSVagter')->findBySelectMany($select);
        $return = array();
        if ($result) {
            foreach ($result as $vagt) {
                $act = array(
                    'gds_id' => intval($vagt->gds_id),
                    'vagt_id' => intval($vagt->id),
                    'start' => $this->makeJsonTimestamp($vagt->start),
                    'end' => $this->makeJsonTimestamp($vagt->slut),
                    'people_needed' => intval($vagt->antal_personer),
                );
            }
            $return[] = $act;
        }
        return $return;
    }

    /**
     * build the GDS structure for the
     * JSON api call to get details of GDS
     *
     * @param array $ids
     *
     * @access public
     * @return array
     */
    public function getGDSStructure(array $ids) {
        $select = $this->createEntity('GDS')
            ->getSelect();
        if ($ids) {
            $select->setWhere('id', 'in', $ids);
        }

        $result = $this->createEntity('GDS')->findBySelectMany($select);
        $return = array();

        if ($result) {
            foreach ($result as $res) {
                $act = array(
                    'gds_id'      => intval($res->id),
                    'vagter'      => array(),
                    'category_id' => $res->category_id,
                    'info'        => array(
                        'title_da'       => $res->navn,
                        'description_da' => $res->beskrivelse,
                        'title_en'       => $res->title_en,
                        'description_en' => $res->description_en,
                        'category_da'    => $res->getCategory()->name_da,
                        'category_en'    => $res->getCategory()->name_en,
                    ),
                );
                $act['vagter'] = $this->prepareShiftStructure($res);
                $return[]      = $act;
            }
        }

        return $return;
    }

    /**
     * build the GDS structure for the
     * JSON api call to get details of GDS
     *
     * @param array $ids
     *
     * @access public
     * @return array
     */
    public function getGDSCategoryStructure(array $ids) {
        $select = $this->createEntity('GDSCategory')
            ->getSelect();
        if ($ids) {
            $select->setWhere('id', 'in', $ids);
        }

        $result = $this->createEntity('GDSCategory')->findBySelectMany($select);
        $return = array();

        if ($result) {
            foreach ($result as $res) {
                $category = array(
                    'gdscategory_id' => $res->id,
                    'category_da'    => $res->name_da,
                    'category_en'    => $res->name_en,
                    'shifts'         => array(),
                );

                foreach ($res->getDIYObjects() as $diy) {
                    $category['shifts'] = array_merge($category['shifts'], $this->prepareShiftStructure($diy));
                }

                $return[] = $category;
            }
        }

        return $return;
    }

    /**
     * creates
     *
     * @param
     *
     * @access protected
     * @return void
     */
    protected function prepareShiftStructure(GDS $gds)
    {
        $shifts = array();
        foreach ($gds->getVagter() as $shift) {
            $parsed = strtotime($shift->start);
            $time = date('G', $parsed);
            if ($time > 04 && $time < 12) {
                $period = date('Y-m-d ', $parsed) . '04-12';
            } elseif ($time >= 12 && $time <= 17) {
                $period = date('Y-m-d ', $parsed) . '12-17';
            } else {
		if ($time <= 4) {
                    $parsed = strtotime($shift->start . ' - 4 hours');
                }
                $period = date('Y-m-d ', $parsed) . '17-04';
            }

            if (empty($shifts[$period])) {
                $shifts[$period] = array(
                    'gds_id'        => intval($gds->id),
                    'period'        => $period,
                    'people_needed' => intval($shift->antal_personer),
                );
            } else {
                $shifts[$period]['people_needed'] += intval($shift->antal_personer);
            }
        }

        return $shifts;
    }

    /**
     * build the food structure for the
     * JSON api call to get details of food
     *
     * @param array $ids
     *
     * @access public
     * @return array
     */
    public function getFoodStructure(array $ids) {
        $select = $this->createEntity('Mad')
            ->getSelect();
        if ($ids) {
            $select->setWhere('id', 'in', $ids);
        }
        $result = $this->createEntity('Mad')->findBySelectMany($select);
        $return = array();
        if ($result) {
            foreach ($result as $res) {
                $act = array(
                    'mad_id' => intval($res->id),
                    'tider' => array(),
                    'info' => array(
                        'title_da' => $res->id == 2 ? 'Ja tak (vegetarisk)' : 'Ja tak',
                        'title_en' => $res->id == 2 ? 'Yes please (vegetarian)' : 'Yes please',
                        'price'    => intval($res->pris),
                    ),
                );
                foreach ($res->getMadtider() as $tid) {
                    $time = array(
                        'mad_id' => intval($res->id),
                        'madtid_id' => intval($tid->id),
                        'start' => $this->makeJsonTimestamp(date('Y-m-d', strtotime($tid->dato))),
                    );
                    $act['tider'][] = $time;
                }
                $return[] = $act;
            }
        }
        return $return;
    }

    /**
     * build the entrance structure for the
     * JSON api call to get details of entrance
     *
     * @param array $ids
     *
     * @access public
     * @return array
     */
    public function getEntranceStructure(array $ids) {
        $select = $this->createEntity('Indgang')
            ->getSelect();
        if ($ids) {
            $select->setWhere('id', 'in', $ids);
        }
        $result = $this->createEntity('Indgang')->findBySelectMany($select);
        $return = array();
        if ($result) {
            $weekdays = array('Søndag', 'Mandag', 'Tirsdag', 'Onsdag', 'Torsdag', 'Fredag', 'Lørdag', 'Søndag');
            foreach ($result as $res) {
                switch ($res->type) {
                    case 'entrance-all':
                        $text_dk = "Indgang - partout";
                        $text_en = "Entrance - all days";
                        break;
                    case 'entrance-day':
                        $text_dk = "Indgang - " . $weekdays[date('N', strtotime($res->start))];
                        $text_en = "Entrance - " . date('l', strtotime($res->start));
                        break;
                    case 'sleepover-all':
                        $text_dk = "Overnatning - partout";
                        $text_en = "Overnight - all days";
                        break;
                    case 'sleepover-day':
                        $text_dk = "Overnatning - " . $weekdays[date('N', strtotime($res->start))];
                        $text_en = "Overnight - " . date('l', strtotime($res->start));
                        break;
                }
                $act = array(
                    'indgang_id' => intval($res->id),
                    'type' => $res->type,
                    'price' => intval($res->pris),
                    'start' => $this->makeJsonTimestamp($res->start),
                    'text_da' => $text_dk,
                    'text_en' => $text_en,
                );
                $return[] = $act;
            }
        }
        return $return;
    }

    /**
     * attempts to parse a text string to a participant category
     *
     * @param string $type String indicating type
     *
     * @access public
     * @return BrugerKategorier|false
     */
    public function parseParticipantType($type)
    {
        $select = $this->createEntity('BrugerKategorier')
            ->getSelect()
            ->setWhere('navn', '=', str_replace('arrangoer', 'arrangør', $type));

        $usertype_obj = $this->createEntity('BrugerKategorier')->findBySelect($select);

        return $usertype_obj && $usertype_obj->id ? $usertype_obj : false;
    }


    /**
     * build the wear structure for the
     * JSON api call to get details of wear
     *
     * @param array $ids
     *
     * @access public
     * @return array
     */
    public function getWearStructure(array $ids, $usertype = '') {
        $select = $this->createEntity('Wear')
            ->getSelect();
        if ($ids) {
            $select->setWhere('id', 'in', $ids);
        }

        $result = $this->createEntity('Wear')->findBySelectMany($select);
        $return = array();

        if ($result) {

            $usertype_obj = null;

            if ($usertype) {
                $select = $this->createEntity('BrugerKategorier')
                    ->getSelect()
                    ->setWhere('navn', '=', str_replace('arrangoer', 'arrangør', $usertype));

                $usertype_obj = $this->createEntity('BrugerKategorier')->findBySelect($select);
            }

            foreach ($result as $res) {
                $act = array(
                    'wear_id'    => intval($res->id),
                    'size_range' => $res->size_range,
                    'title_da'   => $res->navn,
                    'title_en'   => $res->title_en,
                    'prices'     => array(),
                );

                if (file_exists(PUBLIC_PATH . 'uploads/wear/' . intval($res->id) . '.jpg')) {
                    $act['image'] = $this->config->get('app.public_uri') . 'uploads/wear/' . intval($res->id) . '.jpg';

                } elseif (file_exists(PUBLIC_PATH . 'uploads/wear/' . intval($res->id) . '.png')) {
                    $act['image'] = $this->config->get('app.public_uri') . 'uploads/wear/' . intval($res->id) . '.png';
                }

                foreach ($res->getWearpriser() as $price) {
                    if ($usertype_obj && $usertype_obj->id !== $price->brugerkategori_id) {
                        continue;
                    }

                    $act['prices'][] = array(
                        'wear_id' => intval($res->id),
                        'wearpris_id' => intval($price->id),
                        'brugerkategori_id' => intval($price->brugerkategori_id),
                        'category' => $price->getCategory()->navn,
                        'price' => intval($price->pris),
                    );
                }

                if (empty($act['prices'])) {
                    continue;
                }

                $return[] = $act;
            }
        }

        return $return;
    }

    public function makeJsonTimestamp($time, $app_output = false) {
        $timestamp = strtotime($time);

        if ($app_output) {
            return $timestamp;
        }

        return array(
            'day'       => date('j', $timestamp),
            'month'     => date('n', $timestamp),
            'year'      => date('Y', $timestamp),
            'h'         => date('G', $timestamp),
            'm'         => intval(date('i', $timestamp)),
            'date'      => date('j-n-Y', $timestamp),
            'datetime'  => date('j-n-Y G:i', $timestamp),
            'timestamp' => $timestamp,
            'mysql'     => date('Y-m-d H:i:s', $timestamp),
        );
    }

    public function createParticipant($post) {
        $data = array();;

        if (isset($post->data)) {
            $data = json_decode($post->data, true);
        }

        if (!isset($data['brugertype']) || !isset($data['email'])) {
            $data = array(
                'brugertype' => 'Deltager',
                'email'      => '',
            );

        }

        $bk  = $this->createEntity('BrugerKategorier');
        $sel = $bk->getSelect()->setWhere('navn', '=', $data['brugertype']);
        $bk->findBySelect($sel);

        $deltager                    = $this->createEntity('Deltagere');
        $deltager->email             = $data['email'];
        $deltager->fornavn           = $deltager->efternavn = $deltager->adresse1 = $deltager->postnummer = $deltager->by = $deltager->land = '';
        $deltager->medical_note      = $deltager->gcm_id = '';
        $deltager->password          = sprintf('%06d', mt_rand(100, 1000000));
        $deltager->brugerkategori_id = $bk->id;
        $deltager->gender            = 'm';
        $deltager->alder             = 0;
        $deltager->birthdate         = '0000-00-00';
        $deltager->annulled          = 'nej';
        $deltager->package_gds       = 0;
        $deltager->insert();

        $hash = $this->setParticipantPaymentHash($deltager);

        return array('id' => $deltager->id, 'password' => $deltager->password, 'payment_url' => $this->url('participant_payment', array('hash' => $hash)));
    }

    /**
     * creates a payment hash
     *
     * @param \Deltagere $participant Participant to set hash for
     *
     * @access public
     * @return string
     */
    public function setParticipantPaymentHash(DBObject $participant)
    {
        $query = '
INSERT INTO participantpaymenthashes SET participant_id = ?, hash = ? ON DUPLICATE KEY UPDATE hash = ?
';

        $hash = makeRandomString(32);

        $this->db->exec($query, [$participant->id, $hash, $hash]);

        return $hash;
    }

    public function addWear(array $json, $deltager = null) {
        try {
            if (!$deltager) {
                $deltager = $this->createEntity('Deltagere')->findById($json['id']);
            }

            if (!isset($json['wear']) || !is_array($json['wear'])) {
                throw new FrameworkException('No data available ');
            }

            $deltager->removeAllWear();
            //$this->db->exec("DELETE FROM deltagere_wear WHERE deltager_id = ?", $deltager->id);

            foreach ($json['wear'] as $wear) {
                $wearprice = $this->createEntity('WearPriser');
                $wearprice = $wearprice->findBySelect($wearprice->getSelect()->setWhere('wear_id', '=', $wear['id'])->setWhere('brugerkategori_id', '=', $deltager->brugerkategori_id));

                if (!$wearprice) {
                    $participant_category = $this->createEntity('BrugerKategorier');
                    $participant_category->findBySelect($participant_category->getSelect()->setWhere('navn', '=', 'Deltager'));
                    $wearprice = $this->createEntity('WearPriser');
                    $wearprice = $wearprice->findBySelect($wearprice->getSelect()->setWhere('wear_id', '=', $wear['id'])->setWhere('brugerkategori_id', '=', $participant_category->id));

                    if (!$wearprice) {
                        continue;
                    }

                }

                $deltager->setWearOrder($wearprice, $wear['size'], $wear['amount']);

//                $this->db->exec("INSERT INTO deltagere_wear (deltager_id, wearpris_id, size, antal) VALUES (?, ?, ?, ?)", $deltager->id, $wearprice->id, $wear['size'], $wear['amount']);
            }

        } catch (Exception $e) {
            return 'Failed to add wear choices for participant';
        }

        return '';
    }

    public function addGDS(array $json, $deltager) {
        try {
            if (!$deltager) {
                $deltager = $this->createEntity('Deltagere')->findById($json['id']);
            }

            if (!isset($json['gds']) || !is_array($json['gds'])) {
                throw new FrameworkException('No data available');
            }

            //$this->db->exec("DELETE FROM deltagere_gdstilmeldinger WHERE deltager_id = ?", $deltager->id);
            $deltager->removeDiySignup();

            foreach ($json['gds'] as $gds) {
                $category = $this->createEntity('GDSCategory')->findById($gds['kategori_id']);

                $deltager->setGDSTilmelding($category, $gds['period']);
                //$this->db->exec("INSERT INTO deltagere_gdstilmeldinger (deltager_id, category_id, period) VALUES (?, ?, ?)", $deltager->id, $gds['kategori_id'], $gds['period']);
            }
        } catch (Exception $e) {
            return 'Failed to add gds choices for participant';
        }
    }

    public function addFood(array $json, $deltager) {
        try {
            if (!$deltager) {
                $deltager = $this->createEntity('Deltagere')->findById($json['id']);
            }

            if (!isset($json['food']) || !is_array($json['food'])) {
                throw new FrameworkException('No data available');
            }

            $deltager->removeFood();
            //$this->db->exec("DELETE FROM deltagere_madtider WHERE deltager_id = ?", $deltager->id);

            foreach ($json['food'] as $food) {
                $foodtime = $this->createEntity('Madtider')->findById($food['madtid_id']);
                $deltager->setMad($foodtime);
                //$this->db->exec("INSERT INTO deltagere_madtider (deltager_id, madtid_id) VALUES (?, ?)", $deltager->id, $food['madtid_id']);
            }
        } catch (Exception $e) {
            return 'Failed to add food choices for participant';
        }
    }

    public function addActivity(array $json, $deltager = null) {
        try {
            if (!$deltager) {
                $deltager = $this->createEntity('Deltagere')->findById($json['id']);
            }

            if (!isset($json['activity']) || !is_array($json['activity'])) {
                throw new FrameworkException('No data available ');
            }

            $deltager->removeActivitySignups();

            foreach ($json['activity'] as $activity) {
                $schedule = $this->createEntity('Afviklinger')->findById($activity['schedule_id']);

                if (intval($activity['priority']) === 4) {
                    $deltager->setAktivitetTilmelding($schedule, 1, 'spiller');
                    $deltager->setAktivitetTilmelding($schedule, 0, 'spilleder');

                } else {
                    $deltager->setAktivitetTilmelding($schedule, $activity['priority'], $activity['type']);
                }
            }
        } catch (Exception $e) {
            $e->logException();
            return 'Failed to add activity choices for participant';
        }
    }

    public function addEntrance(array $json, $deltager = null) {
        try {
            if (!$deltager) {
                $deltager = $this->createEntity('Deltagere')->findById($json['id']);
            }

            if (!isset($json['entrance']) || !is_array($json['entrance'])) {
                throw new FrameworkException('No data available');
            }

            $deltager->removeEntrance();
            //$this->db->exec("DELETE FROM deltagere_indgang WHERE deltager_id = ?", $deltager->id);

            foreach ($json['entrance'] as $entrance) {
                $entry = $this->createEntity('Indgang')->findById($entrance['entrance_id']);

                $deltager->setIndgang($entry);
                //$this->db->exec("INSERT INTO deltagere_indgang (deltager_id, indgang_id) VALUES (?, ?)", $deltager->id, $entrance['entrance_id']);
            }
        } catch (Exception $e) {
            $e->logException();
            return 'Failed to add entrance choices for participant';
        }
    }

    /**
     * parses the actual signup data and sets it on
     * the provided participant object
     *
     * @param array     $data        Data to parse
     * @param Deltagere $participant Participant object
     *
     * @throws Exception
     * @access public
     * @return array
     */
    public function parseSignupData(array $data, DBObject $participant)
    {
        $participant->signed_up = date('Y-m-d H:i:s');
        $participant->annulled  = 'nej';

        $this->setParticipantData($participant, $data['participant']);

        try {
            if (!$participant->update()) {
                return ['Could not update database with participant data'];
            }

        } catch (Exception $e) {
            $e->logException();

            return ['Could not update database with participant data'];

        }

        $errors = array();

        if (!empty($data['wear']) && is_array($data['wear'])) {
            $errors[] = $this->addWear($data, $participant);

        } else {
            $errors[] = $this->addWear(['wear' => []], $participant);
        }

        if (!empty($data['activity']) && is_array($data['activity'])) {
            $errors[] = $this->addActivity($data, $participant);

        } else {
            $errors[] = $this->addActivity(['activity' => []], $participant);
        }

        if (!empty($data['entrance']) && is_array($data['entrance'])) {
            $errors[] = $this->addEntrance($data, $participant);
        }

        if (!empty($data['gds']) && is_array($data['gds'])) {
            $errors[] = $this->addGDS($data, $participant);

        } else {
            $errors[] = $this->addGDS(['gds' => []], $participant);
        }

        if (!empty($data['food']) && is_array($data['food'])) {
            $errors[] = $this->addFood($data, $participant);

        } else {
            $errors[] = $this->addFood(['food' => []], $participant);
        }

        return array_filter($errors);
    }

    /**
     * creates a dummy participant and adds all relevant data
     * from signup to it
     *
     * @param array $data Signup confirmation data
     *
     * @access public
     * @return array
     */
    public function parseSignupConfirmation(array $data)
    {
        $participant = $this->createEntity('DummyParticipant');

        $errors = $this->parseSignupData($data, $participant);

        if (!$errors) {
            return $participant;
        }

        throw new FrameworkException('Errors in signup data');
    }

    /**
     * runs through data from a signup to finalize
     * a participants signup
     *
     * @param array $data Data from post
     *
     * @access public
     * @return array
     */
    public function parseSignup(array $data)
    {
        $participant = $this->createEntity('Deltagere')->findById($data['id']);

        if (!$participant || !$participant->id) {
            $this->fileLog('No participant available for signup');
            return array(
                array(
                    'status'     => 'fail',
                    'failReason' => 'No such participant'
                ),
                $participant,
            );
        }

        if (isset($data['session'])) {
            file_put_contents(__DIR__ . '/../signup-data/session-' . $participant->id, $data['session']);
        }

        if (empty($data['participant'])) {
            $this->fileLog('No participant data available for signup');
            return array(
                array(
                    'status'     => 'fail',
                    'failReason' => 'No participant data sent to API'
                ),
                $participant,
            );
        }

        $errors = $this->parseSignupData($data, $participant);

        $errors = array_filter($errors);

        if ($errors) {
            $this->fileLog('Failed to create participant relations. Errors: ' . print_r($errors, true) . '. Data: ' . $data);
            $this->cleanParticipantSignup($participant);
            return array(
                array(
                    'status'     => 'fail',
                    'failReason' => implode("\n", $errors),
                ),
                $participant,
            );
        } else {
            return array(
                array(
                    'status'     => 'ok',
                    'failReason' => null,
                ),
                $participant,
            );
        }
    }

    /**
     * removes all signup choices for a participant
     *
     * @param Deltagere $participant Participant to clean data for
     *
     * @access protected
     * @return void
     */
    protected function cleanParticipantSignup(DBObject $participant)
    {
        $this->db->exec('DELETE FROM deltagere_tilmeldinger WHERE deltager_id = ?', $participant->id);
        $this->db->exec('DELETE FROM deltagere_madtider WHERE deltager_id = ?', $participant->id);
        $this->db->exec('DELETE FROM deltagere_gdstilmeldinger WHERE deltager_id = ?', $participant->id);
        $this->db->exec('DELETE FROM deltagere_indgang WHERE deltager_id = ?', $participant->id);
        $this->db->exec('DELETE FROM deltagere_wear WHERE deltager_id = ?', $participant->id);
    }

    /**
     * sets data in the participant entity from the api json
     *
     * @param Deltagere $participant Participant object
     * @param array     $data        JSON data
     *
     * @access protected
     * @return $this
     */
    protected function setParticipantData(DBObject $participant, array $data)
    {
        $fields = array(
            'fornavn',
            'efternavn',
            'gender',
            'birthdate',
            'alder',
            'email',
            'tlf',
            'mobiltlf',
            'adresse1',
            'adresse2',
            'postnummer',
            'by',
            'land',
            'medbringer_mobil',
            'sprog',
            'forfatter',
            'international',
            'arrangoer_naeste_aar',
            'deltager_note',
            'flere_gdsvagter',
            'supergm',
            'supergds',
            'rig_onkel',
            'arbejdsomraade',
            'hemmelig_onkel',
            'ready_mandag',
            'ready_tirsdag',
            'oprydning_tirsdag',
            'tilmeld_scenarieskrivning',
            'may_contact',
            'desired_activities',
            'game_reallocation_participant',
            'dancing_with_the_clans',
            'sovesal',
            'ungdomsskole',
            'original_price',
            'scenarie',
            'medical_note',
            'interpreter',
            'skills',
            'package_gds',
        );

        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $participant->$field = $data[$field];
            }
        }

        $participant->birthdate = strtotime($participant->birthdate) ? $participant->birthdate : '0000-00-00';

        $bk  = $this->createEntity('BrugerKategorier');
        $sel = $bk->getSelect()->setWhere('navn', '=', $data['brugertype']);
        $bk->findBySelect($sel);

        if ($bk->id) {
            $participant->brugerkategori_id = $bk->id;
        }

        return $this;
    }

    /**
     * returns data from given graph, if possible
     *
     * @param string $name Name of graph to fetch data for
     *
     * @throws Exception
     * @access public
     * @return array
     */
    public function fetchGraphData($name)
    {
        $model = $this->factory('Graph');
        switch ($name) {
        case 'signupsOverTime':
            return $model->getSignupData();

        case 'signupsOverTimeAgeGrouped':
            return $model->getAgeGroupedSignupData();

        case 'accommodationAgeGrouped':
            return $model->getAgeGroupedAccommodationData();

        default:
            throw new FrameworkException('No such graph');
        }

        return $data;
    }

    /**
     * method docblock
     *
     * @param
     *
     * @access public
     * @return array
     */
    public function getActivityStructure()
    {
        $friendly_names = array(
            'navn'                   => 'Titel (Dk)',
            'kan_tilmeldes'          => 'Muligt at tilmelde sig',
            'note'                   => 'Noter',
            'foromtale'              => 'Foromtale (Dk)',
            'varighed_per_afvikling' => 'Varighed (timer)',
            'min_deltagere_per_hold' => 'Minimum deltagere',
            'max_deltagere_per_hold' => 'Maximum deltagere',
            'spilledere_per_hold'    => 'Spilledere per hold',
            'pris'                   => 'Pris',
            'lokale_eksklusiv'       => 'Skal foregå i eget lokale',
            'wp_link'                => 'Wordpress ID',
            'teaser_dk'              => 'Teaser (Dk)',
            'teaser_en'              => 'Teaser (Gb)',
            'title_en'               => 'Teaser (Gb)',
            'description_en'         => 'Foromtale (Gb)',
            'author'                 => 'Forfatter',
            'type'                   => 'Aktivitetstype',
            'tids_eksklusiv'         => 'Udelukker samtidige aktiviteter',
            'sprog'                  => 'Sprog',
            'replayable'             => 'Kan spilles flere gange',
        );

        $struct = $this->convertColumnInfoToHtmlStructure($this->createEntity('Aktiviteter')->getColumnInfo());
        unset($struct['id']);

        foreach ($struct as $field => $type) {
            $struct[$field] = array(
                'type'          => $type,
                'friendly_name' => isset($friendly_names[$field]) ? $friendly_names[$field] : '',
            );
        }

        return $struct;
    }

    /**
     * converts a column info structure to html meaningful terms
     *
     * @param array $structure Column info structure to convert
     *
     * @access protected
     * @return array
     */
    protected function convertColumnInfoToHtmlStructure(array $structure)
    {
        $output = array();
        foreach ($structure as $field => $type) {
            if ($type == 'text') {
                $output[$field] = 'textarea';

            } elseif (strpos($type, 'varchar') !== false) {
                $output[$field] = 'text';

            } elseif (preg_match('/enum\((.*)\)/', $type, $match)) {
                $fields         = array_map(function($x) {return trim($x, "'\"");}, explode(',', $match[1]));
                $output[$field] = 'select:' . implode('|', array_diff($fields, array('system')));

            } elseif (strpos($type, 'int') !== false) {
                $output[$field] = 'number:int';

            } elseif (strpos($type, 'float') !== false) {
                $output[$field] = 'number:float';

            } else {
                $output[$field] = 'text';
            }
        }

        return $output;
    }

    /**
     * returns array of values for entity
     *
     * @param DBObject $entity Entity to format
     *
     * @access public
     * @return array
     */
    public function formatEntityForJson(DBObject $entity)
    {
        $output = array();
        foreach ($entity->getColumns() as $column) {
            $output[$column] = $entity->$column;
        }

        return $output;
    }

    /**
     * attempts to find an activity by a field and a value
     *
     * @param string $field Field to search by
     * @param mixed  $value Value to search with
     *
     * @throws Exception
     * @access public
     * @return null|DBObject
     */
    public function findActivityByField($field, $value)
    {
        $activity = $this->createEntity('Aktiviteter');
        if (!in_array($field, $activity->getColumns())) {
            throw new FrameworkException('No such field in entity');
        }

        $select = $activity->getSelect()
            ->setWhere($field, '=', $value);

        $result = $this->createEntity('Aktiviteter')->findBySelect($select);
        return $result ? $result : null;
    }

    /**
     * creates an activity based on POST data
     *
     * @param array $data Data to use for activity creation
     *
     * @throws Exception
     * @access public
     * @return void
     */
    public function createActivity(array $data)
    {
        $activity = $this->createEntity('Aktiviteter');
        $this->fillActivity($activity, $data);

        try {
            $activity->insert();

        } catch (Exception $e) {
            error_log($e->getMessage());
            error_log(print_r($data, true));

            throw $e;
        }
    }

    /**
     * method docblock
     *
     * @param
     *
     * @access protected
     * @return void
     */
    protected function fillActivity(DBObject $activity, array $data)
    {
        foreach ($activity->getColumns() as $column) {
            if ($column == 'id' || $column == 'title_en') {
                continue;
            }

            $activity->$column = isset($data[$column]) ? str_replace(chr(0xc2) . chr(0xa0), ' ', $data[$column]) : '';
        }

        $activity->updated = date('Y-m-d H:i:s');

        $activity->convertProblematicToDefault();
    }

    /**
     * updates an activity based on POST data
     *
     * @param array $data Data to use for activity creation
     * @param int   $id   ID of activity to update
     *
     * @throws Exception
     * @access public
     * @return void
     */
    public function updateActivity(array $data, $id)
    {
        $activity = $this->createEntity('Aktiviteter')->findById($id);
        if (!$activity->isLoaded()) {
            throw new FrameworkException('Could not find activity by id');
        }

        $this->fillActivity($activity, $data);

        $activity->update();
    }

    /**
     * returns a participant if it exists
     *
     * @param int $id Id of participant to find
     *
     * @access public
     * @return null|Deltagere
     */
    public function findParticipant($id)
    {
        return $this->createEntity('Deltagere')->findById($id);
    }

    /**
     * returns a participant if it exists
     *
     * @param string $email Email of participant to find
     *
     * @access public
     * @return array
     */
    public function findParticipantsByEmail($email)
    {
        $select = $this->createEntity('Deltagere')
            ->getSelect();

        $select->setWhere('email', '=', $email);

        $result = $this->createEntity('Deltagere')->findBySelectMany($select);

        return $result;
    }

    /**
     * returns a participant if it exists
     *
     * @param string $email Email of participant to find
     * @param string $pass  Password of participant to find
     *
     * @access public
     * @return null|Deltagere
     */
    public function getParticipantByEmailAndPassword($email, $pass)
    {
        $select = $this->createEntity('Deltagere')
            ->getSelect();

        $select->setWhere('email', '=', $email);
        $select->setWhere('password', '=', $pass);

        $result = $this->createEntity('Deltagere')->findBySelectMany($select);

        if (count($result) !== 1) {
            return null;
        }

        return array_pop($result);
    }

    /**
     * returns JSON object of participants schedule for fastaval
     *
     * @param DBObject $participant Participant to get schedule for
     *
     * @access public
     * @return array
     */
    public function getParticipantBaseData(DBObject $participant)
    {
        $return = array(
            'fornavn' => $participant->fornavn,
            'efternavn' => $participant->efternavn,
            'gender' => $participant->gender,
            'package_gds' => $participant->package_gds,
            'birthdate' => $participant->birthdate,
            'alder' => $participant->alder,
            'email' => $participant->email,
            'tlf' => $participant->tlf,
            'mobiltlf' => $participant->mobiltlf,
            'adresse1' => $participant->adresse1,
            'adresse2' => $participant->adresse2,
            'postnummer' => $participant->postnummer,
            'by' => $participant->by,
            'land' => $participant->land,
            'medbringer_mobil' => $participant->medbringer_mobil,
            'sprog' => $participant->sprog,
            'forfatter' => $participant->forfatter,
            'international' => $participant->international,
            'arrangoer_naeste_aar' => $participant->arrangoer_naeste_aar,
            'deltager_note' => $participant->deltager_note,
            'flere_gdsvagter' => $participant->flere_gdsvagter,
            'supergm' => $participant->supergm,
            'supergds' => $participant->supergds,
            'rig_onkel' => $participant->rig_onkel,
            'arbejdsomraade' => $participant->arbejdsomraade,
            'hemmelig_onkel' => $participant->hemmelig_onkel,
            'ready_mandag' => $participant->ready_mandag,
            'ready_tirsdag' => $participant->ready_tirsdag,
            'oprydning_tirsdag' => $participant->oprydning_tirsdag,
            'tilmeld_scenarieskrivning' => $participant->tilmeld_scenarieskrivning,
            'may_contact' => $participant->may_contact,
            'desired_activities' => $participant->desired_activities,
            'game_reallocation_participant' => $participant->game_reallocation_participant,
            'sovesal' => $participant->sovesal,
            'ungdomsskole' => $participant->ungdomsskole,
            'original_price' => $participant->original_price,
            'scenarie' => $participant->scenarie,
            'medical_note' => $participant->medical_note,
            'interpreter' => $participant->interpreter,
            'skills' => $participant->skills,
            'brugerkategori' => $participant->getBrugerKategori()->navn,
            'payment_url' => $this->url('participant_payment', array('hash' => $this->getParticipantPaymentHash($participant))),
            'id' => $participant->id,
            'session' => '',
        );

        if (is_file(__DIR__ . '/../signup-data/session-' . $participant->id)) {
            $return['session'] = file_get_contents(__DIR__ . '/../signup-data/session-' . $participant->id);
        }

        return $return;
    }

    /**
     * returns the payment hash for a participant
     *
     * @param Deltagere $participant Participant to get hash for
     *
     * @throws FrameworkException
     * @access public
     * @return string
     */
    public function getParticipantPaymentHash(DBObject $participant)
    {
        $query = '
SELECT hash FROM participantpaymenthashes WHERE participant_id = ?
';

        $result = $this->db->query($query, [$participant->id]);

        if (empty($result)) {
            throw new FrameworkException('No payment hash available for participant');
        }

        return $result[0]['hash'];
    }

    /**
     * returns JSON object of participants schedule for fastaval
     *
     * @param DBObject $participant Participant to get schedule for
     *
     * @access public
     * @return array
     */
    public function getParticipantSchedule(DBObject $participant, $version = 1)
    {
        $sleep = $mattress = 0;

        $otto_party = array();

        foreach ($participant->getIndgang() as $entrance) {
            if ($entrance->isSleepTicket()) {
                $sleep = 1;
            }

            if ($entrance->isParty() || $entrance->isPartyBubbles()) {
                $otto_party[] = array(
                    'id'       => $entrance->id,
                    'title_en' => $entrance->getDescription(true),
                    'title_da' => $entrance->getDescription(),
                    'amount'   => 1,
                );
            }

            if ($entrance->isMattress()) {
                $mattress = 1;
            }

        }

        $sleep = $participant->sovesal == 'ja' ? 2 : $sleep;

        $category = $participant->getBrugerKategori();

        $participant_model = $this->factory('Participant');
        $sleep_data        = $participant_model->getSleepDataForParticipant($participant);

        if (intval($version) >= 3) {
            $access = 0;

            switch ($sleep) {
            case 1:
                $name    = 'Store sovesal';
                $area_id = 68;
                $access  = 1;
                break;

            case 2:
                $name    = 'Arrangørsovesal';
                $area_id = 66;
                $access  = 1;
                break;

            default:
                $name    = '';
                $area_id = 0;
            }

            if ($sleep_data) {
                $room_data = reset($sleep_data);
                $access    = 1;
                $name      = $room_data['room']->beskrivelse;
                $area_id   = $room_data['room']->id;

            }

            $sleep = array(
                'id'        => 1,
                'access'    => $access,
                'mattress'  => $mattress,
                'area_name' => $name,
                'area_id'   => 'R' . $area_id,
            );

        }

        $return = array(
            'id'         => $participant->id,
            'name'       => trim($participant->fornavn . ' ' . $participant->efternavn),
            'checked_in' => strtotime($participant->checkin_time) > 1 ? 1 : 0,
            'messages'   => $participant->beskeder,
            'sleep'      => $sleep,
            'category'   => $category->navn,
            'food' => array(
            ),
            'wear' => array(
            ),
            'scheduling' => array(),
        );

        if ($version >= 3) {
            $return['barcode'] = $participant->getEan8Number();
        }

        if ($version >= 2) {
            $return['otto_party'] = $otto_party;
        }

        foreach ($participant->getWear() as $wearorder) {
            $wear = $wearorder->getWear();

            $item = array(
                'amount'   => $wearorder->antal,
                'size'     => $wearorder->size,
                'title_da' => $wear->navn,
                'title_en' => $wear->title_en,
                'wear_id'  => $wear->id,
            );

            if ($version >= 3) {
                $item['received'] = $wearorder->received === 't' ? 1 : 0;
            }

            $return['wear'][] = $item;
        }

        $foodtime_links = $participant->getFoodOrderLinks();
        $ids            = array_map(function ($x) {
            return $x->madtid_id;
        }, $foodtime_links);

        $combined = array_combine($ids, $foodtime_links);

        foreach ($participant->getMadTider() as $foodtime) {
            $food = $foodtime->getMad();

            $start = strtotime($foodtime->dato);
            $end   = strtotime($foodtime->dato) + 7200;
            if (isset($combined[$foodtime->id]) && $combined[$foodtime->id]->time_type) {
                $start = $start + ($combined[$foodtime->id]->time_type - 1) * 1800;
                $end   = $start + 1800;
            }

            $return['food'][] = array(
                'time'     => $start,
                'time_end' => $end,
                'title_da' => $food->kategori,
                'title_en' => $food->title_en,
                'food_id'  => $food->id,
                'time_id'  => $foodtime->id,
                'text_da'  => $foodtime->description_da,
                'text_en'  => $foodtime->description_en,
            );
        }

        foreach ($participant->getPladser() as $play) {
            $schedule  = $play->getAfvikling();
            $activity  = $schedule->getAktivitet();
            $room_name = $schedule->getRoom();

            if ($activity->hidden === 'ja' || ($version == 1 && $activity->type == 'system')) {
                continue;
            }

            $type = $play->type === 'spilleder' && $version >= 2 ? 'spilleder' : $activity->type;

            $item = array(
                'type'          => 'activity',
                'activity_type' => $type,
                'id'   => $activity->id,
                'schedule_id' => $schedule->id,
                'title_da'  => $activity->navn,
                'title_en' => $activity->title_en,
                'start' => strtotime($schedule->start),
                'stop' => strtotime($schedule->slut),
                'room_da'  => $room_name,
                'room_en'  => $room_name,
            );

            if ($version >= 3) {
                $room = $play->type === 'spilleder' ? $play->getLokale() : false;

                $item['play_room_id'] = $room ? 'R' . $room->id : '';
                $item['play_room_name'] = $room ? $room->beskrivelse : '';

                $room = $schedule->getRoomObject();

                $item['meet_room_id'] = $room ? 'R' . $room->id : '';
                $item['meet_room_name'] = $room ? $room->beskrivelse : '';

            }

            $return['scheduling'][] = $item;
        }

        foreach ($participant->getGDSVagter() as $shift) {
            $diy = $shift->getGDS();

            $return['scheduling'][] = array(
                'type'          => 'gds',
                'activity_type' => 'gds',
                'id'            => $diy->id,
                'schedule_id'   => $shift->id,
                'title_da'      => $diy->navn,
                'title_en'      => $diy->title_en,
                'room_da'       => $diy->moedested,
                'room_en'       => $diy->moedested_en,
                'start'         => strtotime($shift->start),
                'stop'          => strtotime($shift->slut),
            );

        }

        usort($return['scheduling'], function($a, $b) {
            return $a['start']['timestamp'] - $b['start']['timestamp'];
        });

        return $return;
    }

    /**
     * registers a GCM id for a participant
     *
     * @param DBObject $participant Participant to register id for
     * @param array    $json        Data from request
     *
     * @throws FrameworkException
     * @access public
     * @return void
     */
    public function registerApp(DBObject $participant, $json)
    {
        if (empty($json['gcm_id']) && empty($json['apple_id'])) {
            throw new FrameworkException('Lacking device id in post');
        }

        if (!empty($json['gcm_id'])) {
            return $this->handleGcmRegistration($participant, $json);
        }

        if (!empty($json['apple_id'])) {
            return $this->handleAppleRegistration($participant, $json);
        }
    }

    /**
     * handles apple device registration
     *
     * @param DBObject $participant Participant to register id for
     * @param array    $json        Data from request
     *
     * @access protected
     * @return self
     */
    protected function handleAppleRegistration(DBObject $participant, $json)
    {
        if ($participant->apple_id === $json['apple_id']) {
            return $this;
        }

        $participant->apple_id = $json['apple_id'];
        $participant->update();

        if ($participant->speaksDanish()) {
            $message = 'Du vil fremover modtage notifikationer via Fastaval appen';
            $title   = 'Fastaval notifikation';

        } else {
            $message = 'You will from now on receive notification via the Fastaval App';
            $title   = 'Fastaval app notification';
        }

        //list($code, $data, $return) = $participant->sendAppleMessage($this->config->get('gcm.server_api_key'), $message, $title);

        //$this->log('Sent apple notification to participant #' . $participant->id . '. Result: ' . $return, 'App', null);

        return $this;
    }

    /**
     * handles GCM/google registration
     *
     * @param DBObject $participant Participant to register id for
     * @param array    $json        Data from request
     *
     * @access protected
     * @return self
     */
    protected function handleGcmRegistration(DBObject $participant, $json)
    {
        if ($participant->gcm_id === $json['gcm_id']) {
            return $this;
        }

        $participant->gcm_id = $json['gcm_id'];
        $participant->update();

        if ($participant->speaksDanish()) {
            $message = 'Du vil fremover modtage notifikationer via Fastavappen';
            $title   = 'Fastavappen notifikation';

        } else {
            $message = 'You will from now on receive notification via the Fastaval App';
            $title   = 'Fastaval app notification';
        }

        $result = $participant->sendFirebaseMessage($this->config->get('firebase.server_api_key'), $message, $title);
        $this->log('Sent android notification to participant #' . $participant->id . '. Result: ' . $result, 'App', null);

        return $this;
    }

    /**
     * unregisters a GCM id for a participant
     *
     * @param DBObject $participant Participant to register id for
     * @param array    $json        Data from request
     *
     * @throws FrameworkException
     * @access public
     * @return void
     */
    public function unregisterApp(DBObject $participant)
    {
        $participant->gcm_id = $participant->apple_id = '';
        $participant->update();
    }

    public function getScheduleInfo(DBObject $activity)
    {
        $output = array();

        foreach ($activity->getAfviklinger() as $schedule) {
            $output[] = array(
                'start'   => strtotime($schedule->start),
                'end'     => strtotime($schedule->slut),
                'room_en' => $schedule->getRoom(),
                'room_da' => $schedule->getLokale(),
            );

            foreach ($schedule->getMultiBlok() as $subschedule) {
                $output[] = array(
                    'start'   => strtotime($subschedule->start),
                    'end'     => strtotime($subschedule->slut),
                    'room_en' => $subschedule->getRoom(),
                    'room_da' => $subschedule->getLokale(),
                );
            }
        }

        return $output;
    }
}
