<?php

    // specialni vypis
    if (!function_exists("printr")) {
        function printr($val)
        {
            echo "<hr><pre>";
            print_r($val);
            echo "</pre><hr>";
        }
    }

