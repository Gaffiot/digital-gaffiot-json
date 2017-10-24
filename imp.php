<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$array = [];
header('Content-Type: application/json; charset=utf-8');
$gaffiot = file_get_contents('gaff.txt', FILE_USE_INCLUDE_PATH);
$file = 'Desktop/gaffiot_json.txt';

//$gaffiot = substr($gaffiot, strpos($gaffiot, "\sansqu{zygostasium}"), strlen($gaffiot));

$i = 72252;
$stop = false;
while (!$stop) {
    if (strlen($gaffiot) == 0) {
        $stop = true;
    } else {
        preg_match('/(\\\\sansqu{([^}]+)})\n(\\\\entree{([^}]+)})([\s\S]+)?(?=\\\\sansqu{)/', $gaffiot, $matches);
        $id = $matches[2];
        $latin = $matches[4];
        $content = $matches[5];

        echo $i . " - " . $id . "\n";

        file_put_contents($file, json_format(json_encode([
                "id" => $i,
                "latin_raw" => $id,
                "latin" => $latin,
                "french" => substr($content, 0, strpos($content, "\sansqu{")),
            ])) . ",", FILE_APPEND | LOCK_EX);

        $gaffiot = substr($content, strpos($content, "\sansqu{"), (strlen($content) - strpos($content, "\sansqu{")));
        $i++;
    }
}

/**
 * @param $content
 * @param $i
 * @return mixed
 */
function search($content, $i)
{
    if (strlen($content) > 0) {
        preg_match('/(\\\\sansqu{([^}]+)})\n(\\\\entree{([^}]+)})([\s\S]+)?(?=\\\\sansqu{)/', $content, $matches);
        $id = $matches[2];
        $latin = $matches[4];
        $content = $matches[5];

        echo json_format(json_encode([
            "id" => $i,
            "latin_raw" => $id,
            "latin" => $latin,
            "french" => substr($content, 0, strpos($content, "\sansqu{")),
        ]));
        echo ",";
        return search(substr($content, strpos($content, "\sansqu{"), (strlen($content) - strpos($content, "\sansqu{"))), $i + 1);
    }
}


// Pretty print some JSON  ## http://php.net/manual/en/function.json-encode.php
function json_format($json)
{
    $tab = "  ";
    $new_json = "";
    $indent_level = 0;
    $in_string = false;

    $json_obj = json_decode($json);

    if ($json_obj === false)
        return false;

    $json = json_encode($json_obj);
    $len = strlen($json);

    for ($c = 0; $c < $len; $c++) {
        $char = $json[$c];
        switch ($char) {
            case '{':
            case '[':
                if (!$in_string) {
                    $new_json .= $char . "\n" . str_repeat($tab, $indent_level + 1);
                    $indent_level++;
                } else {
                    $new_json .= $char;
                }
                break;
            case '}':
            case ']':
                if (!$in_string) {
                    $indent_level--;
                    $new_json .= "\n" . str_repeat($tab, $indent_level) . $char;
                } else {
                    $new_json .= $char;
                }
                break;
            case ',':
                if (!$in_string) {
                    $new_json .= ",\n" . str_repeat($tab, $indent_level);
                } else {
                    $new_json .= $char;
                }
                break;
            case ':':
                if (!$in_string) {
                    $new_json .= ": ";
                } else {
                    $new_json .= $char;
                }
                break;
            case '"':
                if ($c > 0 && $json[$c - 1] != '\\') {
                    $in_string = !$in_string;
                }
            default:
                $new_json .= $char;
                break;
        }
    }

    return $new_json;
}
//print_r($array);