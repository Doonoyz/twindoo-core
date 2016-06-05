<?php
/**
 * PHP_UML (PHP/MOF program elements classes)
 *
 * PHP version 5
 * 
 * @category   PHP
 * @package    PHP_UML
 * @subpackage Metamodel
 * @author     Baptiste Autin <ohlesbeauxjours@yahoo.fr> 
 * @license    http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version    SVN: $Revision: 97 $
 * @link       http://pear.php.net/package/PHP_UML
 * @link       http://www.omg.org/mof/
 * @since      $Date: 2009-01-04 21:57:08 +0100 (dim., 04 janv. 2009) $
 *
 */

/**
 * A superstructure to gather program elements
 * A PHP_UML_Metamodel_Superstructure object reflects the structure of 
 * some existing PHP code (like the one parsed by the parser), in termes of
 * packages, classes, functions, etc.
 * Once such an object is built, we just need to "serialize" it to get the XMI code.
 * This is the job of PHP_UML->generateXMI()
 * 
 * Normally, such a PHP_UML_Metamodel_Superstructure object is built and passed
 * to the PHP_UML_PHP_Parser by an instance of PHP_UML.
 * 
 * @category   PHP
 * @package    PHP_UML
 * @subpackage Metamodel
 * @author     Baptiste Autin <ohlesbeauxjours@yahoo.fr> 
 * @license    http://www.gnu.org/licenses/lgpl.html LGPL License 3
 */
class PHP_UML_Metamodel_Superstructure
{
    /**
     * The root package (a UML Model)
     *
     * @var PHP_UML_Metamodel_Package
     */
    public $packages;
    
    /**
     * The root package (= the physical folder)
     *
     * @var PHP_UML_Metamodel_Package
     */    
    public $deploymentPackages;
    
    /**
     * Stack of all stereotypes
     * TODO: when real stereotypes will be implemented, deleting that array, and
     * reading the stereotypes from the $packages instead
     *
     * @var array
     */
    public $stereotypes = array();

    /**
     * Constructor
     *
     */
    public function __construct()
    {
    }

