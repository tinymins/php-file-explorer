<?php
/**
 * @name: TmsFileManager
 * @author: tinymins@gmail.com
 * @version: 2013-07-25 v0.1.2
 * @link: Http://WwW.ZhaiYiMing.CoM
 */
set_time_limit(0);
@$response_type = $_REQUEST['rt'];
@$current_dir_relative = str_replace(array(':','|'),'',$_REQUEST['cd']);
if(empty($current_dir_relative)) $current_dir_relative = '';
//(realpath(".")
$tfm = new TmsFileManager($current_dir_relative);

switch(strtolower($response_type)){
	case 'json':
		break;
	case 'xml':
		break;
	case 'debug':
		header('Content-Type: text/plain; charset=utf-8');
		echo "\n------\ncurrent_dir\n";echo $tfm->current_dir_relative."\n".$tfm->current_dir_fullpath;
		echo "\n------\npath_hide\n";print_r($tfm->sub_path_hide);
		echo "\n------\ndir_vitual\n";print_r($tfm->sub_dir_vitual);
		echo "\n------\nsub_dir\n";print_r($tfm->sub_dir);
		echo "\n------\nsub_file\n";print_r($tfm->sub_file);
		break;
	default:
		$template = <<<END
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no"/>
	<title>{title}</title>
</head>
<body>
	<H1>{headline}</H1>
	<hr>
	<pre><A HREF="{parent_href}">[To Parent Directory]</A><br><br>{body}</pre>
	<hr>
	<div style="font-size:12px">Author: 翟一鸣tinymins 2013.07.24 <a style="color:red" href="http://www.zhaiyiming.com">Http://WwW.ZhaiYiMing.CoM/</a></div>
</body>
</html>
END;
		$template = str_replace('{title}', htmlspecialchars('TMS FILE EXPLORER - '.iconv('gbk','utf-8',$tfm->current_dir_relative)), $template);
		$template = str_replace('{headline}', htmlspecialchars('TMS FILE EXPLORER - '.iconv('gbk','utf-8',$tfm->current_dir_relative)), $template);
		$template = str_replace('{parent_href}', '?cd='.urlencode(substr($tfm->current_dir_relative,0,strrpos(substr($tfm->current_dir_relative,0,-1),'/'))), $template);
		$body = '';
		foreach($tfm->sub_dir_vitual as $filename=>$file){
			$body .= sprintf(' %s %10s %10s <A HREF="?cd=%s">%s</A><br>',
				date('Y/m/d h:i:s',$file['time']),
				$file['type'],
				$tfm->format_file_size($file['size']),
				urlencode($tfm->current_dir_relative.$filename),
				iconv('gbk','utf-8',$filename)
			);
		}
		foreach($tfm->sub_dir as $file){
			$body .= sprintf(' %s %10s %10s <A HREF="?cd=%s">%s</A><br>',
				date('Y/m/d h:i:s',$file['time']),
				$file['type'],
				$tfm->format_file_size($file['size']),
				urlencode($tfm->current_dir_relative.$file['name']),
				iconv('gbk','utf-8',$file['name'])
			);
		}
		foreach($tfm->sub_file as $file){
			$body .= sprintf(' %s %10s %10s <A HREF="?cd=%s">%s</A><br>',
				date('Y/m/d h:i:s',$file['time']),
				$file['type'],
				$tfm->format_file_size($file['size']),
				urlencode($tfm->current_dir_relative.$file['name']),
				iconv('gbk','utf-8',$file['name'])
			);
		}
		$template = str_replace('{body}', $body, $template);
		echo $template;
		break;
}

class TmsFileManager{
	var $current_dir_relative;
	var $current_dir_fullpath;
	var $sub_path_hide = array();
	var $sub_dir_vitual = array();
	var $sub_dir = array();
	var $sub_file = array();
	
