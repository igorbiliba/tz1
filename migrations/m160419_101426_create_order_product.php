<?php

use yii\db\Migration;

class m160419_101426_create_order_product extends Migration
{
    public function up()
    {
        $this->createTable('order_product', [
            'id' => $this->primaryKey(),
            'product_id' => $this->integer()->notNull() . ' COMMENT "Ссылка на продукт"',
            'order_id' => $this->integer()->notNull() . ' COMMENT "Ссылка на заказ"',
            'count' => $this->integer()->notNull()->defaultValue(1) . ' COMMENT "Кол-во"',
        ]);

        $this->addForeignKey('order_product_product_fk', 'order_product', 'product_id', 'product', 'id');
        $this->addForeignKey('order_product_order_fk', 'order_product', 'order_id', 'order', 'id');
    }

    public function down()
    {
        $this->dropTable('order_product');
    }
}
