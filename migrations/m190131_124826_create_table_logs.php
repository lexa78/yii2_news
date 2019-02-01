<?php

use yii\db\Migration;

/**
 * Class m190131_124826_create_table_logs
 */
class m190131_124826_create_table_logs extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $this->createTable('logs', [
            'id' => $this->primaryKey(),
            'created_at' => $this->dateTime()->notNull(),
            'changed_by' => $this->integer()->notNull(),
            'model' => $this->string()->notNull(),
            'field' => $this->string()->notNull(),
            'old_val' => $this->text()->notNull(),
            'new_val' => $this->text()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-logs-user_id',
            'logs',
            'changed_by',
            'user',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $this->dropTable('logs');
    }
}
