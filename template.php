<?php
if (! defined ( 'IN_MAUOS' )) {
	exit ( 'Access Denied' );
}
class Template {
	var $subtemplates = array ();
	var $csscurmodules = '';
	var $replacecode = array ('search' => array (),'replace' => array ());
	var $blocks = array ();
	
	function parse_template($tplfile,$cachefile) {
		$basefile = basename($tplfile, '.html');
		if ($fp = @fopen ($tplfile, 'r' )) {
			$template = @fread ( $fp, filesize ($tplfile));
			fclose ( $fp );
		} elseif ($fp = @fopen ( $filename = substr ($tplfile, 0, - 5 ) . '.php', 'r' )) {
			$template = $this->getphptemplate(@fread( $fp, filesize($tplfile)));
			fclose ( $fp );
		}
		$var_regexp = "((\\\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*(\-\>)?[a-zA-Z0-9_\x7f-\xff]*)(\[[a-zA-Z0-9_\-\.\"\'\[\]\$\x7f-\xff]+\])*)";
		$const_regexp = "([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)";
		$headerexists = preg_match ("/{(sub)?template\s+[\w\/]+?header\}/", $template );
		$this->subtemplates = array ();
		for($i = 1; $i <= 3; $i ++) {
			
			if (strexists( $template, '{subtemplate' )) {
				$template = preg_replace ( "/[\n\r\t]*(\<\!\-\-)?\{subtemplate\s+([a-z0-9_:\/]+)\}(\-\-\>)?[\n\r\t]*/ies", "\$this->loadsubtemplate('\\2')", $template );
			}
		}
		
		$template = preg_replace ( "/([\n\r]+)\t+/s", "\\1", $template );
		$template = preg_replace ( "/\<\!\-\-\{(.+?)\}\-\-\>/s", "{\\1}", $template ); //处理隐藏的函数功能
		
		$template = preg_replace ( "/[\n\r\t]*\{eval\s+(.+?)\s*\}[\n\r\t]*/ies", "\$this->evaltags('\\1')", $template );
		
		$template = str_replace ( "{LF}", "<?=\"\\n\"?>", $template ); //换行处理
		
		$template = preg_replace ( "/\{(\\\$[a-zA-Z0-9_\-\>\[\]\'\"\$\.\x7f-\xff]+)\}/s", "<?=\\1?>", $template ); //变量输出

		$template = preg_replace ( "/$var_regexp/es","\$this->addquote('<?=\\1?>')", $template );		
		$template = preg_replace ( "/\<\?\=\<\?\=$var_regexp\?\>\?\>/es", "\$this->addquote('<?=\\1?>')", $template );
		
		$headeradd = $headerexists ? "hookscriptoutput('$basefile');" : '';
		if (! empty ( $this->subtemplates )) {
			$headeradd .= "\n0\n";
			foreach ( $this->subtemplates as $fname ) {
				$headeradd .= "|| checktplrefresh('$tplfile', '$fname', " . time () . ", '$templateid', '$cachefile', '$tpldir', '$file')\n";
			}
			$headeradd .= ';';
		}
		if (! empty ( $this->blocks )) {
			$headeradd .= "\n";
			$headeradd .= "block_get('" . implode ( ',', $this->blocks ) . "');";
		}
		
		
		$template = "<? if(!defined('IN_MAUOS')) exit('Access Denied'); {$headeradd}?>\n$template";
		
		$template = preg_replace ( "/[\n\r\t]*\{template\s+([a-z0-9_:\/]+)\}[\n\r\t]*/ies", "\$this->stripvtags('<? include template(\'\\1\'); ?>')", $template );
		
		$template = preg_replace ( "/[\n\r\t]*\{template\s+(.+?)\}[\n\r\t]*/ies", "\$this->stripvtags('<? include template(\'\\1\'); ?>')", $template );
		
		$template = preg_replace ( "/[\n\r\t]*\{echo\s+(.+?)\}[\n\r\t]*/ies", "\$this->stripvtags('<? echo \\1; ?>')", $template );
		
		$template = preg_replace ( "/([\n\r\t]*)\{if\s+(.+?)\}([\n\r\t]*)/ies", "\$this->stripvtags('\\1<? if(\\2) { ?>\\3')", $template );
		
		$template = preg_replace ( "/([\n\r\t]*)\{elseif\s+(.+?)\}([\n\r\t]*)/ies", "\$this->stripvtags('\\1<? } elseif(\\2) { ?>\\3')", $template );
		
		$template = preg_replace ( "/\{else\}/i", "<? } else { ?>", $template );
		
		$template = preg_replace ( "/\{\/if\}/i", "<? } ?>", $template );
		
		$template = preg_replace ( "/[\n\r\t]*\{loop\s+(\S+)\s+(\S+)\}[\n\r\t]*/ies", "\$this->stripvtags('<? if(is_array(\\1)) foreach(\\1 as \\2) { ?>')", $template );
		
		$template = preg_replace ( "/[\n\r\t]*\{loop\s+(\S+)\s+(\S+)\s+(\S+)\}[\n\r\t]*/ies", "\$this->stripvtags('<? if(is_array(\\1)) foreach(\\1 as \\2 => \\3) { ?>')", $template );
		
		$template = preg_replace ( "/\{\/loop\}/i", "<? } ?>", $template );
		
		$template = preg_replace ( "/\{$const_regexp\}/s", "<?=\\1?>", $template );
		
		if (! empty ( $this->replacecode )) {
			$template = str_replace($this->replacecode ['search'], $this->replacecode ['replace'], $template);
		}
		$template = preg_replace ( "/ \?\>[\n\r]*\<\? /s", " ", $template );
		/**
		 * 开始写入编译后的模版
		 */
		if (! @$fp = fopen ($cachefile, 'w' )) {
			$this->error ( 'directory_notfound', dirname ($cachefile));
		}
		$template = preg_replace ( "/\"(http)?[\w\.\/:]+\?[^\"]+?&[^\"]+?\"/e", "\$this->transamp('\\0')", $template );
		
		$template = preg_replace ( "/\<script[^\>]*?src=\"(.+?)\"(.*?)\>\s*\<\/script\>/ies", "\$this->stripscriptamp('\\1', '\\2')", $template );
		
		$template = preg_replace ( "/[\n\r\t]*\{block\s+([a-zA-Z0-9_\[\]]+)\}(.+?)\{\/block\}/ies", "\$this->stripblock('\\1', '\\2')", $template );
		$template = preg_replace ( "/\<\?(\s{1})/is", "<?php\\1", $template );
		$template = preg_replace ( "/\<\?\=(.+?)\?\>/is", "<?php echo \\1;?>", $template );
		flock ( $fp, 2 );
		fwrite ( $fp, $template );
		fclose ( $fp );
	}
	
	
	
