<?php
/**
 * PHP_UML (MOF Program elements classes)
 *
 * PHP version 5
 *
 * This is a PHP implementation of the EMOF Metamodel.
 * It might be filled out, but it is enough for representing
 * the object concepts that we need for our XMI generator.
 * It conforms to most of the EMOF standard (only handling of interfaces is 
 * specialized).
 * 
 * For more information on language metamodels, see :
 * - The OMG website, and its MOF standard
 * - Ceejay, a Java/C++ Code Generation Metamodel for ULF-Ware (M. Piefel)
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

abstract class PHP_UML_Metamodel_NamedElement
{
    public $name;
}

class PHP_UML_Metamodel_Type extends PHP_UML_Metamodel_NamedElement
{
}

class PHP_UML_Metamodel_TypedElement extends PHP_UML_Metamodel_NamedElement
{
    public $type;
}

class PHP_UML_Metamodel_Interface extends PHP_UML_Metamodel_Type
{
    public $superClass = array();
    public $ownedOperation = array();
    public $file;
    public $package;
}

class PHP_UML_Metamodel_Class extends PHP_UML_Metamodel_Interface
{
    public $ownedAttribute = array();
    public $isAbstract;
    public $isInstantiable;
    public $implements = array();
}

class PHP_UML_Metamodel_Operation extends PHP_UML_Metamodel_NamedElement
{
    public $isAbstract;
    public $isInstantiable;
    public $ownedParameter = array();
    public $class;
    public $visibility;
}

class PHP_UML_Metamodel_Property extends PHP_UML_Metamodel_TypedElement
{
    public $isReadOnly;
    public $isInstantiable;
    public $visibility;
    public $default;
    public $class;
}

class PHP_UML_Metamodel_Parameter extends PHP_UML_Metamodel_TypedElement
{
    public $default;
    public $operation;
    public $direction;
}

class PHP_UML_Metamodel_Package extends PHP_UML_Metamodel_NamedElement
{
    public $nestingPackage;
    public $nestedPackage = array();
    public $ownedType = array();
    public $uri;
}

?>
