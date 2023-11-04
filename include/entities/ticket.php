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
class Ticket extends DBObject
{
  /**
   * Name of database table
   *
   * @var string
   */
  protected $tablename = 'tickets';

  /**
   * Message structure for description
   *
   * @var string
   */
  protected $description = [];

  public function insert() {
    $status = parent::insert();

    if($status) {
      $query =
      "INSERT INTO tickets_messages (user, ticket, posted, message) VALUES (NULL, ?, ?, ?)";

      $values = [
        $this->id,
        time(),
        $this->description['message'],
      ];

      $this->db->exec($query, $values);
    }

    return $status;
  }


  public function setDescription($text) {
    $this->description['message'] = $text;
    $this->description['last_edit'] = time();

    if (!$this->isLoaded()) return;

    $query =
    "UPDATE tickets_messages SET message = ?, last_edit = ? WHERE user IS NULL AND ticket = ?";

    $values = [
      $this->description['message'],
      $this->description['last_edit'],
      $this->id,
    ];

    $this->db->exec($query, $values);
  }

  public function getDescription() {
    if (!empty($this->description)) return $this->description;

    if (!$this->isLoaded()) return [];

    $query = 
    "SELECT * FROM tickets_messages WHERE user IS NULL AND ticket = $this->id";

    $result = $this->db->query($query);
    $this->description = count($result) ? $result[0] : [];
    return $this->description;
  }

  public function getSubscribers() {
    if (!$this->isLoaded()) return [];

    $query = "SELECT user FROM tickets_subscriptions WHERE ticket = $this->id";
    $result = $this->db->query($query);
    return array_column($result, 'user');
  }

  public function isSubscribed($user_id) {
    return in_array($user_id, $this->getSubscribers());
  }
}