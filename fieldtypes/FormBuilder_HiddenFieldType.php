<?php
namespace Craft;

class FormBuilder_HiddenFieldType extends BaseFieldType
{
  //======================================================================
  // Get FieldType Name
  //======================================================================
  public function getName()
  {
    return Craft::t('| FormBuilder | Hidden Field');
  }

  //======================================================================
  // Get Settings HTML
  //======================================================================
  public function getSettingsHtml()
  {
    return craft()->templates->render('formbuilder/_components/fieldtypes/HiddenField/settings', array(
      'settings' => $this->getSettings()
    ));
  }

  //======================================================================
  // Get Input HTML
  //======================================================================
  public function getInputHtml($name, $value)
  {
    $fieldId      = $name->id;

    $id = craft()->templates->namespaceInputId($fieldId, 'field'); 

    craft()->path->setTemplatesPath(craft()->path->getPluginsPath().'formbuilder/templates');
    $html = craft()->templates->render('_components/fieldtypes/HiddenField/input', array(
      'id'            => $id,
      'name'          => $name,
      'value'         => $value,
      'settings'      => $this->getSettings()
    ));
    craft()->path->setTemplatesPath(craft()->path->getTemplatesPath());

    return $html;
  }
  
  //======================================================================
  // Define Settings
  //======================================================================
  protected function defineSettings()
  {
    return array(
      'value'   => array(AttributeType::String),
    );
  }

}
