<?php
/**
 * PHP_UML (PHP_UML_XMI_AbstractBuilder)
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_UML
 * @author   Baptiste Autin <ohlesbeauxjours@yahoo.fr> 
 * @license  http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version  SVN: $Revision: 97 $
 * @link     http://pear.php.net/package/PHP_UML
 * @since    $Date: 2009-01-04 21:57:08 +0100 (dim., 04 janv. 2009) $
 */

/**
 * Abstract class to generate UML elements in XMI code.
 * 
 * To deal with the two different versions of XMI (1.4 and 2.1), you must use one of
 * the two specialized versions:
 * PHP_UML_XMI_BuilderImpl1, or PHP_UML_XMI_BuilderImpl2
 *
 * @category   PHP
 * @package    PHP_UML
 * @subpackage XMI
 * @author     Baptiste Autin <ohlesbeauxjours@yahoo.fr> 
 * @license    http://www.gnu.org/licenses/lgpl.html LGPL License 3
 */
abstract class PHP_UML_XMI_AbstractBuilder implements PHP_UML_XMI_Builder
{
    const EXPORTER_NAME = 'PEAR\PHP_UML';
    const PHP_FILE      = 'PHP File';

    static public $stereotypes = array('File', self::PHP_FILE);
    static public $extensions  = array(''=>'File', 'php'=>self::PHP_FILE);

    protected $xmlEncoding;
    
    /**
     * Generates an ID for an element. A partial identifier can be provided
     * (used for classes and their idrefs)
     *
     * @param string $prefix Prefix
     * 
     * @return string ID
     */
    static public function getUID($prefix = null)
    {
        if (is_null($prefix))
            return PHP_UML_SimpleUID::getUID();
        else
            return md5(self::EXPORTER_NAME.'#'.$prefix);
    }

    /**
     * Gets an XML header for the XMI file
     *
     * @return string
     */
    public function getXmlHeader()
    {
        return '<?xml version="1.0" encoding="'.$this->xmlEncoding.'"?>'; 
    }

    /**
     * Factory method. Retrieves a proper implementation class,
     * matching the XMI version.
     *
     * @param float  $version     XMI version
     * @param string $xmlEncoding XML encoding
     * 
     * @return PHP_UML_XMI_Builder An XMI builder object 
     */
    static function factory($version, $xmlEncoding)
    {
        if ($version < 2)
            return new PHP_UML_XMI_BuilderImpl1($xmlEncoding);
        else
            return new PHP_UML_XMI_BuilderImpl2($xmlEncoding);
    }
    
    /**
     * Constructor
     *
     * @param string $xmlEncoding XML encoding of the file
     */
    public function __construct($xmlEncoding)
    {
        $this->xmlEncoding = $xmlEncoding;
    }
    

    /**
     * Get all packages, recursively, with all the objects they contain
     * Initially called by PHP_UML->generateXMI() on the root package
     * 
     * @param PHP_UML_Metamodel_Package $package Base package
     * 
     * @return string XMI code
     */
    public function getAllPackages(PHP_UML_Metamodel_Package $package)
    {
        $str = $this->getPackageOpen($package);
        if (isset($package->description)) {
            $str .= $this->getComment($package->description, $package->id);
        }
        $str .= $this->getNamespaceOpen();

        $str .= $this->getOwnedTypes($package);

        foreach ($package->nestedPackage as $pkg)
            $str .= $this->getAllPackages($pkg, false);

        $str .= $this->getNamespaceClose();
        $str .= $this->getPackageClose();
       
        return $str;
    }
    
    /**
     * Get the different types owned by  package
     *
     * @param PHP_UML_Metamodel_Package $package Package to get the types of
     * 
     * @return string XMI code
     */
    public function getOwnedTypes(PHP_UML_Metamodel_Package $package)
    {
        $str = '';
        foreach ($package->ownedType as &$elt) {
            switch(get_class($elt)) {
            case 'PHP_UML_Metamodel_Interface':
                $str .= $this->getInterface($elt);
                break;
            case 'PHP_UML_Metamodel_Type':
                $str .= $this->getDatatype($elt);
                break;
            case 'PHP_UML_Metamodel_File':
                $str .= $this->getArtifact($elt, $elt->manifested);
                break;
            default:
                $str .= $this->getClass($elt);
            }
        }
        return $str;
    }

    /**
     * Get all components, with its provided classes
     * PHP_UML considers each logical package as a component, and each owned class
     * as a provided class.
     *
     * @param PHP_UML_Metamodel_Package $package Package to map to a component
     * 
     * @return string XMI code
     */
    public function getAllComponents(PHP_UML_Metamodel_Package $package)
    {
        $str      = '';
        $cv       = new PHP_UML_Metamodel_Package;
        $cv->id   = self::getUID();
        $cv->name = $package->name;

        $classes = array();
        foreach ($package->ownedType as &$elt) {
            $classes[] = $elt;
        }
        $str .= $this->getComponentOpen($cv, $classes, array());

        foreach ($package->nestedPackage as $pkg)
            $str .= $this->getAllComponents($pkg);

        $str .= $this->getComponentClose();
        return $str;
    }
}
?>
