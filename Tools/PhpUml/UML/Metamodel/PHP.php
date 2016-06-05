<?php
/**
 * PHP_UML (PHP/MOF program elements classes)
 *
 * PHP version 5
 *
 * This subpackage is a add-on to the EMOF Program elements, as defined
 * in EMOF_Metamodel.php
 * It provides a set of utility classes. 
 * 
 * @category   PHP
 * @package    PHP_UML
 * @subpackage Metamodel
 * @author     Baptiste Autin <ohlesbeauxjours@yahoo.fr> 
 * @license    http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version    SVN: $Revision: 43 $
 * @link       http://pear.php.net/package/PHP_UML
 * @link       http://www.omg.org/mof/
 * @since      $Date: 2008-05-13 01:15:27 +0200 (mar., 13 mai 2008) $
 *
 */

/**
 * Enumerates the basic PHP types.
 * 
 * @package    PHP_UML
 * @subpackage Metamodel
 *
 */
class PHP_UML_Metamodel_Enumeration
{
    /**
     * Datatypes of the language
     *
     * @var array
     */
    static public $datatypes = array('mixed', 'array', 'string', 'int', 'integer',
        'bool', 'boolean', 'float', 'void', 'null', 'object', 'resource');
    
    /**
     * Main file types. Used as stereotypes for qualifying the artifacts.
     *
     * @var array
     */
    static public $filetype = array('PHP File');
}

/**
 * A file object
 * 
 * @package    PHP_UML
 * @subpackage Metamodel
 *
 */
class PHP_UML_Metamodel_File extends PHP_UML_Metamodel_NamedElement
{
}


/**
 * A structure designed to store instances of PHP_UML metamodel's elements.
 * It is composed of a stack, an internal iterator ($_key), a search function
 * and somes accessors.
 * It only accepts PHP_UML_Metamodel_Metamodel_NamedElement objects.
 * 
 * @package    PHP_UML
 * @subpackage Metamodel
 * 
 */
class PHP_UML_Metamodel_Sequence
{
    private $_objects = array();
    private $_key = null;    // internal iterator

    /**
     * Adds a program element to the sequence
     *
     * @param PHP_UML_Metamodel_NamedElement &$element Program element
     */
    public function add(PHP_UML_Metamodel_NamedElement &$element)
    {
        $this->_objects[] = $element;
        if (is_null($this->_key))
            $this->_key = 0;
        else
            $this->_key++;
    }

    /**
     * Returns the object stored at the $index position
     *
     * @param int $index Index position
     * 
     * @return PHP_UML_Metamodel_NamedElement
     */
    public function get($index)
    {
        return $this->_objects[$index];
    }

    /**
     * Returns all the objects stored
     *
     * @return array()
     */
    public function getAll()
    {
        return $this->_objects;
    }
    
    /**
     * Searches for an object
     *
     * @param mixed  $value    The asserted value 
     * @param string $property The property to look into
     * 
     * @return mixed Either the index position, or FALSE
     */
    public function searchElement($value, $property = 'name')
    {
        foreach ($this->_objects as $key => &$o) {
            if ($o->{$property}==$value) {
                return $key;
            }
        }
        return false;
    }

    /**
     * Returns a reference to the current element (head)
     *
     * @return PHP_UML_Metamodel_NamedElement
     */
    public function &current()
    {
        return $this->_objects[$this->_key];
    }

    /**
     * Returns the current index position
     *
     * @return int
     */
    public function key()
    {
        return $this->_key;
    }

    /**
     * Returns an iterator containing all the objects stored
     *
     * @return PHP_UML_Metamodel_SequenceIterator
     */
    public function getIterator()
    {
        return new PHP_UML_Metamodel_SequenceIterator($this->_objects);
    }
}


/**
 * An external iterator for PHP_UML_Metamodel_Sequence
 * PHP_UML_Metamodel_Sequence->getIterator() can get you one.
 *
 * @category   PHP
 * @package    PHP_UML
 * @subpackage Metamodel
 * 
 */
class PHP_UML_Metamodel_SequenceIterator implements Iterator, Countable
{
    private $_key = 0;
    private $_objects = array();
    
    function __construct(Array &$set)
    {
        $this->_objects = $set;
    }

    function current()
    {
        return $this->_objects[$this->_key];
    }

    function key()
    {
        return $this->_key;
    }

    function next()
    {
        $this->_key++;
    }

    function valid()
    {
        return $this->_key<count($this->_objects);
    }

    function rewind()
    {
        $this->_key = 0;
    }
    
    function count()
    {
        return count($this->_objects);
    }
}

