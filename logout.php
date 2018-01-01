<?php session_start(); ?>

<?php
  include("_form.php"); 
  print_p_with_div("notice", "Logging out....", 1, "index.php");
  session_destroy();
?>

