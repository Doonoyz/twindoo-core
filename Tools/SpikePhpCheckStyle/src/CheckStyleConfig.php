<?php
    /*
    *  $Id: CheckStyleConfig.php 28215 2005-07-28 02:53:05Z hkodungallur $
    *
    *  Copyright(c) 2004-2005, SpikeSource Inc. All Rights Reserved.
    *  Licensed under the Open Source License version 2.1
    *  (See http://www.spikesource.com/license.html)
    */
?>
<?php 

    if (!defined("PHPCHECKSTYLE_HOME_DIR")) { 
        define("PHPCHECKSTYLE_HOME_DIR", dirname(__FILE__) . "/.."); 
    }
    
    /** 
     * Loads the user specified test configuration 
     * 
     * @author Hari Kodungallur <hkodungallur@spikesource.com>
     * @version $Revision: $
     */
    class CheckStyleConfig
    {
        private $file;

        private $_myConfig = array();
        private $_currentTest = false;

        private $_xmlParser;

        /** 
         * constructor 
         * 
         * @param $file 
         * @return 
         * @access public
         */
        public function __construct($file) 
        {
            $this->file = $file;
            //$this->file = PHPCHECKSTYLE_HOME_DIR . "/config/pear.cfg.xml";
            $this->_xmlParser = xml_parser_create();
            xml_set_object($this->_xmlParser, $this);
            xml_set_element_handler($this->_xmlParser, "_startElement", "_endElement");
            xml_set_character_data_handler($this->_xmlParser, "_gotCdata");
            xml_set_default_handler($this->_xmlParser, "_gotCdata");
        }

        /** 
         * destructor 
         * 
         * @return 
         * @access public
         */
        public function __destruct() 
        {
            xml_parser_free($this->_xmlParser);
        }

        /** 
         * parses the configuration file and stores the values 
         * 
         * @return none
         * @access public
         */
        public function parse() 
        { 
            // example from php.net
            if (!($fp = fopen($this->file, "r"))) {
               die("Could not open XML input file");
            }

            while ($data = fread($fp, 4096)) { 
                if (!xml_parse($this->_xmlParser, $data, feof($fp))) { 
                    $msg = sprintf("Warning: XML error: %s at line %d", 
                        xml_error_string(xml_get_error_code($this->_xmlParser)), 
                        xml_get_current_line_number($this->_xmlParser)); 
                    echo $msg;
                    $_myConfig = array();
                } 
            }
        }

        /** 
         * Checks if the "lineLength" check needs to be done
         * 
         * @return boolean
         * @access public
         */
        public function needLineLengthCheck()
        {
            return $this->_configCheck("lineLength");
        }

        /** 
         * If "lineLength" is to be checked, what is the max line length 
         * that is allowed?
         * 
         * @return max line length
         * @access public
         */
        public function getMaxLineLength() 
        {
            if (($ret = $this->_configCheck("lineLength", "maxLineLength")) === false) {
                $ret = 80;
            } 
            return $ret;
        }

        /** 
         * Are tabs allowed? 
         * 
         * @return boolean
         * @access public
         */
        public function allowTabs()
        {
            return !$this->_configCheck("noTabs");
        }

        /** 
         * Are short php code tags allowed? 
         * 
         * @return boolean
         * @access public
         */
        public function allowShortPhpCodeTag() 
        {
            return !$this->_configCheck("noShortPhpCodeTag");
        }

        /** 
         * Are shell/perl style comments allowed? 
         * 
         * @return boolean
         * @access public
         */
        public function allowShellComments() 
        {
            return !$this->_configCheck("noShellComments");
        }

        /** 
         * Are docblocks needed for classes and functions? 
         * 
         * @return boolean
         * @access public
         */
        public function needDocBlocks() 
        {
            return $this->_configCheck("docBlocks");
        }

        /** 
         * Are docblocks needed for private member functions? 
         * 
         * @return boolean
         * @access public
         */
        public function needDocBlocksPrivateMembers() 
        {
            return !$this->_configCheck("docBlocks", "excludePrivateMembers");
        }

        /** 
         * Are curly brackets required for all control statements even if
         * they are syntactically not required?
         * 
         * @return boolean
         * @access public
         */
        public function csNeedCurly() 
        {
            return $this->_configCheck("controlStructNeedCurly");
        }

        /** 
         * Need to check for the position of open curly bracket for
         * control strutures?
         * 
         * @return boolean
         * @access public
         */
        public function needCsOpenCurlyTests() 
        {
            return $this->_configCheck("controlStructOpenCurly");
        }

        /**
          * What is the allowed position of else?
          *
          * If pos is "nl", then the correct way is:
          *    if (statement)
          *    {
          *      ...
          *      ...
          *    }
          *    else   // or else if
          *
          *
          * If pos is "sl", then the correct way is:
          *    if (statement) {
          *      ...
          *      ...
          *    } else // or else if
          *
          * @return "nl" or "sl"
          * @access public
          */
         public function csElsePos()
         {
             if (($ret = $this->_configCheck("controlStructElse", "position")) === false) {
                 $ret = "sl";
             }
             return $ret;
         }

        /** 
         * If open curly bracket position is to be checked for control
         * structures , what is the allowed position? 
         * 
         * If pos is "nl", then the correct way is:
         *    if (statement)
         *    {
         *      ...
         *      ...
         *    }
         *
         * If pos is "sl", then the correct way is:
         *    if (statement) {
         *      ...
         *      ...
         *    }
         * 
         * @return "nl" or "sl"
         * @access public
         */
        public function csOpenCurlyPos() 
        {
            if (($ret = $this->_configCheck("controlStructOpenCurly", "position")) === false) {
                $ret = "sl";
            } 
            return $ret;
        }

        /** 
         * All operators to be surrounded by whitespaces? 
         * If this is true, then 
         *     $a = b; // good
         *     if ($a < b) // bad 
         *     $a=b; // not good
         *     if ($a< b) // bad
         * 
         * @return boolean
         * @access public
         */
        public function whiteSpaceSurroundsOperators() 
        {
            return $this->_configCheck("whiteSpaceSurroundsOperators");
        }

        /** 
         * Need to check for the position of open curly bracket for
         * function definitions ?
         * 
         * @return boolean
         * @access public
         */
        public function needFuncOpenCurlyTests() 
        {
            return $this->_configCheck("funcDefinitionOpenCurly");
        }

        /** 
         * If open curly bracket position is to be checked for function
         * definitions , what is the allowed position? 
         *
         * If pos is "nl", then the correct way is:
         *    private function ()
         *    {
         *      ...
         *      ...
         *    }
         *
         * If pos is "sl", then the correct way is:
         *    private function() {
         *      ...
         *      ...
         *    }
         * 
         * 
         * @return 
         * @access public
         */
        public function funcOpenCurlyPos() 
        {
            if (($ret = $this->_configCheck("funcDefinitionOpenCurly", "position")) === false) {
                $ret = "nl";
            } 
            return $ret;
        }

        /** 
         * Check for the existence of the given test ($test). And
         * if $prop is not false, check if that test has the given
         * property
         * 
         * 
         * @param $test name of the test
         * @param $prop name of the property of the test (false by default)
         * @return boolean true if test exists and prop, if not false, exists
         *                 for the test; false otherwise
         * @access private
         */
        private function _configCheck($test, $prop = false)
        {
            $ret = false;
            if (array_key_exists($test, $this->_myConfig)) {
                if ($prop) {
                    if (array_key_exists($prop, $this->_myConfig[$test])) {
                        $ret = $this->_myConfig[$test][$prop];
                    }
                } else {
                    $ret = true;
                }
            }
            return $ret;
        }

        /** 
         * SAX function indicating start of an element 
         * Store the TEST and PROPERTY values in an array
         * 
         * @param $parser the parser
         * @param $elem name of element
         * @param $attrs list of attributes of the element
         * @return nothing
         * @access private
         */
        private function _startElement($parser, $elem, $attrs)
        {
            switch ($elem) {
                case 'TEST':
                    $this->_currentTest = $attrs['NAME'];
                    $this->_myConfig[$this->_currentTest] = array();
                    break;

                case 'PROPERTY':
                    $pname = $attrs['NAME'];
                    $pval = true;
                    if (array_key_exists('VALUE', $attrs)) {
                        $pval = $attrs['VALUE'];
                    }
                    $this->_myConfig[$this->_currentTest][$pname] = $pval;
                    break;

                default:
                    break;
            }
        }

        /** 
         * SAX function indicating end of element
         * Currenlty we dont need to do anything here
         * 
         * @param $parser 
         * @param $name 
         * @return 
         * @access private
         */
        private function _endElement($parser, $name)
        {
        }

        /** 
         * SAX function for processing CDATA 
         * Currenlty we dont need to do anything here
         * 
         * @param $parser 
         * @param $name 
         * @return 
         * @access private
         */
        private function _gotCdata($parser, $name)
        {
        }

       /**
         *
         * @return boolean
         * @access public
         */
        public function saControlStmt()
        {
            return $this->_configCheck("spaceAfterControlStmt");
        }

        /**
         *
         * @return boolean
         * @access public
         */
        public function saSemicolon()
        {
            return $this->_configCheck("spaceAfterSemicolon");
        }
 
        /**
         *
         * @return boolean
         * @access public
         */
        public function saFuncNameDefn()
        {
            return $this->_configCheck("spaceAfterFuncNameDefn");
        }
    }

    //$x = new CheckStyleConfig("");
    //$x->parse();
    //$ret = $x->getMaxLineLength();
    //echo $ret;
?>
