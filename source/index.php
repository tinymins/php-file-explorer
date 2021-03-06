<?php
/**
 * @name: TmsFileManager
 * @author: tinymins@gmail.com
 * @version: 2013-07-25 v0.1.3
 * @link: Http://WwW.ZhaiYiMing.CoM
 */
//报告运行时错误
// @error_reporting(E_ERROR | E_WARNING | E_PARSE);
@error_reporting(E_ERROR | E_PARSE);
@set_time_limit(0);
if(!array_key_exists('cd',$_REQUEST)) { echo_ui(); die(); }
@$response_type = $_REQUEST['rt'];
@$current_dir_relative = str_replace(array(':','|'),'',$_REQUEST['cd']);
if(empty($current_dir_relative)) $current_dir_relative = '';

$ted = new TmsEncoding();
$tfm = new TmsFileManager($current_dir_relative);
switch(strtolower($response_type)){
	case 'json':
        $response = array(
            'cd' => array(
                'name' => $tfm->current_dir_exist?$ted->iconv('utf-8',$tfm->current_dir_relative,"utf-8 gbk"):null,
                'url' => $tfm->current_dir_exist?urlencode($tfm->current_dir_relative):null,
            ),
            'pd' => array(),
            'sub_dir_vitual' => array(),
            'sub_dir' => array(),
            'sub_file' => array()
        );
        $dir_name_prefix = './';
        foreach( explode('/',trim($tfm->current_dir_relative,'.\\/')) as $dir_name ) {
            if(empty($dir_name)) continue;
            $response['pd'] []= array(
                'name' => $ted->iconv('utf-8',$dir_name,"utf-8 gbk"),
                'url' => urlencode($dir_name_prefix.$dir_name.'/'),
            );
            $dir_name_prefix .= $dir_name.'/';
        }
        foreach( $tfm->sub_dir_vitual as $filename=>$file ) { $response['sub_dir_vitual'] []= array(
            'name' => $ted->iconv('utf-8',$filename,"utf-8 gbk"),
            'size' => (int)$ted->iconv('utf-8',$file['size'],"utf-8 gbk"),
            'time' => (int)$ted->iconv('utf-8',$file['time'],"utf-8 gbk"),
            'url' => urlencode($tfm->current_dir_relative.$filename),
        ); }
        foreach( $tfm->sub_dir as $file ) { $response['sub_dir'] []= array(
            'name' => $ted->iconv('utf-8',$file['name'],"utf-8 gbk"),
            'size' => (int)$ted->iconv('utf-8',$file['size'],"utf-8 gbk"),
            'time' => (int)$ted->iconv('utf-8',$file['time'],"utf-8 gbk"),
            'url' => urlencode($tfm->current_dir_relative.$file['name']),
        ); }
        foreach( $tfm->sub_file as $file ) { $response['sub_file'] []= array(
            'name' => $ted->iconv('utf-8',$file['name'],"utf-8 gbk"),
            'size' => (int)$ted->iconv('utf-8',$file['size'],"utf-8 gbk"),
            'time' => (int)$ted->iconv('utf-8',$file['time'],"utf-8 gbk"),
            'url' => urlencode($tfm->current_dir_relative.$file['name']),
        ); }
        echo(json_encode($response));
		break;
	case 'xml':
		break;
	case 'debug':
		header('Content-Type: text/plain; charset=utf-8');
		echo "\n------\ncurrent_dir\n";echo $tfm->current_dir_relative."\n".$tfm->current_dir_fullpath;
		echo "\n------\npath_hide\n";print_r($tfm->sub_path_hide);
		echo "\n------\nsub_dir_vitual\n";print_r($tfm->sub_dir_vitual);
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
	<div style="font-size:12px">Author: 翟一鸣tinymins During:2013.07.24 - 2014.03.24 <a style="color:red" href="http://www.zhaiyiming.com">Http://WwW.ZhaiYiMing.CoM/</a></div>
</body>
</html>
END;
		$template = str_replace('{title}', htmlspecialchars('TMS FILE EXPLORER - '.$ted->iconv('utf-8',$tfm->current_dir_relative,"utf-8 gbk")), $template);
		$template = str_replace('{headline}', htmlspecialchars('TMS FILE EXPLORER - '.$ted->iconv('utf-8',$tfm->current_dir_relative,"utf-8 gbk")), $template);
		$template = str_replace('{parent_href}', '?cd='.urlencode(substr($tfm->current_dir_relative,0,strrpos(substr($tfm->current_dir_relative,0,-1),'/'))), $template);
		$body = '';
		if($tfm->current_dir_exist){
		foreach($tfm->sub_dir_vitual as $filename=>$file){
			$body .= sprintf(' %s %10s %10s <A HREF="?cd=%s">%s</A><br>',
				date('Y/m/d h:i:s',$file['time']),
				$file['type'],
				$tfm->format_file_size($file['size']),
				urlencode($tfm->current_dir_relative.$filename),
				$ted->iconv('utf-8',$filename,"utf-8 gbk")
			);
		}
		foreach($tfm->sub_dir as $file){
			$body .= sprintf(' %s %10s %10s <A HREF="?cd=%s">%s</A><br>',
				date('Y/m/d h:i:s',$file['time']),
				$file['type'],
				$tfm->format_file_size($file['size']),
				urlencode($tfm->current_dir_relative.$file['name']),
				$ted->iconv('utf-8',$file['name'],"utf-8 gbk")
			);
		}
		foreach($tfm->sub_file as $file){
			$body .= sprintf(' %s %10s %10s <A HREF="?cd=%s">%s</A><br>',
				date('Y/m/d h:i:s',$file['time']),
				$file['type'],
				$tfm->format_file_size($file['size']),
				urlencode($tfm->current_dir_relative.$file['name']),
				$ted->iconv('utf-8',$file['name'],"utf-8 gbk")
			);
		}
		} else {
            header('HTTP/1.0 404 Not Found');
			$body = '目录不存在！';
		}
		$template = str_replace('{body}', $body, $template);
		echo $template;
		break;
}

