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

# Dynamic Routes
In the case where your resource changes, it is possible to still correctly match by setting a resources with a Route Pattern.
By default the system will inspect the $request's 'route' attribute and this Object should return the route pattern with ->getPatter();
Out of the box this will work with Slim 3 routes if you have turned on the 'determineRouteBeforeAppMiddleware' => true option.

Example Config:
```php
return [
    "resources" => ["/", "/login", "/grid", "/404", "/logout", "/roles", "/roles/{pein}"],
    "roles" => ["guest", "grid", "roles"],
    "assignments" => [
        "allow" => [
            "guest" => ["/", "/404", "/login"],
            "grid" => [ '/grid', '/logout' ],
            "roles" => ['/roles', '/roles/{pein}']
        ],
        "deny" => []
    ]
];
```

If this does not fit your usage, feel free to override the default handler by setting your own via `setHandler(callable)`

## Middleware
You can use the repo class directly which contains this code block... or modify this code block to suit your needs.
```php

$app->add(function (Request $request, Response $res, $next) {
    /** @var $aclRepo AclRepository */
    $aclRepo = $this->get(AclRepository::class);
    $allowed = false;

    $route = '/' . ltrim($request->getUri()->getPath(), '/');
    var_dump($route);

    try {
        $allowed = $aclRepo->isAllowedWithRoles($aclRepo->getRole(), $route);
    } catch (InvalidArgumentException $iae) {
        $fn = function (ServerRequestInterface $requestInterface, AclRepository $aclRepo) {

            $route = $requestInterface->getAttribute('route');
            if (!empty($route)) {
                foreach ($aclRepo->getRole() as $role) {
                    if ($aclRepo->isAllowed($role, $route->getPattern())) {
                        return true;
                    }
                }
            }
            return false;
        };

        $allowed = $fn($request, $aclRepo);
    }

    if ($allowed) {
        return $next($request, $res);
    } else {
        return $res->withStatus(401);
    }
});
```
