<?php
/**
 * Example file for PEAR PHP_UML
 *
 * See also the screen captures obtained with those examples in various UML softwares.
 *
 * BEWARE : test_example2.xmi is generated in XMI version 2, so you can import it only
 * in a UML tool compatible with the version 2 of XMI (like Bouml)
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_UML
 * @author   Baptiste Autin <ohlesbeauxjours@yahoo.fr>
 *
 */

error_reporting(E_ALL);
require_once 'PHP/UML.php';


/**
 * Example 1 : Basic example with file "test.php", in UML/XMI version 1
 */

$t = new PHP_UML;
$t->setFilePatterns('*');

$t->parseFile('test1.php.txt', 'global');   // We parse the file "test.php", and the default namespace is 'global'
$t->generateXMI(1);                         // We generate XMI/UML in version 1
$t->saveXMI('test_example1.xmi');           // We save the XMI/UML code



/**
 * Example 2 : Advanced example in UML/XMI version 2
 */

$t = new PHP_UML;

$t->dollar             = false;    // We don't keep the $ before the variable names
$t->componentView      = false;    // We don't want a component view to be included
$t->deploymentView     = false;    // We don't want a deployment view (artifacts+folders)
$t->docblocks          = true;     // We want the parser to look into the class/file comments

$t->setFiles('test1.php.txt, test2.php.txt');   // We want only "test1.php.txt" and "test2.php.txt"
$t->parse('testModel');                         // The UML model will be named "testModel"
$t->generateXMI(2, 'utf-8');                    // We generate XMI/UML in version 2
$t->saveXMI('test_example2.xmi');               // We save the XMI/UML code in a file
echo htmlentities($t->XMI);                     // And we echo it.


/**
 * Example 3 : Example with a whole directory, in UML/XMI version 1
 */
 
$t = new PHP_UML;

$t->setFilePatterns(array('*.php', '*.txt'));    // We want to parse every file with an extension "php" or "txt"
$t->setIgnorePatterns('.svn');                   // We want all directories to be scanned, except the ".svn"

$t->setDirectories('./');                        // ... in the current directory
$t->parse();
$t->generateXMI(1, 'iso-8859-1');
$t->saveXMI('test_example3.xmi');
     
echo '<br/>Done !!';

?>
