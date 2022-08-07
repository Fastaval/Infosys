<?php
/**
 * Copyright (C) 2017  Peter Lind
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
 * @copyright 2017 Peter Lind
 * @license   http://www.gnu.org/licenses/gpl.html GPL 3
 * @link      http://www.github.com/Fake51/Infosys
 */

/**
 * handles all id template stuff
 *
 * @category Infosys
 * @package  Framework 
 * @author   Peter Lind <peter.e.lind@gmail.com>
 * @license  http://www.gnu.org/licenses/gpl.html GPL 3
 * @link     http://www.github.com/Fake51/Infosys
 */
class IdTemplateRenderer
{
    const FONT_SIZE = 30;

    // todo:
    // caching
    // cache invalidation

    /**
     * participant to render template of
     *
     * @var Deltagere
     */
    private $participant;

    /**
     * template to render
     *
     * @var IdTemplate
     */
    private $template;

    /**
     * public constructor
     *
     * @param Deltagere  $participant Participant to render template for
     * @param IdTemplate $template    Template to render
     *
     * @access public
     */
    public function __construct(Deltagere $participant, IdTemplateData $template, ParticipantModel $model)
    {
        $this->participant = $participant;
        $this->template    = $template;
        $this->model       = $model;
    }

    /**
     * converts the imagemagick image to image string
     *
     * @param  $image Image to convert
     *
     * @access protected
     * @return string
     */
    protected function convertToPngOutput($image)
    {
        ob_start();
        imagepng($image);
        $output = ob_get_contents();
        ob_end_clean();

        return base64_encode($output);
    }

    /**
     * loads an image from a path
     *
     * @param string $path Path to file to load
     *
     * @throws FrameworkException
     * @access protected
     * @return resource
     */
    protected function loadImage($path)
    {
        $info = getimagesize($path);

        if (!isset($info[2])) {
            throw new FrameworkException('Could not load image: ' . $path);
        }

        switch ($info[2]) {
        case IMAGETYPE_PNG:
            $bg = imagecreatefrompng($path);
            imagealphablending($bg, true);
            imagesavealpha($bg, true);
            return $bg;

        case IMAGETYPE_JPEG:
            return imagecreatefromjpeg($path);

        case IMAGETYPE_GIF:
            return imagecreatefromgif($path);

        default:
            throw new FrameworkException('Unrecognized background format');
        }
    }

    /**
     * creates a base image from the template background
     *
     * @param IdTemplateData $template Template to process
     *
     * @access protected
     * @return resource
     */
    protected function createBackground(IdTemplateData $template)
    {
        return $this->loadImage(PUBLIC_PATH . $template->getBackground());
    }

    /**
     * adds a photo template element
     *
     * @param resource  $image       GD image resource
     * @param array     $item        Template item
     * @param Deltagere $participant Participant to render for
     *
     * @access protected
     * @return resource
     */
    protected function addBarcodeElement($image, array $item, Deltagere $participant)
    {
        $path = $this->model->generateEan8Barcode($participant->id);

        $photo = $this->loadImage($path);

        imagecopyresampled($image, $photo, $item['x'], $item['y'], 0, 0, $item['width'], $item['height'], imagesx($photo), imagesy($photo));

        return $image;
    }

    /**
     * adds a photo template element
     *
     * @param resource  $image       GD image resource
     * @param array     $item        Template item
     * @param Deltagere $participant Participant to render for
     *
     * @access protected
     * @return resource
     */
    protected function addPhotoElement($image, array $item, Deltagere $participant)
    {
        $photo_path = $participant->getCroppedPhotoPath();

        if (!$photo_path) {
            return $image;
        }

        $photo = $this->loadImage($photo_path);

        imagecopyresampled($image, $photo, $item['x'], $item['y'], 0, 0, $item['width'], $item['height'], imagesx($photo), imagesy($photo));

        return $image;
    }

