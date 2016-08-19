<?php
/**
 * Created by solly [19.08.16 14:12]
 */

namespace insolita\migrik\tests\data;

/**
 * @db (db)
 * @table ({{%guyii_log}})
 **/
class HistoryItem
{
    /**
     * @var int $id @column pk()
    **/
    public $id;
    /**
     * @var string $route @column string(100)|notNull()
    **/
    public $route;
    /**
     * @var string $args @column string(1024)|notNull()|defaultValue("")
    **/
    public $args;
    /**
     * @column string(2048)|notNull()|defaultValue("")
     * @var string $options
     **/
    public $options;
    /**
     * @var string $output @column text()|notNull()|defaultValue("")|comment("Result")
     **/
    public $output;
    /**
     * @column (success) boolean()|notNull()|defaultValue(true)|comment("Is Success")
     * @var bool
     **/
    public $success;
    /**
     * @column(createdBy) integer()|null()|defaultValue(null)
     * @var string
     **/
    public $createdBy;
    /**
     * @var string $createdAt @column datetime(0)|notNull()|defaultExpression(CURRENT_TIMESTAMP)
     **/
    public $createdAt;
}