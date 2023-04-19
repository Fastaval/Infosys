<?php
/**
 * Copyright (C) 2009-2012 Peter Lind
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
 * This factory is used to create entity objects like user
 * All MVC-model derived classes are born with an instance of it
 *
 * @category  Infosys
 * @package   Framework
 * @author    Peter Lind <peter.e.lind@gmail.com>
 * @copyright 2009-2012 Peter Lind
 * @license   http://www.gnu.org/licenses/gpl.html GPL 3
 * @link      http://www.github.com/Fake51/Infosys
 */

/**
 * Entity sub-hierarchy exception class
 *
 * @category Infosys
 * @package  Framework
 * @author   Peter Lind <peter.e.lind@gmail.com>
 * @license  http://www.gnu.org/licenses/gpl.html GPL 3
 * @link     http://www.github.com/Fake51/Infosys
 */
class EntityException extends FrameworkException
{
}

/**
 * Entity factory class
 *
 * @category Infosys
 * @package  Framework
 * @author   Peter Lind <peter.e.lind@gmail.com>
 * @license  http://www.gnu.org/licenses/gpl.html GPL 3
 * @link     http://www.github.com/Fake51/Infosys
 */
class EntityFactory
{

    /**
     * here's where you store the name of the file of your entity class
     * omit the .php bit - because you have to store a .ini file too
     * and it has to have the same name as the class file, except for ending
     *
     * @var array
     */
    protected $entities = array(
        'Afviklinger'               => 'Afviklinger',
        'AfviklingerMultiblok'      => 'AfviklingerMultiblok',
        'Aktiviteter'               => 'Aktiviteter',
        'Boardgame'                 => 'Boardgame',
        'BrugerKategorier'          => 'BrugerKategorier',
        'Deltagere'                 => 'Deltagere',
        'DeltagereArrangoerer'      => 'DeltagereArrangoerer',
        'DeltagereGDSTilmeldinger'  => 'DeltagereGDSTilmeldinger',
        'DeltagereGDSVagter'        => 'DeltagereGDSVagter',
        'DeltagereIndgang'          => 'DeltagereIndgang',
        'DeltagereMadtider'         => 'DeltagereMadtider',
        'DeltagereTilmeldinger'     => 'DeltagereTilmeldinger',
        'DeltagereUngdomsskole'     => 'DeltagereUngdomsskole',
        'DeltagereWear'             => 'DeltagereWear',
        'Gamestart'                 => 'Gamestart',
        'GamestartSchedule'         => 'GamestartSchedule',
        'GDS'                       => 'GDS',
        'GDSCategory'               => 'GDSCategory',
        'GDSVagter'                 => 'GDSVagter',
        'Hold'                      => 'Hold',
        'IdTemplate'                => 'IdTemplate',
        'Indgang'                   => 'Indgang',
        'Loanitem'                  => 'Loanitem',
        'LogItem'                   => 'LogItem',
        'Lokaler'                   => 'Lokaler',
        'Mad'                       => 'Mad',
        'Madtider'                  => 'Madtider',
        'Newsletter'                => 'Newsletter',
        'NewsletterSubscriber'      => 'NewsletterSubscriber',
        'DummyParticipant'          => 'DummyParticipant',
        'Pladser'                   => 'Pladser',
        'Privilege'                 => 'Privilege',
        'Role'                      => 'Role',
        'RolePrivilege'             => 'RolePrivilege',
        'ShopProduct'               => 'ShopProduct',
        'SMSLog'                    => 'SMSLog',
        'TodoItems'                 => 'TodoItems',
        'User'                      => 'User',
        'UserRole'                  => 'UserRole',
        'Videoer'                   => 'Videoer',
        'Wear'                      => 'Wear',
        'WearPriser'                => 'WearPriser',
        );

    /**
     * This static array stores entity reflection instances - that is, the
     * reflection objects used to instantiate entities. Done in order to avoid
     * instantiating them  over and over
     *
     * @var array
     */
    protected static $entity_classes = array();

    /**
     * db connection object
     *
     * @var DB
     */
    protected $db;

    /**
     * Autoloader object
     *
     * @var Autoloader
     */
    protected $autoload;

    public function __construct(DB $db, Autoloader $autoload)
    {
        $this->db       = $db;
        $this->autoload = $autoload;
    }

    /**
     * Creates an entity object based on input args and returns it
     * Passes any arguments to the entity when creating it
     * NOTE: to pass arguments to this function, pass them as normal!
     * I.E.: $this->entity_factory('Group', $other_parameter, $other_parameter, etc);
     *
     * @throws EntityException
     * @return mixed An entity based on RoxEntityBase or false on fail
     * @access public
     */
    public function create(/* args */)
    {
        $arguments = func_get_args();
        if (empty($arguments)) {
            throw new EntityException('No arguments passed to entity factory');
        }

        $entity_name = array_shift($arguments);
        if (!isset($this->entities[$entity_name])) {
            throw new EntityException('No such entity is defined: '. $entity_name);
        }

        if (!isset(self::$entity_classes[$entity_name])) {
            $class_name = $this->autoload->normalizeClass($this->entities[$entity_name]);

            include_once(ENTITY_FOLDER . "{$class_name}.php");
            // all entity classes are created using reflection classes and stored for reuse
            self::$entity_classes[$entity_name] = new ReflectionClass($entity_name);
        }

        array_unshift($arguments, $this);

        $e = self::$entity_classes[$entity_name]->newInstanceArgs($arguments);
        $e->setDB($this->db);
        return $e;
    }
}