    /**
     * adds a text bit to the template
     *
     * @param resource  $image       Image to modify
     * @param array     $item        Template item
     * @param Deltagere $participant Participant to use
     *
     * @access protected
     * @return resource
     */
    protected function addTextElement($image, array $item, Deltagere $participant)
    {
        switch ($item['datasource']) {
        case 'name':
            $string = $participant->getName();
            break;

        case 'doctorname':
            $string = 'Dr. ' . $participant->getName();
            break;

        case 'id':
            $string = $participant->id;
            break;

        case 'workarea':
            $string = $participant->arbejdsomraade;
            break;

        case 'group':
            $string = $participant->ungdomsskole;
            break;

        case 'scenario':
            $string = $participant->scenarie;
            break;

        default:
            return $image;
        }

        $y_offset = 0;

        do {
            $bounding_box = imagettfbbox(self::FONT_SIZE, $item['rotation'] * -1, PUBLIC_PATH . 'fonts/CaesarDressing-Regular.ttf', $string);
            $width        = abs($bounding_box[4] - $bounding_box[0]);

            if ($width <= $item['width']) {
                $rendered_string = $string;
                $string = '';

                $x_offset = ($item['width'] / 2) - ($width / 2);

            } else {
                $strings = explode("\n", wordwrap($string, floor($item['width'] / $width * mb_strlen($string))));

                $rendered_string = array_shift($strings);
                $string = trim(implode(' ', $strings));

                $bounding_box = imagettfbbox(self::FONT_SIZE, $item['rotation'] * -1, PUBLIC_PATH . 'fonts/CaesarDressing-Regular.ttf', $rendered_string);
                $width        = abs($bounding_box[4] - $bounding_box[0]);
                $x_offset     = ($item['width'] / 2) - ($width / 2);

            }

            imagettftext($image, self::FONT_SIZE, $item['rotation'] * -1, $item['x'] + $x_offset, $item['y'] + $y_offset, imagecolorallocate($image, 0, 0, 0), PUBLIC_PATH . 'fonts/CaesarDressing-Regular.ttf', $rendered_string);

            $y_offset    += 35;

        } while ($string);

        // while bounding box exceeds width
        // chop up string
        // print as much as you can
        // continue with smaller string

        return $image;
    }

    /**
     * processes template items
     *
     * @param resource $agg Image to work on
     * @param $item
     *
     * @access protected
     * @return resource
     */
    protected function processTemplateItems($agg, $item)
    {
        switch ($item['itemtype']) {
        case 'photo':
            return $this->addPhotoElement($agg, $item, $this->participant);

        case 'text':
            return $this->addTextElement($agg, $item, $this->participant);

        case 'barcode':
            return $this->addBarcodeElement($agg, $item, $this->participant);

        default:
            return $agg;
        }
    }

    /**
     * processes the template to render a composite image
     *
     * @access protected
     * @return string
     */
    protected function processTemplate()
    {
        // load background into image
        // process items
        // get image output

        return $this->convertToPngOutput(
            array_reduce(
                $this->template->getItems(),
                [$this, 'processTemplateItems'],
                $this->createBackground($this->template)
            )
        );
    }

    /**
     * returns a filepath for the cache version of the id card
     *
     * @access protected
     * @return string
     */
    protected function getCachePath()
    {
        return CACHE_FOLDER . 'idcard_' . $this->template->id . '_' . $this->participant->id;
    }

    /**
     * fetches template as base64 encoded string, either from
     * cache or from processing it
     *
     * @access protected
     * @return string
     */
    protected function fetchTemplateData()
    {
        if (!file_exists($this->getCachePath())) {
            file_put_contents($this->getCachePath(), $this->processTemplate());
        }

        return file_get_contents($this->getCachePath());
    }

    /**
     * renders template as an image
     *
     * @access public
     * @return string
     */
    public function renderImage()
    {
        return '<img class="idCardContainer_card" src="data:image/png;base64,' . $this->fetchTemplateData() . '" alt=""/>';
    }
}
