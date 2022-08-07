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
 * handles all id template stuff
 *
 * @category Infosys
 * @package  Controllers 
 * @author   Peter Lind <peter.e.lind@gmail.com>
 * @license  http://www.gnu.org/licenses/gpl.html GPL 3
 * @link     http://www.github.com/Fake51/Infosys
 */
class IdTemplateController extends Controller
{
    protected $prerun_hooks = [
                               [
                                'method'    => 'checkUser',
                                'exclusive' => true,
                               ]
                              ];

    /**
     * show the id template editing page
     *
     * @access public
     * @return void
     */
    public function showEdit()
    {
        $this->page->clearEarlyLoadJs();

        $this->page->registerLateLoadJS('jquery.2.2.4.min.js');
        $this->page->registerLateLoadJS('jquery-ui.1.12.1.js');
        $this->page->registerLateLoadJS('jquery-ui-timepicker-addon.js');
        $this->page->registerLateLoadJS('filereader.js');
        $this->page->registerLateLoadJS('bluebird.min.js');
        $this->page->registerLateLoadJS('lodash.min.js');
        $this->page->registerLateLoadJS('rcolor.js');
        $this->page->registerLateLoadJS('common.js');
        $this->page->registerLateLoadJS('idtemplate.js');

        $this->page->includeCSS('main.css');
        $this->page->includeCSS('jquery-ui.1.12.1.min.css');
        $this->page->includeCss('bootstrap.min.css');
        $this->page->includeCss('bootstrap-responsive.min.css');
        $this->page->includeCss('main-print.css', 'print');
        $this->page->includeCss('idtemplate.css');
        $this->page->includeCss('fontello-ebe72605/css/idtemplate.css');

        $this->page->setTemplate('idtemplate/showedit');

        $this->page->template_data = $this->model->fetchTemplateData();

        $this->page->category_data = $this->model->fetchCategoryData();

        $this->page->layout_template = 'noincludes.phtml';
    }

    /**
     * creates a new template if possible, given a post request with a name
     *
     * @access public
     * @return void
     */
    public function createTemplate()
    {
        $this->page->setTemplate('generic/json');
        $this->page->layout_template = 'contentonly.phtml';

        if (!$this->page->request->isPost() || !$this->page->request->post->name) {
            $this->page->setStatus(400, 'Lacking POST input');
            return;
        }

        $template = $this->model->createTemplate($this->page->request->post->name);

        if (!$template) {
            $this->page->setStatus(400, 'Lacking POST input');
            return;
        }

        $this->page->setHeader('Content-Type', 'application/json');

        $this->page->json_input = ['id' => intval($template->id)];
    }

    /**
     * deletes a template, if possible
     *
     * @access public
     * @return void
     */
    public function deleteTemplate()
    {
        $this->page->setTemplate('generic/json');
        $this->page->layout_template = 'contentonly.phtml';

        if (empty($this->vars['id'])) {
            $this->page->setStatus(400, 'Lacking id of template to delete');
            return;
        }

        if (!$this->model->deleteTemplate(intval($this->vars['id']))) {
            $this->page->setStatus(500, 'Failed to delete template');
            return;
        }
    }

    /**
     * updates a template, if possible
     *
     * @access public
     * @return void
     */
    public function updateTemplate()
    {
        $this->page->setTemplate('generic/json');
        $this->page->layout_template = 'contentonly.phtml';

        if (empty($this->vars['id'])) {
            $this->page->setStatus(400, 'Lacking id of template to update');
            return;
        }

        if (!$this->page->request->isPost() || !$this->page->request->post->data) {
            $this->page->setStatus(400, 'Lacking update data');
            return;
        }

        $data = json_decode($this->page->request->post->data, true);

        if (!is_array($data)) {
            $this->page->setStatus(400, 'Update data not JSON-compliant');
            return;
        }

        if (!$this->model->updateTemplate(intval($this->vars['id']), $data)) {
            $this->page->setStatus(500, 'Failed to update template');
            return;
        }
    }

    /**
     * updates a category template relationship, if possible
     *
     * @access public
     * @return void
     */
    public function updateCategoryTemplate()
    {
        $this->page->setTemplate('generic/json');
        $this->page->layout_template = 'contentonly.phtml';

        if (empty($this->vars['id'])) {
            $this->page->setStatus(400, 'Lacking id of category to update');
            return;
        }

        if (!$this->page->request->isPost()) {
            $this->page->setStatus(400, 'Lacking update data');
            return;
        }

        if (!$this->model->updateCategoryTemplate(intval($this->vars['id']), intval($this->page->request->post->template_id))) {
            $this->page->setStatus(500, 'Failed to update category');
            return;
        }
    }

    /**
     * renders ID cards onto a page
     *
     * @access public
     * @return void
     */
    public function renderIdCards()
    {
        $this->page->setTemplate('idtemplate/renderidcards');
        $this->page->layout_template = 'minimal.phtml';
        $this->page->includeCSS('idcards.css');

        $this->page->registerLateLoadJS('bluebird.min.js');
        $this->page->registerLateLoadJS('lodash.min.js');
        $this->page->registerLateLoadJS('jspdf.min.js');
        $this->page->registerLateLoadJS('idcards.js');

        $this->page->id_card_entities = $this->model->fetchIdCardData($this->page->request->get);
    }

}
