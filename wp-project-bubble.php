<?php
/*
Plugin Name: WP Project Bubble
Version: 0.1
Description: WordPress plugin for Project Bubble project management tool
Author: Ján Bočínec
Author URI: http://johnnypea.wp.sk/
Plugin URI: http://www.webikon.sk/
Author Email: johnnypea@wp.sk


Copyright (C) 2011 Ján Bočínec (johnnypea@wp.sk)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

// Load scbFramework (http://wordpress.org/extend/plugins/scb-framework/) maintained by scribu (http://scribu.net/)
require dirname(__FILE__) . '/scb/load.php';

function _scb_pb_init() {

	new WP_ProjectBubble();

}
scb_init( '_scb_pb_init' );

class WP_ProjectBubble {
	 
	/*--------------------------------------------*
	 * Constructor
	 *--------------------------------------------*/
	
	/**
	 * Initializes the plugin by setting localization, filters, and administration functions.
	 */
	function __construct() {
	
		register_activation_hook( __FILE__, array( &$this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( &$this, 'deactivate' ) );

		// Creating an options object
		$settings = new scbOptions( 'pb_settings', __FILE__, array(
			'pb_cache_time' => 86400,
		) );

		if ( is_admin() ) {
			require_once( dirname( __FILE__ ) . '/admin.php' );

			$options = pb_get_credentials();
			$data = pb_get_request( 'user', 60*60*24*356 );

			if ( $options['first_run'] == 1 && $data !== false ) {
				new ProjectBubble_Dashboard( __FILE__ );
				new ProjectBubble_Projects_Page( __FILE__ );
				new ProjectBubble_Tasks_Page( __FILE__ );
				new ProjectBubble_Clients_Page( __FILE__ );
				new ProjectBubble_Contacts_Page( __FILE__ );
				new ProjectBubble_Settings_Page( __FILE__, $settings );
			}
			new ProjectBubble_Disconnect_Page( __FILE__ );
		}	

		add_action(	'wp_dashboard_setup',	array( &$this, 'pb_dashboard_setup' ) );
		add_action('admin_print_styles-index.php', array( &$this, 'pb_dashboard_styles' ) );

		add_shortcode( 'pbform', array( &$this, 'pb_form_shortcode' ) );

	} // end constructor

	/**
	 * Fired when the plugin is activated.
	 *
	 * @params	$network_wide	True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog 
	 */
	function activate( $network_wide=false ) {
		// TODO define activation functionality
	} // end activate
	
	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @params	$network_wide	True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog 
	 */
	function deactivate( $network_wide=false ) {
		delete_transient( 'pb_projects' );
		delete_transient( 'pb_tasks' );
		delete_transient( 'pb_clients' );
		delete_transient( 'pb_contacts' );
		delete_transient( 'pb_company' );
		delete_transient( 'pb_user' );				
	} // end deactivate

	function pb_dashboard_styles() {
	?>
	<style type="text/css">
	#wppb_right_now p.sub,
	#wppb_right_now .table,
	#wppb_right_now .versions{margin:-12px;}
	#wppb_right_now .inside{font-size:12px;padding-top:20px;}
	#wppb_right_now p.sub{font-style:italic;font-family:Georgia,"Times New Roman","Bitstream Charter",Times,serif;padding:5px 10px 15px;color:#777;font-size:13px;position:absolute;top:-17px;left:15px;}
	#wppb_right_now .table{margin:0 -9px;padding:0 10px;position:relative;}
	#wppb_right_now .table_general{float:left;border-top:#ececec 1px solid;width:45%;}
	#wppb_right_now .table_user{float:right;border-top:#ececec 1px solid;width:45%;}
	#wppb_right_now table td{padding:3px 0;white-space:nowrap;}
	#wppb_right_now table tr.first td{border-top:none;}
	#wppb_right_now td.b{padding-right:6px;text-align:right;font-family:Georgia,"Times New Roman","Bitstream Charter",Times,serif;font-size:14px;width:1%;}
	#wppb_right_now td.b a{font-size:18px;}
	#wppb_right_now td.b a:hover{color:#d54e21;}
	#wppb_right_now .t{font-size:12px;padding-right:12px;padding-top:6px;color:#777;}
	#wppb_right_now .t a{white-space:nowrap;}
	#wppb_right_now .versions{padding:6px 10px 12px;clear:left;}
	#wppb_right_now .versions .b{font-weight:bold;}
	#wppb_right_now a.button{float:right;clear:right;position:relative;top:-5px;}	
	</style>
	<?php
	}

	function pb_dashboard_setup(){
		wp_add_dashboard_widget( 'wppb_right_now', 'Project Bubble Overview', array( &$this, 'admin_dashboard_widget' ) );	
	}

	function admin_dashboard_widget(){
		$plugin_data = get_plugin_data( __FILE__ );
		?>
		<div class="table table_general">
			<p class="sub">You have:</p>
			<table>
				<tbody>
					<tr class="first"><td class="b"><a href="<?php echo admin_url() ?>admin.php?page=projects"><?php echo $this->pb_item_counter( 'projects' ); ?></a></td>
						<td class="t"><a href="<?php echo admin_url() ?>admin.php?page=projects">Projects</a></td></tr>
					<tr><td class="b"><a href="<?php echo admin_url() ?>admin.php?page=tasks"><?php echo $this->pb_item_counter( 'tasks' ); ?></a></td>
						<td class="t"><a href="<?php echo admin_url() ?>admin.php?page=tasks">Tasks</a></td></tr>
					<tr><td class="b"><a href="<?php echo admin_url() ?>admin.php?page=clients"><?php echo $this->pb_item_counter( 'clients' ); ?></a></td>
						<td class="t"><a href="<?php echo admin_url() ?>admin.php?page=clients">Clients</a></td></tr>
				</tbody>
			</table>
		</div>
		<div class="versions" style="clear:both;">
		<p>This is <span class="b"><a href="http://wordpress.org/extend/plugins/wp-project-bubble/">WP Project Bubble</a></span> version <?php echo $plugin_data['Version']; ?></p>
		</div>
		<?php
	}

	function pb_item_counter( $resource='' ){
		if ( !$resource )
			return;

		return count( pb_get_request( $resource ) );
	}

	function pb_form_shortcode() {
		global $pb_form_error;

		wp_enqueue_script('formalize-script', plugins_url('js/jquery.formalize.min.js', __FILE__), array('jquery'));
		wp_enqueue_style('formalize-style', plugins_url('css/pb-form.css', __FILE__));

		if($pb_form_error)
			return $pb_form_error;

		if( isset($_GET['status']) ) {
			return '<div class="pb-form-update"><p>Your project was successfully submitted!</p></div>';
		}

		return '<div id="pb-form">
		<div id="pb-form-title">Add New Project</div>
  <form action="" method="post">
  	<input type="hidden" value="1" name="submit_project" />
	<table>
		<tr>
	      <th>
	        <label for="project_name">Project Name:</label>
	      </th>
	      <td>
	        <input class="input_full" type="text" id="project_name" name="project_name">
	      </td>
	    </tr>
	    <tr>
	      <th>
	        <label for="description">Description:</label>
	      </th>
	      <td>
	        <textarea name="description" rows="4"></textarea>
	      </td>
	    </tr>
		<tr>
	      <th>
	        <label for="date_due">Due Date:</label>
	      </th>
	      <td>
	        <input class="input_full" type="text" id="date_due" name="date_due" placeholder="MM/DD/YYYY">
	      </td>
	    </tr>
		<tr>
	      <th>
	        <label for="contact_email">Contact email:</label>
	      </th>
	      <td>
	    	<input type="email" id="contact_email" name="contact_email" placeholder="name@example.com" />
	      </td>
	    </tr>
	    <tr>
	      <th>
	        <label for="notes">Notes</label>
	      </th>
	      <td>
	        <textarea name="notes" rows="4"></textarea>
	      </td>
	    </tr>
	</table>
	<input type="reset" value="Reset" class="float_left" />
	<input type="submit" value="Submit" class="float_right" />  	
  </form></div>';
	}
  
} // end class

