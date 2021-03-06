<?php
/**
 * Options available for Navigation sub menu of Theme Options
 * 
 */

$navigation_options 	= apply_filters( 'electro_navigation_options_args', array(
	'title'		=> esc_html__( 'Navigation', 'electro' ),
	'icon'		=> 'fas fa-bars',
	'fields'	=> array(
		array(
			'id'        => 'primary-navigation-start',
			'type'      => 'section',
			'title'     => esc_html__( 'Primary Navigation', 'electro' ),
			'subtitle'  => esc_html__( 'Options for primary navigation of the theme', 'electro' ),
			'indent'    => false,
		),

		array(
			'title'     => esc_html__( 'Dropdown Trigger', 'electro' ),
			'id'        => 'primary-nav_dropdown_trigger',
			'type'      => 'select',
			'options'   => array(
			    'click'     => esc_html__( 'Click', 'electro' ),
			    'hover'     => esc_html__( 'Hover', 'electro' ),
			),
			'default'   => 'click',
		),

		array(
			'id'        => 'primary-navigation-end',
			'type'      => 'section',
			'indent'    => false,
		),

		array(
			'id'        => 'secondary-navigation-start',
			'type'      => 'section',
			'title'     => esc_html__( 'Secondary Navigation', 'electro' ),
			'subtitle'  => esc_html__( 'Options for secondary navigation of the theme', 'electro' ),
			'indent'    => false,
		),

		array(
			'title'     => esc_html__( 'Dropdown Trigger', 'electro' ),
			'id'        => 'secondary-nav_dropdown_trigger',
			'type'      => 'select',
			'options'   => array(
			    'click'     => esc_html__( 'Click', 'electro' ),
			    'hover'     => esc_html__( 'Hover', 'electro' ),
			),
			'default'   => 'click',
		),

		array(
			'id'        => 'secondary-navigation-end',
			'type'      => 'section',
			'indent'    => false,
		),

		array(
			'id'        => 'navbar-primary-navigation-start',
			'type'      => 'section',
			'title'     => esc_html__( 'Navbar Primary Navigation', 'electro' ),
			'subtitle'  => esc_html__( 'Options for navbar primary navigation of the theme', 'electro' ),
			'indent'    => false,
		),

		array(
			'title'     => esc_html__( 'Dropdown Trigger', 'electro' ),
			'id'        => 'navbar-primary_dropdown_trigger',
			'type'      => 'select',
			'options'   => array(
			    'click'     => esc_html__( 'Click', 'electro' ),
			    'hover'     => esc_html__( 'Hover', 'electro' ),
			),
			'default'   => 'click',
		),

		array(
			'id'        => 'navbar-primary-navigation-end',
			'type'      => 'section',
			'indent'    => false,
		),

		array(
			'id'        => 'topbar-left-navigation-start',
			'type'      => 'section',
			'title'     => esc_html__( 'Topbar Left Navigation', 'electro' ),
			'subtitle'  => esc_html__( 'Options for topbar left navigation of the theme', 'electro' ),
			'indent'    => false,
		),

		array(
			'title'     => esc_html__( 'Dropdown Trigger', 'electro' ),
			'id'        => 'topbar-left_dropdown_trigger',
			'type'      => 'select',
			'options'   => array(
			    'click'     => esc_html__( 'Click', 'electro' ),
			    'hover'     => esc_html__( 'Hover', 'electro' ),
			),
			'default'   => 'click',
		),

		array(
			'id'        => 'topbar-left-navigation-end',
			'type'      => 'section',
			'indent'    => false,
		),

		array(
			'id'        => 'topbar-right-navigation-start',
			'type'      => 'section',
			'title'     => esc_html__( 'Topbar Right Navigation', 'electro' ),
			'subtitle'  => esc_html__( 'Options for topbar right navigation of the theme', 'electro' ),
			'indent'    => false,
		),

		array(
			'title'     => esc_html__( 'Dropdown Trigger', 'electro' ),
			'id'        => 'topbar-right_dropdown_trigger',
			'type'      => 'select',
			'options'   => array(
			    'click'     => esc_html__( 'Click', 'electro' ),
			    'hover'     => esc_html__( 'Hover', 'electro' ),
			),
			'default'   => 'click',
		),

		array(
			'id'        => 'topbar-right-navigation-end',
			'type'      => 'section',
			'indent'    => false,
		),

		array(
			'id'        => 'header-v9-navbar-navigation-start',
			'type'      => 'section',
			'title'     => esc_html__( 'Header v9 Navbar', 'electro' ),
			'subtitle'  => esc_html__( 'Options for Header v9 Navbar navigation of the theme', 'electro' ),
			'indent'    => false,
		),

		array(
			'title'     => esc_html__( 'Dropdown Trigger', 'electro' ),
			'id'        => 'header-v9-navbar_dropdown_trigger',
			'type'      => 'select',
			'options'   => array(
			    'click'     => esc_html__( 'Click', 'electro' ),
			    'hover'     => esc_html__( 'Hover', 'electro' ),
			),
			'default'   => 'click',
		),

		array(
			'id'        => 'header-v9-navbar-navigation-end',
			'type'      => 'section',
			'indent'    => false,
		),
	)
) );