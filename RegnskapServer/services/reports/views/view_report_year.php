<table>
<?php
foreach($data as $one) {
    if($one["sum"] == 0) {
        continue;
    }
?>
<tr><td><?=$one["post_type"]?></td><td><?=$one["description"]?></td><td><?=$one["sum"]?></td></tr>
<?php
}
?>
