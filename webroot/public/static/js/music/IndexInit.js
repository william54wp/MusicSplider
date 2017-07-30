var app = angular.module('music-splider', []);

app.service("Albums", function() {
    this.getNewAlbums = function($http, area, limit) {
        $http.post(album_New_Controller, { 'area': area, 'limit': limit }).then(function successCallback(response) {
            return response.data;
        });
    }
});

app.controller('myctrl', function($scope, $http, Albums) {

    $scope.Albums = [];
    $scope.Songs = [];

    // 默认采集专辑参数
    $scope.area = 'all';
    $scope.limit = 16;

    // 页面显示消息
    $scope.all = true;
    $scope.SongMsg = "";
    $scope.zipPath;

    // $scope.all = true;

    // 获取默认采集最新专辑
    // $scope.Albums = Albums.getNewAlbums($http, $scope.area, $scope.limit);

    $http.post(album_New_Controller, { 'area': $scope.area, 'limit': $scope.limit }).then(function successCallback(response) {
        $scope.Albums = response.data;
    });
    // 获取自定义采集最新专辑
    $scope.getNewAlbums = function() {
        $http.post(album_New_Controller, { 'area': $scope.area, 'limit': $scope.limit }).then(function successCallback(response) {
            $scope.Albums = response.data;
        });
    };

    // 获取专辑列表包含的歌曲
    $scope.getSong = function() {
        $scope.Songs = [];
        $scope.Albums.forEach(function(element, index) {
            // getMusic Func
            $http.post(album_Detail_Controller, { "id": element.id }).then(function successCallback(response) {
                songs = response.data;
                angular.forEach(songs, function(song, index) {
                    $scope.Songs.push(song);
                    $scope.SongMsg = "已解析到" + $scope.Albums.length + "个专辑和" + $scope.Songs.length + "首歌曲"
                });
            });
        }, this);
    };
    // 生成命令包
    $scope.CmdZipDown = function() {
        angular.forEach($scope.Albums, function(item, index) {
            $scope.SongMsg = "正在采集第" + (index + 1) + "个专辑:" + item.name;
            $http.post(splider_downloadSpliderCmd_Controller, { 'album': item }).then(function successCallback(response) {
                if (response.data.status == "ok") {
                    $scope.zipPath = response.data.zipPath;
                } else {
                    $scope.SongMsg = response.data.msg;
                }
            });
        });
    }

    $scope.chk_ops = function() {
        $scope.Albums.forEach(function(element, index) {
            element.check = !element.check;
        });
    };

    $scope.getSingerAlbums = function() {
        console.log($scope.singer);
    }
});