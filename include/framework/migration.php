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
 * PHP version 5.5+
 *
 * @package   Framework
 * @author    Peter Lind <peter.e.lind@gmail.com>
 * @copyright 2015 Peter Lind
 * @license   http://www.gnu.org/licenses/gpl.html GPL 3
 * @link      http://www.github.com/Fake51/Infosys
 */

/**
 * DB migration class
 *
 * @package Framework
 * @author  Peter Lind <peter.e.lind@gmail.com>
 */
class Migration
{
    const INDEX_FILE = 'index.json';

    /**
     * db instance
     *
     * @var DB
     */
    private $db;

    /**
     * Log instance
     *
     * @var Log
     */
    private $log;

    /**
     * public constructor
     *
     * @param DB  $db      DB instance
     * @param int $version Log instance
     *
     * @access public
     */
    public function __construct(DB $db, Log $log)
    {
        $this->db  = $db;
        $this->log = $log;
    }

    /**
     * checks for the migrations index file and status thereof
     *
     * @access protected
     * @return bool
     */
    protected function isIndexUpdated()
    {
        if (!is_file(MIGRATION_FOLDER . self::INDEX_FILE)) {
            return false;
        }

        $stat = stat(MIGRATION_FOLDER . self::INDEX_FILE);

        if (empty($stat[9]) || time() > $stat[9] + 86400) {
            return false;
        }

        return true;
    }

    /**
     * updates the index file
     *
     * @throws MigrationException
     * @access protected
     * @return $this
     */
    protected function updateIndex()
    {
        if ((is_file(MIGRATION_FOLDER . self::INDEX_FILE) && !is_writable(MIGRATION_FOLDER . self::INDEX_FILE)) || !is_writable(MIGRATION_FOLDER)) {
            throw new MigrationException('Cannot write to migration index file!');
        }

        $dir = new DirectoryIterator(MIGRATION_FOLDER);

        $migrations = [];

        foreach ($dir as $file) {
            if ($file->isDot()) {
                continue;
            }

            if (preg_match('/^[1-9][0-9]*-.+.sql$/', $file->getFilename())) {
                $migrations[intval($file->getFilename())] = $file->getPathname();
            }

        }

        if (!file_put_contents(MIGRATION_FOLDER . self::INDEX_FILE, json_encode($migrations, JSON_PRETTY_PRINT))) {
            throw new MigrationException('Cannot write to migration index file!');
        }

        return $this;
    }

    /**
     * returns migrations that have not yet been run
     *
     * @throws MigrationException
     * @access protected
     * @return array
     */
    protected function getAvailableMigrations()
    {
        if (!($file = file_get_contents(MIGRATION_FOLDER . self::INDEX_FILE))) {
            throw new MigrationException('Could not open migration index file');
        }

        $index = json_decode($file, true);

        asort($index);

        if (empty($index)) {
            return [];
        }

        $mapper = function ($x) {
            return 'SELECT ? AS id';
        };

        $mapped = array_map($mapper, $index);

        $query = '
SELECT
    temp.id
FROM
    (
        ' . implode(' UNION ', $mapped) . '
    ) AS temp
    LEFT JOIN migrations AS m ON m.id = temp.id
WHERE
    m.id IS NULL';

        try {
            $migrations = [];

            foreach ($this->db->query($query, array_keys($index)) as $row) {
                $migrations[$row['id']] = $index[$row['id']];
            }

            return $migrations;

        } catch (DBException $e) {
            return $index;
        }

    }

    /**
     * checks if a migration is needed
     *
     * @access public
     * @return bool
     */
    public function checkMigrationRequirement()
    {
        if (!$this->isIndexUpdated()) {
            $this->updateIndex();
        }

        return !!$this->getAvailableMigrations();
    }

    /**
     * runs available migrations
     *
     * @throws Exception
     * @access public
     * @return $this
     */
    public function runAvailableMigrations()
    {
        foreach ($this->getAvailableMigrations() as $id => $migration_file) {
            $contents = file_get_contents($migration_file);

            if ($contents) {
                // Match single line comments or multiline queries
                preg_match_all('/^[ \n]*--.+?$|^.+?;\s*$/ms', $contents . ';', $matches);

                $this->log->logToFile('Running migration ' . $migration_file . ' with id ' . $id);

                foreach ($matches[0] as $query) {
                    $query = trim($query, "\n ;");
                    if (strpos($query, "--") === 0) {
                        $this->log->logToFile('Skipping comment '.$query);
                        continue; //Skip comment
                    }

                    if ($query) {
                        $this->log->logToFile('Executing query: '.$query);
                        $this->db->exec($query);
                    }

                }

            }

            $this->markMigrationRun($id);

            $this->log->logToFile('Migration ' . $id . ' run');

        }

        return $this;
    }

    /**
     * records a migration as run
     *
     * @param int $migration_id Id of migration
     *
     * @access protected
     * @return $this
     */
    protected function markMigrationRun($migration_id)
    {
        $query = 'INSERT INTO migrations SET id = ?';

        $this->db->exec($query, [$migration_id]);

        return $this;
    }

    /**
     * ensures that a specific migration version
     * is in place
     *
     * @param int $version_id Id of required migration
     *
     * @throws Exception
     * @access public
     * @return $this
     */
    public function ensureVersion($version_id)
    {
        if ($this->checkIfMigrationRun($version_id)) {
            return $this;
        }

        $migrations = $this->updateIndex()
            ->getAvailableMigrations();

        if (!isset($migration[$version_id])) {
            throw new MigrationException('Requested migration version not available');
        }

        return $this->runAvailableMigrations();
    }

    /**
     * returns true if migration with provided id has been run
     *
     * @param int $version_id Id of migration to check
     *
     * @access public
     * @return bool
     */
    public function checkIfMigrationRun($version_id)
    {
        $query = '
SELECT id
FROM migrations
WHERE id = ?
';

        $rows = $this->db->query($query, [$version_id]);

        return count($rows) > 0;
    }
}
