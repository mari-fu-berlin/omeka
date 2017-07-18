<?php
/**
 * Spreadsheet (export)
 * @copyright Copyright 2017 Viktor Grandgeorg
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

class Spreadsheet_IndexController extends
    Omeka_Controller_AbstractActionController
{
    public function init()
    {
        $this->_helper->db->setDefaultModelName('SpreadsheetExport');
    }


    public function indexAction()
    {
        $this->_helper->redirector('browse');
        return;
    }

    public function browseAction()
    {
        $spreadsheetImport = $this->_helper->db->getTable('SpreadsheetImport');
        $importSchemes = $spreadsheetImport->findAll();
        $this->view->assign(array('spreadsheet_imports' => $importSchemes));
        // var_dump($importSchemes);
        parent::browseAction();
    }

    public function addAction()
    {
        $model = new SpreadsheetExport;
        $model->created_by_user_id = current_user()->id;
        $model->modified_by_user_id = current_user()->id;
        $this->view->form = $this->_getFormOne($model);
        $this->_processForm($model, 'add');
    }

    public function editAction()
    {
        $model = $this->_helper->db->findById();
        $this->view->form = $this->_getFormOne($model);
        $this->_processForm($model, 'edit');
    }

    public function generateAction()
    {
        $model = $this->_helper->db->findById();
        $options = unserialize($model->options);
        // var_dump($options);
        // var_dump($model);
        // var_dump($model->name);
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $itemsDbTbl = $this->_helper->db->getTable('Item');

        // var_dump(get_class_methods($itemsDbTbl));
        // $items = $itemsDbTbl->findBySql('item_type_id = ?', array($options['itemtype']));
        $items = $itemsDbTbl->findBy(array('item_type_id' => $options['itemtype']));

        if (count($items) <= 0) {
            return;
        }

        // var_dump(get_class_methods($items[0]));
        // var_dump($items[0]->getFiles());
        // var_dump($items[0]->getItemType()->name);

        $itemTypeName = $items[0]->getItemType()->name;
        // var_dump($items[0]->toArray());
        // var_dump($items[0]->getItemTypeElements());

        $elements = $items[0]->getItemTypeElements();
        $elementCols = array_keys($elements);
        // var_dump($elements);
        // var_dump($elements['Titel']->toArray());
        // var_dump(get_class_methods($elements['Titel']));
        // var_dump($elements['Titel']->getElementSet());
        // var_dump(array_keys($elements));

        // var_dump(
        //     all_element_texts(
        //         $items[0],
        //         array(
        //             'show_empty_elements' => true,
        //             // 'show_element_sets' => $itemTypeName . ' Item Type Metadata',
        //             // 'show_element_sets' => 'MWW - HAB Item Type Metadata',
        //             // 'show_element_set_headings' => false,
        //             'show_element_sets' => array('Item Type Metadata'),
        //             'return_type' => 'array'
        //         )
        //     )
        // );

        // $metas = all_element_texts(
            // $items[0],
            // array(
                // 'show_empty_elements' => true,
                // 'show_element_sets' => array('Item Type Metadata'),
                // 'return_type' => 'array'
            // )
        // );
        // $metas = current($metas);
        // var_dump($metas);
        // foreach ($metas as $meta) {
            // var_dump(implode("\n", $meta));
        // }

        $user = current_user();
        // var_dump($user);

        $itemCols = array_keys($items[0]->toArray());
        // var_dump($itemCols);

        date_default_timezone_set('Europe/Berlin');
        define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

        $alphas = range('A', 'Z');
        // var_dump($alphas);

        // return;

        // init PHPExcel
        require_once dirname(__FILE__) . '/../vendor/PHPExcel/Classes/PHPExcel.php';
        $objPHPExcel = new PHPExcel();

        $objPHPExcel->getProperties()->setCreator($user->name)
            ->setLastModifiedBy($user->name)
            ->setTitle($model->name)
            ->setSubject('Omeka Spreedsheat Export');

        $objPHPExcel->setActiveSheetIndex(0);

        // Set title row
        // $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(+1);
        $objPHPExcel->getActiveSheet()->setCellValue($alphas[0] . '1', __('featured'));
        $objPHPExcel->getActiveSheet()->getColumnDimension($alphas[0])->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue($alphas[1] . '1', __('public'));
        $objPHPExcel->getActiveSheet()->getColumnDimension($alphas[1])->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue($alphas[2] . '1', __('added'));
        $objPHPExcel->getActiveSheet()->getColumnDimension($alphas[2])->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->setCellValue($alphas[3] . '1', __('modified'));
        $objPHPExcel->getActiveSheet()->getColumnDimension($alphas[3])->setAutoSize(true);

        $alphaCounter = 4;
        $alphaParentCounter = -1;
        $alphaParent = '';
        // $resetNext = false;

        foreach ($elementCols as $elementCol) {
            if (($alphaCounter % 26) == 0) {
                $alphaCounter = 0;
                $alphaParentCounter++;
            }
            if ($alphaParentCounter >= 0) {
                $alphaParent = $alphas[$alphaParentCounter];
            }
            $objPHPExcel->getActiveSheet()->getColumnDimension($alphaParent . $alphas[$alphaCounter])->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->setCellValue($alphaParent . $alphas[$alphaCounter] . '1', $elementCol);

            $objPHPExcel->getActiveSheet()->calculateColumnWidths();
            $titlecolwidth = $objPHPExcel->getActiveSheet()->getColumnDimension($alphaParent . $alphas[$alphaCounter])->getWidth();
            // $titlecolwidth = ($titlecolwidth < 10)? $titlecolwidth + 10 : $titlecolwidth;
            switch (true) {
                case ($titlecolwidth < 10):
                    $titlecolwidth += 30;
                    break;
                case ($titlecolwidth < 20):
                    $titlecolwidth += 20;
                    break;
                case ($titlecolwidth < 30):
                    $titlecolwidth += 10;
                    break;
            }
            $objPHPExcel->getActiveSheet()->getColumnDimension($alphaParent . $alphas[$alphaCounter])->setAutoSize(false);
            $objPHPExcel->getActiveSheet()->getColumnDimension($alphaParent . $alphas[$alphaCounter])->setWidth($titlecolwidth);



            $alphaCounter++;
        }

        switch ($options['images']) {
            case 'url':
            case 'name':
                if (($alphaCounter % 26) == 0) {
                    $alphaCounter = 0;
                    $alphaParentCounter++;
                }
                if ($alphaParentCounter >= 0) {
                    $alphaParent = $alphas[$alphaParentCounter];
                }
                $objPHPExcel->getActiveSheet()->setCellValue($alphaParent . $alphas[$alphaCounter] . '1', __('Dateien'));
                $objPHPExcel->getActiveSheet()->getColumnDimension($alphaParent . $alphas[$alphaCounter])->setWidth(60);
                $objPHPExcel->getActiveSheet()->getColumnDimension($alphaParent . $alphas[$alphaCounter])->setAutoSize(false);
                $alphaCounter++;
                break;
        }

        $objPHPExcel->getActiveSheet()
            ->getStyle('A1:' . $alphaParent . $alphas[$alphaCounter] .  '1')
            ->getFill()
            ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB('FF035151');
        $objPHPExcel->getActiveSheet()
            ->getStyle('A1:' . $alphaParent . $alphas[$alphaCounter] .  '1')
            ->getFont()
            ->getColor()
            ->setARGB(PHPExcel_Style_Color::COLOR_WHITE);
        $objPHPExcel->getActiveSheet()
            ->getStyle('A1:' . $alphaParent . $alphas[$alphaCounter] .  '1')
            ->getFont()
            ->setBold(true);

        // Set data rows
        $rowCounter = 2;
        foreach ($items as $item) {
            $metas = $item->getItemTypeElements();
            $metas = all_element_texts(
                $item,
                array(
                    'show_empty_elements' => true,
                    'show_element_sets' => array('Item Type Metadata'),
                    'return_type' => 'array'
                )
            );
            $metas = current($metas);
            $objPHPExcel->getActiveSheet()->setCellValue($alphas[0] . $rowCounter, $item->featured);
            $objPHPExcel->getActiveSheet()->setCellValue($alphas[1] . $rowCounter, $item->public);
            $objPHPExcel->getActiveSheet()->setCellValue($alphas[2] . $rowCounter, $item->added);
            $objPHPExcel->getActiveSheet()->setCellValue($alphas[3] . $rowCounter, $item->modified);
            $alphaCounter = 4;
            $alphaParentCounter = -1;
            $alphaParent = '';
            foreach ($metas as $meta) {
                if (($alphaCounter % 26) == 0) {
                    $alphaCounter = 0;
                    $alphaParentCounter++;
                }
                if ($alphaParentCounter >= 0) {
                    $alphaParent = $alphas[$alphaParentCounter];
                }
                $objPHPExcel->getActiveSheet()->getStyle($alphaParent . $alphas[$alphaCounter] . $rowCounter)->getAlignment()->setWrapText(true);
                $objPHPExcel->getActiveSheet()->setCellValue($alphaParent . $alphas[$alphaCounter] . $rowCounter, implode("\n", $meta));
                $alphaCounter++;
            }
            $fileBasUrl = '';
            switch ($options['images']) {
                case 'url':
                    $fileBasUrl = WEB_FILES . '/original/';
                case 'name':
                    $files = '';
                    foreach ($item->getFiles() as $file) {
                        $files = (empty($files))? $fileBasUrl . $file->filename : $files . "\n" . $fileBasUrl . $file->filename;
                    }
                    if (($alphaCounter % 26) == 0) {
                        $alphaCounter = 0;
                        $alphaParentCounter++;
                    }
                    if ($alphaParentCounter >= 0) {
                        $alphaParent = $alphas[$alphaParentCounter];
                    }
                    $objPHPExcel->getActiveSheet()->getStyle($alphaParent . $alphas[$alphaCounter] . $rowCounter)->getAlignment()->setWrapText(true);
                    $objPHPExcel->getActiveSheet()->setCellValue($alphaParent . $alphas[$alphaCounter] . $rowCounter, $files);
                    $alphaCounter++;
                    break;
            }
            $rowCounter++;
        }

        // return;

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $model->name . '.xlsx"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;

    }

    protected function _getFormOne($model = null)
    {
        $formOptions = array(
            'type' => 'spreadsheet',
        );

        $options = array(
            'option_itemtype' => null,
            'option_images' => null,
        );

        if ($model && $model->exists()) {
            $formOptions['record'] = $model;
            $tempOptions = unserialize($model->options);
            // var_dump($tempOptions);
            $options = array(
                'option_itemtype' => $tempOptions['itemtype'],
                'option_images' => $tempOptions['images'],
            );
            // var_dump($options);
        }

        $form = new Omeka_Form_Admin($formOptions);
        $form->addElementToEditGroup(
            'text', 'name',
            array(
                'id' => 'spreadsheet-name',
                'value' => $model->name,
                'label' => __('Name'),
                'description' => __('Name des Schemas'),
                'required' => true
            )
        );
        $form->addElementToEditGroup(
            'select',
            'option_itemtype',
            array(
                'id' => 'spreadsheet-item-type',
                'value' => $options['option_itemtype'],
                'label' => __('Objekttyp'),
                'description' => __('Wählen Sie einen Objekttyp aus.'),
                'required' => true,
                'multiOptions' => get_table_options('ItemType')
            )
        );
        $form->addElementToEditGroup(
            'select',
            'option_images',
            array(
                'id' => 'spreadsheet-images',
                'value' => $options['option_images'],
                'label' => __('Dateien'),
                'description' => __('Wählen Sie aus wie Dateien exportiert werden sollen.'),
                'multiOptions' => array(
                    'no' => 'Dateinamen nicht exportieren',
                    'name' => 'Dateinamen exportieren',
                    'url' => 'Dateinamen mit kompletter URL exportieren'
                )
            )
        );

        return $form;
    }

    private function _processForm($model, $action)
    {
        if ($this->getRequest()->isPost()) {
            try {
                // var_dump($_POST);
                // die();
                $options = array();
                if (isset($_POST['option_itemtype'])) {
                    $options['itemtype'] = $_POST['option_itemtype'];
                }
                if (isset($_POST['option_images'])) {
                    $options['images'] = $_POST['option_images'];
                }
                $options = serialize($options);

                $model->setPostData(array('options' => $options, 'name' => $_POST['name']));
                if ($model->save()) {
                    if ('add' == $action) {
                        $this->_helper->flashMessenger(__('Das Schema "%s" wurde erfolgreich hinzugefügt.', $model->name), 'success');
                    } else if ('edit' == $action) {
                        $this->_helper->flashMessenger(__('Das Schema "%s" wurde erfolgreich bearbeitet.', $model->name), 'success');
                    }
                    $this->_helper->redirector('browse');
                    return;
                }
            } catch (Omeka_Validate_Exception $e) {
                $this->_helper->flashMessenger($e);
            }
        }
        $this->view->spreadsheet = $model;
    }

    protected function _getDeleteSuccessMessage($record)
    {
        return __('Das Schema "%s" wurde erfolgreich gelöscht.', $record->name);
    }
}
