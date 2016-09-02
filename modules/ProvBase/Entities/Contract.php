<?php

namespace Modules\ProvBase\Entities;

use Modules\ProvBase\Entities\Qos;

class Contract extends \BaseModel {

	// get functions for some address select options
	use \App\Models\AddressFunctionsTrait;

	// The associated SQL table for this Model
	public $table = 'contract';

	// temporary Variables filled during accounting command execution (Billing)
	public $expires = false;			// flag if contract expires this month - used in accounting command
	public $charge = [];				// total charge for each different Sepa Account with net and tax values


	// Add your validation rules here
	// TODO: dependencies of active modules (billing)
	public static function rules($id = null)
	{
		return array(
			'number' => 'integer|unique:contract,number,'.$id.',id,deleted_at,NULL',
			'number2' => 'string|unique:contract,number2,'.$id.',id,deleted_at,NULL',
			'firstname' => 'required',
			'lastname' => 'required',
			'street' => 'required',
			'zip' => 'required',
			'city' => 'required',
			'phone' => 'required',
			'email' => 'email',
			'birthday' => 'required|date',
			'contract_start' => 'date',
			'contract_end' => 'dateornull', // |after:now -> implies we can not change stuff in an out-dated contract
			'sepa_iban' => 'iban',
			'sepa_bic' => 'bic',
			);
	}


	// Name of View
	public static function view_headline()
	{
		return 'Contract';
	}

	// link title in index view
	public function view_index_label()
	{
		$bsclass = 'success';

		if ($this->network_access == 0)
			$bsclass = 'danger';

		return ['index' => [$this->number, $this->firstname, $this->lastname, $this->zip, $this->city, $this->street.' '.$this->house_number],
				'index_header' => ['Contract Number', 'Firstname', 'Lastname', 'Postcode', 'City', 'Street'],
				'bsclass' => $bsclass,
				'header' => $this->number.' '.$this->firstname.' '.$this->lastname];

		// deprecated ?
		$old = $this->number2 ? ' - (Old Nr: '.$this->number2.')' : '';
		return $this->number.' - '.$this->firstname.' '.$this->lastname.' - '.$this->city.$old;
	}

	// View Relation.
	public function view_has_many()
	{
		if (\PPModule::is_active('billingbase'))
		{
			$ret['Base']['Modem'] = $this->modems;
			$ret['Base']['Item']        = $this->items;
			$ret['Base']['SepaMandate'] = $this->sepamandates;
		}

		$ret['Technical']['Modem'] = $this->modems;

		if (\PPModule::is_active('billingbase'))
		{
			$ret['Billing']['Item']        = $this->items;
			$ret['Billing']['SepaMandate'] = $this->sepamandates;
		}

		if (\PPModule::is_active('provvoipenvia'))
		{
			$ret['Envia']['EnviaOrder']['class'] = 'EnviaOrder';
			$ret['Envia']['EnviaOrder']['relation'] = $this->_envia_orders;

			// TODO: auth - loading controller from model could be a security issue ?
			$ret['Envia']['Envia API']['view']['view'] = 'provvoipenvia::ProvVoipEnvia.actions';
			$ret['Envia']['Envia API']['view']['vars']['extra_data'] = \Modules\ProvBase\Http\Controllers\ContractController::_get_envia_management_jobs($this);
		}

		if (\PPModule::is_active('ccc'))
		{
			$ret['Create Connection Infos']['Connection Information']['view']['view'] = 'ccc::prov.conn_info';
		}

		return $ret;
	}


	/*
	 * Relations
	 */
	public function modems()
	{
		return $this->hasMany('Modules\ProvBase\Entities\Modem');
	}


	/**
	 * Get the purchase tariff
	 */
	public function phonetariff_purchase() {

		if ($this->voip_enabled) {
			return $this->belongsTo('Modules\ProvVoip\Entities\PhoneTariff', 'purchase_tariff');
		}
		else {
			return null;
		}
	}


