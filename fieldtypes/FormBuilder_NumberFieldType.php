<?php
namespace Craft;

class FormBuilder_NumberFieldType extends BaseFieldType
{
	//======================================================================
	// Get FieldType Name
	//======================================================================
	public function getName()
	{
		return Craft::t('| FormBuilder | Number');
	}

	//======================================================================
	// Get Settings HTML
	//======================================================================
	public function getSettingsHtml()
	{
		return craft()->templates->render('_components/fieldtypes/Number/settings', array(
			'settings' => $this->getSettings()
		));
	}

	//======================================================================
	// Define Content Attribute
	//======================================================================
	public function defineContentAttribute()
	{
		$attribute = ModelHelper::getNumberAttributeConfig($this->settings->min, $this->settings->max, $this->settings->decimals);
		$attribute['default'] = 0;

		return $attribute;
	}

	//======================================================================
	// Get Input HTML
	//======================================================================
	public function getInputHtml($name, $value)
	{
		$fieldId      = $name->id;
		$required     = $name->required;
		$instructions = $name->instructions;

		$id = craft()->templates->namespaceInputId($fieldId, 'field');

		if ($this->isFresh() && ($value < $this->settings->min || $value > $this->settings->max))
		{
			$value = $this->settings->min;
		}

		craft()->path->setTemplatesPath(craft()->path->getPluginsPath().'formbuilder/templates');
		$html = craft()->templates->render('/_includes/forms/text', array(
			'id'            => $id,
			'instructions'  => $instructions,
			'required'      => $required,
			'name'  				=> $name,
			'value' 				=> craft()->numberFormatter->formatDecimal($value, false),
			'size'  				=> 5
		));
		craft()->path->setTemplatesPath(craft()->path->getTemplatesPath());

		return $html;
	}

	//======================================================================
	// Pre Value From Post
	//======================================================================
	public function prepValueFromPost($data)
	{
		if ($data === '')
		{
			return 0;
		}
		else
		{
			return LocalizationHelper::normalizeNumber($data);
		}
	}

	//======================================================================
	// Define Settings
	//======================================================================
	protected function defineSettings()
	{
		return array(
			'min'      => array(AttributeType::Number, 'default' => 0),
			'max'      => array(AttributeType::Number, 'compare' => '>= min'),
			'decimals' => array(AttributeType::Number, 'default' => 0),
		);
	}
}
