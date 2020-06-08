<?php

class WCCPS_CMB2{

	private $version;
    public function __construct( $version ){
		$this->version = $version;
        add_action( 'cmb2_admin_init', array( $this, 'fn_settings'));
    }

    public function fn_settings(){
        $wccps_cmb_options =  new_cmb2_box(array(
            'id'       => 'wccps_cmb2',
			'title'    => 'WC Clear Price Stock <small>' . $this->version . '</small>',
			'menu_title' => 'WC Clear Price Stock',
			'position' => 1,
            'icon_url' => 'dashicons-store',
            'show_on'  => array(
                'options-page' => 'wccps_options'
            )
        ));

		$wccps_cmb_options->add_field( array(
			'name'     => 'Debug',
			'type'     => 'title',
			'id'       => 'debug_title_wccps'
		));
		$wccps_cmb_options->add_field( array(
			'name'     => 'Save LOG',
			'desc'     => 'Log will be saved with result of executed functions.',
			'id'       => 'debug_text_wccps',
			'type'     => 'select',
			'default'  => 0,
			'options'  => array(
				0 => 'No',
				1 => 'Yes'
			)
        ));
    }
    
}

?>