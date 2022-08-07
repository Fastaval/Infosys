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
 * PHP version 5.3+
 *
 * @category  Infosys
 * @package   Framework
 * @author    Peter Lind <peter.e.lind@gmail.com>
 * @copyright 2009-2012 Peter Lind
 * @license   http://www.gnu.org/licenses/gpl.html GPL 3
 * @link      http://www.github.com/Fake51/Infosys
 */

/**
 * deals with config values
 *
 * @category  Infosys
 * @package   Framework
 * @author    Peter Lind <peter.e.lind@gmail.com>
 * @copyright 2009-2012 Peter Lind
 * @license   http://www.gnu.org/licenses/gpl.html GPL 3
 * @link      http://www.github.com/Fake51/Infosys
 */
class RequirementBlob
{
    /**
     * set requirements
     *
     * @var array
     */
    private $requirements = [];

    /**
     * returns true if all the requirement parts are fulfilled
     *
     * @access public
     * @return bool
     */
    public function isFulfilledBy(FulfilmentBlob $blob)
    {
        $total     = count($this->requirements);
        $fulfilled = 0;

        foreach ($this->requirements as $requirement) {
            if ($requirement->isFulfilledBy($blob)) {
                $fulfilled++;
            }
        }

        return $total === $fulfilled;
    }

    /**
     * adds a requirement to the blob, for later checking
     *
     * @param Requirement $requirement Requirement to add
     *
     * @access public
     * @return $this
     */
    public function addRequirement(Requirement $requirement)
    {
        $this->requirements[] = $requirement;

        return $this;
    }
}
