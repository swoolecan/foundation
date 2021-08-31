<?php

namespace Swoolecan\Foundation\Services;

trait TraitDatabaseService
{
    public function dumpSql($connection)
    {
        $infos = $this->getModelObj('dataSync')->where(['source_type' => $connection])->whereIn('status', ['9'])->get();
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

    public function dumpSqlOld($connection = null)
    {
        //$infos = $this->getModelObj('dataSync')->where(['source_type' => $connection, 'status' => ''])->get();
        $status = '';
        $status = 'fixedness';
        $status = '9';
        $infos = $this->getModelObj('dataSync')->where(['source_type' => $connection, 'status' => $status])->get();
        $sql = '';
        foreach ($infos as $info) {
            $sql .= $this->createSql($info['code'], $connection);
        }   
        file_put_contents("/data/log/dealdata/{$connection}{$status}.sql", $sql);//, FILE_APPEND);
        echo $sql;exit();
        return true;
    }

    protected function createSql($table, $connection, $start = null)
    {
        $config = $this->config->get('database.connections.' . $connection);
        $sqlBase = "/usr/local/mysql/bin/mysqldump -h{$config['host']} -u{$config['username']} -P{$config['port']} -p'{$config['password']}' --default-character-set=utf8mb4 --set-gtid-purged=off {$config['database']} {{TABLE}} {{WHERE}} > /data/wwwroot/happy-writing/public/docs/d/sql1/{{TABLE}}.sql";
        //$where = empty($start) ? '' : '--where="id >= ' . $start .' "';
        $where = '--where="id >= 5000001 AND id < 10000001';

        $sql = str_replace(['{{TABLE}}', '{{WHERE}}'], [$table, $where], $sqlBase) . "\n";
        return $sql;
    }

    public function recordDataSync($schema, $connection, $tables = null)
    {
        $tables = is_null($tables) ? $this->getTableDatas($schema, $connection) : $tables;
        //print_R($tables);exit();
        $model = $this->getModelObj('bigdata-dataSync');
        $sql = 'INSERT INTO `wp_data_sync`(`code`, `source_type`, `name`, `last_num`, `last_id`, `sync_id`, `created_at`, `updated_at`) VALUES';
        $updateSqlBase = "UPDATE `wp_data_sync` SET {{SET}} WHERE `code` = '{{TABLE}}' AND `source_type` = '{$schema}';\n";
        $updateSql = '';
        foreach ($tables as $table => $data) {
            $where = ['code' => $table];
            $exist = $model->where($where)->first();
            $lastId = intval($data['last_id']);
            if ($exist) {
                $syncId = $data['syncId'] ?? 0;
                $setStr = "`sync_id` = {$syncId}";
                //$setStr = "`last_id` = {$lastId}, `sync_id` = {$syncId}";
                $updateSql .= str_replace(['{{SET}}', '{{TABLE}}'], [$setStr, $table], $updateSqlBase);
            } else {
                $updatedAt = empty($data['updated_at']) ? date('Y-m-d H:i:s') : $data['updated_at'];
                $syncId = $data['syncId'] ?? 0;
                $sql .= "('{$data['code']}', '{$schema}', '{$data['name']}', '{$data['last_num']}', '{$lastId}', '{$syncId}', '{$data['created_at']}', '{$updatedAt}'),\n";
            }
        }
        echo trim(trim($sql), ',') . ';';
        echo $updateSql;
        exit();
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
            if (!in_array($code, ['tmp_order', 'tmp_order_id'])) {
                $info = $db->SELECT("SELECT * FROM `{$schema}`.`{$code}` ORDER BY `id` DESC LIMIT 1;");
                $datas[$code]['syncId'] = empty($info) ? 0 : $info[0]->id;
            }
        }
        return $datas;
    }
}
