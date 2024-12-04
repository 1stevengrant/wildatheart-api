<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;

class ImportAmazonBooks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-amazon-books';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports Amazon Books into the Books collection.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Importing Amazon Books...');

        // get all book ASINs to import
        $allASINs = $this->getAllASINs();

        // process ASIN array in chunks of 10 (PAAPI limit)
        foreach (array_chunk($allASINs, 10) as $chunk) {

            // get Amazon items by ASIN
            $items = $this->getAmazonItemsByASIN($chunk);

            // process each item
            foreach ($items as $item) {
                $publish_date = date_create($item->{'ItemInfo'}->{'ContentInfo'}->{'PublicationDate'}->{'DisplayValue'});
                $title = $item->{'ItemInfo'}->{'Title'}->{'DisplayValue'};

                $this->info('ASIN: '.$item->{'ASIN'});
                $this->info('  title: '.$title);
                $this->info('  published: '.date_format($publish_date, 'Y/m/d'));

                // TODO: add entry to Statamic collection
            }
        }

        $this->info('Done.');
    }

    /**
     * Get all ASINs to import.
     *
     * @return array An array of ASINs (strings)
     */
    private function getAllASINs()
    {
        /**
         * NOTE: I copied these ASINs from list HTML at https://www.amazon.com/shop/ransomedheart on 2024-10-10.
         * (couldn't figure out how to get the lists programmatically with PAAPI...)
         */
        $books_list = [
            '1400208645', // Resilient
            'B08BYZBC8C', // Wild at Heart Expanded Edition
            'B08BZ1BQ5T', // Captivating Expanded Edition
            '1400208661', // Get Your Life Back
            '0718037995', // All Things New
            '0718080874', // Waking the Dead
            '071808098X', // Walking with God
            '0785232117', // Becoming a King
            '071808859X', // Moving Mountains
            '1455525707', // Beautiful Outlaw
            '1400280273', // Fathered by God
            '1400208696', // Defiant Joy
            '1434708411', // Becoming Myself
            '0718080785', // The Journey of Desire
            '0307730212', // Love and War
            '0785273425', // The Sacred Romance
            'B00IMN2VZE', // Free to Live
            '0718080866', // Killing Lions
            '1434708632', // Free to Be Me
            'B00AQ0AEOQ', // Epic
            '0785288767', // You Have What It Takes
            '0718034155', // You Are Captivating
        ];

        $workbooks_list = [
            'B08BZ1DRDL', // Wild at Heart Study Guide, Updated Edition
            '0310135648', // Wild at Heart Field Guide, Revised Edition
            '0310128846', // Captivating Bible Study Guide, Updated Edition
            'B08NHWCBK1', // Captivating Guided Journal, Revised Edition
            '0310097029', // Get Your Life Back Study Guide
            '0310682169', // All Things New Study Guide
            '0310084776', // The Walking with God Study Guide Expanded Edition
            '0310084792', // The Waking the Dead Study Guide Expanded Edition
            '0310115248', // Becoming a King Study Guide
            '0310084814', // The Journey of Desire Study Guide Expanded Edition
            '0785268464', // The Sacred Romance Workbook and Journal
            '0718038495', // Moving Mountains Study Guide
            '0310329213', // Love and War Participant's Guide
            '141854289X', // Fathered by God Participant's Guide (A Band of Brothers Small Group Video)
            '1418500151', // Epic Study Guide
            '0310096901', // Unwavering Bible Study Guide
            '0781409551', // Becoming Myself 8-Session Study Guide
            '1400206723', // Killing Lions Journal
        ];

        $other_list = [
            '0578321009', // A Year with Men: A 12-Month Plan for Your Guys' Group
            '1400234352', // The One Minute Pause Journal
        ];

        $group_study_list = [
            '0310129125', // Wild at Heart Video Series Updated Edition
            '0310127440', // Captivating Video Series Updated Edition
            '0310097037', // Get Your Life Back Video Study
            '0310087651', // All Things New Video Study
            '0718038525', // Moving Mountains Video Study
            '0310115264', // Becoming a King Video Study
            '0310329132', // Love and War Video Study
            '1418528609', // Epic Live DVD
            '0310096928', // Unwavering Video Study
            '143470713X', // Becoming Myself 8-Session DVD
        ];

        $devotionals_list = [
            '140020948X', // Restoration Year: A 365-Day Devotional
            '1434709949', // Beautiful Me: Believing God's Truth about You
            '0781414075', // Beautiful Now: 90 Days of Experiencing God's Dreams for You
            '1400202523', // Knowing the Heart of God: A Year of Devotional Readings to Help You Abide in Him
        ];

        $spanish_list = [
            'B003DESKNO', // Salvaje de corazón
            'B076K4DX62', // Todas las cosas nuevas
            'B01864DTYU', // Mueve montañas
            'B0779M24JS', // Forjado por el padre
            'B00I5QX3YQ', // Mata a tu león
            'B003D87P9K', // Camine con Dios
            '1418599727', // Gozo desafiante
            '1434706222', // Llegando a ser yo misma
            '0789919818', // Amor y guerra
            '0881138088', // Majestuoso
            'B00L88EO14', // El despertar de los muertos
            '0881136484', // El Sagrado Romance Vivamos Mas Cerca De Dios
            'B003917VM2', // Cautivante
            'B003917VHW', // La travesía del corazón salvaje
        ];

        return array_merge(
            $books_list,
            $workbooks_list,
            $other_list,
            $group_study_list,
            $devotionals_list,
            $spanish_list
        );
    }

    /**
     * Get Amazon items by ASIN (Amazon Standard Identification Number) using
     * the Amazon Product Advertising API (PAAPI).
     *
     * PAAPI Docs: https://webservices.amazon.com/paapi5/documentation/
     *
     * Most of this code is from the PAAPI Scratchpad tool:
     * https://webservices.amazon.com/paapi5/scratchpad/index.html
     *
     * @param  array  $ASINs  An array of ASINs (strings)
     * @return array An array of Amazon items
     */
    private function getAmazonItemsByASIN($ASINs)
    {
        $partnerTag = env('AMAZON_PAAPI_PARTNER_TAG');
        $accessKey = env('AMAZON_PAAPI_ACCESS_KEY');
        $secretKey = env('AMAZON_PAAPI_SECRET_KEY');

        $serviceName = 'ProductAdvertisingAPI';
        $region = 'us-east-1';
        $payload = '{'
            .' "Resources": ['
            .'  "Images.Primary.Large",'
            .'  "ItemInfo.ByLineInfo",'
            .'  "ItemInfo.ContentInfo",'
            .'  "ItemInfo.ContentRating",'
            .'  "ItemInfo.Classifications",'
            .'  "ItemInfo.ExternalIds",'
            .'  "ItemInfo.Features",'
            .'  "ItemInfo.ManufactureInfo",'
            .'  "ItemInfo.ProductInfo",'
            .'  "ItemInfo.TechnicalInfo",'
            .'  "ItemInfo.Title",'
            .'  "ItemInfo.TradeInInfo",'
            .'  "ParentASIN"'
            .' ],'
            .' "ItemIds": ['
            .'"'.implode('","', $ASINs).'"'
            .'],'
            .' "PartnerTag": "'.$partnerTag.'",'
            .' "PartnerType": "Associates",'
            .' "Marketplace": "www.amazon.com"'
            .'}';
        $host = 'webservices.amazon.com';
        $uriPath = '/paapi5/getitems';
        $awsv4 = new AwsV4($accessKey, $secretKey);
        $awsv4->setRegionName($region);
        $awsv4->setServiceName($serviceName);
        $awsv4->setPath($uriPath);
        $awsv4->setPayload($payload);
        $awsv4->setRequestMethod('POST');
        $awsv4->addHeader('content-encoding', 'amz-1.0');
        $awsv4->addHeader('content-type', 'application/json; charset=utf-8');
        $awsv4->addHeader('host', $host);
        $awsv4->addHeader('x-amz-target', 'com.amazon.paapi5.v1.ProductAdvertisingAPIv1.GetItems');
        $headers = $awsv4->getHeaders();
        $headerString = '';
        foreach ($headers as $key => $value) {
            $headerString .= $key.': '.$value."\r\n";
        }
        $params = [
            'http' => [
                'header' => $headerString,
                'method' => 'POST',
                'content' => $payload,
            ],
        ];
        $stream = stream_context_create($params);

        $fp = @fopen('https://'.$host.$uriPath, 'rb', false, $stream);

        if (! $fp) {
            throw new Exception('Exception Occurred');
        }
        $response = @stream_get_contents($fp);
        if ($response === false) {
            throw new Exception('Exception Occurred');
        }

        // return array of items
        $responseJSON = json_decode($response);

        return $responseJSON->{'ItemsResult'}->{'Items'};
    }
}

