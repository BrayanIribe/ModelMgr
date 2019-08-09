<?php

class Utils
{
    const PHALCON_PROJECT = 1;
    const LARAVEL_PROJECT = 2;
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

    public static function text_color($str, $color = "white")
    {
        $str = sprintf(self::COLORS[$color], $str);
        return $str;
    }
    public static function log($str, $type = -1, $die_on_error = true)
    {
        $die = false;
        $color = "white";
        if ($type === true || $type === false) {
            $str = ($type === true ? self::text_color("[  OK  ] ", "green") : self::text_color("[ FAIL ] ", "red")) . $str;
            if ($type === false) {
                $die = true;
            }
        } else if ($type !== "NO_BRACKET") {
            if ($type == "WARN") {
                $str = self::text_color("[ WARN ] ", "yellow") . $str;
            } else if ($type != "NO_COLOR") {
                if ($type === -1) {
                    $type = "bold";
                }
                $str = self::text_color("[ INFO ] ", "bold") . self::text_color($str, $type);
            }
        }
        $str .= "\n";

        @printf($str);

        if ($die) {
            die();
        }
    }

    public static function find_project()
    {
        $root = getcwd();
        //search for phalcon project
        if (file_exists($root . "/.phalcon")) {
            return self::PHALCON_PROJECT;
        }

        return false;
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

    public static function strpos_line($file, $str, $number = true)
    {
        foreach ($file as $lineNumber => $line) {
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

    public static function get_tablename($table, $keep_suffix, &$avoided_suffixes, &$suffix = null)
    {
        if (strpos($table, "_")) {
            $table_name = ""; //reset table name
            $words = explode("_", $table);

            for ($i = 0; $i < count($words); $i++) {
                $word = ucfirst($words[$i]);
                if ($i == 0 && !in_array($word, $keep_suffix)) {
                    if (!in_array($word, $avoided_suffixes) || $suffix !== null) {
                        $avoided_suffixes[] = $word;
                        $suffix = $word;
                    }
                    continue;
                }
                $table_name .= $word;
            }
            return $table_name;
        } else {
            return $table;
        }
    }

    public static function create_model($table, $dbname, $namespace, &$avoided_suffixes, &$model_name, $fixer, $keep_suffix = [])
    {
        $table_name = self::get_tablename($table, $keep_suffix, $avoided_suffixes);
        $model_name = $table_name;
        $model = getcwd() . "/app/models/" . $table_name . ".php";
        $file = [];
        $have_ns = false;
        $update = false;
        if (file_exists($model)) {
            //force model update
            $update = true;
            $have_ns = strpos(file_get_contents($model), "namespace") !== false; //find the top of file
            $file = file($model);
            copy($model, $model . '.bak'); //generate backup
            unlink($model);
        }

        $cmd = "phalcon model {$table_name} --schema={$dbname} --name={$table}";

        if (strlen($namespace)) {
            $cmd .= " --namespace={$namespace}";
        }
        $output = self::shell($cmd);
        $success = strpos($output, "successfully created.") !== false && file_exists($model);

        if (!$success) {
            $output_ln = explode("\r\n", $output);
            $error = self::strpos_line($output_ln, "ERROR: ") - 1;
            $error = str_replace(["\r", "\n"], '', $output_ln[$error]);
            if ($update && count($file)) {
                file_put_contents($model, $file);
                file_put_contents($model . '.bak', $file);
            }
            self::log($error, false);
        }

        if ($success && $update && count($file)) {
            //just update props, keep the functions
            //get the public variables
            $props = [];
            $keyword = "public $";
            $keyword_type = "@var ";
            $new_file = file($model);
            foreach ($new_file as $ln => $line) {
                $pos = strpos($line, $keyword);
                if ($pos === false) {
                    continue;
                }
                $name = substr($line, $pos + strlen($keyword)); //get only variable name
                $name = substr($name, 0, strpos($name, ";")); //remove the colon and new line bytes

                //now get variable type
                $line = $new_file[$ln - 2]; //set the line position to type @var like
                $pos = strpos($line, $keyword_type);
                $type = substr($line, $pos + strlen($keyword_type)); //get only variable name
                $type = substr($type, 0, strlen($type) - 1); //remove the new line byte

                $props[] = [
                    "name" => $name,
                    "type" => $type,
                ];

            }

            //now, merge props!
            //KEEP THE ORIGINAL NAMESPACE AND COMMENTS OF TOP
            $pos = self::strpos_line($file, "class"); //find the top of file
            $pos++; //add new line for key open
            $pos++; //add new line for key open
            $carries = $file[$pos];
            $carries = substr($carries, 0, strpos($carries, "/**"));
            $top = array_slice($file, 0, $pos);
            //now find the initialization (end of props)
            $pos = self::strpos_line($file, "public function initialize()");
            //code injection
            $relative_schema_pos = self::strpos_line(array_slice($file, $pos - 4), "\$this->setSchema") - 1;
            //inject the code of the previous model initialize()!
            $bottom = array_slice($file, $pos - 4, $relative_schema_pos);
            //Find the source in the NEW FILE
            $set_source_ln = self::strpos_line($new_file, "\$this->setSource");
            //find bracket
            $relative_close_bracket = self::strpos_line(array_slice($new_file, $set_source_ln), $carries . "}") - 1;
            //inject the db params
            $bottom = array_merge($bottom, array_slice($new_file, $set_source_ln - 2, 2));
            //inject FK keys
            $bottom = array_merge($bottom, array_slice($new_file, $set_source_ln, $relative_close_bracket));
            //pass the fixer in FK keys
            foreach ($fixer as $find => $replace) {
                $bottom = str_replace($find, $replace, $bottom);
            }
            //now inject the rest of code
            $end_bracket_pos = self::strpos_line($file, $carries . "}") - 1;
            $bottom = array_merge($bottom, array_slice($file, $end_bracket_pos));

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
        } else if ($success && !$update) {
            //pass the fixer anyways
            $file = file_get_contents($model);
            foreach ($fixer as $find => $replace) {
                $file = str_replace($find, $replace, $file);
            }
            @unlink($model);
            file_put_contents($model, $file);
        }

        return $success;
    }

    public static function splash()
    {

        $limit = 70;
        $title = "\x20\x5F\x5F\x5F\x5F\x5F\x20\x20\x20\x20\x20\x20\x20\x5F\x20\x20\x20\x20\x20\x5F\x20\x5F\x5F\x5F\x5F\x5F\x20\x20\x20\x20\x20\x20\x20\x20\x20\x0D\x0A\x7C\x20\x20\x20\x20\x20\x7C\x5F\x5F\x5F\x20\x5F\x7C\x20\x7C\x5F\x5F\x5F\x7C\x20\x7C\x20\x20\x20\x20\x20\x7C\x5F\x5F\x5F\x20\x5F\x5F\x5F\x20\x0D\x0A\x7C\x20\x7C\x20\x7C\x20\x7C\x20\x2E\x20\x7C\x20\x2E\x20\x7C\x20\x2D\x5F\x7C\x20\x7C\x20\x7C\x20\x7C\x20\x7C\x20\x2E\x20\x7C\x20\x20\x5F\x7C\x0D\x0A\x7C\x5F\x7C\x5F\x7C\x5F\x7C\x5F\x5F\x5F\x7C\x5F\x5F\x5F\x7C\x5F\x5F\x5F\x7C\x5F\x7C\x5F\x7C\x5F\x7C\x5F\x7C\x5F\x20\x20\x7C\x5F\x7C\x20\x20\x0D\x0A\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x20\x7C\x5F\x5F\x5F\x7C\x20\x20\x20\x20";
        self::log($title, "NO_BRACKET");
        self::log(self::text_color("version ", "green") . self::text_color(version, "yellow") . "\n", "NO_BRACKET");
    }

    public static function command($str)
    {
        $padding = 25;
        $str = str_pad($str, $padding);
        $str = self::text_color($str, "green");
        return " " . $str;
    }

    public static function help()
    {
        self::log(self::text_color(" Available commands:\n", "yellow"), "NO_BRACKET");
        self::log(self::command("--update") . "Update model properties only, keep the original functions.", "NO_BRACKET");
        self::log(self::command("--keep-suffix") . "Keeps the suffix of the table of model.", "NO_BRACKET");
        self::log(self::command("--db") . "Generate models from certain schemas.", "NO_BRACKET");
        self::log(self::command("--namespace") . "Namespace of the generated models.", "NO_BRACKET");
        self::log(self::command("--help") . "prints this screen.", "NO_BRACKET");
    }
}