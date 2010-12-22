<?php 
/*
Plugin Name: Tynt.com For WordPress
Plugin URI: http://mikhailkozlov.com/tynt-insight-for-wordpress/
Description: Make link-backs to your content effortless for readers and gain new insight into user engagement with Tynt Insight.
Author: Mikhail Kozlov
Version: 1.0.0
License: MIT
Author Url: http://mikhailkozlov.com/
*/

class TyntComForWordPress{
	static $options=array(
				'v'=>'1.0.0',
				'key'=>'tynt-insight-for-wordpress',
				'tynt-where'=>'footer',
				'tynt-id'=>'',
				'param-t'=>'empty', // show link above copied message - true
				'param-a'=>'empty', // if false - Disable attribution link
	 			'param-ap'=>'Read more:',  //  attribution link prefix (def: Read more:)
 				'param-as'=>'empty', //attribution link suffix (def: none). 
			 	'param-st'=>'empty', // true show page title as link, leave empty not to show
			 	'param-su'=>'empty', // false show link after title, set to false if not to show. Ignor to show
				'param-cc'=>'empty', // include licence. value= licence code
				'param-b'=>'empty', // true - Enable Address Bar Tracking 
				'param-ba'=>'empty', // true - Exclude Tracking Homepage
				'param-el'=>'empty', // build sponsor link
				'link-formating'=>0,
				'tynt-sponsor-text'=>'',
				'tynt-sponsor-name'=>'',
				'tynt-sponsor-link'=>'',
	);
	/**	 
	 * Licence codes according to Tynt 
	 **/
	static $contebtLicence = array(
		'empty'=>'Do not use',
		1=>'Attribution',
		2=>'Attribution Share Alike',
		3=>'Attribution No Derivatives',
		4=>'Attribution Non-Commercial',
		5=>'Attribution Non-Commercial Share Alike',
		6=>'Attribution Non-Commercial No Derivatives'
	);
	
	static $defs=array(
		'tynt-where'=>array('footer'=>'WP Footer','header'=>'WP Header','custom'=>'I will paste the code'),
		'tynt-options'=>array(
			'link-format'=>array('1'=>'URL','2'=>'Page Title','3'=>'Page Title and URL'),
			'link-placement'=>array('empty'=>'below the copied text','true'=>'above the copied text')
		)
	);
	
