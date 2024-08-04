<?php
  /**
   * Copyright (C) 2024 Mikkel Westh
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
   * PHP version 8
   *
   * @package    MVC
   * @subpackage Entities
   * @author     Mikkel Westh <westh_mikkel$hotmail.com>
   * @copyright  2024 Mikkel Westh
   * @license    http://www.gnu.org/licenses/gpl.html GPL 3
   * @link       https://github.com/Fastaval/Infosys
   */

class SpecialSpot extends Pladser {

  // The participant who's schedule the spot is for
  protected $participant;

  // The run that should apear on the participant's schedule
  protected $run;

  // What type of spot is it
  protected $type;

  public function __construct(EntityFactory $entity_factory, int|Afviklinger $run, int|Deltagere $participant, String $type) {
    parent::__construct($entity_factory);

    $this->type = $type;

    if (is_a($run, "Afviklinger")) {
      $this->run = $run;
    } else {
      $this->run = $this->createEntity('Afviklinger')->findById($run);
    }

    if (is_a($participant, "Deltagere")) {
      $this->participant = $participant;
    } else {
      $this->participant = $this->createEntity('Deltagere')->findById($participant);
    }
  }

  public function __get($var) {
    switch ($var) {
      case 'deltager_id':
        return $this->participant->id;
      
      case 'type':
        return $this->type;

      case 'holdnummer':
        return 0;

      case 'lokale_id':
        return $this->run->lokale_id;
    }
  }

  #[\Override]
  public function getHold() {
    return false;
  }

  #[\Override]
  public function getAfvikling() {
    return $this->run;
  }

  #[\Override]
  public function getLokale() {
    return $this->run->getRoomObject();
  }

  #[\Override]
  public function getAktivitet() {
    return $this->createEntity('Aktiviteter')->findById($this->run->aktivitet_id);
  }

  #[\Override]
  public function getDeltager() {
    return $this->participant;
  }

  #[\Override]
  public function delete() {
    return true;
  }  
}