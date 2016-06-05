<?php 
    /*
    *  $Id: XmlFormatReporter.php 26740 2005-07-15 01:37:10Z hkodungallur $
    *
    *  Copyright(c) 2004-2005, SpikeSource Inc. All Rights Reserved.
    *  Licensed under the Open Source License version 2.1
    *  (See http://www.spikesource.com/license.html)
    */
?>
<?php
    if (!defined("PHPCHECKSTYLE_HOME_DIR")) { 
        define("PHPCHECKSTYLE_HOME_DIR", dirname(__FILE__) . "/../.."); 
        define('PHPCHECKSTYLE_HOME_DIR', dirname(__FILE__) . "/../.."); 
    } 
    
    require_once PHPCHECKSTYLE_HOME_DIR . "/src/reporter/Reporter.php"; 


    /** 
     * Writes the errors into an xml file 
     * Format:
     * ================================ 
     * <phpcheckstyle>
     *    <file name="file1">
     *        <error line="M" message="error message"/>
     *    </file>
     *    <file name="file2">
     *        <error line="X" message="error message"/>
     *        <error line="Y" message="error message"/>
     *    </file>
     *    <file name="file3"/>
     * </phpcheckstyle>
     * ================================ 
     * 
     * @author Hari Kodungallur <hkodungallur@spikesource.com>
     */
    class XmlFormatReporter extends Reporter
    {
        private $document = false;
        private $root = false;
        private $currentElement = false;

        private $rootDir = false;         // path to root of output directory
        private $home = false;            // first overall xml element under 'phpcheckstyle'
        private $path = false;            // file location for display purposes
        private $lineTotal = 0;           // sum lines into 'home' element for xsl ease

        /**
         * Constructor; calls parent's constructor 
         */
        public function __construct($ofile = false, $reldir, $path) 
        {
            parent::__construct($ofile);
            $this->rootDir = $reldir;
            $this->path = $path;
        }

        /** 
         * @see Reporter::start
         * create the document root (<phpcheckstyle>)
         * 
         */
        public function start() 
        {
            $this->_initXml();
        }

        /** 
         * @see Reporter::start
         * add the last element to the tree and save the DOM tree to the 
         * xml file
         * 
         */
        public function stop() 
        {
            $this->_endCurrentElement();
            $this->home->setAttribute("lineTotal", $this->lineTotal);
            $this->document->save($this->outputFile);
        }

        /** 
         * @see Reporter::currentlyProcessing
         * add the previous element to the tree and start a new elemtn
         * for the new file
         * 
         */
        public function currentlyProcessing($phpFile, $name=false) 
        {
            parent::currentlyProcessing($phpFile);
            $this->_endCurrentElement();
            $this->_startNewElement($phpFile, $name);
        }

        /** 
         * @see Reporter::writeError 
         * creates a <error> element for the current doc element
         * 
         */
        public function writeError($line, $message) 
        {
            $e = $this->document->createElement("error");
            $e->setAttribute("line", $line);
            $e->setAttribute("message", $message);
            $this->currentElement->appendChild($e);
        }

        private function _initXml() 
        {
            $this->document = new DomDocument("1.0");
            $this->root = $this->document->createElement('phpcheckstyle');
            $this->document->appendChild($this->root);
            
            // add the path to css+images, as well as path for display purposes
            $this->home = $this->document->createElement("home");
            $this->currentElement = $this->home;
            $this->currentElement->setAttribute("src", $this->rootDir);
            $this->currentElement->setAttribute("path", $this->path);
            $this->_endCurrentElement();
        }

        private function _startNewElement($f, $name=false)
        {
            $this->currentElement = $this->document->createElement("file");
            //add all file info desired for stats here

            //if fake name provided display it, else lengthy filename
            if ($name) {                 
                $this->currentElement->setAttribute("name", $name);
            } else { 
                $this->currentElement->setAttribute("name", $f);
            }

            $lines = count(file($f));
            $this->currentElement->setAttribute("lines", $lines);
            $this->lineTotal += $lines;
        }

        private function _endCurrentElement()
        {
            if ($this->currentElement) {
                $this->root->appendChild($this->currentElement);
            }
        }
    }
?>