function pb_get_request( $resource='', $cache='' ) {
		if ( !$resource )
			return;

		if ( $cache ) {
			$cache_time = $cache;
		} else if ( $options = get_option('pb_settings') ) {
			$cache_time = ($options['pb_cache_time']) ? $options['pb_cache_time'] : 86400;
		}
        
        $options = get_option('pb_credentials');
        // what is your subdomain?
        $subdomain = $options['pb_domain'];
        // what is your key
        $key = $options['pb_key'];

        //delete_transient( 'pb_'.$resource );
        $pb_resource = get_transient( 'pb_'.$resource );

        if (false === $pb_resource) {

            $response = wp_remote_get( $subdomain.'/api/'.$resource.'/format/json?X-PROJECTBUBBLE-KEY='.$key, array( 'sslverify' => false ) );

            if ( is_wp_error($response) )
            	return false;

            $resbody = wp_remote_retrieve_body( $response );

            $response_code = wp_remote_retrieve_response_code( $response );


            if ( $response_code == 404 )
            		return false;

            $pb_resource = json_decode( $resbody, TRUE );

            if ( $pb_resource['status'] === FALSE )            
            		return false;

            set_transient( 'pb_'.$resource, $pb_resource, $cache_time );

        }

        return $pb_resource;
} // End request()

