<table class="report">
<?php
 foreach(array_keys($data) as $key) {
 	$val = $data[$key];

    if($val["value"] == 0) {
    	continue;
    }
    ?>
<tr><td><?=$key?></td><td><?=$val["desc"]?></td><td><?=$val["value"]?></td></tr>

    <?php
 }
?>
</table>