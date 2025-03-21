<?php if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class NF_Abstracts_Model
 */
class NF_Abstracts_Model
{
    /**
     * Database Object
     *
     * @var string
     */
    protected $_db = '';

    /**
     * ID
     *
     * The ID is assigned after being saved to the database.
     *
     * @var int
     */
    protected $_id = '';

    /**
     * Temporary ID
     *
     * The temporary ID is used to reference unsaved objects
     *   before they are stored in the database.
     *
     * @var string
     */
    protected $_tmp_id = '';

    /**
     * Type
     *
     * The type is used to pragmatically identify the type
     *   of an object without inspecting the class.
     *
     * @var string
     */
    protected $_type = '';

    /**
     * Parent ID
     *
     * The ID of the parent object.
     *
     * @var string
     */
    protected $_parent_id = '';

    /**
     * Parent Type
     *
     * The type of the parent object.
     *
     * @var string
     */
    protected $_parent_type = '';

    /**
     * Table Name
     *
     * The name of the table where the model objects are stored.
     *
     * @var string
     */
    protected $_table_name = '';

    /**
     * Meta Table Name
     *
     * The name of the table where the object settings are stored.
     *
     * @var string
     */
    protected $_meta_table_name = '';

    /**
     * ? Deprecated ?
     * @var string
     */
    protected $_relationships_table = 'nf3_relationships';

    /**
     * Columns
     *
     * A list of settings that are stored in the main table as columns.
     *   These settings are NOT stored in the meta table.
     *
     * @var array
     */
    protected $_columns = array();

    /**
     * Settings
     *
     * A list of settings that are stored in the meta table.
     *
     * @var array
     */
    protected $_settings = array();

    /**
     * Results
     *
     * The last results returned by a query.
     *
     * @var array
     */
    protected $_results = array();

    /**
     * Cache
     *
     * A Flag for using or bypassing caching.
     *
     * @var bool
     */
    protected $_cache = TRUE;

    /**
     * A Flag for testing whether or not we've completed stage 1 of our db.
     * 
     * @var boolean
     */
    protected $db_stage_1_complete = TRUE;

    //-----------------------------------------------------
    // Public Methods
    //-----------------------------------------------------

    /**
     * NF_Abstracts_Model constructor.
     *
     * @param $db
     * @param $id
     * @param $parent_id
     */
    public function __construct( $db, $id = NULL, $parent_id = '' )
    {
        /*
         * Injected the Database Dependency
         */
        $this->_db = $db;

        /*
         * Assign Database Tables using the DB prefix
         */
        $this->_table_name          = $this->_db->prefix . $this->_table_name;
        $this->_meta_table_name     = $this->_db->prefix . $this->_meta_table_name;
        $this->_relationships_table = $this->_db->prefix . $this->_relationships_table;

        /*
         * Set the object ID
         *   Check if the ID is Permanent (int) or Temporary (string)
         */
        if( is_numeric( $id ) ) {
            $this->_id = absint( $id );
        } elseif( $id ) {
            $this->_tmp_id = $id;
        }

        /*
         * Set the Parent ID for context
         */
        $this->_parent_id = $parent_id;
    }

    /**
     * Get the Permanent ID
     *
     * @return int
     */
    public function get_id()
    {
        return intval( $this->_id );
    }

    /**
     * Get the Temporary ID
     *
     * @return null|string
     */
    public function get_tmp_id()
    {
        return $this->_tmp_id;
    }

    /**
     * Get the Type
     *
     * @return string
     */
    public function get_type()
    {
        return $this->_type;
    }

    /**
     * Get a single setting with a default fallback
     *
     * @param string $setting
     * @param bool $default optional
     * @return string|int|bool
     */
    public function get_setting( $setting, $default = FALSE )
    {
        if( isset( $this->_settings[ $setting ] )){
            $return =  $this->_settings[ $setting ];
        } else {
            $return = $this->get_settings($setting);
            if( is_array( $return ) && empty( $return ) ) $return = false;
        }

        return ( $return !== false ) ? $return : $default;
    }

