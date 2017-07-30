<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
    '__pattern__' => [
        'name' => '\w+',
        'id'   => '\d+'
    ],
    '[hello]'     => [
        ':id'   => ['index/hello', ['method' => 'get'], ['id' => '\d+']],
        ':name' => ['index/hello', ['method' => 'post']],
        'test' => ['index/test', ['method' => 'get']],
    ],
    '[consult]'=>[
        ':id'=>['Consult/Consult/Show',['method'=>'get']]
    ],
    '[music]'=>[
        // 'album/:id'=>['Music/Album/Get'],
        'Song/:id'=>['Music/Song/Detail'],
        'Url/:id'=>['Music/Song/Url']
    ]
];