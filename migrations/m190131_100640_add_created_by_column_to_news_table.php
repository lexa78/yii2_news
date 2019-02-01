<?php

use yii\db\Migration;

/**
 * Handles adding created_by to table `news`.
 */
class m190131_100640_add_created_by_column_to_news_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('news', 'created_by', $this->Integer()->notNull());
        $this->addForeignKey(
            'fk-news-user_id',
            'news',
            'created_by',
            'user',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('news', 'created_by');
    }
}
