<?php session_start(); ?>

<?php
  include("connect_database.php");
  include("_form.php");
  include("_personinfo.php");

  if(!isset($_SESSION['in_use_is_admin'])){
    print_p_with_div("alert", "please login", 2, "index.php");
  }
  //else if($_SESSION['in_use_is_admin'] == 0){
  //  print_p_with_div("alert", "Pemission denied, only administrator can use this page.", 2, "member.php");
  //}
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
    if(!empty($_POST['time'])){
      $require .= " AND h.id IN (" . str_house_select_by('time') . ")";
      $array_for_execute['time'] = $_POST['time'];
    }
    if(!empty($_POST['owner'])){
      $require .= " AND h.id IN (" . str_house_select_by('owner') . ")";
      $array_for_execute['owner'] = $_POST['owner'];
    }
    if(isset($_POST['information']) && $_POST['information'][0] != 10){
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
      $require_order = " ORDER BY price $_POST[price_search]";
    }
    else if(isset($_POST['time_search'])){
      $require_order = " ORDER BY time $_POST[time_search]";
    }
    else{
      $require_order = " ORDER BY id ASC";
    }
    echo "<br>" . $require . "<br>"; 
    if($require != ""){
      $house_rs = house_show($require, $require_order, $array_for_execute);
    }
    else{
      $house_rs = house_show_only_order($require_order);
    }
?>
<!-- Table part START-->
    <div id="welcome">
      <h1>Welcome to the <?php if($_SESSION['in_use_is_admin'] == 1){echo "Admin";}else{echo "Member";}?> page!</h1>
      <div id="transbutton">
        <p class="margin">
          <input type="submit" onclick="location.href='user_favorite.php'" value="我的最愛"></input>
          <input type="submit" onclick="location.href='user_house.php'" value="房屋管理"></input>
<?php
          if($_SESSION['in_use_is_admin'] == 1){
?>
          <input type="submit" onclick="location.href='user_user.php'" value="會員管理"></input>
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
                    for($i = 1;$i < 11;$i++){
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
          while($table = $house_rs->fetchObject()){
          if(check_is_favorite($_SESSION['in_use_id'], $table->id) == 1){
            $is_favorite = 1;
          }
          else{
            $is_favorite = 0;
          }
?>
          <tr>
            <td><?php echo $table->id; ?></td>
            <td><?php echo $table->name; ?></td>
            <td><?php echo $table->price; ?></td>
            <td><?php echo $table->location; ?></td>
            <td><?php echo $table->time; ?></td>
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
              <form method="post" action="user.php">
                <input type="hidden" name="favorite_house_by_button" value="<?php echo $table->id; ?>" <?php if($is_favorite == 1){ echo "disabled"; } ?>>
                <input class="adjust" value="<?php if($is_favorite == 1) {echo "已在我的最愛內";}else{echo "favorite";} ?>" type="submit" <?php if($table->user_id != NULL) {echo "disabled";} ?> >
              </form>
<?php
              if($_SESSION['in_use_is_admin'] == 1){
?>  
              <form method="post" action="user.php">
                <input type="hidden" name="delete_house_by_button" value="<?php echo $table->id; ?>">
                <input class="adjust" value="delete" type="submit">
              </form>
<?php
              }
 ?>  
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