	/**
	 * Get the next purchase tariff
	 */
	public function phonetariff_purchase_next() {

		if ($this->voip_enabled) {
			return $this->belongsTo('Modules\ProvVoip\Entities\PhoneTariff', 'next_purchase_tariff');
		}
		else {
			return null;
		}
	}


	/**
	 * Get the sale tariff
	 */
	public function phonetariff_sale() {

		if ($this->voip_enabled) {
			return $this->belongsTo('Modules\ProvVoip\Entities\PhoneTariff', 'voip_id');
		}
		else {
			return null;
		}
	}


	/**
	 * Get the next sale tariff
	 */
	public function phonetariff_sale_next() {

		if ($this->voip_enabled) {
			return $this->belongsTo('Modules\ProvVoip\Entities\PhoneTariff', 'next_voip_id');
		}
		else {
			return null;
		}
	}

	/**
	 * Get relation to envia orders.
	 *
	 * @author Patrick Reichel
	 */
	protected function _envia_orders() {

		if (!\PPModule::is_active('provvoipenvia')) {
			throw new \LogicException(__METHOD__.' only callable if module ProvVoipEnvia as active');
		}

		return $this->hasMany('Modules\ProvVoipEnvia\Entities\EnviaOrder')->where('ordertype', 'NOT LIKE', 'order/create_attachment');

	}

	public function items()
	{
		if (\PPModule::is_active('billingbase'))
			return $this->hasMany('Modules\BillingBase\Entities\Item');
		return null;
	}

	public function items_sorted_by_valid_from_desc()
	{
		if (\PPModule::is_active('billingbase'))
			return $this->hasMany('Modules\BillingBase\Entities\Item')->orderBy('valid_from', 'desc');
		return null;
	}

	public function sepamandates()
	{
		if (\PPModule::is_active('billingbase'))
			return $this->hasMany('Modules\BillingBase\Entities\SepaMandate');
		return null;
	}

	public function costcenter()
	{
		if (\PPModule::is_active('billingbase'))
			return $this->belongsTo('Modules\BillingBase\Entities\CostCenter', 'costcenter_id');
		return null;
	}

	public function salesman()
	{
		if (\PPModule::is_active('billingbase'))
			return $this->belongsTo('Modules\BillingBase\Entities\Salesman');
		return null;
	}

	public function cccauthuser()
	{
		if (\PPModule::is_active('ccc'))
			return $this->hasOne('Modules\Ccc\Entities\CccAuthuser');
		return null;
	}


    /**
     * Generate use a new user login password
     * This does not save the involved model
     */
    public function generate_password($length=10)
    {
        $this->password = \Acme\php\Password::generate_password($length);
    }


	/**
	 * Helper to get the contract number.
	 * As there is no hard coded contract number in database we have to use this mapper. The semantic meaning of number…number4 can be defined in global configuration.
	 *
	 * @author Patrick Reichel
	 *
	 * @todo: in this first step the relation is hardcoded within the function. Later on we have to check the mapping against the configuration.
	 * @return current contract number
	 */
	public function contract_number() {

		$contract_number = $this->number;

		return $contract_number;

	}


	/**
	 * Helper to get the customer number.
	 * As there is no hard coded customer number in database we have to use this mapper. The semantic meaning of number…number4 can be defined in global configuration.
	 *
	 * @author Patrick Reichel
	 *
	 * @todo: in this first step the relation is hardcoded within the function. Later on we have to check the mapping against the configuration.
	 * @return current customer number
	 */
	public function customer_number() {

		if (boolval($this->number3)) {
			$customer_number = $this->number3;
		}
		else {
			$customer_number = $this->number;
		}

		return $customer_number;

	}


	/*
	 * Convert a 'YYYY-MM-DD' to Carbon Time Object
	 *
	 * We use this to convert a SQL start / end contract date to a carbon
	 * object. Carbon Time Objects can be compared with lt(), gt(), ..
	 *
	 * TODO: move this stuff to extensions
	 */
	private function _date_to_carbon ($date)
	{
		// createFromFormat crashes if nothing given
		if (!boolval($date)) {
			return null;
		}

		return \Carbon\Carbon::createFromFormat('Y-m-d', $date);
	}