	function evaltags($php) {
		$php = str_replace ( '\"', '"', $php );
		$i = count ( $this->replacecode ['search'] );
		$this->replacecode ['search'] [$i] = $search = "<!--EVAL_TAG_$i-->";
		$this->replacecode ['replace'] [$i] = "<?php $php?>";
		return $search;
	}
	
	
	function stripphpcode($type, $code) {
		$this->phpcode [$type] [] = $code;
		return '{phpcode:' . $type . '/' . (count ( $this->phpcode [$type] ) - 1) . '}';
	}
	
	
	function loadsubtemplate($file) {
		
		$tplfile = template( $file, 0, '', 1 );
		$filename = MAUOS_ROOT.$tplfile;
		if (($content = @implode ( '', file( $filename ))) || ($content = $this->getphptemplate(@implode('', file( substr ( $filename, 0, - 4 ) . '.php' ))))) {
			$this->subtemplates [] = $tplfile;
			return $content;
		} else {
			return '<!-- ' . $file . ' -->';
		}
	}
	function getphptemplate($content) {
		$pos = strpos ( $content, "\n" );
		return $pos !== false ? substr ( $content, $pos + 1 ) : $content;
	}
	
	
	function cssvtags($param, $content) {
		global $_G;
		$modules = explode ( ',', $param );
		foreach ( $modules as $module ) {
			$module .= '::'; // fix notice
			list ( $b, $m ) = explode ( '::', $module );
			if ($b && $b == $_G ['basescript'] && (! $m || $m == CURMODULE)) {
				$this->csscurmodules .= $content;
				return;
			}
		}
		return;
	}
	
	
	
	/**
	 * 处理方括号内的内容
	 * @param unknown_type $var
	 * @return mixed
	 */
	function addquote($var) {
		return str_replace ( "\\\"", "\"", preg_replace ( "/\[([a-zA-Z0-9_\-\.\x7f-\xff]+)\]/s", "['\\1']", $var ) );
	}
	
	function stripvtags($expr, $statement = '') {
		$expr = str_replace ( "\\\"", "\"", preg_replace ( "/\<\?\=(\\\$.+?)\?\>/s", "\\1", $expr ) );
		$statement = str_replace ( "\\\"", "\"", $statement );
		return $expr . $statement;
	}
	
	/**
	 * 处理url转义符
	 * @param unknown_type $str
	 * @return mixed
	 */
	function transamp($str) {
		$str = str_replace ( '&', '&amp;', $str );
		$str = str_replace ( '&amp;amp;', '&amp;', $str );
		$str = str_replace ( '\"', '"', $str );
		return $str;
	}
	
	/**
	 * 处理script中的转义符
	 * @param unknown_type $s
	 * @param unknown_type $extra
	 * @return string
	 */
	function stripscriptamp($s, $extra) {
		$extra = str_replace ( '\\"', '"', $extra );
		$s = str_replace ( '&amp;', '&', $s );
		return "<script src=\"$s\" type=\"text/javascript\" $extra></script>";
	}
	
	
	function stripblock($var, $s) {
		$s = str_replace ( '\\"', '"',$s);
		$s = preg_replace ( "/<\?=\\\$(.+?)\?>/", "{\$\\1}", $s );
		preg_match_all ( "/<\?=(.+?)\?>/e", $s, $constary );
		$constadd = '';
		$constary [1] = array_unique ( $constary [1] );
		foreach ( $constary [1] as $const ) {
			$constadd .= '$__' . $const . ' = ' . $const . ';';
		}
		$s = preg_replace ( "/<\?=(.+?)\?>/", "{\$__\\1}", $s );
		$s = str_replace ( '?>', "\n\$$var .= <<<EOF\n", $s );
		$s = str_replace ( '<?', "\nEOF;\n", $s );
		return "<?\n$constadd\$$var = <<<EOF\n" . $s . "\nEOF;\n?>";
	}
	
}

?>