<?php

// 自动清理日志工具

/*
	-time 5  				设置为日志仅保留5天,会按照文件修改时间,删除5天前的日志
	-path /var/file/dir/   	设置要删除的目录,可以设置多个-path参数
	-file_ext 				log  设置要删除的后缀名

*/

//请在此处配置需要删除的目录路径,系统会自动递归查找并删除
$GLOBALS['path'] = array(
'/tmp',
);


//请在此添加需要检查的文件后缀名
$GLOBALS['file_ext'] = array(
'log',
);

if(count($argv)>1){

	//获取输入参数函数
	$paragm = get_paragm($argv);

	

	$paragm = load_conf($paragm);

	print_r($paragm);
	

	//删除日志
	del_log($paragm);

	


}else{
	echo '使用方法:
-time 5  				设置为日志仅保留5天,会按照文件修改时间,删除5天前的日志
-path /var/file/dir/   	设置要删除的目录,可以设置多个-path参数
示例:php auto_clear_log.php -time 5 -path /var/www/html/ccc 
';

}

/*
	载入
*/
function load_conf($paragm){
	
	//添加路径
	// $paragm['path'][] = '/tmp/';

	foreach ($GLOBALS['path'] as $key => $value) {
		$paragm['path'][] = $value;
	}
	


	return $paragm;
}


/*
	获取参数
*/
function get_paragm($argv){

	foreach ($argv as $key => $value) {

		if($value == '-time'){
			$data['time'] = $argv[$key+1];

			if(!is_numeric($data['time'])){
				die('-time 参数错误,必须为数字');
			}

		}

		if($value == '-path'){
			$data['path'][] = $argv[$key+1];
		}

		if($value == '-file_ext'){
			$data['file_ext'][] = $argv[$key+1];
		}



	}


	return $data;

}

/*
	删除日志函数
*/
function del_log($paragm){

		foreach ($paragm['path'] as $key => $value) {
			// print_r($value);
			// echo PHP_EOL;
			if(is_dir($value)){

				//递归检查目录
				$path_file = scandirFolder($value);

				print_r($value.PHP_EOL);

				check_log_file($paragm,$path_file,$value);


				

			}else{
				echo $value.' 目录不存在,请检查目录名称是否正确或带有特殊字符'.PHP_EOL;
			}
		}

}


/*
	检查符合条件的文件,
*/
function check_log_file($paragm,$path_file,$path){

	//是目录,调用本函数递归
	// print_r($path_file);exit;

	foreach ($path_file as $key => $value) {
		
		
		// print_r($value);
		
		if(is_dir($path.'/'.$key)){
			echo '进入目录:'.$path.'/'.$key.PHP_EOL;
			// print_r($path.'/'.$key.PHP_EOL);
			check_log_file($paragm,$value,$path.'/'.$key);
		}else{
			// echo '是文件,进行处理'.PHP_EOL;
			//检查文件后缀名是否符合要求,并把不符合要求的删除
			check_file_ext($paragm,$value,$path,$key);
		}

		clearstatcache();
	}



	// 是文件 检查是否符合


}

/*
	把符合条件的删除
*/
function check_file_ext($paragm,$path_file,$path,$file){
	
	

	//判断是否是数组还是一个文件
	if(is_array($path_file)){
		print_r($path_file);
		die('带入数组,停止');
		foreach ($path_file as $key => $value) {

			$file_name =  $path.'/'.$file.'/'.$value;
			file_del($file_name,$paragm);

		}
	}else{
		//单独的文件
		$file_name = $path.'/'.$path_file;
		// print_r($file_name.PHP_EOL);
	
		file_del($file_name,$paragm);
	}




	
}


function file_del($file_name,$paragm){
	
			//检查文件是否存在
			$file_exists = file_exists($file_name);

			//获取文件修改时间
			$file_time = filemtime($file_name);

			//获取后缀名
			$file_ext_name =  substr(strrchr($file_name, '.'), 1);
		
			//必须文件是存在的
			if($file_exists){

				


				if(isset($paragm['time'])){
					$time =  $paragm['time'];
				}else{
					// 默认为30天
					$time = 30;
				}

			
				if(in_array($file_ext_name, $GLOBALS['file_ext'])){
				


					//后缀名必须是指定的后缀名才行
					//修改时间必须小于  当前时间-86400*指定的天数 
					if($file_time < time()-(86400*$time)  ){

					
						@$is_del = unlink($file_name);

						if($is_del){
							show_error(1,$file_name,'成功删除文件');
						}else{
							show_error(-1,$file_name,'删除失败,请检查权限');
						}

						
					}else{
						show_error(-1,$file_name,'文件未过期');
					}

				}else{
					// show_error(1,$file_name,'文件名后缀不符合,跳过文件');
				}

			}else{
				show_error(-1,$file_name,'文件不存在或软链接指向的文件不存在');
			}
}

function show_error($status,$file_name,$msg){
	if($status>0){
		echo "\033[1;32;1;1m";
		echo $file_name.'        '.$msg;
		echo "\e[0m ".PHP_EOL;
	}else{
		
		echo "\033[1;31;1;1m";
		echo $file_name.'        '.$msg;
		echo "\e[0m ".PHP_EOL;
	}
}

/*
	递归获取文件夹
*/
function scandirFolder($path)
{
$list =array();
	//屏蔽权限不够的检查错误
    @$temp_list=scandir($path);
    if(!is_array($temp_list)){
    	return array();
    }

    foreach ($temp_list as $file)
    {
        //排除根目录
        if ($file != ".." && $file != ".")
        {
            if (is_dir($path . "/" . $file))
            {
                //子文件夹，进行递归
                $list[$file] = scandirFolder($path . "/" . $file);
            }
            else
            {
                //根目录下的文件
                $list[] = $file;
            }
        }
    }
    return $list;
}


echo PHP_EOL;


