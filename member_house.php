<?php session_start(); ?>

<?php
  include("connect_database.php");
  include("_form.php");
  unset_session('login_account');
  unset_session('is_update');
  unset_session('change_house_id');
  unset_session('change_house_name');
  unset_session('change_house_price');
  unset_session('change_house_location');
  if(isset($_SESSION['account'])){
    $my_account = $_SESSION['account'];
    if($_SESSION['is_admin'] == 1){
      print_p_with_div("alert", "This account is admin, you DON'T belong here", 2, "admin.php");
    }
    else{
//delete part start
      if(isset($_POST['button_delete_house'])){
        $house_id=$_POST['button_delete_house'];
        delete_house($db, $house_id);
        print_p_with_div("notice", "already delete", 1, "member_house.php");
      }
//delete part end

      $my_account = $_SESSION['account'];//for sql;
      $account_using = find_account($db, $my_account);
?>
      <div id="welcome">
        <h1>Welcome to your house manage page!</h1>
        <div id="transbutton">
          <p class="margin">
            <input type="submit" onclick="location.href='member.php'" value="首頁"></input>
          </p>
        </div>
      </div>
<!-- Personinfo part START-->
      <div id="personinfo">
        <p>Hello, <?php echo "$account_using[0]"; ?> ! </p>
        <table>
          <tbody>
            <tr>
              <th colspan="2">info</th>
            </tr>
            <tr>
              <td>name</td>
              <td><?php echo "$account_using[3]"; ?></td>
            </tr>
            <tr>
              <td>email</td>
              <td><?php echo "$account_using[4]"; ?></td>
            </tr>
          </tbody>
        </table>
        <p class="margin">
          <input type="button" onclick="location.href='logout.php'" value="logout"></input>
        </p>
      </div>
<!-- Personinfo part END-->

<?php
//search part start
      $user_id = $account_using[5];//5 is id
      $people_rs = show_house_my($db, $user_id);
      //while($my_house = $people_rs->fetchObject()){
      //  print_r($my_house);
      //}
//search part end
?>

<!-- Table part START-->
      <div id="table">
        <table>
          <h3>Your houses</h3>
          <tr>
            <td class="adjust" colspan="7"></td>
            <td class="adjust">
              <form method="post" action="member_house_change.php">
              <input type="hidden" name="button_new_house" value="<?php echo $user_id; ?>">
              <input class="adjust" value="新增" type="submit">
              </form>
            </td>
          </tr>
<?php
      $has_house = 0;
      while($my_houses = $people_rs->fetchObject()){
        if($my_houses != NULL && $has_house == 0){
          $has_house = 1;
?>
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

<?php
        }

?>  
          <tr>
	    <td><?php echo $my_houses->hid; ?></td>
            <td><?php echo $my_houses->hname; ?></td>
            <td><?php echo $my_houses->price; ?></td>
            <td><?php echo $my_houses->location; ?></td>
            <td><?php echo $my_houses->time; ?></td>
            <td><?php echo $my_houses->owner; ?></td>
            <td><?php print_info($db, $my_houses->hid, $info_to_num, $num_to_info); ?></td>
            <td class="adjust">
              <?php button_with_form("member_house.php", "button_delete_house", $my_houses->hid, "delete"); ?>
              <?php button_with_form("member_house_change.php", "button_change_house", $my_houses->hid, "change"); ?>
            </td>
          </tr>
<?php
      }
      
        if($has_house == 0){
          print_p("notice", "您尚未擁有任何房子");
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