    /**
     * Get Settings
     *
     * @param string ...$only returns a subset of the object's settings
     * @return array
     */
    public function get_settings()
    {
        // If the ID is not set, then we cannot pull settings from the Database.
        if( ! $this->_id ) return $this->_settings;

        if( ! $this->_settings && 'field' == $this->_type ) {
            global $wpdb;
            $results = $wpdb->get_results(
                "
                SELECT Meta.key, Meta.value
                FROM $this->_table_name as Object
                JOIN $this->_meta_table_name as Meta
                ON Object.id = Meta.parent_id
                WHERE Object.id = '$this->_id'
                "
            , ARRAY_A );

            foreach( $results as $result ) {
                $key = $result[ 'key' ];
                $this->_settings[ $key ] = $result[ 'value' ];
            }

            $field = $wpdb->get_row(
                "
                SELECT `label`, `key`, `type`
                FROM $this->_table_name
                WHERE `id` = '$this->_id'
                ",
                ARRAY_A
            );

            if( ! is_wp_error( $field ) ){
                $this->_settings[ 'label' ] = $field[ 'label' ];
                $this->_settings[ 'key' ] = $field[ 'key' ];
                $this->_settings[ 'type' ] = $field[ 'type' ];
                $this->_settings[ 'field_label' ] = $field[ 'label' ];
                $this->_settings[ 'field_key' ] = $field[ 'key' ];
            }
        }

        if( ! $this->_settings ) {
            if( WPN_Helper::use_cache() ) {
                $form_cache = WPN_Helper::get_nf_cache( $this->_parent_id );
                if ($form_cache) {

                    if ('field' == $this->_type) {

                        if (isset($form_cache['fields'])) {

                            foreach ($form_cache['fields'] as $object) {
                                if ($this->_id != $object['id']) continue;

                                $this->update_settings($object['settings']);
                                break;
                            }
                        }
                    }
                }
            }
        }

        // Only query if settings haven't been already queried or cache is FALSE.
        if( ! $this->_settings || ! $this->_cache ) {

            // Build query syntax from the columns property.
            $columns = '`' . implode( '`, `', $this->_columns ) . '`';

            // Query column settings
            $results = $this->_db->get_row(
                "
                SELECT $columns
                FROM   `$this->_table_name`
                WHERE `id` = $this->_id
                "
            );

            /*
             * If the query returns results then
             *   assign settings using the column name as the setting key.
             */
            if( $results ) {
                foreach ($this->_columns as $column) {
                    $this->_settings[$column] = $results->$column;
                }
            }

            $meta_select_fields = "SELECT `key`, `value`";//, `meta_key`,
	        //`meta_value`";

            // Query settings from the meta table.
            $meta_results = $this->_db->get_results(
            	$meta_select_fields .
                "
                FROM   `$this->_meta_table_name`
                WHERE  `parent_id` = $this->_id
                "
            );

            // Assign settings to the settings property.
            foreach ($meta_results as $meta) {
                // If we don't already have a value from the main table...
                // OR If that value was NULL...
                if ( ! isset( $this->_settings[ $meta->key ] ) || NULL == $this->_settings[ $meta->key ] ) {
                    // TODO: Update this logic after removal of original meta columns.
                    // Set the value from meta.
                    $this->_settings[ $meta->key ] = $meta->value;
                }
            }
        }

        // Un-serialize queried settings results.
        foreach( $this->_settings as $key => $value ){
            $this->_settings[ $key ] = maybe_unserialize( $value );
        }

        // Check for passed arguments to limit the returned settings.
        $only = func_get_args();
        if ( $only && is_array($only)
            // And if the array is NOT multidimensional
            && (count($only) == count($only, COUNT_RECURSIVE))) {

            // If only one setting, return a single value
            if( 1 == count( $only ) ){

                if( isset( $this->_settings[ $only[0] ] ) ) {
                    return $this->_settings[$only[0]];
                } else {
                    return NULL;
                }
            }

            // Flip the array to match the settings property
            $only_settings = array_flip( $only );

            // Return only the requested settings
            return array_intersect_key( $this->_settings, $only_settings );
        }

        // Return all settings
        return $this->_settings;
    }

