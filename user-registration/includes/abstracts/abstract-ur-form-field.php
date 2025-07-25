<?php
/**
 * Abstract UR_Form_Field Class
 *
 * Implemented by classes using the same CRUD(s) pattern.
 *
 * @version  2.6.0
 * @package  UserRegistration/Abstracts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * UR_Form_Field Class
 */
abstract class UR_Form_Field {

	/**
	 * ID for this object.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	public $id = 0;
	/**
	 * Default fields array.
	 *
	 * @var array
	 */
	public $field_defaults = array();
	/**
	 * Admin Data Object.
	 *
	 * @var object
	 */
	public $admin_data = array();
	/**
	 * Registered fields configuration.
	 *
	 * @var array
	 */
	public $registered_fields_config = array();

	/**
	 * Form ID for this object.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	protected $form_id = 0;

	/**
	 * Abstract function to get regestered admin fields.
	 */
	abstract public function get_registered_admin_fields();

	/**
	 * Get General Setting fields
	 *
	 * @param string $key Atrribute of fields.
	 */
	public function get_general_setting_data( $key ) {

		if ( isset( $this->admin_data->general_setting->$key ) ) {
			return $this->admin_data->general_setting->$key;
		}

		if ( isset( $this->field_defaults[ 'default_' . $key ] ) ) {
			return $this->field_defaults[ 'default_' . $key ];
		}

		return '';
	}

	/**
	 * Get advance setting values.
	 *
	 * @param string $key Atrribute of fields.
	 */
	public function get_advance_setting_data( $key ) {

		if ( isset( $this->admin_data->advance_setting->$key ) ) {
			return $this->admin_data->advance_setting->$key;
		}

		if ( isset( $this->field_defaults[ 'default_' . $key ] ) ) {
			return $this->field_defaults[ 'default_' . $key ];
		}

		return '';
	}

	/**
	 * Include admin template for each form fields
	 *
	 * @param  array $admin_data Admin Data.
	 */
	public function get_admin_template( $admin_data = array() ) {

		$this->admin_data = $admin_data;

		ob_start();
		$template_path = str_replace( '_', '-', str_replace( 'user_registration_', 'admin-', $this->id ) );
		/**
		 * Filter the admin template path for a specific form element.
		 *
		 * The dynamic portion of the hook name, $this->id
		 *
		 * @param string $admin_template_path The default admin template path for the form element.
		 */
		$admin_template_path = apply_filters( $this->id . '_admin_template', UR_FORM_PATH . 'views' . UR_DS . 'admin' . UR_DS . $template_path . '.php' );

		if ( file_exists( $admin_template_path ) ) {
			include $admin_template_path;
		}
		$template = ob_get_clean();

		$settings = $this->get_setting();

		$this->admin_data = array();

		return array(
			'template' => $template . $settings,
			'settings' => $settings,
		);
	}


