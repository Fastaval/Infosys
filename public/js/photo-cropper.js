/*global infosysExportWidth, infosysExportHeight, infosysCropUrl, infosysOriginalUrl */

(function () {
    'use strict';
    var elements = {},
        cropItSettings = {
            imageBackground: true,
            width: infosysExportWidth,
            height: infosysExportHeight
        },
        indicateSuccess = function () {
            elements.$cropper.hide();
            elements.$uploadSuccess.show();
        },
        indicateFailure = function () {
            elements.$uploadFailure.show();
        },
        upload = function (data, url) {
            return new Promise(function (resolve, reject) {
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {image: data},
                    success: resolve,
                    error: reject
                });
            });
        },
        uploadCropped = function () {
            return upload(elements.$cropper.cropit('export'), infosysCropUrl);
        },
        uploadOriginal = function (data) {
            return upload($('.cropit-preview-background').attr('src'), infosysOriginalUrl);
        },
        uploadPhoto = function () {
            Promise.all([
                uploadCropped(),
                uploadOriginal()
            ])
                .then(indicateSuccess)
                .catch(indicateFailure);
        },
        triggerImageSelect = function () {
            elements.$fileInput.click();
        },
        rotateCcw = function () {
            elements.$cropper.cropit('rotateCCW');
        },
        rotateCw = function () {
            elements.$cropper.cropit('rotateCW');
        },
        registerElements = function () {
            elements.$cropper       = $('#image-cropper');
            elements.$uploadBtn     = $('.upload-image-btn');
            elements.$uploadFailure = $('.upload-failure');
            elements.$uploadSuccess = $('.upload-success');
            elements.$selectImage   = $('.select-image-btn');
            elements.$fileInput     = $('.cropit-image-input');
            elements.$rotateCcw     = $('.cropit-rotate-ccw');
            elements.$rotateCw      = $('.cropit-rotate-cw');
        },
        setup = function () {
            elements.$cropper.cropit(cropItSettings);
            elements.$uploadBtn.click(uploadPhoto);
            elements.$selectImage.click(triggerImageSelect);

            elements.$rotateCcw.click(rotateCcw);;
            elements.$rotateCw.click(rotateCw);;

            if (infosysExistingImageUrl !== '') {
                elements.$cropper.cropit('imageSrc', infosysExistingImageUrl);
            }
        },
        init = function () {
            registerElements();
            setup();
        };

    // todo
    // create id templating output
    // set used id template per participant

    $(init);
}());

