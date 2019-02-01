<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\News */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="news-form">

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?php
      if($update and !empty($model->img)) :
    ?>
        <img src="pictures/<?= $model->img ?>" alt="<?= $model->title ?>" title="<?= $model->title ?>" align="middle"
             width="100" height="150">
        <h4>Для смены картинки, загрузите новую.</h4>
    <?php
      endif;
    ?>
    <?= $form->field($model, 'img')->fileInput(['aria-required' => false]) ?>

    <?= $form->field($model, 'short_text')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'news_text')->textarea(['rows' => 8]) ?>

    <?= $form->field($model, 'is_active')->dropDownList([1 => 'Да', 0 => 'Нет']) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
