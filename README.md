# fuel-ext-direct

FuelPHP Sencha Ext JS direct provider

registration to packagist not yet.

1. You should make **fuel/app/direct** directory
2. Create direct function classes

```
<?php

class Direct_Foo {

    /**
     * bar
     * @param $age
     * @param $sage
     * @remotable
     * @formHandler
     */
    function bar($age, $sage) {

        return "result bar";
    }

}
```

## API definitions

```
http://localhost/direct/api
```



