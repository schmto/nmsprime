<?php

namespace Modules\HfcReq\Http\Controllers;

use Modules\HfcReq\Entities\NetElementType;
use Modules\HfcSnmp\Entities\OID;

class NetElementTypeController extends \BaseController {

    /**
     * defines the formular fields for the edit and create view
     */
	public function view_form_fields($model = null)
	{
		$hidden  = in_array($model->name, ['Net', 'Cluster']);
		$parents = $model->html_list(NetElementType::all(), 'name', true);

		// label has to be the same like column in sql table
		return array(
			array('form_type' => 'text', 'name' => 'name', 'description' => 'Name', 'options' => $hidden ? ['readonly'] : []),
			array('form_type' => 'text', 'name' => 'vendor', 'description' => 'Vendor', 'hidden' => $hidden ? '1' : '0'),
			array('form_type' => 'text', 'name' => 'version', 'description' => 'Version', 'hidden' => $hidden ? '1' : '0'),
			array('form_type' => 'select', 'name' => 'parent_id', 'description' => 'Parent Device Type', 'value' => $parents, 'hidden' => $hidden ? '1' : '0'),
			array('form_type' => 'text', 'name' => 'icon_name', 'description' => 'Icon'),
			array('form_type' => 'textarea', 'name' => 'description', 'description' => 'Description')
		);
	}


	/**
	 * Assign OIDs to NetElementType - Store in pivot/intermediate-table
	 *
	 * @param 	$id 			integer 	device type
	 * @input 	$mibfile_id 	integer 	ID of MIB-File we want to attach the OIDs to the device type
	 */
	public function add_oid_from_mib($id)
	{
		if (($mibfile_id = \Request::input('mibfile_id')) == 0)
			return \Redirect::back();

		// generate list of OIDs and attach to device type (fastest method)
		$oids = OID::where('mibfile_id', '=', $mibfile_id)->get(['id'])->keyBy('id')->keys()->all();

		$devtype = NetElementType::findOrFail($id);
		$devtype->oids()->attach($oids);

		return \Redirect::route('NetElementType.edit', $devtype->id);
	}


	/**
	 * Return the View for Assigning existing OIDs to the NetElementType
	 */
	public function assign($id)
	{
		$view_var 		= NetElementType::findOrFail($id);
		$view_header 	= 'Attach single OIDs';
		$headline       = 'Headline';

		$mibs = \Modules\HfcSnmp\Entities\MibFile::select(['id', 'name', 'version'])->get();
		$mibs = isset($mibs[0]) ? $mibs[0]->html_list($mibs, 'name', true) : [];

		// exclude mibs that don't have OIDs ??
		// foreach ($mibs as $mib)
		// {
		// 	if ($mib->oids)
		// 		$mibs_e[$mib->id] = $mib->name;
		// }

		$oids 	  = [];
		$oids_raw = OID::get(['id', 'name', 'oid']);
		foreach ($oids_raw as $key => $oid)
			$oids[$oid->id] = $oid->name.' - '.$oid->oid; 

		return \View::make('hfcreq::NetElementType.assign', $this->compact_prep_view(compact('view_header', 'headline', 'view_var', 'oids', 'mibs')));
	}


	/**
	 * Attach single chosen OIDs (multiselect) to NetElementType - Store in pivot/intermediate-table
	 *
	 * @param 	$id 			integer 	device type
	 * @input 					array 		IDs of the OIDs we want to attach to the given device type transfered via HTTP POST/PUT
	 */
	public function attach($id)
	{
		$devtype = NetElementType::findOrFail($id);
		$devtype->oids()->attach(\Request::input('oid_id'));

		return \Redirect::route('NetElementType.edit', $devtype->id);
	}


	/**
	 * Detach an existing OID from the NetElementType
	 */
	public function detach($id)
	{
		$devtype = NetElementType::findOrFail($id);
		$devtype->oids()->detach(array_keys(\Request::input('ids')));

		return \Redirect::back();
	}

	/**
	 * Detach all attached OID from the NetElementType
	 */
	public function detach_all($id)
	{
		$devtype = NetElementType::findOrFail($id);
		$oids 	 = array_keys($devtype->oids->keyBy('id')->all());

		$devtype->oids()->detach($oids);

		return \Redirect::back();
	}


}
