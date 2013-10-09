<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Freeform - Freeform Custom Table Fieldtype
 *
 * ExpressionEngine fieldtype interface licensed for use by EllisLab, Inc.
 *
 * @package		Solspace:Freeform
 * @author		Solspace, Inc.
 * @copyright	Copyright (c) 2008-2013, Solspace, Inc.
 * @link		http://solspace.com/docs/freeform
 * @license		http://www.solspace.com/license_agreement
 * @filesource	third_party/custom_table/freeform_ft.custom_table.php
 */

class Freeform_grid_freeform_ft extends Freeform_base_ft {

    public $info = array(
        'name'          => 'Grid',
        'version'       => '1.0',
        'description'   => 'A basic grid field to hold tabular data'
    );

    public $default_min_rows 		= '2';
    public $default_max_rows 		= '5';
    public $default_column_headers 	= '[{"heading":"Column 1"}]';

    // --------------------------------------------------------------------

    /**
     * Constructor
     *
     * @access	public
     * @return	null
     */

    public function __construct()
    {
    	parent::__construct();

    	ee()->load->add_package_path(PATH_THIRD.'freeform_grid/');

    	$this->info['name'] 		= lang('freeform_grid_name');
    	$this->info['description'] 	= lang('freeform_grid_desc');
    }
    //END __construct

    // --------------------------------------------------------------------

    /**
     * Replace Tag
     *
     * @access	public
     * @param	string 	data
     * @param 	array 	params from tag
     * @param 	string 	tagdata if there is a tag pair
     * @return	string
     */

    public function replace_tag($data, $params = array(), $tagdata = FALSE)
    {
    	$this->_get_settings();

    	$tagdata = (!$tagdata) ? ee()->load->view('default_tag.html', array(), TRUE) : $tagdata;
    	$vars	 = array();

    	// Format our headings for TMPL
		foreach ($this->headers as $key => $header)
		{
			$this->headers[$key] = get_object_vars($header);
		}

		$vars['headings'] = $this->headers;

		// Format our rows/cells for TMPL
		$raw 	= json_decode($data);
		$rows 	= array();

		foreach ($raw as $key => $row)
		{
			$cells = array();
			foreach ($row as $k => $cell)
			{
				$cells[$k] = array('cell' => $cell);
			}
			$rows[$key]['cells'] = $cells;
		}

		$vars['rows'] = $rows;

		// Send all of our variables to TMPL for parsing
		$variables[] = $vars;

    	return ee()->TMPL->parse_variables($tagdata, $variables);
    }
    //END replace_tag

    // --------------------------------------------------------------------

    /**
     * Display Entry in the CP
     *
     * @access	public
     * @param 	string 	data from table for email output
     * @return	string 	output data
     */

    public function display_entry_cp($data)
    {
    	$this->_get_settings();

    	if (is_null($data) || $data == '') return $data;

		$vars = array(
			'row_data' 	=> json_decode($data),
			'headers' 	=> $this->headers
		);

    	return ee()->load->view('cp_display.html', $vars, TRUE);
    }
    // END display_entry_cp

    // --------------------------------------------------------------------

    /**
	 * Display Field
	 *
	 * @access	public
	 * @param	string 	saved data input
	 * @param  	array 	input params from tag
	 * @param 	array 	attribute params from tag
	 * @return	string 	display output
	 */

    public function display_field($data = '', $params = array(), $attr = array())
    {
    	$this->_get_settings();

		$output = '';
		$output .= ee()->load->view('base.js', array(), TRUE);

		// Build our default row data in case the data passed to us is empty
		$default_rows = array();
		for ($i=0; $i < $this->minrows; $i++)
		{
			array_push($default_rows, '[]');
		}

		$vars = array(
        	'id'		=> $this->field_id,
        	'name'		=> $this->field_name,
        	'minrows'	=> $this->minrows,
        	'maxrows'	=> $this->maxrows,
        	'headers'	=> json_encode($this->headers),
        	'rows'		=> ($data == '') ? '['.implode(',', $default_rows).']' : $data,
			'delete_button_src' => URL_THIRD_THEMES.'freeform/images/custom_images/delete.png'
        );

		//var_dump($data,$params,$attr);
		$output .= ee()->load->view('field_display.html', $vars, TRUE);
		return $output;
    }
    // END display_field

    // --------------------------------------------------------------------

    /**
     * Display Field Settings
     *
     * @access	public
     * @param	array
     * @return	string
     */

