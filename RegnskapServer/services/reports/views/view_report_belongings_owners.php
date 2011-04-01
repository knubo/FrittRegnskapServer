<h1>Eiendeler</h1>
<table class="tableborder">
<tr class="header">
<td>Eiendel</td>
<td>Serienr</td>
<td>Garantidato</td>
<td>Ansvarlig</td>
</tr>
<?php
 foreach($data as $one) {
    $class = $class == "showlineposts1" ? "showlineposts2" : "showlineposts1";
     
    ?>
<tr class="<?=$class?>">
<td><?=$one["belonging"]?></td>
<td><?=$one["serial"]?></td>
<td><?=$one["warrenty_date"]?></td>
<td><?=$one["firstname"]." ".$one["lastname"]?></td>
</tr>

    <?php
 }
?>
</table>