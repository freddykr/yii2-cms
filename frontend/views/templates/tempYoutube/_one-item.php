<?php
/**
 * User: Vladimir Baranov <phpnt@yandex.ru>
 * Git: <https://github.com/phpnt>
 * VK: <https://vk.com/phpnt>
 * Date: 12.12.2018
 * Time: 19:46
 */

use frontend\views\templates\tempYoutube\assets\YoutubeTempAsset;

/* @var $this \yii\web\View */
/* @var $page array Главная страница меню */
/* @var $parent array Родительская папка */
/* @var $item array Выбранный элемент */
/* @var $fieldsManage \common\components\other\FieldsManage */
$fieldsManage = Yii::$app->fieldsManage;
/* @var $youTubeData \phpnt\youtube\components\YouTubeData */
$youTubeData = Yii::$app->youTubeData;
$templateData = $fieldsManage->getData($item['id'], $item['template_id']);
YoutubeTempAsset::register($this);
?>
<div class="col-md-12">
    <?php p($this->viewFile); ?>
</div>
<div class="col-md-12">
    <div class="row">
        <div class="item-youtube sidebar-selected-item-content">
            <div class="col-md-12 text-center">
                <h1><?= Yii::t('app', $item['name']) ?></h1>
            </div>
            <div class="col-md-12">
                <?= Yii::t('app', $item['content']) ?>
            </div>
            <?php if ($item['template_id']): ?>
                <?php p($templateData) ?>
            <?php endif; ?>
        </div>
    </div>
</div>