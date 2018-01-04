<?php session_start(); ?>

<?php
  include("connect_database.php");
  include("_form.php");
  include("_personinfo.php");
  unset_session("require_order");

  if(check_is_admin() == -1){    
    print_p_with_div("alert", "Please login", 2, "index.php");
  }
  else if(check_is_admin() == 0){
    print_p_with_div("alert", "Pemission denied, only administrator can use this page.", 2, "user.php");
  }
  else{
    //regist part start
    if(isset($_POST['account'])){
      store_post_as_session('try_to_regist_account', 'account');
      store_post_as_session('try_to_regist_is_admin', 'is_admin');
      store_post_as_session('try_to_regist_name', 'name');
      store_post_as_session('try_to_regist_email', 'email');
      $try_to_regist_account=$_POST['account'];//for sql
      $try_to_regist_password=$_POST['password'];
      $try_to_regist_re_password=$_POST['re_password'];
      $try_to_regist_name=$_POST['name'];
      $try_to_regist_email=$_POST['email'];
      $try_to_regist_is_admin=$_POST['is_admin'];
           
      $needto_reinput = 0;

      $needto_output = array();

      if($try_to_regist_account == null){
        array_push($needto_output, "account can not be null");
        $needto_reinput = 1;
      }
      if(check_is_account_exist($try_to_regist_account) == 1){
        array_push($needto_output, "account is already been used");
        $needto_reinput = 1;
      }
      if(preg_match('/\s/', $try_to_regist_account)){//if $account have " "
        array_push($needto_output, "account can not use whitespace");
        $needto_reinput = 1;
      }
      if($try_to_regist_password == null){
        array_push($needto_output, "password can not be null");
        $needto_reinput = 1;
      }
      if($try_to_regist_password != $try_to_regist_re_password){
        array_push($needto_output, "password isn't the same");
        $needto_reinput = 1;
      }
      if($try_to_regist_is_admin == null){
        array_push($needto_output, "is_admin can not be null");
        $needto_reinput = 1;
      }
      else if($try_to_regist_is_admin != "1" && $try_to_regist_is_admin != "0" && $try_to_regist_is_admin != ""){
        array_push($needto_output, "is_admin must be 0 or 1");
        $needto_reinput = 1;
      }
      if($try_to_regist_name == null){
        array_push($needto_output, "name can not be null");
        $needto_reinput = 1;
      }
      if(!filter_var($try_to_regist_email, FILTER_VALIDATE_EMAIL)){//check email
        array_push($needto_output, "email is invalid");
        $needto_reinput = 1;
      }
      if($needto_reinput == 1){
        $needto_output_with_header = array();
        array_push($needto_output_with_header, "Regist failed");
        array_push($needto_output_with_header, $needto_output);
        print_p_with_div("alert", $needto_output_with_header, 2, "admin_users.php");
      }
      else{
        unset($_SESSION['try_to_regist_account']);
        unset($_SESSION['try_to_regist_is_admin']);
        unset($_SESSION['try_to_regist_name']);
        unset($_SESSION['try_to_regist_email']);
        account_create($try_to_regist_account, $try_to_regist_password, $try_to_regist_is_admin, $try_to_regist_name, $try_to_regist_email);
        print_p_with_div("notice", "Regist success!", 2, "admin_users.php");
      }
    }
  
    //delete part start (delete account)
    if(isset($_POST['delete_account_by_button'])){
      if($_POST['delete_account_by_button'] != $_SESSION['in_use_id']){
        $id=$_POST['delete_account_by_button'];
        account_delete($id);
        print_p_with_div("notice", "Delete success!", 1, "admin_users.php");
      }
      else{
        print_p_with_div("alert", "Can't delete this account by itself.", 0.5, "admin_users.php");
      }
    }
    
    //change part start (change is_admin, 1->0, 0->1)
    if(isset($_POST['change_account_by_button'])){
      if($_POST['change_account_by_button'] != $_SESSION['in_use_id']){
        $id=$_POST['change_account_by_button'];
        account_change($id);
        print_p_with_div("notice", "Change sucess!", 1, "admin_users.php");
      }
      else{
        print_p_with_div("alert", "Can't change this account by itself", 5, "admin_users.php");
      }
    }
    
    $people_rs = account_show_all();
?>
    <div id="welcome">
      <h1>Welcome to the user manage page!</h1>
      <div id="transbutton" >
        <p class="margin">
        <input type="submit" onclick="location.href='user.php'" value="首頁"></input>
      </p>       
      </div>
    </div>
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
            <form method="post" action="admin_users.php">
              <input type="hidden" name="delete_account_by_button" value="<?php echo $table->id; ?>">
              <input class="adjust" value="delete" type="submit">
            </form>
            <form method="post" action="admin_users.php">
              <input type="hidden" name="change_account_by_button" value="<?php echo $table->id; ?>">
              <input class="adjust" value="change" type="submit">
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
      <form name="update_or_build" method="post" action="admin_users.php">
        <table class="noshadow">
          <tbody>
            <tr>
              <td>account</td>
              <td>
                <input name="account" type="text" value="<?php print_session('try_to_regist_account'); ?>">
              </td>
            </tr>
            <tr>
              <td>password</td>
              <td>
                <input name="password" type="password">
              </td>
            </tr>
            <tr>
              <td>confirm</td>
              <td>
                <input name="re_password" type="password">
              </td>
            </tr>
            <tr>
              <td>is_admin</td>
              <td>
                <input name="is_admin" type="text" value="<?php print_session('try_to_regist_is_admin'); ?>">
              </td>
            </tr>
            <tr>
              <td>name</td>
              <td>
                <input name="name" type="text" value="<?php print_session('try_to_regist_name'); ?>">
              </td>
            </tr>
            <tr>
              <td>email</td>
              <td>
                <input name="email" type="text" value="<?php print_session('try_to_regist_email'); ?>">
              </td>
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
?>

