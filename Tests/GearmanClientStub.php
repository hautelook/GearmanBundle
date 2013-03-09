<?php

define('GEARMAN_SUCCESS', 0);

class GearmanClient
{
    public function addOptions($options) {}
    public function addServer($host, $port) {}
    public function addServers($servers) {}
    public function addTask($function_name , $workload, &$context, $unique) {}
    public function addTaskBackground($function_name , $workload, &$context, $unique) {}
    public function addTaskHigh($function_name , $workload, &$context, $unique) {}
    public function addTaskHighBackground($function_name , $workload, &$context, $unique) {}
    public function addTaskLow($function_name , $workload, &$context, $unique) {}
    public function addTaskLowBackground($function_name , $workload, &$context, $unique) {}
    public function addTaskStatus($job_handle, &$context) {}
    public function clearCallbacks() {}
    public function __construct() {}
    public function context() {}
    public function data() {}
    public function doBackground($function_name , $workload, $unique = "1234") {}
    public function doHigh($function_name , $workload, $unique = "1234") {}
    public function doHighBackground($function_name , $workload, $unique = "1234") {}
    public function doJobHandle() {}
    public function doLow($function_name , $workload, $unique = "1234") {}
    public function doLowBackground($function_name , $workload, $unique = "1234") {}
    public function doNormal($function_name , $workload, $unique = "1234") {}
    public function doStatus() {}
    public function error() {}
    public function getErrno() {}
    public function jobStatus($job_handle) {}
    public function ping($workload) {}
    public function removeOptions($options) {}
    public function returnCode() {}
    public function runTasks() {}
    public function setClientCallback($callback) {}
    public function setCompleteCallback($callback) {}
    public function setContext($context) {}
    public function setCreatedCallback($callback) {}
    public function setData($data) {}
    public function setDataCallback($callback) {}
    public function setExceptionCallback($callback) {}
    public function setFailCallback($callback) {}
    public function setOptions($options) {}
    public function setStatusCallback($callback) {}
    public function setTimeout($timeout) {}
    public function setWarningCallback($callback) {}
    public function setWorkloadCallback($callback) {}
    public function timeout() {}
}
