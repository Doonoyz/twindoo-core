<?php
echo "Hello"; /*I want to test that the
max line length check will give an error here before the error on the next line, but first I must exceed 80 characters. 
Now, let's see*/ $x= -5;

$x = 5;
$y = 6;
if($x > $y) {
     echo "Greater";
} elseif ($x == $y) {
    echo "Equal";
    //Why does this happen?
}
else {
   echo "Less than"; }

$z= "Hello";
echo $z{0}; //will brackets be deemed opening/closing of code blocks?
echo $z{ 0 }; // will whitespace checks kick in? 

$x++;$y++;
$x++; //space after this statement, not the previous

//$z=$x+$y;
?>
