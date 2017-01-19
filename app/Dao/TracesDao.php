<?php

namespace App\Dao;

use Lib\PublicClass\{Log};
use App\Model\{Traces};

/**
 * 操作记录流水
 */
class TracesDao
{

    /**
     * @param   $id           mixed      被操作记录的id
     * @param   $action       string      操作行为
     *                                  1-新增  2-修改  3-审核
     * @param   $module       string      所属模块
     *                                  1-内部部门  2-客户
     * @param   $nodeId       string      商户号
     * @param   $data         array       备份数据
     * @param   $heap         bool        是否批量处理
     * @param   $run_flag     string      流水状态  1-进行中   2-结束
     * @return bool
     */
    public static function index($id, $action, $module, $data, $heap = false, $run_flag = '')
    {
        if(empty($id) || empty($action) || empty($module) || empty($data)) return false;

        $userInfo = session('user');

        $result = false;
        //文件上传批量新增的时候
        if($heap){
            $saveData = [];
            //内部部门
            if($module == '1' && $action == '1') {
                foreach ($id as $key => $value) {
                    foreach ($data as $key2 => $value2) {
                        if ($value['sort'] == $value2['sort']) {
                            $saveData[] = [
                                    'node_id'              => $userInfo['node_id'],
                                    'action'               => $action,
                                    'source_id'            => $value->id,
                                    'request_user_id'      => $userInfo['id'],
                                    'request_user_name'    => $userInfo['nickname'],
                                    'request_storage_id'   => $userInfo['storage_id'],
                                    'request_storage_name' => $userInfo['storage_name'],
                                    'request_data'         => json_encode($value2),
                                    'module'               => $module,
                                    'created_at'           => date('Y-m-d H:i:s'),
                                    'run_flag'             => (empty($run_flag) || $run_flag == '1') ? 1 : $run_flag
                            ];
                            break;
                        }
                    }
                }
            }
            //客户
            if($module == '2' && $action == '1') {
                foreach ($id as $key => $value) {
                    foreach ($data as $key2 => $value2) {
                        if ($value['seq'] == $value2['seq']) {
                            $saveData[] = [
                                    'node_id'              => $userInfo['node_id'],
                                    'action'               => $action,
                                    'source_id'            => $value->id,
                                    'request_user_id'      => $userInfo['id'],
                                    'request_user_name'    => $userInfo['nickname'],
                                    'request_storage_id'   => $userInfo['storage_id'],
                                    'request_storage_name' => $userInfo['storage_name'],
                                    'request_data'         => json_encode($value2),
                                    'module'               => $module,
                                    'created_at'           => date('Y-m-d H:i:s'),
                                    'run_flag'             => (empty($run_flag) || $run_flag == '1') ? 1 : $run_flag
                            ];
                            break;
                        }
                    }
                }
            }
            $result = Traces::insert($saveData);
        }else{
            //添加流水记录
            if($action == '1' || $action == '2'){
                $saveData = [
                        'node_id'               => $userInfo['node_id'],
                        'action'                => $action,
                        'source_id'             => $id,
                        'request_user_id'       => $userInfo['id'],
                        'request_user_name'     => $userInfo['nickname'],
                        'request_storage_id'    => $userInfo['storage_id'],
                        'request_storage_name'  => $userInfo['storage_name'],
                        'request_data'         => json_encode($data),
                        'module'                => $module,
                        'created_at'            => date('Y-m-d H:i:s'),
                        'run_flag'              => (empty($run_flag) || $run_flag == '1') ? 1 : $run_flag
                ];
                $result = Traces::insert($saveData);
            }
            //审核
            if($action == '3'){
                $saveData = [
                        'action'                => $action,
                        'approve_user_id'       => $userInfo['id'],
                        'approve_user_name'     => $userInfo['nickname'],
                        'approve_storage_id'    => $userInfo['storage_id'],
                        'approve_storage_name'  => $userInfo['storage_name'],
                        'approve_time'          => date('Y-m-d H:i:s'),
                        'approve_data'          => json_encode($data),
                        'run_flag'              => (empty($run_flag) || $run_flag == '2') ? 2 : $run_flag
                ];
                try{
                    $id = is_array($id) ? $id : [$id];
                    $result = Traces::whereIn('source_id',array_values($id))->where('run_flag',1)->where('module',$module)->update($saveData);
                }catch(\Exception $e){
                    $result = false;
                }
            }

        }

        if($result){
            return true;
        }else{
            $msg = '操作流水记录错误,id:['.print_r($id,1).'],action:['.print_r($action,1).'],module:['.print_r($module,1).'],data:['.print_r($data,1).'],商户号:'.$userInfo['node_id'];
            Log::log_write($msg,'','TracesDao');
            return false;
        }

    }

}