    /**
     * Update Setting
     *
     * @param $key
     * @param $value
     * @return bool|false|int
     */
    public function update_setting( $key, $value )
    {
        $this->_settings[ $key ] = $value;

        return $this;
    }

    /**
     * Update Settings
     *
     * @param $data
     * @return bool
     */
    public function update_settings( $data )
    {
        if( is_array( $data ) ) {
            foreach ($data as $key => $value) {
                $this->update_setting($key, $value);
            }
        }

        return $this;
    }

    /**
     * Delete
     *
     * Delete the object, its children, and its relationships.
     *
     * @return bool
     */
    public function delete()
    {
        if( ! $this->get_id() ) return;

        $results = array();

        // Delete the object from the model's table
        $results[] = $this->_db->delete(
            $this->_table_name,
            array(
                'id' => $this->_id
            )
        );

        // Delete settings from the model's meta table.
        $results[] = $this->_db->delete(
            $this->_meta_table_name,
            array(
                'parent_id' => $this->_id
            )
        );

        // Query for child objects using the relationships table.

        $children = $this->_db->get_results(
            "
            SELECT child_id, child_type
            FROM $this->_relationships_table
            WHERE parent_id = $this->_id
            AND   parent_type = '$this->_type'
            "
        );

        // Delete each child model
        foreach( $children as $child ) {
            $model = Ninja_Forms()->form()->get_model( $child->child_id, $child->child_type );
            $model->delete();
        }

        // Delete all relationships
        $this->_db->delete(
            $this->_relationships_table,
            array(
                'parent_id' => $this->_id,
                'parent_type' => $this->_type
            )
        );

        // return False if there are no query errors.
        return in_array( FALSE, $results );
    }

    /**
     * Find
     *
     * @param string $parent_id
     * @param array $where
     * @return array
     */
    public function find( $parent_id = '', array $where = array() )
    {
        // Build the query using the $where argument
        $query = $this->build_meta_query( $parent_id, $where );

        // Get object IDs from the query
        $ids = $this->_db->get_col( $query );

        // Get the current class name
        $class = get_class( $this );

        $results = array();
        foreach( $ids as $id ){

            // Instantiate a new object for each ID
            $results[] = $object = new $class( $this->_db, $id, $parent_id );
        }

        if( ! WPN_Helper::use_cache() ) {
            $results = $this->get_object_settings($results);
        }
        // Return an array of objects
        return $results;
    }

    public function get_object_settings($obj_array ) {
        global $wpdb;
        $id_array = array();
        $generic_object_array = array();
        foreach($obj_array as $obj) {
            $id_array[] = $obj->get_id();
            $generic_object_array[$obj->get_id()] = $obj;
        }
        
        if( 0 < count($id_array) ) {
            $obj_query = "SELECT `id`, `" . implode( '`, `', $this->_columns) . "` FROM {$this->_table_name} WHERE id IN (" . implode(',', $id_array) . ")";


            $table_data = $wpdb->get_results($obj_query, ARRAY_A);

            foreach($table_data as $data_item) {
                foreach($data_item as $label => $val ) {
                    $generic_object_array[$data_item['id']]->_settings[$label] = maybe_unserialize( $val );
                }
            }
        
            $meta_query = "SELECT * FROM {$this->_meta_table_name} WHERE parent_id IN (" . implode(',', $id_array) . ")";

            $meta_data = $wpdb->get_results($meta_query, ARRAY_A);

            foreach($meta_data as $meta) {

                // if( ! isset($generic_object_array[$meta['parent_id']]->_settings[$meta['meta_key']])) {
                    $generic_object_array[$meta['parent_id']]->_settings[$meta['meta_key']] = maybe_unserialize( $meta['meta_value'] );
                // }
            }
        }
        $obj_array = array_values($generic_object_array);

        return $obj_array;
    }

    /*
     * UTILITY METHODS
     */

