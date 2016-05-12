<?php

namespace Modules\ProvVoipEnvia\Entities;

// Model not found? execute composer dump-autoload in lara root dir
class EnviaOrderDocument extends \BaseModel {

	// The associated SQL table for this Model
	public $table = 'enviaorderdocument';

	// where to save the uploaded documents (relative to /storage/app)
	public static $document_base_path = "modules/ProvVoipEnvia/EnviaOrderDocuments";

	public static $allowed_mimetypes = array(
		'application/msword',
		'application/pdf',
		'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
		'image/jpeg',
		'image/tif',
	);

	// Add your validation rules here
	public static function rules($id=null) {

		// for validation rule we only need the concrete type (e.g. pdf instead of application/pdf)
		$mimes_short = array();
		foreach (self::$allowed_mimetypes as $mime) {
			array_push($mimes_short, explode('/', $mime)[1]);
		}
		$mimestring = implode(',', $mimes_short);

		return array(
			'document_type' => 'required',
			'document_upload' => 'required|mimes:'.$mimestring,
			'enviaorder_id' => 'required|exists:enviaorder,id',
			'mime_type' => 'required',
		);
	}

	// Don't forget to fill this array
	protected $fillable = [
		'document_type',
		'mime_type',
		'filename',
		'uploaded_order_id',
		'enviaorder_id',
	];

	// Name of View
	public static function view_headline()
	{
		return 'EnviaOrderDocuments';
	}

	// link title in index view
	public function view_index_label()
	{
		return $this->created_at.': '.$this->document_type.' ('.$this->upload_order_id.')';
	}

	// belongs to a modem - see BaseModel for explanation
	public function view_belongs_to ()
	{
		return $this->enviaorder;
	}

	public function enviaorder() {

		return $this->belongsTo('Modules\ProvVoipEnvia\Entities\EnviaOrder', 'enviaorder_id');
	}

}

