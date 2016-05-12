<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/
use Illuminate\Http\Request;
use Aws\Ec2\Ec2Client;


$app->get('/', function () use ($app) {
    return redirect('/register');
});


/**
 * GIP登録系
 */
$app->group(['prefix' => 'register'], function () use ($app) {
    $app->get('/', function (Request $request) {
        $userGIP = $request->ip();

        return view('register.form', ['userGIP' => $userGIP]);
    });

    $app->post('/', function (Request $request) {
        $messages = [];
        $userGIP = $request->ip();
        $postedGIP = $request->input('addr');

        if ($userGIP != $postedGIP) {
            $messages[] = '登録しようとしたアドレスが一致しません';
        }

        // 登録処理
        $gip = $userGIP . '/32';
        $client = new Ec2Client(['region' => env('AWS_REGION'), 'version' => '2015-10-01']);
        $groups = $client->describeSecurityGroups(
            ['GroupIds' => [env('AWS_SG_ID')]]
        )['SecurityGroups'];
        $cidrIps = [];
        foreach ($groups[0]['IpPermissions'] as $permission) {
            if ($permission['ToPort'] != '22') {
                continue;
            }
            foreach ($permission['IpRanges'] as $range) {
                $cidrIps[] = $range['CidrIp'];
            }
        }
        if (!in_array($gip, $cidrIps)) {
            $client->authorizeSecurityGroupIngress([
                'GroupId' => env('AWS_SG_ID'),
                'FromPort' => 22,
                'ToPort' => 22,
                'IpProtocol' => 'tcp',
                'CidrIp' => $gip,
            ]);
        }
        $messages[] = '登録しました';

        return view('register.result', ['messages' => $messages]);
    });

});
