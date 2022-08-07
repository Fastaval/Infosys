<?php
/**
 * Copyright (C) 2015 Peter Lind
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
 * @package   Framework
 * @author    Peter Lind <peter.e.lind@gmail.com>
 * @copyright 2015 Peter Lind
 * @license   http://www.gnu.org/licenses/gpl.html GPL 3
 * @link      http://www.github.com/Fake51/Infosys
 */

/**
 * helper for handling datauris
 *
 * @category Infosys
 * @package  Framework
 * @author   Peter Lind <peter.e.lind@gmail.com>
 * @license  http://www.gnu.org/licenses/gpl.html GPL 3
 * @link     http://www.github.com/Fake51/Infosys
 */
class DataUri
{
    /**
     * mime type of content
     *
     * @var string
     */
    private $type = 'text/plain';

    /**
     * true if raw content is base64 encoded
     *
     * @var boolean
     */
    private $is_encoded = false;

    /**
     * raw content of data uri
     *
     * @var binary
     */
    private $raw_content;

    /**
     * public constructor
     *
     * @param string $datauri Uri to parse
     *
     * @throws Exception
     * @access public
     */
    public function __construct($datauri)
    {
        if (!preg_match('#^data(:(image/png|image/jpeg|image/gif))?(;base64)?,#i', $datauri, $matches)) {
            throw new FrameworkException('Construction value for DataUri must be a proper DataUri');
        }

        if (!empty($matches[2])) {
            $this->type = mb_strtolower($matches[2]);
        }

        if (!empty($matches[3])) {
            $this->is_encoded = true;
        }

        $this->raw_content = substr($datauri, strlen($matches[0]));
    }

    /**
     * returns the mime type of the content
     *
     * @access public
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * returns true if the content is encoded
     *
     * @access public
     * @return bool
     */
    public function isEncoded()
    {
        return $this->is_encoded;
    }

    /**
     * returns raw content of the data uri
     *
     * @access public
     * @return string
     */
    public function getRawContent()
    {
        return $this->raw_content;
    }

    /**
     * returns content of the data uri
     *
     * @access public
     * @return binary
     */
    public function getContent()
    {
        return $this->isEncoded() ? base64_decode($this->getRawContent()) : $this->getRawContent();
    }
}
