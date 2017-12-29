<div id="personinfo">
  <p>Hello, <?php echo "$_SESSION[in_use_account]"; ?> ! </p>
  <table>
    <tbody>
      <tr>
        <th colspan="2">info</th>
      </tr>
      <tr>
        <td>name</td>
        <td><?php echo "$_SESSION[in_use_name]"; ?></td>
      </tr>
      <tr>
        <td>email</td>
        <td><?php echo "$_SESSION[in_use_email]"; ?></td>
      </tr>
    </tbody>
  </table>

  <p class="margin">
    <input type="button" onclick="location.href='logout.php'" value="logout"></input>
  </p>
</div>
