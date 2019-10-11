<?php
	
	//微信读书app爬虫

	//github : http://github.com/yyggttcc
	
	date_default_timezone_set('PRC'); 


	class readBook{

		public $url,$header,$result;
		
		//用户id 我的 376702916 信息等~
		public $vid,$skey,$accessToken;

		public $bookList = [];

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
				'feeds' => 'https://i.weread.qq.com/review/feeds?', // 想法
				'login' => 'https://i.weread.qq.com/login', //登录
				'bookSearch' => 'https://i.weread.qq.com/store/search?author=&authorVids=&categoryId=&maxIdx=0&type=0&v=2&outer=1&fromBookId=&scope=0&scene=0&keyword=', //图书搜索
				'bookInfo' => 'https://i.weread.qq.com/book/info?myzy=1&bookId=' , //图书信息
				'readingStat' => 'https://i.weread.qq.com/book/readingStat?readingList=2&bookId=', //今日在读
				'shelfAdd' => 'https://i.weread.qq.com/shelf/add', //加入书架
				'shelfBook' => 'https://i.weread.qq.com/shelf/friendCommon?minCount=0&synckey=0&userVid=' //书架
			);


			if(!$this->skey || !$this->accessToken){
				$this->login();
			}

			$userinfo = json_decode(file_get_contents('userinfo.txt'), true);

			$this->header = array(

				'accessToken: '.$userinfo['accessToken'],
				'vid: '.$userinfo['vid'],
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

		//初始化操作，你会关注我，然后给我点赞，接着发个私信~
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

			$header = $this->header;

			array_pop($header);

			$list = $this->curl($this->url['friendList'].time(),$header,'',false,true);

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
		//返回值{"userVid":66834388,"name":"鑫","gender":1,"avatar":"https://res.weread.qq.com/wravatar/WV0021-MeV5GdZHKNMwubrmPUjIx95/0","isV":0,"vDesc":"哈萨ki","isWeChatFriend":1,"isHide":1,"signature":"","location":""}
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
				$data = '{"content":"'.$info['from'].'","nick":"鑫_Robot","vDesc":"'.$info['hitokoto'].'"}';


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

			//35924840 找
			//66834388  鑫大号

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
					$data = '{"clientTime":'.time().rand(111,999).',"content":{"imgHeight":0,"imgWidth":0,"userProfile":{"avatarUrl":"https://res.weread.qq.com/wravatar/WV0021-MeV5GdZHKNMwubrmPUjIx95/0","finished":0,"like":7,"name":"鑫","readingTime":134200,"review":2,"signature":"哈萨ki","v":false,"vid":66834388}},"sid":"v_'.$vid.'","type":10}';

					break;

				case '13':

					$data = '{"clientTime":'.time().rand(111,999).',"content":{"imgHeight":0,"imgWidth":0,"link":{"abst":"..","key":"376702916_78EDf1Zm0","scheme":"weread://reviewDetail?reviewId=376702916_78EDf1Zm0&style=1&reviewType=5&isBookAuthor=0","title":"鑫的想法"}},"sid":"v_'.$vid.'","type":13}';
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
		
		public function bookComment($content ='写的很好' ,$reviewId = ''){

			$data = '{"content":"'.$content.'","reviewId":"'.$reviewId.'"}';

			$this->curl($this->url['bookComment'],'',$data,true);

			return $this;

		}

		//删除书评
		
		public function delComment($commentId ='20039553_738Bu4Q6M_c376702916_78EEydeJt' ){

			$data = '{"commentId":"'.$commentId.'"}';

			$this->curl($this->url['delComment'],'',$data,true);

			return $this;

		}
		//书本的点赞热评等信息
		//reviewId 热门讨论
		//likesCount=13 点赞次数
		//{"reviewId":"20039553_738Bu4Q6M","review":{"abstract":"这些加在一起，已经让张俊实际上控制了南宋的大半军队！","bookId":"22917962","bookVersion":0,"chapterUid":127,"content":"中兴四将里面，刘光世虽然无能，不善分辨时势，但本性良善，且他手下刘家军能人倍出，而刘光世也非常能容人。岳家军战无不胜，是南宋实力最强的部队。韩家军处在楚州，不过三万，多次以少胜多，退能守进可攻，但张俊除了早期有几次伐流匪战绩外，面对其他兵变叛乱，对抗金军无一不是以窝囊收场，张俊本人草包，又常为计较小利而险恶算计他人，甚至后来岳飞之死，他也扮演了重要的角色，但就因为这种人，听赵构话，所以最后把韩家军，部分岳家军都交给他，而岳飞，韩世忠累累战绩还不如一个投机者，这就是朝廷","htmlContent":"","isPrivate":0,"range":"6954-6980","title":"","type":1,"createTime":1539434326,"userVid":20039553,"reviewId":"20039553_738Bu4Q6M","isLike":0,"isReposted":0,"book":{"bookId":"22917962","format":"txt","version":0,"soldout":0,"bookStatus":2,"type":0,"cover":"https://wfqqreader-1252317822.image.myqcloud.com/cover/962/22917962/s_22917962.jpg","title":"如果这是宋史（新版套装全5册）","author":"高天流云","payType":12290},"chapterIdx":127,"chapterTitle":"收兵权","author":{"userVid":20039553,"name":"水绿杯秋","avatar":"http://wx.qlogo.cn/mmhead/V0mhkIwf3EH2FenpzjejJ4KrTPd8at6qQ7ibhw0ZRx86NICLWceYopA/0","isFollowing":0,"isFollower":0,"nick":"水绿杯秋"}},"likesCount":13,"likes":[{"userVid":66834388,"name":"鑫","avatar":"https://res.weread.qq.com/wravatar/WV0021-MeV5GdZHKNMwubrmPUjIx95/0","isFollowing":1,"isFollower":1,"isV":0,"vDesc":"哈萨ki"},{"userVid":77558337,"name":"金圣叹","avatar":"http://thirdwx.qlogo.cn/mmopen/vi_32/1IibribDyVlpJ2JDXq8zI6EXucKy4iaHd3ZXGWafm38OUwaYqCuTLRQLQIl4BM1m0yVZkM6dDZqBNrpz6uXfJlGtw/132","isFollowing":0,"isFollower":0,"nick":"金圣叹","isV":0,"vDesc":"吾日三省吾身"},{"userVid":27235477,"name":"天空洗雨","avatar":"https://res.weread.qq.com/wravatar/WV0013-Oq8KwlaVf~A4TE0Rq0AO~95/0","isFollowing":0,"isFollower":0,"nick":"天空洗雨"},{"userVid":207515759,"name":"芸芸众生皆平凡","avatar":"http://thirdwx.qlogo.cn/mmopen/vi_32/Yoww5mxBfZibXGzboS1HjKmH4UicricNdOhhUWtGcicLUic38JLycHYMahgU8c6icGhCJSess0jTiabkpqEuj3pqa3Slw/132","isFollowing":0,"isFollower":0,"nick":"芸芸众生皆平凡","isV":0,"vDesc":"平生无所夸，书酒乐年华。"},{"userVid":26938798,"name":"兰茵","avatar":"http://thirdwx.qlogo.cn/mmopen/vi_32/DYAIOgq83eqUicDfdGIGCU8AjjuiaYhfkTbZDQxG6W0rsK6hIXEX74wLibEq5a7oMic3QROff99L376OnwPI19TddQ/132","isFollowing":0,"isFollower":0},{"userVid":37077524,"name":"好心情","avatar":"https://res.weread.qq.com/wravatar/WV0022-Lzsp7y1oS35gBAZaiek4T8c/0","isFollowing":0,"isFollower":0,"isV":0,"vDesc":"我是山西省怀仁市人"},{"userVid":41565727,"name":"浪迹天涯","avatar":"http://thirdwx.qlogo.cn/mmopen/vi_32/VcVmAody4nnkAlhF25icMy577FYfxqoDRwqUjDPTPhWERal4plXibiaX4nf9c1DLv8GGw2zj2UsjZIUn6RqqxicFzg/132","isFollowing":0,"isFollower":0},{"userVid":9512624,"name":"William","avatar":"http://thirdwx.qlogo.cn/mmopen/vi_32/QEsPrrdMvoYUuQtmeTEvZvloRedxVmh5mfsBqJtbXoibdqAsPd2IgYCUHW4yjCR2yEWaiafFaj0dj4jToojyzXBQ/132","isFollowing":0,"isFollower":0},{"userVid":51406350,"name":"ALFTLGMS","avatar":"http://thirdwx.qlogo.cn/mmopen/vi_32/OkFdlgbIcS29C8IcBWuKf2r9QddXpxYb9vslowItRjRicqCjiaIfSkQjWqjOF9rVFVNgjfttr3YmS77DChKRiaVOA/132","isFollowing":0,"isFollower":0},{"userVid":50539257,"name":"马金鑫 Jason","avatar":"http://wx.qlogo.cn/mmhead/Q3auHgzwzM5Aic5rCjia63xR4qgCoWNEO7guQ3e9yCLoSgibVZlnludUA/0","isFollowing":0,"isFollower":0,"nick":"马金鑫 Jason","isV":0,"vDesc":"读书，健身，学英语"},{"userVid":28969355,"name":"Mr.L","avatar":"http://thirdwx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTJxwoNQS5ZCJvWPAas4icSAiamVrFpXXxicdLiaHIqj2SoovXoSu5oLCd9atbuXpG698YfYbh7BYVricNw/132","isFollowing":0,"isFollower":0,"isV":0,"vDesc":"读书使我内心安静，使我快乐"},{"userVid":5505618,"name":"徐波","avatar":"http://thirdwx.qlogo.cn/mmopen/vi_32/ibQA60nNv8AJhTu46nhr23S4lYUtyK3F2jcEE1bCD1d8pCvJnBeRo4bdOelNlO4CtnhUEVCASJzkdeS789vnibCQ/132","isFollowing":0,"isFollower":0},{"userVid":25827223,"name":"唔知叫咩名","avatar":"http://thirdwx.qlogo.cn/mmopen/vi_32/wmbOYhvGfpB7f92NUFxw3zjNlYYLZ0vASFuMF75QcGRyuB8ZS8nRVUHHC7S0UzXNJUxWico9hL4P7GdxqemUY8g/132","isFollowing":0,"isFollower":0,"isV":0,"vDesc":"公众号 有才小家 欢迎关注"}],"commentsCount":2,"hotComments":[],"comments":[{"content":"，，","reviewId":"20039553_738Bu4Q6M","userVid":376702916,"commentId":"20039553_738Bu4Q6M_c376702916_78EEo9XDv","createTime":1558862667,"author":{"userVid":376702916,"name":"鑫","avatar":"http://thirdwx.qlogo.cn/mmopen/vi_32/A5mWEuSVdDuRSrKl97f3n3uOqa8krmLJDpia7E8Oh4pdbf1oibK4gibKj8ICe5yf9AjOREAia7BIkYo31E6cf6GEaA/132","isFollowing":1,"isFollower":1,"isV":0,"vDesc":"1"}},{"content":"一直如此，不反就等死！","reviewId":"20039553_738Bu4Q6M","userVid":41565727,"commentId":"20039553_738Bu4Q6M_c41565727_78ECPDYlw","createTime":1558861241,"author":{"userVid":41565727,"name":"浪迹天涯","avatar":"http://thirdwx.qlogo.cn/mmopen/vi_32/VcVmAody4nnkAlhF25icMy577FYfxqoDRwqUjDPTPhWERal4plXibiaX4nf9c1DLv8GGw2zj2UsjZIUn6RqqxicFzg/132","isFollowing":0,"isFollower":0}}],"synckey":1558863189,"bookReviewCount":4827}
		public function bookInfo(){

			$this->curl($this->url['bookInfo']);

			return $this;

		}

		//摇一摇 暂时失效
		public function shake(){

			$this->curl($this->url['shake']."vid=".$this->vid."&firstUpgrade=0&skey=".$this->skey);

			return $this;

		}

		//想法
		//$num 最多30条
		public function feeds($num = 10 ,$type ='num'){

			if($type == 'time'){

				$time = time();

				$end_time = $time - 600;

				$url = $this->url['feeds']."synckey=".$time."&maxIdx=".$end_time."&listMode=1";

			}

			if($type == 'num'){

				//$num = $num > 30 ? 30 : $num;

				$url = $this->url['feeds']."count=".$num."&maxIdx=".time()."&listMode=1";

			}

			$header = $this->header;

			array_pop($header);

			$this->curl($url,$header);

			return $this;
		}

		//想法点赞
		public function feeds_like(){

			$i = 1;

			$arr = $this->tojson();

			if( count($arr['followings']) > 0) {

				foreach ($arr['followings'] as $k => $v) {

					echo $v['reviewId']."_\n";
					$this->bookLike($v['reviewId'])->bookComment('点赞评论小王子~',$v['reviewId']);

					echo $i."_\n";
					$i++;
				}


			}

		}

		//登录
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

  			//登录的核心 ，需要你自己获取，或者自己想手动修改token
			$data= '{"deviceId":"","inBackground":0,"":1,"":219,"refCgi":"//","refreshToken":"@4wS80pTMR4yOLPYlFrFU_gAA","signature":"","timestamp":'.$timestamp.',"trackId":"","wxToken":0}';

			$data = $this->curl($this->url['login'],$header,$data,true,true);
			
			$res = json_decode($data,true);
			

			file_put_contents('userinfo.txt', $data);

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

		//搜索图书
		//默认一百条
		public function bookSearch($bookName = '程序员' , $count = '100'){

			$url = $this->url['bookSearch'].urlencode($bookName).'&count='.$count;
			
			$header = $this->header;

			array_pop($header);

			$this->curl($url,$header);

		
			return $this;

		}

		//今日在读
		public function readingStat($bookid = ''){

			$header = $this->header;

			array_pop($header);

			$this->curl($this->url['readingStat'].$bookid,$header);

			return $this;

		}

		//加入书架
		public function shelfAdd($bookid = ''){

			$data = '{"bookIds":["'.$bookid.'"],"follow":0,"lectureBookIds":[],"promoteId":"BookStore_HotSearch"}';

			$this->curl($this->url['shelfAdd'],'',$data,true);

			return $this;

		}


		//查看书架
		public function shelfBook($vid = '252230748'){

			$header = $this->header;

			array_pop($header);

			$this->curl($this->url['shelfBook'].$vid,$header);

			return $this;

		

		}

		//从一个人的书架开始关注好友
		public function set2($vid= '252230748'){

			$this->shelfBook($vid);
			
			$i = 1;

			$arr = json_decode($this->result,true);

			if(count($arr['commonReadBooks'] > 0)){

				foreach ($arr['commonReadBooks'] as $k => $v) {


					if(!in_array($v['bookId'], $this->bookList) ){
						array_push($this->bookList, $v['bookId']);
					}else{
						continue;
					} 


					$res = $this->readingStat($v['bookId'])->tojson();
				
					//书？
					if( isset($res['readingList']) && count($res['readingList'])){
						foreach ($res['readingList'] as $k => $v) {
							$this->follow( $v['vid'] )->bookLike($v['reviewId']);

							$this->set2( $v['vid']);

							echo $i."_\n";
							$i++;
						}
					}

					//公众号
					if( isset($res['readingUsers']) && count($res['readingUsers'])){
						foreach ($res['readingUsers'] as $k => $v) {
							$this->follow( $v['user']['userVid'] )->bookLike($v['reviewId']);

							$this->set2( $v['user']['userVid'] );

							echo $i."_\n";
							$i++;
						}
					}


				}
			}



		}
		public function set(){

				$i = 1;

				$arr = json_decode($this->result,true);
				
				//循环搜索结果
				foreach ($arr['books'] as $key => $value) {

					$bookid = $value['bookInfo']['bookId'];

					if(!in_array($bookid, $this->bookList) ){
						array_push($this->bookList, $bookid);
					}else{
						continue;
					} 

					//加入书架
					$this->shelfAdd($bookid);

					$title = $value['bookInfo']['title'];


					$author = $value['bookInfo']['author'];

					//获取在读信息
					$res = $this->readingStat($bookid)->tojson();
				
					if($author == '公众号文集'){

						if($res['todayReadingCount'] > 0){

							$list = $res['readingUsers'];

							
						}

					}else{

						if($res['readingCount'] > 0){

							$list = $res['readingList'];

						}

					}

					if(count($list) > 0){

						foreach ($list as $k => $v) {
							
							$vid = $author == '公众号文集' ? $v['user']['userVid'] : $v['vid'];
							
							//随机执行时间
							//sleep(rand(5,15));

							//随机问候
							$msg =array(
								'嗨，我也在看'.$title.'，以后请多指教',
								'你好，我也在看'.$title.'，以后请多指教',
								'您好，我也在看'.$title.'，以后请多指教',
								'我也在看'.$title.'，以后请多指教',
								'我也在看'.$title.'，以后多多交流',
								'嗨，我也在看'.$title.'，相互关注点赞',
								'我也在看'.$title.'，互相关注点赞吧',
								'[微笑]，我也在看'.$title.'，我关注你了，互相点赞吧',
								'[微笑]，我也在看'.$title.'，关注我给你点赞',
								'[微笑]，我在看'.$title.'，我关注你了~',
							);

							//关注人 关注书
							$this->follow( $vid )->bookLike($v['reviewId']);//->sendMsg($vid , array_rand($msg));
							
							//$this->follow( $vid ) ->sendMsg($vid , '嗨，我也在看'.$title.'，以后请多指教');

							$i++;

							echo $i."_\n";

						}

					}

					
					
				}

				$this->wlog('成功关注点赞'.$i.'人');

		}

		//图书信息,搜索列表
		public function bookInfos($bookId = '' ){

			$this->curl($this->url['bookInfo'].$bookId);

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


			   /* if(strstr($dataInfo,'-2051')){

			    	echo $url."\n";

			    }
*/
			    $cmd_info =  (strlen($dataInfo) > 50 ? '内容太多暂不显示' : $dataInfo)."\r\n";

			    echo  iconv('UTF-8','GB18030',$cmd_info);
			   
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

		public function iconv($text){
			echo  iconv('UTF-8','GB18030',$text); exit;
		}

		//编码转换
		public function convert($type= 'GB18030' ){

			if($type === true){
				return iconv('UTF-8',$type,$this ->result);
			}
			
			$this ->result = iconv('UTF-8',$type,$this ->result);

			return  $this;

		}

		//输出成员方法
		public function output($name = ''){

			if(!empty($name)){

				if(is_array($this->$name)){
					print_r($this->$name); exit;
				}

				return  $this->$name;

			}

		}

		//json 转数组 
		public function tojson($data = ''){
		
			return json_decode(!empty($data) ?: $this->result ,true);

		}

		public static function main(){
			return new self;
		}

		//写入日志
		public function wlog($text=''){

			if($this->log || empty($text)){

				$text = empty($text) ? $this->result : $text;

				fwrite($this->file, $this->i.".  __   ".date('Y-m-d H:i:s',time())."__ msg: ".$text."\r\n");

				return $this;
			}
		}


	    public function __destruct(){
			$this->wlog();
		}


}

//初始操作，关注我，给我点赞，发消息
readBook::main()->init()->convert()->output('result'); exit;

//从一本书开始 关注
//readBook::main()->set2('252230748'); exit;


//排行点赞
/*readBook::main()->friendList();
exit;*/


//拉取最新想法并点赞
//readBook::main()->feeds(35)->feeds_like(); exit;


//从一本书开始关注好友
//readBook::main()->set2(); exit;


//查看一个人的书架
//readBook::main()->shelfBook(); exit;

//搜索图书并点赞关注看这本书的所有人
//readBook::main()->bookSearch("程序员")->set(); exit;

