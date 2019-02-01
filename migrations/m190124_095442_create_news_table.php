<?php

use yii\db\Migration;

/**
 * Handles the creation of table `news`.
 */
class m190124_095442_create_news_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('news', [
            'id' => $this->primaryKey(),
            'title' => $this->string(100)->notNull(),
            'img' => $this->string(50),
            'short_text' => $this->string()->notNull(),
            'news_text' => $this->text()->notNull(),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->datetime(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('news');
    }
}
