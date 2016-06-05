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
 * @subpackage Output
 * @author     Baptiste Autin <ohlesbeauxjours@yahoo.fr> 
 * @license    http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version    SVN: $Revision: 78 $
 * @link       http://pear.php.net/package/PHP_UML
 * @link       http://www.omg.org/mof/
 * @since      $Date: 2008-12-22 20:39:25 +0100 (lun., 22 déc. 2008) $
 *
 */
 
/**
 * Exportation class.
 * Currently, this class reads XMI data, and applies an XSL transformation.
 * Two formats are available: html and php.
 * 
 * @category   PHP
 * @package    PHP_UML
 * @subpackage Output
 * @author     Baptiste Autin <ohlesbeauxjours@yahoo.fr>
 * @license    http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @link       http://pear.php.net/package/PHP_UML
 */
class PHP_UML_Output_Exporter
{
    const APP_NAME         = 'PHP_UML';
    const RESOURCES_FOLDER = 'resources';
    const STARTING_TPL     = 'main.xsl';
    
    /**
     * Generates output data by applying a transformation on a given XMI file
     *
     * @param string $outputDir Output directory
     * @param string $xslFile   XSL file (template file)
     * @param string $xmlFile   XMI file  
     */
    static public function generateFromFile($outputDir, $xslFile, $xmlFile)
    {
        if (is_file($xmlFile) && is_readable($xmlFile)) {
            $xmlDom = new DomDocument;
            $xmlDom->load($xmlFile);
            self::generate($outputDir, $xslFile, $xmlDom);
        } else {
            throw new PHP_UML_Exception(
                'Could not read file '.$xmlFile
            );
        }
    }
    
    /**
     * Generates ouput data by applying a transformation on some provided XMI
     *
     * @param string $outputDir Output directory
     * @param string $xslFile   XSL file (template file)
     * @param string $xmlData   The XMI data
     */
    static public function generateFromXml($outputDir, $xslFile, $xmlData)
    {
        if(empty($xmlData))
            throw new PHP_UML_Exception(
                'No XMI data was available for transformation.'
            );
        $xmlDom = new DomDocument;
        $xmlDom->loadXML($xmlData);
        self::generate($outputDir, $xslFile, $xmlDom);
    }
    
    /**
     * Generates output data by applying a transformation on some provided XMI
     *
     * @param string $outputDir Output folder
     * @param string $format    Desired format (html, php...)
     * @param string $xmlData   XMI code
     */
    static public function generateFromFormat($outputDir, $format, $xmlData)
    {
        $xslFile = dirname(__FILE__).DIRECTORY_SEPARATOR.$format.
             DIRECTORY_SEPARATOR.self::STARTING_TPL;

        if (file_exists($xslFile))
            self::generateFromXml($outputDir, $xslFile, $xmlData);
        else {
            throw new PHP_UML_Exception(
                'Could not find the XSL template file. It must be named '.
                self::STARTING_TPL.', under Format/YourTemplate/'
            );
        }
    }
    
