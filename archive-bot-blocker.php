<?php
/*
Plugin Name: WayBack Archive.org Bot Blocker
Description: Super fast, light-weight plugin to block Bots - Backlink crawling bots, Wayback like archive crawling bots. Archive Bot Blocker users User-agent String to Block these bots   
Version: 1.1
Author: Sunny Wp
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

if(!class_exists('Archive_Bot_Blocker'))
{
	class Archive_Bot_Blocker{
		
		public function __construct() {
			
			if(is_admin())
			{
				add_action('admin_menu',array($this,'abb_admin_menu'));
			}
			add_action( 'wp', array($this,'abb_send_headers' ));
		}
		
		static function abb_active()
		{
			$abb_user_agent=array('archive.org','ia_archiver','domaintools','surveybot');
			update_option('abb_user_agent',implode("\n",$abb_user_agent));
			update_option('abb_error_code','414');	
			
		}
		public function abb_send_headers()
		{
			$abb_user_agent=strtolower(get_option('abb_user_agent'));
			$abb_error_code=get_option('abb_error_code');
			$abb_user_agents=explode("\n",$abb_user_agent);
			$user_agent = $_SERVER['HTTP_USER_AGENT']; 
			$is_valid=0;
			
			
			foreach($abb_user_agents as $ab_user_agent)
			{
				if(strpos(strtolower($user_agent),$ab_user_agent) !==false)
				{
					$is_valid=1;
					break;
				}
			
			}
			if($is_valid)
			{
				status_header($abb_error_code,"user agent not matched");
			}
		
		}
		
		public function abb_admin_menu()
		{
			add_options_page('Archive Bot Blocker','Archive Bot Blocker','manage_options','archive_bot_blocker',array($this,		'abb_settings_page'	));
		}
		
		public function abb_settings_page() {
			
			$notification_html="";
			if(isset($_POST['submit']))
			{
				if ( !wp_verify_nonce( $_POST['abb_nonce'], 'abb_nonce_action' ) ) {
						die("wp noonce not matched!!!");
						
				}
				
				if ( ! current_user_can( 'manage_options') ) {
						die("You have not access to save this options");
					
				}	
				
				$abb_user_agent = sanitize_textarea_field($_POST['abb_user_agent']);
				$abb_error_code = sanitize_text_field($_POST['abb_error_code']);
				update_option('abb_user_agent',$abb_user_agent);
				update_option('abb_error_code',$abb_error_code);	
				
				$notification_html='<div id="message" class="updated fade" style="margin:0px 20px 10px 2px;"><p><strong>Options Saved </strong></p></div>';
	
			}
		
			$abb_user_agent=get_option('abb_user_agent');
			$abb_error_code=get_option('abb_error_code');
		?>
		<div  id="poststuff">
		<?php echo $notification_html; ?>
		<div class="postbox">
			<h3 class="hndle">
			 <span>Archieve Bot Blocker</span>
			</h3>
			<div class="inside">
		
			<div>
			<form action="" method="post">
			<?php wp_nonce_field('abb_nonce_action', 'abb_nonce'); ?>
			<table><tr><td>	
				<label>Useragent:</label></td><td>
				<textarea rows="5" cols="50" name="abb_user_agent"><?php echo $abb_user_agent;?></textarea></p>
				</td></tr>
				<tr><td>
				<label>Error Code:</label></td>
				<td><select name="abb_error_code">
				<?php for($abb_e_code=400;$abb_e_code<=510;$abb_e_code++) { 
					echo '<option value="'.$abb_e_code.'"';
						if($abb_error_code == $abb_e_code)
							echo "selected";
					echo '>'.$abb_e_code.'</option>';
				}
				?>
					</select></td></tr>
				</table>	
				<input type="submit" name="submit" class="button button-primary"/>		
			</form>
			</div>
			</div></div></div>
		<?php	
		}

	}	

	new Archive_Bot_Blocker;	
	register_activation_hook( __FILE__, array( 'Archive_Bot_Blocker', 'abb_active' ) );
}	