<?php session_start(); ?>

<?php
  include("_form.php");
  unset_session('account');
  unset_session('is_admin');
  unset_session('regist_account');
  unset_session('regist_is_admin');
  unset_session('regist_name');
  unset_session('regist_email');
  unset_session('login_account');
  print_p_with_div("notice", "Logging out....", 1, "index.php");
?>

