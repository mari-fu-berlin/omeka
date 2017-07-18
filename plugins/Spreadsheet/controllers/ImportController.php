<?php
/**
 * Spreadsheet (import)
 * @copyright Copyright 2016 Viktor Grandgeorg
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

class Spreadsheet_ImportController extends Omeka_Controller_AbstractActionController
{
    public function init()
    {
        $this->_helper->db->setDefaultModelName('SpreadsheetImport');
    }

    public function browseAction()
    {
        $this->_helper->redirector('browse', 'index');
    }

    public function indexAction()
    {
        Locale::setDefault('de');
        $translator = new Zend_Translate(
            array(
                'adapter' => 'array',
                'content' => dirname(__FILE__) . '/../vendor/ZF/resources/languages',
                'locale'  => 'de',
                'scan' => Zend_Translate::LOCALE_DIRECTORY
            )
        );
        Zend_Validate_Abstract::setDefaultTranslator($translator);

        $elementFile = new Zend_Form_Element_File('spreadsheetImportFile');
        $elementFile
            ->setTranslator($translator)
            ->setLabel('Excel Datei hochladen')
            ->setDestination(realpath(dirname(__FILE__) . '/../import/temp'))
            ->addValidator('Count', false, 1)
            ->addValidator('Size', false, (102400 * 20))
            ->addValidator('Extension', false, 'xlsx')
        ;

        $elementItemType = new Zend_Form_Element_Select('spreadsheetImportItemType');
        $elementItemType
            ->setTranslator($translator)
            ->setLabel(__('Objekttyp'))
            ->setDescription(__('Wählen Sie den Omeka Objekttyp, für den Sie die Daten importieren möchten, aus.'))
            ->setMultiOptions(get_table_options('ItemType'))
            ->setRequired(true)
        ;

        $form = new Zend_Form();
        $form
            ->setTranslator($translator)
            ->setMethod('post')
            ->setAttrib('enctype', 'multipart/form-data')
            ->addElement($elementItemType)
            ->addElement($elementFile)
            ->addElement(
                'submit',
                'submit',
                array(
                    'label' => __('Hochladen'),
                    'class' => 'submit big green button'
                )
            )
        ;

        $this->view->form = $form;

        if ($this->getRequest()->isPost()) {
            if (!$form->isValid($this->getRequest()->getPost())) {
                $this->_helper->flashMessenger(__('Validierungs Fehler! Siehe Fehler bei den Eingaben.'), 'error');
            } else {
                if (!$form->spreadsheetImportFile->receive()) {
                    $this->_helper->flashMessenger(__('Keine Datei hochgeladen?.'), 'error');
                } else {
                    if ($form->spreadsheetImportFile->isUploaded()) {

                        $filename = $form->spreadsheetImportFile->getFileName();
                        // $fileType = $form->spreadsheetImportFile->getMimeType();
                        $newFile = str_replace(array('.', '.'), '', microtime(true)) . '.xlsx';
                        $newFilename = realpath(dirname(__FILE__) . '/../import') . '/' . $newFile;
                        rename($filename, $newFilename);
                        $this->_helper->redirector('import', null, null, array(
                            'current_spreadsheet' => urlencode($newFile),
                            'item_type' => $form->spreadsheetImportItemType->getValue()
                        ));
                        // var_dump($fileName);
                        // var_dump(realpath(dirname(__FILE__) . '/../import') . '/' . str_replace(array('.', '.'), '', microtime(true)) . '.xlsx');
                        // var_dump($fileType);
                    } else {
                        $this->_helper->flashMessenger(__('Keine Datei hochgeladen?'), 'error');
                    }
                }
            }
        }
    }

    public function importByShemeAction()
    {

        $params = $this->getRequest()->getParams();

        if (!isset($params['id']) || empty($params['id'])) {
            $this->_helper->flashMessenger(__('Es wurde kein Importschema gewählt.'), 'error');
            $this->_helper->redirector('browse', 'index');
            return;
        }

        $spreadsheetImport = new SpreadsheetImport;
        $config = $spreadsheetImport->getTable()->find($params['id']);

        if (!property_exists($config, 'id') || !isset($config->id) || empty($config->id)) {
            $this->_helper->flashMessenger(__('Importschema nicht gefunden.'), 'error');
            $this->_helper->redirector('browse', 'index');
            return;
        }

        Locale::setDefault('de');
        $translator = new Zend_Translate(
            array(
                'adapter' => 'array',
                'content' => dirname(__FILE__) . '/../vendor/ZF/resources/languages',
                'locale'  => 'de',
                'scan' => Zend_Translate::LOCALE_DIRECTORY
            )
        );
        Zend_Validate_Abstract::setDefaultTranslator($translator);

        $elementFile = new Zend_Form_Element_File('spreadsheetImportFile');
        $elementFile
            ->setTranslator($translator)
            ->setLabel('Excel Datei hochladen')
            ->setDestination(realpath(dirname(__FILE__) . '/../import/temp'))
            ->addValidator('Count', false, 1)
            ->addValidator('Size', false, (102400 * 20))
            ->addValidator('Extension', false, 'xlsx')
        ;

        $form = new Zend_Form();
        $form
            ->setTranslator($translator)
            ->setMethod('post')
            ->setAttrib('enctype', 'multipart/form-data')
            ->addElement($elementFile)
            ->addElement('hidden', 'import_sheme_id')
            ->addElement(
                'submit',
                'submit',
                array(
                    'label' => __('Hochladen'),
                    'class' => 'submit big green button'
                )
            )
        ;

        $form->getElement('import_sheme_id')->setValue($config->id);

        $this->view->form = $form;

        if ($this->getRequest()->isPost()) {

            $post = $this->getRequest()->getPost();
            if (isset($post['import_sheme_id'])) {
                if (!$form->isValid($post)) {
                    $this->_helper->flashMessenger(__('Validierungs Fehler! Siehe Fehler bei den Eingaben.'), 'error');
                } else {
                    if (!$form->spreadsheetImportFile->receive()) {
                        $this->_helper->flashMessenger(__('Keine Datei hochgeladen?.'), 'error');
                    } else {
                        if ($form->spreadsheetImportFile->isUploaded()) {

                            $filename = $form->spreadsheetImportFile->getFileName();
                            $newFile = str_replace(array('.', '.'), '', microtime(true)) . '.xlsx';
                            $newFilename = realpath(dirname(__FILE__) . '/../import') . '/' . $newFile;
                            rename($filename, $newFilename);
                            $settings = unserialize($config->options);
                            $settings['file'] = $newFile;
                            $this->importXls($settings);

                            $this->_helper->flashMessenger(__('Datei wurde erfolgreich importiert.'), 'success');
                            $this->_helper->redirector('browse', 'index');
                            return;

                        } else {
                            $this->_helper->flashMessenger(__('Keine Datei hochgeladen?'), 'error');
                        }
                    }
                }
            }
        }
    }

    protected function importXls($data)
    {
        // var_dump($data);
        $user = current_user();
        $file = realpath(dirname(__FILE__) . '/../import') . '/' . urldecode($data['file']);

        if (is_file($file) && is_readable($file)) {

            // Get Spreadsheet data
            require_once dirname(__FILE__) . '/../vendor/PHPExcel/Classes/PHPExcel/IOFactory.php';
            $inputFileType = 'Excel2007';
            $objReader = PHPExcel_IOFactory::createReader($inputFileType);
            $objReader->setReadDataOnly(true);
            $objPHPExcel = $objReader->load($file);
            $worksheet = $objPHPExcel->getSheet(0);
            // var_dump(get_class_methods($worksheet));
            // return;

            foreach ($worksheet->getRowIterator() as $row) {
                if ($row->getRowIndex() != 1) {
                    // var_dump(get_class_methods($row));
                    // var_dump($row->getRowIndex());
                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(false);

                    $dcTitle = null;
                    if ($data['main_dctitle'] != '0' && $worksheet->cellExists($data['main_dctitle'] . $row->getRowIndex())) {
                        $dcTitle = $worksheet->getCell($data['main_dctitle'] . $row->getRowIndex())->getValue();
                    }
                    if (!isset($dcTitle) || empty($dcTitle)) {
                        continue;
                    }

                    $item = new Item;
                    $item->item_type_id = $data['item_type'];
                    $item->collection_id = null;
                    $item->public = 0;
                    $item->owner_id = $user->id;
                    if (isset($data['main_featured']) && !empty($data['main_featured']) &&
                        $worksheet->cellExists($data['main_featured'] . $row->getRowIndex())) {

                        $item->featured = $worksheet->getCell($data['main_featured'] . $row->getRowIndex())->getValue();
                    } else {
                        $item->featured = 0;
                    }

                    $item->save();

                    if (isset($data['main_dctitle']) && !empty($data['main_dctitle'])) {
                        $elementText = new ElementText;
                        $elementText->record_id = $item->id;
                        $elementText->record_type = 'Item';
                        $elementText->element_id = 50;
                        $elementText->text = $text = $worksheet->getCell($data['main_dctitle'] . $row->getRowIndex())->getValue();
                        $elementText->save();
                    }


                    foreach ($data as $elementIdKey => $importDataXlsColKey) {
                        $elementSave = false;
                        if (strstr($elementIdKey, 'itemtype')) {
                            // var_dump($importDataXlsColKey . $row->getRowIndex());
                            // continue;
                            $text = null;
                            if ($importDataXlsColKey !== '0' && $worksheet->cellExists($importDataXlsColKey . $row->getRowIndex())) {
                                $text = $worksheet->getCell($importDataXlsColKey . $row->getRowIndex())->getValue();
                            }
                            if (isset($text)) {
                                $elementText = new ElementText;
                                $elementText->record_id = $item->id;
                                $elementText->record_type = 'Item';
                                $elementText->element_id = substr($elementIdKey, strlen('itemtype_'));
                                $elementText->text = $text;
                                // var_dump($text);
                            }
                        }
                        if (strstr($elementIdKey, 'itemcontenttype')) {
                            if ($importDataXlsColKey == 'html') {
                                $elementText->html = 1;
                            } else {
                                $elementText->html = 0;
                            }
                            $elementSave = true;
                        }
                        if ($elementSave === true) {
                            $elementText->save();
                        }
                    }


                    // $item->item_type_id = $data['item_type'];

                    foreach ($cellIterator as $cell) {
                        if (!is_null($cell) && !is_null($cell->getCalculatedValue())) {
                            $cellCoord = str_replace('1', '', $cell->getCoordinate());
                            // $firstRow[$cellCoord] = $cell->getCalculatedValue() . ' (' . $cellCoord . ')';

                        }
                    }
                }
            }
        }
    }

    public function importAction()
    {
        $params = $this->getRequest()->getParams();
        // var_dump($params);
        // var_dump($_POST);

        if ($this->getRequest()->isPost() && isset($_POST['submit_save_import'])) {
            // var_dump($this->getRequest()->getPost());

            $postData = $this->getRequest()->getPost();

            if ($postData['import_settings'] == 'save' ||
                $postData['import_settings'] == 'save_and_import') {

                $model = new SpreadsheetImport;
                $model->created_by_user_id = current_user()->id;
                $model->modified_by_user_id = current_user()->id;

                $options = $postData;
                unset(
                    $options['admin'],
                    $options['module'],
                    $options['controller'],
                    $options['action'],
                    $options['current_spreadsheet'],
                    $options['import_name'],
                    $options['import_settings'],
                    $options['file'],
                    $options['submit_save_import']
                );
                $options = serialize($options);

                $model->setPostData(array(
                    'name' => $postData['import_name'],
                    'options' => $options
                ));
                $model->save();
            }

            if ($postData['import_settings'] == 'import' ||
                $postData['import_settings'] == 'save_and_import') {

                $this->importXls($postData);
            }

            $this->_helper->flashMessenger(__('Der Import wurde erfolgreich durchgeführt.'), 'success');
            $this->_helper->redirector('browse', 'index');
            return;

        }



        if (isset($params['current_spreadsheet'])) {

            $file = realpath(dirname(__FILE__) . '/../import') . '/' . urldecode($params['current_spreadsheet']);

            if (is_file($file) && is_readable($file)) {

                // Get Spreadsheet data
                require_once dirname(__FILE__) . '/../vendor/PHPExcel/Classes/PHPExcel/IOFactory.php';
                $inputFileType = 'Excel2007';
                $objReader = PHPExcel_IOFactory::createReader($inputFileType);
                $objReader->setReadDataOnly(true);
                $objPHPExcel = $objReader->load($file);
                $worksheet = $objPHPExcel->getSheet(0);
                $firstRow = array(0 => '');
                foreach ($worksheet->getRowIterator() as $row) {
                    if ($row->getRowIndex() == 1) {
                        $cellIterator = $row->getCellIterator();
                        $cellIterator->setIterateOnlyExistingCells(false);
                        foreach ($cellIterator as $cell) {
                            if (!is_null($cell) && !is_null($cell->getCalculatedValue())) {
                                $cellCoord = str_replace('1', '', $cell->getCoordinate());
                                $firstRow[$cellCoord] = $cell->getCalculatedValue() . ' (' . $cellCoord . ')';
                            }
                        }
                    } else {
                        break;
                    }
                }

                $itemTypes = $this->_helper->db->getTable('Element')->findByItemType($params['item_type']);

                // var_dump($firstRow);
                // var_dump(count($itemTypes));

                Locale::setDefault('de');
                $translator = new Zend_Translate(
                    array(
                        'adapter' => 'array',
                        'content' => dirname(__FILE__) . '/../vendor/ZF/resources/languages',
                        'locale'  => 'de',
                        'scan' => Zend_Translate::LOCALE_DIRECTORY
                    )
                );
                Zend_Validate_Abstract::setDefaultTranslator($translator);

                $form = new Zend_Form();

                // settings
                $elementSettings = new Zend_Form_Element_Select('import_settings');
                $elementSettings
                    ->setTranslator($translator)
                    ->setLabel(__('Import Einstellungen'))
                    ->setMultiOptions(array(
                        'import' => 'Nur Objekte importieren',
                        'save_and_import' => 'Importschema speichern und Objekte importieren',
                        'save' => 'Nur Importschema speichern',
                    ))
                ;
                $elementImportTitle = new Zend_Form_Element_Text('import_name');
                $elementImportTitle
                    ->setTranslator($translator)
                    ->setLabel(__('Name des Importschemas'))
                    ->setRequired(true)
                ;
                $form
                    ->addElement($elementImportTitle)
                    ->addElement($elementSettings)
                    ->addDisplayGroup(
                        array('import_name', 'import_settings'),
                        'import_settings_group',
                        array('legend' => __('Einstellungen'))
                    )
                ;


                // Mainfields and DC
                $elementFeatured = new Zend_Form_Element_Select('main_featured');
                $elementFeatured
                    ->setTranslator($translator)
                    ->setLabel(__('featured'))
                    ->setMultiOptions($firstRow)
                ;

                $elementDCTitle = new Zend_Form_Element_Select('main_dctitle');
                $elementDCTitle
                    ->setTranslator($translator)
                    ->setLabel(__('Dublin Core Titel'))
                    ->setMultiOptions($firstRow)
                ;

                $form
                    ->setTranslator($translator)
                    ->setMethod('post')
                    ->addElement('hidden', 'file')
                    ->addElement('hidden', 'item_type')
                    ->addElement($elementFeatured)
                    ->addElement($elementDCTitle)
                ;
                $form->getElement('file')->setValue($params['current_spreadsheet']);
                $form->getElement('item_type')->setValue($params['item_type']);
                $form->addDisplayGroup(
                    array('main_featured', 'main_dctitle'),
                    'main',
                    array('legend' => __('Objektfelder und Dublin Core'))
                );

                // Itemtype fields
                $itemTypeFormElements = array();
                foreach ($itemTypes as $itemType) {

                    $itemTypeFormElements[] = 'itemtype_' . $itemType->id;
                    $itemTypeFormElement = new Zend_Form_Element_Select('itemtype_' . $itemType->id);
                    $itemTypeFormElement
                        ->setTranslator($translator)
                        ->setLabel(__('Metadaten') . ' - ' . $itemType->name)
                        ->setMultiOptions($firstRow)
                    ;

                    $itemTypeFormElements[] = 'itemcontenttype_' . $itemType->id;
                    $itemContentTypeFormElement = new Zend_Form_Element_Select('itemcontenttype_' . $itemType->id);
                    $itemContentTypeFormElement
                        ->setTranslator($translator)
                        ->setLabel(__('Inhaltstyp') . ' - ' . $itemType->name)
                        ->setMultiOptions(array('text' => 'text', 'html' => 'html'))
                    ;

                    $form->addElement($itemTypeFormElement);
                    $form->addElement($itemContentTypeFormElement);
                }
                $form->addDisplayGroup(
                    $itemTypeFormElements,
                    'itemtype_elements',
                    array('legend' => __('Objekttyp - Metadaten'))
                );

                // submit
                $form->addElement(
                        'submit',
                        'submit_save_import',
                        array(
                            'label' => __('Speichern'),
                            'class' => 'submit big green button'
                        )
                    )
                ;

                $this->view->form = $form;

            }
        }
    }

    public function showShemeAction()
    {
        $params = $this->getRequest()->getParams();
        $sheme = null;
        $db = get_db();
        if (isset($params['id'])) {
            $sheme = $db->getTable('SpreadsheetImport')->find($params['id']);
            $this->view->itemTypes = $db->getTable('ItemType')->findAll();
            $this->view->elements = $db->getTable('Element')->findAll();
        }
        $this->view->sheme = $sheme;
    }
}