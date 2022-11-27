<?php

namespace Programmerphp\Loginmanagement\Config {
    function header(string $url)
    {
        echo $url;
    }
}

namespace Programmerphp\Loginmanagement\Service {
    function setcookie(string $name, string $value)
    {
        echo "$name: $value";
    }
}
