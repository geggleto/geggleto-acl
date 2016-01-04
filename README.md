# geggleto-acl
Provides a ACL repository and Middleware using Zend/Permissions/Acl library

# Usage Example

```php
//Define or Pull your ACL's into the following format
/*
$aclList = [
  "resources" => [list of resources names]
  "roles" => [list of role names]
  "assignments" => [
       "allow" => [
           "rolename" => [list of resources]
       ]
       "deny" => [
           "rolename" => [list of resources]
       ]
  ]
];
*/

$app->add(\Geggleto\Acl\AclRepository($_SESSION['user_id'], [
"resources" => ['/'],
"roles" => ['guest'],
"assignments" => [
  "allow" => [ "guest" => [ '/' ]] 
]
]));
```
