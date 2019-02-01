<?php

namespace app\models;

use HTMLPurifier;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;
use app\controllers\behaviors\LogBehavior;

/**
 * This is the model class for table "news".
 *
 * @property int $id
 * @property string $title
 * @property string $img
 * @property string $short_text
 * @property string $news_text
 * @property string $created_at
 * @property string $updated_at
 */
class News extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'news';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'short_text', 'news_text'], 'required'],
            [['news_text'], 'string'],
            [['is_active'], 'integer'],
            ['is_active', 'filter', 'filter' => 'intval'],
            [['title'], 'string', 'max' => 100],
            [['short_text'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Название',
            'img' => 'Картинка',
            'short_text' => 'Описание',
            'news_text' => 'Текст новости',
            'is_active' => 'Новость активна',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $this->title = self::purifier($this->title);
            $this->short_text = self::purifier($this->short_text);
            $this->news_text = self::purifier($this->news_text);
            return true;
        } else {
            return false;
        }
    }

    public static function purifier($text)
    {
        $pr = new HtmlPurifier;
        return $pr->purify($text);
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
                'value' => new Expression('NOW()'),
            ],
            [
                'class' => LogBehavior::className(),
            ],
        ];
    }
}