	function init(){
		$options=unserialize(get_option(self::$options['key']));
		if($options === false){
			$options = self::activate();
		}
		if(is_admin()) {
			add_action('admin_menu', array('TyntComForWordPress','admin_menu'));
			wp_enqueue_script('tynt-insight-for-wordpress', plugins_url('tynt-insight-for-wordpress') . '/scripts.js', array('jquery'), '1.0.0', true);
			add_filter( 'plugin_action_links', array('TyntComForWordPress','tynt_insight_action_links'), 10, 2 );
			self::saveSettings();
		}else{
			switch($options['tynt-where']){
				case 'footer':
					add_action('wp_footer', array('TyntComForWordPress','printTyntInsight'));
				break;
				case 'header':
					add_action('wp_head', array('TyntComForWordPress','printTyntInsight'));
				break;
				default:
					// this custome case, so user will implement code.
				break;
			}
		}		
		
	}
	function saveSettings(){
			if(isset($_POST['action']) && isset($_GET['page']) && $_GET['page'] == 'tynt-insight-for-wordpress'){
				$options=self::$options;
				$options=array_merge($options,unserialize(get_option($options['key'])));
				foreach(self::$options as $k=>$v){
					if(isset($_POST[$k]) && !empty($_POST[$k])){
						$options[$k] = urldecode($_POST[$k]);
					}
					if(array_key_exists($k,$options) && empty($_POST[$k])){
						$options[$k] = 'empty';
					}					
				}
				// format sponsor link
				$options['tynt-sponsor-name'] = ($options['tynt-sponsor-name'] == 'empty') ? '':$options['tynt-sponsor-name'];
				$options['tynt-sponsor-link'] = ($options['tynt-sponsor-link'] == 'empty') ? '':$options['tynt-sponsor-link'];
				$options['tynt-sponsor-text'] = ($options['tynt-sponsor-text'] == 'empty') ? '':$options['tynt-sponsor-text'];
				$options['param-el'] =(!empty($options['tynt-sponsor-name']) && !empty($options['tynt-sponsor-link'])) ?  $options['tynt-sponsor-text'].' <a href="'.$options['tynt-sponsor-link'].'" target="_blank" color="#003399">'.$options['tynt-sponsor-name'].'</a>':'empty';
				// format main link
				switch($options['link-formating']){
					case '2':
						$options['param-st'] = 'true';
						$options['param-su'] = 'false';
						break;
					case '3':
						$options['param-st'] = 'true';
						$options['param-su'] = 'empty';
						break;
				}
				if(update_option( self::$options['key'], serialize($options) )){
					header('Location: options-general.php?page=tynt-insight-for-wordpress&updated=true');
				}
			}			
	}
	function tynt_insight_action_links($links, $file){
		if ( $file == plugin_basename( dirname(__FILE__).'/plugin.php' ) ) {
			$links[] = '<a href="options-general.php?page=tynt-insight-for-wordpress">'.__('Settings').'</a>';
		}
	
		return $links;
	}
	function admin_menu(){
		add_options_page('Tynt Insight Options', 'Tynt Insight', 'manage_options', 'tynt-insight-for-wordpress', array('TyntComForWordPress','admin_options_page'));
	}
	function admin_options_page(){
		if (!current_user_can('manage_options'))  {
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}
		$options=self::$options;
		$options=array_merge($options,unserialize(get_option($options['key'])));
		echo '<div class="wrap">';
		echo '<div class="icon32" id="icon-options-general"><br /></div><h2>Plugin Options</h2>';
		echo '<p>
				Before you can start you need to have and account @ <a href="http://www.tynt.com/" target="_blank">http://www.tynt.com/</a>.<br />
				Once you have access to your panel, paste your Tynt Insight code here.
				</p>';
		
		echo '<form action="options-general.php?page=tynt-insight-for-wordpress" name="tynt-insight-for-wordpress" method="post">';
		echo '
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row">Tynt Insight ID</th>
						<td>
							<input type="text" id="tynt-id" class="regular-text" value="'.$options['tynt-id'].'" name="tynt-id">
							<div>On Your Script page find line of code that looks like this<br /><i>var Tynt=Tynt||[];Tynt.push(\'<span style="color:red">findyourcode</span>\');</i><br /> and copy it and paste it above.</div>
						</td>
					</tr>
					<tr>
						<th scope="row">When to Load Tynt Insight</th>
						<td>
							<select name="tynt-where" id="tynt-where">
		';
		foreach(self::$defs['tynt-where'] as $k=>$v){
			echo '<option value="'.$k.'" ';
			echo ($k==$options['tynt-where']) ? ' selected="selected"':'';
			echo '>'.$v.'</option>';
		}		
		echo'							
							</select>
							<div>Default is WordPress footer, but you may want to load script in the header so it works before even page loads. Downside of loading script in the header is overall slower website response. Select last option if you want to paste code into template yourself.</div>
							<div>
								<strong>Selfinstall code:</strong>
								<pre>
if ( function_exists(\'tyntInsight\') ){
	tyntInsight();
}
								</pre>
							</div>
						</td>
					</tr>
					
				</tbody>
			</table>
			<p>For more information check out <a href="http://www.tynt.com/" target="_blank">http://www.tynt.com/</a>.
		';
		echo '<div class="icon32" id="icon-themes"><br /></div><h2>Tynt Insight Settings</h2>';
		
		echo '
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row" colspan="2"><h3>Attribution</h3></th>
					</tr>
					<tr>
						<th scope="row">
							Disable Atribution:
						</th>
						<td>
							<input type="checkbox" id="param-a" name="param-a" value="false" ';
		echo ($options['param-a'] != 'empty') ? ' checked="checked"':'';
		echo ' /><label>&nbsp;Will remove attribution, but not sponsor link</label>
						</td>
					</tr>
					<tr>
						<th scope="row">
							Link Prefix:
						</th>
						<td>
							<input type="text" id="param-ap" name="param-ap" class="regular-text" value="';
		echo ($options['param-ap'] != 'empty') ? $options['param-ap']:'';							
		echo '" />
						</td>
					</tr>
					<tr>
						<th scope="row">
							Link Suffix:
						</th>
						<td>
							<input type="text" id="param-as" name="param-as" class="regular-text" value="';
		echo ($options['param-as'] != 'empty') ? $options['param-as']:'';							
		echo '" />
						</td>
					</tr>
					<tr>
						<th scope="row">
							Link Placement:
						</th>
						<td>
		';
		foreach(self::$defs['tynt-options']['link-format'] as $k=>$v){
			echo '<input id="link-formating" name="link-formating" type="radio" value="'.$k.'" ';
			echo ($k==$options['link-formating']) ? ' checked="checked" ':'';
			echo '/><label>'.$v.'</label><br />';
		}		
		echo '							
						</td>
					</tr>
					<tr>
						<th scope="row">
							Attribution Link Placement:
						</th>
						<td>
		';
		foreach(self::$defs['tynt-options']['link-placement'] as $k=>$v){
			echo '<input id="param-t" name="param-t" type="radio" value="'.$k.'" ';
			echo ($k==$options['param-t']) ? ' checked="checked" ':'';
			echo '/><label>'.$v.'</label>&nbsp;&nbsp;&nbsp;&nbsp;';
		}		
		echo '							
						</td>
					</tr>					
					<tr>
						<th scope="row">Add Creative Commons License</th>
						<td>
							<select name="param-cc" id="param-cc">
		';
		foreach(self::$contebtLicence as $k=>$v){
			echo '<option value="'.$k.'" ';
			echo ($k==$options['param-cc']) ? ' selected="selected"':'';
			echo '>'.$v.'</option>';
		}		
		echo '							
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row" colspan="2"><h3>Address Bar Tracking</h3></th>
					</tr>
					<tr>
						<th scope="row">
							Enable Address Bar Tracking:
						</th>
						<td>
	                        <input id="param-b" name="param-b" value="true" type="checkbox" ';
		echo ($options['param-b'] == 'true') ? ' checked="checked"':'';							
		echo ' /><label>&nbsp;</label>
	                        <div>
	                        	<input id="param-ba" name="param-ba" value="true" type="checkbox" ';
		echo ($options['param-ba'] == 'true') ? ' checked="checked"':'';
		echo ($options['param-b'] != 'true') ? ' disabled="disabled"':'';							
		echo ' />&nbsp;<label>Exclude Tracking Homepage</label><br />
	                        	Applies to the root path only - e.g. http://www.tynt.com/
	                        </div>
						</td>
					</tr>					
					
					<tr>
						<th scope="row" colspan="2">
							<h3>Link Sponsorship</h3>
							<p>You have to provide message, name and link in order to enable this option.</p>
						
						</th>
					</tr>
					<tr>
						<th scope="row">
							Extra link message:
						</th>
						<td>
							<input type="text" id="tynt-sponsor-text" name="tynt-sponsor-text" class="regular-text" value="';
		echo ($options['tynt-sponsor-text'] != 'empty') ? $options['tynt-sponsor-text']:'';							
		echo '" />
						</td>
					</tr>	
					<tr>
						<th scope="row">
							Sponsor Name:
						</th>
						<td>
							<input type="text" id="tynt-sponsor-name" name="tynt-sponsor-name" class="regular-text" value="';
		echo ($options['tynt-sponsor-name'] != 'empty') ? $options['tynt-sponsor-name']:'';							
		echo '" />
						</td>
					</tr>					
					<tr>
						<th scope="row">
							Link Destination:
						</th>
						<td>
							<input type="text" id="tynt-sponsor-link" name="tynt-sponsor-link" class="regular-text" value="';
		echo ($options['tynt-sponsor-link'] != 'empty') ? $options['tynt-sponsor-link']:'';							
		echo '" />
							<div> like: http://www.mysponsordomain.com/article</div>
						</td>
					</tr>
					<tr>
						<th scope="row" colspan="2" id="tynt-sponsor-preview">Link Sponsorship Preview: <span>';
		echo ($options['param-el'] != 'empty') ? $options['param-el']:'';							
		echo '</span></th>
					</tr>					
				</tbody>
			</table>
			<input type="hidden" id="param-el" name="param-el" value="';
		echo ($options['param-el'] != 'empty') ? urlencode($options['param-el']):'';							
		echo '" />
			
			
		';
		
		
		echo '
			<p class="submit"><input type="submit" id="action" class="button" value="Save Changes" name="action"></p>
		';
		echo '</form>';
		echo '</div>';
	}
	
	/**
	 * 
	 * @return Array()
	 */
	function activate(){
		add_option(self::$options['key'], serialize(self::$options),'','yes');
		return self::$options;
	}
	function deactivate(){
		delete_option(self::$options['key']);
	}	
	function getCode(){
		$code = '';
		$options=self::$options;
		$options=array_merge($options,unserialize(get_option($options['key'])));
		if(isset($options['tynt-id']) && !empty($options['tynt-id'])){
			// let's build params:
			$aParms = array();
			foreach($options as $k=>$v){
				if(stripos($k,'param-') !== FALSE && $v != 'empty'){
					$k = explode('-',$k);
					$k = $k[1];
					$aParms[$k] = urldecode($v);
				}
			}
			// clean up js a bit
			$params = str_replace('"false"','false',json_encode($aParms));
			$params = str_replace('"true"','true',$params);
			$code='
				<script type="text/javascript">
				if(document.location.protocol==\'http:\'){
				 var Tynt=Tynt||[];Tynt.push(\''.$options['tynt-id'].'\');';
			$code .='Tynt.i='.$params.';';//Tynt.i={"a":false,"b":true,"ba":true};			
			$code .='(function(){var s=document.createElement(\'script\');s.async="async";s.type="text/javascript";s.src=\'http://tcr.tynt.com/ti.js\';var h=document.getElementsByTagName(\'script\')[0];h.parentNode.insertBefore(s,h);})();
				}
				</script>					
			';
		}		
		return $code;
	}
	function printTyntInsight(){
		echo self::getCode();
	}
}
add_action('init', array('TyntComForWordPress','init'));

/**
 * 
 * @return string();
 */
function tyntInsight(){
	echo TyntComForWordPress::getCode();	
}

register_deactivation_hook( __FILE__, array('TyntComForWordPress','deactivate') ); 