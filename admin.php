<?php
function pb_activation_options_init() {
  if (pb_get_credentials() === false) {
    add_option('pb_credentials', pb_get_activation_default_options());
  }

  register_setting(
    'pb_activation_options',
    'pb_credentials',
    'pb_activation_options_validate'
  );
}

add_action('admin_init', 'pb_activation_options_init');


function pb_init_activation_page() {
  $data = pb_get_request( 'user', 60*60*24*356 );
  $pb_activation_options = pb_get_credentials();
  if (!$pb_activation_options['first_run'] || $data == false ) {
    $pb_activation = add_menu_page(
      'Project Bubble ACtivation',
      'Project Bubble',
      'manage_options',
      'pb-activation',
      'pb_activation_page'
    );
  } 
  else {
    if (is_admin() && isset($_GET['page']) && $_GET['page'] === 'pb-activation') {
      wp_redirect(admin_url('admin.php?page=project-bubble'));
      exit;
    }
  }

}
add_action('admin_menu', 'pb_init_activation_page');

function pb_get_activation_default_options() {
  $default_options = array(
    'first_run'      => false,
    'pb_key'         => '',
    'pb_domain'      => '',
  );

  return $default_options;
}

function pb_get_credentials() {
  return get_option('pb_credentials', pb_get_activation_default_options());
}

function pb_activation_page() { ?>

  <div class="wrap">
    <?php screen_icon(); ?>
    <h2>Project Bubble Activation</h2>
    <?php settings_errors(); ?>

    <form method="post" action="options.php">

      <?php
        settings_fields('pb_activation_options');
        $pb_activation_options = pb_get_credentials();
        $pb_activation_default_options = pb_get_activation_default_options();
      ?>

      <input type="hidden" value="1" name="pb_credentials[first_run]" />

      <table class="form-table">

        <tr valign="top"><th scope="row">Domain</th>
          <td>
            <fieldset><legend class="screen-reader-text"><span>Domain/span></legend>
              <input class="regular-text" id="pb_domain" name="pb_credentials[pb_domain]" value="<?php echo $pb_activation_options['pb_domain']; ?>" type="text">
              <br />
            </fieldset>
          </td>
        </tr>

        <tr valign="top"><th scope="row">API key</th>
          <td>
            <fieldset><legend class="screen-reader-text"><span>API key</span></legend>
              <input class="regular-text" id="pb_key" name="pb_credentials[pb_key]" value="<?php echo $pb_activation_options['pb_key']; ?>" type="text">
              <br />
            </fieldset>
          </td>
        </tr>

      </table>

      <?php submit_button('Connect!'); ?>
    </form>
  <?php
	$data = pb_get_request( 'user', 60*60*24*356 );
	if ( $data === false && $pb_activation_options['first_run'] == 1 ) {
		echo '<div class="error"><p>We cannot connect to the API. Please check your credentials!</p></div>';
	}  
  ?>
  </div>

<?php }

function pb_activation_options_validate($input) {
  $output = $defaults = pb_get_activation_default_options();

  if (isset($input['first_run'])) {
    if ($input['first_run'] === '1') {
      $input['first_run'] = true;
    }
    $output['first_run'] = $input['first_run'];
  } 
  if ( isset($input['pb_domain']) ) {
	$output['pb_domain'] = untrailingslashit($input['pb_domain']);
  } 
  if ( isset($input['pb_key']) ) {
	$output['pb_key'] = $input['pb_key'];
  }

  return $output;
}

class ProjectBubble_Dashboard extends scbBoxesPage {

	function setup() {
		$this->args = array(
			'page_title' => 'Project Bubble Dashboard',
			'menu_title' => 'Project Bubble',
			'toplevel' => 'menu',
			'action_link' => false
		);

		$this->boxes = array(
			array( 'company', 'My Company', 'normal' ),
			array( 'user', 'My Account', 'right' )
		);
	}

