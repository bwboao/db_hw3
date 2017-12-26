<?php session_start(); ?>

<?php
  include("connect_database.php");
  include("_form.php");
  
  unset_session('regist_account');
  unset_session('regist_name');

  if(isset($_POST['account'])){
    store_post_as_session('login_account', 'account');
    $account=$_POST['account'];//for sql
    $password=$_POST['password'];
    $hash_password=hash('sha256', $password);
    $table_who_login = find_account($db, $account);

    $needto_output = array();
    $needto_reinput = 0;

    if($_SESSION['login_account'] == null){
      array_push($needto_output, "account can't be null");
      $needto_reinput = 1;
    }
    if($password == null){
      array_push($needto_output, "password can't be null");
      $needto_reinput = 1;
    }
    if($_SESSION['login_account'] != $table_who_login[0]){
      array_push($needto_output, "account doesn't be exist");
      $needto_reinput = 1;
    }
    if($hash_password != $table_who_login[1]){
      array_push($needto_output, "password isn't correct");
      $needto_reinput = 1;
    }
     
    if($needto_reinput == 1){
      $needto_output_with_header = array();
      array_push($needto_output_with_header, "Login failed");
      array_push($needto_output_with_header, $needto_output);
      print_p_with_div("alert", $needto_output_with_header, 2, "index.php");
      unset($needto_output);
    }
    else{
      $_SESSION['account'] = $account;
      $_SESSION['is_admin'] = $table_who_login[2];
      unset($_SESSION['login_account']);

      if($table_who_login[2] == 0){
        $who = "member";
      }
      else{
        $who = "admin";
      }
      print_p_with_div("alert", "$who login successed", 1, "$who.php");
    }
  }
?>
<html>
<head>
  <title>need_to_login</title>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>

<body>
  <div>
    <h1>Welcome to HW2</h1>
  </div>
  <div id="index" >  
    <h1>Login</h1>
    <!--form name="login" method="post" action="can_login.php"-->
    <form name="login" action="index.php" method="post">
      <table id="login" class="noshadow">
      <tr>
        <td>account</td>
        <td>
          <input name="account" type="text" value="<?php print_session('login_account'); ?>">
        </td>
      </tr>
      <tr>
        <td>password</td>
        <td><input name="password" id = "password" type="password"></td>
      </tr>
      </table>
      
      <p>
        <input name="button_to_submit" type="submit" value="login">&nbsp;&nbsp;
        <input type="button" onclick="location.href='regist.php'" value="regist"></input>
      </p>
    </form>
  </div>
</body>
</html>


