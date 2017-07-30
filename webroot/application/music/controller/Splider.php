<?php
namespace app\music\controller;

use think\Controller;
use think\File;
use ZipArchive;
use app\music\api\weapi\NeteaseMusicAPI;
use app\music\api\zipFolder;
use app\music\controller\Album;

class Splider extends Controller
{
    public function _initialize()
    {
        $SpliderPath = config($_SERVER)["document_root"]."/splider/";
        $daypath     = $SpliderPath.date('Ymd');

        // 创建当日文件夹
        if (!file_exists($daypath)) {
            mkdir($daypath);
            mkdir("$daypath/download/");
            mkdir("$daypath/download/album");
            mkdir("$daypath/download/artist");
            mkdir("$daypath/download/mp3");
        }
    }

    public function Index()
    {
        return "Music Splider Index";
    }

    public function downloadSpliderCmd($album)
    {
        $res = [
            'status' => 'ok',
            'msg' => null
        ];
        if (count($album)>0) {
            $this->GetInfos($album);
            $res = $this->zipPackage();
        } else {
            $res =[
                'msg' => '未指定专辑!'
            ];
        }
        return json_encode($res, 320);
    }
    function zipPackage()
    {
        $zipFolder = new zipFolder;

        $SpliderPath = config($_SERVER)["document_root"]."/splider/";
        $daypath     = $SpliderPath.date('Ymd');

        if ($zipFolder->zipFolder($daypath, $daypath.'.zip')) {
            $res['status'] = "ok";
            $res=[
                'status'  => 'ok',
                'zipPath' => '/splider/'.date('Ymd').'.zip'
            ];
        } else {
            $res=['status' => "err",
            'msg'=>'zip file error'];
        }
        return $res;
    }
    function GetInfos($album)
    {
        $album_Info = $this->GetAlbumInfoFromApi($album['id']);
        // 生成专辑信息
        // 直接使用$album
        // 生成专辑图片下载脚本
        $album_pic_cmd[]= "wget {$album['picUrl']} -P ./download/album/  -N -t 3 -T 60\r\n" ;
        // 生成歌手信息
        $artist=$album['artist'];
        // 生成歌手图片下载脚本
        $artist_pic_cmd[]="wget {$album['artist']['picUrl']} -P ./download/artist/  -N -t 3 -T 60\r\n";
        // 生成歌曲信息
        foreach ($album_Info['detail']->songs as $song_info) {
            $song_url = $this->getSongurl($song_info->id);
            $songs[]=[
                'song_id'     => $song_info->id,
                'song_name'   => $song_info->name,
                'album_id'    => $song_info->al->id,
                'album_name'  => $song_info->al->name,
                'artist_id'   => $album['artist']['id'],
                'artist_name' => $album['artist']['name'],
                'starred'     => false,
                'popularity'  => $song_info->pop,
                'starred_Num' => 0,
                'played_Num'  => 0,
                'duration'    => $song_info->dt,
                'position'    => $song_info->pst,
                'score'       => 5,
                'mvid'        => 0,
                'mp3Url'      => $song_url
            ];
            // 生成歌曲下载脚本
            $songs_mp3_cmd[]= "wget {$song_url} -P ./download/mp3/  -N -t 3 -T 60\r\n";
            // 下载歌词
            $this->GetLyric($song_info->id);
        }
        // 写入专辑信息
        $this->genAlbumJsonFile('album_json.txt', $album);

        // 写入歌曲信息
        $this->genSongJsonFile('songs_json.txt', $songs);
        // 写入歌手信息
        $this->genArtistJsonFile('artist_json.txt', $artist);
        
        // 写入专辑图片下载脚本
        $this->genFile('album_pic_wget.cmd', implode("\r\n", $album_pic_cmd));
        // 写入歌手图片下载脚本
        $this->genFile('artist_pic_wget.cmd', implode("\r\n", $artist_pic_cmd));
        // 写入歌曲下载脚本
        $this->genFile('songs_mp3_wget.cmd', implode("\r\n", $songs_mp3_cmd));
    }
    function GetAlbumInfoFromApi($albumid)
    {
        $weapi = new NeteaseMusicAPI;
        $albumInfo_simple = json_decode($weapi->album($albumid));
        $albumInfo_detail = json_decode($weapi->album($albumid));
        $result =  [
            'simgple'=>$albumInfo_simple,
            'detail'=>$albumInfo_detail
        ];
        return $result;
    }
    function GetSongUrl($songid)
    {
        $weapi = new NeteaseMusicAPI;
        $song_detail = $weapi->url($songid);
        return json_decode($song_detail)->data[0]->url;
    }
    
