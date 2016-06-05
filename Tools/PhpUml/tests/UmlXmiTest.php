<?php
/**
 * Main test unit
 * 
 * USAGE:
 * - chdir to the current directory
 * - run on the command line : phpunit UmlXmiTest
 * 
 * (you might need to redirect the output to a file, if some errors occur in long
 * XMI files...)
 * 
 * UmlXmiTest compares globally (all cases at once) the XMI that PHP_UML
 * (along with PHP_UML_XMI_Builder) has generated, against the "correct" original
 * XMI files (in XMI version 1, and in XMI version 2). 
 * If new features are added to the XMI builder, those two "correct" versions may
 * no longer be correct, and they then should be updated. This is the aim of
 * rebuildExpectedObjects(). Only a maintainer of the package should run that
 * method though.
 * 
 * Tip: the "current" xmi files are created on the disc (as "new_globalX.xmi")
 * so that you can compare them with the original ones (data-providers/globalX.xmi)
 * if they turned out to be different.
 * 
 * PHP version 5
 * 
 * @category PHP
 * @package  PHP_UML::tests
 * @author   Baptiste Autin <ohlesbeauxjours@yahoo.fr>
 * @license  http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version  SVN: $Revision: 97 $
 * @link     http://www.phpunit.de/
 * 
 */

error_reporting(E_ALL);

require_once 'PHPUnit/Framework.php';
require_once 'PHP/UML.php';

/**
 * Test unit class for the XMI generation
 *
 * @category PHP
 * @package  PHP_UML::tests
 * @author   Baptiste Autin <ohlesbeauxjours@yahoo.fr>
 */
class UmlXmiTest extends PHPUnit_Framework_TestCase
{
    const SUITE_DIR     = './suite/';
    const PROVIDERS_DIR = 'data-providers/';
    
    static public $IGNORED_DIR = array('.svn');
   
    /**
     * Provides the data for the "all at once" model check
     *
     * @return array
     */
    static public function providerXMIGlobal()
    {
        $t = self::getPhpUmlObject();

        $data = array();

        $t->parseDirectory(self::SUITE_DIR);
        $t->generateXMI(1);
        // We are saving what was generated (for later manual check, if needed)
        $t->saveXMI('new_global1.xmi');

        // Then let's compare the content of global1.xmi, with the
        // XMI code we have just generated
        $data[] = array(
            file_get_contents(
                self::SUITE_DIR.self::PROVIDERS_DIR.'global1.xmi'
            ),
            $t->getXMI(), 'XMI version 1'
        );

        // Same with XMI version 2:
        $t->generateXMI(2);
        $t->saveXMI('new_global2.xmi');
        $data[] = array(
            file_get_contents(self::SUITE_DIR.self::PROVIDERS_DIR.'global2.xmi'),
            $t->getXMI(), 'XMI version 2'
        );

        return $data;
    }
    
    /**
     * Checks the XMI files globally (all bug cases at once)
     * 
     * @param mixed  $expected Expected element
     * @param mixed  $actual   Current element
     * @param string $msg      Message
     * 
     * @dataProvider providerXMIGlobal
     */
    public function testXMIGlobal($expected, $actual, $msg)
    {
        $this->assertXmlStringEqualsXmlString($expected, $actual, 'Difference in '.$msg);
    }
    
    /**
     * Rebuilds the set of original objects (stored in data-providers).
     * You should not need to run it. If you do so, run it with a
     * trusted version of UML.
     */
    static public function rebuildExpectedObjects()
    {
        // Individual check (each PHP file one by one)
        $t = self::getPhpUmlObject();
        foreach (new DirectoryIterator(self::SUITE_DIR) as $file) {
            if (!$file->isDot() && !$file->isDir()) {
                $filename = $file->getFilename();
                $t->parseFile(self::SUITE_DIR.$filename);
                $str = serialize($t->model);
                $ptr = fopen(
                    self::SUITE_DIR.self::PROVIDERS_DIR.$filename.'.obj', 'wb'
                );
                fwrite($ptr, $str);
                fclose($ptr);
            }
        }
 
        // Global check (the two XMI files)
        $t = self::getPhpUmlObject();
        $t->parseDirectory(self::SUITE_DIR);

        $t->generateXMI(1);
        $t->saveXMI(self::SUITE_DIR.self::PROVIDERS_DIR.'global1.xmi');
        $t->generateXMI(2);
        $t->saveXMI(self::SUITE_DIR.self::PROVIDERS_DIR.'global2.xmi');
        
        // used by UmlParserTest::providerModelGlobal():
        $str = serialize($t->model);
        $ptr = fopen(self::SUITE_DIR.self::PROVIDERS_DIR.'global.obj', 'wb');
        fwrite($ptr, $str);
        fclose($ptr);
 
    }

    /**
     * Return a PHP_UML object to test
     *
     * @return PHP_UML
     */
    static function getPhpUmlObject()
    {
        PHP_UML_SimpleUID::$deterministic = true;
        PHP_UML_SimpleUID::reset();
        $t = new PHP_UML();
        $t->setIgnorePatterns(self::$IGNORED_DIR);
        $t->docblocks      = true;
        $t->dollar         = false;
        $t->componentView  = false;
        $t->deploymentView = true;
        return $t;
    }
}

//UmlXmiTest::rebuildExpectedObjects();
?>