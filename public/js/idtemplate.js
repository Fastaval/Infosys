/*jslint nomen:true */
/*global $, RColor, _, infosysCreateTemplateUrl, Promise */

(function () {
    'use strict';

    /*
     * todos:
     * add scroll to preview as needed, not always
     * add text/icon to boxes to show what they are
     * scroll inside preview when dragging should drag along item
     * styling
     * zoom
     */
    var elements = {},
        appState,
        newTemplateTemplate,
        activeElement,
        activeTemplateIndex = false,
        context,
        isDragging = false,
        dragFrom = {
            x: 0,
            y: 0
        },
        previewDimensions = {
            width: 0,
            height: 0
        },
        elementPersistMapper = function (element) {
            var out = {},
                key;

            for (key in element) {
                if (element.hasOwnProperty(key) && key !== 'element') {
                    out[key] = element[key];
                }
            }

            return out;
        },
        templatePersistMapper = function (template) {
            return {
                id: template.id,
                name: template.name,
                background: template.background,
                elements: template.elements.map(elementPersistMapper)
            };
        },
        syncListItemName = function (id, name) {
            var $listItemName = elements.$templatesList.find('.idTemplate_templatesList_item[data-id="' + id + '"] .idTemplate_templatesList_templateName');

            if ($listItemName.length) {
                $listItemName.text(name);
            }
        },
        persistTemplateChanges = _.debounce(function () {
            if (activeTemplateIndex === false) {
                return;
            }

            $.ajax({
                url: infosysUpdateTemplateUrl.replace(/:id:/, appState[activeTemplateIndex].id),
                type: 'POST',
                data: {
                    data: JSON.stringify({
                        template: templatePersistMapper(appState[activeTemplateIndex])
                    })
                },
                success: function () {
                    syncListItemName(appState[activeTemplateIndex].id, appState[activeTemplateIndex].name);
                    updateCategoryList();
                },
                error: function () {
                    alert('Failed to save template');
                }
            });
        }, 100),
        disableEditControls = function () {
            elements.$positionControls.addClass('hidden');
            elements.$dataSourceControl.addClass('hidden');
            elements.$removeControl.addClass('hidden');
            elements.$typeControl.addClass('hidden');
        },
        resetPreview = function () {
            if (activeTemplateIndex === false) {
                return;
            }

            appState[activeTemplateIndex].elements.forEach(function (item) {
                item.element.remove();
            });

            disableEditControls();
        },
        disableElementAddControls = function () {
            $('*[data-group="add-item"]').attr('disabled', true);
        },
        enableElementAddControls = function () {
            $('*[data-group="add-item"]').removeAttr('disabled');
        },
        updateSizeIndicator = function (width, height) {
            var widthCm = Math.round(width / 300 * 2.54, 1),
                heightCm = Math.round(height / 300 * 2.54, 1),
                widthMm = Math.round(width / 30 * 2.54, 1),
                heightMm = Math.round(height / 30 * 2.54, 1);

            elements.$renderSize.text('Render size (at 300 DPI): width ' + widthCm + ' cm / ' + widthMm + ' mm, height ' + heightCm + ' cm / ' + heightMm + ' mm');
        },
        setPreviewBackground = function (template) {
            return new Promise(function (resolve, reject) {
                var image = new Image();

                if (!template.background) {
                    reject();
                    return;
                }

                image.onload = function () {
                    previewDimensions.width = image.width;
                    previewDimensions.height = image.height;

                    updateSizeIndicator(previewDimensions.width, previewDimensions.height);

                    elements.$canvas.attr('height', previewDimensions.height);
                    elements.$canvas.attr('width', previewDimensions.width);
                    elements.$svg.attr('height', previewDimensions.height);
                    elements.$svg.attr('width', previewDimensions.width);

                    context.drawImage(this, 0, 0);

                    enableElementAddControls();

                    resolve(template);
                };

                if (template.background.dataUrl) {
                    image.src = template.background.dataUrl;

                } else if (template.background.src) {
                    image.src = template.background.src;

                } else {
                    disableElementAddControls();
                    elements.$canvas.attr('height', 0);
                    elements.$canvas.attr('width', 0);
                    elements.$svg.attr('height', 0);
                    elements.$svg.attr('width', 0);
                    resolve(template);
                }
            });
        },
        constrainTemplateElements = function (template) {
            var height = elements.$canvas.attr('height'),
                width  = elements.$canvas.attr('width');

            template.elements.forEach(function (item) {
                if (item.width + item.x > width) {
                    item.x -= item.width + item.x - width;
                }

                if (item.x < 0) {
                    item.width += item.x;
                    item.x = 0;
                }

                if (item.height + item.y > height) {
                    item.y -= item.height + item.y - height;
                }

                if (item.y < 0) {
                    item.height += item.y;
                    item.y = 0;
                }

                updateAttributes(item);
            });

            return template;
        },
        addEditingElements = function (template) {
            template.elements.forEach(function (item) {
                item.element = createRect();
                item.element.InfosysOwner = item;

                updateAttributes(item);
            });
        },
        handleFileLoad = function (e) {
            resetPreview();

            appState[activeTemplateIndex].background.dataUrl = e.target.result;

            setPreviewBackground(appState[activeTemplateIndex])
                .then(constrainTemplateElements)
                .then(addEditingElements);

            persistTemplateChanges();

            elements.$backgroundInput.val('');
        },
        fileReaderOptions = {
            accept: 'image/*',

            readAsDefault: 'DataURL',
            on: {
                load: handleFileLoad,
                error: function (e, file) {
                    console.log(e);
                }
            }
        },
        updateCategoryList = function () {
            var options = appState.reduce(function (agg, item) {
                    agg[item.id] = item.name;

                    return agg;
                }, {});

            elements.$categoryList.find('select').each(function () {
                var self = $(this),
                    selected = self.val(),
                    tempOptions = _.assign({}, options);

                self.children().each(function () {
                    if (this.value) {
                        if (!tempOptions[this.value]) {
                            if (selected === this.value) {
                                this.parentNode.selectedIndex = 0;
                                categoryTemplateChangeHandler.call(this.parentNode);
                            }

                            this.parentNode.removeChild(this);

                        } else {
                            if (this.value && this.textContent !== tempOptions[this.value]) {
                                this.textContent = tempOptions[this.value];
                            }

                            delete tempOptions[this.value];
                        }
                    }
                });

                _.forEach(tempOptions, function (item, key) {
                    self.append('<option value="' + key + '">' + item + '</option>');
                });
            });
            // step through all categories and update
            // if update affects selected option, fire off change trigger
        },
        updateTemplateHandler = function () {
            var newName = elements.$editTemplateName.val(),
                update = false;

            if (activeTemplateIndex === false) {
                return;
            }

            if (appState[activeTemplateIndex].name !== newName) {
                update = true;
                appState[activeTemplateIndex].name = newName;
            }

            if (update) {
                persistTemplateChanges();

            }
        },
        midPoint = function (start, size) {
            return start + size / 2;
        },
        makeRotationPoint = function (element) {
            if (!element.rotation) {
                return 0;
            }

            return element.rotation + ' ' + midPoint(element.x, element.width) + ' ' + midPoint(element.y, element.height);
        },
        updateAttributes = function (element) {
            element.element.setAttribute('x', element.x);
            element.element.setAttribute('y', element.y);
            element.element.setAttribute('width', element.width);
            element.element.setAttribute('height', element.height);

            element.element.setAttribute('transform', 'rotate(' + makeRotationPoint(element) + ')');
        },
        updateControls = function (element) {
            elements.$xInput.val(element.x);
            elements.$yInput.val(element.y);
            elements.$widthInput.val(element.width);
            elements.$heightInput.val(element.height);
            elements.$rotationInput.val(element.rotation);

            switch (element.type) {
            case 'text':
                elements.$typeControlName.text('Text');
                elements.$dataSourceControl.removeClass('hidden');
                break;

            case 'photo':
                elements.$typeControlName.text('Photo');
                elements.$dataSourceControl.addClass('hidden');
                break;

            case 'barcode':
                elements.$typeControlName.text('Barcode');
                elements.$dataSourceControl.addClass('hidden');
                break;

            }

            if (element.dataSource) {
                elements.$dataSourceSelect.val(element.dataSource);
            }
        },
        updateElement = function (element) {
            updateAttributes(element);
            updateControls(element);

            persistTemplateChanges();
        },
        moveElement = function (element, dX, dY) {
            element.x += dX;
            element.y += dY;

            if (element.x < 0) {
                element.x = 0;
            }

            if (element.y < 0) {
                element.y = 0;
            }

            if (element.x + element.width > previewDimensions.width) {
                element.x = previewDimensions.width - element.width;

                if (element.x < 0) {
                    element.width += element.x;
                    element.x = 0;
                }

            }

            if (element.y + element.height > previewDimensions.height) {
                element.y = previewDimensions.height - element.height;

                if (element.y < 0) {
                    element.height += element.y;
                    element.y = 0;
                }

            }
        },
        setTextDataSource = function () {
            if (!activeElement) {
                return;
            }

            activeElement.dataSource = elements.$dataSourceSelect.val();

            persistTemplateChanges();
        },
        manualPositionHandler = function () {
            var tempX,
                tempY,
                tempWidth,
                tempHeight,
                tempRotation;

            if (!activeElement) {
                return;
            }

            tempX        = Number(elements.$xInput.val()) || 0;
            tempY        = Number(elements.$yInput.val()) || 0;
            tempWidth    = Number(elements.$widthInput.val()) || 0;
            tempHeight   = Number(elements.$heightInput.val()) || 0;
            tempRotation = Number(elements.$rotationInput.val() || 0);

            if (tempRotation >= 360) {
                tempRotation = tempRotation % 360;

            } else if (tempRotation < 0) {
                tempRotation += 360;
            }

            if (activeElement.x === tempX && activeElement.y === tempY && activeElement.width === tempWidth && activeElement.height === tempHeight && activeElement.rotation === tempRotation) {
                return;
            }

            moveElement(activeElement, tempX - activeElement.x, tempY - activeElement.y);

            activeElement.width    = tempWidth;
            activeElement.height   = tempHeight;
            activeElement.rotation = tempRotation;

            updateElement(activeElement);
        },
        createRect = function () {
            var newElement = document.createElementNS("http://www.w3.org/2000/svg", 'rect'),
                color = new RColor();

            newElement.setAttribute('fill', color.get(true));
            elements.$svg[0].appendChild(newElement);

            return newElement;
        },
        enablePreview = function () {
            elements.$previewComponents.removeClass('hidden');
        },
        disablePreview = function () {
            elements.$previewComponents.addClass('hidden');
        },
        enableEditControls = function (type) {
            elements.$positionControls.removeClass('hidden');
            elements.$removeControl.removeClass('hidden');
            elements.$typeControl.removeClass('hidden');
        },
        addBlock = function (block) {
            activeElement = block;
            activeElement.element.InfosysOwner = activeElement;

            updateElement(activeElement);

            appState[activeTemplateIndex].elements.push(activeElement);

            enableEditControls(activeElement.type);
        },
        addBarcodeBlock = function () {
            addBlock({
                x:        0,
                y:        0,
                width:    200,
                height:   100,
                rotation: 0,
                element:  createRect(),
                type:     'barcode'
            });
        },
        addTextBlock = function () {
            addBlock({
                x:          0,
                y:          0,
                width:      200,
                height:     50,
                rotation:   0,
                element:    createRect(),
                dataSource: elements.$dataSourceSelect.children().first().attr('value'),
                type:       'text'
            });
        },
        addPhotoBlock = function () {
            addBlock({
                x:        0,
                y:        0,
                width:    213,
                height:   295,
                rotation: 0,
                element:  createRect(),
                type:     'photo'
            });
        },
        dragStartHandler = function (e) {
            if (!e.target || e.target.nodeName !== 'rect') {
                return;
            }

            activeElement = e.target.InfosysOwner;

            dragFrom.x = e.screenX;
            dragFrom.y = e.screenY;

            isDragging = true;

            updateElement(activeElement);
            enableEditControls(activeElement.type);
        },
        dragEndHandler = function () {
            isDragging = false;
        },
        dragHandler = function (e) {
            if (!isDragging) {
                return;
            }

            moveElement(activeElement, e.screenX - dragFrom.x, e.screenY - dragFrom.y);

            dragFrom.x = e.screenX;
            dragFrom.y = e.screenY;

            updateElement(activeElement);
        },
        registerElements = function () {
            elements.$canvas            = $('.idTemplate_edit_preview_image');
            elements.$svg               = $('.idTemplate_edit_preview_svg');
            elements.$addPhotoButton    = $('.idTemplate_edit_addPhoto');
            elements.$addTextButton     = $('.idTemplate_edit_addText');
            elements.$addBarcodeButton  = $('.idTemplate_edit_addBarcode');
            elements.$positionControls  = $('.idTemplate_edit_controls_position');
            elements.$dataSourceControl = $('.idTemplate_edit_controls_dataSource');
            elements.$dataSourceSelect  = $('.idTemplate_edit_controls_dataSource select');
            elements.$removeControl     = $('.idTemplate_edit_controls_remove');
            elements.$typeControl       = $('.idTemplate_edit_controls_type');
            elements.$typeControlName   = $('.idTemplate_edit_controls_type_name');
            elements.$previewComponents = $('.idTemplate_edit_container');
            elements.$addTemplateItem   = $('.idTemplate_templatesList_addTemplate');
            elements.$addTemplate       = $('.idTemplate_templatesList_addTemplate_button');
            elements.$addTemplateName   = $('.idTemplate_templatesList_name');
            elements.$templatesList     = $('.idTemplate_templatesList');
            elements.$editTemplateName  = $('.idTemplate_edit_templateName_input');
            elements.$renderSize        = $('.idTemplate_edit_templateName_renderSize');
            elements.$categoryList      = $('.idTemplate_categoryTemplates');
            elements.$backgroundInput   = $('.idTemplate_edit_setTemplate');

            newTemplateTemplate = $('#idTemplate_templateItem').text();

            elements.$yInput        = elements.$positionControls.find('[data-content="y"]');
            elements.$xInput        = elements.$positionControls.find('[data-content="x"]');
            elements.$widthInput    = elements.$positionControls.find('[data-content="width"]');
            elements.$heightInput   = elements.$positionControls.find('[data-content="height"]');
            elements.$rotationInput = elements.$positionControls.find('[data-content="angle"]');

            context = elements.$canvas[0].getContext('2d');
        },
        removeElement = function (element) {
            element.element.remove();

            appState[activeTemplateIndex].elements = appState[activeTemplateIndex].elements.filter(function (item) {
                return item !== element;
            });
        },
        removeActiveElement = function () {
            if (activeElement) {
                removeElement(activeElement);
                activeElement = null;
            }

            disableEditControls();
            persistTemplateChanges();
        },
        addTemplateItemHtml = function (name, id) {
            $(newTemplateTemplate.replace(/:name:/, name).replace(/:id:/, id)).insertBefore(elements.$addTemplateItem);

        },
        addTemplateItem = function (name, id) {
            resetPreview();

            addTemplateItemHtml(name, id);

            activeTemplateIndex = appState.length;

            appState.push({
                id: id,
                name: name,
                background: {
                    dataUrl: undefined,
                    src: undefined
                },
                elements: []
            });

            setPreviewBackground(appState[activeTemplateIndex]);

            return id;
        },
        updateTemplateNameEdit = function (template) {
            elements.$editTemplateName.val(template.name);
        },
        finalizeAddingTemplate = function (name) {
            updateTemplateNameEdit(appState[activeTemplateIndex]);
            elements.$addTemplateName.val('');

            enablePreview();
        },
        requestTemplateDeletion = function (id) {
            return new Promise(function (resolve, reject) {
                $.ajax({
                    url: infosysDeleteTemplateUrl.replace(/:id:/, id),
                    success: resolve,
                    error: reject
                });
            });
        },
        requestTemplateCreation = function (name) {
            return new Promise(function (resolve, reject) {
                $.ajax({
                    url: infosysCreateTemplateUrl,
                    type: 'POST',
                    data: {name: name},
                    success: function (data) {
                        resolve(data.id);
                    },
                    error: reject
                });
            });
        },
        fillInTemplate = function (original) {
            appState[activeTemplateIndex].background.src = original.background.src;
            appState[activeTemplateIndex].background.dataUrl = original.background.dataUrl;

            setPreviewBackground(appState[activeTemplateIndex]);

            original.elements.forEach(function (item) {
                var block = {
                        x:        item.x,
                        y:        item.y,
                        width:    item.width,
                        height:   item.height,
                        rotation: item.rotation,
                        element:  createRect(),
                        type:     item.type
                    };

                if (item.dataSource) {
                    block.dataSource = item.dataSource;
                }

                block.element.InfosysOwner = block;

                appState[activeTemplateIndex].elements.push(block);

                updateAttributes(block);
            });

            persistTemplateChanges();
        },
        copyTemplateHandler = function () {
            var $item = $(this).closest('li'),
                name  = $item.find('.idTemplate_templatesList_templateName').text(),
                id    = Number($item.attr('data-id')),
                original;

            if (!name) {
                return;
            }

            name = 'Copy of ' + name;

            appState.forEach(function (item, index) {
                if (id === item.id) {
                    original = item;
                }
            });

            resetPreview();

            requestTemplateCreation(name)
                .then(addTemplateItem.bind(null, name))
                .then(fillInTemplate.bind(null, original))
                .then(finalizeAddingTemplate.bind(null, name))
                .then(updateCategoryList)
                .catch(function (e) {
                    alert('Failed to copy template');
                });
        },
        addTemplateHandler = function (e) {
            var name;

            if (e.keyCode && e.keyCode !== 13) {
                return;
            }

            name = elements.$addTemplateName.val().replace(/^s+/, '').replace(/\s+$/, '');

            if (!name) {
                return;
            }

            requestTemplateCreation(name)
                .then(addTemplateItem.bind(null, name))
                .then(finalizeAddingTemplate.bind(null, name))
                .then(updateCategoryList)
                .catch(function () {
                    alert('Failed to create new template');
                });
        },
        activateTemplate = function (e) {
            var id = Number($(this).closest('.idTemplate_templatesList_item').attr('data-id')),
                templateIndex = false;

            appState.forEach(function (item, index) {
                if (id === item.id) {
                    templateIndex = index;
                }
            });

            if (templateIndex === false) {
                return;
            }

            resetPreview();

            activeTemplateIndex = templateIndex;

            elements.$editTemplateName.val(appState[activeTemplateIndex].name);
            setPreviewBackground(appState[activeTemplateIndex]);

            addEditingElements(appState[activeTemplateIndex]);

            enablePreview();
        },
        removeTemplateItem = function (id, $item) {
            $item.remove();

            if (appState[activeTemplateIndex] && appState[activeTemplateIndex].id === id) {
                disableEditControls();
                disableElementAddControls();
                disablePreview();

                activeTemplateIndex = false;

            }

            appState = appState.filter(function (item) {
                return item.id !== id;
            });
        },
        removeTemplate = function (e) {
            var $item = $(this).closest('.idTemplate_templatesList_item'),
                id = Number($item.attr('data-id'));

            if (confirm('Er du sikker på at slette denne skabelon?')) {
                requestTemplateDeletion(id)
                    .then(removeTemplateItem.bind(null, id, $item))
                    .then(updateCategoryList)
                    .catch(function () {
                        alert('Failed to remove template');
                    });
            }
        },
        categoryTemplateChangeHandler = function () {
            var self = $(this),
                id   = self.closest('li').attr('data-id');

            $.ajax({
                url: infosysUpdateCategoryTemplate.replace(/:id:/, id),
                type: 'POST',
                data: {template_id: self.val()}
            });
        },
        setupFilereader = function () {
            $('.idTemplate_edit, .idTemplate_edit_setTemplate').fileReaderJS(fileReaderOptions);
        },
        setupHandlers = function () {
            elements.$addPhotoButton.click(_.debounce(addPhotoBlock, 200));
            elements.$addTextButton.click(_.debounce(addTextBlock, 200));
            elements.$addBarcodeButton.click(_.debounce(addBarcodeBlock, 200));
            elements.$removeControl.click(_.debounce(removeActiveElement, 200));
            elements.$dataSourceSelect.change(setTextDataSource);
            elements.$templatesList.on('click', '.idTemplate_templatesList_copyTemplate_button', _.debounce(copyTemplateHandler, 100))
                .on('click', '.idTemplate_templatesList_templateName', _.debounce(activateTemplate, 50))
                .on('click', '.idTemplate_templatesList_removeTemplate_button', _.debounce(removeTemplate, 50));

            elements.$editTemplateName.keyup(_.debounce(updateTemplateHandler, 100));

            elements.$positionControls.on('keyup', 'input', _.debounce(manualPositionHandler, 100))
                .on('change', 'input', manualPositionHandler);

            elements.$svg.on('mousedown', _.debounce(dragStartHandler, 100));
            elements.$addTemplate.click(_.debounce(addTemplateHandler, 200));
            elements.$addTemplateName.keyup(_.debounce(addTemplateHandler, 200));

            elements.$categoryList.on('change', 'select', categoryTemplateChangeHandler);

            $('body').on('mousemove', dragHandler)
                .on('mouseup', _.debounce(dragEndHandler, 100));
        },
        populateTemplateList = function (state) {
            state.forEach(function (item) {
                addTemplateItemHtml(item.name, item.id);
            });
        },
        handleExistingData = function (dataStructure) {
            appState = dataStructure.map(function (item) {
                item.id = Number(item.id);

                return item;
            });

            populateTemplateList(appState);
        },
        init = function () {
            registerElements();
            setupFilereader();
            setupHandlers();
            handleExistingData(infosysTemplateData || []);
        };

    $(init);
}());
