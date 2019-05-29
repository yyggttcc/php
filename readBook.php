<?php
	
	//微信读书机器人，抓包实现了部分功能
	//git : http:// github.com/yyggttcc
	
	class readBook{

		public $url,$header,$result;
		
		//用户id 我的  信息等~
		public $vid,$skey,$accessToken;

		public $log = true,$file,$i = 1; //是否开启日志，文件句柄，计数器

		public function  __construct(){

			$this->url = array(
				'friendList' => 'https://i.weread.qq.com/friend/ranking?mine=0&synckey=', //读书排行榜
				'like' => 'https://i.weread.qq.com/friend/like', //读书排行点赞点赞取消点赞
				'follow' => 'https://i.weread.qq.com/friend/follow', //关注
				'userInfo' => 'https://i.weread.qq.com/user?userVid=', //用户资料
				'profile' => 'https://i.weread.qq.com/user/profile?gender=1&signature=1&vDesc=1&location=1&totalReadingTime=1&currentReadingTime=1&finishedBookCount=1&followerCount=1&followingCount=1&buyCount=1&reviewCount=1&reviewLikedCount=1&sameFollowing=1&reviewCommentedCount=1&likeBookCount=1&isFollowing=1&isFollower=1&isBlackMe=1&isInBlackList=1&bookReviewCount=1&noteBookCount=1&exchangedMoney=1&recentReadingBooks=1&booklistCount=1&booklistCollectCount=1&articleBookId=1&articleCount=1&articleDraftCount=1&articleReadCount=1&articleSubscribeCount=1&articleWordCount=1&audioCount=1&audioListenCount=1&audioLikedCount=1&audioCommentedCount=1&totalLikedCount=1&mpAccount=1&canExchange=1&isSubscribing=1&hideMe=1&wechatFriendCount=1&wechatFriendSubscribeCount=1&isHide=1&userVid=', //大概信息
				'modifySlef' => 'https://i.weread.qq.com/user/signature', //修改个人资料
				'sendMsg' => 'https://i.weread.qq.com/chat/send',//发送短消息
				'contributions' => 'https://i.weread.qq.com/fm/contributions?authorVid=', //跳转到用户个人中心
				'bookLike' => 'https://i.weread.qq.com/review/like', //书评点赞
				'bookComment' =>'https://i.weread.qq.com/review/comment', //书评 评论 
				'delComment' => 'https://i.weread.qq.com/review/delComment', //删除书评
				'bookInfo' => 'https://i.weread.qq.com/review/single?reviewId=20039553_738Bu4Q6M&bookReviewCount=1&synckey=1558862816&commentsCount=100&commentsDirection=1&likesCount=20&likesDirection=1',
				'shake' => 'https://weread.qq.com/wrpage/app/shake?', //摇一摇,暂时失效
				'feeds' => 'https://i.weread.qq.com/review/feeds?synckey=1558863189&maxIdx=1521161315&listMode=1', // 发现
				'login' => 'https://i.weread.qq.com/login', //登录
			);

			if(!$this->skey || !$this->accessToken){
				$this->login();
			}

			$this->header = array(

				'accessToken: '.$this->accessToken,
				'vid: '.$this->vid,
				'basever: 3.6.2.10136865',
				'channelId: 5',
				'beta: 0',
				'User-Agent: WeRead/3.6.2 WRBrand/Xiaomi Dalvik/2.1.0 (Linux; U; Android 7.0; MI 5s MIUI/V9.6.1.0.NAGCNFD)',
				'osver: 7.0',
				'appver: 3.6.2.10136865',
				'Host: i.weread.qq.com',
				'Connection: Keep-Alive',
				'Accept-Encoding: gzip',

			);

			if($this->log){
				$this->file = fopen(date('m-d',time()).'.txt','a');
			}

			
		}

		//初始化操作，你会关注我，然后给我点赞，接着发个私信~,然后给自己换一个很浪漫的个人签名~
		//关注 点赞，发消息,修改个人资料
		public function init($vid = '66834388'){

			//关注
			$this->follow($vid)->like($vid)->sendMsg($vid,'嗨，我刚刚关注你了，以后请多指教~ [微笑]')->modifySlef();
			
			return $this;

		}

		//设置默认header
		public function set_header(){

			return array(

				'accessToken: '.$this->accessToken,
				'vid: '.$this->vid,
				'basever: 3.6.2.10136865',
				'channelId: 5',
				'beta: 0',
				'User-Agent: WeRead/3.6.2 WRBrand/Xiaomi Dalvik/2.1.0 (Linux; U; Android 7.0; MI 5s MIUI/V9.6.1.0.NAGCNFD)',
				'osver: 7.0',
				'appver: 3.6.2.10136865',
				'Host: i.weread.qq.com',
				'Connection: Keep-Alive',
				'Accept-Encoding: gzip',

			);

		}

		public function __set($name, $value){

		        $this->$name = $value;

		}

		//读书朋友排行榜列表

		public function friendList(){

			$list = $this->curl($this->url['friendList'].time(),'','',false,true);

			$list = json_decode($list,true);
		
			$i = 1;

			foreach ($list['ranking'] as $k => $v) {
				
				if($v['isLiked'] == '0'){

					$this->like($v['user']['userVid']);

					$i++;
				}

			}

			$this->result = '成功点赞'.$i.'个';

			return $this;

		}

		//微信朋友同意关注
		//type int 0关注，1取消关注
		public function follow($vid= '',  $type = '0'){

			$data = '{"isBlack":'.$type.',"isUnfollow":'.$type.',"vid":'.$vid.'}';

			$this->curl($this->url['follow'],'',$data,true);

			return $this;

		}

		//点赞，取消点赞
		//type int  1点赞， 0取消点赞
		public function like($vid = '',$type = '1'){

			$data = '{"like":'.$type.',"type":0,"vid":'.$vid.'}';

			$this->curl($this->url['like'],'',$data,true);

			return $this;
		}

		//好友资料
		//返回值{"userVid":66834388,"name":"","gender":1,"avatar":"https://res.weread.qq.com/wravatar/WV0021-MeV5GdZHKNMwubrmPUjIx95/0","isV":0,"vDesc":"哈萨ki","isWeChatFriend":1,"isHide":1,"signature":"","location":""}
		public function userInfo(){

			$this->curl($this->url['userInfo'].$this->vid);

			return $this;

		}
		//大概信息
		/*
		{"signature":描述,"location":"安道尔","gender":1,"registTime":1514032380,"isFollowing":1,"isFollower":1,"vDesc":"哈萨ki，","isNewDevice":false,"isSubscribing":1,"hideMe":0,"isHide":1,"isApply":0,"recentLoginTime":1558935880,"wechatFriendCount":239,"wechatFriendSubscribeCount":235,"followingCount":264,"followingListCount":29,"followerCount":244,"followerListCount":20,"likeBookCount":0,"finishedBookCount":1,"recentReadingBooks":["621731","907764","MP_WXS_3286016687"],"totalReadingTime":134206,"currentReadingTime":6,"reviewCount":0,"bookReviewCount":2,"reviewCommentedCount":0,"reviewLikedCount":0,"totalLikedCount":7,"audioListenCount":0,"audioLikedCount":0,"audioCommentedCount":0,"audioCount":0,"noteBookCount":18,"articleBookId":"","articleCount":0,"articleDraftCount":0,"articleWordCount":0,"sameFollowing":0}
		*/
		public function profile($vid = ''){

			$this->curl($this->url['userInfo'].$vid);

			return $this;

		}

		//修个人资料
		public function modifySlef($content='', $nick='',$vDesc=''){

			//同步微信头像
			if($content === true){

				$data = '{"clearAvatar":1}';

			}else{

				//调用一句话
				$info = $this->one();
				//修改昵称，描述
				//$data = '{"content":"'.$info['from'].'","nick":"_Robot","vDesc":"'.$info['hitokoto'].'"}';
				$data = '{"content":"'.$info['from'].'","vDesc":"'.$info['hitokoto'].'"}';

			}

			$this->curl($this->url['modifySlef'],'',$data,true);

			return $this;

		}

		//hitokoto and from
		public function one(){

			return  json_decode(file_get_contents('https://v1.hitokoto.cn/'),true);

		}

		//发送短消息
		//建议不要发送频率太快
		// text [表情]
		//type 1 文字 带中括号可以发表情， 
		//2 灰色居中消息提醒，
		//3 图片， 可发网络图片， 
		// 4图书
		// 10 分享用户
		// 13 分享想法

		public function sendMsg($vid='',$text ='hai~' , $type ='1'){


			$vid = empty($vid)? $this->vid : $vid; //不指定就给自己发消息
		
			switch ($type) {
				//图片 $text 图片地址，imgHeight 图片宽高
				case '3':
					$data = '{"clientTime":'.time().'000,"content":{"imgHeight":0,"imgUrl":"'.$text.'","imgWidth":0},"sid":"v_'.$vid.'","type":3}';
					break;
				
				case '4':

					$data = '{"clientTime":'.time().rand(111,999).',"content":{"book":{"author":"张玮","bookId":"23934901","bookStatus":1,"cover":"https://wfqqreader-1252317822.image.myqcloud.com/cover/901/23934901/s_23934901.jpg","format":"epub","payType":4097,"title":"历史的温度3：时代扑面而来，转瞬即成历史","type":1},"imgHeight":0,"imgWidth":0},"sid":"v_'.$vid.'","type":4}';

					break;
				case '10':
					$data = '{"clientTime":'.time().rand(111,999).',"content":{"imgHeight":0,"imgWidth":0,"userProfile":{"avatarUrl":"https://res.weread.qq.com/wravatar/WV0021-MeV5GdZHKNMwubrmPUjIx95/0","finished":0,"like":7,"name":"","readingTime":134200,"review":2,"signature":"哈萨ki","v":false,"vid":66834388}},"sid":"v_'.$vid.'","type":10}';

					break;

				case '13':

					$data = '{"clientTime":'.time().rand(111,999).',"content":{"imgHeight":0,"imgWidth":0,"link":{"abst":"..","key":"376702916_78EDf1Zm0","scheme":"weread://reviewDetail?reviewId=376702916_78EDf1Zm0&style=1&reviewType=5&isBookAuthor=0","title":"的想法"}},"sid":"v_'.$vid.'","type":13}';
					break;

				default:
					$data = '{"clientTime":'.time().rand(111,999).',"content":{"imgHeight":0,"imgWidth":0,"text":"'.$text.'"},"sid":"v_'.$vid.'","type":'.$type.'}';

					break;
			}

			$this->curl($this->url['sendMsg'],'',$data,true);

			return $this;

		}

		//书评点赞
		//reviewId 书评id
		//type  0点赞，1取消点赞
		public function bookLike($reviewId ='' ,$type = '0'){

			$data = '{"isUnlike":'.$type.',"reviewId":"'.$reviewId.'"}';

			$this->curl($this->url['bookLike'],'',$data,true);

			return $this;

		}

		//书评 评论
		
		public function bookComment($content ='写的很好' ,$reviewId = '20039553_738Bu4Q6M'){

			$data = '{"content":"'.$content.'","reviewId":"'.$reviewId.'"}';

			$this->curl($this->url['bookComment'],'',$data,true);

			return $this;

		}

		//删除书评
		
		public function delComment($commentId ='' ){

			$data = '{"commentId":"'.$commentId.'"}';

			$this->curl($this->url['delComment'],'',$data,true);

			return $this;

		}
		//书本的点赞热评等信息
		//reviewId 热门讨论
		//likesCount=13 点赞次数
		
		public function bookInfo(){

			$this->curl($this->url['bookInfo']);

			return $this;

		}

		//摇一摇 暂时失效
		public function shake(){

			$this->curl($this->url['shake']."vid=".$this->vid."&firstUpgrade=0&skey=".$this->skey);

			return $this;

		}

		//发现
		public function feeds(){

			$this->curl($this->url['feeds']);

			return $this;
		}

		//登录 请手动获取data的数值，用于计算token
		public function login(){

			$header =array(
				'basever: 3.6.2.10136865',
				'channelId: 5',
				'beta: 0',
				'User-Agent: WeRead/3.6.2 WRBrand/Xiaomi Dalvik/2.1.0 (Linux; U; Android 7.0; MI 5s MIUI/V9.6.1.0.NAGCNFD)',
				'osver: 7.0',
				'appver: 3.6.2.10136865',
				'Content-Type: application/json; charset=UTF-8',
				'Content-Length: 317',
				'Host: i.weread.qq.com',
				'Connection: Keep-Alive',
				'Accept-Encoding: gzip',

			);

			list($t1, $t2) = explode(' ', microtime()); 

			//13位时间戳
  			$timestamp = (float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000); 

  			//请手动获取data的数值，用于计算token
			$data= '{"deviceId":"","inBackground":0,"kickType":1,"random":219,"refCgi":"/discover/interact","refreshToken":"","signature":"","timestamp":'.$timestamp.',"trackId":"","wxToken":0}';

			$data = $this->curl($this->url['login'],$header,$data,true,true);
			
			$res = json_decode($data,true);
			
			$this ->vid = $res['vid']; 
			$this ->skey = $res['skey']; 
			$this ->accessToken = $res['accessToken']; 

			return $this;

		}

		//跳转到用户中心,没什么卵用
		public function contributions($vid=''){

			$vid = empty($vid)? $this->vid : $vid;

			$this->curl($this->url['contributions'].$vid);

			return $this;
		}

		//curl 方法
		public function curl($url,$header='',$data='',$post = false, $return = false){

				if(empty($header)){
					$header = $this->header;
				}

				$curl = curl_init (); // 启动一个CURL会话
			  
			    curl_setopt ( $curl, CURLOPT_URL, $url ); // 要访问的地址
			    curl_setopt ( $curl, CURLOPT_SSL_VERIFYPEER, 0 ); // 
			    @curl_setopt ( $curl, CURLOPT_FOLLOWLOCATION, 1 ); // 使用自动跳转
			    curl_setopt ( $curl, CURLOPT_AUTOREFERER, 1 ); // 自动设置Referer
			    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

			    curl_setopt ( $curl, CURLOPT_POST, $post ); // 发送一个常规的Post请求
			    
			    if($post === true){
			    	
			    	curl_setopt ( $curl, CURLOPT_POSTFIELDS, $data ); // Post提交的数据包

			    }

			    curl_setopt ( $curl, CURLOPT_TIMEOUT, 30 ); // 设置超时限制防止死循环
			    curl_setopt ( $curl, CURLOPT_HEADER, 0 ); // 显示返回的Header区域内容
			    curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 ); // 获取的信息以文件流的形式返回
			    
			    $dataInfo = curl_exec ( $curl ); // 执行操作
			    
			    if (curl_errno ( $curl )) {
			        echo 'Errno' . curl_error ( $curl );
			    }

			    curl_close ( $curl ); // 关闭CURL会话

			    //未登录 就登录
			    if(strstr($dataInfo,'登录失败')){

			    	$this->login()->curl($url,$header,$data);

			    }

			    echo $dataInfo."\r\n";

			    if($return){
			    	return $dataInfo;
			    }
			    
			    $this->result = $dataInfo;


			    return $this;


		}
		
		public function run(){

			$url = 'https://i.weread.qq.com/friend/ranking?synckey=1558852716&mine=0';

			$this->result = $this->curl($url);

			return $this;

		}

		//编码转换
		public function convert($type= 'GB18030' ){

			$this ->result = iconv('UTF-8',$type,$this ->result);

			return  $this;

		}

		//输出 某个值
		public function output($name){

			if(is_array($this->$name)){
				print_r($this->$name); exit;
			}

			return  $this->$name;

		}

		public static function main(){
			return new self;
		}

		//写入日志
		public function wlog($text=''){

			if($this->log){

				$text = empty($text) ? $this->result : $text;

				fwrite($this->file, $this->i.".  __   ".date('Y-m-d H:i:s',time())."__ msg: ".$text."\r\n");

				return $this;
			}
		}


	    public function __destruct(){
			$this->wlog();
		}


}


echo  readBook::main()->init()->convert()->output('header'); exit;

