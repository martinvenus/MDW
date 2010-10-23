<?php

/**
 * This file is part of the Nette Framework.
 *
 * Copyright (c) 2004, 2010 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license", and/or
 * GPL license. For more information please see http://nette.org
 * @package Nette\Forms
 */



/**
 * Text box and browse button that allow users to select a file to upload to the server.
 *
 * @author     David Grudl
 */
class NFileUpload extends NFormControl
{

	/**
	 * @param  string  label
	 */
	public function __construct($label = NULL)
	{
		parent::__construct($label);
		$this->control->type = 'file';
	}



	/**
	 * This method will be called when the component (or component's parent)
	 * becomes attached to a monitored object. Do not call this method yourself.
	 * @param  IComponent
	 * @return void
	 */
	protected function attached($form)
	{
		if ($form instanceof NForm) {
			if ($form->getMethod() !== NForm::POST) {
				throw new InvalidStateException('File upload requires method POST.');
			}
			$form->getElementPrototype()->enctype = 'multipart/form-data';
		}
		parent::attached($form);
	}



	/**
	 * Sets control's value.
	 * @param  array|NHttpUploadedFile
	 * @return NFileUpload  provides a fluent interface
	 */
	public function setValue($value)
	{
		if (is_array($value)) {
			$this->value = new NHttpUploadedFile($value);

		} elseif ($value instanceof NHttpUploadedFile) {
			$this->value = $value;

		} else {
			$this->value = new NHttpUploadedFile(NULL);
		}
		return $this;
	}



	/**
	 * Filled validator: has been any file uploaded?
	 * @param  IFormControl
	 * @return bool
	 */
	public static function validateFilled(IFormControl $control)
	{
		$file = $control->getValue();
		return $file instanceof NHttpUploadedFile && $file->isOK();
	}



	/**
	 * FileSize validator: is file size in limit?
	 * @param  NFileUpload
	 * @param  int  file size limit
	 * @return bool
	 */
	public static function validateFileSize(NFileUpload $control, $limit)
	{
		$file = $control->getValue();
		return $file instanceof NHttpUploadedFile && $file->getSize() <= $limit;
	}



	/**
	 * MimeType validator: has file specified mime type?
	 * @param  NFileUpload
	 * @param  array|string  mime type
	 * @return bool
	 */
	public static function validateMimeType(NFileUpload $control, $mimeType)
	{
		$file = $control->getValue();
		if ($file instanceof NHttpUploadedFile) {
			$type = strtolower($file->getContentType());
			$mimeTypes = is_array($mimeType) ? $mimeType : explode(',', $mimeType);
			if (in_array($type, $mimeTypes, TRUE)) {
				return TRUE;
			}
			if (in_array(preg_replace('#/.*#', '/*', $type), $mimeTypes, TRUE)) {
				return TRUE;
			}
		}
		return FALSE;
	}

}
