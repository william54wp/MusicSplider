<?php
namespace app\music\controller;

use app\music\api\weapi\NeteaseMusicAPI;

class Song
{


    public function Index()
    {
        return "music album index";
    }
    public function Detail($id = null)
    {
        $weapi = new NeteaseMusicAPI;
        $song = $weapi -> detail($id);
        return $song;
    }
    public function Url($id = null)
    {
        $weapi = new NeteaseMusicAPI;
        $song_url = $weapi -> url($id);
        $res = json_decode($song_url,1)['data'][0]['url'];
        return $res;
    }
}
