<?php
namespace Craft;

class FormBuilder_EntriesService extends BaseApplicationComponent
{

	//======================================================================
	// Fires 'onBeforeSave' Form Entry
	//======================================================================
	public function onBeforeSave(FormBuilder_FormEntriesEvent $event)
	{
		$this->raiseEvent('onBeforeSave', $event);
	}

	//======================================================================
	// Get All Entries
	//======================================================================
	public function getAllEntries()
	{
		$entries = FormBuilder_EntryRecord::model()->findAll();
		return $entries;
	}

	//======================================================================
	// Get All Forms
	//======================================================================
	public function getAllForms()
	{
		$forms = FormBuilder_FormRecord::model()->findAll();
		return $forms;
	}

	//======================================================================
	// Get Form By Handle Name
	//======================================================================
	public function getFormByHandle($handle)
	{
		$formRecord = FormBuilder_FormRecord::model()->findByAttributes(array(
			'handle' => $handle,
		));

		if (!$formRecord) {	return false; }
		return FormBuilder_FormModel::populateModel($formRecord);
	}

	//======================================================================
	// Get Form Entry By ID
	//======================================================================
	public function getFormEntryById($id)
	{
		return craft()->elements->getElementById($id, 'FormBuilder');
	}

	//======================================================================
	// Save Form Entry
	//======================================================================
	public function saveFormEntry(FormBuilder_EntryModel $entry)
	{
		$entryRecord = new FormBuilder_EntryRecord();

		$entryRecord->formId 	= $entry->formId;
		$entryRecord->title 	= $entry->title;
		$entryRecord->data   	= $entry->data;

		$entryRecord->validate();
		$entry->addErrors($entryRecord->getErrors());

		if (!$entry->hasErrors()) {
			$transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;
			try {
				if (craft()->elements->saveElement($entry))	{
					$entryRecord->id = $entry->id;
					$entryRecord->save(false);

					if ($transaction !== null) { $transaction->commit(); }
					return $entryRecord->id;
				} else { return false; }
			} catch (\Exception $e) {
				if ($transaction !== null) { $transaction->rollback(); }
				throw $e;
			}
			return true;
		}	else { return false; }
	}

	//======================================================================
  // Send Email Notification to Admin
  //======================================================================
	public function sendEmailNotification($form, $message, $html = true, $email = null)
	{	
		$errors = false;
		$email = new EmailModel();
		$emailTo = explode(',', $form->toEmail);

		$email->toEmail		= $form->toEmail;
		$email->replyTo   = $emailTo[0];
		$email->fromName  = craft()->getSiteName() . ' | Submission Notification';
		$email->subject   = $form->subject;
		$email->htmlBody  = $message;

		foreach ($emailTo as $emailAddress) {
			$email->toEmail = $emailAddress;
			if (!craft()->email->sendEmail($email)) {
				$errors = true;
			}
		}
		return $errors ? false : true;
	}

	//======================================================================
  // Send Email Notification to Submitter
  //======================================================================
	public function sendRegistrantEmailNotification($form, $message, $submitterEmail, $html = true, $email = null)
	{
		$errors = false;
		$email = new EmailModel();
		$emailTo = explode(',', $form->toEmail);

		$email->toEmail   = $submitterEmail;
		$email->fromEmail = $emailTo[0];
		$email->replyTo   = $emailTo[0];
		$email->fromName  = craft()->getSiteName() . ' | Submission Notification';
		$email->subject   = $form->subject;
		$email->htmlBody  = $message;

		if (!craft()->email->sendEmail($email)) {
			$errors = true;
		}
		return $errors ? false : true;
	}

	//======================================================================
  // Validate values of a submitted form
  //======================================================================
  public function validateEntry($form, $postData){
  	$fieldLayoutFields = $form->getFieldLayout()->getFields();
  	
  	$errorMessage = [];

  	foreach ($fieldLayoutFields as $key => $fieldLayoutField) {
      $requiredField = $fieldLayoutField->attributes['required'];
  		$fieldId = $fieldLayoutField->fieldId;
      $field = craft()->fields->getFieldById($fieldId);

  		$userValue = (array_key_exists($field->handle, $postData)) ? $postData[$field->handle] : false;          

      if ($requiredField == 1) {
  			$field->required = true;
      }
  		
  		$_processError = function($field, $message) {
        craft()->userSession->setFlash('error', $message);
        return $message;
      };

  		switch ($field->type) {
        case "FormBuilder_PlainText":
        	if ($field->required) {
	        	$text = craft()->request->getPost($field->handle);
	      		if ($text == '') {
	      			$errorMessage[] = $field->name . ' cannot be blank.';
	      	    // $_processError($field, $field->name . " cannot be blank.");
	      		}
        	}
        break;
        case "FormBuilder_Number":
        	if ($field->required) {
	        	$number = craft()->request->getPost($field->handle);
	        	if (!ctype_digit($number)) {
	      			$errorMessage[] = $field->name . ' cannot be blank and needs to contain only numbers.';
	            // $_processError($field, $field->name . " cannot be blank and needs to contain only numbers.");
	        	}
        	}
        break;
        case "FormBuilder_Email":
        	$email = craft()->request->getPost($field->handle);
        	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      			$errorMessage[] = $field->name . ' needs to contain a valid email.';
            // $_processError($field, $field->name . " needs to contain a valid email.");
        	}
        break;
      }
  	}
    return $errorMessage;
  }
}