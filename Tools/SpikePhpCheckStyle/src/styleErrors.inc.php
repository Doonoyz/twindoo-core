<?php 
    /*
    *  $Id: styleErrors.inc.php 26757 2005-07-15 03:04:26Z hkodungallur $
    *
    *  Copyright(c) 2004-2005, SpikeSource Inc. All Rights Reserved.
    *  Licensed under the Open Source License version 2.1
    *  (See http://www.spikesource.com/license.html)
    */
?>
<?php
   /**
    * Constants describing all the errors 
    * 
    * @author Hari Kodungallur <hkodungallur@spikesource.com>
    */
   
   define("PHPCHECKSTYLE_TAB_IN_LINE", 
          "The line contains a tab"); 

   define("PHPCHECKSTYLE_WRONG_OPEN_TAG", 
          "The php open tag must be '<?php'"); 

   define("PHPCHECKSTYLE_SPACE_AFTER_CS", 
          "'%s' should be followed by one space"); 

   define("PHPCHECKSTYLE_NOSPACE_AFTER_CS",
          "'%s' should not be followed by one space");

   define("PHPCHECKSTYLE_CS_LEFT_PARANTHESIS", 
          "'(' should not be followed by whitespace"); 

   define("PHPCHECKSTYLE_CS_RIGHT_PARANTHESIS",     
          "')' should not be preceded by whitespace"); 

   define("PHPCHECKSTYLE_CS_LEFT_CURLY_SPACE",
          "'{' should be preceded by whitespace"); 

   define("PHPCHECKSTYLE_LEFT_CURLY_POS", 
          "'{' should be on %s"); 

   define("PHPCHECKSTYLE_CS_NO_OPEN_CURLY",
          "Control statement should always be placed within {} blocks"); 

   define("PHPCHECKSTYLE_CS_STMT_ALIGNED_WITH_CURLY",
          "'%s' should be in the same line as '}'");

   define("PHPCHECKSTYLE_CS_STMT_ON_NEW_LINE",
          "'%s' should be on the line after '}'");


   define("PHPCHECKSTYLE_NOSPACE_BEFORE_SEMICOLON",
          "';' should not be prceded by a whitespace"); 

   define("PHPCHECKSTYLE_SPACE_BEFORE_STRING",      
          "Provide whitespace before %s"); 

   define("PHPCHECKSTYLE_SPACE_AFTER_STRING",       
          "Provide whitespace after %s"); 

   define("PHPCHECKSTYLE_NOSPACE_BEFORE_STRING",
          "Should not have whitespace before %s"); 

   define("PHPCHECKSTYLE_NOSPACE_AFTER_STRING",
          "Should not have whitespace after %s");

   define("PHPCHECKSTYLE_END_BLOCK_NEW_LINE",
          "'}' should be on a new line");
   define("PHPCHECKSTYLE_CONSTANT_NAMING",
          "Constant should be all uppercase (starting with a letter) with underscores to separate words");

   define("PHPCHECKSTYLE_FUNCNAME_SPACE_AFTER",
          "Function call should not have a whitespace between function name and opening paranthesis");

   define("PHPCHECKSTYLE_PRIVATE_FUNCNAME_NAMING",
          "Private function name should start with an underscore followed by lowercase letter");

   define("PHPCHECKSTYLE_FUNCNAME_NAMING",
          "Function name should start with a lowercase letter");

   define("PHPCHECKSTYLE_FUNC_DEFAULTVALUE_ORDER",
          "All arguments with default values should be at the end");

   define("PHPCHECKSTYLE_CLASSNAME_NAMING",
          "Class name should start with an uppercase letter");

   define("PHPCHECKSTYLE_NO_SHELL_COMMENTS",
          "Shell/Perl like comments (starting with '#') are not allowed");

   define("PHPCHECKSTYLE_MISSING_DOCBLOCK",
          "Docblock missing");

   define("PHPCHECKSTYLE_LONG_LINE",
          "Line contains more than %s characters");
?>
