<?php namespace Modules\Voipmon\Http\Controllers;

class CdrController extends \BaseController {
	/*
	public function index()
	{
		return view('voipmon::index');
	}
	*/

	public function view_form_fields($model = null)
	{
		if (!$model)
			$model = new Cdr;

		// label has to be the same like column in sql table
		return array(
			//array('form_type' => 'select', 'name' => 'country_code', 'description' => 'Country Code', 'value' => Phonenumber::getPossibleEnumValues('country_code')),
			array('form_type' => 'text', 'name' => 'calldate', 'description' => 'Call Start', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'callend', 'description' => 'Call End', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'duration', 'description' => 'Call Duration/s', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'mos_min_mult10', 'description' => 'min. MOS x 10', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'packet_loss_perc_mult1000', 'description' => 'Packet loss/% x 1000', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'jitter_mult10', 'description' => 'Jitter/ms x 10', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'delay_avg_mult100', 'description' => 'avg. Delay/ms x 100', 'options' => ['readonly'], 'space' => '1'),
			/* monitoring quality indicators caller -> callee */
			array('form_type' => 'text', 'name' => 'caller', 'description' => 'Caller (-> Callee)', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'caller_domain', 'description' => '@', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'a_mos_f1_min_mult10', 'description' => 'min. MOS 50ms x 10', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'a_mos_f2_min_mult10', 'description' => 'min. MOS 200ms x 10', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'a_mos_adapt_min_mult10', 'description' => 'min. MOS adaptive 500ms x 10', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'a_mos_f1_mult10', 'description' => 'avg. MOS 50ms x 10', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'a_mos_f2_mult10', 'description' => 'avg. MOS 200ms x 10', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'a_mos_adapt_mult10', 'description' => 'avg. MOS adaptive 500ms x 10', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'a_received', 'description' => 'Received Packets', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'a_lost', 'description' => 'Lost Packets', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'a_packet_loss_perc_mult1000', 'description' => 'Packet loss/% x 1000', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'a_delay_avg_mult100', 'description' => 'avg. Delay/ms x 100', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'a_avgjitter_mult10', 'description' => 'avg. Jitter/ms x 10', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'a_maxjitter', 'description' => 'max. Jitter/ms', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'a_sl1', 'description' => '1 loss in a row', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'a_sl2', 'description' => '2 loss in a row', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'a_sl3', 'description' => '3 loss in a row', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'a_sl4', 'description' => '4 loss in a row', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'a_sl5', 'description' => '5 loss in a row', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'a_sl6', 'description' => '6 loss in a row', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'a_sl7', 'description' => '7 loss in a row', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'a_sl8', 'description' => '8 loss in a row', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'a_sl9', 'description' => '9 loss in a row', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'a_d50', 'description' => 'PDV 50ms - 70ms', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'a_d70', 'description' => 'PDV 70ms - 90ms', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'a_d90', 'description' => 'PDV 90ms - 120ms', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'a_d120', 'description' => 'PDV 120ms - 150ms', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'a_d150', 'description' => 'PDV 150ms - 200ms', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'a_d200', 'description' => 'PDV 200ms - 300ms', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'a_d300', 'description' => 'PDV >300 ms', 'options' => ['readonly'], 'space' => '1'),
			/* monitoring quality indicators caller -> callee */
			array('form_type' => 'text', 'name' => 'called', 'description' => 'Callee (-> Caller)', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'called_domain', 'description' => '@', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'b_mos_f1_min_mult10', 'description' => 'min. MOS 50ms x 10', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'b_mos_f2_min_mult10', 'description' => 'min. MOS 200ms x 10', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'b_mos_adapt_min_mult10', 'description' => 'min. MOS adaptive 500ms x 10', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'b_mos_f1_mult10', 'description' => 'avg. MOS 50ms x 10', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'b_mos_f2_mult10', 'description' => 'avg. MOS 200ms x 10', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'b_mos_adapt_mult10', 'description' => 'avg. MOS adaptive 500ms x 10', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'b_received', 'description' => 'Received Packets', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'b_lost', 'description' => 'Lost Packets', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'b_packet_loss_perc_mult1000', 'description' => 'Packet loss/% x 1000', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'b_delay_avg_mult100', 'description' => 'avg. Delay/ms x 100', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'b_avgjitter_mult10', 'description' => 'avg. Jitter/ms x 10', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'b_maxjitter', 'description' => 'max. Jitter/ms', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'b_sl1', 'description' => '1 loss in a row', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'b_sl2', 'description' => '2 loss in a row', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'b_sl3', 'description' => '3 loss in a row', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'b_sl4', 'description' => '4 loss in a row', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'b_sl5', 'description' => '5 loss in a row', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'b_sl6', 'description' => '6 loss in a row', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'b_sl7', 'description' => '7 loss in a row', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'b_sl8', 'description' => '8 loss in a row', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'b_sl9', 'description' => '9 loss in a row', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'b_d50', 'description' => 'PDV 50ms - 70ms', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'b_d70', 'description' => 'PDV 70ms - 90ms', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'b_d90', 'description' => 'PDV 90ms - 120ms', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'b_d120', 'description' => 'PDV 120ms - 150ms', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'b_d150', 'description' => 'PDV 150ms - 200ms', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'b_d200', 'description' => 'PDV 200ms - 300ms', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'b_d300', 'description' => 'PDV >300 ms', 'options' => ['readonly'])
		);
	}

}