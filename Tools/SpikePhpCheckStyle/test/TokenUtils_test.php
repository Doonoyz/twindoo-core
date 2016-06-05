<?php 
    /*
    *  $Id: TokenUtils_test.php 26459 2005-07-12 03:52:01Z hkodungallur $
    *
    *  Copyright(c) 2004-2005, SpikeSource Inc. All Rights Reserved.
    *  Licensed under the Open Source License version 2.1
    *  (See http://www.spikesource.com/license.html)
    */
?>
<?php 
    require_once('simpletest/unit_tester.php');
    require_once('../src/TokenUtils.php');

    class TokenUtils_test extends UnitTestCase 
    { 
        private $totalTokensInData1 = 71;
        private $tokenUtils;
        private $curDir;

        public function __construct() 
        {
            $this->tokenUtils = new TokenUtils();
            $this->curDir = dirname(__FILE__);
        }

        function setUp() 
        {
        }

        function tearDown() 
        {
            $this->tokenUtils->reset();
        }

        function testTokenize() 
        {
            $ret = $this->tokenUtils->tokenize($this->curDir . "/data/data1.php");
            $this->assertIdentical($ret, $this->totalTokensInData1);
            //echo "$ret ...\n";

        }

        function testGetToken() 
        {
            $ret = $this->tokenUtils->tokenize($this->curDir . "/data/data1.php");
            $ret = $this->tokenUtils->getNextToken();
            $this->assertTrue(is_array($ret));
            list ($k, $v) = $ret;
            $this->assertIdentical($k, T_OPEN_TAG);

            $this->tokenUtils->reset();

            $ret = $this->tokenUtils->tokenize($this->curDir . "/data/data1.php");
            for ($i = 0; $i < $this->totalTokensInData1; $i++) {
                $ret = $this->tokenUtils->getNextToken();
            }
            $this->assertTrue(is_array($ret));
            list ($k, $v) = $ret;
            $this->assertIdentical($k, T_CLOSE_TAG);
        }

        function testPeekToken() 
        {
            $ret = $this->tokenUtils->tokenize($this->curDir . "/data/data1.php");
            $ret = $this->tokenUtils->getNextToken(); // open-tag (move index)
            $ret = $this->tokenUtils->peekNextToken(); // whitespace (index unchanged)
            list ($k, $v) = $ret;
            $this->assertIdentical($k, T_WHITESPACE);

            $ret = $this->tokenUtils->peekNextValidToken(); // define (index unchanged)
            list ($k, $v) = $ret;
            $this->assertIdentical($k, T_STRING);
            $this->assertIdentical($v, "define");

            // move forward a bit
            $ret = $this->tokenUtils->getNextToken(); // whitespace (move index)
            $ret = $this->tokenUtils->getNextToken(); // comment (move index)
            $ret = $this->tokenUtils->getNextToken(); // whitespace (move index)
            $ret = $this->tokenUtils->getNextToken(); // define (move index)
            $ret = $this->tokenUtils->getNextToken(); // whitespace (move index)
            $ret = $this->tokenUtils->getNextToken(); // "(" (move index)
            $ret = $this->tokenUtils->getNextToken(); // constant (move index)
            $ret = $this->tokenUtils->getNextToken(); // "," (move index)
            $ret = $this->tokenUtils->getNextToken(); // whitespace (move index)

            $ret = $this->tokenUtils->peekPrvsToken(); // "," (index unchanged)
            $this->assertTrue(is_string($ret));
            $this->assertIdentical($ret, ",");

            $ret = $this->tokenUtils->peekPrvsValidToken(); // same as above
            $this->assertTrue(is_string($ret));
            $this->assertIdentical($ret, ",");
        }
    }
?>
