# SimpleSAMLphp Module UCOFilter

This module can be used to add or change attributes using the [ExpressionLanguage](http://symfony.com/doc/current/components/expression_language.html) Symfony component.

## Requirements

* PHP>=5.5

## Installation

Installation can be as easy as executing:

```bash
bash$ composer require informaticauco/simplesamlphp-module-ucofilter
```

## Uso

From any entity that supports filters (_Authentication Processing Filters_ or _authproc_) we can use this module in this way:

```php
<?php

use SimpleSAML\Modules\UcoFilter\Auth\Process\UcoFilter;

$config = array(
    // ...
    
    50 => array(
        'class' => UcoFilter::class,
        // If one expression is true, all previous attributtes present in mapping
        // will be removed. Reset is empty by default, so no attributes are removed.
        'reset' => [
            '"sp-remote-id" in request["saml:RequesterID"]',
        ],
        'mapping' => array (
            // Concatenation example without conditions
            // firstName, middleName and lastName exists in Attributes.
            'commonName' => 'firstName[0]~" "~middleName[0]~" "~lastName[0]',
            
            // Multiple attributes
            'eduPersonPrincipalName' => [
                'uid[0]',  
                'mail[0]',
                'commonName[0]' // previous attributes are available
            ],
            
            // Complete syntax with conditions
            'groups' => [
                // value expression => condition expression
                // value only is added if condition is true
                '"staff"' => 'in_attribute(attributes["uid"], ["username1", "username2])',
                '"guest"' => '"sp-remote-id" in request["saml:RequesterID"]',
                '"student"' => 'attributes["uid"][0] matches "/^alum\d+/"',
            ],
        ),
    ),
    // ...    
);
```

## ExpressionLanguage reference

### Functions

This methods are available inside the expressions:

* `string md5(string)`: call to PHP md5 method 
* `string sha1(string)`: call to PHP sha1 method
* `bool in_attribute(array, array)`: search if exists elements from first array in second array. Useful to check if an attribute has a value.

### Variables

Value expressions receives all the request attributes as variables. V.g: ```$request['Attributes']['uid']``` will be accessible as ```uid``` variable inside expression. Remember than all attributes are arrays. 

Condition expressions has three variables:

* `request`: The complete request
* `attributes`: Only attributes
* `value`: The value to be assigned if condition is true


### Syntax

To see the complete syntax supported by the _Expression Language_ component see the  
[official documentation site](http://symfony.com/doc/current/components/expression_language/syntax.html).

