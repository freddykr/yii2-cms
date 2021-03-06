<?php
/**
 * User: Vladimir Baranov <phpnt@yandex.ru>
 * Git: <https://github.com/phpnt>
 * VK: <https://vk.com/phpnt>
 * Date: 26.10.2018
 * Time: 21:47
 */

use yii\bootstrap\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $document_id int */
/* @var $likes int */
?>
<div id="like-widget-<?= $document_id ?>">
    <?= Html::a('<i class="fas fa-thumbs-up"></i> ' . $likes, 'javascript:void(0);', [
        'class' => 'btn btn-success',
        'onclick' => '
            $.pjax({
                type: "GET",
                url: "' . Url::to(['/rating/like', 'document_id' => $document_id, 'dislike' => false]) . '",
                container: "#like-widget-' . $document_id . '",
                push: false,
                timeout: 10000,
                scrollTo: false
            })'
    ]); ?>
</div>