	/*
	 * Check if Carbon date is null
	 *
	 * NOTE: This is a little bit of pain, but it works.
	 *       But string compare to '0000' is even more pain and
	 *       also other tutorials point this out to be a freaky problem.
	 * See: http://stackoverflow.com/questions/25959324/comparing-null-date-carbon-object-in-laravel-4-blade-templates
	 *
	 * TODO: move this stuff to extensions
	 *
	 * Note by Patrick: null dates can either be Carbons or strings with “0000-00-00”/“0000-00-00 00:00:00” or “NULL”
	 */
	private function _date_null ($date)
	{
		if (!boolval($date))
			return True;

		if (is_string($date)) {
			return (\Str::startswith($date, '0000'));
		}

		// Carbon object
		return !($date->year > 1900);
	}


	/**
	 * The Daily Scheduling Function
	 *
	 * Tasks:
	 *  1. Check if $this contract end date is expired -> disable network_access
	 *  2. Check if $this is a new contract and activate it -> enable network_access
	 *  3. Change QoS id and Voip id if actual valid (billing-) tariff changes
	 *
	 * @TODO try to avoid the use of multiple saves, instead use one save at the end
	 *
	 * @return none
	 * @author Torsten Schmidt, Nino Ryschawy, Patrick Reichel
	 */
	public function daily_conversion()
	{
		\Log::Debug('Starting daily conversion for contract '.$this->number, [$this->id]);

		if (!\PPModule::is_active('Billingbase')) {

			$this->_update_network_access_from_contract();
		}
		else {

			// Task 3: Check and possibly update item's valid_from and valid_to dates
			$this->_update_inet_voip_dates();

			// Task 4: Check and possibly change product related data (qos_id, voip, purchase_tariff)
			// for this contract depending on the start/end times of its items
			$this->update_product_related_data($this->items);

			// NOTE: Keep this order! - update network access after all adaptions are made
			// Task 1 & 2 included
			$this->_update_network_access_from_items();


			// commented out by par for reference ⇒ if all is running this can savely be removed
			/* $qos_id = ($tariff = $this->get_valid_tariff('Internet')) ? $tariff->product->qos_id : 0; */

			/* if ($this->qos_id != $qos_id) */
			/* { */
			/* 	\Log::Info("daily: contract: changed qos_id (tariff) to $qos_id for Contract ".$this->number, [$this->id]); */
			/* 	$this->qos_id = $qos_id; */
			/* 	$this->save(); */
			/* 	$this->push_to_modems(); */
			/* } */

			/* $voip_id = ($tariff = $this->get_valid_tariff('Voip')) ? $tariff->product->voip_sales_tariff_id : 0; */

			/* if ($this->voip_id != $voip_id) */
			/* { */
			/* 	\Log::Info("daily: contract: changed voip_id (tariff) to $voip_id for Contract ".$this->number, [$this->id]); */
			/* 	$this->voip_id = $voip_id; */
			/* 	$this->save(); */
			/* } */
		}
	}


	/**
	 * This enables/disables network_access according to start and end date of the contract.
	 * Used if billing is disabled.
	 *
	 * @author Torsten Schmidt
	 */
	protected function _update_network_access_from_contract() {

		$now   = \Carbon\Carbon::now();

		// Task 1: Check if $this contract end date is expired -> disable network_access
		if ($this->contract_end) {
			$end  = $this->_date_to_carbon($this->contract_end);
			if ($end->lt($now) && !$this->_date_null($end) && $this->network_access == 1)
			{
				\Log::Info('daily: contract: disable based on ending contract date for '.$this->id);

				$this->network_access = 0;
				$this->save();
			}
		}

		// Task 2: Check if $this is a new contract and activate it -> enable network_access
		// Note: to avoid enabling contracts which are disabled manually, we also check if
		//       maximum time beetween start contract and now() is not older than 1 day.
		// Note: This requires the daily scheduling to run well
		//       Otherwise the contracts must be enabled manually
		// TODO: give them a good testing
		if ($this->contract_start) {
			$start = $this->_date_to_carbon($this->contract_start);
			if ($start->lt($now) && !$this->_date_null($start) && $start->diff($now)->days <= 1 && $this->network_access == 0)
			{
				\Log::Info('daily: contract: enable contract based on start contract date for '.$this->id);

				$this->network_access = 1;
				$this->save();
			}
		}
	}


