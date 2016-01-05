<?php
use Geggleto\Acl\AclRepository;
use Geggleto\Acl\Stubs\RequestStub;
use Geggleto\Acl\Stubs\ResponseStub;
use Geggleto\Acl\Stubs\UriStub;

/**
 * Created by PhpStorm.
 * User: Glenn
 * Date: 2016-01-05
 * Time: 11:02 AM
 */
class AclRepositoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var AclRepository
     */
    protected $user1;

    /**
     * @var AclRepository
     */
    protected $user2;

    /**
     * @var AclRepository
     */
    protected $guest;

    public function setUp ()
    {
        $this->user1 = new AclRepository('user1', $this->configProvider());
        $this->user2 = new AclRepository('user2', $this->configProvider());
        $this->guest = new AclRepository('guest', $this->configProvider());
    }

    public function configProvider() {
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
        return $aclList;
    }

    public function getClosure() {
        $context = $this;
        $closure = function ($req, $res) use ($context) {
            return $context->mockResponse(200);
        };
        return $closure;
    }

    public function mockUri($path = '') {
        $stub = $this->getMockBuilder(UriStub::class)->getMock();

        $stub->method('getPath')->willReturn($path);

        return $stub;
    }

    public function mockRequest($path = '') {
        $stub = $this->getMockBuilder(RequestStub::class)->getMock();
        $stub->method('getUri')->willReturn($this->mockUri($path));
        return $stub;
    }


    public function mockResponse($statusCode = 200) {
        $stub = $this->getMockBuilder(ResponseStub::class)->getMock();
        $stub->method('getStatusCode')->willReturn($statusCode);
        $stub->method('withStatus')->will($this->returnSelf());
        return $stub;
    }

    public function testAclRepoUser1_root() {
        $reqRoot = $this->mockRequest('/');
        $res = $this->mockResponse(401);


        $acl = $this->user1;
        $output = $acl($reqRoot, $res, $this->getClosure());

        $this->assertEquals(200, $output->getStatusCode());

    }

    public function testAclRepoUser1_no() {
        $reqRoot = $this->mockRequest('/no');
        $res = $this->mockResponse(401);

        $acl = $this->user1;
        $output = $acl($reqRoot, $res, $this->getClosure());

        $this->assertEquals(200, $output->getStatusCode());
    }

    public function testAclRepoUser1_yes() {
        $reqRoot = $this->mockRequest('/yes');
        $res = $this->mockResponse(401);

        $acl = $this->user1;
        $output = $acl($reqRoot, $res, $this->getClosure());

        $this->assertEquals(401, $output->getStatusCode());
    }

    public function testAclRepoGuest_root() {
        $reqRoot = $this->mockRequest('/');
        $res = $this->mockResponse(401);


        $acl = $this->guest;
        $output = $acl($reqRoot, $res, $this->getClosure());

        $this->assertEquals(200, $output->getStatusCode());

    }

    public function testAclRepoGuest_no() {
        $reqRoot = $this->mockRequest('/no');
        $res = $this->mockResponse(401);

        $acl = $this->guest;
        $output = $acl($reqRoot, $res, $this->getClosure());

        $this->assertEquals(401, $output->getStatusCode());
    }

    public function testAclRepoGuest_yes() {
        $reqRoot = $this->mockRequest('/yes');
        $res = $this->mockResponse(401);

        $acl = $this->guest;
        $output = $acl($reqRoot, $res, $this->getClosure());

        $this->assertEquals(401, $output->getStatusCode());
    }

    public function testAclRepoUser2_root() {
        $reqRoot = $this->mockRequest('/');
        $res = $this->mockResponse(401);


        $acl = $this->user2;
        $output = $acl($reqRoot, $res, $this->getClosure());

        $this->assertEquals(200, $output->getStatusCode());

    }

    public function testAclRepoUser2_no() {
        $reqRoot = $this->mockRequest('/no');
        $res = $this->mockResponse(401);

        $acl = $this->user2;
        $output = $acl($reqRoot, $res, $this->getClosure());

        $this->assertEquals(401, $output->getStatusCode());
    }

    public function testAclRepoUser2_yes() {
        $reqRoot = $this->mockRequest('/yes');
        $res = $this->mockResponse(401);

        $acl = $this->user2;
        $output = $acl($reqRoot, $res, $this->getClosure());

        $this->assertEquals(200, $output->getStatusCode());
    }


}
