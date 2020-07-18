<?php

use Carbon\Carbon;
use yii\db\Migration;

class m170428_223745_temp_pgsql extends Migration
{
    public function safeUp()
    {

        $this->createTable(
            'migrik_special',
            [
                'id'            => $this->char(30)->unique()->notNull(),
                'arrField'      => 'int[]',
                'arrField2'     => 'text[]',
                'arrField3'     => "int[] NULL DEFAULT '{2,3,4,5,6}'",
                'arrField4'     => "text[] NOT NULL DEFAULT '{this, some, test, data}'",
                'arrField5'      => 'int[2] NOT NULL',
                'arrField6'      => 'int[][] NOT NULL',
                'jsonField'     => 'JSON NULL',
                'jsonField2'    => "JSON DEFAULT '".json_encode(['one' => 'foo', 'two' => 'bar'])."'",

            ]
        );
    }

    public function safeDown()
    {
        $this->dropTable('migrik_special');
    }
}
