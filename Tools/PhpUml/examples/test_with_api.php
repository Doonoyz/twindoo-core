<?php
/**
 * PEAR Package PHP_UML
 *
 * This shows how to generate XMI with the API (without using the parser).
 * It works a bit like the DOM, by attaching elements to each other, with an add() method.
 *
 * This sample defines :
 * - a file called 'Exception.php'
 * - a package called 'PEAR'
 * - a class called PEAR_Exception contained in that package
 * - PEAR_Exception has a private static property called $_observers, and a constructor, taking one parameter ($message)
 * - That class is defined in the file Exception.php
 *
 * @category PHP
 * @package  PHP_UML
 * @author   Baptiste Autin <ohlesbeauxjours@yahoo.fr> 
 * @license  http://www.gnu.org/licenses/lgpl.html LGPL License 3
 *
 */

require 'PHP/UML.php';

$m = new PHP_UML_Metamodel_Superstructure;

$file = new PHP_UML_Metamodel_File;
$file->name = 'Exception.php';
$m->files->add($file);

$pa = new PHP_UML_Metamodel_Parameter;
$pa->name      = 'message';
$pa->type      = 'string';
$pa->direction = 'in';
$m->parameters->add($pa);

$f = new PHP_UML_Metamodel_Operation;
$f->name           = '__construct';
$f->visibility     = 'public';
$f->ownedParameter = array($pa);
$m->operations->add($f);

$a = new PHP_UML_Metamodel_Property;
$a->name           = '$_observers';
$a->visibility     = 'private';
$a->default        = 'array()';
$a->type           = 'array';
$a->isInstantiable = false;

$c = new PHP_UML_Metamodel_Class;
$c->name           = 'PEAR_Exception';
$c->ownedOperation = array($f);
$c->ownedAttribute = array($a);
$c->file           = $file;
$m->classes->add($c);

$p = new PHP_UML_Metamodel_Package;
$p->name      = 'PEAR';
$p->ownedType = array($c);
$m->packages->add($p);

$uml = new PHP_UML;
$uml->model = $m;
$uml->model->resolveAll();

// Result in XMI 1:
$uml->generateXMI(1);
$uml->saveXMI('example1.xmi');

// Result in XMI 2:
$uml->generateXMI(2);
$uml->saveXMI('example2.xmi');

?>
