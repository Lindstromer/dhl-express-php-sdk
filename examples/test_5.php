<?php
require_once("vendor/autoload.php");

use adiyya\DHLExpress\Ship;
use adiyya\DHLExpress\Address;
use adiyya\DHLExpress\Shipper;
use adiyya\DHLExpress\Contact;
use adiyya\DHLExpress\Packages;
use adiyya\DHLExpress\Recipient;
use adiyya\DHLExpress\Commodities;
use adiyya\DHLExpress\Credentials;
use adiyya\DHLExpress\ShipmentInfo;
use adiyya\DHLExpress\SpecialService;
use adiyya\DHLExpress\ShipmentRequest;
use adiyya\DHLExpress\RequestedPackage;
use adiyya\DHLExpress\RequestedShipment;
use adiyya\DHLExpress\InternationalDetail;

$credentials = new Credentials(true);
$credentials
    ->setUsername('YOUR-USERNAME')
    ->setPassword('YOUR-PASSWORD');

$specialService = new SpecialService();
$specialService->setServiceType(SpecialService::INTERNATIONAL_DUTY_DUTIES_AND_TAXES_PAID);

$shipmentInfo = new ShipmentInfo();
$shipmentInfo
    ->setDropOffType(ShipmentInfo::DROP_OFF_TYPE_REGULAR_PICKUP)
    ->setServiceType(ShipmentInfo::SERVICE_TYPE_EXPRESS_WORLDWIDE_NON_DOC)
    ->setAccount('YOUR-ACCOUNT')
    ->setCurrency('EUR')
    ->setUnitOfMeasurement(ShipmentInfo::UNIT_OF_MEASRUREMENTS_KG_CM)
    ->setLabelType(ShipmentInfo::LABEL_TYPE_PDF)
    ->setLabelTemplate(ShipmentInfo::LABEL_TEMPLATE_ECOM26_A6_002)
    ->addSpecialService($specialService);

$shipperContact = new Contact();
$shipperContact
    ->setPersonName('Max Mustermann')
    ->setCompanyName('Acme Inc.')
    ->setPhoneNumber('0123456789')
    ->setEmailAddress('max.mustermann@example.com');

$shipperAddress = new Address();
$shipperAddress
    ->setStreetLines('Hauptstrasse 1')
    ->setCity('Berlin')
    ->setPostalCode('10317')
    ->setCountryCode('DE');

$shipper = new Shipper();
$shipper
    ->setContact($shipperContact)
    ->setAddress($shipperAddress);

$recipientContact = new Contact();
$recipientContact
    ->setPersonName('Max Mustermann')
    ->setCompanyName('Acme Inc.')
    ->setPhoneNumber('0123456789')
    ->setEmailAddress('max.mustermann@example.com');

$recipientAddress = new Address();
$recipientAddress
    ->setStreetLines('Zentralstrasse 50-62')
    ->setCity('Zürich')
    ->setPostalCode('8003')
    ->setCountryCode('CH');

$recipient = new Recipient();
$recipient
    ->setContact($recipientContact)
    ->setAddress($recipientAddress);

$ship = new Ship();
$ship
    ->setShipper($shipper)
    ->setRecipient($recipient);

$package1 = new RequestedPackage();
$package1
    ->setWeight(2)
    ->setDimensions(1, 2, 3)
    ->setCustomerReferences('test 1');

$packages = new Packages();
$packages
    ->addRequestedPackage($package1);

$commodities = new Commodities();
$commodities
    ->setDescription('Stuff')
    ->setCustomsValue(10);

// The InternationalDetail seems to be required even if its a domestic package
$internationalDetail = new InternationalDetail();
$internationalDetail
    ->setCommodities($commodities)
    ->setContent(InternationalDetail::CONTENT_NON_DOCUMENTS);

$timestamp = new DateTime("now", new DateTimeZone("Europe/Berlin"));
$timestamp->modify('+3 days');

$requestedShipment = new RequestedShipment();
$requestedShipment
    ->setShipmentInfo($shipmentInfo)
    ->setShipTimestamp($timestamp)
    ->setPaymentInfo(RequestedShipment::PAYMENT_INFO_DELIVERY_DUTY_PAID)
    ->setShip($ship)
    ->setPackages($packages)
    ->setInternationalDetail($internationalDetail);

$shipment = new ShipmentRequest($credentials);
$shipment->setRequestedShipment($requestedShipment);
$response = $shipment->send();

if ($response->isSuccessful()) {
    print_r($response->getTrackingNumber());
    file_put_contents('label_5.pdf', base64_decode($response->getLabel()));
} else {
    print_r($response->getErrors());
}