    /**
     * Save
     */
    public function save()
    {

        /**
         * Check to see if we've completed stage 1 of our db update.
         */

        $sql = "SHOW COLUMNS FROM {$this->_db->prefix}nf3_fields LIKE 'field_key'";
        $results = $this->_db->get_results( $sql );
        /**
         * If we don't have the field_key column, we need to remove our new columns.
         *
         * Also, set our db stage 1 tracker to false.
         */
        if ( empty ( $results ) ) {
            $this->db_stage_1_complete = false;
        }

        $data = array ( 'updated_at' => current_time( 'mysql' ));

        // If the ID is not set, assign an ID
        if( ! $this->_id ){

            $data[  'created_at' ] = current_time( 'mysql' ) ;

            if( $this->_parent_id ){
                $data['parent_id'] = $this->_parent_id;
            }

            // Create a new row in the database
            $result = $this->_db->insert(
                $this->_table_name,
                $data
            );

            // Assign the New ID
            $this->_id = $this->_db->insert_id;
        } else {

            $result = $this->_db->get_row( "SELECT * FROM $this->_table_name WHERE id = $this->_id" );

            if( ! $result ){
                $this->_insert_row( array( 'id' => $this->_id ) );
            }
        }

        $this->_save_settings();

        // If a Temporary ID is set, return it along with the newly assigned ID.
        if( $this->_tmp_id ){
            return array( $this->_tmp_id => $this->_id );
        }
    }

    public function _insert_row( $data = array() )
    {
        $data[ 'created_at' ] = current_time( 'mysql' );

        if( $this->_parent_id ){
            $data['parent_id'] = $this->_parent_id;
        }

        // Create a new row in the database
        $result = $this->_db->insert(
            $this->_table_name,
            $data
        );
    }

    /**
     * Cache Flag
     *
     * @param string $cache
     * @return $this
     */
    public function cache( $cache = '' )
    {
        // Set the Cache Flag Property.
        if( $cache !== '' ) {
            $this->_cache = $cache;
        }

        // Return the current object for method chaining.
        return $this;
    }

    /**
     * Add Parent
     *
     * Set the Parent ID and Parent Type properties
     *
     * @param $parent_id
     * @param $parent_type
     * @return $this
     */
    public function add_parent( $parent_id, $parent_type )
    {
        $this->_parent_id = $parent_id;

        $this->_parent_type = $parent_type;

        // Return the current object for method chaining.
        return $this;
    }

    //-----------------------------------------------------
    // Protected Methods
    //-----------------------------------------------------

    /**
     * Save Setting
     *
     * Save a single setting.
     *
     * @param $key
     * @param $value
     * @return bool|false|int
     */
    protected function _save_setting( $key, $value )
    {
        // If the setting is a column, save the settings to the model's table.
        if( in_array( $key, $this->_columns ) ){

        	$format = null;
        	if( in_array( $key, array( 'show_title', 'clear_complete', 'hide_complete', 'logged_in' ) ) ) {
        		// gotta set the format for the columns that use bit type
        		$format = '%d';
	        }

	        if( 'form' === $this->_type && 'title' == $key ) {
		        $this->_db->update(
			        $this->_table_name,
			        array(
				        'form_title' => $value
			        ),
			        array(
				        'id' => $this->_id
			        ),
			        $format
		        );
	        }

	        // Don't update the form_title. Duplicating issue for now
	        if( 'form_title' !== $key ) {
		        $update_model = $this->_db->update(
			        $this->_table_name,
			        array(
				        $key => $value
			        ),
			        array(
				        'id' => $this->_id
			        ),
			        $format
		        );
	        } else {
        		return 1;
	        }

	        /*
	         * if it's not a form, you can return, but we are still saving some
	         * settings for forms in the form_meta table
	         */
        	if( 'form' != $this->_type
	            || ( 'form' == $this->_type && 'title' == $key ) ) {
        		return $update_model;
	        }
        }

        $meta_row = $this->_db->get_row(
            "
                SELECT `value`
                FROM   `$this->_meta_table_name`
                WHERE  `parent_id` = $this->_id
                AND    `key` = '$key'
                "
        );

        if( $meta_row ){

        	$update_values = array(
                'value' => $value,
	        );

        	// for forms we need to update the meta_key and meta_value columns
        	if( 'form' == $this->_type || $this->db_stage_1_complete ) {
        		$update_values[ 'meta_key' ] = $key;
        		$update_values[ 'meta_value' ] = $value;
	        }

            $result = $this->_db->update(
                $this->_meta_table_name,
                $update_values,
                array(
                    'key' => $key,
                    'parent_id' => $this->_id
                )
            );

        } else {

        	$insert_values = array(
		        'key' => $key,
		        'value' => $value,
		        'parent_id' => $this->_id
	        );

	        // for forms we need to update the meta_key and meta_value columns
        	if( 'form' == $this->_type || $this->db_stage_1_complete ) {
        		$insert_values[ 'meta_key' ] = $key;
        		$insert_values[ 'meta_value' ] = $value;
	        }

            $result = $this->_db->insert(
                $this->_meta_table_name,
                $insert_values,
                array(
                    '%s',
                    '%s',
                    '%d'
                )
            );
        }

        return $result;
    }