class TmsFileManager{
	var $current_dir_relative;
	var $current_dir_fullpath;
    var $current_dir_exist = false;
	var $sub_path_hide = array();
	var $sub_dir_vitual = array();
	var $sub_file_vitual = array();
	var $sub_dir = array();
	var $sub_file = array();
	
	function __construct($cd) { #构造函数
        # 格式化输入替换\\为/
		$cd = str_replace('\\','/',$cd);
        do { # 去掉目录里的 /xxx/../ccc 为 /ccc
            $cd = preg_replace('/\/[^\/]*\/\.\.\//','/',$cd,-1,$count);
        } while($count>0);
        # 格式化$cd 去掉/.k空格../ ./等字符（过滤掉绝对目录访问和越界的上级目录访问）
		$cd = "./" . str_replace( './', '', str_replace( '../', '', trim( $cd," /.") ) );
        $real_path = $this->realpath($cd);
        if( is_file($real_path) ) { $this->echo_file($real_path); exit(); } else { $cd = rtrim($cd,'/').'/'; }
        if($real_path===false) return;
        $this->current_dir_exist = true;
		$this->current_dir_relative = $cd;
        $this->current_dir_fullpath = $real_path;
        if( $config = $this->load_config_file($this->current_dir_fullpath.'.conf') ) {
            $this->sub_path_hide = $config['sub_path_hide'];
            $this->sub_dir_vitual = $config['sub_dir_vitual'];
            $this->sub_file_vitual = $config['sub_file_vitual'];
        };
        if( $sub = $this->scan_dir($this->current_dir_fullpath) ) {
            $this->sub_dir = $sub['sub_dir'];
            $this->sub_file = $sub['sub_file'];
        }
	}
	/**
	 * 加载目录配置文件
	 * @param string $config_file_path 配置文件绝对路径
	 * @return bool 是否成功加载
	 */
	function load_config_file($config_file_path){
		@$file = fopen($config_file_path,'r');
        $ret = array(
            'sub_file_vitual' => array(),
            'sub_dir_vitual' => array(),
            'sub_path_hide' => array()
        );
		if($file){
			$config_var_name = '';
			while(! feof($file)){
				$s_line = trim(fgets($file));
				switch(strtolower($s_line)){
				case '[vitual]':
					$config_var_name = 'sub_path_vitual';
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
							$ret['sub_path_hide'][$s_line] = true;
						break;
						case 'sub_path_vitual':
							$filename = substr($s_line,0,strpos($s_line,'|'));
							$file_location = substr($s_line,strpos($s_line,'|')+1);
                            // if(substr($config_file_path,1,1)!=':' && substr($config_file_path,0,1)!='/') = dirname($config_file_path).'/'.$file_location;
							if(is_dir($file_location)) {
                                $ret['sub_path_vitual'][$filename] = $ret['sub_dir_vitual'][$filename] = array(
                                    'path'=>$file_location,
                                    'size'=>'NULL',
                                    'time'=>filemtime($file_location),
                                    'type'=>'vitual dir',
                                );
                            } elseif( is_file($file_location) ) {
                                $ret['sub_path_vitual'][$filename] = $ret['sub_file_vitual'][$filename] = array(
                                    'path'=>$file_location,
                                    'size'=>filesize64($file_location),
                                    'time'=>filemtime($file_location),
                                    'type'=>'vitual dir',
                                );
                            }
						break;
					}
					break;
				}
			}
			fclose($file);
			return $ret;
		}
		return false;
	}
	/**
	 * 扫描目录 变量文件文件夹
	 * @param string $scan_dir 扫描的目录
	 * @return bool/array 扫描成功与否 成功返回列表
	 */
	function scan_dir($scan_dir){
		$scan_dir = $this->realpath($scan_dir);
		if(!is_dir($scan_dir)) return false;
        $ret = array( 'sub_dir'=>array(), 'sub_file'=>array());
		$filelist = scandir($scan_dir); # 得到该文件下的所有文件和文件夹
		foreach($filelist as $filename){#遍历
			$file_location=$scan_dir."/".$filename;#生成路径
			
			$b_skip_dir = false;
			if($this->sub_path_hide[$filename]) $b_skip_dir = true;
			if($filename=="." || $filename==".." || $b_skip_dir) {
				continue;
			}elseif(is_dir($file_location)) { #判断是不是文件夹
				$ret['sub_dir'] []= array(
					'name'=>$filename,
					'size'=>'NULL',
					'time'=>filemtime($file_location),
					'type'=>filetype($file_location),
				);
			} elseif(is_file($file_location)) { #判断是不是文件
				$ret['sub_file'] []= array(
					'name'=>$filename,
					'size'=>filesize64($file_location),
					'time'=>filemtime($file_location),
					'type'=>filetype($file_location),
				);
			}
		}
		#uasort($this->sub_dir, 'strcasecmp');
		$ret['sub_dir'] = array_merge($ret['sub_dir']);
		#uasort($this->sub_file, 'strcasecmp');
		$ret['sub_file'] = array_merge($ret['sub_file']);
		return $ret;
	}
	
	/**
	 * (通过虚拟路径)计算绝对路径
	 * @param string $path_org 需要计算的(可能是)虚拟路径
	 * @param string $path_prefix 不需计算的(已计算好的)路径prefix
	 * @return string 实际绝对路径
	 */
	function realpath($path_org, $path_prefix='./'){
        do { # 去掉目录里的 /xxx/../ccc 为 /ccc
            $path_org = preg_replace('/\/[^\/]*\/\.\.\//','/',$path_org,-1,$count);
        } while($count>0);
        # 检测$path_org是否为绝对目录 如果是则直接返回
		if(substr($path_org,1,1)==':' || substr($path_org,0,1)=='/') return file_exists($path_org)?$path_org:false;
        # 处理$path_org 替换\\为/ 去除两端/ 转为待处理数组
        foreach( explode('/',trim(str_replace('\\','/',$path_org),'/')) as $path_each ) {
            # 去掉右侧的\/ 替换所有/./为/ 格式化$path_org为/结束
            $path_prefix = str_replace( '/./', '/', rtrim($path_prefix,'\\/').'/' );
            # 检测配置文件
            if( $config = $this->load_config_file($path_prefix.'/.conf') ) {//print_r($config);
                # 发现目录配置文件
                if( $real_path = str_replace('\\','/',$config['sub_path_vitual'][$path_each]) ) {
                    $real_path = $real_path['path'];
                    # 匹配到虚拟目录
                    if( substr($real_path, 1,1)==':' || substr($real_path,0,1)=='/' ) {
                        # 虚拟目录指向绝对位置
                        $path_prefix = $real_path;
                    }else {
                        # 虚拟目录指向相对位置
                        $path_prefix = realpath($path_prefix.$real_path);
                    }
                } elseif ($config['sub_path_hide'][$path_each]) {
                    # 匹配到禁访目录
                    return false;
                } else {
                    # 普通目录
                    $path_prefix .= $path_each;
                }
            } else {
                # 普通目录
                $path_prefix .= $path_each;
            }
            // echo $path_prefix.','.$path_each.''.'|';print_r(explode('/',$path_org));
            if(!file_exists($path_prefix)) {echo 'ciao';return false;}
        }
        // $path_prefix = './'.str_replace('./','',$path_prefix);
        if(is_dir($path_prefix)) $path_prefix .= '/';
        return $path_prefix;
		
		while(!empty($path_org)){ # 层层遍历子目录直到$path_org遍历为空
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
					$real_path = ltrim($real_path, "\\/");
					$path = realpath($path.$real_path).'/';
				}
				if(substr($path, -1)!='/') $path.='/';
			} elseif ($tfm->sub_path_hide[$next_sub_dir]) {
                return false;
            }
			else
				$path .= $next_sub_dir . '/';
            if (!file_exists($path)) { return false; }
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
		$slashpos 	=strrpos($fullpath, '/');
		$filename	=substr($fullpath, ($slashpos===false?0:$slashpos+1));
		$filesize	=filesize64($fullpath);
		$filetime	=filectime($fullpath);
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
			'png'	=>'image/png',
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
			'xml'	=>'text/xml',
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
		ob_clean();
        if (isset($_SERVER['HTTP_RANGE']) && ($_SERVER['HTTP_RANGE'] != "") && preg_match("/^bytes=([0-9]+)-$/i", $_SERVER['HTTP_RANGE'], $match) && ($match[1] < $filesize))
            $filestart = $match[1];
        else
            $filestart = 0;
		// header("Content-Disposition: inline; filename=".$ted->iconv('utf-8', $filename."","utf-8 gbk"));
		$handle = fopen($fullpath, "rb");
        
        @header("Cache-control: public");
        @header("Pragma: public");
		if($filesize>0) header("Content-Length: " . ($filesize - $filestart));
        if ($filestart > 0) {
            fseek($handle, $filestart);
            Header("HTTP/1.1 206 Partial Content");
            Header("Content-Ranges: bytes" . $filestart . "-" . ($filesize - 1) . "/" . $filesize);
        } else {
            header("Last-Modified: " . gmdate("D, d M Y H:i:s",$filetime) . " GMT");
            Header("Accept-Ranges: bytes");
        }
        
		header("Content-Type: {$filemime}");
        # 解决在IE中下载时中文乱码问题
        if(preg_match('/MSIE/',$_SERVER['HTTP_USER_AGENT'])) { $filename = str_replace('+','%20',urlencode($filename)); }
        @header("Content-Disposition: inline;filename={$filename}"); 
        // @header("Content-Disposition: attachment;filename={$filename}"); 
        
        //fpassthru($handle);
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
		} elseif ($filesize<=1024) {
			return $filesize.' B';
		} elseif (($filesize=$filesize/1024)<=1024) {
			return sprintf("%.2f",$filesize).'KB';
		} elseif (($filesize=$filesize/1024)<=10240) {
			return sprintf("%.2f",$filesize).'MB';
		} elseif (($filesize=$filesize/1024)<=1024) {
			return sprintf("%.2f",$filesize).'GB';
		}
		// elseif ($filesize/1073741824>10) {
			// return sprintf("%.2f",$filesize/1073741824).'GB';
		// } elseif ($filesize/1048576>1) {
			// return sprintf("%.2f",$filesize/1048576).'MB';
		// } elseif (shr32($filesize,10)) {
			// return (shr32($filesize,10)).'KB';
		// } else {
			// return $filesize.' B';
		// }
	}
}