	/**
	 * This enables/disables network_access based on existence of currently active items of types Internet and Voip
	 *
	 * Check also if contract is outdated
	 *
	 * @author Patrick Reichel
	 */
	protected function _update_network_access_from_items() {

		$contract_changed = False;

		$active_tariff_info_internet = $this->_get_valid_tariff_item_and_count('Internet');
		$active_tariff_info_voip = $this->_get_valid_tariff_item_and_count('Voip');

		$active_count_internet = $active_tariff_info_internet['count'];
		$active_count_voip = $active_tariff_info_voip['count'];
		$active_count_sum = $active_count_internet + $active_count_voip;

		$active_item_internet = $active_tariff_info_internet['item'];
		$active_item_voip = $active_tariff_info_voip['item'];

		if ($active_count_sum == 0 || !$this->check_validity('now')) {
			// if there is no active item of type internet or voip or contract is outdated: disable network_access (if not already done)
			if (boolval($this->network_access)) {
				$this->network_access = 0;
				$contract_changed = True;
				\Log::Info('daily: contract: disabling network_access based on active internet/voip items for contract '.$this->id);
			}
		}
		else {
			// changes are only required if not active
			if (!boolval($this->network_access)) {

				// then we compare the startdate of the most current active internet/voip type item with today
				// if the difference between the two dates is to big we assume that access has been disabled manually – we don't change the state in this case
				// this follows the philosophy introduced by Torsten within method _update_network_access_from_contract (e.g. lack of payment)
				$now = \Carbon\Carbon::now();
				
				$starts = array();
				if ($active_item_internet) {
					array_push($starts, $this->_date_to_carbon($active_item_internet->valid_from));
				}
				if ($active_item_voip) {
					array_push($starts, $this->_date_to_carbon($active_item_voip->valid_from));
				}
				$start = max($starts);

				if (($start->diff($now)->days) <= 1) {
					$this->network_access = 1;
					$contract_changed = True;
					\Log::Info('daily: contract: enabling network_access based on active internet/voip items for contract '.$this->id);
				}
			}

		}

		if ($contract_changed) {
			$this->save();
		}

	}


	/**
	 * This helper updates dates for Internet & Voip items on this contract under the following conditions:
	 *	- valid_from:
	 *		- valid_from_fixed is false
	 *		- valid_from is before tomorrow
	 *		- if both are true: set to tomorrow
	 *	- valid_to:
	 *		- valid_to_fixed is false
	 *		- valid_to is before today
	 *		- if both are true: set to today
	 *
	 *	This way we ensure:
	 *		- items with not fixed end dates are valid today
	 *		- items with not fixed start dates are not active
	 *
	 * Attention: Have in mind that changing item dates also fires in ItemObserver::updating()
	 * which for example possibly changes contracts (voip_id, purchase_tariff) etc.!
	 *
	 * @author Patrick Reichel
	 *
	 * @return null
	 */
	protected function _update_inet_voip_dates() {

		// items only exist if Billingbase is enabled
		if (!\PPModule::is_active('Billingbase')) {
			return;
		}

		// get tomorrow and today as Carbon objects – so they can directly be compared to the dates at items
		$tomorrow = \Carbon\Carbon::tomorrow();
		$today = \Carbon\Carbon::today();

		// check for each item on contract
		// attention: update youngest valid_from items first (to avoid problems in relation with
		// ItemObserver::update() which else set valid_to smaller than valid_from in some cases)!
		// and to avoid “Multipe valid tariffs active” warning
		foreach ($this->items_sorted_by_valid_from_desc as $item) {

			$type = isset($item->product) ? $item->product->type : '';

			if (!in_array($type, ['Voip', 'Internet']))
				continue;

			// flag to decide if item has to be saved at the end of the loop
			$item_changed = False;

			// if the startdate is fixed: ignore
			if (!boolval($item->valid_from_fixed)) {
				// set to tomorrow if there is a start date but this is less then tomorrow
				if (!$this->_date_null($item->valid_from)) {
					$from = $this->_date_to_carbon($item->valid_from);
					if ($from->lt($tomorrow)) {
						$new_date = $tomorrow->toDateString();
						$item->valid_from = $new_date;
						$item_changed = True;
						\Log::Info("contract: changing item ".$item->id." valid_from to ".$new_date." for Contract ".$this->number, [$this->id]);
					}
				}
			}

			// if the enddate is fixed: ignore
			if (!boolval($item->valid_to_fixed)) {
				// set to today if there is an end date less than today
				if (!$this->_date_null($item->valid_to)) {
					$to = $this->_date_to_carbon($item->valid_to);
				    if ($to->lt($today)) {
						$new_date = $today->toDateString();
						$item->valid_to = $new_date;
						$item_changed = True;
						\Log::Info("contract: changing item ".$item->id." valid_to to ".$new_date." for Contract ".$this->number, [$this->id]);
					}
				}
			}

			// finally: save the change(s)
			if ($item_changed) {
				$item->save();
			}
		}

	}


