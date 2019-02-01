<?php

namespace app\controllers\behaviors;


use app\models\Logs;
use Yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;

class LogBehavior extends Behavior
{
    private static $MODEL_NAMES = ['app\models\News' => 'Новости', 'app\models\User' => 'Пользователи'];

    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeUpdate',
        ];
    }

    public function beforeUpdate($event) {
        $dirtyAttr = $this->owner->getDirtyAttributes();
        foreach ($dirtyAttr as $key => $attr) {
            if($key == 'updated_at') {
                continue;
            }
            $log = new Logs();
            $log->changed_by = Yii::$app->user->id;
            $log->model = self::$MODEL_NAMES[get_class($this->owner)];
            $log->field = $this->owner->attributeLabels()[$key];
            $log->old_val = $this->owner->getOldAttribute($key);
            $log->new_val = $attr;
            $log->save();
        }
    }
}