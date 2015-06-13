<?php

/*
    TreeWeb

    Copyright 2015 Gerardo Óscar Jiménez Tornos <gerardooscarjt@gmail.com>

    This file is part of TreeWeb.

    TreeWeb is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    TreeWeb is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with TreeWeb.  If not, see <http://www.gnu.org/licenses/>.
*/

import('core.Test');

/**
 * How to use Tests:
 *
 * First of all, add tests:

    Tests::add('Test one', function($t) {
        $t->log('You can log things');
    });

    Tests::add('Test two', function($t) {
        throw new Exception('accidental exception');
    });

    Tests::add('Test three', function($t) {
        $_is_going_to_fail = true;
        if ($_is_going_to_fail) {
            $t->error('this should not fail');
        }
    });

 * Secondly, run tests:

    Tests::run();

 * You can also run only one test:

    Tests::run('Test two');

 *
*/
class Tests {

    private static $battery = array();
    public static $verbose = false;

    public static function add($name, $code) {
        self::$battery[$name] = $code;
    }

    private static function run_one($name) {

        $code = self::$battery[$name];

        $test = new Test();
        try {
            echo "$name...";
            $code($test);
            echo "OK\n";

            if (self::$verbose) {
                foreach ($test->messages as $message) {
                    echo "    $message\n";
                }
            }
        } catch (Exception $e) {
            echo "EXCEPTION: ".$e->getMessage()."\n";
            echo "".$e->getFile().":".$e->getLine()."\n";
            echo $e->getTraceAsString()."\n";

            return false;
        }

        return true;
    }

    private static function run_all() {
        foreach (self::$battery as $name=>$code) {
            if (!self::run_one($name)) {
                return false;
            }
        }
        return true;
    }

    public static function run($name=null) {
        if (null === $name) {
            $result = self::run_all();
        } else{
            $result = self::run_one($name);
        }

        echo $result ? "PASS\n" : "FAIL\n";
        return $result;
    }

}
