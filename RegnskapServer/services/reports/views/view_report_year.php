<table class="report">
<?php
foreach(array_keys($data) as $post_type) {
    if($data[$post_type]["value"] == 0) {
        continue;
    }
    $sum+=$data[$post_type]["value"];

    $class = $class == "showlineposts1" ? "showlineposts2" : "showlineposts1";
?>
<tr class="<?=$class?>"><td><?=$post_type?></td><td><?=$data[$post_type]["description"]?></td><td><?=$data[$post_type]["value"]?></td></tr>
<?php
}
?>
<tr class="sum"><td></td><td>SUM</td><td><?=ReportYear::fixNum($sum)?></td></tr>
</table>
