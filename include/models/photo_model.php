<?php
    /**
     * Copyright (C) 2009  Peter Lind
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
     * @package    MVC
     * @subpackage Models
     * @author     Peter Lind <peter.e.lind@gmail.com>
     * @copyright  2009 Peter Lind
     * @license    http://www.gnu.org/licenses/gpl.html GPL 3
     * @link       http://www.github.com/Fake51/Infosys
     */

    /**
     * handles data for the photo MVC
     *
     * @package    MVC
     * @subpackage Models
     * @author     Peter Lind <peter.e.lind@gmail.com>
     */
class PhotoModel extends Model
{
    /**
     * checks that the given photo identifier exists
     *
     * @param string $identifier ID to check
     *
     * @access public
     * @return bool
     */
    public function identifierExists($identifier)
    {
        return !is_bool($this->getParticipantIdFromIdentifier($identifier));
    }

    /**
     * returns participant id given an identifier, if it exists
     *
     * @param  $identifier Identifier to look up with
     *
     * @access public
     * @return int|false
     */
    public function getParticipantIdFromIdentifier($identifier)
    {
        $result = $this->db->query('SELECT participant_id FROM participantphotoidentifiers WHERE identifier = ?', $identifier);

        return count($result) ? intval($result[0]['participant_id']) : false;
    }

    /**
     * handles the uploaded image, if possible
     *
     * @param string  $identifier  ID of upload
     * @param string  $upload_type Type of upload to handle
     * @param Request $request     Request object
     *
     * @access public
     * @return bool
     */
    public function handlePhotoUpload($identifier, $upload_type, Request $request)
    {
        if (!$request->isPost()) {
            return false;
        }

        $this->removeExisting($upload_type, $identifier);

        try {
            $datauri = new \DataUri($request->post->image);

            switch ($datauri->getType()) {
            case 'image/png':
                $suffix = 'png';
                break;

            case 'image/jpeg':
                $suffix = 'jpg';
                break;

            case 'image/gif':
                $suffix = 'gif';
                break;

            default:
                return false;
            }

            return !!file_put_contents(PUBLIC_PATH . 'uploads/photo-' . $upload_type . '-' . mb_strtolower($identifier) . '.' . $suffix, $datauri->getContent());

        } catch (FrameworkException $e) {
            return false;
        }
    }

    /**
     * removes existing versions of image
     *
     * @param string $upload_type
     * @param string $identifier
     *
     * @access protected
     * @return self
     */
    protected function removeExisting($upload_type, $identifier)
    {
        $iterator = new DirectoryIterator(PUBLIC_PATH . 'uploads/');

        foreach ($iterator as $file) {
            if (strpos($file->getFilename(), 'photo-' . $upload_type . '-' . mb_strtolower($identifier)) === 0) {
                unlink($file->getPathname());
            }

        }

        return $this;
    }

    /**
     * returns filename and webpath of exitsing image if possible
     *
     * @param string $identifier Photo identifier to check with
     *
     * @access public
     * @return string
     */
    public function getExistingImage($identifier)
    {
        return $this->getExisting($identifier, 'original');
    }

    /**
     * returns filename and webpath of exitsing image if possible
     *
     * @param string $identifier Photo identifier to check with
     *
     * @access public
     * @return string
     */
    public function getExistingCroppedImage($identifier)
    {
        return $this->getExisting($identifier, 'cropped');
    }

    /**
     * returns path to existing image, if found
     *
     * @param string $identifier Identifier to search by
     * @param string $type       Type of image
     *
     * @access protected
     * @return string
     */
    protected function getExisting($identifier, $type)
    {
        $iterator = new DirectoryIterator(PUBLIC_PATH . 'uploads/');

        foreach ($iterator as $file) {
            if (strpos($file->getFilename(), 'photo-' . $type . '-' . mb_strtolower($identifier)) === 0) {
                return '/uploads/' . $file->getFilename();
            }

        }

        return '';
    }

    /**
     * fetches participants that should have but haven't yet
     * uploaded a photo
     *
     * @param int $days How many days between reminders
     *
     * @access public
     * @return array
     */
    public function fetchParticipantsToRemind($days)
    {
        $query = '
SELECT
    d.id,
    ppi.identifier,
    DATE(d.signed_up) AS signed_up
FROM
    deltagere AS d
    JOIN brugerkategorier AS k ON k.id = d.brugerkategori_id
    JOIN participantphotoidentifiers AS ppi ON ppi.participant_id = d.id
WHERE
    k.arrangoer = "ja"
';

        $ids = [];

        $now = new DateTime(date('Y-m-d') . ' 00:00:00');

        foreach ($this->db->query($query) as $row) {
            if (!glob(PUBLIC_PATH . 'uploads/photo-cropped-' . mb_strtolower($row['identifier']) . '*')) {
                if ($days === 0) {
                    $ids[] = $row['id'];
                    continue;

                }

                $diff = $now->diff(new DateTime($row['signed_up'] . ' 00:00:00'));

                if ($diff->d && $diff->d % $days === 0) {
                    $ids[] = $row['id'];
                }

            }

        }

        if (empty($ids)) {
            return [];
        }

        $select = $this->createEntity('Deltagere')->getSelect();

        $select->setWhere('id', 'in', $ids);

        return $this->createEntity('Deltagere')->findBySelectMany($select);
    }
}
