<?php

namespace Modules\ProvVoip\Entities;

// Model not found? execute composer dump-autoload in lara root dir
class PhonenumberManagement extends \BaseModel {

    // The associated SQL table for this Model
    public $table = 'phonenumbermanagement';


	// Add your validation rules here
	public static function rules($id=null)
	{
		return array(
			'phonenumber_id' => 'required|exists:phonenumber,id|min:1',
		);
	}

	// Don't forget to fill this array
	protected $fillable = [
					'phonenumber_id',
					'trcclass',
					'activation_date',
					'porting_in',
					'carrier_in',
					'ekp_in',
					'deactivation_date',
					'porting_out',
					'carrier_out',
					'ekp_out',
					'subscriber_company',
					'subscriber_salutation',
					'subscriber_academic_degree',
					'subscriber_firstname',
					'subscriber_lastname',
					'subscriber_street',
					'subscriber_house_number',
					'subscriber_zip',
					'subscriber_city',
					'subscriber_country',
				];


	// Name of View
	public static function get_view_header()
	{
		return 'Phonenumber Management';
	}

	// link title in index view
	public function get_view_link_title()
	{
		return $this->id;
	}

	/**
	 * ALL RELATIONS
	 * link with phonenumbers
	 */
	public function phonenumber()
	{
		return $this->belongsTo('Modules\ProvVoip\Entities\Phonenumber');
	}

	// belongs to an phonenumber
	public function view_belongs_to ()
	{
		return $this->phonenumber;
	}

	/**
	 * return a list [id => number] of all phonenumbers
	 */
	public function phonenumber_list()
	{
		$ret = array();
		foreach ($this->phonenumber()['phonenumbers'] as $phonenumber)
		{
			$ret[$phonenumber->id] = $phonenumber->prefix_number.'/'.$phonemumber->number;
		}

		return $ret;
	}

	/**
	 * return a list [id => number] of all phonenumber
	 */
	public function phonenumber_list_with_dummies()
	{
		$ret = array();
		foreach ($this->phonenumber() as $phonenumber_tmp)
		{
			foreach ($phonenumber_tmp as $phonenumber)
			{
				$ret[$phonenumber->id] = $phonenumber->prefix_number.'/'.$phonemumber->number;
			}
		}

		return $ret;
	}

	/**
	 * Get relation to trc classes.
	 *
	 * @author Patrick Reichel
	 */
	public function trc_class() {

		if ($this->module_is_active('provvoipenvia')) {
			return $this->hasOne('Modules\ProvVoipEnvia\Entities\TRCClass', 'trcclass');
		}

		return null;
	}

	/**
	 * Get relation to external orders.
	 *
	 * @author Patrick Reichel
	 */
	public function external_orders() {

		if ($this->module_is_active('provvoipenvia')) {
			return $this->phonenumber->hasMany('Modules\ProvVoipEnvia\Entities\EnviaOrder')->withTrashed()->where('ordertype', 'NOT LIKE', 'order/create_attachment');
		}

		return null;
	}


	/**
	 * Get relation to trc classes.
	 *
	 * @author Patrick Reichel
	 */
	public function phonebookentry() {

		return $this->hasOne('Modules\ProvVoip\Entities\PhonebookEntry', 'phonenumbermanagement_id');
	}

	/**
	 * Helper to define possible salutation values.
	 * E.g. Envia-API has a well defined set of valid values – using this method we can handle this.
	 *
	 * @author Patrick Reichel
	 */
	public function get_salutation_options() {

		$defaults = [
			'Herr',
			'Frau',
			'Firma',
			'Behörde',
		];

		if ($this->module_is_active('provvoipenvia')) {

			$options = [
				'Herrn',
				'Frau',
				'Firma',
				'Behörde',
			];
		}
		else {
			$options = $defaults;
		}

		$result = array();
		foreach ($options as $option) {
			$result[$option] = $option;
		}

		return $result;
	}


	/**
	 * Helper to define possible academic degree values.
	 * E.g. Envia-API has a well defined set of valid values – using this method we can handle this.
	 *
	 * @author Patrick Reichel
	 */
	public function get_academic_degree_options() {

		$defaults = [
			'',
			'Dr.',
			'Prof. Dr.',
		];

		if ($this->module_is_active('provvoipenvia')) {

			$options = [
				'',
				'Dr.',
				'Prof. Dr.',
			];
		}
		else {
			$options = $defaults;
		}

		$result = array();
		foreach ($options as $option) {
			$result[$option] = $option;
		}

		return $result;
	}


	// has zero or one phonebookentry object related
	public function view_has_one() {
		return array(
			'PhonebookEntry' => $this->phonebookentry,
		);
	}


	 // View Relation.
	public function view_has_many() {

		if ($this->module_is_active('provvoipenvia')) {
			$ret['EnviaOrder'] = $this->external_orders;
		}

		return $ret;
	}
}
