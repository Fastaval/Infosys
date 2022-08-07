<?php
/**
 * Copyright (C) 2011-2012 Peter Lind
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
 * @package   Helpers
 * @author    Peter Lind <peter.e.lind@gmail.com>
 * @copyright 2011-2012 Peter Lind
 * @license   http://www.gnu.org/licenses/gpl.html GPL 3
 * @link      http://www.github.com/Fake51/Infosys
 */

/**
 * contains helper functions used by a number of views
 *
 * @category Infosys
 * @package  Helpers
 * @author   Peter Lind <peter.e.lind@gmail.com>
 * @license  http://www.gnu.org/licenses/gpl.html GPL 3
 * @link     http://www.github.com/Fake51/Infosys
 */
class ViewHelper extends Common
{
    protected $view;

    public function __construct(Page $view, Config $config) {
        $this->view   = $view;
        $this->config = $config;
    }

    /**
     * returns an array of hours of the day
     *
     * @access public
     * @return array
     */
    public function getTimeArray()
    {
        $return = array();
        foreach (range(0, 23) as $hour)
        {
            $return[sprintf("%02d:00", $hour)] = $hour . ":00";
            $return[sprintf("%02d:30", $hour)] = $hour . ":30";
        }
        return $return;
    }

    /**
     * outputs a search box with fields for various things
     *
     * @param array $search_vars deprecated
     *
     * @access public
     * @return string
     */
    public function deltagerSearchbox(array $search_vars, Model $model) {

        $fooddays = $model->getAllFoodDays();

        $output = <<<HTML
    <div class="deltager-search-box-inner">
            <fieldset>
                <legend><b>Deltager-søgning</b></legend>
                <table id='search-participant'>


                    <tr>
                        <td><b>ID:</b> <input type='text' name='deltager_search[id]' value=""/></td>
                        <td><b>Køn:</b> {$this->view->genSelect('deltager_search[gender]', array('', 'Mand' => 'm', 'Kvinde' => 'k', 'Andet' => 'a'))}</td>
                        <td><b>Fødselsdato:</b> <input type='text' value='' name='deltager_search[birthdate]' /></td>
                    </tr>
                    <tr>
                        <td colspan='2'><b>Kategori:</b>
                            <select name="deltager_search[brugerkategori_id]" class="doubleinput">
                                <option value=""></option>
HTML;

         foreach ($model->getAllBrugerkategorier() as $b) {
             $output .= '
                                <option value="' . e($b->id) . '">' . e($b->navn) . '</option>
';
        }

        $output .= <<<HTML
                            </select>
                        </td>
                        <td><b>Forudbetalt:</b> <input type='text' value='' name='deltager_search[betalt_beloeb]' /></td>
                    </tr>
                    <tr>
                        <td><b>Fornavn:</b> <input type='text' value='' name='deltager_search[fornavn]' /></td>
                        <td><b>Efternavn:</b> <input type='text' value='' name='deltager_search[efternavn]' /></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td colspan='2'><b>Email:</b> <input class='doubleinput' type='text' value='' name='deltager_search[email]' /></td>
                        <td><b>International:</b> {$this->view->genSelect('deltager_search[international]', array('', 'nej','ja'))}</td>
                    </tr>
                    <tr>
                        <td><b>Adresse:</b> <input type='text' value='' name='deltager_search[adresse]' /></td>
                        <td><b>Postnr:</b> <input type='text' value='' name='deltager_search[postnummer]' /></td>
                        <td><b>By:</b> <input type='text' value='' name='deltager_search[by]' /></td>
                    </tr>
                    <tr>
                        <td><b>Land:</b> <input type='text' value='' name='deltager_search[land]' /></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td><b>Alt.Tlf:</b> <input type='text' value='' name='deltager_search[tlf]' /></td>
                        <td><b>Mobil:</b> <input type='text' value='' name='deltager_search[mobiltlf]' /></td>
                        <td><b>Mobil med:</b> {$this->view->genSelect('deltager_search[medbringer_mobil]', array('', 'nej','ja'))}</td>
                    </tr>

                    <tr>
                        <td colspan='2'><b>Sprog:</b>
HTML;

            foreach ($model->getAvailableSprog() as $sprog) {
                $output .= "

                                <label for='lang_" . e($sprog) . "'>" . substr($sprog, 0,3) . ".</label><input class='langbox' type='checkbox' name='deltager_search[lang][" . e($sprog) . "]' value='" . e($sprog) . "' id='lang_" . e($sprog) . "'/>
";
            }

            $output .= <<<HTML
                        </td>
                    </tr>
                    <tr>
                        <td><b>SuperGDS:</b> {$this->view->genSelect('deltager_search[supergds]', array('', 'nej','ja'))}</td>
                        <td><b>Flere GDSVagter:</b> {$this->view->genSelect('deltager_search[flere_gdsvagter]', array('', 'nej','ja'))}</td>
                        <td><b>SuperGM:</b> {$this->view->genSelect('deltager_search[supergm]', array('', 'nej','ja'))}</td>
                    </tr>

                    <tr>
                        <td><b>Arr. sovesal:</b> {$this->view->genSelect('deltager_search[sovesal]', array('', 'nej','ja'))}</td>
                        <td><b>Arrangør igen:</b> {$this->view->genSelect('deltager_search[arrangoer_naeste_aar]', array('', 'nej','ja'))}</td>
                        <td><b>Må kontaktes:</b> {$this->view->genSelect('deltager_search[may_contact]', array('', 'nej', 'ja'))}</td>
                    </tr>
                    <tr>
                        <td><b>Forfatter:</b> {$this->view->genSelect('deltager_search[forfatter]', array('', 'nej','ja'))}</td>
                        <td><b>Rig onkel:</b> {$this->view->genSelect('deltager_search[rig_onkel]', array('', 'nej', 'ja'))}</td>
                        <td><b>Hemmelig onkel:</b> {$this->view->genSelect('deltager_search[hemmelig_onkel]', array('', 'nej', 'ja'))}</td>
                    </tr>
                    <tr>
                        <td><b>Opstart mandag:</b> {$this->view->genSelect('deltager_search[ready_mandag]', array('', 'nej', 'ja'))}</td>
                        <td><b>Opstart tirsdag:</b> {$this->view->genSelect('deltager_search[ready_tirsdag]', array('', 'nej', 'ja'))}</td>
                        <td><b>Checkin-time:</b>  <input type='text' value='' name='deltager_search[checkin_time]' /></td>
                    </tr>
                    <tr>
                        <td><b>Ønsket antal aktiviteter:</b> <input type='number' min='0' max='10' step='1' value='' name='deltager_search[desired_activities]'/></td>
                        <td><b>Udeblevet:</b> {$this->view->genSelect('deltager_search[udeblevet]', array('', 'nej','ja'))}</td>
                        <td><strong>Simultantolk:</strong> {$this->view->genSelect('deltager_search[interpreter]', array('', 'nej','ja'))}</td>
                    </tr>
                    <tr>
                        <td><b>Annulleret:</b> {$this->view->genSelect('deltager_search[annulled]', array('', 'nej','ja'))}</td>
                        <td><b>Ønsket antal GDS:</b> <input type='number' min='0' max='10' step='1' value='' name='deltager_search[desired_diy_shifts]'/></td>
                    </tr>
                    <tr><td colspan='3'><b>Ungdomsskole:</b> <input class='tripleinput' type='text' value='' name='deltager_search[ungdomsskole]' /></td></tr>
                    <tr><td colspan='3'><b>Arrangørområde:</b> <input class='tripleinput' type='text' value='' name='deltager_search[arbejdsomraade]' /></td></tr>
                    <tr><td colspan='3'><b>Scenarie:</b> <input class='tripleinput' type='text' value='' name='deltager_search[scenarie]' /></td></tr>
                    <tr><td colspan='3'><b>Skills:</b> <input class='tripleinput' type='text' value='' name='deltager_search[skills]' /></td></tr>

                </table>
                <hr/>
                <table id='search-indgang'>
                    <tbody>
HTML;

        foreach ($model->getAllIndgang() as $ind) {
            $output .= <<<HTML
                        <tr>
                            <td>{$ind->getDescription()}</td>
                            <td>{$this->view->genSelect("indgang_search[ind_" . $ind->id . "]", array('','ja','nej'))}</td>
                        </tr>
HTML;
        }

        $output .= <<<HTML
                    </tbody>
                </table>
                <table id='search-food'>
                    <thead>
                        <tr>
                            <th>&nbsp;</th>
HTML;

        foreach ($fooddays as $day) {
            $output .= "<th>" . danishDayNames(date('D', strtotime($day))) . "</th>";

        }

        $output .= <<<HTML
                        </tr>
                    </thead>
                    <tbody>
HTML;
        foreach ($model->getAllMad() as $mad) {
            $madtider = $mad->getMadTider();

            if ($madtider) {
                $output .= "
                            <tr>
                                <td>{$mad->kategori}</td>
";
                foreach ($fooddays as $day) {
                    $output .= "<td>";
                    foreach ($madtider as $mt) {
                        if ($day == $mt->dato) {
                            $output .= $this->view->genSelect("mad_search[mt_" . $mt->id . "]", array('', 'ja', 'nej'));
                        }
                    }

                    $output .= "</td>";
                }

                $output .= "</tr>";
            }
        }

        $output .= <<<HTML
                    </tbody>
                </table>
            </fieldset>
            <label for='logic'>Søgelogik:</label>
            <select name='logic'>
                <option value='and'>And</option>
                <option value='or'>Or</option>
            </select>
            <p>
                <label><input type='radio' name='search_combination' value="" checked /> Ny søgning</label>
                <label><input type='radio' name='search_combination' value="intersection" /> Begræns søgning til tidligere resultat</label>
                <label><input type='radio' name='search_combination' value="union" /> Tilføj søgning til tidligere resultat</label>
                <label><input type='radio' name='search_combination' value="difference" /> Træk tidligere resultat fra denne søgning</label>
            </p>
            <p>
                <button class="submit" type='submit'>Søg</button><button class="reset">Reset</button>
            </p>
    </div>
HTML;

        return $output;
    }

    /**
     * returns a markdown editor
     *
     * @param string $text String to init the editor with
     *
     * @access public
     * @return string
     */
    public function markdown($text) {
        if (function_exists('Markdown')) {
            return Markdown($text);
        }
        return $text;
    }
}
