<?php

namespace Modules\BillingBase\Entities;

class Price extends \BaseModel {

	// The associated SQL table for this Model
	public $table = 'price';

	// Add your validation rules here
	public static function rules($id = null)
	{
		return array(
			// 'hostname' => 'required|unique:cmts,hostname,'.$id.',id,deleted_at,NULL'  	// unique: table, column, exception , (where clause)
		);
	}


	/**
	 * View related stuff
	 */

	// Name of View
	public static function get_view_header()
	{
		return 'Price Entry';
	}

	// link title in index view
	public function get_view_link_title()
	{
		return $this->type.' - '.$this->name.' | '.$this->price.' €';
	}

	// Return a pre-formated index list
	public function index_list ()
	{
		return $this->orderBy('type')->get();
	}


	/**
	 * Relationships:
	 */

	public function quality ()
	{
		return $this->belongsTo('Modules\ProvBase\Entities\Qos', 'qos_id');
	}

}