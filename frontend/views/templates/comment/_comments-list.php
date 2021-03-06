<?php
/**
 * User: Vladimir Baranov <phpnt@yandex.ru>
 * Git: <https://github.com/phpnt>
 * VK: <https://vk.com/phpnt>
 * Date: 19.01.2019
 * Time: 13:00
 */

/* @var $this yii\web\View */
/* @var $document_id int */
/* @var $comments array */
/* @var $access_answers boolean */
/* @var $commentsManage \common\widgets\Comment\components\CommentManage */

$commentsManage = Yii::$app->commentsManage;
?>
<div class="comment-list">
    <?php foreach ($comments as $comment): ?>
        <div class="comment-item">
            <?= $comment['text'] ?>
            <p><?= Yii::$app->formatter->asDate($comment['updated_at']) ?></p>
        </div>
        <div id="block-comment-update-form-<?= $comment['id'] ?>" class="m-b-md text-right">
            <?= $this->render('__buttons-comment', [
                'document_id' => $document_id,
                'comment_id' => $comment['id'],
                'user_id' => $comment['user_id'],
                'access_answers' => $access_answers,
            ]) ?>
        </div>
        <?php $commentsIn = $commentsManage->getChilds($comment['id']); ?>
        <?php if ($commentsIn): ?>
            <div class="comment-sub-item">
                <?= $this->render('_comments-list', [
                    'document_id' => $document_id,
                    'comments' => $commentsIn,
                    'access_answers' => $access_answers,
                ]) ?>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
