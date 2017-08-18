<?php

use PragmaRX\Google2FA\Google2FA;
use SparkPost\SparkPost;
use GuzzleHttp\Client;
use Http\Adapter\Guzzle6\Client as GuzzleAdapter;
use \Bitpay\Client\Client as BitpayClient;

use \Bitpay\Client\Adapter\CurlAdapter as BitpayCurlAdapter;
use \Bitpay\Network\Testnet as BitpayTestnet;
use \Bitpay\Client\Request as BitpayRequest;
use Bitpay\Bitpay;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


Route::get('/tweet', function()
{
    //URL::asset('/background')
    //$uploaded_media = Twitter::uploadMedia(['media' => File::get(public_path('filename.jpg'))]);

    return Twitter::postTweet(['status' => 'Post from an api lang eh ', 'format' => 'json']);
});


Route::get('/2fa', function(){

    $google2fa = new Google2FA();
    $two_factor_val = 'NQXC4GGEHG4ZPGJK'; //strictly only to this user //$google2fa->generateSecretKey(); add per user salt for extra security

    $google2fa_url = $google2fa->getQRCodeGoogleUrl(
        'YourCompany',
        'email@email.com',
        $two_factor_val
    );

    return view('tests.two-fa')
        ->with('google2fa_url', $google2fa_url)
        ->with('two_factor_val', $two_factor_val);
    //return ;
})->middleware(['2fa']);


Route::post('/google2fa/authenticate', function(\Illuminate\Http\Request $request){

    $two_factor_val = 'NQXC4GGEHG4ZPGJK'; //user hash
    $secret = $request->input('secret'); //numbers inputted
    $google2fa = new Google2FA();
    $one_time_password = $request->input('one_time_password');

    $window = 8; //default = 4 //8 keys (respectively 4 minutes) past and future

    $valid = $google2fa->verifyKey($two_factor_val, $one_time_password, $window);

    $message = $valid ? '2-factor Authentication is valid!' : 'Invalid 2-FA' ;

    $google2fa_url = $google2fa->getQRCodeGoogleUrl(
        'YourCompany',
        'email@email.com', //agaldones@olx.ph
        $two_factor_val
    );

    return view('tests.two-fa')
        ->with('google2fa_url', $google2fa_url)
        ->with('two_factor_val', $two_factor_val)
        ->withMessage($message);

});


Route::get('/spark', function(\Illuminate\Http\Request $request){
    $httpClient = new GuzzleAdapter(new Client());
    $sparky = new SparkPost($httpClient, ['key' => config('services.sparkpost')['secret'] ]);


    $sparky->setOptions([
        'protocol' => 'https',
        'port'  => 443,
        'async' => true
    ]);

    $promise = $sparky->transmissions->post([
        'content'   => [
            'from'  =>  [
                'name'  =>  'Team',
                'email' =>  'from@parkpostbox.com',
            ],
            'subject'   =>  'First Mailing from L5 Test',
            'html'      =>  '<html><body><h1>Congratulations, {{name}}!</h1><p>You just sent your very first mailing!</p></body></html>',
            'text'      =>  'Congratulations, {{name}}! You just sent your very first mailing',
        ],
        'substitution_data' =>  ['name' =>  'Xander'],
        'recipients'    =>  [
            [
                'address'   =>  [
                    'name'  =>  'Alex',
                    'email' =>  'agaldones@olx.ph'
                ]
            ]
        ],
        'cc'    =>  [
            [
                'address'   =>  [
                    'name'  =>  'XG',
                    'email' =>  'alexander.galdones@gmail.com'
                ]
            ]
        ],
        'bcc'   =>  [
            [
                'address'   =>  [
                    'name'  =>  'xanderyui',
                    'email' =>  'alexanderyuigreat@gmail.com'
                ]
            ]
        ]
    ]);

    $response = $promise->wait();

    echo 'Status code: ' . $response->getStatusCode();
});


Route::get('/loaderio-53811d5b169448791a6132843346eeef', function() {
    echo '53811d5b169448791a6132843346eeef';
});

Route::get('/loaderio-53811d5b169448791a6132843346eeef.html'  , function() {
    echo '53811d5b169448791a6132843346eeef';
});

Route::get('/loaderio-53811d5b169448791a6132843346eeef.txt'  , function() {
    echo '53811d5b169448791a6132843346eeef';
});

Route::get('/bitpay/rate', function() {
    $client = new BitpayClient();
    $client->setAdapter(new BitpayCurlAdapter());
    $client->setNetwork(new BitpayTestnet());

    $request = new BitpayRequest();
    $request->setHost('test.bitpay.com');
    $request->setMethod(BitpayRequest::METHOD_GET);
    $request->setPath('rates/USD');

    $response = $client->sendRequest($request);

    $data = json_encode($response->getBody(), true);
    var_dump($data);
});

