<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Aws\Ec2\Ec2Client;


class IpCleaner extends Command
{
    /**
     * 管理者権限付与コマンド
     *
     * @var string
     */
    protected $signature = 'ip:clean';

    /**
     * 説明文
     *
     * @var string
     */
    protected $description = '登録されているGIPを解除する';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $client = new Ec2Client(['region' => env('AWS_REGION'), 'version' => '2015-10-01']);
        $groups = $client->describeSecurityGroups(
            ['GroupIds' => [env('AWS_SG_ID')]]
        )['SecurityGroups'];
        foreach ($groups[0]['IpPermissions'] as $permission) {
            if ($permission['ToPort'] != '22') {
                continue;
            }
            $cidrIps = [];
            foreach ($permission['IpRanges'] as $range) {
                $cidrIps[] = $range['CidrIp'];
            }
            foreach ($cidrIps as $gip) {
                $client->revokeSecurityGroupIngress([
                    'GroupId' => env('AWS_SG_ID'),
                    'FromPort' => 22,
                    'ToPort' => 22,
                    'IpProtocol' => 'TCP',
                    'CidrIp' => $gip,
                ]);
            }
        }

        return true;
    }
}