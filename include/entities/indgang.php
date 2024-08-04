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
     * handles the indgang table
     *
     * @package MVC
     * @subpackage Entities
     */
class Indgang extends DBObject
{

    /**
     * Name of database table
     *
     * @var string
     */
    protected $tablename = 'indgang';


    /**
     * wrapper for isSleepTicket
     *
     * @access public
     * @return bool
     */
    public function isOvernatning()
    {
        return $this->isSleepTicket();
    }

    /**
     * checks whether the current entry object represents a sleep-option
     *
     * @access public
     * @return bool
     */
    public function isSleepTicket()
    {
        if (!$this->isLoaded()) {
            return false;
        }

        return stripos($this->type, 'overnatning') !== false;
    }

    /**
     * checks whether the current entry object represents an entrance option
     *
     * @access public
     * @return bool
     */
    public function isEntrance()
    {
        if (!$this->isLoaded()) {
            return false;
        }

        return (stripos($this->type, 'indgang') !== false) || (stripos($this->type, 'Dagsbillet') !== false);
    }

    public function isParty()
    {
        if (!$this->isLoaded()) {
            return false;
        }
        return $this->type == 'Ottofest';
    }

    /**
     * returns true if the type is a fee
     *
     * @access public
     * @return bool
     */
    public function isFee()
    {
        return stripos($this->type, 'gebyr') !== false;
    }

    public function isPartyBubbles()
    {
        if (!$this->isLoaded()) {
            return false;
        }

        return $this->type == 'Ottofest - Champagne';
    }

    public function isMattress()
    {
        if (!$this->isLoaded()) {
            return false;
        }

        return mb_stripos($this->type, 'madras') !== false;
    }

    public function isDigital() {
        if (!$this->isLoaded()) {
            return false;
        }

        return mb_stripos($this->type, 'digital') !== false;
    }

    /**
     * returns a shorthand string for the object
     *
     * @access public
     * @return string
     */
    public function getShortType()
    {
        if (!$this->isLoaded()) {
            return '';
        }

        switch ($this->type) {
            case "Partout":
            case "Overnatningsgebyr":
            case "Campingvogn":
                return ucfirst($this->type);
                break;
            default:
                $parts = explode(' ',$this->type);
                return count($parts) > 1 ? $parts[1] : $parts[0];
        }
    }

    /**
     * searches for entry objects by type
     *
     * @param string $type - type of entry
     * @access public
     * @return bool|object
     */
    public function findByType($type)
    {
        if ($this->isLoaded())
        {
            return false;
        }
        $select = $this->getSelect();
        $select->setWhere('type', '=', $type);
        return $this->findBySelect($select);
    }

    public function isPartout()
    {
        if (!$this->isLoaded())
        {
            return false;
        }
        return stripos($this->type, 'partout') !== false;
    }

    /**
     * checks whether the ticket spans all convention
     * (whether sleep or entrance)
     *
     * @access public
     * @return bool
     */
    public function spansAllConvention() {
        if (!$this->isLoaded()) {
            return false;
        }

        return stripos($this->type, 'alle') !== false || stripos($this->type, 'partout') !== false || stripos($this->type, 'leje') !== false;
    }

    public function isDayTicket()
    {
        if (!$this->isLoaded()) {
            return false;
        }

        return ($this->type == "Indgang - Enkelt") || (stripos($this->type, 'Dagsbillet') !== false);
    }

    public function isAleaMembership()
    {
        if (!$this->isLoaded()) {
            return false;
        }

        return stripos($this->type, "medlemskab") !== false;
    }

    public function isRich()
    {
        if (!$this->isLoaded()) {
            return false;
        }

        return stripos($this->type, "onkel") !== false;
    }

    public function isSecret()
    {
        if (!$this->isLoaded()) {
            return false;
        }

        return stripos($this->type, "Hemmelig") !== false;
    }

    public function getDescription($english = false)
    {
        if (!$this->isLoaded()) {
            return '';
        }

        $date_part = $this->spansAllConvention() ? 'Partout' : date('D', strtotime($this->start));
        if ($this->isEntrance()) {
            $description = $english ? 'Entrance' : "Indgang";
            if ($this->isDigital()) $description .= ' (Digital)';
        } elseif ($this->isSleepticket()) {
            $description = $english ? 'Accommodation' : "Overnatning";

        } elseif ($this->isParty()) {
            return $english ? 'Party' : "Fest";

        } elseif ($this->isPartyBubbles()) {
            return $english ? 'Champagne' : 'Champagne';

        } elseif ($this->type == 'Alea medlemskab') {
            return $english ? 'Alea membership' : $this->type;

        } elseif ($this->isMattress()) {
            return $english ? 'Mattress' : $this->type;

        } else {
            return $this->type;
        }

        if (preg_match("/Alea/", $this->type)) $description .= " Alea";
        if (preg_match("/Arrangør/", $this->type)) $description .= " Arrangør";
        if (preg_match("/Ung/", $this->type)) $description .= " Ung";
        if (preg_match("/Barn/", $this->type)) $description .= " Barn";

        return danishDayNames("{$description} {$date_part}");
    }

    public function getShortDescription($english = false)
    {
        if (!$this->isLoaded()) {
            return '';
        }

        $desc = $this->spansAllConvention() ? 'Partout' : date('D', strtotime($this->start));
        $desc = $english ? $desc : danishDayNames($desc);
        if ($this->isDigital()) $desc .= ' (Digital)';
        return $desc;
    }

    public function getProperType() {
        if (!$this->id) {
            return '';
        }

        if ($this->spansAllConvention()) {
            if ($this->isSleepTicket()) {
                return "Overnatning, partout";
            } else {
                return "Indgang, partout";
            }
        } elseif ($this->isDayTicket()) {
            return "Indgang, enkelt dag";
        } elseif ($this->isSleepTicket()) {
            return "Overnatning, enkelt dag";
        } elseif ($this->isParty()) {
            return "Fest";
        } else {
            return $this->type;
        }
    }

    public function getParticipantCount() {
        if (!$this->isLoaded()) {
            return 0;
        }
        $query = "SELECT COUNT(*) FROM deltagere_indgang WHERE indgang_id = {$this->id}";
        $res = $this->getDB()->query($query);
        return $res[0][0];
    }
}
