<?php session_start(); ?>

<?php
  include("connect_database.php");
  include("_form.php");
  unset_session('login_account');

  $account_status = check_is_admin($db);
  if($account_status == 0){//check is_admin
      print_p_with_div("alert", "Pemission denied, only administrator can use this page.", 2, "member.php");
  }  
  else if($account_status == -1){
    print_p_with_div("alert", "please login", 2, "index.php");
  }
  else{
//delete part start
    if(isset($_POST['button_delete_house'])){
      $house_id=$_POST['button_delete_house'];
      delete_house($db, $house_id);
      print_p_with_div("notice", "already delete", 1, "admin.php");
    }
//delete part end

//favorite part start
    if(isset($_POST['button_favorite_house'])){
        $account = $_SESSION['account'];
        $table = find_account($db, $account);
        $user_id = $table[5];
        
        $house_id = $_POST['button_favorite_house'];
        favorite_house($db, $user_id, $house_id);
        print_p_with_div("notice", "Favorited <3", 1, "admin.php");
    }
//favorite part end
    
    $my_account = $_SESSION['account'];//for sql;
    $table = find_account($db, $my_account);
?>
    <div id="welcome">
      <h1>Welcome to the Adim page!</h1>
      <div id="transbutton">
        <p class="margin">
          <input type="submit" onclick="location.href='admin_favorite.php'" value="我的最愛"></input>
          <input type="submit" onclick="location.href='admin_house.php'" value="房屋管理"></input>
          <input type="submit" onclick="location.href='admin_user.php'" value="會員管理"></input>
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
//search part start
    $user_id = $table[5];
    $num_to_require = array('id', 'name', 'location', 'time');
    $require = "";
    $array_for_execute = array();
//select num_to_require part start
    for($i = 0;$i < 4;$i++){
      if(!empty($_POST[$num_to_require[$i]])){
        $condition_left = $num_to_require[$i];
        $condition_right = $_POST[$condition_left];
        $condition_count = "condition_right" . "$i";
        $array_for_execute[$condition_count] = $condition_right;
        $require .= " AND h.$condition_left = :$condition_count";
      }
    }
//select num_to_require part end

//select price part start
    if(!empty($_POST['price'])){
      switch($_POST['price']){
        case "1":
          $require .= " AND price <= 300";
          break;
        case "2":
          $require .= " AND price <= 600 AND price >= 300";
          break;
        case "3":
          $require .= " AND price <= 1200 AND price >= 600";
          break;
        case "4":
          $require .= " AND price >= 1200";
      }
    }
    if(!empty($_POST['owner'])){
      $require .= " AND p.name = :owner";
      $array_for_execute['owner'] = $_POST['owner'];
      //$require .= " AND p.name = '$_POST[owner]'";
    }
//select price part end

//select information part start    
    $require_info = "";
    $require_info_num = 0;
    if(isset($_POST['information'])){
      foreach($_POST['information'] as $info_id){
        if($info_id == 10){
          for($i = 0;$i < 10;$i++){
            $require_info .= " OR info.information = \"$num_to_info[$i]\"";
          }
          break;
        }
        $require_info .= " OR info.information = \"$num_to_info[$info_id]\"";
      }
      $require_info = substr($require_info, 3);
      if($info_id != 10){
        $require_info_num = count($_POST['information']);
      } 
      $require .= " AND (" . $require_info . ")";
    }
//select infomation part end

//order part start
    if(isset($_POST['price_search'])){
      $require_order = " ORDER BY price $_POST[price_search]";
    }
    else if(isset($_POST['time_search'])){
      $require_order = " ORDER BY time $_POST[time_search]";
    }
    else{
      $require_order = " ORDER BY h.id ASC";
    }
//order part end
    if(substr($require, 0, 4) === " AND"){
      $require = substr($require, 4);
    }
    $people_rs = show_house($db, $user_id, $require, $require_info_num, $require_order, $array_for_execute);
//search part end
?>
<!-- Search part END-->
<!-- Table part START-->
    <div id="table">
      <table>
        <h3>All houses</h3>
        <tbody>
          <tr><td class="adjust" colspan="8">
          <p style="text-align:end;font-size:10px;">*info:use ctrl + mouse to multi-check the information</p>
          </td></tr>
          <tr>
    <form method="post" action="admin.php" id="searchform">
            <td class="adjust">
            <input class="search" name="id" type="number" placeholder="interval" min="0" <?php check_post_value("id"); ?>>
            </td>
            <td class="adjust">
              <input class="search" name="name" type="text" placeholder="keywords"<?php check_post_value("name"); ?>>
            </td>
            <td class="adjust">
              <select class="search" name="price"  placeholder="keywords" >
                <option value="0" <?php check_post_select("price", "0"); ?>>--</option>
                <option value="1" <?php check_post_select("price", "1"); ?>>0 ~ 300</option>
                <option value="2" <?php check_post_select("price", "2"); ?>>300 ~ 600</option>
                <option value="3" <?php check_post_select("price", "3"); ?>>600 ~ 1200</option>
                <option value="4" <?php check_post_select("price", "4"); ?>>1200 ~</option>
              </select>
            </td>
            <td class="adjust">
              <input class="search" name="location" type="text" placeholder="keywords"<?php check_post_value("location"); ?>>
            </td>
            <td class="adjust">
              <input class="search" name="time" type="date" placeholder="date"<?php check_post_value("time"); ?>>
            </td>
            <td class="adjust">
              <input class="search" name="owner" type="text" placeholder="keywords"<?php check_post_value("owner"); ?>>
            </td>
            <td class="adjust">
              <div id="infoselect" >
                <select class="search" name="information[]" multiple="multiple">
<?php
    
                  echo "<option value='10' ", check_post_multiselect('information','10') ,">-none-</option>";
                  for($i = 0;$i < 10;$i++){
                    $tmp_str = $num_to_info[$i];
                    echo "<option value='$i' ", check_post_multiselect('information',$i) ,">$tmp_str</option>";
                  }
?>
                </select>
              </div>
            </td>
            <td class="adjust">
              <input name="advanced_search" type="hidden" value="true">
              <input type="submit" value="search">
            </td>
    </form>
          </tr>
        </tbody>
        <tbody>
        <tr>
          <th>id</th>
          <th>name</th>
          <th>
            <form method="post" action="admin.php" >
              <button type="submit" form="searchform" class="svgbutton" name="price_search" value="ASC">
                <svg height="10" width="10">
                  <polygon points="5,0 0,10 10,10" style="fill:rgba(50,0,255,0.5)" />
                </svg>
              </button>
            </form>
              price
            <button type="submit" form="searchform" class="svgbutton" name="price_search" value="DESC">
            <svg height="10" width="10">
              <polygon points="0,0 5,10 10,0" style="fill:rgba(50,0,255,0.5)" />
            </svg>
            </button>
          </th>
          <th>location</th>
          <th>
            <button type="submit" form="searchform" class="svgbutton" name="time_search" value="ASC">
              <svg height="10" width="10">
                <polygon points="5,0 0,10 10,10" style="fill:rgba(50,0,255,0.5)" />
              </svg>
            </button>
              time
            <button type="submit" form="searchform" class="svgbutton" name="time_search" value="DESC">
              <svg height="10" width="10">
                <polygon points="0,0 5,10 10,0" style="fill:rgba(50,0,255,0.5)" />
              </svg>
            </button>
          </th>
          <th>owner</th>
          <th>information</th>
          <th>option</th>
        </tr>
<?php
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
            <form method="post" action="admin.php">
            <input type="hidden" name="button_favorite_house" value="<?php echo $table->hid; ?>" <?php if($table->user_id != NULL){ echo "disabled"; } ?>>
              <input class="adjust" value="<?php if($table->user_id != NULL) {echo "已在我的最愛內";}else{echo "favorite";} ?>" type="submit" <?php if($table->user_id != NULL) {echo "disabled";} ?> >
            </form>
            <form method="post" action="admin.php">
              <input type="hidden" name="button_delete_house" value="<?php echo $table->hid; ?>">
              <input class="adjust" value="delete" type="submit">
            </form>
          </td>
        </tr>
<?php
    }
?>
      </tbody>
      </table>
    </div>
<!-- Table part END -->
<?php
  }
?>