/**
 * 获取文件大小信息
 * @param string 文件路径
 * @return number 文件大小
 */
function filesize64($file) {
    static $iswin;
    if (!isset($iswin))
        $iswin = (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN');

    static $exec_works;
    if (!isset($exec_works))
        $exec_works = (function_exists('exec') && !ini_get('safe_mode') && @exec('echo EXEC') == 'EXEC');

    // try a shell command
    if ($exec_works) {
        $cmd = ($iswin) ? "for %F in (\"$file\") do @echo %~zF" : "stat -c%s \"$file\"";
        @exec($cmd, $output);
        if (is_array($output) && ctype_digit($size = trim(implode("\n", $output))))
            return $size;
    }

    // try the Windows COM interface
    if ($iswin && class_exists("COM")) {
        try {
            $fsobj = new COM('Scripting.FileSystemObject');
            $f = $fsobj->GetFile( realpath($file) );
            $size = $f->Size;
        } catch (Exception $e) {
            $size = null;
        }
        if (ctype_digit($size))
            return $size;
    }
    
    return sprintf("%u", filesize($file));
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
class TmsEncoding {
	function iconv( $toEncoding, $string, $from_encoding_list = '' ) { # 判断文本编码类型
		$toEncoding = strtoupper($toEncoding);
		$from_encoding_list = explode(' ', trim(strtoupper($from_encoding_list)));
		$fromEncoding = (empty($from_encoding_list)) ? $this->detectEncoding( $string, $toEncoding ) : $this->detectEncoding( $string, $from_encoding_list );
		if( $fromEncoding && $fromEncoding!=$toEncoding ) $string = iconv( $fromEncoding, $toEncoding, $string );
		return $string;
	}
	function detectEncoding( $string, $encoding_list = array('GBK', 'GB2312', 'ASCII', 'UTF-8') ) { # 判断文本编码类型(是否为$is_encode)
		// if($this->is_utf8($string)) return 'UTF-8';
		// if(preg_match("/[".chr(0xa1)."-".chr(0xff)."]/",$string)) return 'GBK';
		// if(preg_match("/[x{4e00}-x{9fa5}]/u",$string)) return 'UTF-8';
		foreach($encoding_list as $c){
			if( $string === @iconv(($c=='UTF-8')?'GB2312':'UTF-8', $c, iconv($c, ($c=='UTF-8')?'GB2312':'UTF-8', $string))){ return $c; }
		}
		return false;
	}
	// Returns true if $string is valid UTF-8 and false otherwise.
	function is_utf8($string) {
		// From http://w3.org/International/questions/qa-forms-utf-8.html
		return preg_match('%^(?:
			  [\x09\x0A\x0D\x20-\x7E]            # ASCII
			| [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
			|  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
			| [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
			|  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
			|  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
			| [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
			|  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
		)*$%xs', $string);
		
	} // function is_utf8
}
function echo_ui() {
    file_put_contents('index.html');
}
?>