function pb_post_request( $resource='', $parameters='' ) {
	if ( !$resource || !is_array($parameters) )
		return;
    
    $options = get_option('pb_credentials');
    // what is your subdomain?
    $subdomain = $options['pb_domain'];
    // what is your key
    $key = $options['pb_key'];

	$response = wp_remote_post( $subdomain.'/api/'.$resource, array(
		'sslverify' => false,
		'headers' => array('X-PROJECTBUBBLE-KEY'=>$key),
		'body' => $parameters,
	    )
	);

	return $response; 
} // End request()

function _date_is_valid($str) {
    if (substr_count($str, '/') == 2) {
        list($m, $d, $y) = explode('/', $str);
        return checkdate($m, $d, sprintf('%04u', $y));
    }

    return false;
}

add_action('init', 'pb_form_redirect');

function pb_form_redirect() {
	global $pb_form_error;
	if ( isset($_POST['submit_project']) && $_POST['submit_project'] ) {
		array_shift($_POST);
		$error = '';
		if ( empty($_POST['project_name']) ) {
			$error .= '<li>"PROJECT NAME" cannot be empty!</li>';
		}
		if ( !empty($_POST['date_due']) && !_date_is_valid($_POST['date_due']) ) {
			$error .= '<li>Invalid "DUE DATE" format!</li>';
		}
		if ( empty($_POST['contact_email']) ) {
			$error .= '<li>"CONTACT EMAIL" cannot be empty!</li>';
		}
		if ( $error ) {
			$pb_form_error = '<ul class="pb-form-error">'.$error.'<li>Please, <a href=""javascript: history.back()"><b>try again</b></a>.</li></ul>';
		} else {
			$_POST['date_due'] = date( "Ymd", strtotime($_POST['date_due']) );

			$response = pb_post_request( 'projects', $_POST );

			if( $response ) {
				$mail_body = "Project Name: ".$_POST['project_name']."\n\nDescription: ".$_POST['description']."\n\nDue Date: ".date("m/d/Y", strtotime($_POST['date_due']))."\n\nNotes: ".$_POST['notes']."\n\nContact Email: ".$_POST['contact_email'];
				wp_mail(get_bloginfo('admin_email'), 'New Project Added', $mail_body);		
			}
			wp_redirect( add_query_arg('status', 'thank_you', get_permalink()) );
			exit;
		}
	}
}
