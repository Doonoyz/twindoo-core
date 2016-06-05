<?php
    /*
    *  $Id: PHPCheckstyle.php 28215 2005-07-28 02:53:05Z hkodungallur $
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

    //config parameters, error definitions, token parsing, output methods, RESPECTIVELY
    require_once PHPCHECKSTYLE_HOME_DIR . "/src/CheckStyleConfig.php";
    require_once PHPCHECKSTYLE_HOME_DIR . "/src/styleErrors.inc.php";
    require_once PHPCHECKSTYLE_HOME_DIR . "/src/TokenUtils.php";
    require_once PHPCHECKSTYLE_HOME_DIR . "/src/reporter/PlainFormatReporter.php";
    require_once PHPCHECKSTYLE_HOME_DIR . "/src/reporter/XmlFormatReporter.php";


    if (!defined("T_ML_COMMENT")) {
        define("T_ML_COMMENT", T_COMMENT);
    }


    /** 
     * Main Class. Does most of the parsing and processing 
     * 
     * @author Hari Kodungallur <hkodungallur@spikesource.com>
     * @revisor Ashwin Kumar <ashwink3029@gmail.com> 6/21/07
     */
    class PHPCheckstyle 
    {
        // debug
        private $debug_lineiso = false;
        private $debug_ptokens = false;
        private $debug_endblock = false;

        // variables that help control output/translation
        private $outformat = "html";
        private $errorsflag = false;

        // concurrent line parser
        private $line;
        private $buffer;
        private $slineNumber;
        private $endblock;

        // {{{ Variables
        private $lineTokenizer;
        private $validExtensions = array("php", "tpl");

        // variables used while processing control structure
        private $_csLeftBracket = 0;
        private $_fcLeftBracket = 0;
        private $inDoWhile = false;

        private $tok;
        private $text;
        private $token;
        private $prvsToken;
        private $tlineNumber;
        private $prvsLine;

        private $_curLineStr = "";
        private $_inControlStmt = false;
        private $_inFuncStmt = false;
        private $_inFuncCall = false;
        private $_justAfterFuncStmt = false;
        private $_justAfterControlStmt = false;
        private $_constantDef = false;

        // this is used only for do/while processing now... 
        // WARN: the logic for do/while is not right and needs rethinking
        //       and reimplementation
        private $_curControlStmt = false;

       /**
         * @var array   List of the magic methods 
         * @link http://www.php.net/manual/en/language.oop5.magic.php
         */
        private $_specialFunctions = array("__construct", "__destruct", 
          "__call", "__get", "__set", "__isset", "__unset", "__sleep", 
          "__wakeup", "__toString", "__set_state", "__clone", "__autoload"); 

        private $_xsl;
        private $_reporter;               // mandatory output
        private $_stats;                  // unused in "text" output
        private $_config;

        // }}}

        // constructor {{{
        
        /** 
         * Constructor.  
         * 
         * @param $config - where to find config file
         * @access public
         */
        public function __construct($config)
        {
            // load output mechanism
            $this->lineTokenizer = new TokenUtils();

            // load configuration paramaters
            $this->_config = new CheckStyleConfig($config);
            $this->_config->parse();
        }

        // }}}

        // {{{ public function processFiles

        /** 
         * driver function that call processFile repeatedly for each php
         * file that is encountered
         * 
         * @param $src a php file or a directory. in case of directory, it
         *        searches for all the php/tpl files within the directory 
         *        (recursively) and each of those files are processed
         * @param $excludes an array of directories or files that need to be
         *        excluded from processing
         * @param $outformat either "html" or "text" determines which 
         *        Reporter derivative to use, and in the case of "html"
         *        output, note that the _stats will be invoked
         * @param $outdir directory in which to output files
         * @return nothing
         * @access public
         */
        public function processFiles($src, $excludes, $outformat, $outdir)
        {
            $this->outformat = $outformat;

            global $util;
            // if output directory does exist, let's start fresh by deleting 
            // its contents; otherwise, create it
            if (file_exists($outdir)) {
                $util->wipeDir($outdir);
            } else {
                $util->makeDirRecursive($outdir);
            }

            // recursive obtain array of all php files to be checked
            $files = $this->_getAllPhpFiles($src, $excludes);
 
            // text output simply compiles one large list, thus the
            // _stats reporter will not be used - see writeError()
            if ($outformat == "text") {
                $txtOutFile = "style-report.txt";
                $outFile = $outdir . "/" . $txtOutFile;
                $this->_reporter = new PlainFormatReporter($outFile);
                $this->_reporter->start();
                foreach ($files as $file) {
                    if (is_array($file)) {
                        continue;
                    }
                    $this->_reporter->currentlyProcessing($file);
                    $this->_processFile($file);
                }
                $this->_reporter->stop();
            } else {
                // copy the css and images
                $util->copyr(PHPCHECKSTYLE_HOME_DIR . "/html/css", $outdir . "/css");
                $util->copyr(PHPCHECKSTYLE_HOME_DIR . "/html/images", $outdir . "/images");
                
                // setup html translation for the isolated reports
                $this->_xsl = new XSLTProcessor();
                $outFile = $outdir . "/" . "temp_perfile.xml";
                $xslFile = PHPCHECKSTYLE_HOME_DIR . "/html/xsl/perfile.xsl";
                $this->_xsl->importStyleSheet(DOMDocument::load($xslFile));

                // setup reporter for compiled html list/summary
                $this->_stats = new XmlFormatReporter($outdir . "/" . "temp_index.xml", "", $src);
                $this->_stats->start();   
 
                foreach ($files as $file) {
                    if (is_array($file)) {
                        continue;
                    }

                    // for output purposes, the entire path is lengthy for dir creation
                    $relfile = str_replace($src . "/", "", $file);
                    // any need for php output files? - already presumed existence as server module
                    $htmlFile = $outdir . "/" . $relfile . ".html";

                    // a directory tree, similar to that of the original source, will be created
                    // this is for easier navigation
                    $curDir = dirname($htmlFile);
                    if (!file_exists($curDir)) {
                        $util->makeDirRecursive($curDir);
                    }

                    // get relative path to home directory by joining "../" multiple times
                    // this is so that files deep in recreated dir tree can access css
                    $temp = str_replace("/", "", $relfile);     //num dirs traversed
                    $count = strlen($relfile) - strlen($temp);
                    $relpath = str_repeat("../", $count);

                    // setup reporter for individual isolated file reports
                    // provide where to output, path to css/images, and simpler path for display
                    $this->_reporter = new XmlFormatReporter($outFile, $relpath, $relfile);
                    $this->_reporter->start();

                    // begin <file> info in xml file
                    $this->_reporter->currentlyProcessing($file, $relfile);
                    $this->_stats->currentlyProcessing($file, $relfile);
 
                    // the code involving 'errorsflag' prevents creation of isolated 
                    // report files in which there are no errors to report
                    $this->errorsflag = false;
                    $this->_processFile($file);        // produce error messages in xml file
                    if (!$this->errorsflag) {
                        continue;
                    }

                    // halt adding info and transform the xml file into isolated html report
                    $this->_reporter->stop(); 
                    $this->_transformXSL($outFile, $htmlFile);                
                }    
                 
                // halt adding info to overeall list
                $this->_stats->stop();
                // setup html translation for the compiled list
                $xslFile = PHPCHECKSTYLE_HOME_DIR . "/html/xsl/index.xsl";
                $this->_xsl->importStyleSheet(DOMDocument::load($xslFile));
                $this->_transformXSL($outdir . "/" . "temp_index.xml", $outdir . "/" . "index.html");
            }
        }

        // }}}

        // {{{ public function transformXSL

        /**
         * simple function that translates a given xml file into an html
         * file that is saved to the given location
         *
         * @param $xmlfile where to read data from
         * @param $htmlfile where to output translation to
         * @return nothing
         * @access private
         */
        private function _transformXSL($xmlFile, $htmlFile)
        {        
            $hfh = fopen($htmlFile, "w");
            fwrite($hfh, $this->_xsl->transformToXML(DOMDocument::load($xmlFile)));
            fclose($hfh);
        }

        // }}}

        // {{{ public function processFile
        
        /** 
         * Process one php file
         * Parsing led by tokens, followed by string parser
         * 
         * @param $f input file
         * @return  nothing
         * @access private
         */
        private function _processFile($f) 
        {
            //initialize all, PER FILE
            $this->lineTokenizer->reset();
            $this->lineTokenizer->tokenize($f);
            $this->tok = "";
            $this->text = "";
            $this->token = false;
            $this->prvsToken = false;
            $this->tlineNumber = 0;
            $this->prvsLine = 0;
            $this->slineNumber = 1;
            $this->buffer = "";
            $this->line = "";
            $this->endblock = -1;

            $go = true;
            while ($go) {
                $go = $this->_moveToken();

                //Off-By-One-Bug purposes - so as to check last line
                if (!$go) {
                    $this->tlineNumber++;
                }

                if (is_array($this->token)) {
                    list ($this->tok, $this->text) = $this->token;
                    $this->_processToken($this->text, $this->tok);
                } else if (is_string($this->token)) {
                    $this->text = $this->token;
                    $this->_processString($this->text);
                }

                //debug to check some Tokenizer modifications made
                if ($this->debug_ptokens) {
                    $line = 0;
                    $ret = $this->lineTokenizer->peekPrvsValidToken($line);
                    $t = $this->lineTokenizer->extractTokenText($ret);
                    $this->_writeError($this->tlineNumber, "(A)    " . $this->text);
                    $this->_writeError($line, "(B)    " . $t);
                }

                // store arguments for checking default values
                if ($this->_inFuncStmt) {
                    $this->_createFunctionArgumentString();
                }

                /* When the tokens have jumped to the next line, or even multiple lines ahead,
                   the code will step through those tokens to recreate the lines. These lines
                   will be extracted from the buffer, that has continuously been added to as
                   the tokens have been proceeding. Once a line has been isolated, there are 
                   several error checks that exploit the line approach, towards the end of the
                   loop. Utlimately, the lines are continuously extracted till it catches up 
                   to the token parser*/
                if ($this->prvsLine != $this->tlineNumber) {
                    $isHtml = $this->lineTokenizer->checkProvidedToken($this->token,T_INLINE_HTML);
                    if (!$isHtml) {
                        while ($this->slineNumber < $this->tlineNumber) {
                            if ($this->buffer{0} == "\n") {
                                //should  this be replaced by one "continue" for execution speed
                                $this->line = " ";
                                $this->buffer = substr($this->buffer, 1);
                            } else {
                                $this->line = strtok($this->buffer, "\n");
                                $this->buffer = substr($this->buffer, strlen($this->line) + 1);
                            }

                            //debug to check line is grabbed correctly
                            if ($this->debug_lineiso) {
                                $this->_writeError($this->slineNumber, $this->line);
                            }

                            /* Code that is easier to execute when examining an
                               entire line, should be placed in the following code block.*/
                            if ($this->_config->needLineLengthCheck()) {
                                $this->_checkLargeLine();
                            }
                            if ($this->endblock == $this->slineNumber) {
                                $this->_checkEndBlock();
                            }
                            $this->_checkIndentation();

                            $this->slineNumber++;
                        }
                    }
                } 
            }
        }
         // }}}

        // {{{ private function _getAllPhpFiles

        /** 
         * got through a directory recursively and get all the 
         * php (with extension .php and .tpl) files
         * Ignores files or subdirectories that are in the $excludes
         * 
         * @param $src source directory
         * @return an array of files that end with .php or .tpl
         * @access private
         */
        private function _getAllPhpFiles($src, $excludes) 
        {
            $files[] = array();
            if (!is_dir($src)) {
                $files[] = $src;
            } else {
                if ($root = @opendir($src)) { 
                    while ($file = readdir($root)) {
                        if ($file == "." || $file == "..") {
                            continue;
                        } 
                        $fullPath = $src . "/" . $file;
                        $relPath =  substr($fullPath, strlen($src) + 1);
                        if (!in_array($relPath, $excludes)) {
                            if (is_dir($src . "/" . $file)) { 
                                $files = array_merge($files, $this->_getAllPhpFiles($src . "/" . $file, $excludes));
                            } else { 
                                $pathParts = pathinfo($file);
                                if (array_key_exists('extension', $pathParts)) {
                                    if (in_array($pathParts['extension'], 
                                                 $this->validExtensions)) {
                                        $files[] = $src . "/" . $file; 
                                    }
                                } 
                            } 
                        } 
                    } 
                } 
            }
            return $files; 
        }

        // }}}

        // {{{ private function _processString

        /** 
         * Processes a string token 
         * 
         * @param $text the token string
         * @return nothing
         * @access private
         */
        private function _processString($text)
        {
            switch ($text) {
                // "{" signifies beginning of a block. We need to look for 
                // its position when it is a beginning of a control structure
                // or a function definitino
                // if _justAfterControlStmt is set, the "{" is the beginning
                // of a control structure block
                // if _justAfterFuncStmt is set, the "{" is the beginning of 
                // a function definition block
                case "{":
                    if ($this->_justAfterControlStmt) { 
                        if ($this->_config->needCsOpenCurlyTests()) {

                            $msg = sprintf(PHPCHECKSTYLE_SPACE_BEFORE_STRING, "'{'");
                            if (!$this->lineTokenizer->checkProvidedToken($this->prvsToken, T_WHITESPACE)) {
                                $this->_writeError($this->tlineNumber, $msg);
                            }

                            $pos = $this->_config->csOpenCurlyPos();
                            $isPosOk = ($pos == "nl") ? ($this->prvsLine != $this->tlineNumber) :
                                                        ($this->prvsLine == $this->tlineNumber);
                            //if ($this->prvsLine != $this->tlineNumber) {
                            if (!$isPosOk) {
                                $tmp = ($pos == "sl") ? "the previous line." : "a new line.";
                                $msg = sprintf(PHPCHECKSTYLE_LEFT_CURLY_POS, $tmp);
                                $this->_writeError($this->tlineNumber, $msg);
                            }
                        }

                        // WARN: used for a very simple (and wrong!) do/while processing
                        $this->_justAfterControlStmt = false;
                        if ($this->_curControlStmt != "do" 
                               && $this->_curControlStmt != "dowhile") {
                            $this->_curControlStmt = "";
                        }

                    } elseif ($this->_justAfterFuncStmt) {
                        if ($this->_config->needFuncOpenCurlyTests()) {
                            $pos = $this->_config->funcOpenCurlyPos();
                            $isPosOk = ($pos == "nl") ? ($this->prvsLine != $this->tlineNumber) :
                                                        ($this->prvsLine == $this->tlineNumber);
                            if (!$isPosOk) {
                                $tmp = ($pos == "sl") ? "the previous line." : "a new line.";
                                $msg = sprintf(PHPCHECKSTYLE_LEFT_CURLY_POS, $tmp);
                                $this->_writeError($this->tlineNumber, $msg);
                            }
                        }

                        $this->_justAfterFuncStmt = false;
                    } elseif ($this->lineTokenizer->checkToken(NEXT_VALID_TOKEN, T_LNUMBER)) {
                        //what kind of tokens can reside in a {} string reference?
                        if ($this->lineTokenizer->checkToken(NEXT_TOKEN, T_WHITESPACE)) {
                           $msg = sprintf(PHPCHECKSTYLE_NOSPACE_AFTER_STRING, "{");
                           $this->_writeError($this->tlineNumber, $msg);
                        }
                    }
                       

                    break;

                case "}":
                    // "}" signifies the end of a block

                    if ($this->lineTokenizer->checkToken(PRVS_VALID_TOKEN, T_LNUMBER)) {
                        // what kind of tokens can reside in a {} string reference?
                        if ($this->lineTokenizer->checkToken(PRVS_TOKEN, T_WHITESPACE)) {
                            $msg = sprintf(PHPCHECKSTYLE_NOSPACE_BEFORE_STRING, "}");
                            $this->_writeError($this->tlineNumber, $msg);
                        }
                    } else {
                        // force the line parser to pass this particular line to checkEndBlock()
                        $this->endblock = $this->tlineNumber;    
                    }
                   
                    if ($this->lineTokenizer->checkToken(NEXT_VALID_TOKEN, T_ELSE) ||
                        $this->lineTokenizer->checkToken(NEXT_VALID_TOKEN, T_ELSEIF)) {
                        $line = 0;
                        $ntok = $this->lineTokenizer->peekToken(NEXT_VALID_TOKEN, $line);
                        $csText = $this->lineTokenizer->extractTokenText($ntok);
                        if (($this->_config->csElsePos() == 'sl') && ($line != $this->tlineNumber)) {
                            $msg = sprintf(PHPCHECKSTYLE_CS_STMT_ALIGNED_WITH_CURLY, "$csText");
                            $this->_writeError($line, $msg);
                        }
                        if (($this->_config->csElsePos() == 'nl') && ($line == $this->tlineNumber)) {
                            $msg = sprintf(PHPCHECKSTYLE_CS_STMT_ON_NEW_LINE, "$csText");
                            $this->_writeError($line, $msg);
                        }
                    }
                    break;

                case ";":
                    // ";" -> end of statement
                    // we only need to make sure that we are not hitting ":"
                    // before "{" in the case of a control structure, in which
                    // case we have a control structure is not using the curly
                    // brackets
                    if ($this->_justAfterControlStmt) {
                        $this->_justAfterControlStmt = false;

                        // WARN: the following if condition used for a very 
                        // simple (and wrong!) do/while processing
                        if ($this->_curControlStmt == "dowhile") {
                            $this->_curControlStmt == "";
                        } else {
                            if ($this->_config->csNeedCurly()) {
                                $this->_writeError($this->tlineNumber, 
                                          PHPCHECKSTYLE_CS_NO_OPEN_CURLY);
                            }
                        }
                    }

                    if ($this->_config->saSemicolon()) {
                        $this->_checkWhiteSpaceAfter("'$text'");
                    }

                    // ";" should never be preceded by a whitespace
                    if ($this->lineTokenizer->checkToken(PRVS_TOKEN, T_WHITESPACE)) {
                        $this->_writeError($this->tlineNumber, 
                                  PHPCHECKSTYLE_NOSPACE_BEFORE_SEMICOLON);
                    }
                    break;

                case "-":
                    // allow negative numbers to skip space after
                    if ($this->_config->whiteSpaceSurroundsOperators()) {
                        if ($this->lineTokenizer->checkToken(NEXT_TOKEN, T_LNUMBER) ||
                            $this->lineTokenizer->checkToken(NEXT_TOKEN, T_DNUMBER)) {
                            $last_valid = $this->lineTokenizer->peekPrvsValidToken();
                            list ($lvk, $lvv) = $last_valid;
                            if ($lvk !== T_VARIABLE
                                && $lvk !== T_LNUMBER
                                && $lvk !== T_DNUMBER) {
                                $this->_checkWhiteSpaceBefore($text);
                                break;
                            }
                        }
                    }
                case "=": 
                case "<":
                case ">":
                case "+":
                case ".":
                case "/":
                    // operators generally will need to be surrounded by
                    // whitespaces
                    // TODO: I might have missed some operators here
                    if ($this->_config->whiteSpaceSurroundsOperators()) {
                        $this->_checkSurroundingWhiteSpace("'$text'");
                    }
                    break;

                case "(":
                    // the only issue with "(" is generally whether there
                    // should be space after it or not
                    if ($this->_inFuncCall) {
                        $this->_fcLeftBracket += 1;
                    } elseif ($this->_inControlStmt || $this->_inFuncStmt) {
                        $this->_csLeftBracket += 1;
                    }

                    if ($this->lineTokenizer->checkToken(NEXT_TOKEN, T_WHITESPACE)) {
                        $this->_writeError($this->tlineNumber, 
                                  PHPCHECKSTYLE_CS_LEFT_PARANTHESIS);
                    }
                    break;

                case ")":
                    // again the only issue here the space after/before it
                    if ($this->_inFuncCall) {
                        $this->_fcLeftBracket -= 1;
                    } elseif ($this->_inControlStmt || $this->_inFuncStmt) {
                        $this->_csLeftBracket -= 1;
                    }
                    if ($this->_fcLeftBracket == 0) {
                        $this->_inFuncCall = false;
                    }
                    if ($this->_csLeftBracket == 0) {
                        if ($this->_inControlStmt) {
                            $this->_inControlStmt = false;
                            $this->_justAfterControlStmt = true;
                        } elseif ($this->_inFuncStmt) {
                            $this->_inFuncStmt = false;
                            $this->_justAfterFuncStmt = true;
                            $this->_checkFunctionDefaultValueOrdering();
                        }
                    }

                    if ($this->lineTokenizer->checkProvidedToken($this->prvsToken, T_WHITESPACE)) {
                        $this->_writeError($this->tlineNumber, 
                                  PHPCHECKSTYLE_CS_RIGHT_PARANTHESIS);
                    }
                    break; 

                default:
                    break;
            }
        }

        // }}}

        // {{{ private function _processToken

        /** 
         * processes a token that is not a string, that is
         * a token that is a array of a token id (key) and
         * a token text (value)
         * 
         * @param $text the text of the token
         * @param $tok token id
         * @return nothing
         * @access private
         */
        private function _processToken($text, $tok)
        {
            switch ($tok) {
                // in case of a comment see if the comment starts
                // with '#'
                case T_COMMENT:
                    $allow = $this->_config->allowShellComments();
                    if (!$allow) {
                        $s = strpos($text, '#');
                        # this type of comment
                        if ($s === 0) {
                            $this->_writeError($this->tlineNumber, 
                                      PHPCHECKSTYLE_NO_SHELL_COMMENTS);
                        }
                    }
                case T_ML_COMMENT:
                case T_DOC_COMMENT:
                    break;

                // check if shorthand code tags are allowed
                case T_OPEN_TAG:
                    $allow = $this->_config->allowShortPhpCodeTag();
                    if (!$allow) {
                        $s = strpos($text, '<?php');
                        if ($s === false) {
                            $this->_writeError($this->tlineNumber, 
                                      PHPCHECKSTYLE_WRONG_OPEN_TAG);
                        }
                    }

                    $a = false;
                    break;

                // beginning of a control statement
                case T_ELSE:
                case T_ELSEIF:
                case T_DO:
                case T_WHILE:
                case T_IF:
                case T_FOR:
                case T_FOREACH:
                case T_SWITCH:

                    // WARN: the following if conditions used for a very simple 
                    // (and a very wrong!) do/while processing
                    if ($tok == T_DO) {
                        $this->_curControlStmt = "do";
                    } elseif ($tok == T_WHILE && $this->_curControlStmt == "do") {
                        $this->_curControlStmt = "dowhile";
                    } else {
                        $this->_curControlStmt = $text;
                    }

                    $this->_processControlStatement($text);
                    break;

                case T_WHITESPACE:
                    break;

                case T_INLINE_HTML:
                    break;

                // beginning of a function definition
                // check also for existance of docblock
                case T_FUNCTION:
                    $need = $this->_config->needDocBlocks();
                    if ($need) {
                        $this->_checkDocComments();
                    }
                    $this->_processFunctionStatement();
                    break;

                // beginning of a class
                // check also for the existence of a docblock
                case T_CLASS:
                    $need = $this->_config->needDocBlocks();
                    if ($need) {
                        $this->_checkDocComments();
                    }
                    $this->_processClassStatement();
                    break;

                // operators, generally, need to be surrounded by whitespace
                case T_PLUS_EQUAL:
                case T_MINUS_EQUAL:
                case T_MUL_EQUAL:
                case T_DIV_EQUAL:
                case T_CONCAT_EQUAL:
                case T_MOD_EQUAL:
                case T_AND_EQUAL:
                case T_OR_EQUAL:
                case T_XOR_EQUAL:
                case T_SL_EQUAL:
                case T_SR_EQUAL:
                case T_BOOLEAN_OR:
                case T_BOOLEAN_AND:
                case T_IS_EQUAL:
                case T_IS_NOT_EQUAL:
                case T_IS_IDENTICAL:
                case T_IS_NOT_IDENTICAL:
                case T_IS_SMALLER_OR_EQUAL:
                case T_IS_GREATER_OR_EQUAL:
                    if ($this->_config->whiteSpaceSurroundsOperators()) {
                        $this->_checkSurroundingWhiteSpace("'$text'");
                    }
                    break;

                // ASSUMPTION:
                //   that T_STRING followed by "(" is a function call
                //   Actually, I am not sure how good an assumption this is.
                case T_STRING:
                    // check whether this is a function call
                    $this->_processFunctionCall($text);
                    break;

                // found constant definition
                case T_CONSTANT_ENCAPSED_STRING:
                    $this->_checkConstantNaming($text);
                    break;

                default:
                    break;
            }
        }

        // }}}

        // {{{ private function _checkDocComments

        /** 
         * check for the existence of a docBlock for the current token 
         * it calls a utility function in the TokenUtils. See comments
         * in that file
         * 
         * @return nothing
         * @access private
         */
        private function _checkDocComments()
        {
            $pr = $this->_config->needDocBlocksPrivateMembers();
            if (!$this->lineTokenizer->docCommentExistsForCurrentToken($pr)) {
                    $this->_writeError($this->tlineNumber, 
                              PHPCHECKSTYLE_MISSING_DOCBLOCK);
            }
        }

        // }}}

        // {{{ private function _checkConstantNaming

        /** 
         * Checks to see if the constant follows the naming convention
         * Constants should only have uppercase letters and underscores
         * 
         * @param $text the string containing the constant. note that the
         *        string also has the quotes (single or double), so we need 
         *        remove them from the string before testing
         * @return nothing
         * @access private
         */
        private function _checkConstantNaming($text)
        {
            if ($this->_constantDef) { 
                $text = ltrim($text, "\"'"); 
                $text = rtrim($text, "\"'"); 
                $ret = preg_match("/^[A-Z_][A-Z_]*[A-Z_]$/", $text); 
                if (!$ret) { 
                    $this->_writeError($this->tlineNumber, 
                              PHPCHECKSTYLE_CONSTANT_NAMING);
                }

                $this->_constantDef = false;
            }
        }

        // }}}

        // {{{ private function _processFunctionCall

        private function _processFunctionCall($text)
        {
            if ($text == "define") {
                $this->_constantDef = true;
            }

            if ($this->lineTokenizer->checkTokenText(NEXT_VALID_TOKEN, "(")) {
                // ASSUMPTION:
                //   that T_STRING followed by "(" is a function call
                $this->_inFuncCall = true;

                if (!$this->lineTokenizer->checkTokenText(NEXT_TOKEN, "(")) {
                    $this->_writeError($this->tlineNumber, 
                              PHPCHECKSTYLE_FUNCNAME_SPACE_AFTER);
                }
            }
        }

        // }}}

        /*{{{ private function _processControlStatement */

        private function _processControlStatement($csText)
        {
            $this->_inControlStmt = true;

            // first token: if not configured for one whitespace, 
            // it will default to no whitespace
            if ($this->_config->saControlStmt()) {
                if (!$this->lineTokenizer->checkToken(NEXT_TOKEN, T_WHITESPACE, " ")) {
                    $msg = sprintf(PHPCHECKSTYLE_SPACE_AFTER_CS, "$csText");
                    $this->_writeError($this->tlineNumber, $msg);
                }
            } else {
                if ($this->lineTokenizer->checkToken(NEXT_TOKEN, T_WHITESPACE, " ")) {
                    $msg = sprintf(PHPCHECKSTYLE_NOSPACE_AFTER_CS, "$csText");
                    $this->_writeError($this->tlineNumber, $msg);
                }
            }

            // for some control structures like "else" and "do", 
            // there is no statments they will be followed directly by "{"
            if ($csText == "else" || $csText == "do") {
                if ($this->lineTokenizer->checkTokenText(NEXT_VALID_TOKEN, "{")) {
                    $this->_inControlStmt = false;
                    $this->_justAfterControlStmt = true;
                }
            }

            // "else if" is different 
            if ($csText == "else") {
                if ($this->lineTokenizer->checkToken(NEXT_VALID_TOKEN, T_IF)) {
                    // control statement for "else" is done with... new control 
                    // statement "if" is starting
                    $this->_inControlStmt = false;
                }
            }

            // TODO: "else" and "elseif" should start in the same line as "}"
            //   -- now its done in _processString for the "{" string. Ideally,
            // it should be here.

            // TODO: "while" as part of the "do/while" should be in the same 
            // line as "}"; but how do we know whether it is part of "do/while"?
            // Current implementation is kinda forced. It just sets a flag to 
            // see to do the "do" and "while" came in a sequence. This works 
            // only if there is no other "do" or "while" loops within the do/while. 
        }
        
        /*}}}*/

        // {{{ private function _processFunctionStatement

        private function _processFunctionStatement()
        {
            $this->_inFuncStmt = true;

            $isPrivate = false;
            if ($this->lineTokenizer->checkToken(PRVS_VALID_TOKEN, T_PRIVATE)) {
                $isPrivate = true;
            }

            // skip until T_STRING representing the function name
            while (!$this->lineTokenizer->checkProvidedToken($this->token, T_STRING)) {
                $this->_moveToken();
            }

            // function name has to start with lowercase (or underscore
            // followed by lowercase in case of a private function)
            $funcNameRegExp = "/^[a-z]/";
            if ($isPrivate) {
                $funcNameRegExp = "/^_[a-z]/";
            }
            list ($k, $v) = $this->token;

            if (!in_array($v, $this->_specialFunctions)) {
                if (!preg_match($funcNameRegExp, $v)) {
                    if ($isPrivate) {
                        $this->_writeError($this->tlineNumber,
                                  PHPCHECKSTYLE_PRIVATE_FUNCNAME_NAMING);
                    } else {
                        $this->_writeError($this->tlineNumber,
                                  PHPCHECKSTYLE_FUNCNAME_NAMING);
                    }
                }
            }
            // $this->_checkWhiteSpaceAfter("function name");
        }

        // {{{ private function _processClassStatement

        private function _processClassStatement()
        {
            // for the purpose of opening curly bracket "{",
            // class is like a function statement without arguments...
            $this->_justAfterFuncStmt = true;

            // skip until T_STRING representing the class name
            while (!$this->lineTokenizer->checkProvidedToken($this->token, T_STRING)) {
                $this->_moveToken();
            }

            // class name has to start with uppercase
            $funcNameRegExp = "/^[A-Z]/";
            list ($k, $v) = $this->token;
            if (!preg_match($funcNameRegExp, $v)) {
                $this->_writeError($this->tlineNumber, 
                          PHPCHECKSTYLE_CLASSNAME_NAMING);
            }

            $this->_checkWhiteSpaceAfter("class name");
        }

        // }}}

        // {{{ private function _checkSurroundingWhiteSpace

        private function _checkSurroundingWhiteSpace($text)
        {
            $this->_checkWhiteSpaceBefore($text);
            $this->_checkWhiteSpaceAfter($text);
        }

        // }}}

        // {{{ private function _checkWhiteSpaceBefore

        private function _checkWhiteSpaceBefore($text)
        {
            if (!$this->lineTokenizer->checkProvidedToken($this->prvsToken, T_WHITESPACE)) {
                $msg = sprintf(PHPCHECKSTYLE_SPACE_BEFORE_STRING, "$text");
                $this->_writeError($this->tlineNumber, $msg);
            }
        }

        // }}}

        // {{{ private function _checkWhiteSpaceAfter

        private function _checkWhiteSpaceAfter($text)
        {
            if (!$this->lineTokenizer->checkToken(NEXT_TOKEN, T_WHITESPACE)) {
                $msg = sprintf(PHPCHECKSTYLE_SPACE_AFTER_STRING, "$text");
                $this->_writeError($this->tlineNumber, $msg);
            }
        }

        // }}}
        
        // {{{ private function _moveToken

        /**
         * Moves to the next token, by means of the TokenUtil class functions.
         * It also updates this class's token variables. 
         *
         * @return nothing
         * @access private
         */
        private function _moveToken() 
        {
            $this->prvsToken = $this->token;
            $this->prvsLine = $this->tlineNumber;
            $this->token = $this->lineTokenizer->getNextToken($this->tlineNumber);

            $this->buffer .= $this->text;     //maintain buffer for line extraction

            return $this->token;
        }

        // }}}

        /*{{{ private function _checkLargeLine */

        /** 
         * Check if the current line exceeds the maxLineLength allowed.
         * Has been modified to a simple if clause based on the line 
         * that should have previously been loaded.
         * 
         * @return nothing
         * @access private
         */
        private function _checkLargeLine() 
        {
            $llen = $this->_config->getMaxLineLength();
            if (strlen($this->line) > $llen) {
                $msg = sprintf(PHPCHECKSTYLE_LONG_LINE, "$llen");
                $this->_writeError($this->slineNumber, $msg);
            }
        }

        /*}}}*/
        
        /*{{{ private function _checkEndBlock */

        /**
         *
         * @return nothing
         * @access private
         */
        private function _checkEndBlock()
        {
            // debug to see if } is being grabbed appropriately
            if ($this->debug_endblock) {
                $this->_writeError($this->slineNumber, $this->line);
            }
            // if } is not preceded by only whitespace, output an error
            if (!preg_match("/^[\s]*}/", $this->line)) {
                $this->_writeError($this->slineNumber, PHPCHECKSTYLE_END_BLOCK_NEW_LINE);
            } 
        }

        /*}}}*/


        // {{{ private function _checkIndentation

        /** 
         * Checks for tab character throughout pre-loaded line 
         * 
         * Could be modified to also check whether amount of 
         * whitespace is an appropriate multiple of 4 or 3 or 
         * some value. The desired multiple could be some simple
         * mathematical equation based on the number of { versus
         * } at any given point in the code.
         *
         * @return nothing
         * @access private
         */
        private function _checkIndentation() 
        {
            $tallow = $this->_config->allowTabs();
            if (!$tallow) {
                $tabfound = preg_match("/\t/", $this->line);
                if ($tabfound) {
                    $this->_writeError($this->slineNumber, 
                              PHPCHECKSTYLE_TAB_IN_LINE);
                }
            }
        }

        // }}}

        // {{{ private function _createFunctionArgumentString

        private function _createFunctionArgumentString() 
        {
            if ($this->_inFuncStmt && 
                   !$this->lineTokenizer->checkProvidedToken($this->token, T_WHITESPACE)) {
                $this->funcArgString .= $this->lineTokenizer->extractTokenText($this->token);
            }
        }

        // }}}

        /*{{{ private function _checkFunctionDefaultValueOrdering */

        private function _checkFunctionDefaultValueOrdering() 
        {
            $args = strstr($this->funcArgString, "(");
            $tok = strtok($args, ",");
            $foundDefaultValues = false;
            while ($tok) {
                $defaultSpecified = strpos($tok, "=");
                if ($foundDefaultValues && !$defaultSpecified) {
                    $this->_writeError($this->tlineNumber, 
                              PHPCHECKSTYLE_FUNC_DEFAULTVALUE_ORDER);
                    break;
                }
                if ($defaultSpecified) {
                    $foundDefaultValues = true;
                }
                $tok = strtok(",");
            }
            $this->funcArgString = "";
        }

        /*}}}*/

        //{{{ private function _writeError 

        /**
         * This function determines how to output errors. Primarily, it checks
         * if the error flag has already been set; if not, it flags that errors
         * have been made in this file. It must output to the default _reporter.
         * However, in the case of 'html', it also outputs to a second XmlFormatReporter
         * which produces the compiled list/summary. 
         *
         * @param $line line number to output
         * @param $message error message to output
         * @return nothing
         * @access private
         */
        private function _writeError($line, $message)
        {
            if (!$this->errorsflag) {
                 $this->errorsflag = true;
            }
            $this->_reporter->writeError($line, $message);
            if ($this->outformat == "html") {
                $this->_stats->writeError($line, $message);
            }
        }
 
        //}}}
    }
?>
