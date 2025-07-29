<?php

use PHPUnit\Framework\TestCase;
use App\Controller\CheckoutController;

class CheckoutControllerTest extends TestCase
{
    private function getUserStub(): object
    {
        return new class {
            public function getEmail() { return 'customer@example.com'; }
            public function getFirstname() { return 'John'; }
            public function getLastname() { return 'Doe'; }
            public function getStreet() { return 'Main St 1'; }
            public function getZip() { return '12345'; }
            public function getCity() { return 'Test City'; }
            public function getCountryCode() { return 'US'; }
        };
    }

    private function getController($user): CheckoutController
    {
        return new class($user) extends CheckoutController {
            private $user;
            public function __construct($user) { $this->user = $user; }
            protected function getUser() { return $this->user; }
            public function callFillDeliveryAddressFromCustomer($deliveryAddress)
            {
                return $this->fillDeliveryAddressFromCustomer($deliveryAddress);
            }
        };
    }

    public function testFillDeliveryAddressFromCustomerUsesUserDataWhenNull(): void
    {
        $user = $this->getUserStub();
        $controller = $this->getController($user);

        $result = $controller->callFillDeliveryAddressFromCustomer(null);

        $expected = [
            'email' => 'customer@example.com',
            'firstname' => 'John',
            'lastname' => 'Doe',
            'street' => 'Main St 1',
            'zip' => '12345',
            'city' => 'Test City',
            'countryCode' => 'US',
        ];

        $this->assertSame($expected, $result);
    }

    public function testFillDeliveryAddressFromCustomerPreservesExistingValues(): void
    {
        $user = $this->getUserStub();
        $controller = $this->getController($user);

        $address = [
            'firstname' => 'Existing',
            'zip' => '99999',
        ];

        $result = $controller->callFillDeliveryAddressFromCustomer($address);

        $this->assertSame('Existing', $result['firstname']);
        $this->assertSame('99999', $result['zip']);
        $this->assertSame('customer@example.com', $result['email']);
        $this->assertSame('Doe', $result['lastname']);
        $this->assertSame('Main St 1', $result['street']);
        $this->assertSame('Test City', $result['city']);
        $this->assertSame('US', $result['countryCode']);
    }
}