/**
 * Class used to sign requests to Amazon Product Advertising API (PAAPI)
 */
class AwsV4
{
    private $accessKey = null;

    private $secretKey = null;

    private $path = null;

    private $regionName = null;

    private $serviceName = null;

    private $httpMethodName = null;

    private $queryParametes = [];

    private $awsHeaders = [];

    private $payload = '';

    private $HMACAlgorithm = 'AWS4-HMAC-SHA256';

    private $aws4Request = 'aws4_request';

    private $strSignedHeader = null;

    private $xAmzDate = null;

    private $currentDate = null;

    public function __construct($accessKey, $secretKey)
    {
        $this->accessKey = $accessKey;
        $this->secretKey = $secretKey;
        $this->xAmzDate = $this->getTimeStamp();
        $this->currentDate = $this->getDate();
    }

    public function setPath($path)
    {
        $this->path = $path;
    }

    public function setServiceName($serviceName)
    {
        $this->serviceName = $serviceName;
    }

    public function setRegionName($regionName)
    {
        $this->regionName = $regionName;
    }

    public function setPayload($payload)
    {
        $this->payload = $payload;
    }

    public function setRequestMethod($method)
    {
        $this->httpMethodName = $method;
    }

    public function addHeader($headerName, $headerValue)
    {
        $this->awsHeaders[$headerName] = $headerValue;
    }

