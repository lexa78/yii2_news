<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\News */

$this->params['breadcrumbs'][] = ['label' => 'News', 'url' => ['index']];
$this->params['breadcrumbs'][] = $oneNews->title;
\yii\web\YiiAsset::register($this);
?>
<div class="news-view">

  <h1><?= Html::encode($oneNews->title) ?></h1>
  <div>
      <?php
      if(!empty($oneNews->img)) :
      ?>
      <img src="pictures/<?= $oneNews->img ?>" alt="<?= $oneNews->title ?>" title="<?= $oneNews->title ?>" align="left">
      <?php
      endif;
      ?>
      <?= $oneNews->news_text ?>
  </div>
  <div>
    <a class="btn btn-info" href="<?= Yii::$app->urlManager->createUrl(['news/update', 'id' => $oneNews->id]); ?>">Редактировать</a>
  </div>

</div>
