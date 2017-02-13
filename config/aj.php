<?php
/**
 * Created by PhpStorm.
 * Author: Elena Kolevska
 * Date: 1/30/17
 * Time: 00:22
 */

return [
    'call_statuses_by_keyword' => [
        'not_called' => ['id'=>0, 'name'=>'not_called', 'label' => 'Not called yet'],
        'call_trigerred' => ['id'=>1, 'name'=>'call_trigerred', 'label' => 'Call triggered'],
        'call_in_progress' => ['id'=>2, 'name'=>'call_in_progress', 'label' => 'Call in progress'],
        'call_completed' => ['id'=>3, 'name'=>'call_completed', 'label' => 'Call completed'],
    ],
    'call_statuses_by_id' => [
        '0' => ['id'=>0, 'name'=>'not_called', 'label' => 'Not called yet'],
        '1' => ['id'=>1, 'name'=>'call_trigerred', 'label' => 'Call triggered'],
        '2' => ['id'=>2, 'name'=>'call_in_progress', 'label' => 'Call in progress'],
        '3' => ['id'=>3, 'name'=>'call_completed', 'label' => 'Call completed'],
    ]
];