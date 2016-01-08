<?php
/**
 * Created by PhpStorm.
 * User: Glenn
 * Date: 2016-01-04
 * Time: 3:35 PM
 */

namespace Geggleto\Acl;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Exception\InvalidArgumentException;
use Zend\Permissions\Acl\Role\GenericRole as Role;
use Zend\Permissions\Acl\Resource\GenericResource as Resource;

class AclRepository
{
    /**
     * @var \Zend\Permissions\Acl\Acl
     */
    protected $acl;

    /**
     * @var array
     */
    protected $role;

    /**
     * @return array
     */
    public function getRole ()
    {
        return $this->role;
    }


    protected $handler;

    /**
     * AclRepository constructor.
     *
     * the user_id injected should be a "role name"
     * Each Role would actually be a user.
     * Then each User can have 1 or more different roles
     * which then correspond to one or more different resources
     *
     * $config = [
     * "resources" => [list of resources names]
     * "roles" => [list of role names]
     * "assignments" => [
     *      "allow" => [
     *          "rolename" => [list of resources]
     *      ]
     *      "deny" => [
     *          "rolename" => [list of resources]
     *      ]
     * ]
     *
     *
     * @param array $role This is the current Role(s) you are testing for
     * @param array $config
     */
    public function __construct(array $role, array $config = [])
    {
        $this->acl = new Acl();
        $this->role = $role;

        if (isset($config['resources'])) {
            foreach ($config['resources'] as $resource) {
                $this->acl->addResource($this->makeResource($resource));
            }
        }

        if (isset($config['roles'])) {
            foreach ($config['roles'] as $role) {
                $this->acl->addRole($this->makeRole($role));
            }
        }

        if (isset($config['assignments'])) {
            foreach ($config['assignments']['allow'] as $role => $resources) {
                foreach ($resources as $resource) {
                    $this->addAllow($role, $resource);
                }
            }

            foreach ($config['assignments']['deny'] as $role => $resources) {
                foreach ($resources as $resource) {
                    $this->addDeny($role, $resource);
                }
            }
        }

        $this->handler = function (ServerRequestInterface $requestInterface, AclRepository $aclRepo) {

            $route = $requestInterface->getAttribute('route');
            var_dump($route);
            var_dump($requestInterface->getAttributes());

            if (!empty($route)) {
                foreach ($aclRepo->getRole() as $role) {
                    if ($aclRepo->isAllowed($role, $route->getPattern())) {
                        return true;
                    }
                }
            }
            return false;
        };

    }

    public function setCustomHandler(callable $handler) {
        $this->handler = $handler;
    }

    /**
     * @return \Zend\Permissions\Acl\Acl
     */
    public function getAcl() {
        return $this->acl;
    }

    /**
     * @param string $id
     * @return \Zend\Permissions\Acl\Resource\GenericResource
     */
    public function makeResource($id = '') {
        return new Resource($id);
    }

    /**
     * @param string $id
     * @return \Zend\Permissions\Acl\Role\GenericRole
     */
    public function makeRole($id = '') {
        return new Role($id);
    }

    /**
     * @param \Zend\Permissions\Acl\Role\GenericRole $role
     * @param array                                  $parents
     */
    public function addRole(Role $role, array $parents = []) {
        $this->acl->addRole($role, $parents);
    }

    /**
     * @param string $role_id
     * @param string $resource_id
     */
    public function addDeny($role_id = '', $resource_id = '') {
        $this->acl->deny($role_id, $resource_id);
    }

    /**
     * @param string $role_id
     * @param string $resource_id
     */
    public function addAllow($role_id = '', $resource_id = '') {
        $this->acl->allow($role_id, $resource_id);
    }

    /**
     * @param string $role_id
     * @param string $resource_id
     * @return bool
     */
    public function isAllowed($role_id = '', $resource_id = '') {
        return $this->acl->isAllowed($role_id, $resource_id);
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $requestInterface
     * @param \Psr\Http\Message\ResponseInterface      $responseInterface
     * @param callable                                 $next
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke(ServerRequestInterface $requestInterface, ResponseInterface $responseInterface, callable $next) {
        $allowed = false;

        $route = '/' . ltrim($requestInterface->getUri()->getPath(), '/');

        try {
            foreach ($this->role as $role) {
                if ($this->isAllowed($role, $route)) {
                    $allowed = true;
                }
            }
        } catch (InvalidArgumentException $iae) {
            $fn = $this->handler;
            $allowed = $fn($requestInterface, $this);
        }

        if ($allowed) {
            return $next($requestInterface, $responseInterface);
        } else {
            return $responseInterface->withStatus(401);
        }
    }
}