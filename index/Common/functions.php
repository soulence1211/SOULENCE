<?php
defined('IN_FW') or die('deny');
/**
 *  auther: soulence
 *	date : 2014-12-05 13:23:20
 *  这里可以写本项目的一些公用的方法
 */
/**
 * 验证手机号码
 */
function CheckMobile($mobile){
	return (strlen($mobile)==11 && preg_match("/1[3458]{1}\d{9}$/",$mobile))?true:false;
}

/**
 * 生成订单编号 用户ID  订单生成时间
 */
function encodeOrderCode($nUserId, $sOrderAddTime) {
	$sCircle = '5814703692'; // 数字转换秘钥
	$sStartTime = '2012-12-12 12:12:12'; // 起始日期
	$sTime = strtotime($sOrderAddTime) - strtotime($sStartTime);
	$sTime = str_pad($sTime, 9, '0', STR_PAD_LEFT);
	$sUserId = str_pad($nUserId, 6, '0', STR_PAD_LEFT);

	$sInfo = $sUserId.$sTime;
	$nLen = strlen($sInfo);
	$sCode = '';
	for ($i = 0; $i < $nLen; $i++) {
		$sCode .= $sCircle{$sInfo{$i}};
	}
	return $sCode;
}

/**
 * 生成礼券代码，算法：
 * 1，生成8位随机数，将其乘以9作为左8位
 * 2，将随机数凑满8位作为卡片
 * 3，将礼券ID凑满8位作为右8位
 * 4，把右8位数字的每一位，以10为一圈增大N格，N是卡片上的对应数字
 */
function encodeGiftCode($nGiftId) {
	$nPre = rand(0, 11111111);
	$sPre = str_pad($nPre*9, 8, '0', STR_PAD_LEFT);
	$sAAA = str_pad($nPre, 8, '0', STR_PAD_LEFT);
	$sBBB = str_pad($nGiftId, 8, '0', STR_PAD_LEFT);
	$sFix = '';
	for ($i = 0; $i < 8; $i++) {
		$sFix .= ($sAAA{$i} + $sBBB{$i})%10;
	}
	return $sPre.$sFix;
}

/**
 * 时间与现在的差距
 */
function overtime($sTime) {
	static $sNowStamp;
	if (!isset($sNowStamp)) {
		$sNowStamp = time();
	}
	$nStamp = strtotime($sTime);
	if (!(0 < $nStamp)) {
		return '某时';
	}

	if ($nStamp > $sNowStamp) {
		$sFix = '后';
		$nSecond = $nStamp - $sNowStamp;
	}
	else {
		$sFix = '前';
		$nSecond = $sNowStamp - $nStamp;
	}
	if (86400 < $nSecond) {
		return floor($nSecond/86400).'天'.$sFix;
	}
	elseif (3600 < $nSecond) {
		return floor($nSecond/3600).'小时'.$sFix;
	}
	elseif (60 < $nSecond) {
		return floor($nSecond/60).'分钟'.$sFix;
	}
	else {
		return floor($nSecond).'秒'.$sFix;
	}
}