	function __construct($current_dir_relative,$auto_load = true) {	#构造函数
		$current_dir_relative = str_replace('\\','/',$current_dir_relative);
		$current_dir_relative = str_replace('../','',$current_dir_relative);
		if(substr($current_dir_relative,-1)!='/') $current_dir_relative.='/';
		$this->current_dir_relative = $current_dir_relative;
		if($auto_load) {
			$this->current_dir_fullpath = $this->realpath($current_dir_relative);
			if(is_file(substr($this->current_dir_fullpath,0,strrpos($this->current_dir_fullpath,'/')))) {
				$this->echo_file(substr($this->current_dir_fullpath,0,strrpos($this->current_dir_fullpath,'/')));
				exit;
			}
			$this->load_config_file($this->current_dir_fullpath.'.conf');
			$this->scan_dir($this->current_dir_fullpath);
		}
	}
	/**
	 * 加载目录配置文件
	 * @param string $config_file_path 配置文件绝对路径
	 * @return bool 是否成功加载
	 */
	function load_config_file($config_file_path){
		@$file = fopen($config_file_path,'r');
		if($file){
			$config_var_name = '';
			while(! feof($file)){
				$s_line = trim(fgets($file));
				switch(strtolower($s_line)){
				case '[vitualdir]':
					$config_var_name = 'sub_dir_vitual';
					break;
				case '[hide]':
					$config_var_name = 'sub_path_hide';
					break;
				default:
					if(empty($s_line)) break;
					switch($config_var_name){
						case '':
						break;
						case 'sub_path_hide':
							$this->sub_path_hide []= $s_line;
						break;
						case 'sub_dir_vitual':
							$filename = substr($s_line,0,strpos($s_line,'|'));
							$file_location = substr($s_line,strpos($s_line,'|')+1);
							if(!is_dir($file_location)) break;
							$this->sub_dir_vitual [$filename]= array(
								'path'=>$file_location,
								'size'=>'NULL',
								'time'=>filemtime($file_location),
								'type'=>'vitual dir',
							);
						break;
					}
					break;
				}
			}
			fclose($file);
			return true;
		}
		return false;
	}
	/**
	 * 扫描目录 变量文件文件夹
	 * @param string $scan_dir 扫描的目录
	 * @return bool 扫描成功与否
	 */
	function scan_dir($scan_dir){
		$scan_dir = $this->realpath($scan_dir);
		if(!is_dir($scan_dir)) return false;
		$filelist = scandir($scan_dir); # 得到该文件下的所有文件和文件夹
		foreach($filelist as $filename){#遍历
			$file_location=$scan_dir."/".$filename;#生成路径
			
			$b_skip_dir = false;
			foreach($this->sub_path_hide as $k=>$v){
				if($v==$filename) $b_skip_dir = true;
			}
			if($filename=="." || $filename==".." || $b_skip_dir) {
				continue;
			}elseif(is_dir($file_location)) { #判断是不是文件夹
				$this->sub_dir []= array(
					'name'=>$filename,
					'size'=>'NULL',
					'time'=>filemtime($file_location),
					'type'=>filetype($file_location),
				);
			} elseif(is_file($file_location)) { #判断是不是文件
				$this->sub_file []= array(
					'name'=>$filename,
					'size'=>filesize($file_location),
					'time'=>filemtime($file_location),
					'type'=>filetype($file_location),
				);
			}
		}
		#uasort($this->sub_dir, 'strcasecmp');
		$this->sub_dir = array_merge($this->sub_dir);
		#uasort($this->sub_file, 'strcasecmp');
		$this->sub_file = array_merge($this->sub_file);
		return true;
	}
	
	/**
	 * (通过虚拟路径)计算绝对路径
	 * @param string $path_org (可能是)虚拟路径
	 * @return string 实际绝对路径
	 */
	function realpath($path_org){
		if(substr($path_org,1,1)!=':')
			$path = realpath('.').'/'; 
		else {
			$path =  substr($path_org,0,strpos($path_org,'/')+1);
			$path_org = substr($path_org,strpos($path_org,'/')+1);
		}
		if(substr($path_org,0,1)=='/') $path_org = substr($path_org,1);
		
		while(!empty($path_org)){
			$next_sub_dir = '';
			if(strpos($path_org,'/')) {
				$next_sub_dir = substr($path_org,0,strpos($path_org,'/'));
				$path_org = substr($path_org,strpos($path_org,'/')+1);
			} else {
				$next_sub_dir = $path_org;
				$path_org = '';
			}
			$tfm = new TmsFileManager($path,false);
			$tfm->load_config_file($path.".conf");
			if(!empty($tfm->sub_dir_vitual[$next_sub_dir])){
				$real_path = $tfm->sub_dir_vitual[$next_sub_dir]['path'];
				if(substr($real_path, 1,1)==':') {
					$path = $real_path;
				}else {
					if(substr($real_path,0,1)=='/') $real_path = substr($real_path, 1);
					$path = realpath($path.$real_path).'/';
				}
			}
			else
				$path .= $next_sub_dir . '/';
		}
		if(substr($path, -1)!='/') $path.='/';
		return $path;
	}
	