	/**
	 * Includes any classes we need within frontend.
	 *
	 * @param integer $form_id Form ID.
	 * @param string  $field_type Field Type.
	 * @param string  $field_key Field Key.
	 * @param array   $data Form data.
	 */
	public function frontend_includes( $form_id, $field_type, $field_key, $data = array() ) {
		$this->form_id          = $form_id;
		$form_data              = (array) $data['general_setting'];
		$form_data['form_id']   = $form_id;
		$form_data['type']      = $field_type;
		$form_data['field_key'] = $field_key;
		$form_data['icon']      = $data['icon'];

		if ( isset( $form_data['hide_label'] ) && ur_string_to_bool( $form_data['hide_label'] ) ) {
			unset( $form_data['label'] );
		}

		if ( isset( $data['general_setting']->required ) ) {

			if ( in_array( $field_key, ur_get_required_fields() )
			|| ur_string_to_bool( $data['general_setting']->required ) ) {

				$form_data['required']                      = true;
				$form_data['custom_attributes']['required'] = 'required';
			}
		}

		if ( isset( $data['advance_setting']->size ) ) {
			$form_data['size'] = $data['advance_setting']->size;
		}

		if ( isset( $data['advance_setting']->limit_length ) && $data['advance_setting']->limit_length ) {
			if ( isset( $data['advance_setting']->limit_length_limit_count ) && isset( $data['advance_setting']->limit_length_limit_mode ) ) {
				if ( 'characters' === $data['advance_setting']->limit_length_limit_mode ) {
					$form_data['max-characters'] = $data['advance_setting']->limit_length_limit_count;
				} elseif ( 'words' === $data['advance_setting']->limit_length_limit_mode ) {
					$form_data['max-words'] = $data['advance_setting']->limit_length_limit_count;
				}
			}
		}

		if ( isset( $data['advance_setting']->minimum_length ) && $data['advance_setting']->minimum_length ) {
			if ( isset( $data['advance_setting']->minimum_length_limit_count ) && isset( $data['advance_setting']->minimum_length_limit_mode ) ) {
				if ( 'characters' === $data['advance_setting']->minimum_length_limit_mode ) {
					$form_data['min-characters'] = $data['advance_setting']->minimum_length_limit_count;
				} elseif ( 'words' === $data['advance_setting']->minimum_length_limit_mode ) {
					$form_data['min-words'] = $data['advance_setting']->minimum_length_limit_count;
				}
			}
		}

		if ( isset( $data['advance_setting']->min ) ) {
			$form_data['min'] = $data['advance_setting']->min;
		}

		if ( isset( $data['advance_setting']->max ) ) {
			$form_data['max'] = $data['advance_setting']->max;
		}

		if ( isset( $data['advance_setting']->step ) ) {
			$form_data['step'] = $data['advance_setting']->step;
		}

		if ( isset( $data['advance_setting']->default_value ) ) {
			$form_data['default'] = $data['advance_setting']->default_value;
		}

		if ( isset( $data['general_setting']->max_files ) ) {
			$form_data['max_files'] = $data['general_setting']->max_files;
		}

		if ( isset( $data['advance_setting']->max_upload_size ) ) {
			$form_data['max_upload_size'] = $data['advance_setting']->max_upload_size;
		}

		if ( isset( $data['advance_setting']->valid_file_type ) ) {
			$form_data['valid_file_type'] = $data['advance_setting']->valid_file_type;
		}

		if ( isset( $data['advance_setting']->enable_crop_picture ) ) {
			$form_data['enable_crop_picture'] = $data['advance_setting']->enable_crop_picture;
		}

		if ( isset( $data['advance_setting']->enable_take_picture ) ) {
			$form_data['enable_take_picture'] = $data['advance_setting']->enable_take_picture;
		}

		$form_data['input_class'] = array( 'ur-frontend-field ' );

		if ( isset( $data['advance_setting']->custom_class ) ) {
			array_push( $form_data['input_class'], $data['advance_setting']->custom_class );
		}

		if ( isset( $data['advance_setting']->date_format ) ) {
			update_option( 'user_registration_' . $data['general_setting']->field_name . '_date_format', $data['advance_setting']->date_format );
			$form_data['custom_attributes']['data-date-format'] = $data['advance_setting']->date_format;
		}

		if ( isset( $data['advance_setting']->enable_min_max ) && ur_string_to_bool( $data['advance_setting']->enable_min_max ) ) {
			if ( isset( $data['advance_setting']->min_date ) ) {
				$min_date                                        = str_replace( '/', '-', $data['advance_setting']->min_date );
				$form_data['custom_attributes']['data-min-date'] = '' !== $min_date ? date_i18n( $data['advance_setting']->date_format, strtotime( $min_date ) ) : '';
			}

			if ( isset( $data['advance_setting']->max_date ) ) {
				$max_date                                        = str_replace( '/', '-', $data['advance_setting']->max_date );
				$form_data['custom_attributes']['data-max-date'] = '' !== $max_date ? date_i18n( $data['advance_setting']->date_format, strtotime( $max_date ) ) : '';
			}
		}

		if ( isset( $data['advance_setting']->set_current_date ) ) {
			$form_data['custom_attributes']['data-default-date'] = ur_string_to_bool( $data['advance_setting']->set_current_date );
		}

		if ( isset( $data['advance_setting']->enable_date_range ) ) {
			$form_data['custom_attributes']['data-mode'] = ur_string_to_bool( $data['advance_setting']->enable_date_range );
		}

		if ( isset( $data['advance_setting']->date_localization ) ) {
			$date_localization = apply_filters( 'user_registration_date_localization', $data['advance_setting']->date_localization );
			if ( wp_script_is( 'flatpickr' ) && 'en' !== $date_localization ) {
				wp_enqueue_script( 'flatpickr-localization_' . $date_localization, UR()->plugin_url() . '/assets/js/flatpickr/dist/I10n/' . $date_localization . '.js', array(), UR_VERSION, true );
			}
			$form_data['custom_attributes']['data-locale'] = $date_localization;
		}

		$form_data['custom_attributes']['data-label'] = ur_string_translation( $form_id, 'user_registration_' . $data['general_setting']->field_name . '_label', $data['general_setting']->label );

		if ( isset( $form_data['label'] ) ) {
			$form_data['label'] = ur_string_translation( $form_id, 'user_registration_' . $data['general_setting']->field_name . '_label', $form_data['label'] );
		}
		if ( isset( $form_data['placeholder'] ) ) {
			$form_data['placeholder'] = ur_string_translation( $form_id, 'user_registration_' . $data['general_setting']->field_name . '_placeholder', $form_data['placeholder'] );
		}
		if ( isset( $form_data['description'] ) ) {
			$form_data['description'] = ur_string_translation( $form_id, 'user_registration_' . $data['general_setting']->field_name . '_description', $form_data['description'] );
		}

		// Filter only selected countries for `Country` fields.
		if ( 'country' === $field_key || 'billing_country' === $field_key || 'shipping_country' === $field_key ) {
			$form_data['options'] = UR_Form_Field_Country::get_instance()->get_country();
			$filtered_options     = array();
			$selected_countries   = $data['advance_setting']->selected_countries;

			if ( is_array( $selected_countries ) ) {
				foreach ( $form_data['options'] as $iso => $country_name ) {
					if ( in_array( $iso, $selected_countries, true ) ) {
						$filtered_options[ $iso ] = $country_name;
					}
				}

				$form_data['options'] = $filtered_options;
			}
		}

		/**  Redundant codes. */
		if ( 'select' === $field_key || 'select2' === $field_key || 'multi_select2' === $field_key ) {
			$option_data = isset( $data['advance_setting']->options ) ? explode( ',', $data['advance_setting']->options ) : array(); // Backward compatibility. Modified since 1.5.7.
			$option_data = isset( $data['general_setting']->options ) ? $data['general_setting']->options : $option_data;
			$options     = array();

			if ( is_array( $option_data ) ) {
				foreach ( $option_data as $index_data => $option ) {
					$options[ $option ] = ur_string_translation( $form_id, 'user_registration_' . $data['general_setting']->field_name . '_option_' . ( ++$index_data ), $option );
				}

				$form_data['options'] = $options;
			}

			if ( 'multi_select2' === $field_key ) {
				$form_data['choice_limit'] = isset( $data['advance_setting']->choice_limit ) ? $data['advance_setting']->choice_limit : '';
				$form_data['select_all']   = isset( $data['advance_setting']->select_all ) ? ur_string_to_bool( $data['advance_setting']->select_all ) : false;
			}
		}

		if ( 'html' === $field_key ) {
			$form_data['html'] = isset( $data['general_setting']->html ) ? ur_string_translation( $form_id, 'user_registration_' . $data['general_setting']->field_name, $data['general_setting']->html ) : '';
		}

		if ( 'radio' === $field_key ) {

			if ( isset( $data['general_setting']->image_choice ) && ur_string_to_bool( $data['general_setting']->image_choice ) ) {
				$option_data = isset( $data['general_setting']->image_options ) ? $data['general_setting']->image_options : array();

				$options = array();
				if ( is_array( $option_data ) ) {
					foreach ( $option_data as $index_data => $option ) {
						$options[ $option->label ] = array(
							'label' => ur_string_translation( $form_id, 'user_registration_' . $data['general_setting']->field_name . '_option_' . ( ++$index_data ), $option->label ),
							'image' => $option->image,
						);
					}

					$form_data['image_options'] = $options;
				}
			} else {
				$option_data = isset( $data['general_setting']->options ) ? $data['general_setting']->options : array();

				$options = array();
				if ( is_array( $option_data ) ) {
					foreach ( $option_data as $index_data => $option ) {
						$options[ $option ] = ur_string_translation( $form_id, 'user_registration_' . $data['general_setting']->field_name . '_option_' . ( ++$index_data ), $option );
					}

					$form_data['options'] = $options;
				}
			}
		}

		if ( 'checkbox' === $field_key ) {
			$form_data['select_all'] = isset( $data['advance_setting']->select_all ) ? ur_string_to_bool( $data['advance_setting']->select_all ) : false;
			if ( isset( $data['general_setting']->image_choice ) && ur_string_to_bool( $data['general_setting']->image_choice ) ) {
				$option_data = isset( $data['general_setting']->image_options ) ? $data['general_setting']->image_options : array();
				$options     = array();

				if ( is_array( $option_data ) ) {
					foreach ( $option_data as $index_data => $option ) {
						$options[ $option->label ] = array(
							'label' => ur_string_translation( $form_id, 'user_registration_' . $data['general_setting']->field_name . '_option_' . ( ++$index_data ), $option->label ),
							'image' => $option->image,
						);
					}

					$form_data['image_options'] = $options;
				}
			} else {
				$option_data = isset( $data['general_setting']->options ) ? $data['general_setting']->options : array();
				$options     = array();

				if ( is_array( $option_data ) ) {
					foreach ( $option_data as $index_data => $option ) {
						$options[ $option ] = ur_string_translation( $form_id, 'user_registration_' . $data['general_setting']->field_name . '_option_' . ( ++$index_data ), $option );
					}

					$form_data['options'] = $options;
				}
			}

			$form_data['choice_limit'] = isset( $data['advance_setting']->choice_limit ) ? $data['advance_setting']->choice_limit : '';
		}
		if ( 'single_item' === $field_key ) {
			$form_data['enable_selling_price_single_item'] = isset( $data['advance_setting']->enable_selling_price_single_item ) ? ur_string_to_bool( $data['advance_setting']->enable_selling_price_single_item ) : '';
			$form_data['selling_price']                    = isset( $data['advance_setting']->selling_price ) ? $data['advance_setting']->selling_price : '';
		}

		if ( 'subscription_plan' === $field_key ) {
			$form_data['select_all'] = isset( $data['advance_setting']->select_all ) ? ur_string_to_bool( $data['advance_setting']->select_all ) : false;
			$choices                 = isset( $data['advance_setting']->choices ) ? explode( ',', $data['advance_setting']->choices ) : array(); // Backward compatibility. Modified since 1.5.7.
			$option_data             = isset( $data['general_setting']->options ) ? $data['general_setting']->options : $choices;
			$options                 = array();

			if ( is_array( $option_data ) ) {
				foreach ( $option_data as $index_data => $option ) {
					if ( isset( $option->label ) ) {
						$options[ $option->label ] = array(
							'label'      => $option->label,
							'value'      => $option->value,
							'sell_value' => $option->sell_value,
						);
					}
				}

				$form_data['options'] = $options;
			}
		}
		if ( 'multiple_choice' === $field_key ) {
			$form_data['select_all'] = isset( $data['advance_setting']->select_all ) ? ur_string_to_bool( $data['advance_setting']->select_all ) : false;
			$option_data             = isset( $data['general_setting']->options ) ? $data['general_setting']->options : array();
			$options                 = array();

			if ( is_array( $option_data ) ) {
				foreach ( $option_data as $index_data => $option ) {
					if ( isset( $data['general_setting']->image_choice ) && ur_string_to_bool( $data['general_setting']->image_choice ) ) {
						$options[ $option->label ] = array(
							'label'      => $option->label,
							'value'      => $option->value,
							'sell_value' => $option->sell_value,
							'image'      => $option->image,
						);
					} else {
						$options[ $option->label ] = array(
							'label'      => $option->label,
							'value'      => $option->value,
							'sell_value' => $option->sell_value,
						);
					}
				}

				$form_data['options'] = $options;
			}

			$form_data['choice_limit'] = isset( $data['advance_setting']->choice_limit ) ? $data['advance_setting']->choice_limit : '';
		}

		if ( 'captcha' === $field_key ) {
			$choices     = isset( $data['advance_setting']->choices ) ? explode( ',', $data['advance_setting']->choices ) : array(); // Backward compatibility. Modified since 1.5.7.
			$option_data = isset( $data['general_setting']->options ) ? $data['general_setting']->options : $choices;
			$options     = array();

			if ( is_array( $option_data ) ) {
				foreach ( $option_data as $index_data => $option ) {
					$options[ $option->question ] = array(
						'question' => ur_string_translation( $form_id, 'user_registration_' . $data['general_setting']->field_name . '_option_' . ( ++$index_data ), $option->question ),
						'answer'   => $option->answer,
					);
				}

				$form_data['options'] = $options;
			}
		}

		if ( 'user_login' === $field_key ) {
			$form_data['username_length'] = isset( $data['advance_setting']->username_length ) ? $data['advance_setting']->username_length : '';

			$form_data['username_character'] = isset( $data['advance_setting']->username_character ) ? $data['advance_setting']->username_character : '';
		}

		if ( 'range' === $field_key ) {
			$form_data['range_min']             = ( isset( $data['advance_setting']->range_min ) && '' !== $data['advance_setting']->range_min ) ? $data['advance_setting']->range_min : '0';
			$form_data['range_max']             = ( isset( $data['advance_setting']->range_max ) && '' !== $data['advance_setting']->range_max ) ? $data['advance_setting']->range_max : '10';
			$form_data['range_step']            = isset( $data['advance_setting']->range_step ) ? $data['advance_setting']->range_step : '';
			$enable_prefix_postfix              = isset( $data['advance_setting']->enable_prefix_postfix ) ? ur_string_to_bool( $data['advance_setting']->enable_prefix_postfix ) : false;
			$enable_text_prefix_postfix         = isset( $data['advance_setting']->enable_text_prefix_postfix ) ? ur_string_to_bool( $data['advance_setting']->enable_text_prefix_postfix ) : false;
			$form_data['enable_payment_slider'] = isset( $data['advance_setting']->enable_payment_slider ) ? ur_string_to_bool( $data['advance_setting']->enable_payment_slider ) : false;

			if ( $enable_prefix_postfix ) {

				if ( $enable_text_prefix_postfix ) {
					$form_data['range_prefix']  = isset( $data['advance_setting']->range_prefix ) ? $data['advance_setting']->range_prefix : '';
					$form_data['range_postfix'] = isset( $data['advance_setting']->range_postfix ) ? $data['advance_setting']->range_postfix : '';
				} else {

					$form_data['range_prefix']  = $form_data['range_min'];
					$form_data['range_postfix'] = $form_data['range_max'];
				}
			}
		}

		if ( 'timepicker' == $field_key ) {
			$form_data['current_time']             = isset( $data['advance_setting']->current_time ) ? $data['advance_setting']->current_time : '';
			$form_data['time_interval']            = isset( $data['advance_setting']->time_interval ) ? $data['advance_setting']->time_interval : '';
			$form_data['enable_time_slot_booking'] = isset( $data['advance_setting']->enable_time_slot_booking ) ? $data['advance_setting']->enable_time_slot_booking : '';
			$form_data['time_format']              = isset( $data['advance_setting']->time_format ) ? $data['advance_setting']->time_format : '';
			$form_data['time_range']               = isset( $data['advance_setting']->time_range ) ? $data['advance_setting']->time_range : '';
			$form_data['target_date_field']        = isset( $data['advance_setting']->target_date_field ) ? $data['advance_setting']->target_date_field : '';
			$form_data['time_min']                 = ( isset( $data['advance_setting']->time_min ) && '' !== $data['advance_setting']->time_min ) ? $data['advance_setting']->time_min : '';
			$form_data['time_max']                 = ( isset( $data['advance_setting']->time_max ) && '' !== $data['advance_setting']->time_max ) ? $data['advance_setting']->time_max : '';
			$timemin                               = isset( $form_data['time_min'] ) ? strtolower( substr( $form_data['time_min'], -2 ) ) : '';
			$timemax                               = isset( $form_data['time_max'] ) ? strtolower( substr( $form_data['time_max'], -2 ) ) : '';
			$minampm                               = intval( $form_data['time_min'] ) <= 12 ? 'AM' : 'PM';
			$maxampm                               = intval( $form_data['time_max'] ) <= 12 ? 'AM' : 'PM';

			// Handles the time format.
			if ( 'am' === $timemin || 'pm' === $timemin ) {
				$form_data['time_min'] = $form_data['time_min'];
			} else {
				$form_data['time_min'] = $form_data['time_min'] . '' . $minampm;
			}

			if ( 'am' === $timemax || 'pm' === $timemax ) {
				$form_data['time_max'] = $form_data['time_max'];
			} else {
				$form_data['time_max'] = $form_data['time_max'] . '' . $maxampm;
			}
		}

		if ( 'date' == $field_key ) {
			$form_data['enable_date_slot_booking'] = isset( $data['advance_setting']->enable_date_slot_booking ) ? $data['advance_setting']->enable_date_slot_booking : false;
		}

		/** Redundant Codes End. */

		$filter_data = array(
			'form_data' => $form_data,
			'data'      => $data,
		);
		/**
		 * Filter the field key based frontend form data .
		 *
		 * The dynamic portion of the hook name, $field_key.
		 *
		 * @param string $filter_data The filtered field data.
		 */
		$form_data_array = apply_filters( 'user_registration_' . $field_key . '_frontend_form_data', $filter_data, false );

		$form_data = isset( $form_data_array['form_data'] ) ? $form_data_array['form_data'] : $form_data;

		if ( isset( $data['general_setting']->field_name ) ) {
			user_registration_form_field( $data['general_setting']->field_name, $form_data );
		}
	}

