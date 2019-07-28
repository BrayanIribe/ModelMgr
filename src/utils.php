<?php

class Utils
{
    const COLORS = [
        'bold' => "\033[1m%s\033[0m",
        'dark' => "\033[2m%s\033[0m",
        'italic' => "\033[3m%s\033[0m",
        'underline' => "\033[4m%s\033[0m",
        'blink' => "\033[5m%s\033[0m",
        'reverse' => "\033[7m%s\033[0m",
        'concealed' => "\033[8m%s\033[0m",
        // foreground colors
        'black' => "\033[30m%s\033[0m",
        'red' => "\033[31m%s\033[0m",
        'green' => "\033[32m%s\033[0m",
        'yellow' => "\033[33m%s\033[0m",
        'blue' => "\033[34m%s\033[0m",
        'magenta' => "\033[35m%s\033[0m",
        'cyan' => "\033[36m%s\033[0m",
        'white' => "\033[37m%s\033[0m",
        // background colors
        'bg_black' => "\033[40m%s\033[0m",
        'bg_red' => "\033[41m%s\033[0m",
        'bg_green' => "\033[42m%s\033[0m",
        'bg_yellow' => "\033[43m%s\033[0m",
        'bg_blue' => "\033[44m%s\033[0m",
        'bg_magenta' => "\033[45m%s\033[0m",
        'bg_cyan' => "\033[46m%s\033[0m",
        'bg_white' => "\033[47m%s\033[0m",
    ];

    const ROWS = 80;

    public static function shell($cmd)
    {
        ob_start();
        @passthru($cmd . " 2>&1");
        $rtn = ob_get_contents();
        ob_end_clean();
        return $rtn;
    }
    public static function log($str, $og_color = "white", $die_on_error = true, $show_clock = true)
    {
        $die = false;
        if ($og_color === true) {
            $og_color = "green";
        } else if ($og_color === false) {
            $og_color = "red";
            $die = $die_on_error;
        }

        $color = self::COLORS[$og_color];
        $time = "[" . date("H:i:s") . "]";
        if ($show_clock) {
            $str = $time . " " . $str;
        }
        if ($og_color == "red" || $og_color == "green") {
            $status_str = ($og_color == "red" ? "ERROR" : "OK");
            $str = str_pad($str, self::ROWS - strlen($status_str)) . $status_str;
        }
        $str .= "\n";
        $final_str = sprintf($color, $str);
        @printf($final_str, $str);

        if ($die) {
            die();
        }
    }

    public static function open_conn($host, $user, $passwd, $db = null)
    {
        $rtn = @new mysqli($host, $user, $passwd, $db);
        if ($rtn->connect_error) {
            self::log("Error trying to connect mysql backend.", false);
            return null;
        }
        return $rtn;
    }

    public static function query($conn, $sql)
    {
        $rtn = [];
        $query = $conn->query($sql);
        if ($query) {
            while ($row = $query->fetch_assoc()) {
                if (is_array($row) && count($row) == 1) {
                    $row = array_values($row)[0];
                }
                $rtn[] = $row;
            }
        }
        return (is_array($rtn) && count($rtn) ? $rtn : false);
    }

    public static function strpos_line($filename, $str, $number = true)
    {
        $lines = file($filename);
        foreach ($lines as $lineNumber => $line) {
            if (strpos($line, $str) !== false) {
                return ($number ? $lineNumber + 1 : $line);
            }
        }
        return -1;
    }

    public static function merge_line(&$file, $line, $str)
    {
        $top = array_slice($file, 0, $line);
        $bottom = array_slice($file, $line + 1);
        $file = array_merge($top, [$str], $bottom);
    }

    public static function parse_argv($arg, $argv)
    {
        if (!count($argv)) {
            return [];
        }

        foreach ($argv as $cmd_arg) {
            $pos = strpos($cmd_arg, $arg);
            if ($pos !== false) {
                $pos = strpos($cmd_arg, "=");
                if ($pos === false) {
                    return [];
                }
                $pos++; //dont keep the = symbol
                $data_inline = substr($cmd_arg, $pos);
                $data = explode(",", $data_inline);
                return $data;
            }
        }
        return [];
    }

