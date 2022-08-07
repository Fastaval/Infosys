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
     * represents a log entry
     *
     * @package    MVC
     * @subpackage Entities
     * @author     Peter Lind <peter.e.lind@gmail.com>
     */
class LogItem extends DBObject
{
    /**
     * name of the matching DB table
     *
     * @var string
     */
    protected $tablename = 'log';


    /**
     * does a wildcard search through the logs, searching by category, user and message
     *
     * @param object $search_object - use search_term, category or user
     *
     * @throws EntityException
     * @access public
     * @return array
     */
    public function searchLogs($search_object)
    {
        if (!is_object($search_object))
        {
            throw new EntityException('Method must be called using object as param');
        }
        $select = $this->getSelect();
        $terms = $category = $user = null;
        if (isset($search_object->search_term))
        {
            $terms = explode(' ', $search_object->search_term);
            foreach ($terms as &$term)
            {
                $term = "message LIKE {$this->getDB()->sanitize('%' . $term . '%')}";
            }
            $terms = '(' . implode(' OR ', $terms) . ')';
        }
        if ($terms)
        {
            $select->setRawWhere($terms, 'and');
        }
        if (isset($search_object->category))
        {
            $select->setWhere('type', '=', $search_object->category);
        }
        if (!empty($search_object->user))
        {
            $select->setWhere('user_id', '=', $search_object->user->id);
        }
        $select->setOrder('id', 'desc');
        $select->setOffset(!empty($search_object->page) ? intval($search_object->page) * 20 : 0);
        $select->setLimit(20);
        return $this->findBySelectMany($select);
    }

    /**
     * returns all available log types
     *
     * @access public
     * @return array
     */
    public function getTypes()
    {
        $select = $this->getSelect();
        $select->setField('DISTINCT(type) AS type', false)->
                 setOrder('type', 'asc');
        if ($results = $this->getDB()->query($select))
        {
            $return = array();
            foreach ($results as $result)
            {
                $return[] = $result['type'];
            }
            return $return;
        }
        return array();
    }
}
