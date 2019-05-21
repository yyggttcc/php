<?php

//与光同尘http://github.com/yyggttcc
class lg {

	public $url,$redis,$mysql,$cookie;
	public $pn = 1;

	public function __construct(){

		$this->url = 'https://www.lagou.com/jobs/positionAjax.json?needAddtionalResult=false';

	}

	public function main(){

		while (true) {
			$this->get_data();
			$this->pn++;
		}
	}

	//获取cookie 参数
	public function get_cookie(){

		if(!empty($this->cookie)){
			return true;
		}


		$headers = array(
			'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3',
			'Accept-Encoding: gzip, deflate, br',
			'Accept-Language: zh-CN,zh;q=0.9',
			'Cache-Control: no-cache',
			'Connection: keep-alive',
			'Host: www.lagou.com',
			'Pragma: no-cache',
			'Upgrade-Insecure-Requests: 1',
			'User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.157 Safari/537.36',
		);

		$url = 'https://www.lagou.com/jobs/list_php';

		$curl = curl_init (); // 启动一个CURL会话
	  
	    curl_setopt ( $curl, CURLOPT_URL, $url ); // 要访问的地址
	    curl_setopt ( $curl, CURLOPT_SSL_VERIFYPEER, 0 ); // 
	    @curl_setopt ( $curl, CURLOPT_FOLLOWLOCATION, 1 ); // 使用自动跳转
	    curl_setopt ( $curl, CURLOPT_AUTOREFERER, 1 ); // 自动设置Referer
	    curl_setopt ( $curl, CURLOPT_POST, 0 ); // 发送一个常规的Post请求
	    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
	    //curl_setopt ( $curl, CURLOPT_POSTFIELDS,  ); // Post提交的数据包
	    curl_setopt ( $curl, CURLOPT_TIMEOUT, 30 ); // 设置超时限制防止死循环
	    curl_setopt ( $curl, CURLOPT_HEADER, 1 ); // 显示返回的Header区域内容
	    curl_setopt ( $curl, CURLINFO_HEADER_OUT, 1 ); 
	    curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 ); // 获取的信息以文件流的形式返回
	    
	    $tmpInfo = curl_exec ( $curl ); // 执行操作
	    
	   // $head = curl_getinfo($curl) ; 


	    if (curl_errno ( $curl )) {
	        echo 'Errno' . curl_error ( $curl );
	    }

	   	preg_match_all('/Cookie: (.*?);/',$tmpInfo,$res);

	   //	print_r($res);exit;
	    $this->cookie = $res[1];
	   

