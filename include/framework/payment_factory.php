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
 * PHP version 5.3+
 *
 * @category  Infosys
 * @package   Framework
 * @author    Peter Lind <peter.e.lind@gmail.com>
 * @copyright 2015 Peter Lind
 * @license   http://www.gnu.org/licenses/gpl.html GPL 3
 * @link      http://www.github.com/Fake51/Infosys
 */

/**
 * fritid.dk payment module
 *
 * @category Infosys
 * @package  Framework
 * @author   Peter Lind <peter.e.lind@gmail.com>
 * @license  http://www.gnu.org/licenses/gpl.html GPL 3
 * @link     http://www.github.com/Fake51/Infosys
 */
class PaymentFactory
{
    /**
     * public constructor
     *
     * @param Config $config Configuration settings provider
     *
     * @access public
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    } 

    /**
     * payment module builder
     *
     * @throws FrameworkException
     * @access public
     * @return PaymentConnector
     */
    public function build()
    {
        switch ($this->config->get('payment.type')) {
        case 'FritidDkLink':
            return new PaymentFritidDkLink(new PaymentFritidDkApi($this->config->get('payment.apikey'), new \GuzzleHttp\Client()));

        case 'FritidDkUrl':
            return new PaymentFritidDkUrl(new PaymentFritidDkApi($this->config->get('payment.apikey'), new \GuzzleHttp\Client()));

        default:
            throw new FrameworkException('Unrecognized payment type - cannot build it. ' . $this->config->get('payment.type'));
        }
    }
}
