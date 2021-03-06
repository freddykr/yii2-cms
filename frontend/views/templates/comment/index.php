<?php
/**
 * User: Vladimir Baranov <phpnt@yandex.ru>
 * Git: <https://github.com/phpnt>
 * VK: <https://vk.com/phpnt>
 * Date: 19.01.2019
 * Time: 9:49
 */

use phpnt\bootstrapNotify\BootstrapNotify;
use frontend\views\templates\comment\assets\CommentTempAsset;

/* @var $this yii\web\View */
/* @var $document_id int */
/* @var $comments array */
/* @var $access_answers boolean */

CommentTempAsset::register($this);
?>
<div id="block-comment-<?= $document_id ?>" class="comment m-t-lg">
    <?php BootstrapNotify::widget() ?>
    <div class="col-md-12 m-b-md">
        <h3>Комментарии:</h3>
    </div>
    <div class="col-md-12 m-b-md">
        <div class="slim-scroll-comments block-comments-height" data-height="600px">
            <?= $this->render('_comments-list', [
                'document_id' => $document_id,
                'comments' => $comments,
                'access_answers' => $access_answers
            ]) ?>
        </div>
    </div>
    <?php if (!Yii::$app->user->isGuest): ?>
    <div class="col-md-12">
        <div id="block-comment-add-form-<?= $document_id ?>">
            <?= $this->render('_button-new-comment', [
                'document_id' => $document_id,
                'access_answers' => $access_answers,
            ]) ?>
        </div>
    </div>
    <?php else: ?>
        <div class="col-md-12 m-t-md">
            <p><?= Yii::t('app', 'Комментарии могут оставлять только зарегистрированные пользователи.') ?></p>
        </div>
    <?php endif; ?>
</div>
<div class="clearfix m-b-lg"></div>
