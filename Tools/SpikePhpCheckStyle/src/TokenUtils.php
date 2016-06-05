<?php
    /*
    *  $Id: TokenUtils.php 28215 2005-07-28 02:53:05Z hkodungallur $
    *
    *  Copyright(c) 2004-2005, SpikeSource Inc. All Rights Reserved.
    *  Licensed under the Open Source License version 2.1
    *  (See http://www.spikesource.com/license.html)
    */
?>
<?php
    if (!defined("T_ML_COMMENT")) {
        define("T_ML_COMMENT", T_COMMENT);
    }

    define("NEXT_TOKEN", "1");
    define("NEXT_VALID_TOKEN", "2");
    define("PRVS_TOKEN", "3");
    define("PRVS_VALID_TOKEN", "4");
    

    /** 
     * Class that stores the tokens for a particular class and provide
     * utility functions like getting the next/previous token,
     * checking whether the token is of particular type etc.
     * 
     * @author Hari Kodungallur <hkodungallur@spikesource.com>
     */
    class TokenUtils
    {
        /*{{{ Variables */

        private $tokens;
        private $totalNumTokens;
        private $nextTokenNumber;
        private $nextLineNumber;

        /*}}}*/

        /*{{{ Constructor */

        /** 
         * constructor 
         * 
         * @return 
         * @access public
         */
        public function __construct() 
        {
            $this->reset();
        }

        /*}}}*/

        /*{{{ public function tokenize */

        /** 
         * Tokenizes the input php file and stores all the tokens in the 
         * $this->tokens variable. 
         * 
         * @param $filename 
         * @return 
         * @access public
         */
        public function tokenize($f) 
        {
            $contents = "";
            if (filesize($f)) {
                $fp = fopen($f, "r");
                $contents = fread($fp, filesize($f));
                fclose($fp);
            }

            $this->tokens = token_get_all($contents);
            $this->totalNumTokens = count($this->tokens);

            return $this->totalNumTokens;
        }

        /*}}}*/

        /*{{{ public function getNextToken */

        /** 
         * Gets the next token; in the process moves the index to the
         * next position, updates the current line number etc
         * 
         * @param &$line = 0 
         * @return the next token
         * @access public
         */
        public function getNextToken(&$line = 0)
        {
            $ret = false;
            if ($this->nextTokenNumber < $this->totalNumTokens) {
                $ret = $this->tokens[$this->nextTokenNumber++];
                $line = $this->nextLineNumber;
                $this->nextLineNumber = $this->_updatedLineNumber($ret);
            }             
            return $ret;
        }

        /*}}}*/

        /*{{{ public function peekNextToken */

        /** 
         * Peeks the next token, i.e., returns the next token without moving 
         * the index. 
         * 
         * @param &$line = 0 
         * @return next token
         * @access public
         */
        public function peekNextToken(&$line = 0)
        {
            $ret = false;
            if ($this->nextTokenNumber <= $this->totalNumTokens) {
                $line = $this->nextLineNumber;
                $ret = $this->tokens[$this->nextTokenNumber];
            }
            return $ret;
        }

        /*}}}*/

        /*{{{ public function peekPrvsToken */

        /** 
         * Peeks at the previous token. That is it returns the previous token
         * without moving the index
         * 
         * @param &$line = 0 
         * @return 
         * @access public
         */
        public function peekPrvsToken(&$line = 0)
        {
            $ret = false;
            if ($this->nextTokenNumber > 1) {
                $line = $this->nextLineNumber
                      - $this->numberOfNewLines($this->tokens[$this->nextTokenNumber - 1])
                      - $this->numberOfNewLines($this->tokens[$this->nextTokenNumber - 2]);
                $ret = $this->tokens[$this->nextTokenNumber - 2];
            }
            return $ret;
        }

        /*}}}*/

        /*{{{ public function peekNextValidToken */

        /** 
         * Peeks at the next valid token. A valid token is one that is not
         * a whitespace or a comment
         * 
         * @param &$line = 0 
         * @return 
         * @access public
         */
        public function peekNextValidToken(&$line = 0) 
        {
            $ret = false;
            $tmpTokenNumber = $this->nextTokenNumber;
            $line = $this->nextLineNumber;
            while ($tmpTokenNumber <= $this->totalNumTokens) {
                $ret = $this->tokens[$tmpTokenNumber++];
                $line += $this->numberOfNewLines($ret);
                if (is_array($ret)) {
                    list ($k, $v) = $ret;
                    if ($k == T_WHITESPACE || $k == T_COMMENT
                        || $k == T_ML_COMMENT || $k == T_DOC_COMMENT) {
                        continue;
                    } else {
                        break;
                    }
                } else {
                    break;
                }
            }
            return $ret;
        }

        /*}}}*/

        /*{{{ public function peekPrvsValidToken */

        /** 
         * Peeks at the previous valid token. A valid token is one that is not
         * a whitespace or a comment
         * 
         * @param &$line = 0 
         * @return 
         * @access public
         */
        public function peekPrvsValidToken(&$line = 0)
        {
            $ret = false;
            $tmpTokenNumber = $this->nextTokenNumber - 2;
            $line = $this->nextLineNumber
                      - $this->numberOfNewLines($this->tokens[$this->nextTokenNumber - 1]);
            while ($tmpTokenNumber > 0) {
                $line -= $this->numberOfNewLines($this->tokens[$tmpTokenNumber]);
                $ret = $this->tokens[$tmpTokenNumber--];
                if (is_array($ret)) {
                    list ($k, $v) = $ret;
                    if ($k == T_WHITESPACE || $k == T_COMMENT
                        || $k == T_ML_COMMENT || $k == T_DOC_COMMENT) {
                        continue;
                    } else {
                        break;
                    }
                } else {
                    break;
                }
            }
            return $ret;
        }

        /*}}}*/

        /*{{{ public function docCommentExistsForCurrentToken */

        /** 
         * Check for the existence of a docblock for the current token
         *  o  go back and find the previous token that is not a whitespace
         *  o  if it is a access specifier (private, public etc), then
         *     see if private members are excluded from comment check
         *     (input argument specified this). if we find an access
         *     specifier move on to find the next best token
         *  o  if the found token is a T_DOC_COMMENT, then we have a docblock
         * 
         * This, of course, assumes that the function or the class has to be
         * immediately preceded by docblock. Even regular comments are not 
         * allowed, which I think is okay.
         * 
         * @param $pr true/false specifying whether private members are
         *        excluded from test
         * @return boolean true: found docblock; false: did not find docblock
         * @access public
         */
        public function docCommentExistsForCurrentToken($pr) 
        {
            // current token = the token after T_CLASS or T_FUNCTION
            //
            // token positions:
            //  .  curToken - 1 = T_CLASS/T_FUNCTION
            //  .  curToken - 2 = whitespace before T_CLASS/T_FUNCTION
            //  .  curToken - 3 = T_ABSTRACT/T_PUBLIC/T_PROTECTED/T_PRIVATE
            //                    or T_DOC_COMMENT, if it is present
            //
            // ISSUE: Assumes that there is no token between T_PUBLIC/etc..
            // and the T_CLASS/T_FUNCTION
            // eg., 
            //    public /* some comment */ MyClassName
            //    { ...
            //
            // will not work.

            $ret = false;

            $tmpTokenNumber = $this->nextTokenNumber - 3;
            $curTok = $this->tokens[$this->nextTokenNumber - 1];

            // if we find T_ABSTRACT, skip token and the whitespace
            // before it
            $ptok = $this->tokens[$tmpTokenNumber];
            if ($this->checkProvidedToken($ptok, T_ABSTRACT)) {
                $tmpTokenNumber -= 2;
            }

            // if we find T_STATIC, skip token and the whitespace
            // before it
            $ptok = $this->tokens[$tmpTokenNumber];
            if ($this->checkProvidedToken($ptok, T_STATIC)) {
                $tmpTokenNumber -= 2;
            }

            if (is_array($curTok)) {
                list ($k, $v) = $curTok;
                if ($k == T_CLASS || $k == T_FUNCTION) {
                    // check for the existence of T_PUBLIC/T_PRIVATE/T_PROTECTED
                    // if found skip them too
                    if ($k == T_FUNCTION) {
                        $ptok = $this->tokens[$tmpTokenNumber];
                        if (is_array($ptok)) {
                            list ($k1, $v1) = $ptok;
                            if ($k1 == T_PUBLIC || $k1 == T_PROTECTED) {
                                $tmpTokenNumber--;
                            } elseif ($k1 == T_PRIVATE) {
                                if (!$pr) {
                                    return true;
                                }
                                $tmpTokenNumber--;
                            }
                        }
                    }

                    // check of the previous valid token is a T_DOC_COMMENT
                    // ignore whitespaces and comments in between
                    while ($tmpTokenNumber > 0) {
                        $ret = $this->tokens[$tmpTokenNumber--];

                        if (is_array($ret)) { 
                            list ($k2, $v2) = $ret; 
                            if ($k2 == T_DOC_COMMENT) {
                                break;
                            } elseif ($k2 == T_WHITESPACE || $k2 == T_COMMENT 
                                  || $k2 == T_ML_COMMENT) {
                                continue;
                            } else {
                                $ret = false;
                                break;
                            }
                        } else {
                            $ret = false;
                            break;
                        }
                    }
                }
            }
            return $ret;
        }

        /*}}}*/

        /*{{{ public function reset */

        /** 
         * Resets all local variables 
         * 
         * @return 
         * @access public
         */
        public function reset() 
        {
            $this->nextTokenNumber = 0;
            $this->nextLineNumber = 1;
            $this->totalNumTokens = 0;
            $this->tokens = array();
            $this->currentLine = "";
        }

        /*}}}*/

        /*{{{ private function _updatedLineNumber */

        // based on the example at http://us4.php.net/token_get_all
        private function _updatedLineNumber($t)
        {
            $ret = $this->nextLineNumber;
            $numNewLines = $this->numberOfNewLines($t);
            if (1 <= $numNewLines) {
               $ret += $numNewLines;
            }

            return $ret;
        }

        /*}}}*/

       /*{{{ public function numberOfNewLines */

        public function numberOfNewLines($t) 
        {
            //use extractTokenText()?
            if (is_array($t)) {
                list ($k, $v) = $t;
            } else {
                $v = $t;
            }
            $numNewLines = substr_count($v, "\n");
            return $numNewLines;
        }

        /*}}}*/
        
        /*{{{ public function checkProvidedToken */
        public function checkProvidedToken($token, $value, $text = false) 
        {
            $ret = false;
            if (is_array($token)) {
                list ($k, $v) = $token;
                if ($k == $value) {
                    if ($text) {
                        if ($v == $text) {
                            $ret = true;
                        }
                    } else {
                        $ret = true;
                    }
                }
            }
            return $ret;
        }

        /*}}}*/

        /*{{{ public function checkToken */

        public function checkToken($which, $value, $text = false) 
        {
            $ret = false;
            $token = $this->peekToken($which);
            if (is_array($token)) {
                list ($k, $v) = $token;
                if ($k == $value) {
                    if ($text) {
                        if ($v == $text) {
                            $ret = true;
                        }
                    } else {
                        $ret = true;
                    }
                }
            }
            return $ret;
        }

        /*}}}*/

        /*{{{ public function checkText */

        public function checkTokenText($which, $text) 
        {
            $ret = false;
            $token = $this->peekToken($which);
            if (is_string($token)) {
                if ($token == $text) {
                    $ret = true;
                }
            } 
            return $ret;
        }

        /*}}}*/

        /*{{{ public function checkProvidedText */

        public function checkProvidedText($token, $text) 
        {
            $ret = false;
            if (is_string($token)) {
                if ($token == $text) {
                    $ret = true;
                }
            } 
            return $ret;
        }

        /*}}}*/

        /*{{{ public function _tokenText */

        public function extractTokenText($token) 
        {
            $ret = $token;
            if (is_array($token)) {
                list ($k, $ret) = $token;
            }
            return $ret;
        }

        /*}}}*/

        /*{{{ public function peekToken */

        public function peekToken($which, &$line = 0)
        {
            $ret = false;
            switch ($which) {
                case NEXT_TOKEN:
                    $ret = $this->peekNextToken($line);
                    break;

                case NEXT_VALID_TOKEN:
                    $ret = $this->peekNextValidToken($line);
                    break;

                case PRVS_TOKEN:
                    $ret = $this->peekPrvsToken($line);
                    break;

                case PRVS_VALID_TOKEN:
                    $ret = $this->peekPrvsValidToken($line);
                    break;

                default:
                    echo "default...\n";
                    break;
            }
            return $ret;
        }

        /*}}}*/

    }
?>