    public function display_settings($data)
    {
		$basejs = ee()->load->view('base.js', array(), TRUE);

		$data['add_button_src'] 	= URL_THIRD_THEMES.'freeform/images/custom_images/add.png';
		$data['delete_button_src'] 	= URL_THIRD_THEMES.'freeform/images/custom_images/delete.png';

		if (!isset($data['column_headers']))
		{
			$data['column_headers'] = $this->default_column_headers;
		}

		ee()->table->add_row(
			lang('settings_define_cols') .
			    '<div class="subtext">' .
			        lang('settings_define_cols_desc') .
			    '</div>',
			$basejs.ee()->load->view('field_settings.html', $data, TRUE)
		);

        // Add minimum rows settings
        ee()->table->add_row(
        	lang('settings_min_rows'),
        	form_input(array(
        		'name'		=> 'min_rows',
        		'id'		=> 'min_rows',
        		'value'		=> (isset($data['min_rows'])) ? $data['min_rows'] : $this->default_min_rows
        	))
        );

        // Add maximum rows settings
        ee()->table->add_row(
        	lang('settings_max_rows'),
        	form_input(array(
        		'name'		=> 'max_rows',
        		'id'		=> 'max_rows',
        		'value'		=> (isset($data['max_rows'])) ? $data['max_rows'] : $this->default_max_rows
        	))
        );

        // Should we validate all cells?
        ee()->table->add_row(
        	lang('settings_validate_cells') .
	        	'<div class="subtext">' .
	        	    lang('settings_validate_cells_desc') .
	        	'</div>',
        	form_checkbox(array(
        		'name'		=> 'validate_cells',
        		'id'		=> 'validate_cells',
        		'value'		=> 'y',
        		'checked'	=> (isset($data['validate_cells'])) ? ($data['validate_cells'] === 'y') : FALSE
        	))
        );
    }
    //END display_settings

    // --------------------------------------------------------------------

    public function save_settings()
    {
        $column_headers = ee()->input->post('column_headers');
        $min_rows = ee()->input->post('min_rows');
        $max_rows = ee()->input->post('max_rows');
        $validate_cells = ee()->input->post('validate_cells');

        return array(
            'column_headers' 	=> !empty($column_headers) ? $column_headers : $this->default_column_headers,
            'min_rows'			=> is_numeric($min_rows) ? $min_rows : $this->default_min_rows,
            'max_rows'			=> is_numeric($max_rows) ? $max_rows : $this->default_max_rows,
            'validate_cells'	=> $validate_cells === 'y' ? $validate_cells : ''
        );
    }
    //END save_settings

    // --------------------------------------------------------------------

    /**
     * validate
     *
     * @access	public
     * @param	string $data 	data to validate
     * @return	bool  			validated?
     */

    public function validate($data)
    {
    	$this->_get_settings();

    	if ($this->validate_cells !== 'y') return TRUE;

    	$data = json_decode($data);

    	// Do we have an array?
    	if (!is_array($data))
    	{
    		return FALSE;
    	}

    	// First let's check that we have the minimum number of rows of data
    	if (count($data) < $this->minrows)
    	{
    		$this->errors[] = str_replace(
				'%num%',
				$this->minrows,
				lang('error_less_than_minrows')
			);
    		return $this->errors;
    	}

    	// Now check that we have actual data in the cells
    	foreach ($data as $i => $row)
    	{
    		foreach ($row as $j => $cell)
    		{
    			if ($cell == '')
    			{
		    		/*$error = str_replace(
						'%row%',
						$i+1,
						lang('error_empty_cells')
					);

					$error = str_replace(
						'%cell%',
						$j+1,
						$error
					);

					$this->errors[] = $error;*/

					$this->errors[] = lang('error_empty_cells');

		    		return $this->errors;
    			}
    		}
    	}

    	return TRUE;
    }
    // END validate

    // --------------------------------------------------------------------

    /**
     * Gets the params of the field
     *
     * @return bool
     */

    protected function _get_settings()
    {
    	$this->minrows = isset($this->settings['min_rows']) ?
							$this->settings['min_rows'] :
							$this->default_min_rows;

		$this->maxrows = isset($this->settings['max_rows']) ?
							$this->settings['max_rows'] :
							$this->default_max_rows;

    	$this->headers = isset($this->settings['column_headers']) ?
							json_decode($this->settings['column_headers']) :
							$this->default_column_headers;

    	$this->validate_cells = isset($this->settings['validate_cells']) ?
								$this->settings['validate_cells'] :
								FALSE;

		return TRUE;
    }
    // END _get_settings
}
// END Freeform_grid_freeform_ft class

/* End of file ft.freeform_grid.php */
/* Location: ./system/expressionengine/third_party/freeform_grid/freeform_ft.freeform_grid.php */
