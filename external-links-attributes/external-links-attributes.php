<?php
/*
 * Plugin Name: External Links Attributes
 * Description: Adds attributes to links and displays custom content
 * Author:      Vashchenko Vladimir
 */

register_activation_hook( __FILE__, array( 'ela', 'elaInstall' ) );
register_activation_hook( __FILE__, array( 'ela', 'firstInsertDb' ) );
register_uninstall_hook( __FILE__, array( 'ela', 'elaUninstall' ));

$ela = new ela();
if (isset($_POST['submit'])) {
	$ela->insertDb();
}
$ela->runEla();

class ela {
	public function __construct() {
		// back end
		add_action ( 'plugins_loaded', array( $this, 'textDomain'));
		add_action('admin_menu', array( $this, 'elaMenu'));

	}

	public function textDomain(){
		load_plugin_textdomain( 'ela', false, dirname( plugin_basename( __FILE__ ) ) );
	}

	public function elaInstall(){
	  global $wpdb;
	  
	  require_once(ABSPATH.'wp-admin/includes/upgrade.php');

	  dbDelta("CREATE TABLE IF NOT EXISTS `{$wpdb -> prefix}ela_table` (
	    `id` INT(2) NOT NULL PRIMARY KEY AUTO_INCREMENT,
	    `nofollow_active` TINYINT(1),
	    `target_active` TINYINT(1),
	    `custom_content_active` TINYINT(1),
	    `custom_content_before` TINYINT(1),
	    `custom_content_after` TINYINT(1),
	    `custom_contetnt_desc` TEXT,
	    UNIQUE KEY id (id)
	  ) {$wpdb -> get_charset_collate()};"); 

	}

	public function firstInsertDb() {
		global $wpdb;
		$table_name = $wpdb->prefix.'ela_table';
		$first_id = $wpdb->get_results( "SELECT id FROM $table_name WHERE id='1'" );
		$item = reset($first_id);
		$first_id_checked = $item->$first_id;
		if(!isset($first_id_checked)) {
			$wpdb->insert($table_name, array(
			    'nofollow_active' => '1',
			    'target_active' => '1',
			    'custom_content_active' => '1',
			    'custom_content_before' => '1'
				)
			);
		}
	}

	public function elaUninstall() {
		global $wpdb;
		$table_name = $wpdb->prefix.'ela_table';
		$sql = "DROP TABLE IF EXISTS $table_name";
	     $wpdb->query($sql);
	}

	public function elaMenu() {
	  add_options_page('ELA Options', 'External Links Attributes', 8, 'ela-plugin', array( $this, 'elaAdminContent' ));
	}

	private function getNofollowActive() {	
		global $wpdb;
		$table_name = $wpdb -> prefix.'ela_table';
		$nofollow_active = $wpdb->get_results( "SELECT nofollow_active FROM $table_name WHERE id='1'" );
		$item = reset($nofollow_active);
		$nofollow_checked = $item->nofollow_active;
		if ($nofollow_checked == '1') {
			$nofollow_checked = 'checked';
		}
		return $nofollow_checked;
	}

	private function getTargetActive() {
		global $wpdb;
		$table_name = $wpdb -> prefix.'ela_table';
		$target_active = $wpdb->get_results( "SELECT target_active FROM $table_name WHERE id='1'" );
		$item = reset($target_active);
		$target_checked = $item->target_active;
		if ($target_checked == '1') {
			$target_checked = 'checked';
		}
		return $target_checked;
	}
	
	private function getCustomContentActive() {	
		global $wpdb;
		$table_name = $wpdb -> prefix.'ela_table';
		$custom_content_active = $wpdb->get_results( "SELECT custom_content_active FROM $table_name WHERE id='1'" );
		$item = reset($custom_content_active);
		$custom_content_checked = $item->custom_content_active;
		if ($custom_content_checked == '1') {
			$custom_content_checked = 'checked';
		}
		return $custom_content_checked;
	}

	private function getCustomContentBefore() {
		global $wpdb;
		$table_name = $wpdb -> prefix.'ela_table';
		$custom_content_before = $wpdb->get_results( "SELECT custom_content_before FROM $table_name WHERE id='1'" );
		$item = reset($custom_content_before);
		$before_content_checked = $item->custom_content_before;
		if ($before_content_checked == '1') {
			$before_content_checked = 'checked';
		}
		return $before_content_checked;
	}

	private function getCustomContentAfter() {	
		global $wpdb;
		$table_name = $wpdb -> prefix.'ela_table';
		$custom_content_after = $wpdb->get_results( "SELECT custom_content_after FROM $table_name WHERE id='1'" );
		$item = reset($custom_content_after);
		$after_content_checked = $item->custom_content_after;
		if ($after_content_checked == '1') {
			$after_content_checked = 'checked';
		}
		return $after_content_checked;
	}

	private function getCustomContentDesc() {
		global $wpdb;
		$table_name = $wpdb -> prefix.'ela_table';
		$custom_contetnt_desc = $wpdb->get_results( "SELECT custom_contetnt_desc FROM $table_name WHERE id='1'" );
		$item = reset($custom_contetnt_desc);
		$custom_contetnt_desc = $item->custom_contetnt_desc;
		return $custom_contetnt_desc;
	}

