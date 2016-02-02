# fuel-ext-direct

FuelPHP Sencha Ext JS direct provider

## Install

```
composer require xenophy/fuel-ext-direct
```

### Add setting to config.php

You should add **fuel-ext-direct** to 'always_load' -> 'packages' like a following 

```
'packages'  => array(
    'orm',
    'auth',
    'fuel-ext-direct',
),
```

### Copy to config

**packages/extdirect/config/extdirect.php** to your FuelPHP config directory.


## How to use

You should make **fuel/app/classes/direct** directory, after that create like a following php classses.

```
<?php

class Direct_Foo {

    /**
     * bar
     *
     * @param $age
     * @param $sage
     *
     * @remotable
     * @formHandler
     */
    function bar($age, $sage) {

        return "result bar";
    }

}
```

### Method Comments

When you would like to publish your class method, you should write method comment.

This package will provide to Sencha Ext JS only **@remotable** in method comment.
So, when you would like to use method as "Form Handler", Please write **@formHandler** in your method comment.


## API definitions

Firstly, maybe you should write "Direct Provider" in your Application.js.

```
requires: [
    'Ext.direct.*'
],

launch: function () {
    Ext.direct.Manager.addProvider(Ext.REMOTING_API);
},
```

Next, you should add following settings in in your **app.json**.
"path" is your local develoment host url using FuelPHP.

```
    "js": [
        {
            "path": "http://[your local develomnent host]/direct/api",
            "remote": true
        },
        {
            "path": "app.js",
            "bundle": true
        }
    ],
    
```

## Conclusion

Please show your Sencha Ext JS Project via Sencha Cmd(jetty).

```
http://localhost:1841/
```

After that, please try following JavaScript code in Developer Console.

```
Foo.bar("age", "sage", function(result) {
  console.log(result);
});
```

Maybe you can see "result bar" message.

Enjoy yourself!