    /**
     * Adds the internal PHP datatypes, classes, interfaces...
     * To be completed...
     *
     * @param PHP_UML_Metamodel_Package &$package Base package
     */
    public function addInternalPhpTypes(PHP_UML_Metamodel_Package &$package)
    {
        foreach (PHP_UML_Metamodel_Enumeration::$datatypes as $d) {
            $type       = new PHP_UML_Metamodel_Type;
            $type->id   = PHP_UML_SimpleUID::getUID();
            $type->name = $d;

            $package->ownedType[] = $type;
        }
        foreach (PHP_UML_Metamodel_Enumeration::$interfaces as $i) {
            $type       = new PHP_UML_Metamodel_Interface;
            $type->id   = PHP_UML_SimpleUID::getUID();
            $type->name = $i;

            $package->ownedType[] = $type;
        }
        foreach (PHP_UML_Metamodel_Enumeration::$classes as $c) {
            $type       = new PHP_UML_Metamodel_Class;
            $type->id   = PHP_UML_SimpleUID::getUID();
            $type->name = $c;

            $package->ownedType[] = $type;
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
     * @param PHP_UML_Metamodel_Package &$ns    Package to resolve the elements of
     * @param array                     &$_oDef Default packages to look for
     *                                          orphaned elements
     */
    private function resolveReferences(PHP_UML_Metamodel_Package &$ns, array &$_oDef)
    {
        if (!is_null($ns->nestedPackage)) {
            foreach ($ns->nestedPackage as &$pkg) {
                $this->resolveReferences($pkg, $_oDef);
            }
        }
        if (!is_null($ns->ownedType))
        foreach ($ns->ownedType as &$elt) {
            if (isset($elt->superClass) && !is_null($elt->superClass)) { 
                foreach ($elt->superClass as &$className) {
                    $this->resolveType($ns, $className, $_oDef, $elt);
                }
            }
            if (isset($elt->implements) && !is_null($elt->implements)) { 
                foreach ($elt->implements as &$className) {
                    $this->resolveType($ns, $className, $_oDef, $elt);
                }
            }
            if (isset($elt->ownedOperation)) {
                foreach ($elt->ownedOperation as &$function) {
                    foreach ($function->ownedParameter as &$parameter) {
                        $this->resolveType($ns, $parameter->type, $_oDef, $elt); 
                    }
                }
            }
            if (isset($elt->ownedAttribute)) {
                if (isset($elt->ownedAttribute)) {
                    foreach ($elt->ownedAttribute as &$attribute) { 
                        $this->resolveType($ns, $attribute->type, $_oDef, $elt);
                    }
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
            if (strcasecmp($o->name, $value)==0) {
                return $o;
            }
        }
        return false;
    }

    /**
     * Retrieves the stereotype (named $name) associated to the element $element
     * If not found, returns null.
     *
     * @param PHP_UML_Metamodel_NamedElement &$element       Related object
     * @param string                         $profileName    Profile name
     * @param string                         $stereotypeName Stereotype name
     * 
     * @return PHP_UML_Metamodel_Stereotype
     */
    public function getStereotype(PHP_UML_Metamodel_NamedElement &$element, $profileName, $stereotypeName)
    {
        foreach ($this->stereotypes->getIterator() as $s) {
            if ($s->element == $element && $s->name == $stereotypeName && $s->profile == $profileName) {
                return $s;
            }
        }
        return null;
    }

    /**
     * Creates a stereotype in a given profile, and binds it to a given element
     * Returns the stereotype that was created
     * 
     * @param PHP_UML_Metamodel_NamedElement &$element       The element
     * @param string                         $profileName    The profile name
     * @param string                         $stereotypeName The stereotype name
     * 
     * @return PHP_UML_Metamodel_Stereotype
     */
    public function createStereotype(PHP_UML_Metamodel_NamedElement &$element, $profileName, $stereotypeName)
    {
        $stereotype = new PHP_UML_Metamodel_Stereotype;

        $stereotype->profile = $profileName;
        $stereotype->name    = $stereotypeName;
        $stereotype->element = $element;
        $this->stereotypes[] = $stereotype;
        return $stereotype;
    }
    
    /**
     * Retrieves a particular tag in a given stereotype
     *
     * @param PHP_UML_Metamodel_Stereotype $s       The stereotype
     * @param string                       $tagName The tag name ("description", e.g)
     * 
     * @return PHP_UML_Metamodel_Tag
     */
    public static function getStereotypeTag(PHP_UML_Metamodel_Stereotype $s, $tagName)
    {
        if (!empty($s)) {
            foreach ($s->ownedAttribute as $tag) {
                if ($tag->name == $tagName)
                    return $tag;
            }
        }
        return null;
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
        foreach ($np->nestedPackage as $pkg) {
            if (strcasecmp($pkg->name, $value)==0) {
                return $pkg;
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
    private function resolveType(PHP_UML_Metamodel_Package &$pkg, &$element, array &$pkgDef, PHP_UML_Metamodel_Type &$context)
    {
        // Is there a ns separator (\) in it ?
        list($pos, $first, $last) = $this->getPackagePathParts($element, false);
        if (!($pos===false)) {
            $tmpPkg = &$this->getPackageFromPath($first);
            if ($tmpPkg===false) {
                $this->resolutionWarning($element, $context->file->name);
                $element = null;
            } else {
                // Do we know that type?
                $_o = $this->searchTypeIntoPackage($tmpPkg, $last);
                if (!($_o===false)) {
                    $element = $_o;
                } else {
                    $this->resolutionWarning($element, $context->file->name);
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
                    $this->resolutionWarning($element, $context->file->name);
                    $element = null;
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
    private function resolutionWarning($element, $file)
    {
        PHP_UML_Warning::add('Could not resolve '.$element.' in '.$file);
    }
 
    /**
     * Splits a package path into its first/last element, and the rest
     * Allows for the two different versions of package delimiter
     * 
     * @param string $path      A path to split
     * @param bool   $modeFirst If true, splits into 1st and the rest
     *                          If false, splits into last and the rest
     * @param string $alt       Alternate separator (eg, the directory separator)
     *
     * @return array Results array
     */
    public function getPackagePathParts($path, $modeFirst = true, $alt=PHP_UML_PHP_Parser::T_NS_SEPARATOR2)
    {
        $first = '';
        $last  = '';
        if ($modeFirst) {
            $pos1 = strpos($path, PHP_UML_PHP_Parser::T_NS_SEPARATOR);
            $pos2 = strpos($path, $alt);
            if ($pos1!==false && ($pos1<$pos2 || $pos2===false)) {
                $pos = $pos1;
                $len = strlen(PHP_UML_PHP_Parser::T_NS_SEPARATOR);
            } else {
                $pos = $pos2;
                $len = strlen($alt);
            }
        } else {
            $pos1 = strrpos($path, PHP_UML_PHP_Parser::T_NS_SEPARATOR);
            $pos2 = strrpos($path, $alt);
            if ($pos1!==false && ($pos1>$pos2 || $pos2===false)) {
                $pos = $pos1;
                $len = strlen(PHP_UML_PHP_Parser::T_NS_SEPARATOR);
            } else {
                $pos = $pos2;
                $len = strlen($alt);
            }
        }
        if ($pos===false)
            $first = $path;
        else  {
            $first = substr($path, 0, $pos);
            $last  = substr($path, $pos+$len);
        }
        return array($pos, $first, $last);
    }
    
    /**
     * Retrieve the PHP_UML_Metamodel_Package object related to a package path
     * (ie, to a qualified name, like A\B\C). 
     * Relies on the model->$packages, when references are still named
     * (= before their resolution)
     * 
     * @param string $path The path to find
     * 
     * @return PHP_UML_Metamodel_Package The package to find. Null if not found.
     */
    private function getPackageFromPath($path)
    {
        $pkg = $this->packages;
        do {
            list($pos, $first, $path) = $this->getPackagePathParts($path);
            if ($first!='')
                $pkg = $this->searchIntoSubpackage($pkg, $first);
            if ($pkg===false)
                return false;
        } while (!($pos===false));
        return $pkg;
    }
    
    /**
     * Launch the resolution of the references for all stacks from the root pkg
     * 
     * Every reference (a temporary string) is replaced by a PHP reference
     * to the corresponding type (that is, a class or a datatype)
     * Must be run once the model is complete (= once PHP parsing is done)
     * 
     * @param array $defPkg Array of PHP_UML_Metamodel_Package where to look into,
     *                      in order to resolve the orphaned elements.
     *                      By default, it will look in the root package. This is,
     *                      by the way, where the PHP datatypes are.
     */
    public function resolveAll($defPkg = array())
    {
        if (empty($defPkg))
            $defPkg = array($this->packages);
        else
            $defPkg[] = &$this->packages;
        $this->resolveReferences($this->packages, $defPkg);
    }
}
?>
