<?php

namespace Swoolecan\Foundation\Services;

trait TraitDatabaseService
{
    public function dumpSql($connection)
    {
        $infos = $this->getModelObj('dataSync')->where(['source_type' => $connection])->get();
        $sql = '';
        foreach ($infos as $info) {
            if ($info->last_id - $info->sync_id == 0 || $info->sync_id == 0) {
                continue;
            }
            $sql .= '--- ' . ($info->last_id - $info->sync_id) . "\n";
            $sql .= $this->createSql($info['code'], $connection, $info->sync_id);
        }
        file_put_contents("/data/log/dealdata/{$connection}.sql", $sql);//, FILE_APPEND);
        echo $sql;
    }

    public function dumpSqlOld($schema, $connection = null)
    {
        foreach ($results as $table => $data) {
            $sql .= $this->createSql($table, $connection);
        }   
        file_put_contents("/data/log/dealdata/{$connection}.sql", $sql);//, FILE_APPEND);
        echo $sql;
        return true;
    }

    protected function createSql($table, $connection, $start = null)
    {
        $config = $this->config->get('database.connections.' . $connection);
        $sqlBase = "/usr/local/mysql/bin/mysqldump --skip-opt -h{$config['host']} -u{$config['username']} -P{$config['port']} -p'{$config['password']}' --default-character-set=utf8mb4 --set-gtid-purged=off {$config['database']} {{TABLE}} {{WHERE}} > /data/log/dealdata/{$config['database']}/{{TABLE}}.sql";
        $where = empty($start) ? '' : '--where="id >= ' . $start .' "';

        $sql = str_replace(['{{TABLE}}', '{{WHERE}}'], [$table, $where], $sqlBase) . "\n";
        return $sql;
    }

    public function recordDataSync($schema, $connection)
    {
        $tables = $this->getTableDatas($schema, $connection);
        $model = $this->getModelObj('bigdata-dataSync');
        $sql = 'INSERT INTO `wp_data_sync`(`code`, `source_type`, `name`, `last_num`, `last_id`, `created_at`, `updated_at`) VALUES';
        $updateSqlBase = "UPDATE `wp_data_sync` SET {{SET}} WHERE `code` = {{TABLE}} AND `source_type` = '{$schema}';";
        $updateSql = '';
        foreach ($tables as $table => $tData) {
            $where = ['code' => $table];
            $exist = $model->where($where)->first();
            if ($exist) {
                $setStr = "`last_id` = {$data['last_id']}";
                $updateSql .= str_replace(['{{SET}}', '{{TABLE}}'], [$setStr, $table], $updateSqlBase);
            } else {
                $sql .= "('{$data['code']}', '{$schema}', '{$data['name']}', '{$data['last_num']}', '{$data['last_id']}', '{$data['created_at']}', '{$data['updated_at']}'),";
            }
        }
        echo $sql;
        echo $updateSql;
    }

    public function getTableDatas($schema, $connection = null)
    {
        $db = $connection ? \DB::connection($connection) : \DB::connection();
        $results = $db->table(\DB::raw('information_schema.TABLES'))->where('TABLE_SCHEMA', $schema)->get();
        $fields = [
            'code' => 'TABLE_NAME',
            'name' => 'TABLE_COMMENT',
            'last_num' => 'TABLE_ROWS',
            'last_id' => 'AUTO_INCREMENT',
            'created_at' => 'CREATE_TIME',
            'updated_at' => 'UPDATE_TIME',
        ];
        $datas = [];
        foreach ($results as $result) {
            $code = $result->TABLE_NAME;
            foreach ($fields as $field => $attr) {
                $datas[$code][$field] = $result->$attr;
            }
        }
        return $datas;
    }
}
