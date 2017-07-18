<?php
$head = array(
    'bodyclass' => 'spreadsheet-import primary',
    'title' => html_escape(__('Spreadsheet importieren'))
);

$css = '
    .zend_form{
        margin: 5px auto;
        padding:0;
        overflow:hidden;
    }
    .zend_form dt{
        padding:0;
        clear:both;
        width:30%;
        float:left;
        text-align:right;
        margin:5px 5px 5px 0;
    }
    .zend_form dd{
        padding:0;
        float:left;
        width:68%;
        margin:5px 2px 5px 0;
    }
    .zend_form p{
        padding:0;
        margin:0;
    }
    .zend_form input, .zend_form textarea{
        margin:0 0 2px 0;
        padding:0;
    }
    .zend_form input[type=submit].big {
        margin: 10px 0;
    }
    .zend_form .description {
        font-style: italic;
        font-size: 12px;
    }
    .zend_form .errors {
        display: block;
        list-style: none;
        background-color: rgba(255, 255, 255, .5);
        border: 1px solid #E7E7E7;
        -webkit-border-radius: 3px;
        -moz-border-radius: 3px;
        border-radius: 3px;
        margin: 0;
        padding: .5em;
    }
    .zend_form .errors li {
        list-style: none;
        color: #914E33;
        padding: 0;
        margin: 0;
    }

    .required:before {
        content:\'* \';
        color: #914E33;
        font-weight: bold;
        font-size: 16px;
        line-height: 1;
    }
    .required:after {
        content:\'\';
    }
    dt {     margin-top: 10px; }
';

queue_css_string($css);
echo head($head);
?>
<?php echo flash(); ?>
<?php

    if (isset($this->sheme)) {
        echo '<h1><span style="font-size: 20px;">Schema </span>' . $this->sheme->name . '</h1>';
        $itemTypesById = array();
        foreach ($this->itemTypes as $itemType) {
            $itemTypesById[$itemType->id] = $itemType->name;
        }
        $elementsById = array();
        foreach ($this->elements as $element) {
            $elementsById[$element->id] = $element->name;
        }
        $options = unserialize($this->sheme->options);
        echo '<dl>';
        foreach ($options as $key => $value) {
            if ($key == 'item_type') {
                $key = 'Objektyp';
                $value = '<strong>' . $itemTypesById[$value] . '</strong>';
            } elseif ($key == 'main_featured') {
                $key = 'Standardfeld <strong>Hervorgehoben</strong>';
                if ($value === 0 || $value === '0') {
                    $value = '<span style="color:#c00;">Nicht zugeordnet.</span>';
                } else {
                    $value = 'Spalte in Exceldatei: <strong>' . $value . '</strong>';
                }
            } elseif ($key == 'main_dctitle') {
                $key = 'Standardfeld <strong>Dublin Core Titel</strong>';
                if ($value === 0 || $value === '0') {
                    $value = '<span style="color:#c00;">Nicht zugeordnet.</span>';
                } else {
                    $value = 'Spalte in Exceldatei: <strong>' . $value . '</strong>';
                }
            } elseif (strpos($key, 'itemtype_') !== false) {
                $id = substr($key, strlen('itemtype_'));
                $key = 'Feld des Objekttyps <strong>' . $elementsById[$id] . '</strong>';
                if ($value === 0 || $value === '0') {
                    $value = '<span style="color:#c00;">Nicht zugeordnet.</span>';
                } else {
                    $value = 'Spalte in Exceldatei: <strong>' . $value . '</strong>';
                }
            } else {
                continue;
            }
            echo '<dt>' . $key . '</dt><dd>' . $value . '</dd>';
        }
        echo '</dl>';
    }
?>
<?php echo foot(); ?>