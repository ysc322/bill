<?php
namespace mylib;

use JPush\Client as JPush;


class jgpush {
	private static $app_key = '89acd43ce3261046cf596029';
	private static $secret = '5ee6cc410e9d1a502b76c06d';
	public static function pushMsg() {
		$client = new JPush(self::$app_key, self::$secret);
		$pusher = $client->push();
		$pusher->setPlatform('all');
		$pusher->addAllAudience();
		$pusher->setNotificationAlert('i红包任务更新了，瞧瞧有没有可以撸的？');
		try {
			$pusher->send();
		} catch (\JPush\Exceptions\JPushException $e) {
			// try something else here
			print $e;
		}
	}
}