    /**
     * Generates output data by applying a transformation on a Dom Document
     *
     * @param string      $outputDir Output folder
     * @param string      $xslFile   XSL file (template file)
     * @param DomDocument $xmlDom    XMI data (Dom)
     *
     * @return string (possible) messages raised during XSLT
     */
    static public function generate($outputDir, $xslFile, DomDocument $xmlDom)
    {
        clearstatcache();

        $userCurrentDir = getcwd();    // we memorize the current working dir
        chdir(dirname(__FILE__));      // ... swap to the /Output dir  
        if ($xslFile=='')
            $xslFile = 'html/'.self::STARTING_TPL;
        
        if (!(file_exists($xslFile) && is_readable($xslFile)))
            throw new PHP_UML_Exception(
                'Could not read file '.$xslFile
            );    
        $xslDom = new DomDocument;
        $xslDom->load($xslFile);       // ... load the XSLT starting file
        
        chdir($userCurrentDir);        // ... and change back to the user dir
 
        // so that we can check the output dir (counted from the user dir)
        if ($outputDir=='')
            throw new PHP_UML_Exception(
                'No output directory was given.'
            );
        if (!file_exists($outputDir))
            throw new PHP_UML_Exception(
                'Directory '.$outputDir.' does not exist.'
            );
        chdir($outputDir);

        self::copyResources(dirname($xslFile));

        $xslProc = new XSLTProcessor;
        $xslProc->registerPHPFunctions();
        $xslProc->importStylesheet($xslDom);

        $xslProc->setParameter('', 'phpSaveToFile', __CLASS__.'::saveToFile');
        $xslProc->setParameter('', 'phpCreateFolder', __CLASS__.'::createFolder');
        $xslProc->setParameter('', 'appName', self::APP_NAME);
        $xslProc->setParameter('', 'genDate', date('r'));

        return $xslProc->transformToXML($xmlDom);
    }

    const XMLNS_UML1 = 'http://www.omg.org/spec/UML/1.4';
    /**
     * Applies a simple transformation on XML data
     *
     * @param string $transformationName Template name
     * @param string $xmi                XML data
     *
     * @return string XML
     */
    public function transform($transformationName, $xmi)
    {   
        //we must set the xmlns:UML to the same value as the XSLT stylesheet
        //(otherwise transfomations don't work)
        $xmi = preg_replace('/xmlns:(UML)\s*=\s*["\'](.*)["\']/i', 'xmlns:$1="'.self::XMLNS_UML1.'"', $xmi, 1);

        $xslDom = new DomDocument;
        $xslDom->load(dirname(__FILE__).DIRECTORY_SEPARATOR.$transformationName);

        $xmlDom = new DomDocument;
        $xmlDom->loadXML($xmi);
 
        /* $xmiTag = &$xmlDom->getElementsByTagName('XMI')->item(0);
        if ($xmiTag->getAttribute('xmlns:UML') != '') {
            $xmlDom->getElementsByTagName('XMI')->item(0)->setAttribute('verified', 'http://www.omg.org/spec/UML/1.4');
        } */

        $xslProc = new XSLTProcessor;
        $xslProc->importStylesheet($xslDom);

        $xslProc->setParameter('', 'appName', self::APP_NAME);

        return $xslProc->transformToXML($xmlDom);
    }
    
    /**
     * Creates a folder
     *
     * @param string $path Folder name
     */
    static public function createFolder($path)
    {   
        if (substr(getcwd(), -1)==DIRECTORY_SEPARATOR)
            $k = getcwd().utf8_decode($path);
            else
            $k = getcwd().DIRECTORY_SEPARATOR.utf8_decode($path);
        if (!file_exists($k)) {
            mkdir($k);
        }
        chdir($k);
    }
   
    /**
     * Saves a content to a file. Callback function for the XSL templates.
     *
     * @param string $name    File name
     * @param mixed  $content Content (can be either a string, or a node-set)
     */
    static public function saveToFile($name, $content)
    {
        $file = fopen(utf8_decode($name), 'w');
 
        if (is_string($content)) {
            fwrite($file, $content);
        } else {
            $dom  = new DomDocument();
            $node = $dom->importNode($content[0], true);
            $dom->appendChild($node);
    
            fwrite($file, $dom->saveHTML());
        }
        fclose($file);
    }
    
    /**
     * Copy the "resources" folder
     *
     * @param string $path Path to the folder that contains the XSL templates
     */
    static public function copyResources($path)
    {
        $dir = $path.DIRECTORY_SEPARATOR.self::RESOURCES_FOLDER;

        if (file_exists($dir)) {
            $iterator = new DirectoryIterator($dir);
            foreach ($iterator as $file) {
                if($file->isFile())
                    copy($file->getPathname(), $file->getFilename());
            }
        }
    }
}

?>
