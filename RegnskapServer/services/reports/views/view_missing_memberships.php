<h1 style="white-space: nowrap;">Manglende medlemskap <?=$title?></h1>
<table class="tableborder">
    <tr class="header">
        <td>Fornavn</td>
        <td>Etternavn</td>
        <td>Medlemsnummer</td>
    </tr>
    <?php
    foreach ($data as $one) {
        $class = $class == "showlineposts1" ? "showlineposts2" : "showlineposts1";

        ?>
        <tr class="<?=$class?>">
            <td ><?=$one["firstname"]?></td>
            <td><?=$one["lastname"]?></td>
            <td class="right"><?=$one["id"]?></td>
        </tr>

        <?php
    }
    ?>
</table>