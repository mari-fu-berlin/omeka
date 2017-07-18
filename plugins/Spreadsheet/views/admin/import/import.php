<?php
$head = array(
    'bodyclass' => 'spreadsheet-import primary',
    'title' => html_escape(__('Spreadsheet importieren'))
);
$css = '
    .zend_form {
        margin: 5px auto;
        padding:0;
        overflow:hidden;
    }
    .zend_form fieldset {
        border: 1px solid #c7c8c0;
        border-radius: 6px;
        padding: 0 8px 15px;
        background-color: rgba(0,0,0,.07);
    }
    .zend_form fieldset > legend {
        border:1px solid #c7c8c0;
        border-radius: 6px;
        padding: 3px 6px;
        font-size: 16px;
        margin-left: 10px;
        margin-bottom: 0;
        background-color: white;
    }
    .zend_form dl {
        overflow: hidden;
    }
    .zend_form > dd {
        margin: 0;
    }
    .zend_form dl dt{
        padding:0;
        clear:both;
        width:30%;
        float:left;
        text-align:right;
        margin:5px 5px 5px 0;
    }
    .zend_form dl dd{
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
    .zend_form input[type="text"] {
        width: 100%;
    }
    .zend_form input[type=submit].big {
        margin: 10px auto;
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
    .zend_form #file-label,
    .zend_form #file-element,
    .zend_form #item_type-label,
    .zend_form #item_type-element {
        height: 0;
        display: none;
    }

';

queue_css_string($css);
echo head($head);
?>
<h2>Field-Matching</h2>
<?php
echo flash();
echo $this->form;
echo foot();
?>
