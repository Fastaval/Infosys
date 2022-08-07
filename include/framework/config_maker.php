<?php
/**
 * Copyright (C) 2015  Peter Lind
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
 * handles setup of the config file, checking that parts are valid
 *
 * @category Infosys
 * @package  Framework
 * @author   Peter Lind <peter.e.lind@gmail.com>
 * @license  http://www.gnu.org/licenses/gpl.html GPL 3
 * @link     http://www.github.com/Fake51/Infosys
 */
class ConfigMaker
{

    /**
     * valid settings
     *
     * @var array
     */
    private $settings;

    /**
     * infosys instance
     *
     * @var Infosys
     */
    private $infosys;

    /**
     * Db property tester
     *
     * @var DbSetupTester
     */
    private $db_tester;

    /**
     * public constructor
     *
     * @param Infosys $infosys Infosys app instance
     *
     * @access public
     */
    public function __construct(Infosys $infosys, DbSetupTester $tester)
    {
        $this->infosys   = $infosys;
        $this->db_tester = $tester;
    }

    /**
     * loads temporary config settings from session
     *
     * @access public
     * @return $this
     */
    public function loadFromSession()
    {
        $this->settings = isset($_SESSION['temp-configuration']) ? $_SESSION['temp-configuration'] : array();

        return $this;
    }

    /**
     * writes validated settings to session
     *
     * @access public
     * @return $this
     */
    public function writeToSession()
    {
        $_SESSION['temp-configuration'] = $this->settings;

        return $this;
    }

    /**
     * handles submission of public uri setting
     *
     * @param string $public_uri Submitted string for Public Uri
     *
     * @access protected
     * @return string
     */
    protected function handlePublicUriSetting($public_uri)
    {
        if (!preg_match('#^(https?://)?[^/]+\.[^/]+#', trim($public_uri))) {
            return 'Url does not look like a proper domain';
        }

        $this->settings['app.public_uri'] = $public_uri;

        return '';
    }

    /**
     * handles submission of sitename setting
     *
     * @param string $sitename Submitted sitename
     *
     * @access protected
     * @return string
     */
    protected function handleSitenameSetting($sitename)
    {
        if (empty($sitename)) {
            return 'No sitename';
        }

        $this->settings['app.sitename'] = $sitename;

        return '';
    }

    /**
     * handles submission of log file setting
     *
     * @param string $log_file Log file setting
     *
     * @access protected
     * @return string
     */
    protected function handleLogfileSetting($log_file)
    {
        if (!is_dir(LOGS_FOLDER) && !mkdir(LOGS_FOLDER)) {
            return 'includes/logs/ does not exist and cannot be created';
        }

        $filename = trim($log_file);

        if (substr(realpath(dirname(LOGS_FOLDER . $filename)), 0, strlen(LOGS_FOLDER)) . '/' !== LOGS_FOLDER) {
            return 'Filename for log file looks to be trying to escape logs - folder';
        }

        $path = LOGS_FOLDER . $filename;

        if ((is_file($path) && !is_writable($path)) || (!is_file($path) && !is_writable(LOGS_FOLDER))) {
            return 'includes/logs/ is not writable - change permissions for folder';
        }

        $this->settings['app.log_file'] = $filename;

        return '';
    }

    /**
     * handles db settings
     *
     * @param array $post Post vars
     *
     * @access protected
     * @return string
     */
    protected function handleDbProperties($post)
    {
        $db_properties = [];

        foreach ($post as $key => $value) {
            if (substr($key, 0, 3) === 'db_') {
                $db_properties['db.' . substr($key, 3)] = $value;
            }
        }

        $config = new ArrayConfig($db_properties);

        return $this->db_tester->testConfig($config);
    }

    /**
     * checks date properties like con start and end
     *
     * @param array $post, array $errors   Post vars
     * @param array $errors Errors array
     *
     * @access protected
     * @return array
     */
    protected function handleDateProperties(array $post, array $errors)
    {
        if (empty($post['con_start'])) {
            $errors['con_start'] = 'Missing convention start date';
        }

        if (empty($post['con_end'])) {
            $errors['con_end'] = 'Missing convention end date';
        }

        if (!empty($post['con_start']) && !empty($post['con_end'])) {
            if (!strtotime($post['con_start'])) {
                $errors['con_start'] = 'Cannot make sense of convention start date';
            }

            if (!strtotime($post['con_end'])) {
                $errors['con_end'] = 'Cannot make sense of convention end date';
            }

            if (strtotime($post['con_start']) && strtotime($post['con_end'])) {
                if (strtotime($post['con_start']) > strtotime($post['con_end'])) {
                    $errors['con_start'] = 'Convention start date is after end date';
                }
            }
        }

        return $errors;
    }

    /**
     * handles ajax calls for setup
     *
     * @access public
     * @return void
     */
    public function handlePost()
    {
        $errors = array();

        if (isset($_POST['app_public_uri'])) {
            $errors['app_public_uri'] = $this->handlePublicUriSetting($_POST['app_public_uri']);
        }

        if (isset($_POST['app_sitename'])) {
            $errors['app_sitename'] = $this->handleSitenameSetting($_POST['app_sitename']);
        }

        if (isset($_POST['app_log_file'])) {
            $errors['app_log_file'] = $this->handleLogfileSetting($_POST['app_log_file']);
        }

        if (isset($_POST['db_type'])) {
            $errors['db'] = $this->handleDbProperties($_POST);
        }

        if (isset($_POST['con_start'])) {
            $errors = $this->handleDateProperties($_POST, $errors);
        }

        return array_filter($errors);
    }
}
