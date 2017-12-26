<?php session_start(); ?>

<?php
  include("connect_database.php");
  include("_form.php");
  unset_session('login_account');
  
  if(isset($_SESSION['account'])){//check is_admin
    if(isset($_SESSION['is_admin']) && $_SESSION['is_admin'] != 1){
      print_p_with_div("alert", "Pemission denied, only administrator can use this page.", 2, "member.php");
    }
    else{
//regist part start
      if(isset($_POST['account'])){//regist part start
        $account=$_POST['account'];//for sql
        $password=$_POST['password'];
        $re_password=$_POST['re_password'];
        $name=$_POST['name'];
        $email=$_POST['email'];
        $is_admin=$_POST['is_admin'];
        store_post_as_session('regist_account', 'account');
        store_post_as_session('regist_is_admin', 'is_admin');
        store_post_as_session('regist_name', 'name');
        store_post_as_session('regist_email', 'email');
        /*$_SESSION['regist_account']=$_POST['account'];//for reinput's value
        $_SESSION['regist_is_admin']=$_POST['is_admin'];
        $_SESSION['regist_name']=$_POST['name'];
        $_SESSION['regist_email']=$_POST['email'];*/

        $needto_reinput = 0;

        $sql_find_account="SELECT account FROM people WHERE account= :account";
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
        if($is_admin == null){
          array_push($needto_output, "is_admin can not be null");
          $needto_reinput=1;
        }
        else if($is_admin != "1" && $is_admin != "0"){
          array_push($needto_output, "is_admin must be 0 or 1");
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

        if($needto_reinput == 1){
          array_push($needto_output, "Regist failed :(");
          ?><div class="transport"><?php
          foreach($needto_output as $key => $value){
            ?><p class="alert"><?php echo $value; ?></p><?php
          }
          unset($needto_output);
          ?></div>
          <meta http-equiv=REFRESH CONTENT=2;url=admin_user.php><?php
        }
        else{
          $hash_password=hash('sha256',$password);
          $sql_to_adduser="INSERT INTO people (account, password, is_admin, name, email) VALUES (:account, :hash_password, :is_admin, :name, :email)";
          //$db->query($sql_to_adduser);
          $rs=$db->prepare($sql_to_adduser);
          $rs->execute(array('account' => $account, 'hash_password' => $hash_password, 'is_admin' => $is_admin, 'name' => $name, 'email' => $email));
          unset($_SESSION['regist_is_admin']);
          unset($_SESSION['regist_account']);
          unset($_SESSION['regist_name']);
          unset($_SESSION['regist_email']);
          ?><div class="transport">
            <p class="notice">Regist success!</p>
          <meta http-equiv=REFRESH CONTENT=2;url=admin_user.php>
            </div>
          <?php
        }
      }
//regist part end

//delete part start
      if(isset($_POST['button_delete_account'])){//delete part start
        $_SESSION['button_delete_account']=$_POST['button_delete_account'];
        if($_POST['button_delete_account'] != $_SESSION['account']){
          $account=$_POST['button_delete_account'];
          $account_table=find_account($db,$account);
          $account_id=$account_table[5];
          unset_session('button_delete_account');
          delete_account($db, $account_id);
          print_p_with_div("notice", "finish delete", 1, "admin_user.php");
        }
        else{
          print_p_with_div("alert", "Can't delete this account by itself.", 0.5, "admin_user.php");
        }
      }
//delete part end

//change part start
      if(isset($_POST['button_change_account'])){
        $account=$_POST['button_change_account'];
        if($account != $_SESSION['account']){
          $sql_find_account = "SELECT * FROM people WHERE account='$account'";
          $this_rs = $db->prepare($sql_find_account);
          $this_rs->execute();
          $table = $this_rs->fetch();
          if($table[2] == 1){
            $new_is_admin = 0;
          }
          else{
            $new_is_admin = 1;
          }

          $sql_find_account="UPDATE people SET is_admin=$new_is_admin WHERE account='$account'";
          $rs=$db->prepare($sql_find_account);
          $rs->execute();
          print_p_with_div("notice", "Upgrade sucess", 1, "admin_user.php");
        }
        else{
          print_p_with_div("alert", "Can't change this account by itself", 5, "admin_user.php");
        }
      }
//change part end
      $my_account = $_SESSION['account'];//for sql;
      $sql_find_account = "SELECT * FROM people WHERE account='$my_account'";
      $this_rs = $db->prepare($sql_find_account);
      $this_rs->execute();
      $table = $this_rs->fetch();
?>
      <div id="welcome">
        <h1>Welcome to the user manage page!</h1>
        <div id="transbutton" >
          <p class="margin">
          <input type="submit" onclick="location.href='admin.php'" value="首頁"></input>
        </p>       
        </div>
      </div>
      <div id="personinfo">
        <p>Hello, <?php echo "$table[0]"; ?> ! </p>

        <table>
          <tbody>
            <tr>
              <th colspan="2">info</th>
            </tr>
            <tr>
              <td>name</td>
              <td><?php echo "$table[3]"; ?></td>
            </tr>
            <tr>
              <td>email</td>
              <td><?php echo "$table[4]"; ?></td>
            </tr>
          </tbody>
        </table>
         <p>
          <input type="button" onclick="location.href='logout.php'" value="logout"></input>
        </p>
      </div>
<?php
      $sql_find_all = "SELECT * FROM people";
      //$people_rs = $db->query($sql_find_all);
      $people_rs = $db->prepare($sql_find_all);
      $people_rs->execute();
?>
      <div id="usertable">
        <table>
          <h3>All users</h3>
          <tr>
            <th>user</th>
            <th>name</th>
            <th>email</th>
            <th>admin</th>
            <th>adjust</th>
          </tr>
<?php
      while($table = $people_rs->fetchObject()){
?>
          <tr>
	          <td><?php echo $table->account; ?></td>
            <td><?php echo $table->name; ?></td>
            <td><?php echo $table->email; ?></td>
	          <td class="adminis<?php echo $table->is_admin; ?>" > <?php if ($table->is_admin == 1) echo 'O' ?></td>
            <td class="adjust">
            <form method="post" action="admin_user.php">
            <input type="hidden" name="button_delete_account" value="<?php echo $table->account; ?>"><input class="adjust" value="delete" type="submit">
            </form>
            <form method="post" action="admin_user.php">
            <input type="hidden" name="button_change_account" value="<?php echo $table->account; ?>"><input class="adjust" value="change" type="submit">
            </form>
            </td>
          </tr>
<?php
      }
?>
        </table>
      </div>

      <div id="create">
        <h3>Create</h3>
        <p>Create user or administrator</p>

        <form name="update_or_build" method="post" action="admin_user.php">
        <table class="noshadow">
          <tbody>
            <tr>
              <td>account</td>
              <td><input name="account" type="text" value="<?php if(isset($_SESSION['regist_account'])){echo $_SESSION['regist_account'];} ?>"></td>
            </tr>
            <tr>
              <td>password</td>
              <td><input name="password" type="password"></td>
            </tr>
            <tr>
              <td>confirm</td>
              <td><input name="re_password" type="password"></td>
            </tr>
            <tr>
              <td>is_admin</td>
              <td><input name="is_admin" type="text" value="<?php if(isset($_SESSION['regist_is_admin'])){echo $_SESSION['regist_is_admin'];} ?>"></td>
            </tr>
            <tr>
              <td>name</td>
              <td><input name="name" type="text" value="<?php if(isset($_SESSION['regist_name'])){echo $_SESSION['regist_name'];} ?>"></td>
            </tr>
            <tr>
              <td>email</td>
              <td><input name="email" type="text" value="<?php if(isset($_SESSION['regist_email'])){echo $_SESSION['regist_email'];} ?>"></td>
            </tr>
          </tbody>
        </table>

        <p>
          <input name="button_to_submit" type="submit" value="create">
        </p>
        </form>
      </div>

<?php
    }
  }
  else{
    print_p_with_div("alert", "please login", 2, "index.php");
  }
?>
