<?php
/**
 * @var \yii\web\View $this
 */
?>
<table class="table table-bordered table-condensed table-striped">
    <thead>
        <tr>
            <td>asd</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
    </thead>
    <tbody>
        <?php foreach($items as $item) : ?>
        <tr>
            <td><?= $item['name'] ?></td>
            <td><?= $item['quantity'] ?></td>
            <td><?= $item['total_price_with_discount'] ?></td>
            <td><?= $item['total_price_without_discount'] ?></td>
            <td></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