/**
 * A superstructure to gather program elements.
 * Normally filled by PHP_UML_PHP_Parser, but you can also fill such an object
 * "by yourself", and pass it to the XMI builder for "manual" XMI generation
 *
 * @category   PHP
 * @package    PHP_UML
 * @subpackage Metamodel
 * 
 */
class PHP_UML_Metamodel_Superstructure
{
    public $packages;
    public $interfaces;
    public $classes;
    public $operations;    
    public $parameters;
    public $datatypes;
    public $files;
    
    
    /**
     * Constructor
     *
     */
    public function __construct()
    {
        $this->packages   = new PHP_UML_Metamodel_Sequence;
        $this->interfaces = new PHP_UML_Metamodel_Sequence;
        $this->classes    = new PHP_UML_Metamodel_Sequence;
        $this->operations = new PHP_UML_Metamodel_Sequence;
        $this->parameters = new PHP_UML_Metamodel_Sequence;
        $this->datatypes  = new PHP_UML_Metamodel_Sequence;
        $this->files      = new PHP_UML_Metamodel_Sequence;

        foreach (PHP_UML_Metamodel_Enumeration::$datatypes as $d) {
            $type       = new PHP_UML_Metamodel_Type;
            $type->name = $d;
            $this->datatypes->add($type);
        }
    }

    /**
     * Recursively replaces all the "named types" by a proper "reference" to a
     * typed element. This impacts:
     * - the extended classes and implemented classes (through their
     * EMOF-"superClass" and "implements" relations)
     * - the function parameters (through their EMOF-"type" attribute)
     * - the properties in classes (through their EMOF-"type" attribute)
     * 
     * @param PHP_UML_Metamodel_Package &$ns    Package to look into
     * @param array                     &$_oDef Default packages to look for
     *                                          orphaned elements
     */
    private function _resolveReferences(PHP_UML_Metamodel_Package &$ns, array &$_oDef)
    {
        if (!is_null($ns->nestedPackage)) {
            foreach ($ns->nestedPackage as &$nsIdx) {
                $this->_resolveReferences($this->packages->get($nsIdx), $_oDef);
            }
        }
        if (!is_null($ns->ownedType))
        foreach ($ns->ownedType as &$elt) {
            if (isset($elt->superClass) && !is_null($elt->superClass)) { 
                foreach ($elt->superClass as &$className) {
                    $this->_resolveType($ns, $className, $_oDef, $elt);
                }
            }
            if (isset($elt->implements) && !is_null($elt->implements)) { 
                foreach ($elt->implements as &$className) {
                    $this->_resolveType($ns, $className, $_oDef, $elt);
                }
            }
            foreach ($elt->ownedOperation as &$function) {
                foreach ($function->ownedParameter as &$parameter) {
                    $this->_resolveType($ns, $parameter->type, $_oDef, $elt); 
                }
            }
            if (isset($elt->ownedAttribute)) {
                foreach ($elt->ownedAttribute as &$attribute) {
                    $this->_resolveType($ns, $attribute->type, $_oDef, $elt);
                }
            }
        } 
    }
    
    /**
     * Searches in a given package for a typed element (likely, a class)
     *
     * @param PHP_UML_Metamodel_Package &$ns   A package element
     * @param string                    $value A name
     *
     * @return mixed Either FALSE if not found, or the element
     */
    public function searchTypeIntoPackage(PHP_UML_Metamodel_Package &$ns, $value)
    {
        foreach ($ns->ownedType as $key => &$o) {
            if (strtolower($o->name)==strtolower($value)) {
                return $o;
            }
        }
        return false;
    }
    
    /**
     * Searches recursively in a given package for a package, knowing its name
     * Works with position numbers, not variable references.
     * 
     * @param PHP_UML_Metamodel_Package &$np   A package element (context)
     * @param string                    $value A package name (to find)
     *
     * @return mixed Either FALSE if not found, or the position in the stack
     */
    public function searchIntoSubpackage(PHP_UML_Metamodel_Package &$np, $value)
    {
        foreach ($np->nestedPackage as $position) {
            $o = $this->packages->get($position);
            if (strtolower($o->name)==strtolower($value)) {
                return $position;
            }
            /*if($recursive) {
                $idx = $this->searchPackageIntoPackage($o, $value);
                if(!($idx===false))
                    return $idx;
            }*/
        }
        return false;
    }
    
