<?php
namespace app\music\controller;

use app\music\api\weapi\NeteaseMusicAPI;

class Playerlist
{
    // 测试控制器
    public function Index()
    {
        return "music album index";
    }
   
    // 获取歌单信息
    public function Detail($id = null)
    {
        $weapi = new NeteaseMusicAPI;
        $album = $weapi -> playlist($id);
        return $album;
    }
}
