<?php
class PB_Contacts_List_Table extends WP_List_Table {

    var $data = '';
    
    function __construct($query=''){
        global $status, $page;
                
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'contact',     //singular name of the listed records
            'plural'    => 'contacts',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );

        $this->data = $query;
        
    }
    
    function column_default($item, $column_name){
        return $item[$column_name];
    }

    function column_address($item){      
        return '<pre>'.$item['address'].'</pre>';
    }

    function column_client($item){
        return pb_filter_data('clients', $item['client_id']);
    } 

    function column_tel_mob_fax($item){  
        $tel = ( $item['tel'] ) ? $item['tel'] : 'none';
        $mob = ( $item['mobile'] ) ? ' / '.$item['mobile'] : ' / none';
        $fax = ( $item['fax'] ) ? ' / '.$item['fax'] : ' / none';  
        return $tel.$mob.$fax;
    }
    
    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/ $item['id']                //The value of the checkbox should be the record's id
        );
    }
    
    function get_columns(){
        $columns = array(
            'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
            'contact_name'     => 'Contact Name',
            'email'    => 'Email',
            'tel_mob_fax'  => 'Tel./Mobile/Fax',
            'role'  => 'Role',
            'address'  => 'Address',
            'client'  => 'Client'
        );
        return $columns;
    }
    
    function get_sortable_columns() {
        $sortable_columns = array(
            'contact_name'     => array('contact_name',true),     //true means its already sorted
            'email'  => array('email',false),
            'role'  => array('role',false)
        );
        return $sortable_columns;
    }
    
    function prepare_items() {
        
        $per_page = 20;
        
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        $this->_column_headers = array($columns, $hidden, $sortable);
        
        $data = $this->data;
                
        function usort_reorder($a,$b){
            $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'id'; //If no sort, default to title
            $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'desc'; //If no order, default to asc
            $result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
            return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
        }
        usort($data, 'usort_reorder');
               
        $current_page = $this->get_pagenum();
        
        $total_items = count($data);
        
        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);
        
        $this->items = $data;
        
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
    }
}