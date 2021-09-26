<?php

namespace Swoolecan\Foundation\Services;

use Illuminate\Database\Schema\Blueprint;

trait TraitDatabaseService
{
    protected function createSql($table, $connection, $where = '', $extSetting = '')
    {
        $dumpPath = $this->config->get('local_params.resourcePath.dumpPath');
        $config = $this->config->get('database.connections.' . $connection);
        $sqlBase = "/usr/local/mysql/bin/mysqldump -h{$config['host']} -u{$config['username']} -P{$config['port']} -p'{$config['password']}' --default-character-set=utf8mb4 {{EXTSETTING}} --set-gtid-purged=off {$config['database']} {{TABLE}} {{WHERE}} > {$dumpPath}/{{TABLE}}.sql";
        //$where = '--where="id >= 5000001 AND id < 10000001';
        //$extSetting = ' --lock-tables=false'

        $sql = str_replace(['{{TABLE}}', '{{WHERE}}', '{{EXTSETTING}}'], [$table, $where, $extSetting], $sqlBase) . "\n";
        return $sql;
    }

    public function getTableDatas($schema, $connection, $withCurrentIncrement = false)
    {
        $results = $this->getConnection($connection)->table(\DB::raw('information_schema.TABLES'))->where('TABLE_SCHEMA', $schema)->get();
        $fields = [
            'name' => 'TABLE_NAME',
            'comment' => 'TABLE_COMMENT',
            'table_row' => 'TABLE_ROWS',
            'increment' => 'AUTO_INCREMENT',
            'created_at' => 'CREATE_TIME',
            'updated_at' => 'UPDATE_TIME',
        ];
        $datas = [];
        foreach ($results as $result) {
            $name = $result->TABLE_NAME;
            foreach ($fields as $field => $attr) {
                $value = $result->$attr;
                $value = in_array($field, ['row', 'increment']) ? intval($value) : $value;
                $datas[$name][$field] = $value;
            }
            if ($withCurrentIncrement) {
                $datas[$name]['currentIncrement'] = $this->currentIncrement($connection, $schema, $name);
            }
        }
        return $datas;
    }

    public function getColumnDatas($connection, $schema, $table = null, $where = null)
    {
        $where = is_null($where) ? [] : $where;
        $where['TABLE_SCHEMA'] = $schema;
        if (!is_null($table)) {
            $where['TABLE_NAME'] = $table;
        }
        //$results = $this->getConnection($connection)->table(\DB::raw('information_schema.COLUMNS'))->where($where)->whereIn('COLUMN_NAME', ['created_at', 'updated_at'])->get();
        //$results = $this->getConnection($connection)->table(\DB::raw('information_schema.COLUMNS'))->where($where)->where('COLUMN_NAME', 'like', '%_at')->get();
        $results = $this->getConnection($connection)->table(\DB::raw('information_schema.COLUMNS'))->where($where)->get();
        $fields = [
            'name' => 'COLUMN_NAME',
            'comment' => 'COLUMN_COMMENT',
            'table' => 'TABLE_NAME',
            'database' => 'TABLE_SCHEMA',
            'type' => 'DATA_TYPE',
            'default' => 'COLUMN_DEFAULT',
            'extra' => 'EXTRA',
        ];
        $datas = [];
        foreach ($results as $result) {
            $name = $result->COLUMN_NAME;
            foreach ($fields as $field => $attr) {
                if (is_null($table)) {
                    $datas[$result->TABLE_NAME][$name][$field] = $result->$attr;
                } else {
                    $datas[$name][$field] = $result->$attr;
                }
            }
        }
        return $datas;
    }

    public function currentIncrement($connection, $schema, $table, $onlyField = false)
    {
        $column = $this->getColumnDatas($connection, $schema, $table, ['EXTRA' => 'auto_increment']);
        if (empty($column)) {
            //echo 'noauto-----' . $table . "\n";
            return -1;
        }
        $column = array_values($column);
        $field = $column[0]['name'];
        if ($field != 'id') {
            //echo 'ffffffffff--' .$table . '==' . $field . "\n";
            $schemaTmp = str_replace('bak_', '', $schema);
            //echo "UPDATE `wp_data_sync` SET `auto_field` = '{$field}' WHERE `source_type` = '{$schemaTmp}' AND `code` = '{$table}';\n";
        }
        $info = $this->getConnection($connection)->SELECT("SELECT * FROM `{$schema}`.`{$table}` ORDER BY `{$field}` DESC LIMIT 1;");
        return empty($info) ? 0 : $info[0]->$field;
    }

    public function getIndexDatas($connection, $tableName, $pointIndex = 'primary')
    {
        $db = \Schema::connection($connection);
        $indexes = [];
        $db->table($tableName, function (Blueprint $table) use ($tableName, $db, & $indexes, $pointIndex) {
            $sm = $db->getConnection()->getDoctrineSchemaManager();
            $allIndexes = $sm->listTableIndexes($tableName);
            if (empty($pointIndex)) {
                $indexes = $allIndexex;
                return ;
            }
            $indexes = array_key_exists($pointIndex, $allIndexes) ? $allIndexes[$pointIndex] : false;

            return $indexes;
            /*if (array_key_exists('index_name', $indexesFound)) {
                $table->dropUnique("index_name");
            }*/
        });
        //$index = $this->getIndexDatas($db, $table);
        //$indexColumns = $index->getColumns();
        //print_R($indexColumns);exit();
        return $indexes;
    }

    protected function getConnection($connection)
    {
        return \DB::connection($connection);
    }
}