	/**
	 * Inlcude advance settings file if exists
	 */
	public function get_field_advance_settings() {

		$file_name  = str_replace( 'user_registration_', '', $this->id );
		$file_path  = UR_FORM_PATH . 'settings' . UR_DS . 'class-ur-setting-' . strtolower( $file_name ) . '.php';
		$class_name = 'UR_Setting_' . ucwords( $file_name );

		if ( ! class_exists( $class_name ) ) {
			/**
			 * Filter the file name and path of advance class.
			 *
			 * The dynamic portion of the hook name, $file_name.
			 *
			 * @param string $file_name The file name.
			 * @param string $file_path The file path.
			 */
			$file_path_array = apply_filters(
				'user_registration_' . strtolower( $file_name ) . '_advance_class',
				array(

					'file_name' => strtolower( $file_name ),
					'file_path' => $file_path,
				)
			);
			$file_path       = isset( $file_path_array['file_path'] ) ? $file_path_array['file_path'] : $file_path;

			if ( file_exists( $file_path ) ) {
				include_once $file_path;
				$instance = new $class_name();
				return $instance->output( $this->admin_data );
			}
		} else {

			$instance = new $class_name();
			return $instance->output( $this->admin_data );
		}

		return '';
	}

	/**
	 * Get field general settings.
	 *
	 * @return string
	 */
	public function get_field_general_settings() {

		$general_settings     = ur_get_general_settings( $this->id );
		$general_setting_html = '';
		$captcha_unique       = uniqid();

		foreach ( $general_settings as $setting_key => $setting_value ) {
			$tooltip_html            = ! empty( $setting_value['tip'] ) ? ur_help_tip( $setting_value['tip'], false, 'ur-portal-tooltip' ) : '';
			$setting_id              = isset( $setting_value['setting_id'] ) ? $setting_value['setting_id'] : str_replace( ' ', '-', strtolower( $setting_value['label'] ) );
			$general_setting_wrapper = '<div class="ur-general-setting ur-setting-' . $setting_value['type'] . ' ur-general-setting-' . $setting_id . '">';

			if ( 'toggle' !== $setting_value['type'] ) {
				$general_setting_wrapper .= '<label for="ur-type-' . $setting_value['type'] . '">' . $setting_value['label'] . $tooltip_html . ( isset( $setting_value['add_bulk_options'] ) ? $setting_value['add_bulk_options'] : '' ) . '</label>';
			}

			$sub_string_key = substr( $this->id, strlen( 'user_registration_' ), 5 );
			$strip_prefix   = substr( $this->id, 18 );

			$smart_tags = '';
			if ( 'hidden_value' === $setting_key ) {
				/**
				 * Filter the smart tags list for general.
				 *
				 * @param array $smart_tags The smart tags list.
				 */
				$smart_tags = apply_filters( 'ur_smart_tags_list_in_general', $smart_tags );
			}

			switch ( $setting_value['type'] ) {
				case 'text':
					$extra_attribute          = in_array( $strip_prefix, ur_get_fields_without_prefix() ) && 'field_name' == $setting_key ? "disabled='disabled'" : '';
					$value                    = in_array( $strip_prefix, ur_get_fields_without_prefix() ) && 'field_name' == $setting_key ? trim( str_replace( 'user_registration_', '', $this->id ) ) : $this->get_general_setting_data( $setting_key );
					$general_setting_wrapper .= '<input value="' . esc_attr( $value ) . '" data-field="' . esc_attr( $setting_key ) . '" class="ur-general-setting-field ur-type-' . esc_attr( $setting_value['type'] ) . '" type="text" name="' . esc_attr( $setting_value['name'] ) . '"  placeholder="' . esc_attr( $setting_value['placeholder'] ) . '"';

					if ( true == $setting_value['required'] ) {
						$general_setting_wrapper .= ' required ';
					}
					$disabled = '';
					// To make invite code field name non editable.
					if ( 'learndash_course' === $value || 'invite_code' === $value || 'profile_pic_url' === $value || 'subscription_plan' === $value ) {
						$disabled = 'disabled';
					}
					$general_setting_wrapper .= $extra_attribute . ' ' . $disabled . '/>';
					break;

				case 'radio':
					// Compatibility for older version. Get string value from options in advanced settings. Modified since @1.5.7.
					if ( isset( $this->admin_data->general_setting->image_choice ) && ur_string_to_bool( $this->admin_data->general_setting->image_choice ) ) {
						$default_options = isset( $this->field_defaults['default_image_options'] ) ? $this->field_defaults['default_image_options'] : array();
						$options         = isset( $this->admin_data->general_setting->image_options ) ? $this->admin_data->general_setting->image_options : $default_options;
						$options         = array_map(
							function ( $option ) {
								if ( is_array( $option ) ) {
									$option['label'] = isset( $option['label'] ) ? trim( $option['label'] ) : '';
								} elseif ( is_object( $option ) ) {
									$option->label = isset( $option->label ) ? trim( $option->label ) : '';
								}
								return $option;
							},
							$options
						);
					} else {
						$default_options = isset( $this->field_defaults['default_options'] ) ? $this->field_defaults['default_options'] : array();
						$options         = isset( $this->admin_data->general_setting->options ) ? $this->admin_data->general_setting->options : $default_options;
						if ( 'radio' === $strip_prefix ) {
							$options = array_map( 'trim', $options );
						}
					}

					$default_value = $this->get_general_setting_data( 'default_value' );
					$default_value = ! empty( $default_value ) ? $default_value : '';

					$general_setting_wrapper .= '<ul class="ur-options-list">';
					$unique                   = uniqid();

					if ( isset( $this->admin_data->general_setting->image_choice ) && ur_string_to_bool( $this->admin_data->general_setting->image_choice ) ) {
						foreach ( $options as  $option ) {
							$label                    = is_array( $option ) ? $option['label'] : $option->label;
							$image                    = is_array( $option ) ? $option['image'] : $option->image;
							$style                    = ( empty( $image ) ) ? 'style="display: none;"' : '';
							$media_style              = ( ! empty( $image ) ) ? 'style="display: none;"' : '';
							$general_setting_wrapper .= '<li>';
							$general_setting_wrapper .= '<div class="ur-options-value-wrapper"><div class="editor-block-mover__control-drag-handle editor-block-mover__control">
							<svg width="18" height="18" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 18 18" role="img" aria-hidden="true" focusable="false"><path d="M13,8c0.6,0,1-0.4,1-1s-0.4-1-1-1s-1,0.4-1,1S12.4,8,13,8z M5,6C4.4,6,4,6.4,4,7s0.4,1,1,1s1-0.4,1-1S5.6,6,5,6z M5,10 c-0.6,0-1,0.4-1,1s0.4,1,1,1s1-0.4,1-1S5.6,10,5,10z M13,10c-0.6,0-1,0.4-1,1s0.4,1,1,1s1-0.4,1-1S13.6,10,13,10z M9,6 C8.4,6,8,6.4,8,7s0.4,1,1,1s1-0.4,1-1S9.6,6,9,6z M9,10c-0.6,0-1,0.4-1,1s0.4,1,1,1s1-0.4,1-1S9.6,10,9,10z"></path></svg>
							</div>';
							$general_setting_wrapper .= '<input value="' . esc_attr( $label ) . '" data-field="default_value" class="ur-general-setting-field ur-type-' . esc_attr( $setting_value['type'] ) . '-value" type="radio" name="' . esc_attr( $unique ) . '_value" ';

							if ( true == $setting_value['required'] ) {
								$general_setting_wrapper .= ' required ';
							}

							$general_setting_wrapper .= '' . checked( $label, $default_value, false ) . ' />';
							$general_setting_wrapper .= '<input value="' . esc_attr( $label ) . '" data-field="' . esc_attr( $setting_key ) . '" data-field-name="image-choice" class="ur-general-setting-field ur-type-' . esc_attr( $setting_value['type'] ) . '-label" type="text" name="' . esc_attr( $setting_value['name'] ) . '_label" >';

							$general_setting_wrapper .= '<a class="add" href="#"><i class="dashicons dashicons-plus"></i></a>';
							$general_setting_wrapper .= '<a class="remove" href="#"><i class="dashicons dashicons-minus"></i></a></div>';
							if ( 'radio' === $strip_prefix ) {
								$general_setting_wrapper .= '<div class="ur-image-choice-wrapper">';
								$general_setting_wrapper .= '<input type="hidden" class="ur-general-setting-field ur-type-image-choice" data-field="' . esc_attr( $setting_key ) . '" data-field-name="image-choice" name="' . esc_attr( $unique ) . '_image" value="' . esc_url( $image ) . '">';
								$general_setting_wrapper .= '<button type="button" class="upload-button ur-media-btn" ' . $media_style . '>Upload Image</button>';
								$general_setting_wrapper .= '<div class="ur-thumbnail-image" style="max-width:100px;max-height:100px;overflow:hidden;"><img src="' . esc_url( $image ) . '" style="max-width:100%;"></div>';
								$general_setting_wrapper .= '<div class="ur-actions"><button type="button" class="button ur-remove-btn" ' . $style . '>Remove</button></div></div>';
							}
							$general_setting_wrapper .= '</li>';

						}
					} elseif ( 'subscription_plan' === $strip_prefix ) {
						foreach ( $options as $key => $option ) {
							$label                = is_array( $option ) ? $option['label'] : ( $option->label ?? '' );
							$value                = is_array( $option ) ? $option['value'] : ( $option->value ?? '' );
							$sell_value           = ( is_array( $option ) && isset( $option['sell_value'] ) ) ? $option['sell_value'] : ( ( is_object( $option ) && isset( $option->sell_value ) ) ? $option->sell_value : null );
							$currency             = get_option( 'user_registration_payment_currency', 'USD' );
							$currencies           = ur_payment_integration_get_currencies();
							$currency             = $currency . ' ' . $currencies[ $currency ]['symbol'];
							$radio_field_options  = '<li class="ur-subscription-plan">';
							$radio_field_options .= '<div class="editor-block-mover__control-drag-handle editor-block-mover__control">
							<svg width="18" height="18" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 18 18" role="img" aria-hidden="true" focusable="false"><path d="M13,8c0.6,0,1-0.4,1-1s-0.4-1-1-1s-1,0.4-1,1S12.4,8,13,8z M5,6C4.4,6,4,6.4,4,7s0.4,1,1,1s1-0.4,1-1S5.6,6,5,6z M5,10 c-0.6,0-1,0.4-1,1s0.4,1,1,1s1-0.4,1-1S5.6,10,5,10z M13,10c-0.6,0-1,0.4-1,1s0.4,1,1,1s1-0.4,1-1S13.6,10,13,10z M9,6 C8.4,6,8,6.4,8,7s0.4,1,1,1s1-0.4,1-1S9.6,6,9,6z M9,10c-0.6,0-1,0.4-1,1s0.4,1,1,1s1-0.4,1-1S9.6,10,9,10z"></path></svg>
							</div>';
							$radio_field_options .= '<input value="' . esc_attr( $label ) . '" data-field="default_value" class="ur-general-setting-field ur-type-' . esc_attr( $setting_value['type'] ) . '-value" type="checkbox" name="' . esc_attr( $unique ) . '_value" ';
							if ( true == $setting_value['required'] ) {
								$radio_field_options .= ' required ';
							}

							$radio_field_options     .= '' . checked( $label, $default_value, false ) . ' />';
							$radio_field_options     .= '<input value="' . esc_attr( $label ) . '" data-field="' . esc_attr( $setting_key ) . '" data-field-name="' . esc_attr( $strip_prefix ) . '" class="ur-general-setting-field  ur-type-' . esc_attr( $setting_value['type'] ) . '-label" type="text" name="' . esc_attr( $setting_value['name'] ) . '_label" >';
							$radio_field_options     .= '<div class="ur-regular-price"><span>Regular Price</span><input value="' . esc_attr( $value ) . '" data-field="' . esc_attr( $setting_key ) . '" data-field-name="' . esc_attr( $strip_prefix ) . '" class="ur-general-setting-field  ur-type-' . esc_attr( $setting_value['type'] ) . '-money-input" type="text" name="' . esc_attr( $setting_value['name'] ) . '_value" data-currency=" ' . esc_attr( $currency ) . ' " ></div>';
							$radio_field_options     .= '<div class="ur-selling-price"><span>Selling Price</span><input value="' . esc_attr( $sell_value ) . '" data-field="' . esc_attr( $setting_key ) . '" data-field-name="' . esc_attr( $strip_prefix ) . '" class="ur-general-setting-field ur-' . esc_attr( $setting_value['type'] ) . '-selling-price-input" type="text" name="' . esc_attr( $setting_value['name'] ) . '_selling_value" data-currency=" ' . esc_attr( $currency ) . ' " placeholder="0.00"></div>';
							$radio_field_options     .= '<a class="add" href="#"><i class="dashicons dashicons-plus"></i></a>';
							$radio_field_options     .= '<a class="remove" href="#"><i class="dashicons dashicons-minus"></i></a>';
							$radio_field_options     .= '</li>';
							$general_setting_wrapper .= apply_filters( 'user_registration_radio_field_options', $radio_field_options, $option, $setting_key, $setting_value, $strip_prefix, $unique, $default_value );
						}
					} else {
						foreach ( $options as  $option ) {
							$radio_field_options  = '<li>';
							$radio_field_options .= '<div class="ur-options-value-wrapper"><div class="editor-block-mover__control-drag-handle editor-block-mover__control">
							<svg width="18" height="18" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 18 18" role="img" aria-hidden="true" focusable="false"><path d="M13,8c0.6,0,1-0.4,1-1s-0.4-1-1-1s-1,0.4-1,1S12.4,8,13,8z M5,6C4.4,6,4,6.4,4,7s0.4,1,1,1s1-0.4,1-1S5.6,6,5,6z M5,10 c-0.6,0-1,0.4-1,1s0.4,1,1,1s1-0.4,1-1S5.6,10,5,10z M13,10c-0.6,0-1,0.4-1,1s0.4,1,1,1s1-0.4,1-1S13.6,10,13,10z M9,6 C8.4,6,8,6.4,8,7s0.4,1,1,1s1-0.4,1-1S9.6,6,9,6z M9,10c-0.6,0-1,0.4-1,1s0.4,1,1,1s1-0.4,1-1S9.6,10,9,10z"></path></svg>
							</div>';
							$radio_field_options .= '<input value="' . esc_attr( $option ) . '" data-field="default_value" class="ur-general-setting-field ur-type-' . esc_attr( $setting_value['type'] ) . '-value" type="radio" name="' . esc_attr( $unique ) . '_value" ';

							if ( true == $setting_value['required'] ) {
								$radio_field_options .= ' required ';
							}

							$radio_field_options .= '' . checked( $option, $default_value, false ) . ' />';
							$radio_field_options .= '<input value="' . esc_attr( $option ) . '" data-field="' . esc_attr( $setting_key ) . '" data-field-name="image-choice" class="ur-general-setting-field ur-type-' . esc_attr( $setting_value['type'] ) . '-label" type="text" name="' . esc_attr( $setting_value['name'] ) . '_label" >';

							$radio_field_options .= '<a class="add" href="#"><i class="dashicons dashicons-plus"></i></a>';
							$radio_field_options .= '<a class="remove" href="#"><i class="dashicons dashicons-minus"></i></a></div>';
							if ( 'radio' === $strip_prefix ) {
								$radio_field_options .= '<div class="ur-image-choice-wrapper" style="display:none;">';
								$radio_field_options .= '<input type="hidden" class="ur-general-setting-field ur-type-image-choice" data-field="' . esc_attr( $setting_key ) . '" data-field-name="image-choice" name="' . esc_attr( $unique ) . '_image" value="">';
								$radio_field_options .= '<button type="button" class="upload-button ur-media-btn">Upload Image</button>';
								$radio_field_options .= '<div class="ur-thumbnail-image" style="max-width:100px;max-height:100px;overflow:hidden;"><img src="" style="max-width:100%;"></div>';
								$radio_field_options .= '<div class="ur-actions"><button type="button" class="button ur-remove-btn" style="display:none">Remove</button></div></div>';
							}
							$radio_field_options .= '</li>';

							$general_setting_wrapper .= apply_filters( 'user_registration_radio_field_options', $radio_field_options, $option, $setting_key, $setting_value, $strip_prefix, $unique, $default_value );

						}
					}
					$general_setting_wrapper .= '</ul>';
					break;

				case 'checkbox':
					// Compatibility for older version. Get string value from options in advanced settings. Modified since @1.5.7.
					if ( isset( $this->admin_data->general_setting->image_choice ) && ur_string_to_bool( $this->admin_data->general_setting->image_choice ) && 'checkbox' === $strip_prefix ) {
						$default_options = isset( $this->field_defaults['default_image_options'] ) ? $this->field_defaults['default_image_options'] : array();
						$options         = isset( $this->admin_data->general_setting->image_options ) ? $this->admin_data->general_setting->image_options : $default_options;
						$options         = array_map(
							function ( $option ) {
								if ( is_array( $option ) ) {
									$option['label'] = isset( $option['label'] ) ? trim( $option['label'] ) : '';
								} elseif ( is_object( $option ) ) {
									$option->label = isset( $option->label ) ? trim( $option->label ) : '';
								}
								return $option;
							},
							$options
						);
					} else {
						$default_options = isset( $this->field_defaults['default_options'] ) ? $this->field_defaults['default_options'] : array();
						$options         = isset( $this->admin_data->general_setting->options ) ? $this->admin_data->general_setting->options : $default_options;
						if ( 'checkbox' === $strip_prefix ) {
							$options = array_map( 'trim', $options );
						}
					}

					$default_values = $this->get_general_setting_data( 'default_value' );
					$default_values = ! empty( $default_values ) ? $default_values : array();
					$default_values = array_map( 'trim', $default_values );

					$general_setting_wrapper .= '<ul class="ur-options-list">';
					$unique                   = uniqid();

					if ( 'multiple_choice' === $strip_prefix ) {

						foreach ( $options as $key => $option ) {
							$label                    = is_array( $option ) ? $option['label'] : $option->label;
							$value                    = is_array( $option ) ? $option['value'] : $option->value;
							$sell_value               = ( is_array( $option ) && isset( $option['sell_value'] ) ) ? $option['sell_value'] : ( ( is_object( $option ) && isset( $option->sell_value ) ) ? $option->sell_value : null );
							$image                    = ( is_array( $option ) && isset( $option['image'] ) ) ? $option['image'] : ( ( is_object( $option ) && isset( $option->image ) ) ? $option->image : null );
							$style                    = ( empty( $image ) ) ? 'style="display: none;"' : '';
							$media_style              = ( ! empty( $image ) ) ? 'style="display: none;"' : '';
							$currency                 = get_option( 'user_registration_payment_currency', 'USD' );
							$currencies               = ur_payment_integration_get_currencies();
							$currency                 = $currency . ' ' . $currencies[ $currency ]['symbol'];
							$general_setting_wrapper .= '<li class="ur-multiple-choice">';
							$general_setting_wrapper .= '<div class="ur-options-value-wrapper"><div class="editor-block-mover__control-drag-handle editor-block-mover__control">
							<svg width="18" height="18" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 18 18" role="img" aria-hidden="true" focusable="false"><path d="M13,8c0.6,0,1-0.4,1-1s-0.4-1-1-1s-1,0.4-1,1S12.4,8,13,8z M5,6C4.4,6,4,6.4,4,7s0.4,1,1,1s1-0.4,1-1S5.6,6,5,6z M5,10 c-0.6,0-1,0.4-1,1s0.4,1,1,1s1-0.4,1-1S5.6,10,5,10z M13,10c-0.6,0-1,0.4-1,1s0.4,1,1,1s1-0.4,1-1S13.6,10,13,10z M9,6 C8.4,6,8,6.4,8,7s0.4,1,1,1s1-0.4,1-1S9.6,6,9,6z M9,10c-0.6,0-1,0.4-1,1s0.4,1,1,1s1-0.4,1-1S9.6,10,9,10z"></path></svg>
							</div>';
							$general_setting_wrapper .= '<input value="' . esc_attr( $label ) . '" data-field="default_value" class="ur-general-setting-field ur-type-' . esc_attr( $setting_value['type'] ) . '-value" type="checkbox" name="' . esc_attr( $unique ) . '_value" ';
							if ( true == $setting_value['required'] ) {
								$general_setting_wrapper .= ' required ';
							}

							if ( in_array( $label, $default_values ) ) {
								$general_setting_wrapper .= 'checked ="checked" />';
							} else {
								$general_setting_wrapper .= '/>';
							}
							$general_setting_wrapper .= '<input value="' . esc_attr( $label ) . '" data-field="' . esc_attr( $setting_key ) . '" data-field-name="' . esc_attr( $strip_prefix ) . '" class="ur-general-setting-field  ur-type-' . esc_attr( $setting_value['type'] ) . '-label" type="text" name="' . esc_attr( $setting_value['name'] ) . '_label" >';
							$general_setting_wrapper .= '<div class="ur-regular-price"><span>Regular Price</span><input value="' . esc_attr( $value ) . '" data-field="' . esc_attr( $setting_key ) . '" data-field-name="' . esc_attr( $strip_prefix ) . '" class="ur-general-setting-field  ur-type-' . esc_attr( $setting_value['type'] ) . '-money-input" type="text" name="' . esc_attr( $setting_value['name'] ) . '_value" data-currency=" ' . esc_attr( $currency ) . ' " ></div>';
							$general_setting_wrapper .= '<div class="ur-selling-price"><span>Selling Price</span><input value="' . esc_attr( $sell_value ) . '" data-field="' . esc_attr( $setting_key ) . '" data-field-name="' . esc_attr( $strip_prefix ) . '" class="ur-general-setting-field ur-' . esc_attr( $setting_value['type'] ) . '-selling-price-input" type="text" name="' . esc_attr( $setting_value['name'] ) . '_selling_value" data-currency=" ' . esc_attr( $currency ) . ' " placeholder="0.00"></div>';
							$general_setting_wrapper .= '<a class="add" href="#"><i class="dashicons dashicons-plus"></i></a>';
							$general_setting_wrapper .= '<a class="remove" href="#"><i class="dashicons dashicons-minus"></i></a></div>';
							$general_setting_wrapper .= '<div class="ur-image-choice-wrapper">';
							$general_setting_wrapper .= '<input type="hidden" class="ur-general-setting-field ur-type-image-choice" data-field="' . esc_attr( $setting_key ) . '" data-field-name="' . esc_attr( $strip_prefix ) . '" name="' . esc_attr( $unique ) . '_image" value="' . esc_url( $image ) . '">';
							$general_setting_wrapper .= '<button type="button" class="upload-button ur-media-btn" ' . $media_style . '>Upload Image</button>';
							$general_setting_wrapper .= '<div class="ur-thumbnail-image" style="max-width:100px;max-height:100px;overflow:hidden;"><img src="' . esc_url( $image ) . '" style="max-width:100%;"></div>';
							$general_setting_wrapper .= '<div class="ur-actions"><button type="button" class="button ur-remove-btn" ' . $style . '>Remove</button></div></div>';
							$general_setting_wrapper .= '</li>';
						}
					} elseif ( isset( $this->admin_data->general_setting->image_choice ) && ur_string_to_bool( $this->admin_data->general_setting->image_choice ) ) {
						foreach ( $options as  $option ) {
							$label                    = is_array( $option ) ? $option['label'] : $option->label;
							$image                    = is_array( $option ) ? $option['image'] : $option->image;
							$style                    = ( empty( $image ) ) ? 'style="display: none;"' : '';
							$media_style              = ( ! empty( $image ) ) ? 'style="display: none;"' : '';
							$general_setting_wrapper .= '<li>';
							$general_setting_wrapper .= '<div class="ur-options-value-wrapper"><div class="editor-block-mover__control-drag-handle editor-block-mover__control">
							<svg width="18" height="18" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 18 18" role="img" aria-hidden="true" focusable="false"><path d="M13,8c0.6,0,1-0.4,1-1s-0.4-1-1-1s-1,0.4-1,1S12.4,8,13,8z M5,6C4.4,6,4,6.4,4,7s0.4,1,1,1s1-0.4,1-1S5.6,6,5,6z M5,10 c-0.6,0-1,0.4-1,1s0.4,1,1,1s1-0.4,1-1S5.6,10,5,10z M13,10c-0.6,0-1,0.4-1,1s0.4,1,1,1s1-0.4,1-1S13.6,10,13,10z M9,6 C8.4,6,8,6.4,8,7s0.4,1,1,1s1-0.4,1-1S9.6,6,9,6z M9,10c-0.6,0-1,0.4-1,1s0.4,1,1,1s1-0.4,1-1S9.6,10,9,10z"></path></svg>
							</div>';
							$general_setting_wrapper .= '<input value="' . esc_attr( $label ) . '" data-field="default_value" class="ur-general-setting-field ur-type-' . esc_attr( $setting_value['type'] ) . '-value" type="checkbox" name="' . esc_attr( $unique ) . '_value" ';

							if ( true == $setting_value['required'] ) {
								$general_setting_wrapper .= ' required ';
							}

							if ( in_array( $label, $default_values ) ) {
								$general_setting_wrapper .= 'checked ="checked" />';
							} else {
								$general_setting_wrapper .= '/>';
							}

							$general_setting_wrapper .= '<input value="' . esc_attr( $label ) . '" data-field="' . esc_attr( $setting_key ) . '" data-field-name="image-choice" class="ur-general-setting-field ur-type-' . esc_attr( $setting_value['type'] ) . '-label" type="text" name="' . esc_attr( $setting_value['name'] ) . '_label" >';

							$general_setting_wrapper .= '<a class="add" href="#"><i class="dashicons dashicons-plus"></i></a>';
							$general_setting_wrapper .= '<a class="remove" href="#"><i class="dashicons dashicons-minus"></i></a></div>';
							$general_setting_wrapper .= '<div class="ur-image-choice-wrapper">';
							$general_setting_wrapper .= '<input type="hidden" class="ur-general-setting-field ur-type-image-choice" data-field="' . esc_attr( $setting_key ) . '" data-field-name="image-choice" name="' . esc_attr( $unique ) . '_image" value="' . esc_url( $image ) . '">';
							$general_setting_wrapper .= '<button type="button" class="upload-button ur-media-btn" ' . $media_style . '>Upload Image</button>';
							$general_setting_wrapper .= '<div class="ur-thumbnail-image" style="max-width:100px;max-height:100px;overflow:hidden;"><img src="' . esc_url( $image ) . '" style="max-width:100%;"></div>';
							$general_setting_wrapper .= '<div class="ur-actions"><button type="button" class="button ur-remove-btn" ' . $style . '>Remove</button></div></div>';
							$general_setting_wrapper .= '</li>';

						}
					} else {
						foreach ( $options as  $option ) {

							$general_setting_wrapper .= '<li>';
							$general_setting_wrapper .= '<div class="ur-options-value-wrapper"><div class="editor-block-mover__control-drag-handle editor-block-mover__control">
							<svg width="18" height="18" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 18 18" role="img" aria-hidden="true" focusable="false"><path d="M13,8c0.6,0,1-0.4,1-1s-0.4-1-1-1s-1,0.4-1,1S12.4,8,13,8z M5,6C4.4,6,4,6.4,4,7s0.4,1,1,1s1-0.4,1-1S5.6,6,5,6z M5,10 c-0.6,0-1,0.4-1,1s0.4,1,1,1s1-0.4,1-1S5.6,10,5,10z M13,10c-0.6,0-1,0.4-1,1s0.4,1,1,1s1-0.4,1-1S13.6,10,13,10z M9,6 C8.4,6,8,6.4,8,7s0.4,1,1,1s1-0.4,1-1S9.6,6,9,6z M9,10c-0.6,0-1,0.4-1,1s0.4,1,1,1s1-0.4,1-1S9.6,10,9,10z"></path></svg>
							</div>';
							$general_setting_wrapper .= '<input value="' . esc_attr( $option ) . '" data-field="default_value" class="ur-general-setting-field ur-type-' . esc_attr( $setting_value['type'] ) . '-value" type="checkbox" name="' . esc_attr( $unique ) . '_value" ';

							if ( true == $setting_value['required'] ) {
								$general_setting_wrapper .= ' required ';
							}

							if ( in_array( $option, $default_values ) ) {
								$general_setting_wrapper .= 'checked ="checked" />';
							} else {
								$general_setting_wrapper .= '/>';
							}

							$general_setting_wrapper .= '<input value="' . esc_attr( $option ) . '" data-field="' . esc_attr( $setting_key ) . '" class="ur-general-setting-field ur-type-' . esc_attr( $setting_value['type'] ) . '-label" type="text" name="' . esc_attr( $setting_value['name'] ) . '_label" >';

							$general_setting_wrapper .= '<a class="add" href="#"><i class="dashicons dashicons-plus"></i></a>';
							$general_setting_wrapper .= '<a class="remove" href="#"><i class="dashicons dashicons-minus"></i></a></div>';
							$general_setting_wrapper .= '<div class="ur-image-choice-wrapper" style="display:none;">';
							$general_setting_wrapper .= '<input type="hidden" class="ur-general-setting-field ur-type-image-choice" data-field="' . esc_attr( $setting_key ) . '" data-field-name="image-choice" name="' . esc_attr( $unique ) . '_image" value="">';
							$general_setting_wrapper .= '<button type="button" class="upload-button ur-media-btn">Upload Image</button>';
							$general_setting_wrapper .= '<div class="ur-thumbnail-image" style="max-width:100px;max-height:100px;overflow:hidden;"><img src="" style="max-width:100%;"></div>';
							$general_setting_wrapper .= '<div class="ur-actions"><button type="button" class="button ur-remove-btn" style="display:none;">Remove</button></div></div>';
							$general_setting_wrapper .= '</li>';

						}
					}
						$general_setting_wrapper .= '</ul>';
					break;
				case 'toggle':
					$disabled = '';

					// To make invite code required field non editable.
					if ( 'required' === $setting_key && 'invite_code' === $strip_prefix ) {
						$disabled = 'disabled';
					}

						$general_setting_wrapper .= '<div class="ur-toggle-section ur-form-builder-toggle" style="justify-content: space-between;">';
						$general_setting_wrapper .= '<label class="ur-label checkbox" for="ur-type-' . $setting_value['type'] . '">' . $setting_value['label'] . $tooltip_html . '</label>';
						$general_setting_wrapper .= '<span class="user-registration-toggle-form">';
						$value                    = $this->get_general_setting_data( $setting_key ) === 1 && isset( $setting_value['default'] ) ? $setting_value['default'] : $this->get_general_setting_data( $setting_key );
						$checked                  = ur_string_to_bool( $this->get_general_setting_data( $setting_key ) ) ? 'checked' : '';
						$general_setting_wrapper .= '<input type="checkbox" data-field="' . esc_attr( $setting_key ) . '" class="ur-general-setting-field ur-type-' . $setting_value['type'] . '"  name="' . esc_attr( $setting_value['name'] ) . '" ' . $checked . ' ' . $disabled . '>';
						$general_setting_wrapper .= '<span class="slider round"></span>';
						$general_setting_wrapper .= '</span>';
						$general_setting_wrapper .= '</div>';
					break;
				case 'select':
					if ( isset( $setting_value['options'] )
						&& gettype( $setting_value['options'] ) == 'array' ) {

						$disabled = '';
							// To make invite code required field non editable.
						if ( 'required' === $setting_key && 'invite_code' === $strip_prefix ) {
							$disabled = 'disabled';
						}

						$general_setting_wrapper .= '<select data-field="' . esc_attr( $setting_key ) . '" class="ur-general-setting-field ur-type-' . esc_attr( $setting_value['type'] ) . '"  name="' . esc_attr( $setting_value['name'] ) . '" ' . $disabled . '>';

						foreach ( $setting_value['options'] as $option_key => $option_value ) {
							$selected                 = $this->get_general_setting_data( $setting_key ) == $option_key ? 'selected = selected' : '';
							$general_setting_wrapper .= '<option ' . esc_attr( $selected ) . " value='" . esc_attr( $option_key ) . "'>" . esc_html( $option_value ) . '</option>';
						}

						$general_setting_wrapper .= '</select>';
					}
					break;

				case 'textarea':
					$general_setting_wrapper .= '<textarea data-field="' . esc_attr( $setting_key ) . '" class="ur-general-setting-field ur-type-' . esc_attr( $setting_value['type'] ) . '"  name="' . esc_attr( $setting_value['name'] ) . '" placeholder= "' . esc_attr( $setting_value['placeholder'] ) . '" ';

					if ( true == $setting_value['required'] ) {
						$general_setting_wrapper .= ' required >';
					}
					$general_setting_wrapper .= esc_html( $this->get_general_setting_data( $setting_key ) ) . '</textarea>';
					break;

				case 'hidden':
					$value = isset( $setting_value['default'] ) ? $setting_value['default'] : '';

					$general_setting_wrapper .= '<input value="' . esc_attr( $value ) . '" data-field="' . esc_attr( $setting_key ) . '" class="ur-general-setting-field ur-type-' . esc_attr( $setting_value['type'] ) . '" type="hidden" name="' . esc_attr( $setting_value['name'] ) . '"  placeholder="' . esc_attr( $setting_value['placeholder'] ) . '"';

					if ( true == $setting_value['required'] ) {
						$general_setting_wrapper .= ' required ';
					}

					$general_setting_wrapper .= '/>';

					break;
				case 'number':
					$val                      = $this->get_general_setting_data( $setting_key );
					$value                    = ! empty( $val ) ? $val : $setting_value['default'];
					$general_setting_wrapper .= '<input value="' . esc_attr( $value ) . '" data-field="' . esc_attr( $setting_key ) . '" class="ur-general-setting-field ur-type-' . esc_attr( $setting_value['type'] ) . '" type="number" name="' . esc_attr( $setting_value['name'] ) . '" min = "1"';

					if ( true == $setting_value['required'] ) {
						$general_setting_wrapper .= ' required ';
					}

					$general_setting_wrapper .= '/>';
					break;
				case 'captcha':
					$default_options               = isset( $this->field_defaults['default_options'] ) ? $this->field_defaults['default_options'] : array();
					$default_image_captcha_options = isset( $this->field_defaults['default_image_options'] ) ? $this->field_defaults['default_image_options'] : array();
					$old_options                   = isset( $this->admin_data->advance_setting->choices ) ? explode( ',', trim( $this->admin_data->advance_setting->choices, ',' ) ) : $default_options;
					$options                       = isset( $this->admin_data->general_setting->options ) ? $this->admin_data->general_setting->options : $old_options;
					$image_options                 = isset( $this->admin_data->general_setting->image_captcha_options ) ? $this->admin_data->general_setting->image_captcha_options : $default_image_captcha_options;

					$general_setting_wrapper .= '<ul class="ur-options-list" data-unique-captcha="' . $captcha_unique . '">';

					if ( 'options' === $setting_key ) {
						foreach ( $options as $key => $option ) {
							$label                    = is_array( $option ) ? $option['question'] : $option->question;
							$answer                   = is_array( $option ) ? $option['answer'] : $option->answer;
							$general_setting_wrapper .= '<li class="ur-custom-captcha">';
							$general_setting_wrapper .= '<div class="editor-block-mover__control-drag-handle editor-block-mover__control">
							<svg width="18" height="18" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 18 18" role="img" aria-hidden="true" focusable="false"><path d="M13,8c0.6,0,1-0.4,1-1s-0.4-1-1-1s-1,0.4-1,1S12.4,8,13,8z M5,6C4.4,6,4,6.4,4,7s0.4,1,1,1s1-0.4,1-1S5.6,6,5,6z M5,10 c-0.6,0-1,0.4-1,1s0.4,1,1,1s1-0.4,1-1S5.6,10,5,10z M13,10c-0.6,0-1,0.4-1,1s0.4,1,1,1s1-0.4,1-1S13.6,10,13,10z M9,6 C8.4,6,8,6.4,8,7s0.4,1,1,1s1-0.4,1-1S9.6,6,9,6z M9,10c-0.6,0-1,0.4-1,1s0.4,1,1,1s1-0.4,1-1S9.6,10,9,10z"></path></svg>
							</div>';
							$general_setting_wrapper .= '<input value="' . esc_attr( $label ) . '" data-field="' . esc_attr( $setting_key ) . '" data-field-name="' . esc_attr( $strip_prefix ) . '" class="ur-general-setting-field ur-type-' . esc_attr( $setting_value['type'] ) . '-question" type="text" name="' . esc_attr( $setting_value['name'] ) . '_captcha_question">';
							$general_setting_wrapper .= '<input value="' . esc_attr( $answer ) . '" data-field="' . esc_attr( $setting_key ) . '" data-field-name="' . esc_attr( $strip_prefix ) . '" class="ur-general-setting-field ur-type-' . esc_attr( $setting_value['type'] ) . '-answer" type="text" name="' . esc_attr( $setting_value['name'] ) . '_captcha_answer">';
							$general_setting_wrapper .= '<a class="add" href="#"><i class="dashicons dashicons-plus"></i></a>';
							$general_setting_wrapper .= '<a class="remove" href="#"><i class="dashicons dashicons-minus"></i></a>';
							$general_setting_wrapper .= '</li>';
						}
					} elseif ( 'image_captcha_options' === $setting_key ) {
						foreach ( $image_options as $key => $option ) {
							$option_json = json_encode( $option );
							$option      = json_decode( $option_json, true );
							if ( isset( $option['icon_tag'] ) ) {
								$icon_tag = $option['icon_tag'];
								unset( $option['icon_tag'] );
							}
							if ( isset( $option['correct_icon'] ) ) {
								$correct_icon = $option['correct_icon'];
								unset( $option['correct_icon'] );
							}

							$general_setting_wrapper .= '<li class="ur-custom-captcha" data-group=' . $key . '>';

							$general_setting_wrapper .= '<div class="icons-group">';

							foreach ( $option as $icon_key => $icon_value ) {
								$icon_checked             = ( $correct_icon === $icon_key ) ? 'checked=checked' : '';
								$general_setting_wrapper .= '<div class="icon-wrap">';
								$general_setting_wrapper .= '<label>';
								$general_setting_wrapper .= '<input type="radio" data-field="' . esc_attr( $setting_key ) . '" data-field-name="' . esc_attr( $strip_prefix ) . '" class="ur-general-setting-field ur-captcha-icon-radio" name="' . esc_attr( $setting_value['name'] ) . '[' . $key . '][correct_icon][' . $captcha_unique . ']" value="' . esc_attr( $icon_key ) . '" ' . esc_attr( $icon_checked ) . ' /><input type="hidden" data-field="' . esc_attr( $setting_key ) . '" data-field-name="' . esc_attr( $strip_prefix ) . '" name="' . esc_attr( $setting_value['name'] ) . '[' . esc_attr( $key ) . '][' . esc_attr( $icon_key ) . ']" value="' . esc_attr( $icon_value ) . '" class="ur-general-setting-field captcha-icon" />';
								$general_setting_wrapper .= '<span class="' . esc_attr( $icon_value ) . '"></span>';
								$general_setting_wrapper .= '</label>';
								$general_setting_wrapper .= '<input class="button dashicons-picker" type="button" value="Choose Icon" data-name="" data-icon-key="' . esc_attr( $icon_key ) . '" data-group-id="' . esc_attr( $key ) . '" data-target="#dashicons_picker_example_icon1"/>';
								$general_setting_wrapper .= '</div>';
							}

							$general_setting_wrapper .= '</div>';
							$general_setting_wrapper .= '<input value="' . esc_attr( $icon_tag ) . '" data-field="' . esc_attr( $setting_key ) . '" data-field-name="' . esc_attr( $strip_prefix ) . '" class="ur-general-setting-field ur-type-' . esc_attr( $setting_value['type'] ) . '-icon-tag" type="text" name="' . esc_attr( $setting_value['name'] ) . '[' . esc_attr( $key ) . '][icon_tag]" placeholder="Icon Tag">';
							$general_setting_wrapper .= '<a class="remove remove-icon-group" href="#"><i class="dashicons dashicons-minus"></i>Remove Group</a>';
							$general_setting_wrapper .= '</li>';
						}
						$general_setting_wrapper .= '<a class="add add-icon-group" data-last-group="' . esc_attr( $key ) . '" href="#"><i class="dashicons dashicons-plus"></i>Add New Group</a>';
					}
					$general_setting_wrapper .= '</ul>';
					break;

				default:
					/**
					 * Filter the field general settings.
					 *
					 * The dynamic portion of the hook name, $setting_value['type'].
					 *
					 * @param string $this The current object.
					 */
					$general_setting_wrapper .= apply_filters( 'user_registration_form_field_general_setting_' . $setting_value['type'], $this );

			}// End switch().

				$general_setting_wrapper .= $smart_tags;
				$general_setting_wrapper .= '</div>';
				$general_setting_html    .= $general_setting_wrapper;

		}// End foreach().

		return $general_setting_html;
	}