    private function prepareCanonicalRequest()
    {
        $canonicalURL = '';
        $canonicalURL .= $this->httpMethodName."\n";
        $canonicalURL .= $this->path."\n"."\n";
        $signedHeaders = '';
        foreach ($this->awsHeaders as $key => $value) {
            $signedHeaders .= $key.';';
            $canonicalURL .= $key.':'.$value."\n";
        }
        $canonicalURL .= "\n";
        $this->strSignedHeader = substr($signedHeaders, 0, -1);
        $canonicalURL .= $this->strSignedHeader."\n";
        $canonicalURL .= $this->generateHex($this->payload);

        return $canonicalURL;
    }

    private function prepareStringToSign($canonicalURL)
    {
        $stringToSign = '';
        $stringToSign .= $this->HMACAlgorithm."\n";
        $stringToSign .= $this->xAmzDate."\n";
        $stringToSign .= $this->currentDate.'/'.$this->regionName.'/'.$this->serviceName.'/'.$this->aws4Request."\n";
        $stringToSign .= $this->generateHex($canonicalURL);

        return $stringToSign;
    }

    private function calculateSignature($stringToSign)
    {
        $signatureKey = $this->getSignatureKey($this->secretKey, $this->currentDate, $this->regionName, $this->serviceName);
        $signature = hash_hmac('sha256', $stringToSign, $signatureKey, true);
        $strHexSignature = strtolower(bin2hex($signature));

        return $strHexSignature;
    }

    public function getHeaders()
    {
        $this->awsHeaders['x-amz-date'] = $this->xAmzDate;
        ksort($this->awsHeaders);

        // Step 1: CREATE A CANONICAL REQUEST
        $canonicalURL = $this->prepareCanonicalRequest();

        // Step 2: CREATE THE STRING TO SIGN
        $stringToSign = $this->prepareStringToSign($canonicalURL);

        // Step 3: CALCULATE THE SIGNATURE
        $signature = $this->calculateSignature($stringToSign);

        // Step 4: CALCULATE AUTHORIZATION HEADER
        if ($signature) {
            $this->awsHeaders['Authorization'] = $this->buildAuthorizationString($signature);

            return $this->awsHeaders;
        }
    }

    private function buildAuthorizationString($strSignature)
    {
        return $this->HMACAlgorithm.' '.'Credential='.$this->accessKey.'/'.$this->getDate().'/'.$this->regionName.'/'.$this->serviceName.'/'.$this->aws4Request.','.'SignedHeaders='.$this->strSignedHeader.','.'Signature='.$strSignature;
    }

    private function generateHex($data)
    {
        return strtolower(bin2hex(hash('sha256', $data, true)));
    }

    private function getSignatureKey($key, $date, $regionName, $serviceName)
    {
        $kSecret = 'AWS4'.$key;
        $kDate = hash_hmac('sha256', $date, $kSecret, true);
        $kRegion = hash_hmac('sha256', $regionName, $kDate, true);
        $kService = hash_hmac('sha256', $serviceName, $kRegion, true);
        $kSigning = hash_hmac('sha256', $this->aws4Request, $kService, true);

        return $kSigning;
    }

    private function getTimeStamp()
    {
        return gmdate("Ymd\THis\Z");
    }

    private function getDate()
    {
        return gmdate('Ymd');
    }
}
