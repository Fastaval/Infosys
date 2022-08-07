<?php
/**
 * contains dependency injection container
 * and it's exception class
 *
 * PHP Version 5.3+
 *
 * @category PLPHP-Library
 * @package  PLPHP-Library
 * @author   Peter Lind <peter.e.lind@gmail.com>
 * @license  https://github.com/Fake51/PLPHP-Library/blob/master/COPYRIGHT FreeBSD License
 * @link     http://www.plphp.dk
 */

/**
 * dependency injection container exception
 *
 * @category PLPHP-Library
 * @package  PLPHP-Library-Exceptions
 * @author   Peter Lind <peter.e.lind@gmail.com>
 * @license  https://github.com/Fake51/PLPHP-Library/blob/master/COPYRIGHT FreeBSD License
 * @link     http://www.plphp.dk
 */
class DICException extends exception
{
}

/**
 * dependency injection container
 *
 * @category PLPHP-Library
 * @package  PLPHP-Library
 * @author   Peter Lind <peter.e.lind@gmail.com>
 * @license  https://github.com/Fake51/PLPHP-Library/blob/master/COPYRIGHT FreeBSD License
 * @link     http://www.plphp.dk
 */
class DIC
{
    /**
     * dependencies for various classes
     *
     * @var array
     */
    protected $dependencies = array(
        'DIC' => array(
            'reusable' => true,
        ),
    );

    /**
     * pool for reusing objects when
     * possible
     *
     * @var array
     */
    protected $object_pool = array();

    /**
     * public constructor
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        if (empty($this->object_pool[get_class($this)])) {
            $this->object_pool[get_class($this)] = $this;
        }
    }

    /**
     * adds a class to list of classes than can be
     * created by the container
     *
     * @param string|object $class   Name of class to add or object of class
     * @param stdClass      $options stdClass object with options
     *
     * @throws DICException
     * @access public
     * @return $this
     */
    public function addClass($class, stdClass $options)
    {
        if (is_string($class)) {
            $class_name = $class;

        } elseif (is_object($class)) {
            $class_name = get_class($class);

        } else {
            throw new DICException('Class parameter is not object or string');
        }

        $class_options = array();

        if (!empty($options->reusable)) {
            $class_options['reusable'] = true;
        }

        $this->dependencies[$class_name] = $class_options;
        return $this;
    }

    /**
     * adds an object to pool of reusables
     * - allows for more complex constructions
     *   and setups of objects if needed
     *
     * @param object $object Object for reuse
     * @param string $class  Class name, optional
     *
     * @throws DICException
     * @access public
     * @return $this
     */
    public function addReusableObject($object, $class = '')
    {
        if (!is_object($object)) {
            throw new DICException('Provided parameter is not an object');
        }

        $class = $class ? $class : get_class($object);

        if (!isset($this->dependencies[$class])) {
            $this->dependencies[$class] = array('reusable' => true);
        }

        $this->object_pool[$class] = $object;

        return $this;
    }

    /**
     * factory method - returns an object
     * of the desired kind
     *
     * @param string $class_name Name of class to return object of
     *
     * @throws DICException
     * @access public
     * @return mixed
     */
    public function get($class_name)
    {
        if (!isset($this->dependencies[$class_name])) {
            throw new DICException('No information available for class ' . $class_name);
        }

        if (!empty($this->dependencies[$class_name]['reusable']) && !empty($this->object_pool[$class_name])) {
            return $this->object_pool[$class_name];
        }

        $object = $this->createObject($class_name);

        if (!empty($this->dependencies[$class_name]['reusable'])) {
            $this->object_pool[$class_name] = $object;
        }

        return $object;
    }

    /**
     * creates the individual objects filling
     * them with dependencies as needed
     *
     * @param string $class_name Name of class to create
     *
     * @throws DICException
     * @access protected
     * @return mixed
     */
    protected function createObject($class_name)
    {
        $reflection  = new ReflectionClass($class_name);

        if (empty($this->dependencies[$class_name]['parameters'])) {
            $constructor = $reflection->getConstructor();

            $params    = array();
            $constants = get_defined_constants();
            if ($constructor) {
                foreach ($constructor->getParameters() as $parameter) {
                    if ($class = $parameter->getClass()) {
                        if (!isset($this->dependencies[$class->getName()])) {
                            throw new DICException('Cannot create dependency class ' . $class->getName());
                        }

                        $params[] = $this->get($class->getName());
                    } else {
                        if (!isset($constants[$parameter->getName()])) {
                            throw new DICException('Cannot find dependency: ' . $parameter->getName());
                        }

                        $params[] = $constants[$parameter->getName()];
                    }
                }
            }

            $this->dependencies[$class_name]['parameters'] = $params;
        }

        return count($this->dependencies[$class_name]['parameters']) > 0 ?
            $reflection->newInstanceArgs($this->dependencies[$class_name]['parameters']) :
            $reflection->newInstance();
    }
}