	/**
	 * The Monthly Scheduling Function
	 *
	 * Tasks:
	 *  1. monthly QOS transition / change
	 *  2. monthly VOIP transition / change
	 *
	 * “next*” values are initialized with 0 on ItemObserver::creating() and
	 * possibly overwritten by ItemObserver::updating() (which can also be executed by daily conversion)
	 *
	 * So the daily conversion also can change these values – but this is only triggered on updating an item.
	 * To write long term changes to DB we have to check all items in this monthly conversion.
	 *
	 * @return none
	 * @author Torsten Schmidt, Patrick Reichel
	 */
	public function monthly_conversion()
	{
		// with billing module -> done by daily conversion
		if (\PPModule::is_active('Billingbase'))
			return;

		$contract_changed = False;

		// Tariff: monthly Tariff change – "Tarifwechsel"
		if (
			($this->next_qos_id > 0)
			&&
			($this->qos_id != $this->next_qos_id)
		) {
			\Log::Info('monthly: contract: change Tariff for '.$this->id.' from '.$this->qos_id.' to '.$this->next_qos_id);
			$this->qos_id = $this->next_qos_id;
			$this->next_qos_id = 0;
			$contract_changed = True;
		}

		// VOIP: monthly VOIP change
		if ($this->next_voip_id > 0)
		{
			\Log::Info('monthly: contract: change VOIP-ID for '.$this->id.' from '.$this->voip_id.' to '.$this->next_voip_id);
			$this->voip_id = $this->next_voip_id;
			$this->next_voip_id = 0;
			$contract_changed = True;
		}

		if ($contract_changed) {
			$this->save();
		}

	}


	/**
	 * Returns last started actual valid tariff assigned to this contract.
	 *
	 * @author Patrick Reichel
	 *
	 * @param $type product type as string (e.g. 'Internet', 'Voip', etc.)
	 *
	 * @return object 	item
	 */
	public function get_valid_tariff($type) {
		return $this->_get_valid_tariff_item_and_count($type)['item'];
	}


	/**
	 * Returns number of currently active items of given type assigned to this contract.
	 *
	 * Use this for checks – a value bigger than 1 should be an error and result in special action!
	 *
	 * @author Patrick Reichel
	 *
	 * @param $type product type as string (e.g. 'Internet', 'Voip', etc.)
	 *
	 * @return number of active items for given type and this contract
	 */
	public function get_valid_tariff_count($type) {
		return $this->_get_valid_tariff_item_and_count($type)['count'];
	}