	    return $this;


	}

	//采集内容
	public function get_data(){
		

		$headers = array(
			'Host: www.lagou.com',
			'Connection: keep-alive',
			'Content-Length: 23',
			'Pragma: no-cache',
			'Cache-Control: no-cache',
			'Origin: https://www.lagou.com',
			'X-Anit-Forge-Code: 0',
			'User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.157 Safari/537.36',
			'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
			'Accept: application/json, text/javascript, */*; q=0.01',
			'X-Requested-With: XMLHttpRequest',
			'X-Anit-Forge-Token: None',
			'Referer: https://www.lagou.com/jobs/list_php?labelWords=&fromSearch=true&suginput=',
			'Accept-Encoding: gzip, deflate, br',
			'Accept-Language: zh-CN,zh;q=0.9',
			'Cookie: '.$this->cookie[2].'; _ga=GA1.2.1202220755.1542122941; LGUID=20181113233146-3b666c68-e759-11e8-9da0-525400f775ce; LG_LOGIN_USER_ID=574dbf08ca12d826b2f82b14b8d9bf7424baf0eda7b62b4f; sensorsdata2015jssdkcross=%7B%22distinct_id%22%3A%22169ac2ea895227-095905ada0dd7e-414c042a-1327104-169ac2ea8965b3%22%2C%22%24device_id%22%3A%22169ac2ea895227-095905ada0dd7e-414c042a-1327104-169ac2ea8965b3%22%2C%22props%22%3A%7B%22%24latest_traffic_source_type%22%3A%22%E7%9B%B4%E6%8E%A5%E6%B5%81%E9%87%8F%22%2C%22%24latest_referrer%22%3A%22%22%2C%22%24latest_referrer_host%22%3A%22%22%2C%22%24latest_search_keyword%22%3A%22%E6%9C%AA%E5%8F%96%E5%88%B0%E5%80%BC_%E7%9B%B4%E6%8E%A5%E6%89%93%E5%BC%80%22%7D%7D; index_location_city=%E5%85%A8%E5%9B%BD; showExpriedIndex=1; showExpriedCompanyHome=1; showExpriedMyPublish=1; gate_login_token=973eaf71fbf820ab0c0864dca9608c4cc7d165d915d2b557; hasDeliver=1146; _gid=GA1.2.849461098.1558420814; '.$this->cookie[0].'; _putrc=8D960C58411A8B80; login=true; unick=%E9%BB%84%E9%91%AB; Hm_lvt_4233e74dff0ae5bd0a3d81c6ccf756e6=1556990090,1557802180,1558152938,1558434944; TG-TRACK-CODE=search_code; _gat=1; LGSID=20190521192545-2d115e06-7bbb-11e9-a10f-5254005c3644; PRE_UTM=; PRE_HOST=; PRE_SITE=https%3A%2F%2Fsec.lagou.com%2Fverify.html%3Fe%3D3%26f%3Dhttps%3A%2F%2Fwww.lagou.com%2Fjobs%2Flist_php%3FlabelWords%3D%26fromSearch%3Dtrue%26suginput%3D; PRE_LAND=https%3A%2F%2Fwww.lagou.com%2Fjobs%2Flist_php%3FlabelWords%3D; '.$this->cookie[1].'; '.$this->cookie[3].'; Hm_lpvt_4233e74dff0ae5bd0a3d81c6ccf756e6=1558437811; LGRID=20190521192808-8264fa50-7bbb-11e9-a10f-5254005c3644',
		);

		$data = 'first=false&pn'.$this->pn.'2&kd=php';

		$curl = curl_init (); // 启动一个CURL会话
	  
	    curl_setopt ( $curl, CURLOPT_URL, $this->url ); // 要访问的地址
	    curl_setopt ( $curl, CURLOPT_SSL_VERIFYPEER, 0 ); // 
	    @curl_setopt ( $curl, CURLOPT_FOLLOWLOCATION, 1 ); // 使用自动跳转
	    curl_setopt ( $curl, CURLOPT_AUTOREFERER, 1 ); // 自动设置Referer
	    curl_setopt ( $curl, CURLOPT_POST, 1 ); // 发送一个常规的Post请求
	    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
	    curl_setopt ( $curl, CURLOPT_POSTFIELDS, $data ); // Post提交的数据包
	    curl_setopt ( $curl, CURLOPT_TIMEOUT, 30 ); // 设置超时限制防止死循环
	    curl_setopt ( $curl, CURLOPT_HEADER, 0 ); // 显示返回的Header区域内容
	    curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 ); // 获取的信息以文件流的形式返回
	    
	    $tmpInfo = curl_exec ( $curl ); // 执行操作
	    
	    if (curl_errno ( $curl )) {
	        echo 'Errno' . curl_error ( $curl );
	    }

	    curl_close ( $curl ); // 关闭CURL会话

	    
	    $encode = json_decode($tmpInfo,true);

	    if($encode && isset($encode['status'])){
	    	echo "\n终止程序".$this->pn."\n";
	    	exit;
	    }
	    print_r(iconv('UTF-8','GB18030',$tmpInfo));

	    echo "\n".$this->pn."\n";
	   //sleep(2);

	    return $this;

	}

	public static function start(){
		return new self;
	}


}

lg::start()->get_cookie()->main();