	function user_box() {
		$data = pb_get_request( 'user', 60*60*24*356 );

		echo html( 'table', array( 'class' => 'pb-table' ),
			  html( 'tr', 
			  	html( 'td', '<b>Name:</b>' ),
			  	html( 'td', '<span>'.$data['first_name'].' '.$data['last_name'].'</span>' )  
			  ),
			  html( 'tr', 
			  	html( 'td', '<b>Email:</b>' ),
			  	html( 'td', '<span>'.$data['email'].'</span>' )  
			  )		  
			 );	
		?>
		<form action="<?php echo admin_url() ?>admin.php?page=pb-disconnect" method="post">
			<input type="hidden" value="1" name="submit_disconnect" />
			<p>Use the button below if you want to disconnect this website from the Project Bubble account. </p>
			<?php submit_button('Disconnect!'); ?>
		</form>
		<?php	
	}

	function company_box() {
		$data = pb_get_request( 'company', 60*60*24*356 );

		$state = ($data['state'] ) ? '/'.$data['state'] : '';

		echo html( 'table', array( 'class' => 'pb-table' ),
			  html( 'tr', 
			  	html( 'td', '<b>Company Name:</b>' ),
			  	html( 'td', '<span>'.$data['company_name'].'</span>' )  
			  ),
			  html( 'tr', 
			  	html( 'td', '<b>Company Name:</b>' ),
			  	html( 'td', '<span>'.$data['domain'].'</span>' )  
			  ),		
			  html( 'tr', array('class' => 'rowspan'), 
			  	html( 'td', array('rowspan' => 2), '<b>Company Address:</b>' ),
			  	html( 'td', '<span style="font-size:18px;">'.$data['address1'].'</span>' )  
			  ),
			  html( 'tr', 
			  	html( 'td', '<span>'.$data['address2'].'</span>' )  
			  ),
			  html( 'tr', 
			  	html( 'td', '<b>City/State:</b>' ),
			  	html( 'td', '<span>'.$data['city'].$state.'</span>' )  
			  ),
			  html( 'tr', 
			  	html( 'td', '<b>ZIP/Post code:</b>' ),
			  	html( 'td', '<span>'.$data['postcode'].'</span>' )  
			  ),
			  html( 'tr', 
			  	html( 'td', '<b>Country:</b>' ),
			  	html( 'td', '<span>'.$data['country'].'</span>' )  
			  ),
			  html( 'tr', 
			  	html( 'td', '<b>Phone:</b>' ),
			  	html( 'td', '<span>'.$data['phone'].'</span>' )  
			  ),			  
			  html( 'tr', 
			  	html( 'td', '<b>Default Currency:</b>' ),
			  	html( 'td', '<span>'.$data['currency'].'</span>' )  
			  ),
			  html( 'tr', 
			  	html( 'td', '<b>Company Details:</b>' ),
			  	html( 'td', '<pre>'.$data['signature'].'</pre>' )  
			  )			  
			 );		
	}

	function page_head() {
?>
	<style type="text/css">
	table.pb-table { width:100%; }
	table.pb-table td { padding-bottom: 20px; }
	table.pb-table tr.rowspan td { padding-bottom: 0px; }
	table.pb-table td span { font-size:18px; }
	table.pb-table td b { color: #8F8F8F }
	</style>
<?php
	}	
}

class ProjectBubble_Projects_Page extends scbAdminPage {

	function setup() {
		$this->args = array(
			'page_title' => 'Projects',
			'parent' => 'project-bubble',
			'action_link' => false
		);
	}

	function page_content() {
		$data = pb_get_request( 'projects' );

		pb_render_list_page($data);		
	}

