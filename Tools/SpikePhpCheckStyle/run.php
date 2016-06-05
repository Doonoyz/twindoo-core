<?php
/*
 *  $Id: run.php 27242 2005-07-21 01:21:42Z hkodungallur $
 *  
 *  Copyright(c) 2004-2005, SpikeSource Inc. All Rights Reserved.
 *  Licensed under the Open Source License version 2.1
 *  (See http://www.spikesource.com/license.html)
 */
?>
<?php
    /**
     *  CLI file to run the PHPCheckstyle
     */

    function usage() 
    {
        echo "Usage: " . $_SERVER['argv'][0] . " <options>\n";
        echo "\n";
        echo "    Options: \n";
        echo "       --src          Root of the source directory tree or a file.\n";
        echo "       --exclude      [Optional] A directory or file that needs to be excluded.\n";
        echo "       --format       [Optional] Output format (html/text). Defaults to 'html'.\n";
        echo "       --outdir       [Optional] Report Directory. Defaults to './style-report'.\n";
        echo "       --config       [Optional] Config. Defaults to './config/pear.cfg.xml'.\n";
        echo "       --help         Display this usage information.\n";
        exit;
    }


    // default values
    $OPTION['src'] = false;
    $OPTION['exclude'] = array();
    $OPTION['format'] = "html";
    $OPTION['outdir'] = "./style-report";
    $OPTION['config'] = "./config/pear.cfg.xml";

    // loop through user input
    for ($i = 1; $i < $_SERVER["argc"]; $i++) {
        switch ($_SERVER["argv"][$i]) {
        case "--src":
            $OPTION['src'] = $_SERVER['argv'][++$i];
            break;

        case "--outdir":
            $OPTION['outdir'] = $_SERVER['argv'][++$i];
            break;

        case "--exclude":
            $OPTION['exclude'][] = $_SERVER['argv'][++$i];
            break;

        case "--format":
            $OPTION['format'] = $_SERVER['argv'][++$i];
            break;

        case "--config":
            $OPTION['config'] = $_SERVER['argv'][++$i];
            break;

        case "--help":
            usage();
            break; 
        }
    }

    //validity checks
    
    // check that source directory is specified and is valid
    if ($OPTION['src'] == false) {
        echo "\nPlease specify a source directory/file using --src option.\n\n";
        usage();
    }

    if (($OPTION['format'] != "html") && ($OPTION['format'] != "text")) {
        echo "\nUnknown format.\n\n";
        usage();
    }

    //input commands end
    //driver commands begin

    define("PHPCHECKSTYLE_HOME_DIR", dirname(__FILE__)); 

    require_once PHPCHECKSTYLE_HOME_DIR . "/src/PHPCheckstyle.php";
    require_once PHPCHECKSTYLE_HOME_DIR . "/src/util/Utility.php";

    $style = new PHPCheckstyle($OPTION['config']);
    $style->processFiles($OPTION['src'], $OPTION['exclude'], $OPTION['format'], $OPTION['outdir']);

    echo "Reporting Completed. Please check the results.\n\n";
?>

