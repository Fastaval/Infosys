<?php
/**
 * Copyright (C) 2009-2012  Peter Lind
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
 * @package   Controllers 
 * @author    Peter Lind <peter.e.lind@gmail.com>
 * @copyright 2009-2012 Peter Lind
 * @license   http://www.gnu.org/licenses/gpl.html GPL 3
 * @link      http://www.github.com/Fake51/Infosys
 */

/**
 * handles users, roles and such
 *
 * @category Infosys
 * @package  Controllers 
 * @author   Peter Lind <peter.e.lind@gmail.com>
 * @license  http://www.gnu.org/licenses/gpl.html GPL 3
 * @link     http://www.github.com/Fake51/Infosys
 */
class PhotoController extends Controller
{
    protected $prerun_hooks = array(
        array('method' => 'checkUser', 'exclusive' => false, 'methodList' => ['showUploadForm']),
    );

    /**
     * shows a form for uploading a photo
     *
     * @access public
     * @return void
     */
    public function showUploadForm()
    {
        if (!$this->checkIdentifier()) {
            return $this->send404();
        }

        $this->page->clearEarlyLoadJs();

        $this->page->registerLateLoadJS('jquery.2.2.4.min.js');
        $this->page->registerLateLoadJS('jquery.cropit.js');
        $this->page->registerLateLoadJS('photo-cropper.js');
        $this->page->includeCSS('photo-shopper.css');
        $this->page->includeCss('fontello-ca56566b/css/idtemplate.css');

        $this->page->photo_width  = 213;
        $this->page->photo_height = 295;
        $this->page->identifier   = $this->vars['identifier'];

        $this->page->existing_image = $this->model->getExistingImage($this->page->identifier);

        $this->page->layout_template = 'stripped.phtml';
    }

    /**
     * stores original photo upload
     *
     * @access public
     * @return void
     */
    public function storeOriginal()
    {
        if (!$this->checkIdentifier()) {
            return $this->send404();
        }

        $this->page->setBodyRendering(false);

        if (!$this->model->handlePhotoUpload($this->vars['identifier'], 'original', $this->page->request)) {
            $this->page->setStatus('500', 'Upload failed');
            return;
        }
    }

    /**
     * stores cropped photo upload
     *
     * @access public
     * @return void
     */
    public function storeCropped()
    {
        if (!$this->checkIdentifier()) {
            return $this->send404();
        }

        $this->page->setBodyRendering(false);

        if (!$this->model->handlePhotoUpload($this->vars['identifier'], 'cropped', $this->page->request)) {
            $this->page->setStatus('500', 'Upload failed');
            return;
        }
    }

    /**
     * checks that a valid identifier is provided
     *
     * @access protected
     * @return bool
     */
    protected function checkIdentifier()
    {
        return !empty($this->vars['identifier']) && $this->model->identifierExists($this->vars['identifier']);
    }

    /**
     * returns a 404 error page
     *
     * @access protected
     * @return void
     */
    protected function send404()
    {
        $this->page->setStatus('404', 'Not found');

        $this->page->setTemplate('generic/notfound');
        $this->page->layout_template = 'external.phtml';
        return;
    }
}