    public static function create_model($table, $dbname, $namespace, $update, &$avoided_suffixes, &$model_name, $keep_suffix = [], $is_app = false)
    {
        $table_name = $table;
        $suffix = "";
        if (strpos($table, "_")) {
            $table_name = ""; //reset table name
            $words = explode("_", $table);

            for ($i = 0; $i < count($words); $i++) {
                $word = ucfirst($words[$i]);
                if ($i == 0 && !in_array($word, $keep_suffix)) {
                    if (!in_array($word, $avoided_suffixes)) {
                        $avoided_suffixes[] = $word;
                    }
                    continue;
                }
                $table_name .= $word;
            }
        }
        $model_name = $table_name;
        $model = __DIR__ . "/../app/models/" . $table_name . ".php";
        $file = [];
        if (file_exists($model)) {
            if ($update) {
                $file = file($model);
            }
            unlink($model);
        }

        $cmd = "phalcon model {$table_name} --schema={$dbname} --name={$table}";
        if (strlen($namespace)) {
            $cmd .= " --namespace={$namespace}";
        }
        $output = self::shell($cmd);
        $success = strpos($output, "successfully created.") !== false && file_exists($model);

        if ($success && $update && count($file)) {
            //just update props, keep the functions
            //get the public variables
            $props = [];
            $keyword = "public $";
            $keyword_type = "@var ";
            foreach ($file as $ln => $line) {
                $pos = strpos($line, $keyword);
                if ($pos === false) {
                    continue;
                }
                $name = substr($line, $pos + strlen($keyword)); //get only variable name
                $name = substr($name, 0, strpos($name, ";")); //remove the colon and new line bytes

                //now get variable type
                $line = $file[$ln - 2]; //set the line position to type @var like
                $pos = strpos($line, $keyword_type);
                $type = substr($line, $pos + strlen($keyword_type)); //get only variable name
                $type = substr($type, 0, strlen($type) - 1); //remove the new line byte

                $props[] = [
                    "name" => $name,
                    "type" => $type,
                ];

            }

            //now, merge props!

            $pos = self::strpos_line($model, "class"); //find the top of file
            $pos++; //add new line for key open
            $pos++; //add new line for key open
            $carries = $file[$pos];
            $carries = substr($carries, 0, strpos($carries, "/**"));
            $top = array_slice($file, 0, $pos);
            $pos = self::strpos_line($model, "public function initialize()");
            $pos -= 5; //go back for comment and new line
            $bottom = array_slice($file, $pos);
            //now set the props!
            $body = [];
            $ln = pack("H*", "0A");
            foreach ($props as $prop) {
                $body[] = $carries . "/**" . $ln;
                $body[] = $carries . " *" . $ln;
                $body[] = $carries . " * @var " . $prop["type"] . $ln;
                $body[] = $carries . " */" . $ln;
                $body[] = $carries . " public $" . $prop["name"] . ";" . $ln . $ln;
            }

            @unlink($model);
            file_put_contents($model, array_merge($top, $body, $bottom));

        }

        if ($success && $is_app && !$update) {
            //manipulate the file!
            //search for initialize function
            $file = file($model);
            $pos = self::strpos_line($model, "public function initialize()");
            if ($pos == -1) {
                return $success;
            }

            //advance one line because the key
            $pos++;
            $line = $file[$pos]; //should return $this->setSchema
            $ln = pack("H*", "0A");
            $tabs = substr($line, 0, strpos($line, '$this'));
            $new_line = $tabs . '$schema = $this->getDI()->get("schema");' . $ln . $tabs . '$this->setSchema($schema);' . $ln;
            self::merge_line($file, $pos, $new_line);
            @unlink($model);
            file_put_contents($model, $file);
        }

        return $success;
    }

