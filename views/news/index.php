<?php
/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\widgets\LinkPager;
use yii\widgets\Pjax;

$this->title = 'Новости';
$this->params['breadcrumbs'][] = $this->title;
?>

<h1><?= $this->title ?></h1>
<div>
  <form method="post" action="<?= Yii::$app->urlManager->createUrl('news/index'); ?>">
    На странице <input type="number" style="width: 35px;" name="user_news_on_page" value="<?= $pageSize ?>"> новостей
    <input type="submit" value="Установить">
      <?= Html:: hiddenInput(Yii::$app->getRequest()->csrfParam, Yii::$app->getRequest()->getCsrfToken()); ?>
  </form>
  <br><hr>
  <?php if( Yii::$app->user->can('manager') or Yii::$app->user->can('admin')) :?>
    <div>
      <a class="btn btn-success" href="<?= Yii::$app->urlManager->createUrl(['news/create']); ?>">Добавить новость</a>
    </div>
    <br><hr>
  <?php endif; ?>
  <div>
    <p>Фильтровать по:</p>
    <?php $filter = Yii::$app->session->get('filter'); ?>
    <form method="post" action="<?= Yii::$app->urlManager->createUrl('news/index'); ?>">
      <label>Названию&nbsp;&nbsp;&nbsp;<input type="text" name="name_filter"
                     value="<?= $filter['name_filter'] ?>"></label><br>
      <label>Описанию&nbsp;&nbsp;&nbsp;<input type="text" name="descr_filter"
                     value="<?= $filter['descr_filter'] ?>"></label><br>
      <label>Статусу&nbsp;&nbsp;&nbsp;
        <select name="status_filter">
          <option value="-1"></option>
          <option value="0"<?php if($filter['status_filter'] == '0') echo 'selected'?>>Неактивна</option>
          <option value="1"<?php if($filter['status_filter'] == 1) echo 'selected'?>>Активна</option>
        </select>
      </label><br>
      <label>Дате добавления C&nbsp;&nbsp;&nbsp;<input type="date" name="date_from_filter"
                     value="<?= $filter['date_from_filter'] ?>">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      По&nbsp;&nbsp;&nbsp;<input type="date" name="date_to_filter"
                     value="<?= $filter['date_to_filter'] ?>"></label><br>
      <input type="submit" value="Фильтровать"><br><br>
        <?= Html:: hiddenInput(Yii::$app->getRequest()->csrfParam, Yii::$app->getRequest()->getCsrfToken()); ?>
    </form>
    <form method="post" action="<?= Yii::$app->urlManager->createUrl('news/index'); ?>">
      <input type="submit" value="Очистить" name="clear">
        <?= Html:: hiddenInput(Yii::$app->getRequest()->csrfParam, Yii::$app->getRequest()->getCsrfToken()); ?>
    </form>
  </div>
  <hr>
</div>
<?php
foreach ($news as $oneNews):
    ?>
  <div style="float: left; height: 300px; width: 200px;">
    <h3><?= $oneNews->title ?></h3>
    <div>
      <?php
      if(!empty($oneNews->img)) :
      ?>
      <img src="pictures/<?= $oneNews->img ?>" alt="<?= $oneNews->title ?>" title="<?= $oneNews->title ?>" align="middle" width="100" height="150">
      <?php
        endif;
       ?>
        <?= $oneNews->short_text ?>
    </div>
    <?php if( ! Yii::$app->user->isGuest) :?>
      <div>
        <a class="btn btn-info" href="<?= Yii::$app->urlManager->createUrl(['news/read', 'id' => $oneNews->id]); ?>">Читать</a>
        <?php if( Yii::$app->user->can('manager') or  Yii::$app->user->can('admin')) :?>
          <button class="active-news btn btn-<?php if($oneNews->is_active) echo 'danger'; else echo 'success'?>" news-id="<?= $oneNews->id ?>">
              <?php if($oneNews->is_active) echo 'Деа'; else echo 'А'?>ктивировать
          </button>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
<?php
endforeach;
?>
<div style="clear: both;"></div>
<?= LinkPager::widget([
    'pagination' => $pages,
]); ?>

<script>
  $('.active-news').click(function () {
    var id = $(this).attr('news-id');
    $.ajax({
      url: "<?= Yii::$app->urlManager->createUrl(['news/activate', 'id' => '']); ?>"+id,
      success: function(data){
        data = JSON.parse(data);
        if(data.success) {
          var button = $('*[news-id = "'+data.success.id+'"]');
          button.removeClass(data.success.remove_class);
          button.addClass(data.success.add_class);
        } else {
          alert(data.error);
        }
      }
    });
  });
</script>