	/**
	 * Return last started actual valid tariff and number of active tariffs of given type for this contract.
	 *
	 * @author Nino Ryschawy, Patrick Reichel
	 *
	 * @param $type product type as string (e.g. 'Internet', 'Voip', etc.)
	 *
	 * @return array containing two values:
	 *	'item' => the last startet tariff (item object)
	 *	'count' => integer
	 */
	protected function _get_valid_tariff_item_and_count($type)
	{
		if (!\PPModule::is_active('Billingbase'))
			return ['item' => null, 'count' => 0];

		$prod_ids = \Modules\BillingBase\Entities\Product::get_product_ids($type);
		if (!$prod_ids)
			return ['item' => null, 'count' => 0];

		$last 	= 0;
		$tariff = null;			// item
		$count = 0;
// dd($prod_ids, $this->items);
		foreach ($this->items as $item)
		{
			if (in_array($item->product->id, $prod_ids) && $item->check_validity('now'))
			{
				$count++;

				$start = $item->get_start_time();
				if ($start > $last)
				{
					$tariff = $item;
					$last   = $start;
				}
			}
		}

		// This is an error! There should only be one active item per type and contract
		if ($count > 1) {
			\Log::Error('There are '.$count.' active items of product type '.$type.' assigned to contract '.$this->number.' ['.$this->id.'].');
		}

		return ['item' => $tariff, 'count' => $count];
	}


	/**
	 * Wrapper to call updater helper methods depending on product type of each given item
	 * The called methods write product related data (qos, voip_id, etc.) from items to contract
	 * depending on item's valid_* dates.
	 * So the data is available if billing is deactivated; also ProvVoipEnvia uses this data directly
	 * (instead of extracting from items).
	 *
	 * This is called by ItemObserver::updating() (also indirectly by daily conversion) for currently
	 * updated items and also by monthly conversion for all items on contract
	 *
	 * @author Patrick Reichel
	 *
	 * @param $items iterable (array, Collection) containing items
	 *
	 * @return null
	 */
	public function update_product_related_data($items) {

		foreach ($items as $item) {

			// a given item can be null – check and ignore
			if (!$item) {
				continue;
			}

			$type = isset($item->product) ? $item->product->type : '';
			// process only particular product types
			if (!in_array($type, ['Voip', 'Internet'])) {
				continue;
			}

			// check which month is affected by the currently investigated item
			if (
				// check if information is current
				// this is the case for currently active items:
				//	- latest possible startday is today
				//	- closest possible endday is today
				// there should only be one of each type
				($item->valid_from <= date('Y-m-d')) &&
				(
					$this->_date_null($item->valid_to) ||
					($item->valid_to >= date('Y-m-d'))
				)
			) {

				// check if there is more than one active item for given type ⇒ this is an error
				// this can happen if one fixes thè start date of one and forgets to fix the end date
				// of an other item
				$valid_tariff_info = $this->_get_valid_tariff_item_and_count($type);
				if ($valid_tariff_info['count'] > 1) {
					// this should never occur!!
					if ($valid_tariff_info['item']->id != $item->id) {
						\Log::Warning('Using newer item '.$valid_tariff_info['item']->id.' instead of '.$item->id.' to update current data on contract '.$this->number.' ['.$this->id.'].');
					}
					$this->_update_product_related_current_data($valid_tariff_info['item']);

				}
				else {
					// default case
					$this->_update_product_related_current_data($item);
				}
			}
			// check if information is for the future
			// this should be save because there is max. one of each type allowed
			// but if there is more than one: no problem – in worst case we overwrite next_* values
			// multiple times
			elseif ($item->valid_from > date('Y-m-d')) {
				$this->_update_product_related_future_data($item);
			}
			else {
				// items finished before today don't update contracts!
				continue;
			}

		}
	}


