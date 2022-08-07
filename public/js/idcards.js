/*global document, window */

(function () {
    'use strict';

    var images,
        currentCanvas,
        CardCanvas = function () {
            this.canvas = document.createElement('canvas');
            this.canvas.setAttribute('width', this.CANVAS_WIDTH);
            this.canvas.setAttribute('height', this.CANVAS_HEIGHT);
            getContainer().appendChild(this.canvas);

            this.context = this.canvas.getContext('2d');

            this.xOffset = 0;
            this.yOffset = 0;
            this.nextLine = 0;
        },
        getContainer = function () {
            var container = document.querySelector('.idCardContainer');

            getContainer = function () {
                return container;
            };

            return container;
        },
        init = function () {
            var doc = new jsPDF(),
                containers,
                i,
                length;

            Array.prototype.forEach.call(document.getElementsByTagName('img'), function (image) {
                if (!currentCanvas) {
                    currentCanvas = new CardCanvas();
                }

                if (!currentCanvas.addImageToCanvas(image)) {
                    currentCanvas = new CardCanvas();
                    currentCanvas.addImageToCanvas(image);
                }
            });

            containers = document.getElementsByTagName('canvas');

            for (i = 0, length = containers.length; i < length; i++) {
                if (i > 0) {
                    doc.addPage();
                }

                doc.addImage(containers[i].toDataURL('image/png'), 'png', 7, 10, 203, 286, null, 'FAST');
            }

            if (containers.length) {
                doc.save('idcards.pdf');
            }
        };

    CardCanvas.prototype.CANVAS_WIDTH = 2310;
    CardCanvas.prototype.CANVAS_HEIGHT = 3258;

    CardCanvas.prototype.getContext = function () {
        return this.context;
    };

    CardCanvas.prototype.addImageToCanvas = function (image) {
        var requiredWidth  = image.width + 60,
            requiredHeight = image.height + 60;

        // determine space needed for image
        if (this.CANVAS_WIDTH - this.xOffset < requiredWidth) {
            this.yOffset = this.nextLine;
            this.xOffset = 0;

        }

        if (this.CANVAS_HEIGHT - this.yOffset < requiredHeight) {
            return false;
        }

        this.context.beginPath();
        this.context.moveTo(this.xOffset, this.yOffset + 29);
        this.context.lineTo(this.xOffset + image.width + 60, this.yOffset + 29);

        this.context.moveTo(this.xOffset + 29, this.yOffset);
        this.context.lineTo(this.xOffset + 29, this.yOffset + 60 + image.height);

        this.context.moveTo(this.xOffset, this.yOffset + 31 + image.height);
        this.context.lineTo(this.xOffset + image.width + 60, this.yOffset + 31 + image.height);

        this.context.moveTo(this.xOffset + 31 + image.width, this.yOffset);
        this.context.lineTo(this.xOffset + 31 + image.width, this.yOffset + 60 + image.height);

        this.context.closePath();
        this.context.stroke();

        this.context.drawImage(image, this.xOffset + 30, this.yOffset + 30);

        this.xOffset += requiredWidth + 30;

        if (this.yOffset + requiredHeight + 30 > this.nextLine) {
            this.nextLine = this.yOffset + requiredHeight + 30;
        }

        return true;
    };

    init();
}());
