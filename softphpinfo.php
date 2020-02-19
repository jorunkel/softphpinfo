<?php

/**
 * The MIT License (MIT)
 * 
 * Copyright (c) 2020 Johannes Runkel (www.jrunkel.de)
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

define('APP_VERSION', '0.4');
define('APP_CHARSET', ini_get('default_charset'));
define('APP_NAME', 'softPhpInfo()');

if(!defined('PHP_INI_USER')) define('PHP_INI_USER', 1);
if(!defined('PHP_INI_PERDIR')) define('PHP_INI_PERDIR', 2);
if(!defined('PHP_INI_SYSTEM')) define('PHP_INI_SYSTEM', 4);
if(!defined('PHP_INI_ALL')) define('PHP_INI_ALL', PHP_INI_USER | PHP_INI_PERDIR | PHP_INI_SYSTEM);

if(defined('ENT_HTML401')) {
	define('ENC_FLAGS', ENT_COMPAT | ENT_HTML401);
} else {
	define('ENC_FLAGS', ENT_COMPAT);
}

function enc($text) {
	if(is_array($text)) print_r($text);
	return htmlentities($text, ENC_FLAGS, APP_CHARSET);
}
function get_value($value) {
	if(!isset($value) || $value=='') {
		return '<span class="no-value">no value</span>';
	} else {
		if(preg_match('/^#[a-f0-9]{6}$/i', $value)) {
			return '<span style="color: '.$value.'">'.$value.'</span>';
		}
		if(is_array($value)) {
			$result = array();
			foreach($value AS $k => $v) {
				$result[] = enc($k).' = '.enc($v);
			}
			return implode("<br />", $result);
		} else {
			return enc($value);
		}
	}
}

function get_extension_version($name) {
	$testName = substr($name, 0, 4)=='pdo_' ? 'pdo_*' : $name;
	try {
		switch($testName) {
			case 'mysql':
				return @mysql_get_client_info();
			case 'mysqli':
				return @mysqli_get_client_info();
			case 'pdo_*':
				$pdo = new PDO(substr($name, 4).':');
				return $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
		}
	} catch(Exception $ex) { }
	return null;
}

# get extension list
$extensions = get_loaded_extensions();
usort($extensions, 'strnatcasecmp');
$extVersions = array();
foreach($extensions AS $ext) {
	$v = get_extension_version($ext);
	if(empty($v)) continue;
	$extVersions[$ext] = $v;
}

# get configuration by extension
$configByExt = array();
foreach(ini_get_all() AS $key => $details) {
	$ext = strpos($key, '.')===false ? '' : substr($key, 0, strpos($key, '.'));
	if(in_array($ext, $extensions)) {
		$configByExt[$ext][$key] = $details;
	} else {
		$configByExt[''][$key] = $details;
	}
}
uksort($configByExt, 'strnatcasecmp');

?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="<?php echo APP_CHARSET; ?>">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title><?php echo APP_NAME.' v'.APP_VERSION; ?></title>
		<link href="data:image/x-icon;base64,Qk02AwAAAAAAADYAAAAoAAAAEAAAABAAAAABABgAAAAAAAADAADEDgAAxA4AAAAAAAAAAAAAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICA19fX19fX19fXwICAwICAwICAwICAwICAwICAwICA19fX19fX19fXwICAwICAwICA19fXAAAA19fXwICAwICAwICAwICAwICAwICAwICA19fXAAAA19fXwICAwICAwICA19fXAAAA19fX19fXwICAwICA19fXwICAwICA19fX19fXAAAA19fX19fXwICAwICA19fXAAAAAAAAAAAA19fX19fXAAAA19fX19fXAAAA19fXAAAAAAAAAAAA19fX19fX19fXAAAA19fX19fXAAAA19fXAAAA19fX19fXAAAA19fXAAAA19fX19fXAAAA19fX19fXAAAA19fX19fXAAAA19fXAAAA19fX19fXAAAA19fXAAAA19fX19fXAAAA19fX19fXAAAA19fX19fXAAAA19fXAAAA19fX19fXAAAA19fXAAAA19fX19fXAAAA19fX19fXAAAAAAAAAAAA19fX19fXAAAAAAAAAAAA19fX19fXAAAAAAAAAAAA19fX19fXwICA19fX19fX19fXwICA19fXAAAA19fX19fXwICAwICA19fX19fX19fXwICAwICAwICAwICAwICAwICAwICA19fXAAAA19fXwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICA19fX19fX19fXwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICAwICA" rel="shortcut icon" type="image/x-icon" />
	</head>
	<body>
		
		<article>
			
			<?php ob_start(); ?>
			
			<h1 id="header"><?php echo APP_NAME.' v'.APP_VERSION; ?></h1>
			
			<table class="table">
				<tbody>
					<tr>
						<td class="key" style="width: 35%">PHP Version</td>
						<td><?php echo enc(phpversion()); ?></td>
					</tr>
					<tr>
						<td class="key">System</td>
						<td><?php echo enc(php_uname()); ?></td>
					</tr>
					<tr>
						<td class="key">Server API</td>
						<td><?php echo enc(php_sapi_name()); ?></td>
					</tr>
					<?php if(function_exists('posix_getpwuid')) { ?>
					<tr>
						<td class="key">Web User</td>
						<td>
							<?php
							$posixUser = posix_getpwuid(posix_geteuid());
							echo enc($posixUser['name']);
							?>
						</td>
					</tr>
					<?php } ?>
					<tr>
						<td class="key">Loaded Configuration File</td>
						<td><?php echo enc(php_ini_loaded_file()); ?></td>
					</tr>
					<tr>
						<td class="key">Scanned Configuration Files</td>
						<td><?php echo str_replace(',', '<br />', enc(php_ini_scanned_files())); ?></td>
					</tr>
					<tr>
						<td class="key">__DIR__</td>
						<td><?php echo enc(__DIR__); ?></td>
					</tr>
					<tr>
						<td class="key">__FILE__</td>
						<td><?php echo enc(__FILE__); ?></td>
					</tr>
					<tr>
						<td class="key">DIRECTORY_SEPARATOR</td>
						<td><?php echo enc(DIRECTORY_SEPARATOR); ?></td>
					</tr>
					<tr>
						<td class="key">PATH_SEPARATOR</td>
						<td><?php echo enc(PATH_SEPARATOR); ?></td>
					</tr>
					<tr>
						<td class="key">Error Reporting</td>
						<td>
							<?php
							$erep = array();
							$er = (int)ini_get('error_reporting');
							$erep[] = $er & E_ERROR ? '<span class="green">E_ERROR</span>' : '<span class="gray">E_ERROR</span>';
							$erep[] = $er & E_WARNING ? '<span class="green">E_WARNING</span>' : '<span class="gray">E_WARNING</span>';
							$erep[] = $er & E_PARSE ? '<span class="green">E_PARSE</span>' : '<span class="gray">E_PARSE</span>';
							$erep[] = $er & E_NOTICE ? '<span class="green">E_NOTICE</span>' : '<span class="gray">E_NOTICE</span>';
							$erep[] = $er & E_CORE_ERROR ? '<span class="green">E_CORE_ERROR</span>' : '<span class="gray">E_CORE_ERROR</span>';
							$erep[] = $er & E_CORE_WARNING ? '<span class="green">E_CORE_WARNING</span>' : '<span class="gray">E_CORE_WARNING</span>';
							$erep[] = $er & E_COMPILE_ERROR ? '<span class="green">E_COMPILE_ERROR</span>' : '<span class="gray">E_COMPILE_ERROR</span>';
							$erep[] = $er & E_COMPILE_WARNING ? '<span class="green">E_COMPILE_WARNING</span>' : '<span class="gray">E_COMPILE_WARNING</span>';
							$erep[] = $er & E_USER_ERROR ? '<span class="green">E_USER_ERROR</span>' : '<span class="gray">E_USER_ERROR</span>';
							$erep[] = $er & E_USER_WARNING ? '<span class="green">E_USER_WARNING</span>' : '<span class="gray">E_USER_WARNING</span>';
							$erep[] = $er & E_USER_NOTICE ? '<span class="green">E_USER_NOTICE</span>' : '<span class="gray">E_USER_NOTICE</span>';
							$erep[] = $er & E_STRICT ? '<span class="green">E_STRICT </span>' : '<span class="gray">E_STRICT</span>';
							$erep[] = $er & E_RECOVERABLE_ERROR ? '<span class="green">E_RECOVERABLE_ERROR</span>' : '<span class="gray">E_RECOVERABLE_ERROR</span>';
							$erep[] = $er & E_DEPRECATED ? '<span class="green">E_DEPRECATED</span>' : '<span class="gray">E_DEPRECATED</span>';
							$erep[] = $er & E_USER_DEPRECATED ? '<span class="green">E_USER_DEPRECATED</span>' : '<span class="gray">E_USER_DEPRECATED</span>';
							echo implode(" ", $erep);
							?>
						</td>
					</tr>
					<tr>
						<td class="key">phpinfo() Availability</td>
						<td>
							<?php
							ob_start();
							if(function_exists('phpinfo')) @phpinfo();
							$pi = ob_get_contents();
							ob_end_clean();
							echo empty($pi) ? '<span class="red">disabled</span>' : '<span class="green">enabled</span>';
							?>
						</td>
					</tr>
					<tr>
						<td class="key">Zend Version</td>
						<td><?php echo enc(zend_version()); ?></td>
					</tr>
					<tr>
						<td class="key">Local Time</td>
						<td><?php echo date('Y-m-d H:i:s'); ?></td>
					</tr>
				</tbody>
			</table>
			
			<h1 id="loaded-extensions">Loaded Extensions</h1>
			<table class="table">
				<thead>
					<tr>
						<th style="width: 35%">Extension</th>
						<th>Version</th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach($extensions AS $ext) {
						echo '<tr>';
						echo '<td class="key">';
						if(array_key_exists($ext, $configByExt)) {
							echo '<a href="#config-'.enc($ext).'">'.enc($ext).'</a>';
						} else {
							echo enc($ext);
						}
						echo ' <a class="docs" target="_blank" href="http://php.net/'.enc($ext).'">[docs]</a>';
						echo '</td>';
						$phpVersion = phpversion($ext);
						$extVersion = isset($extVersions[$ext]) ? $extVersions[$ext] : null;
						echo '<td>'.enc($phpVersion.(empty($extVersion) ? '' : ' ('.$extVersion.')')).'</td>';
						echo '</tr>';
					}
					?>
				</tbody>
			</table>
			
			<h1 id="configuration">Configuration</h1>
			
			<?php
			foreach($configByExt AS $ext => $extConfigs) {
				if($ext=='') $ext = 'Core';
				echo '<h2 id="config-'.enc($ext).'">'.enc($ext).'</h2>';
				echo '<table class="table">';
				echo '<thead>';
				echo '<tr>';
				echo '<th style="width: 35%">Directive</th>';
				echo '<th style="width: 25%">Local Value</th>';
				echo '<th style="width: 25%">Master Value</th>';
				echo '<th style="width: 15%">Access Level</th>';
				echo '</tr>';
				echo '</thead>';
				echo '<tbody>';
				if(isset($extVersions[$ext])) {
					echo '<tr>';
					echo '<td class="key">Version</td>';
					echo '<td colspan="3">'.enc($extVersions[$ext]).'</td>';
					echo '</tr>';
				}
				foreach($extConfigs AS $key => $details) {
					echo '<tr>';
					echo '<td class="key">';
					echo enc($key);
					$link = null;
					switch($ext) {
						case 'Core':
							$link = 'http://php.net/'.$key;
							break;
					}
					if(isset($link)) echo ' <a class="docs" target="_blank" href="'.enc($link).'">[docs]</a>';
					echo '</td>';
					echo '<td>'.get_value($details['local_value']).'</td>';
					echo '<td>'.get_value($details['global_value']).'</td>';
					$access = array();
					$access[] = $details['access'] & PHP_INI_USER ? '<span class="green">User</span>' : '<span class="red">User</span>';
					$access[] = $details['access'] & PHP_INI_PERDIR ? '<span class="green">Dir</span>' : '<span class="red">Dir</span>';
					$access[] = $details['access'] & PHP_INI_SYSTEM ? '<span class="green">Sys</span>' : '<span class="red">Sys</span>';
					$access[] = $details['access'] & PHP_INI_ALL ? '<span class="green">All</span>' : '<span class="red">All</span>';
					echo '<td>'.implode(' ', $access).'</td>';
					echo '</tr>';
				}
				echo '</tbody>';
				echo '</table>';
			}
			?>
			
			<h1 id="dollar-server">$_SERVER</h1>
			<table class="table">
				<thead>
					<tr>
						<th style="width: 35%">Key</th>
						<th>Value</th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach($_SERVER AS $k => $v) {
						echo '<tr>';
						echo '<td class="key">'.enc($k).'</td>';
						echo '<td>';
						if(is_array($v)) {
							$vs = array();
							foreach($v AS $vk => $vv) {
								$vs[] = enc($vk)." = ".enc($vv);
							}
							echo implode("<br />", $vs);
						} else {
							echo enc($v);
						}
						echo '</td>';
						echo '</tr>';
					}
					?>
				</tbody>
			</table>
			
			<h1 id="response-headers">Response Headers</h1>
			<table class="table">
				<thead>
					<tr>
						<th style="width: 35%">Key</th>
						<th>Value</th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach(headers_list() AS $h) {
						$k = $h;
						$v = '';
						if(strpos($k, ':')!==false) {
							$k = substr($k, 0, strpos($k, ':'));
							$v = substr($h, strpos($h, ':')+1);
						}
						echo '<tr>';
						echo '<td>'.enc($k).'</td>';
						echo '<td>'.enc($v).'</td>';
						echo '</tr>';
					}
					?>
				</tbody>
			</table>
			
			<?php
			$article = ob_get_contents();
			ob_end_flush();
			?>
			
		</article>
		
		<nav>
			<?php
			
			preg_match_all('/<h(1|2) id="([^"]+)">(.*)<\/h(1|2)>/U', $article, $headings);
			
			echo '<ul>';
			foreach($headings[0] AS $i => $match) {
				$level = $headings[1][$i];
				$id = $headings[2][$i];
				$title = $headings[3][$i];
				echo '<li class="level'.$level.'"><a href="#'.$id.'">'.$title.'</a></li>';
			}
			echo '</ul>';
			
			?>
		</nav>
		
		<style>
			html {
				font-family: Arial;
				font-size: 12px;
				box-sizing: border-box;
				background: #fff;
				color: #333;
				width: 100%;
				height: 100%;
			}
			* {
				font-family: inherit;
				font-size: inherit;
				font-weight: inherit;
				font-style: inherit;
				text-decoration: inherit;
				box-sizing: inherit;
				padding: 0;
				margin: 0;
				color: inherit;
				background: transparent;
				text-align: inherit;
			}
			body {
				width: 100%;
				height: 100%;
				position: relative;
			}
			nav {
				position: absolute;
				left: 0;
				top: 0;
				bottom: 0;
				width: 250px;
				padding: 2rem;
				overflow: auto;
			}
			article {
				position: absolute;
				overflow: auto;
				left: 250px;
				top: 0;
				bottom: 0;
				right: 0;
				padding: 2rem;
				overflow: auto;
			}
			b {
				font-weight: bold;
			}
			a {
				text-decoration: underline;
			}
			h1 {
				text-align: center;
				font-weight: bold;
				font-size: 1.8rem;
				background: #9999cc;
				border: 1px solid #666;
				padding: 0.5rem;
				box-shadow: 1px 2px 3px #cccccc;
				max-width: 1050px;
				margin: 3rem auto;
				border-radius: 30px;
			}
			#header {
				font-size: 2.2rem;
				padding: 1rem 0.5rem;
				margin-top: 0;
			}
			h2 {
				text-align: center;
				font-weight: bold;
				font-size: 1.6rem;
			}
			h2, table {
				margin: 1.5rem auto;
				max-width: 1000px;
			}
			th {
				font-weight: bold;
			}
			.table {
				border-collapse: collapse;
				border-top: 1px solid #666;
				border-left: 1px solid #666;
				box-shadow: 1px 2px 3px #cccccc;
				width: 100%;
			}
			.table thead {
				background: #9999cc;
			}
			.table td,
			.table th {
				border-right: 1px solid #666;
				border-bottom: 1px solid #666;
				padding: 4px 5px;
			}
			.table td {
				background: #dddddd;
				overflow-x: auto;
				max-width: 300px;
				word-wrap: break-word;
			}
			.table td.key {
				font-weight: bold;
				background-color: #ccccff;
			}
			.no-value {
				color: #999;
				font-style: italic;
			}
			.green {
				color: #008000;
			}
			.red {
				color: red;
			}
			.gray {
				color: #999;
			}
			.docs {
				font-weight: normal;
				color: #666;
				float: right;
			}
			nav ul {
				list-style: none;
				border-bottom: 1px solid #666;
				box-shadow: 1px 2px 3px #ccc;
				font-size: 1.2rem;
				margin-bottom: 2rem;
			}
			nav ul>li:first-child a {
				border-radius: 4px 4px 0 0;
			}
			nav ul {
				border-radius: 0 0 4px 4px;
			}
			nav ul>li:last-child a {
				border-radius: 0 0 3px 3px;
			}
			nav a {
				display: block;
				border: 1px solid #666;
				border-bottom: 0;
				padding: 4px 5px;
				text-decoration: none;
			}
			nav a:hover {
				color: #000;
			}
			nav li.level1 a {
				background: #9999cc;
				font-weight: bold;
			}
			nav li.level2 a {
				background: #ccccff;
				padding-left: 2rem;
			}
		</style>
		
	</body>
</html>