	public function elaAdminContent() {
		global $wpdb;
		echo '<form action="" method="post" class="ela_form">
		    <h2>'.esc_html__('Settings External Links Attributes plugin', 'ela').'</h2>
		    <table>
			    <tr>
			    	<td><input type="checkbox" name="add_nofollow" value="1" '.$this->getNofollowActive().'> '.esc_html__('Add <i>rel="nofollow"</i> to external links').'</td>
			    </tr>

			    <tr>
			    	<td><input type="checkbox" name="add_target" value="1" '.$this->getTargetActive().'> '.esc_html__('Add <i>target="_blank"</i> to external links').'</td>
			    </tr>

			    <tr>
			    	<td><input type="checkbox" name="customContent" value="1" '.$this->getCustomContentActive().'> '.esc_html__('Add custom content').'</td>
			    </tr>

			    <tr>
				    <td><input type="radio" name="content" value="before" '.$this->getCustomContentBefore().'> '.esc_html__('Before content').'</br>

				    <input type="radio" name="content" value="after" '.$this->getCustomContentAfter().'>
				    '.esc_html__('After content').'</td>
			    </tr>

			    <tr>
			    	<td>'.esc_html__('Custom text (HTML allowed)').'<Br>
		   			<textarea name="custom_text" cols="60" rows="5">'.$this->getCustomContentDesc().'</textarea></p></td>
			    </tr>
			    <tr>
			    	<td><p><input type="submit" name="submit" value="'.esc_html__('Save').'"></p></td>
			    </tr>
			</table>
		</form>';
	}
	public function insertDb() {
		global $wpdb;
		$table_name = $wpdb -> prefix.'ela_table';
		if (isset($_POST['add_nofollow'])) {		
			$wpdb->update( $table_name,
				[ 'nofollow_active' => '1' ],
				[ 'id' => '1' ]
			);
		}
		else {
			$wpdb->update( $table_name,
				[ 'nofollow_active' => '0' ],
				[ 'id' => '1' ]
			);
		};

		if (isset($_POST['add_target'])) {		
			$wpdb->update( $table_name,
				[ 'target_active' => '1' ],
				[ 'id' => '1' ]
			);
		}
		else {
			$wpdb->update( $table_name,
				[ 'target_active' => '0' ],
				[ 'id' => '1' ]
			);
		};

		if (isset($_POST['customContent'])) {		
			$wpdb->update( $table_name,
				['custom_content_active' => '1' ],
				['id' => '1' ]
			);
		}
		else {
			$wpdb->update( $table_name,
				['custom_content_active' => '0' ],
				['id' => '1' ]
			);
		};

		if (isset($_POST['content'])) {
			if($_POST['content'] == 'before') {		
				$wpdb->update( $table_name,
					['custom_content_before' => '1', 'custom_content_after' => '0' ],
					['id' => '1' ]
				);
			}
			else {
				$wpdb->update( $table_name,
					['custom_content_before' => '0', 'custom_content_after' => '1' ],
					['id' => '1' ]
				);
			};
		};

		if (isset($_POST['custom_text'])) {		
			$wpdb->update( $table_name,
				['custom_contetnt_desc' => $_POST['custom_text'] ],
				['id' => '1' ]
			);
		};
	}

	private function relNofollow() {
		add_filter( 'the_content', array( $this, 'postsNofollow' ) );
	}

	private function targetBlanc() {
		add_filter( 'the_content', array( $this, 'postsTarget' ) );
	}

	private function addBeforeContent() {
		add_filter( 'the_content', array( $this, 'startContent' ) );
	}

	private function addAfterContent() {
		add_filter( 'the_content', array( $this, 'endContent' ) );
	}


	private function nofollowCallback($matches){
		$a = $matches[0];
		$site_url = site_url();
	 
		if (strpos($a, 'rel') === false){
			$a = preg_replace("%(href=\S(?!$site_url))%i", 'rel="nofollow" $1', $a);
		} elseif (preg_match("%href=\S(?!$site_url)%i", $a)){
			$a = preg_replace('/rel=S(?!nofollow)\S*/i', 'rel="nofollow"', $a);
		}
		return $a;
	}
	 
	public function postsNofollow($content) {
		return preg_replace_callback('/<a[^>]+/', array( $this, 'nofollowCallback' ), $content);
	}
	 
	private function targetCallback($matches){
		$a = $matches[0];
		$site_url = site_url();
	 
		if (strpos($a, 'target') === false){
			$a = preg_replace("%(href=\S(?!$site_url))%i", 'target="_blank" $1', $a);
		} elseif (preg_match("%href=\S(?!$site_url)%i", $a)){
			$a = preg_replace('/target=S(?!_blank)\S*/i', 'target="_blank"', $a);
		}
		return $a;
	}
	 
	public function postsTarget($content) {
		return preg_replace_callback('/<a[^>]+/', array( $this, 'targetCallback' ), $content);
	}


	public function startContent($content){

		$content = $this->getCustomContentDesc().$content;
		return $content;
	}

	public function endContent($content){

		$content.= $this->getCustomContentDesc();
		return $content;
	} 

	public function runEla() {
		$nofollow = $this->getNofollowActive();
		$target = $this->getTargetActive();
		$custom_content = $this->getCustomContentActive();
		if($nofollow == 'checked') {
			$this->relNofollow();
		}
		if($target == 'checked') {
			$this->targetBlanc();
		}
		if($custom_content == 'checked') {
			$where_content = $this->getCustomContentBefore();
			if($where_content == 'checked') {
				$this->addBeforeContent();
			} else {
				$this->addAfterContent();
			}
		}
	}
}