    /**
     * Searches in the datatypes for a particular element
     *
     * @param string $value The datatype to find
     * 
     * @return mixed Either FALSE if not found, or the element
     */
    private function _searchIntoDatatype($value)
    { 
        foreach ($this->datatypes->getIterator() as $dt) {
            if (strtolower($dt->name)==strtolower($value)) {
                return $dt;
            }
        }
        return false;
    }
    
    /**
     * Does the type resolution for a given element in a given package
     *
     * @param PHP_UML_Metamodel_Package &$pkg     The nesting package
     * @param string                    &$element The element to resolve, provided as a name
     * @param array                     &$pkgDef  The default packages
     * @param PHP_UML_Metamodel_Type    &$context The context (the nesting class/interface, which 
     *                                            is the only element to know the nesting file)
     */
    private function _resolveType(PHP_UML_Metamodel_Package &$pkg, &$element, 
        array &$pkgDef, PHP_UML_Metamodel_Type &$context)
    {
        // Is it a datatype ?
        $_o = $this->_searchIntoDatatype($element);
        if (!($_o===false)) {
            $element = $_o;
        } else {

            // Is there a "::" in it ?
            list($pos, $first, $last) = $this->getPackagePathParts($element, false);
            if (!($pos===false)) {
                $tmpPkg = &$this->_getPackageFromPath($first); 
                if (is_null($tmpPkg)) {
                    $this->_resolutionWarning($element, $context->file->name);
                    $element = null;
                } else {
                    // Do we know that package?
                    $_o = $this->searchTypeIntoPackage($tmpPkg, $last);
                    if (!($_o===false)) {
                        $element = $_o;
                    } else {
                        $this->_resolutionWarning($element, $context->file->name);
                        $element = null;
                    }
                }
            } else {

                // Is it in the current package?
                $_o = $this->searchTypeIntoPackage($pkg, $element);
                if (!($_o===false)) {
                    $element = $_o;
                } else {
                    // Is it in one of the "default" packages?
                    $found = false;
                    foreach ($pkgDef as $itemPkg) {
                        $_o = $this->searchTypeIntoPackage($itemPkg, $element);
                        if (!($_o===false)) {
                            $element = $_o;
                            $found   = true;
                            break;
                        }
                    }
                    if (!$found) {
                        $this->_resolutionWarning($element, $context->file->name);
                        $element = null;
                    }
                }
            }
        }
    }
    
    /**
     * Resolution error. Might later be isolated in a specific class.
     * 
     * @param string $element Element
     * @param string $file    File context
     */
    private function _resolutionWarning($element, $file)
    {
        PHP_UML_Warning::add('Could not resolve '.$element.' in '.$file);
    }
 
    /**
     * Splits a package path into its first/last element, and the rest
     * 
     * @param string $path      The path to check
     * @param bool   $modeFirst If true, splits into 1st and the rest
     *                          If false, splits into last and the rest
     * 
     * @return array Results array
     */
    public function getPackagePathParts($path, $modeFirst = true)
    {
        $first = '';
        $last  = '';
        if($modeFirst) 
            $pos = strpos($path, '::');
        else
            $pos = strrpos($path, '::');
        if (!($pos===false)) {
            $first = substr($path, 0, $pos);
            $last  = substr($path, $pos+2);
        }
        else
            $first = $path;
        return array($pos, $first, $last);
    }
    
    /**
     * Retrieves a package from a hierarchical package path
     * Works on a stack where references are still named (= before their resolution)
     * 
     * @param string $path Path to follow
     * 
     * @return PHP_UML_Metamodel_Package The package to find. Null if not found.
     */
    private function _getPackageFromPath($path)
    {
        $pkg = &$this->packages->get(0);
        do {
            list($pos, $first, $path) = $this->getPackagePathParts($path);
            if ($first=='')
                $pkgPos = 0;
            else
                $pkgPos = $this->searchIntoSubpackage($pkg, $first);
            if($pkgPos===false)
                return null;
            else
                $pkg = $this->packages->get($pkgPos);
        } while (!($pos===false));
        return $pkg;
    }
    
    /**
     * Launches the resolution of the references for all stacks from the root pkg
     * 
     * Every reference (a temporary string) is replaced by a PHP reference
     * to the corresponding type (that is, a class or a datatype)
     * Must be run once the model is complete (= once PHP parsing is done)
     * 
     * @param array $defPkg List of packages where to look for the orphaned elements
     *                      By default, looks in the root package
     */
    public function resolveAll($defPkg = array())
    {
        $oRoot = &$this->packages->get(0);
        if (empty($defPkg))
            $defPkg = array($oRoot);
        else
            $defPkg[] = &$this->packages->get(0);
        $this->_resolveReferences($oRoot, $defPkg);
    }

}

?>
