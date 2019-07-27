<?php

/*
IvySoftware. Copyright 2019.
First revision: 26-07-2019.
 */

require_once __DIR__ . "/utils.php";

$builder = new Builder();

class Builder
{

    private $host, $user, $passwd;

    public function __construct()
    {
        global $argv;
        global $argc;

        //check if phalcon module is available

        $benchmark_start = microtime(true);
        $update = in_array("--update", $argv);
        $keep_suffix = Utils::parse_argv("--keep-suffix", $argv);
        $db_params = Utils::parse_argv("--db", $argv);
        $namespace = Utils::parse_argv("--namespace", $argv);
        if (!count($namespace)) {
            $namespace = "App";
        } else {
            $namespace = $namespace[0];
        }

        $db = $db_params;

        Utils::splash();

        if (in_array("--help", $argv)) {
            Utils::help();
            return;
        }
        if (!extension_loaded("phalcon")) {
            Utils::log("Phalcon module is not installed/enabled in the current php instance.", "red");
        }

        //get phalcon config

        $config_dir = __DIR__ . "/../app/config/config.php";

        if (!file_exists($config_dir)) {
            Utils::log("app/config/config.php not found in project! ", "red");
            return;
        }

        $config = require_once $config_dir;

        $host = $config->database->host;
        $user = $config->database->username;
        $passwd = $config->database->password;

        if (isset($config->database->dbname) && !count($db_params)) {
            $db[] = $config->database->dbname;
        }

        if (isset($config->database->app_dbname) && !count($db_params)) {
            $db[] = $config->database->app_dbname;
        }

        //check if phalcon devtools is available
        $cmd = Utils::shell("phalcon --version");
        $error = $cmd === null || !strlen($cmd) || strpos($cmd, "Phalcon DevTools") === false;
        Utils::log("Phalcon devtools installed? ", !$error);

        //check schemas

        $this->host = $host;
        $this->user = $user;
        $this->passwd = $passwd;
        $this->conn = Utils::open_conn($host, $user, $passwd);

        //now retrieve the tables of main db

        if (!$update) {
            Utils::log("Update argument not set. I'll force the creation of the model...", "yellow");
        }

        if (count($db_params)) {
            Utils::log("(!) Overriding databases in config.php! --db argument present!", "yellow");
        }

        if (!file_exists(__DIR__ . "/../app/models")) {
            Utils::log("Models folder doesnt exists, I'll create one...", "yellow");
            mkdir(__DIR__ . "/../app/models");
        }

        //so start with phalcon models

        foreach ($db as $dbname) {
            $tables = Utils::query($this->conn, "SHOW TABLES IN " . $dbname);
            Utils::log("#######################################################", "bold");
            Utils::log("Switching to database " . $dbname, "bold");
            Utils::log("Has tables?", ($tables !== false));
            $models = [];
            $avoided_suffixes = [];
            $is_app = false;
            if (isset($config->database->app_dbname) && $config->database->app_dbname == $dbname){
                $is_app = true;
            }
            foreach ($tables as $table) {
                $model_name = "";
                $ok = Utils::create_model($table, $dbname, $namespace, $update, $avoided_suffixes, $model_name, $keep_suffix, $is_app);
                Utils::log("Model {$table} -> {$model_name} created", $ok);
                $models[] = $model_name;
            }
            Utils::log("Model generation of this database finished!", "bold");
        }

        $benchmark_end = (float) number_format(microtime(true) - $benchmark_start, 2);
        Utils::log("Model generator finished. Took: " . $benchmark_end . "s.", "bg_magenta");

    }
}