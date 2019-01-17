[![Build Status](https://travis-ci.org/geggleto/geggleto-acl.svg)](https://travis-ci.org/geggleto/geggleto-acl)

# geggleto-acl
Provides a ACL repository and Middleware using Zend/Permissions/Acl library
PSR-7 Compliant

- Blog post on this package
- https://glenneggleton.com/page/psr-7-permissions

# How it works
- Resources are end-points
- Roles are a group of resources
- You can either allow or deny those roles.

The roles a user has are loaded into the AclRepo on every request. I suggest loading them into a session variable rather than pulling them from storage everytime (usage case depending).

The current route is then inspected and compared to the list of accessable resources in a middleware. a 401 is returned if a user is not allowed. If the user is allowed the application is allowed to continue.

By default no message body is provided on the 401, and if you require a page to be rendered then you will need to write your own middleware.

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
    $aclRepo = $this->get(AclRepository::class); //In Slim 3 the container is bound to function definitions
    $allowed = false; // We assume that the user cannot access the route

    $route = '/' . ltrim($request->getUri()->getPath(), '/'); //We construct our path

    try { //Check here... This will pass when a route is simple and there is no route parameters
        $allowed = $aclRepo->isAllowedWithRoles($aclRepo->getRole(), $route);
    } catch (InvalidArgumentException $iae) { //This is executed in cases where there is a route parameters... /user/{id:} 
        $fn = function (ServerRequestInterface $requestInterface, AclRepository $aclRepo) {
            //This will likely only work in Slim 3... This requires the determineRouteBeforeAppMiddleware => true to be set in the container
            $route = $requestInterface->getAttribute('route'); // Grab the route to get the pattern
            if (!empty($route)) {
                foreach ($aclRepo->getRole() as $role) {
                    if ($aclRepo->isAllowed($role, $route->getPattern())) { // check to see fi the user can access the pattern
                        return true; //Is allowed
                    }
                }
            }
            return false;
        };

        $allowed = $fn($request, $aclRepo); // Execute the fail-safe
    }

    if ($allowed) {
        return $next($request, $res);
    } else {
        return $res->withStatus(401); //Is not allowed. if you need to render a template then do that.
    }
});
```


## White listing
You may add a URI path for white listing. The whitelisting is based upon `strpos()` so you may use a URI fragment to whitelist a whole class of URIs.
With this it is possible to whitelist URIs by accident.

Example:
```php

$acl = new Acl();
$acl->addWhitelistItem('/api');
```

In this example any URI with `/api` will be whitelisted. 
- `/api/*`
- `/myexample/api/*`
