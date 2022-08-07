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
class PaymentFritidDkLink implements PaymentLink
{
    /**
     * api module
     *
     * @var PaymentFritidDkApi
     */
    private $api_module;

    /**
     * public constructor
     *
     * @param string $api_key     Key to use as identifier for the API
     * @param ?      $http_helper Helper for fetching external data
     *
     * @throws FrameworkException
     * @access public
     */
    public function __construct(PaymentFritidDkApi $api_module)
    {
        $this->api_module = $api_module;
    }

    /**
     * generates a forwarding URL to send the participant to
     *
     * @param Deltagere $participant  Participant to generate payment url for
     * @param int       $price        Price to pay in Ears
     * @param array     $conneection_links Links into the system, for success, callback and cancel
     * @param string    $payment_text Optional text for button/links to initiate payment
     *
     * @throws Exception
     * @access public
     * @return string
     */
    public function generateOutput(Deltagere $participant, $price, array $conneection_links, $payment_text = 'Betal nu')
    {
        $data = $this->api_module->createTicket($participant, $price, $conneection_links);

        return '<a href="' . e($data['data']['url']) . '" class="paymentFritidDk">' . e($payment_text) . '</a>';
    }

    /**
     * parses request data from payment callback
     *
     * @param \Request $request Request data
     *
     * @access public
     * @return array|false
     */
    public function parseCallbackRequest(\Request $request)
    {
        return $this->api_module->parseCallbackRequest($request);
    }
}
