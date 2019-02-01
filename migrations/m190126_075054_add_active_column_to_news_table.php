<?php

use yii\db\Migration;

/**
 * Handles adding active to table `news`.
 */
class m190126_075054_add_active_column_to_news_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('news', 'is_active', $this->tinyInteger(1)->notNull()
            ->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('news', 'is_active');
    }
}
