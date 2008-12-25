<table class="report">
<?php
foreach($data as $one) {
    if($one["sum"] == 0) {
        continue;
    }
    $sum+=$one["sum"];

    $class = $class == "showlineposts1" ? "showlineposts2" : "showlineposts1";
?>
<tr class="<?=$class?>"><td><?=$one["post_type"]?></td><td><?=$one["description"]?></td><td><?=$one["sum"]?></td></tr>
<?php
}
?>
<tr class="sum"><td></td><td>SUM</td><td><?=ReportYear::fixNum($sum)?></td></tr>
</table>