	function page_head() {
?>
	<style type="text/css">
	div.status { 
		 height: 18px;
		 -moz-border-radius:75px;
		 -webkit-border-radius: 75px;
		 width: 18px;
	}
	div.green { background: #9ed500; }
	div.red { background: #d05a00; }
	div.amber { background: #ffc500; }
	.column-project_name { width: 25%; }
	.column-description { width: 20%; }
	</style>
<?php
	}
}

class ProjectBubble_Tasks_Page extends scbAdminPage {

	function setup() {
		$this->args = array(
			'page_title' => 'Tasks',
			'parent' => 'project-bubble',
			'action_link' => false
		);
	}

	function page_content() {

		$data = pb_get_request( 'tasks' );

		pb_render_list_page($data, 'PB_Tasks_List_Table');		
	}

	function page_head() {
?>
	<style type="text/css">
	div.status { 
		 height: 18px;
		 -moz-border-radius:75px;
		 -webkit-border-radius: 75px;
		 width: 18px;
	}
	div.green { background: #9ed500; }
	div.red { background: #d05a00; }
	div.amber { background: #ffc500; }
	.column-task_name { width: 25%; }
	.column-description { width: 20%; }
	</style>
<?php
	}
}

class ProjectBubble_Clients_Page extends scbAdminPage {

	function setup() {
		$this->args = array(
			'page_title' => 'Clients',
			'parent' => 'project-bubble',
			'action_link' => false
		);
	}

	function page_content() {

		$data = pb_get_request( 'clients' );

		pb_render_list_page($data, 'PB_Clients_List_Table');		
	}
}

class ProjectBubble_Contacts_Page extends scbAdminPage {

	function setup() {
		$this->args = array(
			'page_title' => 'Contacts',
			'parent' => 'project-bubble',
			'action_link' => false
		);
	}

	function page_content() {

		$data = pb_get_request( 'contacts' );

		pb_render_list_page($data, 'PB_Contacts_List_Table');		
	}
}

class ProjectBubble_Settings_Page extends scbAdminPage {

	function setup() {
		$this->args = array(
			'page_title' => 'Settings',
			'parent' => 'project-bubble',
			'action_link' => false
		);
	}

	function page_content() {
		echo $this->form_table( array(
			array(
				'title' => 'Cache Time (in seconds):',
				'type' => 'text',
				'name' => 'pb_cache_time',
				'desc' => '<p>Making this number too small could result in much more API requests!<br />
				This could be considered as abuse of the service, please read <a href="http://projectbubble.com/developers/api-rules">API rules</a>.</p>
				The default value is "86400" seconds which means the data are refreshed every 24 hours.'
			)
		) );
		?>
		<form action="" method="post">
		<input type="hidden" value="1" name="submit_delete" />
		<p>Use the button below only if you want to delete all the cached data (projects, tasks, clients, contacts...)! They will be stored again once they are requested for viewing.</p>
		<input type="submit" class="button-primary" value="Delete all cached data!" />
		</form>
		<?php
		if ( isset($_POST['submit_delete']) && $_POST['submit_delete'] ) {
			WP_ProjectBubble::deactivate();
			echo '<div class="updated"><p>WP Project Bubble: All cached data has been deleted!</p></div>';
		}		
	}

}
	
class ProjectBubble_Disconnect_Page extends scbAdminPage {

	function setup() {
		$this->args = array(
			'page_title' => 'You have successfully disconnected your account!',
			'page_slug' => 'pb-disconnect',
			'parent' => NULL,
			'action_link' => false
		);
	}

	function page_content() {
		WP_ProjectBubble::deactivate();
		delete_option( 'pb_credentials' );
		echo '<div class="updated"><p>Would you like to <a href="'.admin_url().'admin.php?page=pb-activation">connect some other account</a>?</p></div>';				
	}
}

if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

require_once( plugin_dir_path(__FILE__).'/classes/class-pb-projects-list-table.php' );
require_once( plugin_dir_path(__FILE__).'/classes/class-pb-tasks-list-table.php' );
require_once( plugin_dir_path(__FILE__).'/classes/class-pb-clients-list-table.php' );
require_once( plugin_dir_path(__FILE__).'/classes/class-pb-contacts-list-table.php' );

function pb_filter_data( $resource='', $id='' ) {
	if ( !$resource || !$id )
		return;

	$data = pb_get_request( $resource );

	foreach ( $data as $d ) {
		if ( $d['id'] == $id )
			return $d[rtrim($resource,'s').'_name'];
	}
}

function pb_render_list_page($query, $class='PB_Projects_List_Table'){    
    //Create an instance of our package class...
    $obj = new $class($query);
    //Fetch, prepare, sort, and filter our data...
    $obj->prepare_items();
    
    ?>   
        <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
        <form id="<?php echo $class; ?>-filter" method="get">
            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
            <!-- Now we can render the completed list table -->
            <?php $obj->display() ?>
        </form>
        
    <?php
}
