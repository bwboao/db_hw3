<?php session_start(); ?>

<?php
  include("connect_database.php");
  include("_form.php");
  include("_personinfo.php");

  if(check_is_admin() == -1){
    print_p_with_div("alert", "please login", 2, "index.php");
  }
  else{
    //delete part
    if(isset($_POST['delete_house_by_button'])){
      house_delete($_POST['delete_house_by_button']);
      print_p_with_div("notice", "already delete", 1, "user.php");
    }

    //favorite part
    if(isset($_POST['favorite_house_by_button'])){
      house_favorite($_SESSION['in_use_id'], $_POST['favorite_house_by_button']);
      print_p_with_div("notice", "Favorited <3", 1, "user.php");
    }

    //book part
    if(isset($_POST['book_house_by_button'])){
      house_book($_SESSION['in_use_id'], $_POST['book_house_by_button'], $_SESSION['time_check_in'], $_SESSION['time_check_out']);
      print_p_with_div("notice", "Book success!", 1, "user.php");
    }

    //search part start
    $require = "";
    $array_for_execute = array();
    if(!empty($_POST['id'])){
      $require .= " AND h.id IN (" . str_house_select_by('id') . ")";
      $array_for_execute['id'] = $_POST['id'];
    }
    if(!empty($_POST['name'])){
      $require .= " AND h.id IN (" . str_house_select_by('name') . ")";
      $array_for_execute['name'] = $_POST['name'];
    }
    if(!empty($_POST['location'])){
      $require .= " AND h.id IN (" . str_house_select_by('location') . ")";
      $array_for_execute['location'] = $_POST['location'];
    }
    if(!empty($_POST['time_check_in']) && !empty($_POST['time_check_out'])){
      store_post_as_session('time_check_in', 'time_check_in');
      store_post_as_session('time_check_out', 'time_check_out');
      if($_SESSION['time_check_in'] >= $_SESSION['time_check_out']){
        $require .= " AND 0 = 1";
      }
      else{
        $require .= " AND h.id NOT IN (" . str_house_select_by('time') . ")";
        $array_for_execute['time_check_in'] = $_POST['time_check_in'];
        $array_for_execute['time_check_out'] = $_POST['time_check_out']; 
      }
    }
    else if(!empty($_POST['time_check_in'])){
      store_post_as_session('time_check_in', 'time_check_in');
      unset_session('time_check_out');
    }
    else if(!empty($_POST['time_check_out'])){
      store_post_as_session('time_check_out', 'time_check_out');
      unset_session('time_check_in');
    }
    else{
      unset_session('time_check_out');
      unset_session('time_check_in');
    }
    if(!empty($_POST['owner'])){
      $require .= " AND h.id IN (" . str_house_select_by('owner') . ")";
      $array_for_execute['owner'] = $_POST['owner'];
    }
    if(isset($_POST['information']) && $_POST['information'][0] != 11){
      $infos = "";
      $info_count = 0;
      foreach($_POST['information'] as $info_id){
        $infos .= ", $info_id";//(, 1, 3, 4
        $info_count += 1;
      }
      $infos = "(" . substr($infos, 2) . ")";//$infos (1, 3, 4)
      $require .= " AND h.id IN (" . str_house_select_by($infos) . " GROUP BY house_id HAVING count(information_id) >= $info_count)";
    }
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
          break;
      }
    }
    $require = substr($require, 4);
    if(isset($_POST['price_search'])){
      $require_order = "ORDER BY price $_POST[price_search]";
    }
    else if(isset($_POST['time_search'])){
      $require_order = "ORDER BY time $_POST[time_search]";
    }
    else{
      $require_order = "ORDER BY h.id ASC";
    }
    if($require != ""){
      $house_rs = house_show($require, $require_order, $array_for_execute);
    }
    else{
      $house_rs = house_show("1", $require_order, array());
    }
?>
<!-- Table part START-->
    <div id="welcome">
      <h1>Welcome to the <?php if($_SESSION['in_use_is_admin'] == 1){echo "Admin";}else{echo "Member";}?> page!</h1>
      <div id="transbutton">
        <p class="margin">
          <input type="submit" onclick="location.href='user_favorites.php'" value="我的最愛"></input>
          <input type="submit" onclick="location.href='user_houses.php'" value="我的房屋"></input>
          <input type="submit" onclick="location.href='user_orders.php'" value="我的訂房"></input>
