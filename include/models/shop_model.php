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
 * PHP version 5
 *
 * @category  Infosys
 * @package   Models
 * @author    Peter Lind <peter.e.lind@gmail.com>
 * @copyright 2009-2012 Peter Lind
 * @license   http://www.gnu.org/licenses/gpl.html GPL 3
 * @link      http://www.github.com/Fake51/Infosys
 */

/**
 * handles all data fetching for the shop controller
 *
 * @category Infosys
 * @package  Models
 * @author   Peter Lind <peter.e.lind@gmail.com>
 * @license  http://www.gnu.org/licenses/gpl.html GPL 3
 * @link     http://www.github.com/Fake51/Infosys
 */
class ShopModel extends Model
{
    public function updateShopData(RequestVars $post)
    {
        if (empty($post->input)) {
            throw new Exception('No input from request');
        }

        $required_headers = array(
                             'Navn',
                             'Kode',
                             'Kostpris',
                             'Salgspris',
                             'Beholdning',
                             'Solgt',
                            );

        $header_index = array();

        $data = explode("\n", str_replace(array("\r\n", "\r"), "\n", $post->input));

        $headers = array_flip(explode("\t", array_shift($data)));

        foreach ($required_headers as $header) {
            if (!isset($headers[$header])) {
                throw new Exception('Lacking header ' . $header);
            }

            $header_index[$header] = $headers[$header];
        }

        $products = array();

        foreach ($data as $row) {
            if (strlen(trim($row)) === 0) {
                continue;
            }

            $columns = explode("\t", $row);

            $product = $this->setupProduct($columns[$header_index['Navn']], $columns[$header_index['Kode']]);

            $product->updateValues(array(
                'cost' => $columns[$header_index['Kostpris']],
                'price' => $columns[$header_index['Salgspris']],
                'stock' => $columns[$header_index['Beholdning']],
                'sold'  => $columns[$header_index['Solgt']],
            ));

            $products[] = array(
                'id'    => $product->id,
                'name'  => $product->name,
                'code'  => $product->code,
                'cost'  => $product->getCost(),
                'price' => $product->getPrice(),
                'stock' => $product->getStock(),
                'sold'  => $product->getSold(),
            );
        }

        return $products;
    }

    /**
     * locates the correct product or creates it
     *
     * @param string $name Name of product
     * @param string $code Code to use for product
     *
     * @access protected
     * @return ShopProduct
     */
    protected function setupProduct($name, $code)
    {
        $product = $this->createEntity('ShopProduct');

        $select = $product->getSelect();
        $select->setWhere('name', '=', $name)
            ->setWhere('status', '=', 1)
            ->setWhere('code', '=', $code);

        $result = $product->findBySelect($select);

        if ($result) {
            return $result;
        }

        $product->name = $name;
        $product->code = $code;

        $product->insert();

        return $product;
    }

    public function getAllProducts()
    {
        $product = $this->createEntity('ShopProduct');

        $select = $product->getSelect()
            ->setWhere('status', '=', 1);

        return $product->findBySelectMany($select);
    }

    public function updateIndividualItem(RequestVars $post)
    {
        if (strlen($post->id) === 0 || strlen($post->type) === 0 || strlen($post->value) === 0) {
            throw new Exception('Lacking values for update');
        }

        $product = $this->createEntity('ShopProduct');

        $select = $product->getSelect();
        $select->setWhere('id', '=', $post->id)
            ->setWhere('status', '=', 1);

        $result = $product->findBySelect($select);

        if (!$result) {
            throw new Exception('No such item');
        }

        $result->updateValues(array(
            $post->type => $post->value,
        ));
    }

    public function deleteIndividualItem(RequestVars $post)
    {
        if (strlen($post->id) === 0) {
            throw new Exception('Lacking values for delete');
        }

        $product = $this->createEntity('ShopProduct');

        $select = $product->getSelect();
        $select->setWhere('id', '=', $post->id)
            ->setWhere('status', '=', 1);

        $result = $product->findBySelect($select);

        if (!$result) {
            throw new Exception('No such item');
        }

        $result->remove();
    }

    public function fetchProductStats($product_id)
    {
        $product = $this->createEntity('ShopProduct')->findById($product_id);

        $data = array();

        $baseline = 0;

        $fill_stock = $fill_sold = null;

        foreach ($product->getEvents('sold') as $sold) {
            $time      = substr($sold['timestamp'], 0, -4) . '0:00';
            $timestamp = strtotime($time);

            $baseline += $sold['amount'];

            if (is_null($fill_sold)) {
                $fill_sold = $baseline;
            }

            $data[$timestamp] = array(
                0 => $time,
                1 => $baseline,
            );
        }

        $baseline = 0;

        foreach ($product->getEvents('stock') as $stock) {
            $time      = substr($stock['timestamp'], 0, -4) . '0:00';
            $timestamp = strtotime($time);

            $baseline += $stock['amount'];

            if (is_null($fill_stock)) {
                $fill_stock = $baseline;
            }

            $data[$timestamp][0] = $time;
            $data[$timestamp][2] = $baseline;
        }

        ksort($data);

        foreach ($data as $timestamp => $row) {
            if (isset($row[1])) {
                $fill_sold = $row[1];
            } else {
                $data[$timestamp][1] = $fill_sold;
            }

            if (isset($row[2])) {
                $fill_stock = $row[2];
            } else {
                $data[$timestamp][2] = $fill_stock;
            }
        }

        foreach ($data as $timestamp => $row) {
            ksort($row);
            $data[$timestamp] = array_values($row);
        }

        return array(
            'chart_config' => array(
                'columns' => array(
                    array(
                        'type' => 'string',
                        'name' => 'Time',
                    ),
                    array(
                        'type' => 'number',
                        'name' => 'Sold',
                    ),
                    array(
                        'type' => 'number',
                        'name' => 'Stock',
                    ),
                ),
                'title' => 'Udvikling',
                'type' => 'LineChart',
            ),
            'chart_data' => array_values($data),
        );
    }

    public function getStats(array $products)
    {
        $return = array(
            'overall_profit' => 0,
        );

        foreach ($products as $product) {
            $return['overall_profit'] += $product->getProfit();
        }

        return $return;
    }

}
