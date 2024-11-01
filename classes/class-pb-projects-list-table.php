<?php
class PB_Projects_List_Table extends WP_List_Table {

    var $data = '';
    
    function __construct($query=''){
        global $status, $page;
                
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'project',     //singular name of the listed records
            'plural'    => 'projects',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );

        $this->data = $query;
        
    }
    
    function column_default($item, $column_name){
        return $item[$column_name];
    }
    
    function column_project_name($item){
        
        //Return the title contents
        return sprintf('<a href="%1$s">%2$s</a>',
            /*$1%s*/ $item['uri'],        
            /*$2%s*/ $item['project_name']
        );
    }
    
    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/ $item['id']                //The value of the checkbox should be the record's id
        );
    }

    function column_client($item){
        return pb_filter_data('clients', $item['client_id']);
    }     

    function column_closed($item){
        return ($item['closed']) ? 'yes' : 'no';
    }  

    function column_important($item){
        return ($item['important']) ? 'yes' : 'no';
    }

    function column_archived($item){
        return ($item['archived']) ? 'yes' : 'no';
    }

    function column_status($item){
        switch ($item['active']) {
            case 0:
                $status = '<div class="status red"></div>';
                break;
            case 1:
                $status = '<div class="status green"></div>';
                break;
            case 2:
                $status = '<div class="status amber"></div>';
                break;
        }        

        return $status;
    }  
    
    function get_columns(){
        $columns = array(
            'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
            'project_name'     => 'Project Name',
            'description'    => 'Description',
            'date_due'  => 'Due date',
            'client'  => 'Client',
            'important'  => 'Important',
            'status'  => 'Status',
            'closed'  => 'Closed',
            'archived'  => 'Archived'
        );
        return $columns;
    }
    
    function get_sortable_columns() {
        $sortable_columns = array(
            'project_name'     => array('project_name',true),     //true means its already sorted
            'date_due'  => array('date_due',false),
            'status'  => array('active',false),
            'important'  => array('important',false),
            'archived'  => array('archived',false),           
            'closed'  => array('closed',false)
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