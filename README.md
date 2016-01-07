[![Build Status](https://travis-ci.org/geggleto/geggleto-acl.svg)](https://travis-ci.org/geggleto/geggleto-acl)

# geggleto-acl
Provides a ACL repository and Middleware using Zend/Permissions/Acl library
PSR-7 Compliant

# Usage Example

```php
//Define or Pull your ACL's into the following format
/*
$config = [
    "resources" => ["/", "/no", "/yes"],
    "roles" => ["guest", "user1", "user2"],
    "assignments" => [
        "allow" => [
            "guest" => ["/"],
            "user1" => ["/", "/no"],
            "user2" => ["/", "/yes"]
        ],
        "deny" => [
            "guest" => ["/no", "/yes"],
            "user1" => ["/yes"],
            "user2" => ["/no"]
        ]
    ]
];
*/

//In Slim v3
$app->add(\Geggleto\Acl\AclRepository(["guest"], 
//This should be in a nice php file by itself for easy inclusion... include '/path/to/acl/definition.php'
[
    "resources" => ["/", "/no", "/yes"],
    "roles" => ["guest", "user1", "user2"],
    "assignments" => [
        "allow" => [
            "guest" => ["/"],
            "user1" => ["/", "/no"],
            "user2" => ["/", "/yes"]
        ],
        "deny" => [
            "guest" => ["/no", "/yes"],
            "user1" => ["/yes"],
            "user2" => ["/no"]
        ]
    ]
]));
```

# See Unit test class for more detailed usage