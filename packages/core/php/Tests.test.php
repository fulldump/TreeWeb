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

Tests::add('Add a test', function(Test $t) {
    $t->log("hola");
});

Tests::add('Run all tests', function(Test $t) {
    $t->log("This should run all tests");
});

Tests::add('Run only one test', function(Test $t) {
    $t->error("This should run all tests");
});

