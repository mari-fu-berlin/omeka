<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

class StaticApi
{
    public $saveKey = 'C4NPUJYe7rVCJIKCWxxI';
    public $chacheTime = 30;
    public $debug = true;

    public function __construct()
    {
        if (isset($_GET['save']) && $_GET['save'] === $this->saveKey) {
            if (isset($_GET['type']) && $_GET['type'] === 'items') {
                $this->dispathItems(true);
            }
        }
        $options = getopt("t:s:");
        if (isset($options['s']) && $options['s'] === $this->saveKey) {
            if (isset($options['t']) && $options['t'] === 'items') {
                $this->dispathItems();
            }
        }
    }

    public function dispathItems($output = false)
    {
        $file = __DIR__ . '/items.json';
        if (is_file($file)) {
            if ((time() - $this->chacheTime) > filemtime($file)) {
                $this->writeItems($file, $output);
            }
        } else {
            $this->writeItems($file, $output);
        }
    }

    public function writeItems($file, $output = false)
    {
        $page = 1;
        $get = true;
        $accumulator = '';

        while ($get === true) {

            $items = $this->getItems($page);

            if ($items === '[]' || $items === false) {
                $get = false;
            } else {
                $page++;
                if (!empty($accumulator)) {
                    $accumulator .= ','    ;
                }
                $accumulator .= trim($items, '[]');
            }
        }

        if (!empty($accumulator)) {
            $response = file_put_contents($file, '[' . $accumulator . ']');
            if ($this->debug === true && $output === true) {
                if ($response !== false) {
                    echo 'File saved';
                } else {
                    echo 'There were errros.';
                }
            }
        }
    }

    public function getItems($page)
    {
        $url = 'https://www.mari-portal.de/omeka/api/items?item_type=27&per_page=999&page=' . $page;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode === 200) {
            return $response;
        } else {
            return false;
        }
    }
}

$api = new StaticApi();

?>
