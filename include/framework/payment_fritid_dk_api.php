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
class PaymentFritidDkApi
{
    const APIURL = 'https://api.fritid.dk/portal/post_unique_product';

    /**
     * http helper
     *
     * @var Net_Http_Client
     */
    private $http_helper;

    /**
     * public constructor
     *
     * @param string $api_key     Key to use as identifier for the API
     * @param ?      $http_helper Helper for fetching external data
     *
     * @throws FrameworkException
     * @access public
     */
    public function __construct($api_key, \GuzzleHttp\ClientInterface $http_client)
    {
        $this->api_key     = $api_key;
        $this->http_helper = $http_client;
    }

    /**
     * generates a forwarding URL to send the participant to
     *
     * @param Deltagere $participant Participant to generate payment url for
     * @param int       $price       Price to pay in Ears
     *
     * @throws Exception
     * @access public
     * @return string
     */
    public function createTicket(Deltagere $participant, $price, array $connection_links)
    {
        if (empty($connection_links['success_url']) || empty($connection_links['success_url']) || empty($connection_links['success_url'])) {
            throw new FrameworkException('Setup data lacks connection links: success, cancel, callback');
        }

        $data = [
            'fritid_key'   => $this->api_key,
            'price'        => intval($price),
            'email'        => $participant->email,
        ];

        $response = $this->http_helper->request('POST', self::APIURL, ['json' => array_merge($data, $connection_links), 'verify' => false]);

        if ($response->getStatusCode() !== 200) {
            throw new FrameworkException('Could not create ticket at fritid.dk');
        }

        $data = json_decode($response->getBody(), true);

        if (!$data) {
            throw new FrameworkException('Data from fritid.dk makes no sense: ' . $response->getBody());
        }

        return $data;
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
        $get_data = $request->get;

        if (empty($get_data->cost) || empty($get_data->fees)) {
            return false;
        }

        $cost = intval($get_data->cost);
        $fees = intval($get_data->fees);

        if (!$cost || !$fees) {
            return false;
        }

        return [
            'amount' => $cost + $fees,
            'cost'   => $cost,
            'fees'   => $fees,
        ];
    }
}