    function genSongJsonFile($filename, $songs)
    {
        $SpliderRootPath = config($_SERVER)["document_root"]."/splider/";
        $DatePath        = $SpliderRootPath.date('Ymd');
        $filename        = "$DatePath/$filename";
        if (file_exists($filename)) {
            // 读取并追加
            $sourse_content = file_get_contents($filename);
            $data_content   = json_decode($sourse_content);
            // 检测是否重复
            foreach ($data_content->data as $key => $value) {
                $data_content_ids[]=$value->song_id;
            }
            foreach ($songs as $song) {
                if (!in_array($song['song_id'], $data_content_ids)) {
                    array_push($data_content->data, $song);
                }
            }
            file_put_contents($filename, json_encode($data_content, 320), LOCK_EX);
        } else {
            // 生成此文件
            $file_content = [
                'total'=>500,
                'data'=> $songs,
                'code'=>200
            ];
            if (!file_put_contents($filename, json_encode($file_content, 320), LOCK_EX)) {
                return json_encode([
                    'status'=>'err',
                    'msg' => '创建文件错误'
                ], 320);
            }
        }
        return json_encode([
            'status'=>'ok'
        ]);
    }
    function genArtistJsonFile($filename, $artist)
    {
        $SpliderRootPath = config($_SERVER)["document_root"]."/splider/";
        $DatePath        = $SpliderRootPath.date('Ymd');
        $filename        = "$DatePath/$filename";
        if (file_exists($filename)) {
            // 读取并追加
            $sourse_content = file_get_contents($filename);
            $data_content   = json_decode($sourse_content);
            // 检测是否重复
            foreach ($data_content->data as $key => $value) {
                $data_content_ids[]=$value->id;
            }
            if (!in_array($artist['id'], $data_content_ids)) {
                array_push($data_content->data, $artist);
            }
            file_put_contents($filename, json_encode($data_content, 320), LOCK_EX);
        } else {
            // 生成此文件
            $file_content = [
            'total'=>500,
            'data'=> [$artist],
            'code'=>200
            ];
            if (!file_put_contents($filename, json_encode($file_content, 320), LOCK_EX)) {
                return json_encode([
                'status'=>'err',
                'msg' => '创建文件错误'
                ], 320);
            }
        }
            return json_encode([
            'status'=>'ok'
            ]);
    }
    function genAlbumJsonFile($filename, $album)
    {
        $SpliderRootPath = config($_SERVER)["document_root"]."/splider/";
        $DatePath        = $SpliderRootPath.date('Ymd');
        $filename = "$DatePath/$filename";
        if (file_exists($filename)) {
            // 读取并追加
            $sourse_content = file_get_contents($filename);
            $data_content = json_decode($sourse_content);
            // 检测是否重复
            foreach ($data_content->data as $key => $value) {
                // dump($value->id);
                $data_content_albumids[]=$value->id;
            }
            if (!in_array($album['id'], $data_content_albumids)) {
                array_push($data_content->data, $album);
            }
            file_put_contents($filename, json_encode($data_content, 320), LOCK_EX);
        } else {
            // 生成此文件
            $file_content = [
            'total'=>500,
            'data'=> [$album],
            'code'=>200
            ];
            if (!file_put_contents($filename, json_encode($file_content, 320), LOCK_EX)) {
                return json_encode([
                'status'=>'err',
                'msg' => '创建文件错误'
                ], 320);
            }
        }
            return json_encode([
            'status'=>'ok'
            ]);
    }
    function genFile($filename, $content)
    {
        $SpliderRootPath = config($_SERVER)["document_root"]."/splider/";
        $DatePath        = $SpliderRootPath.date('Ymd');

        if (file_put_contents("$DatePath/$filename", $content, FILE_APPEND)) {
            return true;
        } else {
            return false;
        }
    }
    public function GetLyric($song_id)
    {
        $SpliderRootPath = config($_SERVER)["document_root"]."/splider/";
        $DatePath        = $SpliderRootPath.date('Ymd');
        
        $weapi = new NeteaseMusicAPI;
        $lyric = json_decode($weapi->lyric($song_id));
        
        if (property_exists($lyric, 'lrc')) {
            $lyric_content = json_decode($weapi->lyric($song_id))->lrc;
            $lyric_folder = "{$DatePath}/download/lyric/" . substr((string)$song_id, 0, 4). '/';

            if (!file_exists($lyric_folder)) {
                mkdir($lyric_folder, 0755, true);
            }

            $lyric_filename = 'media@id='.$song_id;
            
            if (file_put_contents($lyric_folder.$lyric_filename, json_encode($lyric_content, 320))>0) {
                return json_encode([
                'status' => 200,
                'msg'    => 'ok'
                ]);
            } else {
                return json_encode([
                'status' =>500,
                'msg'    => 'err 无法写入文件'
                ], 320);
            }
        } else {
            return json_encode([
            'status' => 200,
            'msg'    => '暂无歌词'
            ], 320);
        }
    }
}