	/**
	 * 向用户(浏览器)发送文件
	 * @param string $fullpath 文件完整路径
	 * @return void
	 */
	function echo_file($fullpath){
		$filename	=substr($fullpath, strrpos($fullpath, '/'));
		$filesize	=filesize($fullpath);
		$filemime	="";
		# write header information
		$mimes = array(
			'323'	=>'text/h323',
			'acx'	=>'application/internet-property-stream',
			'ai'	=>'application/postscript',
			'aif'	=>'audio/x-aiff',
			'aifc'	=>'audio/x-aiff',
			'aiff'	=>'audio/x-aiff',
			'asf'	=>'video/x-ms-asf',
			'asr'	=>'video/x-ms-asf',
			'asx'	=>'video/x-ms-asf',
			'au'	=>'audio/basic',
			'avi'	=>'video/x-msvideo',
			'axs'	=>'application/olescript',
			'bas'	=>'text/plain',
			'bcpio'	=>'application/x-bcpio',
			'bin'	=>'application/octet-stream',
			'bmp'	=>'image/bmp',
			'c'	=>'text/plain',
			'cat'	=>'application/vnd.ms-pkiseccat',
			'cdf'	=>'application/x-cdf',
			'cer'	=>'application/x-x509-ca-cert',
			'class'	=>'application/octet-stream',
			'clp'	=>'application/x-msclip',
			'cmx'	=>'image/x-cmx',
			'cod'	=>'image/cis-cod',
			'cpio'	=>'application/x-cpio',
			'crd'	=>'application/x-mscardfile',
			'crl'	=>'application/pkix-crl',
			'crt'	=>'application/x-x509-ca-cert',
			'csh'	=>'application/x-csh',
			'css'	=>'text/css',
			'dcr'	=>'application/x-director',
			'der'	=>'application/x-x509-ca-cert',
			'dir'	=>'application/x-director',
			'dll'	=>'application/x-msdownload',
			'dms'	=>'application/octet-stream',
			'doc'	=>'application/msword',
			'dot'	=>'application/msword',
			'dvi'	=>'application/x-dvi',
			'dxr'	=>'application/x-director',
			'eps'	=>'application/postscript',
			'etx'	=>'text/x-setext',
			'evy'	=>'application/envoy',
			'exe'	=>'application/octet-stream',
			'fif'	=>'application/fractals',
			'flr'	=>'x-world/x-vrml',
			'gif'	=>'image/gif',
			'gtar'	=>'application/x-gtar',
			'gz'	=>'application/x-gzip',
			'h'	=>'text/plain',
			'hdf'	=>'application/x-hdf',
			'hlp'	=>'application/winhlp',
			'hqx'	=>'application/mac-binhex40',
			'hta'	=>'application/hta',
			'htc'	=>'text/x-component',
			'htm'	=>'text/html',
			'html'	=>'text/html',
			'htt'	=>'text/webviewhtml',
			'ico'	=>'image/x-icon',
			'ief'	=>'image/ief',
			'iii'	=>'application/x-iphone',
			'ins'	=>'application/x-internet-signup',
			'isp'	=>'application/x-internet-signup',
			'jfif'	=>'image/pipeg',
			'jpe'	=>'image/jpeg',
			'jpeg'	=>'image/jpeg',
			'jpg'	=>'image/jpeg',
			'js'	=>'application/x-javascript',
			'latex'	=>'application/x-latex',
			'lha'	=>'application/octet-stream',
			'lsf'	=>'video/x-la-asf',
			'lsx'	=>'video/x-la-asf',
			'lzh'	=>'application/octet-stream',
			'm13'	=>'application/x-msmediaview',
			'm14'	=>'application/x-msmediaview',
			'm3u'	=>'audio/x-mpegurl',
			'man'	=>'application/x-troff-man',
			'mdb'	=>'application/x-msaccess',
			'me'	=>'application/x-troff-me',
			'mht'	=>'message/rfc822',
			'mhtml'	=>'message/rfc822',
			'mid'	=>'audio/mid',
			'mny'	=>'application/x-msmoney',
			'mov'	=>'video/quicktime',
			'movie'	=>'video/x-sgi-movie',
			'mp2'	=>'video/mpeg',
			'mp3'	=>'audio/mpeg',
			'mpa'	=>'video/mpeg',
			'mpe'	=>'video/mpeg',
			'mpeg'	=>'video/mpeg',
			'mpg'	=>'video/mpeg',
			'mpp'	=>'application/vnd.ms-project',
			'mpv2'	=>'video/mpeg',
			'ms'	=>'application/x-troff-ms',
			'mvb'	=>'application/x-msmediaview',
			'nws'	=>'message/rfc822',
			'oda'	=>'application/oda',
			'p10'	=>'application/pkcs10',
			'p12'	=>'application/x-pkcs12',
			'p7b'	=>'application/x-pkcs7-certificates',
			'p7c'	=>'application/x-pkcs7-mime',
			'p7m'	=>'application/x-pkcs7-mime',
			'p7r'	=>'application/x-pkcs7-certreqresp',
			'p7s'	=>'application/x-pkcs7-signature',
			'pbm'	=>'image/x-portable-bitmap',
			'pdf'	=>'application/pdf',
			'pfx'	=>'application/x-pkcs12',
			'pgm'	=>'image/x-portable-graymap',
			'pko'	=>'application/ynd.ms-pkipko',
			'pma'	=>'application/x-perfmon',
			'pmc'	=>'application/x-perfmon',
			'pml'	=>'application/x-perfmon',
			'pmr'	=>'application/x-perfmon',
			'pmw'	=>'application/x-perfmon',
			'pnm'	=>'image/x-portable-anymap',
			'pot,'	=>'application/vnd.ms-powerpoint',
			'ppm'	=>'image/x-portable-pixmap',
			'pps'	=>'application/vnd.ms-powerpoint',
			'ppt'	=>'application/vnd.ms-powerpoint',
			'prf'	=>'application/pics-rules',
			'ps'	=>'application/postscript',
			'pub'	=>'application/x-mspublisher',
			'qt'	=>'video/quicktime',
			'ra'	=>'audio/x-pn-realaudio',
			'ram'	=>'audio/x-pn-realaudio',
			'ras'	=>'image/x-cmu-raster',
			'rgb'	=>'image/x-rgb',
			'rmi'	=>'audio/mid',
			'roff'	=>'application/x-troff',
			'rtf'	=>'application/rtf',
			'rtx'	=>'text/richtext',
			'scd'	=>'application/x-msschedule',
			'sct'	=>'text/scriptlet',
			'setpay'	=>'application/set-payment-initiation',
			'setreg'	=>'application/set-registration-initiation',
			'sh'	=>'application/x-sh',
			'shar'	=>'application/x-shar',
			'sit'	=>'application/x-stuffit',
			'snd'	=>'audio/basic',
			'spc'	=>'application/x-pkcs7-certificates',
			'spl'	=>'application/futuresplash',
			'src'	=>'application/x-wais-source',
			'sst'	=>'application/vnd.ms-pkicertstore',
			'stl'	=>'application/vnd.ms-pkistl',
			'stm'	=>'text/html',
			'svg'	=>'image/svg+xml',
			'sv4cpio'	=>'application/x-sv4cpio',
			'sv4crc'	=>'application/x-sv4crc',
			'swf'	=>'application/x-shockwave-flash',
			't'	=>'application/x-troff',
			'tar'	=>'application/x-tar',
			'tcl'	=>'application/x-tcl',
			'tex'	=>'application/x-tex',
			'texi'	=>'application/x-texinfo',
			'texinfo'	=>'application/x-texinfo',
			'tgz'	=>'application/x-compressed',
			'tif'	=>'image/tiff',
			'tiff'	=>'image/tiff',
			'tr'	=>'application/x-troff',
			'trm'	=>'application/x-msterminal',
			'tsv'	=>'text/tab-separated-values',
			'txt'	=>'text/plain',
			'uls'	=>'text/iuls',
			'ustar'	=>'application/x-ustar',
			'vcf'	=>'text/x-vcard',
			'vrml'	=>'x-world/x-vrml',
			'wav'	=>'audio/x-wav',
			'wcm'	=>'application/vnd.ms-works',
			'wdb'	=>'application/vnd.ms-works',
			'wks'	=>'application/vnd.ms-works',
			'wmf'	=>'application/x-msmetafile',
			'wps'	=>'application/vnd.ms-works',
			'wri'	=>'application/x-mswrite',
			'wrl'	=>'x-world/x-vrml',
			'wrz'	=>'x-world/x-vrml',
			'xaf'	=>'x-world/x-vrml',
			'xbm'	=>'image/x-xbitmap',
			'xla'	=>'application/vnd.ms-excel',
			'xlc'	=>'application/vnd.ms-excel',
			'xlm'	=>'application/vnd.ms-excel',
			'xls'	=>'application/vnd.ms-excel',
			'xlt'	=>'application/vnd.ms-excel',
			'xlw'	=>'application/vnd.ms-excel',
			'xof'	=>'x-world/x-vrml',
			'xpm'	=>'image/x-xpixmap',
			'xwd'	=>'image/x-xwindowdump',
			'z'	=>'application/x-compress',
			'zip'	=>'application/zip'
		);
		$mimes_forbidden = array(
			'php'=>'',
			'asp'=>'',
			'mdb'=>'',
		);
		$ext = substr($fullpath,strrpos($fullpath,'.')+1);
		if(array_key_exists($ext,$mimes_forbidden))
			die('Access Denied');
		elseif(array_key_exists($ext,$mimes))
			$filemime=$mimes[$ext];
		else
			$filemime='application/octet-stream';

		# start sending file
		ob_clean();header("content-type:$filemime");
		header("Content-Disposition:attachment;filename=$filename");
		header("Content-Length:$filesize");
		$handle = fopen($fullpath, "rb");
		while (!feof($handle)) {
			echo fread($handle, 8192);
		}
		fclose($handle);
	}
	