	/**
	 * Display Setting for each fields in options tab
	 *
	 * @return string $settings
	 */
	public function get_setting() {

		$strip_prefix = substr( $this->id, 18 );
		$class        = 'ur-general-setting-' . $strip_prefix;

		$settings  = "<div class='ur-general-setting-block " . esc_attr( $class ) . "'>";
		$settings .= '<h2 class="ur-toggle-heading closed">' . esc_html__( 'General Settings', 'user-registration' ) . '</h2><hr>';
		$settings .= '<div class="ur-toggle-content">';
		$settings .= $this->get_field_general_settings();
		$settings .= '</div>';
		$settings .= '</div>';

		$advance_settings = $this->get_field_advance_settings();

		if ( ! empty( $advance_settings ) ) {
			$settings .= "<div class='user-registration-field-option-group ur-advance-setting-block'>";
			$settings .= '<h2 class="ur-toggle-heading closed">' . __( 'Advanced Settings', 'user-registration' ) . '</h2><hr>';
			$settings .= '<div class="ur-toggle-content">';
			$settings .= $advance_settings;
			$settings .= '</div>';
			$settings .= '</div>';
		}

		// Redundent code start.
		ob_start();
		/**
		 * Action to add settings after advance settings.
		 *
		 * @param array $settings The settings array.
		 * @param array $this->id The field id.
		 * @param array $this->admin_data The admin data.
		 */
		do_action( 'user_registration_after_advance_settings', $this->id, $this->admin_data );
		$settings .= ob_get_clean();
		// Redundent code end.
		/**
		 * Filter to modify or add settings after advance settings.
		 *
		 * @param array $settings The settings array.
		 * @param array $this->id The field id.
		 * @param array $this->admin_data The admin data.
		 */
		$settings = apply_filters( 'user_registration_after_advance_settings_filter', $settings, $this->id, $this->admin_data );
		return $settings;
	}

	/**
	 * Validation for form field.
	 *
	 * @param object $single_form_field The field being validate.
	 * @param object $form_data Form Data.
	 * @param string $filter_hook Filter for validation messages.
	 * @param int    $form_id Form ID.
	 */
	abstract public function validation( $single_form_field, $form_data, $filter_hook, $form_id );
}