    public static function splash()
    {

        $limit = 70;
        $title = "\x0D\x0A\x20\x20\x20\x5F\x5F\x5F\x5F\x5F\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x2E\x5F\x5F\x5F\x20\x20\x20\x20\x20\x20\x20\x2E\x5F\x5F\x20\x20\x20\x20\x20\x20\x5F\x5F\x5F\x5F\x5F\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x0D\x0A\x20\x20\x2F\x20\x20\x20\x20\x20\x5C\x20\x20\x20\x20\x5F\x5F\x5F\x5F\x20\x20\x20\x20\x5F\x5F\x7C\x20\x5F\x2F\x20\x5F\x5F\x5F\x5F\x20\x20\x7C\x20\x20\x7C\x20\x20\x20\x20\x2F\x20\x20\x20\x20\x20\x5C\x20\x20\x20\x20\x5F\x5F\x5F\x5F\x20\x5F\x5F\x5F\x5F\x5F\x5F\x5F\x20\x0D\x0A\x20\x2F\x20\x20\x5C\x20\x2F\x20\x20\x5C\x20\x20\x2F\x20\x20\x5F\x20\x5C\x20\x20\x2F\x20\x5F\x5F\x20\x7C\x5F\x2F\x20\x5F\x5F\x20\x5C\x20\x7C\x20\x20\x7C\x20\x20\x20\x2F\x20\x20\x5C\x20\x2F\x20\x20\x5C\x20\x20\x2F\x20\x5F\x5F\x5F\x5C\x5C\x5F\x20\x20\x5F\x5F\x20\x5C\x0D\x0A\x2F\x20\x20\x20\x20\x59\x20\x20\x20\x20\x5C\x28\x20\x20\x3C\x5F\x3E\x20\x29\x2F\x20\x2F\x5F\x2F\x20\x7C\x5C\x20\x20\x5F\x5F\x5F\x2F\x20\x7C\x20\x20\x7C\x5F\x5F\x2F\x20\x20\x20\x20\x59\x20\x20\x20\x20\x5C\x2F\x20\x2F\x5F\x2F\x20\x20\x3E\x7C\x20\x20\x7C\x20\x5C\x2F\x0D\x0A\x5C\x5F\x5F\x5F\x5F\x7C\x5F\x5F\x20\x20\x2F\x20\x5C\x5F\x5F\x5F\x5F\x2F\x20\x5C\x5F\x5F\x5F\x5F\x20\x7C\x20\x5C\x5F\x5F\x5F\x20\x20\x3E\x7C\x5F\x5F\x5F\x5F\x2F\x5C\x5F\x5F\x5F\x5F\x7C\x5F\x5F\x20\x20\x2F\x5C\x5F\x5F\x5F\x20\x20\x2F\x20\x7C\x5F\x5F\x7C\x20\x20\x20\x0D\x0A\x20\x20\x20\x20\x20\x20\x20\x20\x5C\x2F\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x5C\x2F\x20\x20\x20\x20\x20\x5C\x2F\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x5C\x2F\x2F\x5F\x5F\x5F\x5F\x5F\x2F\x20\x20\x20\x20\x20\x20\x20\x20\x20\x0D\x0A";
        self::log(str_repeat("=", $limit), "bold", false, false);
        self::log($title, "bold", false, false);
        self::log(str_repeat("=", $limit) . "\n", "bold", false, false);
    }

    public static function help()
    {
        self::log(" Available commands:\n", "bold", false, false);
        self::log(" --update Update model properties only, keep the original functions", "bold", false, false);
        self::log(" --keep-suffix=suf1,suf2,suf3 Keeps the suffix of the table of model.", "bold", false, false);
        self::log(" --db=dbname1,dbname2 Generate models from certain schemas.", "bold", false, false);
        self::log(" --namespace=App\\Models Namespace of the generated models. Default: App.", "bold", false, false);
        self::log(" --help prints this screen.", "bold", false, false);
    }
}