	/**
	 * Check for (and possibly perform) product related changes in contract for the current month
	 *
	 * @author Patrick Reichel
	 *
	 * @param $item to be analyzed
	 *
	 * @return null
	 */
	protected function _update_product_related_current_data($item) {

		$contract_changed = False;

		if ($item->product->type == 'Voip') {

			// check if there are changes in state for voip_id and purchase_tariff
			if ($this->voip_id != $item->product->voip_sales_tariff_id) {
				$this->voip_id = $item->product->voip_sales_tariff_id;
				$contract_changed = True;
				\Log::Info("contract: changing voip_id to ".$this->voip_id." for contract ".$this->number, [$this->id]);
			}
			if ($this->purchase_tariff != $item->product->voip_purchase_tariff_id) {
				$this->purchase_tariff = $item->product->voip_purchase_tariff_id;
				$contract_changed = True;
				\Log::Info("contract: changing purchase_tariff to ".$this->purchase_tariff." for contract ".$this->number, [$this->id]);
			}

			if ($contract_changed) {
				$this->save();
			}
		}

		if ($item->product->type == 'Internet') {

			if ($this->qos_id != $item->product->qos_id) {
				$this->qos_id = $item->product->qos_id;
				$contract_changed = True;
				\Log::Info("contract: changing  qos_id to ".$this->qos_id." for contract ".$this->number, [$this->id]);
			}
		}

		if ($contract_changed) {
			$this->save();
		}
	}


	/**
	 * Check for (and possibly perform) product related changes in contract for the next month
	 *
	 * @author Patrick Reichel
	 *
	 * @param $item to be analyzed
	 *
	 * @return null
	 */
	protected function _update_product_related_future_data($item) {

		$contract_changed = False;

		if ($item->product->type == 'Voip') {

			// check if there are changes in state for voip_id and purchase_tariff
			if ($this->next_voip_id != $item->product->voip_sales_tariff_id) {
				$this->next_voip_id = $item->product->voip_sales_tariff_id;
				$contract_changed = True;
				\Log::Info("contract: changing next_voip_id to ".$this->next_voip_id." for contract ".$this->number, [$this->id]);
			}
			if ($this->next_purchase_tariff != $item->product->voip_purchase_tariff_id) {
				$this->next_purchase_tariff = $item->product->voip_purchase_tariff_id;
				$contract_changed = True;
				\Log::Info("contract: changing next_purchase_tariff to ".$this->next_purchase_tariff." for contract ".$this->number, [$this->id]);
			}
		}

		if ($item->product->type == 'Internet') {

			if ($this->next_qos_id != $item->product->qos_id) {
				$this->next_qos_id = $item->product->qos_id;
				$contract_changed = True;
				\Log::Info("contract: changing next_qos_id to ".$this->next_qos_id." for contract ".$this->number, [$this->id]);
			}
		}

		if ($contract_changed) {
			$this->save();
		}
	}


	/*
	 * Push all settings from Contract layer to the related child Modems (for $this)
	 * This includes: network_access, qos_id
	 *
	 * Note: We call this function from Observer context so a change of the explained
	 *       fields will push this changes to the child Modems
	 * Note: This allows only 1 tariff qos_id for all modems
	 *
	 * @return: none
	 * @author: Torsten Schmidt
	 */
	public function push_to_modems()
	{
		// TODO: Speed-up: Could this be done with a single eloquent update statement ?
		//       Note: This requires to use the Eloquent Context to run all Observers
		//       an to rebuild and restart the involved modems
		foreach ($this->modems as $modem)
		{
			$modem->network_access = $this->network_access;
			$modem->qos_id = $this->qos_id;
			$modem->save();
		}
	}


	/**
	 * Helper to map database numberX fields to description
	 *
	 * @author Patrick Reichel
	 */
	public function get_column_description($col_name) {

		// later use global config to get mapping
		$mappings = [
			'number' => 'Contract number',
			'number2' => 'Contract number legacy',
			'number3' => 'Customer number',
			'number4' => 'Customer number legacy',
		];

		return $mappings[$col_name];
	}



	/**
	 * BOOT:
	 * - init observer
	 */
	public static function boot()
	{
		parent::boot();

		Contract::observe(new ContractObserver);
	}


	/**
	 * Returns start time of item - Note: contract_start field has higher priority than created_at
	 *
	 * @return integer 		time in seconds after 1970
	 */
	public function get_start_time()
	{
		$date = $this->contract_start && $this->contract_start != '0000-00-00' ? $this->contract_start : $this->created_at->toDateString();
		return strtotime($date);
	}