	/**
	 * 格式化文件大小信息
	 * @param mixed 文件大小(string|int|long)
	 * @return string 格式化后的文件大小
	 */
	function format_file_size($filesize){
		if(!is_numeric($filesize)){
			return $filesize;
		// } elseif ($filesize>>20) {
		} elseif (shr32($filesize,20)) {
			return shr32($filesize,20).'MB';
		} elseif (shr32($filesize,10)) {
			return (shr32($filesize,10)).'KB';
		} else {
			return $filesize.'B';
		}
	}
}

/**
 * 无符号32位右移
 * @param mixed $x 要进行操作的数字，如果是字符串，必须是十进制形式
 * @param string $bits 右移位数
 * @return mixed 结果，如果超出整型范围将返回浮点数
 */
function shr32($x, $bits){
    // 位移量超出范围的两种情况
    if($bits <= 0){
        return $x;
    }
    if($bits >= 32){
        return 0;
    }
    //转换成代表二进制数字的字符串
    $bin = decbin($x);
    $l = strlen($bin);
    //字符串长度超出则截取底32位，长度不够，则填充高位为0到32位
    if($l > 32){
        $bin = substr($bin, $l - 32, 32);
    }elseif($l < 32){
        $bin = str_pad($bin, 32, '0', STR_PAD_LEFT);
    }
    //取出要移动的位数，并在左边填充0
    return bindec(str_pad(substr($bin, 0, 32 - $bits), 32, '0', STR_PAD_LEFT));
}
/**
 * 无符号32位左移
 * @param mixed $x 要进行操作的数字，如果是字符串，必须是十进制形式
 * @param string $bits 左移位数
 * @return mixed 结果，如果超出整型范围将返回浮点数
 */    
function shl32 ($x, $bits){
    // 位移量超出范围的两种情况
    if($bits <= 0){
        return $x;    
    }
    if($bits >= 32){
        return 0;    
    }
    //转换成代表二进制数字的字符串
    $bin = decbin($x);
    $l = strlen($bin);
    //字符串长度超出则截取底32位，长度不够，则填充高位为0到32位
    if($l > 32){
        $bin = substr($bin, $l - 32, 32);
    }elseif($l < 32){
        $bin = str_pad($bin, 32, '0', STR_PAD_LEFT);
    }
    //取出要移动的位数，并在右边填充0
    return bindec(str_pad(substr($bin, $bits), 32, '0', STR_PAD_RIGHT));
}
?>