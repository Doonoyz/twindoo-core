<?php 
    /*
    *  $Id: allTests.php 26459 2005-07-12 03:52:01Z hkodungallur $
    *
    *  Copyright(c) 2004-2005, SpikeSource Inc. All Rights Reserved.
    *  Licensed under the Open Source License version 2.1
    *  (See http://www.spikesource.com/license.html)
    */
?>
<?php 
    require_once('simpletest/unit_tester.php');
    require_once('simpletest/reporter.php');

    $test = &new GroupTest('PHPCheckstyle Tests');
    $test->addTestFile('TokenUtils_test.php');
    $test->run(new HtmlReporter());
?>
