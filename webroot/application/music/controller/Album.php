<?php
namespace app\music\controller;

use app\music\api\weapi\NeteaseMusicAPI;

class Album
{
    public function Index()
    {
        return "music album index";
    }

    public function Get($id = null)
    {
        $weapi = new NeteaseMusicAPI;
        $album = $weapi -> album($id);
        return $album;
    }

    public function new($area = 'ZH', $limit = 30)
    {
        $weapi = new NeteaseMusicAPI;
        $res = $weapi->newalbum($area, $limit);
        $ablums = json_decode($res)->albums;
        return json_encode($ablums);
    }

    public function artistid($artistid)
    {
        $weapi = new NeteaseMusicAPI;
        $artist = $weapi->artist($artistid);
        dump(\json_decode($artist));
    }
    public function artistname($name){
        return $name;
    }
}
