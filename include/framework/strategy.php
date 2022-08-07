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
     * @package    Framework
     * @author     Peter Lind <peter.e.lind@gmail.com>
     * @copyright  2009 Peter Lind
     * @license    http://www.gnu.org/licenses/gpl.html GPL 3
     * @link       http://www.github.com/Fake51/Infosys
     */

    /**
     * has various strategies for comparison and other things
     * used by several different objects
     *
     * @package Framework
     */
class Strategy
{

    /**
     * returns highest number of seconds that can be achieved from arranging the activities
     *
     * @param array $afviklinger - array of schedules for activities
     * @access public
     * @return int
     */
    public function getMaxTimeForSchedules($afviklinger)
    {
        $unique = array($afviklinger[0]);                                                                                                                    
        for ($i = 1; $i < count($afviklinger); $i++)
        {   
            if (strtotime($afviklinger[$i]->start) != strtotime($afviklinger[$i-1]->start) || strtotime($afviklinger[$i]->slut) != strtotime($afviklinger[$i-1]->slut))
            {   
                $unique[] = $afviklinger[$i];
            }   
        }   
        $day_array = array();
        foreach ($unique as $afvikling)
        {   
            $day_array[date('Y-m-d',(strtotime($afvikling->start) - 4 * 3600))][] = $afvikling;
        }   
        unset($afviklinger, $unique);

        $answer = 0;
        foreach ($day_array as $day => $afviklinger)
        {   
            $day_end = 0;
            foreach ($afviklinger as $afv)
            {   
                $day_end = ((strtotime($afv->slut) > $day_end) ? strtotime($afv->slut) : $day_end);
            }   
            $solve = 0;
            for ($i = 0; $i < count($afviklinger); $i++)
            {   
                $solve = ((($result = $this->findMaxWishedTime($afviklinger[$i], array_slice($afviklinger, $i+1))) && $result > $solve) ? $result : $solve);
                if (empty($afviklinger[$i+1]) || $solve > ($day_end - strtotime($afviklinger[$i+1]->start)))
                {   
                    break;
                }       
            }       
            $answer += $solve;
        } 
        return $answer;
    }

    /**
     * recursive function that finds maximum timespan given a number of activities
     *
     * @param array $afviklinger - array of Afviklinger and AfviklingerMultiblok
     * @param int $day_end - timestamp of the end of the day
     * @access private
     * @return int
     */
    private function findMaxWishedTime($afvikling, $afviklinger)
    {
        $afv_length = strtotime($afvikling->slut) - strtotime($afvikling->start);
        $sublength = 0;
        for ($i = 0; $i < count($afviklinger); $i++)
        {
            if (strtotime($afviklinger[$i]->start) < strtotime($afvikling->slut))
            {
                continue;
            }
            $sublength = ((($result = $this->findMaxWishedTime($afviklinger[$i], array_slice($afviklinger, $i+1))) && $result > $sublength) ? $result : $sublength);
        }
        return $afv_length + $sublength;
    }


    /**
     * sorts an array of schedules
     *
     * @param array $afviklinger - array of Afviklinger entities
     * @access public
     * @return array
     */
    public function sortSchedules($afviklinger)
    {
        if (!is_array($afviklinger))
        {
            return array();
        }
        usort($afviklinger, array($this,'cmpAfvTider'));
        return $afviklinger;
    }

    /**
     * callback function for usort in sortSchedules
     *
     * @param object $a - Afviklinger|AfviklingerMultiblok entity
     * @param object $b - Afviklinger|AfviklingerMultiblok entity
     * @access public
     * @return int
     */
    public function cmpAfvTider($a,$b)
    {
        return ((strtotime($a->start) < strtotime($b->start)) ? -1 : 1);
    }
}
