
//顶部导航栏功能按钮的类型
var TOPBAR_USERINFO = 'userinfo';
var TOPBAR_SEARCH = 'search';
var TOPBAR_SIGN = 'sign';
var TOPBAR_SCAN = 'scan';
var TOPBAR_FASTTEXT = 'fasttext';
var TOPBAR_FASTIMAGE = 'fastimage';
var TOPBAR_FASTCAMERA = 'fastcamera';
var TOPBAR_FASTAUDIO = 'fastaudio';
var TOPBAR_WEBAPP = 'webapp';
var TOPBAR_REFRESH = 'refresh';
var TOPBAR_BROWSER = 'browser';
var TOPBAR_COPY = 'copy';
var TOPBAR_SHARE = 'share';
var TOPBAR_ACTION = 'action';

//定义此方法（必须）
function connectSQJavascriptBridge(callback) {
    if (window.AppbymeJavascriptBridge) {
        window.sq = AppbymeJavascriptBridge;
        callback(AppbymeJavascriptBridge)
    } else {
        document.addEventListener('connectAppbymeJavascriptBridge', function() {
            window.sq = AppbymeJavascriptBridge;
            callback(AppbymeJavascriptBridge)
        }, false)
    }
}

sq3 = {

    /**
     * 打印
     */
    toast: function(p) {
        window.sq.utils.toBridge("toast", {
            text: p.text,
            isLong: (p.isLong == undefined) ? false : (p.isLong ? true : false),
            success: p.success,
            error: p.error,
            complete: p.complete
        });
    },
    /**
     * 分享
     */
    share: function(p) {
    	window.sq.utils.toBridge("share2", {
    		type: p.type, // 分享类型，wechat,qq,weibo,facebook,qqzone,wechattimeline,all
    		title: p.title, // 分享标题
    		content: p.content, // 分享内容
    		url: p.url, // 分享URL
    		picUrl: p.picUrl, // 分享图片URL
    		topicId: p.topicId, // 分享出去帖子ID
            aid: p.aid, //分享出去的文章ID
            boardId: p.boardId,
            success: p.success,
            error: p.error,
            complete: p.complete
        });
    },

    /**
     * 可分享类型：["wechat","qq","weibo","facebook","qqzone","wechattimeline"]
     */
    shareTypes: function(p) {
    	window.sq.utils.toBridge("shareTypes", {
            success: p.success,
            error: p.error,
            complete: p.complete
        });
    },

    /**
     * 预览图片
     */
    previewImage: function(p) {
    	window.sq.utils.toBridge("previewImage", {
    		urls: p.urls, // 图片地址列表，如：["http://1.jpg", "http://2.jpg", "http://3.jpg", "http://4.jpg", "http://5.jpg"]
    		currUrl: p.currUrl, // 当前展示的图片，如："http://3.jpg"
            success: p.success,
            error: p.error,
            complete: p.complete
        });
    },

    /**
     * 打开话题帖子列表
     */
    startTalkTopic: function(p) {
    	window.sq.utils.toBridge("startTalkTopic", {
    		topicId: p.topicId,
            success: p.success,
            error: p.error,
            complete: p.complete
        });
    },

    /**
     * 打开私信聊天窗口
     */
    startChat: function(p) {
        window.sq.utils.toBridge("startChat", {
            userId: p.userId,
            userName:p.userName,
            userAvator:p.userAvator,
            success: p.success,
            error: p.error,
            complete: p.complete
        });
    }, 

    /**
     * 打开图片选择器
     */
    startPhotoUpload: function(p) {
        window.sq.utils.toBridge("startPhotoUpload", {
            moduleType: p.moduleType,
            boardId: p.boardId,
            albumId: p.albumId,
            beforeAsyncProcess: p.beforeAsyncProcess,
            success: p.success,
            error: p.error,
            complete: p.complete
        });
    },

    publish: function(p) {
        window.sq.utils.toBridge("publish", {
            boardId: p.boardId,
            boardName: p.boardName,
            classifyId: p.classifyId,
            hasTitle: p.hasTitle,
            talkId: p.talkId,
            success: p.success,
            error: p.error,
            complete: p.complete
        });
    }

};
