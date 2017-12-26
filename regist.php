<?php session_start(); ?>

<?php
  include("connect_database.php");
  if(isset($_POST['account'])){
    $account=$_POST['account'];//for sql
    $password=$_POST['password'];
    $re_password=$_POST['re_password'];
    $name=$_POST['name'];
    $email=$_POST['email'];
    $_SESSION['regist_account']=$_POST['account'];//for reinput's value
    $_SESSION['regist_name']=$_POST['name'];
    $_SESSION['regist_email']=$_POST['email']; 

    $needto_reinput = 0;
  
    $sql_find_account="SELECT account FROM people WHERE account=:account";
    $find_rs=$db->prepare($sql_find_account);
    $find_rs->execute(array('account' => $account));
    $num=$find_rs->rowCount();
    $table=$find_rs->fetch();
  
      
      $needto_output = array();
      if($account == null){
        array_push($needto_output, "account can not be null");
        $needto_reinput=1;
      }
      if($num != 0){
        array_push($needto_output, "account is already been used");
        $needto_reinput=1;
      }
      if(preg_match('/\s/', $account)){//if $account have " "
        array_push($needto_output, "account can not use whitespace");
        $needto_reinput=1;
      }
      if($password == null){
        array_push($needto_output, "password can not be null");
        $needto_reinput=1;
      }
      if($password != $re_password){
        array_push($needto_output, "password isn't the same");
        $needto_reinput=1;
      }
      if($name == null){
        array_push($needto_output, "name can not be null");
        $needto_reinput=1;
      }
      if(!filter_var($email, FILTER_VALIDATE_EMAIL)){//check email
        array_push($needto_output, "email is invalid");
        $needto_reinput=1;
      }
      ?><div class="transport"><?php
      
      if($needto_reinput == 1){
        ?>
        <p class="notice">Try to regist again</p>
        <p class="alert">Regist failed :(</p>
        <?php
          foreach($needto_output as $key => $value){
        ?><p class="alert" style="text-align:start"><?php echo $value ?></p><?php
        }
        
        unset($needto_output); 
        ?><meta http-equiv=REFRESH CONTENT=2;url=regist.php><?php
      }	  
      else{
  	    $hash_password=hash('sha256',$password);
        $sql_to_adduser="INSERT INTO people (account, password, is_admin, name, email) VALUES (:account, :hash_password, 0, :name, :email)";
        //$db->query($sql_to_adduser);
        $rs=$db->prepare($sql_to_adduser);
        $rs->execute(array('account' => $account, 'hash_password' => $hash_password, 'name' => $name, 'email' => $email));
        ?><p class="notice">Regist success!<p><?php
        unset($_SESSION['regist_account']);
        unset($_SESSION['regist_name']);
        unset($_SESSION['regist_email']);       
        ?><p class="notice">Try to login!<p>
        <meta http-equiv=REFRESH CONTENT=2;url=index.php><?php
      }
    ?></div><?php
  }
?>

<html>
<head>
  <link rel="stylesheet" href="all.css">
  <meta http-equiv="Content-Type" content="text/html charset=utf-8" />
</head>

<body>
  <div id="regist">
  <form name="regist" method="post" action="regist.php">
        <h3>Regist</h3> 
        <p>please registrate</p>
      <table class="noshadow"><tbody>
        <tr>
          <td>account</td>
          <td><input name="account" type="text" value="<?php if(isset($_SESSION['regist_account'])){echo $_SESSION['regist_account'];} ?>"></td>
        </tr>
        <tr>
          <td>password</td>
          <td><input name="password" type="password"></td>
        </tr>
          <td>confirm</td>
          <td><input name="re_password" type="password" placeholder="type password again"></td>
        </tr>
        <tr>
          <td>name</td>
          <td><input name="name" type="text" value="<?php if(isset($_SESSION['regist_name'])){echo $_SESSION['regist_name'];} ?>"></td>
        </tr>
        <tr>
          <td>email</td>
          <td><input name="email" type="text" value="<?php if(isset($_SESSION['regist_email'])){echo $_SESSION['regist_email'];} ?>"><td>
        </tr>
        </tbody></table> 
    <p>
      <input name="button_to_regist" type="submit" value="regist">
      <input type="button" onclick="location.href='index.php'" value="cancel"></input>
    </p>
  </form>
  </div>
</body>
</html>
       
<meta name="viewport" content="width=device-width, initial-scale=1.0">
