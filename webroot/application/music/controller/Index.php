<?php
namespace app\music\controller;

use think\Controller;
use app\music\api\weapi\NeteaseMusicAPI;

class Index extends Controller
{
    public function index()
    {
        $this->assign('album_url', url('Music/Album/New'));
        $this->assign('albumDetail_url', url('Music/Album/Get'));
        $this->assign('downloadSpliderCmd',url('Music/Splider/downloadSpliderCmd'));
        return view();
    }
}