    /**
     * Save Settings
     *
     * Save all settings.
     *
     * @return bool
     */
    protected function _save_settings()
    {
        if( ! $this->_settings ) return;

        foreach( $this->_settings as $key => $value ) {
            $value = maybe_serialize( $value );
            $this->_results[] = $this->_save_setting( $key, $value );
        }

        $this->_save_parent_relationship();

        return $this->_results;
    }

    /**
     * Save Parent Relationship
     *
     * @return $this
     */
    protected function _save_parent_relationship()
    {
        // ID, Type, Parent ID, and Parent Type are required for creating a relationship.
        if( ! $this->_id || ! $this->_type || ! $this->_parent_id || ! $this->_parent_type ) return $this;

        // Check to see if a relationship exists.
        $this->_db->get_results(
            "
          SELECT *
          FROM $this->_relationships_table
          WHERE `child_id` = $this->_id
          AND   `child_type` = '$this->_type'
          "
        );

        // If a relationship does not exists, then create one.
        if( 0 == $this->_db->num_rows ) {

            $this->_db->insert(
                $this->_relationships_table,
                array(
                    'child_id' => $this->_id,
                    'child_type' => $this->_type,
                    'parent_id' => $this->_parent_id,
                    'parent_type' => $this->_parent_type
                ),
                array(
                    '%d',
                    '%s',
                    '%d',
                    '%s',
                )
            );
        }

        // Return the current object for method chaining.
        return $this;
    }

    /**
     * Build Meta Query
     *
     * @param string $parent_id
     * @param array $where
     * @return string
     */
    protected function build_meta_query( $parent_id = '', array $where = array() )
    {
        $join_statement = array();
        $where_statement = array();

        if( $where AND is_array( $where ) ) {

            $where_conditions = array();
            foreach ($where as $key => $value) {
                $conditions['key'] = $key;
                $conditions['value'] = $value;

                $where_conditions[] = $conditions;
            }

            $count = count($where);
            for ($i = 0; $i < $count; $i++) {

                $join_statement[] = "INNER JOIN " . $this->_meta_table_name . " as meta$i on meta$i.parent_id = " . $this->_table_name . ".id";
                $where_statement[] = "( meta$i.key = '" . $where_conditions[$i]['key'] . "' AND meta$i.value = '" . $where_conditions[$i]['value'] . "' )";
            }

        }

        $join_statement = implode( ' ', $join_statement );

        $where_statement = implode( ' AND ', $where_statement );

        // TODO: Breaks SQL. Needs more testing.
        // if( $where_statement ) $where_statement = "AND " . $where_statement;

        if( $parent_id ){
            $where_statement = "$this->_table_name.parent_id = $parent_id $where_statement";
        }

        if( ! empty( $where_statement ) ) {
            $where_statement = "WHERE $where_statement";
        }

        return "SELECT DISTINCT $this->_table_name.id FROM $this->_table_name $join_statement $where_statement";
    }


}
