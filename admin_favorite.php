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
          <meta http-equiv=REFRESH CONTENT=2;url=admin.php><?php
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
          <!--meta http-equiv=REFRESH CONTENT=2;url=admin.php-->
            </div>
          <?php
        }
      }
//regist part end

//delete part start
      if(isset($_POST['button_delete_fav'])){//delete part start
          $fav_id=$_POST['button_delete_fav'];
          unset_session('button_delete_fav');
          $sql="DELETE FROM favorite WHERE id ='$fav_id' ";
          $rs=$db->prepare($sql);
          $rs->execute();
          print_p_with_div("notice", "already delete", 1, "admin_favorite.php");
      }
//delete part end


      $my_account = $_SESSION['account'];//for sql;
      $sql_find_account = "SELECT * FROM people WHERE account='$my_account'";
      $this_rs = $db->prepare($sql_find_account);
      $this_rs->execute();
      $table = $this_rs->fetch();
?>
      <div id="welcome">
        <h1>Welcome to your favorite page!</h1>
        <div id="transbutton">
          <p class="margin">
            <input type="submit" onclick="location.href='admin.php'" value="首頁"></input>
          </p>
        </div>
      </div>
<!-- Personinfo part START-->
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
        <p class="margin">
          <input type="button" onclick="location.href='logout.php'" value="logout"></input>
        </p>
      </div>
<!-- Personinfo part END-->
<!-- Search part START-->
<?php
      //echo "$table[5]";
      $user_id = $table[5] ;
      $sql_find_all = "SELECT *,house.id AS hid,house.name hname, people.name AS owner,favorite.id AS favid FROM `favorite` LEFT JOIN house ON house.id = favorite_id LEFT JOIN people ON owner_id = people.id WHERE user_id = $user_id";
      //$people_rs = $db->query($sql_find_all);
      $people_rs = $db->prepare($sql_find_all);
      $people_rs->execute();
?>

<!-- Search part END-->
<!-- Table part START-->
      <div id="table">
        <table>
          <h3>Your favorites</h3>
<?php
      $table = $people_rs->fetchObject();
      if($table == NULL)
      {
?>
      <h3>你尚未擁有任何最愛</h3>
<?php
      }
      else
      {
?>
          </td></tr>
          <tr>
            <th>id</th>
            <th>name</th>
            <th>price</th>
            <th>location</th>
            <th>time</th>
            <th>owner</th>
            <th>information</th>
            <th>option</th>
          </tr>
          <tr>
	    <td><?php echo $table->hid; ?></td>
            <td><?php echo $table->hname; ?></td>
            <td><?php echo $table->price; ?></td>
            <td><?php echo $table->location; ?></td>
            <td><?php echo $table->time; ?></td>
            <td><?php echo $table->owner; ?></td>
            <td>
<?php
        $sql_find_info = "SELECT * FROM information AS info WHERE info.house_id= $table->hid " ;
        $info_rs = $db->query($sql_find_info);
        while($info = $info_rs->fetchObject()){
          echo "<p> $info->information </p>" ;
        }

?>
            </td>
            <td class="adjust">
              <form method="post" action="admin_favorite.php" style="display:block;text-align:center">
                <input type="hidden" name="button_delete_fav" value="<?php echo $table->favid; ?>">
                <input class="adjust" value="delete" type="submit" >
              </form> 
            </td>
          </tr>
<?php
      }
      while($table = $people_rs->fetchObject()){
?>
          <tr>
	    <td><?php echo $table->hid; ?></td>
            <td><?php echo $table->hname; ?></td>
            <td><?php echo $table->price; ?></td>
            <td><?php echo $table->location; ?></td>
            <td><?php echo $table->time; ?></td>
            <td><?php echo $table->owner; ?></td>
            <td>
<?php
        $sql_find_info = "SELECT * FROM information AS info WHERE info.house_id= $table->hid " ;
        $info_rs = $db->query($sql_find_info);
        while($info = $info_rs->fetchObject()){
          echo "<p> $info->information </p>" ;
        }

?>
            </td>
            <td class="adjust">
              <form method="post" action="admin_favorite.php" style="display:block;text-align:center">
                <input type="hidden" name="button_delete_fav" value="<?php echo $table->favid; ?>">
                <input class="adjust" value="delete" type="submit">
              </form>
            </td>
          </tr>
<?php
      }
?>
        </table>
      </div>
<!-- Table part END -->

<?php
    }
  }
  else{
    print_p_with_div("alert", "please login", 2, "index.php");
  }
?>