<?php
          if($_SESSION['in_use_is_admin'] == 1){
?>
          <input type="submit" onclick="location.href='admin_users.php'" value="會員管理"></input>
<?php
          }
?>
        </p>
      </div>
    </div>
    <div id="table">
      <table>
        <h3>All houses</h3>
        <tbody>
          <tr>
            <td class="adjust" colspan="8">
              <p style="text-align:end;font-size:10px;">*info:use ctrl + mouse to multi-check the information</p>
            </td>
          </tr>
          <tr>
            <td>id_keyword</td>
            <td>name_keyword</td>
            <td>price_keyword</td>
            <td>location_keyword</td>
            <td>time_check_in</td>
            <td>time_check_out</td>
            <td>owner_keyword</td>
            <td>information_selections</td>
          </tr>   
          <tr>          
            <form method="post" action="user.php" id="searchform">
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
                <input class="search" name="time_check_in" type="date" placeholder="date"<?php check_post_value("time_check_in"); ?>>
              </td>
              <td class="adjust">
                <input class="search" name="time_check_out" type="date" placeholder="date"<?php check_post_value("time_check_out"); ?>>
              </td>

              <td class="adjust">
                <input class="search" name="owner" type="text" placeholder="keywords"<?php check_post_value("owner"); ?>>
              </td>
              <td class="adjust">
                <div id="infoselect" >
                  <select class="search" name="information[]" multiple="multiple">
                    <option value='11' ", check_post_multiselect('information','11') ,">-none-</option>;
<?php     
                    $info_rs = information_show_all();
                    while($info_table = $info_rs->fetchObject()){
                      echo "<option value='$info_table->id' ", check_post_multiselect('information',$info_table->id) ,">$info_table->information</option>";
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
              <button type="submit" form="searchform" class="svgbutton" name="price_search" value="ASC">
                <svg height="10" width="10">
                  <polygon points="5,0 0,10 10,10" style="fill:rgba(50,0,255,0.5)" />
                </svg>
              </button>
              price
              <button type="submit" form="searchform" class="svgbutton" name="price_search" value="DESC">
              <svg height="10" width="10">
                <polygon points="0,0 5,10 10,0" style="fill:rgba(50,0,255,0.5)" />
              </svg>
              </button>
            </th>
            <th>location</th>
            <th colspan='2'>
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
          $has_house = 0;
          while($table = $house_rs->fetchObject()){
          if(check_is_favorite($_SESSION['in_use_id'], $table->id) == 1){
            $is_favorite = 1;
          }
          else{
            $is_favorite = 0;
          }
          $has_house = 1;
?>
          <tr>
            <td><?php echo $table->id; ?></td>
            <td><?php echo $table->name; ?></td>
            <td><?php echo $table->price; ?></td>
            <td><?php echo $table->location; ?></td>
            <td colspan='2'><?php echo $table->time; ?></td>
            <td><?php echo $table->owner; ?></td>
            <td>
<?php
            $info_rs = information_show($table->id);
            while($info = $info_rs->fetchObject()){
              echo "<p> $info->information </p>" ;
            }
 ?>
            </td>
            <td class="adjust">
<?php
            if($is_favorite == 0){
              button_with_form("user.php", "favorite_house_by_button", $table->id, "favorite");
            }
            else{
              button_with_form_disabled("user.php", "favorite_house_by_button", $table->id, "已在我的最愛內");
            }
            if($_SESSION['in_use_is_admin'] == 1){
              button_with_form("user.php", "delete_house_by_button", $table->id, "delete"); 
            }
            if($table->owner_id == $_SESSION['in_use_id']){
              button_with_form_disabled("user.php", "book_house_by_button", $table->id, "my_house");
            }
            else if(isset($_SESSION['time_check_out']) && isset($_SESSION['time_check_in'])){
              button_with_form("user.php", "book_house_by_button", $table->id, "BOOK");
            }
            else if(isset($_SESSION['time_check_in'])){
              button_with_form_disabled("user.php", "book_house_by_button", $table->id, "set_time_check_out");
            }
            else{
              button_with_form_disabled("user.php", "book_house_by_button", $table->id, "set_time_check_in");
            }
?>  
            </td>
          </tr>
<?php
          }//while(fetchObject())
?>
        </tbody>
      </table>
<?php
      if($has_house == 0){
        print_p("notice", "check_in_time must smaller than chekc_out_time");
      }
?>
    </div>
<!-- Table part END -->
<?php
  }
?>