	/**
	 * Returns start time of item - Note: contract_start field has higher priority than created_at
	 *
	 * @return integer 		time in seconds after 1970
	 */
	public function get_end_time()
	{
		return $this->contract_end && $this->contract_end != '0000-00-00' ? strtotime($this->contract_end) : null;
	}


	/**
	 * Returns valid sepa mandate for specific timespan
	 *
	 * @param String 	Timespan - LAST (!!) 'year'/'month' or 'now
	 * @return Object 	Sepa Mandate
	 *
	 * @author Nino Ryschawy
	 */
	public function get_valid_mandate($timespan = 'now')
	{
		$mandate = null;
		$last 	 = 0;

		foreach ($this->sepamandates as $m)
		{
			if (!is_object($m) || !$m->check_validity($timespan))
				continue;

			if ($mandate)
				\Log::warning("Multiple valid Sepa Mandates active for Contract ".$this->number, [$this->id]);

			$start = $m->get_start_time();

			if ($start > $last)
			{
				$mandate = $m;
				$last   = $start;
			}

		}

		return $mandate;
	}


}


/**
 * Observer Class
 *
 * can handle   'creating', 'created', 'updating', 'updated',
 *              'deleting', 'deleted', 'saving', 'saved',
 *              'restoring', 'restored',
 */
class ContractObserver
{

	// Start contract numbers from 10000 - TODO: move to global config or remove this after creating number cycle MVC
	protected $num = 490000;

	public function creating($contract)
	{
		// Note: this is only needed when Billing Module is not active - TODO: proof with future static function
		$contract->sepa_iban = strtoupper($contract->sepa_iban);
		$contract->sepa_bic  = strtoupper($contract->sepa_bic);
	}


	public function created($contract)
	{
		// Note: this only works here because id is not yet assigned in creating function
		$contract->number = $contract->number ? $contract->number : $contract->id - $this->num;

		$contract->save();     			// forces to call the updated method of the observer
		$contract->push_to_modems(); 	// should not run, because a new added contract can not have modems..
	}

	public function updating($contract)
	{
		$contract->number = $contract->number ? $contract->number : $contract->id - $this->num;

		$contract->sepa_iban = strtoupper($contract->sepa_iban);
		$contract->sepa_bic  = strtoupper($contract->sepa_bic);
	}

	public function updated ($contract)
	{
		$contract->push_to_modems();
	}

	public function saved ($contract)
	{
		$contract->push_to_modems();
	}
}


/**
 * Base updater for all data that is related to orders and phonenumbers
 *
 * @author Patrick Reichel
 */
abstract class VoipRelatedDataUpdater {

	// the modules that have to be active to instantiate
	// set to empty array if no modules are needed
	protected $modules_to_be_active = ['OverloadThisByTheNeededModules'];

	// Helper flag; set to true if something related to given contract has to be updated
	protected $has_to_be_updated = True;


	/**
	 * Constructor
	 *
	 * @author Patrick Reichel
	 */
	public function __construct($contract_id) {

		if (!$this->_check_modules()) {
			throw new \RuntimeException('Cannot use class '.__CLASS__.' because at least one of the following modules is not active: '.implode(', ', $this->modules_to_be_active));
		}

		$this->contract = Contract::findOrFail($contract_id);
	}


	/**
	 * Check if all needed modules are active
	 *
	 * @author Patrick Reichel
	 */
	protected function _check_modules() {

		foreach ($this->modules_to_be_active as $module) {
			if (!\PPModule::is_active($module)) {
				return false;
			}
		}

		return true;
	}

}

/**
 * Updater using EnviaOrders as data base.
 *
 * @author Patrick Reichel
 */
class VoipRelatedDataUpdaterByEnvia extends VoipRelatedDataUpdater {

	protected $modules_to_be_active = ['ProvVoipEnvia'];

	/**
	 * Constructor
	 *
	 * @author Patrick Reichel
	 */
	public function __construct($contract_id) {

		parent::__construct($contract_id);

		/* dd(__FILE__, __LINE__, $this->contract); */
	}
}