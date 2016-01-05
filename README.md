# geggleto-acl
Provides a ACL repository and Middleware using Zend/Permissions/Acl library

# Usage Example

```php
//Define or Pull your ACL's into the following format
/*
        $aclList = [
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
$app->add(\Geggleto\Acl\AclRepository("guest", 
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
