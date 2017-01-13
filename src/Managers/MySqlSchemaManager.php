<?php

namespace Larapack\DoctrineSupport\Managers;

use Doctrine\DBAL\Types\Type;
use Larapack\DoctrineSupport\Column;
use Doctrine\DBAL\Schema\MySqlSchemaManager as DoctrineMySqlSchemaManager;

class MySqlSchemaManager extends DoctrineMySqlSchemaManager
{
    protected function getPortableTableEnumColumnDefinition(array $tableColumn)
    {
        $tableColumn = array_change_key_case($tableColumn, CASE_LOWER);

        $dbType = strtolower($tableColumn['type']);
        $dbType = strtok($dbType, '(), ');

        $type = $this->_platform->getDoctrineTypeMapping($dbType);

        // In cases where not connected to a database DESCRIBE $table does not return 'Comment'
        if (isset($tableColumn['comment'])) {
            $type = $this->extractDoctrineTypeFromComment($tableColumn['comment'], $type);
            $tableColumn['comment'] = $this->removeDoctrineTypeFromComment($tableColumn['comment'], $type);
        }

        $options = array(
            'length'        => null,
            'unsigned'      => false,
            'fixed'         => null,
            'default'       => isset($tableColumn['default']) ? $tableColumn['default'] : null,
            'notnull'       => (bool) ($tableColumn['null'] != 'YES'),
            'scale'         => null,
            'precision'     => null,
            'autoincrement' => false,
            'comment'       => isset($tableColumn['comment']) && $tableColumn['comment'] !== ''
                ? $tableColumn['comment']
                : null,
        );

        $column = new Column($tableColumn['field'], Type::getType($type), $options);

        if (isset($tableColumn['collation'])) {
            $column->setPlatformOption('collation', $tableColumn['collation']);
        }

        $column->setCustomSchemaOption('options', $this->getEnumOptions($tableColumn));

        return $column;
    }

    protected function _getPortableTableColumnDefinition($tableColumn)
    {
        $keys = array_change_key_case($tableColumn, CASE_LOWER);

        $type = strtolower($keys['type']);
        $type = strtok($type, '(), ');

        $method = camel_case("get_portable_table_{$type}_column_definition");

        if (method_exists($this, $method)) {
            return $this->$method($tableColumn);
        }

        return parent::_getPortableTableColumnDefinition($tableColumn);
    }

    protected function getEnumOptions($tableColumn)
    {
        $type = $tableColumn['type'];

        if (starts_with($type, 'enum(') && ends_with($type, ')')) {
            return explode("','", trim(substr($type, strlen('enum('), -1), "'"));
        }

        return [];
    }
}