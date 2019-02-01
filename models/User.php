<?php

namespace app\models;

use app\controllers\behaviors\LogBehavior;
use yii\behaviors\TimestampBehavior;

class User extends \Da\User\Model\User
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            ['class' => TimestampBehavior::className()],
            ['class' => LogBehavior::className()],
        ];
    }
}
