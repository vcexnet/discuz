<?php
require_once "base.php";
if(empty($uid)) showmessage('to_login', 'member.php?mod=logging&action=login', array(), array('showmsg' => true, 'login' => 1));
if ($action == 'cash') {
	$exchangeData = getExchange();
	if(submitcheck('addsubmit')){
		$cash_address = $_POST['cash_address'];
		if(!$cash_address){			
			showmessage("���ֵ�ַ������д");
		}
		$amount = dintval($_POST['money']);
		$eac = round($amount/$csetting['moneybl']/$exchangeData,3);
		list($msec, $sec) = explode(' ', microtime());
		$msectime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
		$vo = array(
			"uid"			=>	$uid,
			"order_id"		=>	$_SERVER['SERVER_NAME']."_withdraw_".$uid.'_'.$msectime.rand(100000,999999),
			"amount"		=>	$amount,
			"eac"			=>	$eac,
			"create_time"	=>	time(),
			"last_time"		=>	time(),
			"pay_time"		=>	0,
			'address'		=>	$cash_address,
			"type"			=>	'cash',
			"status"		=>	'wait',
		);
		$moneymin =  ($csetting['moneymin'] > 1) ? $csetting['moneymin'] : 1;
		if ($amount < $moneymin || $amount > 99999 || $amount == '') {
			showmessage("��ֵ����С��".$moneymin."�����99999",'index.php');
		}
		$d = array();
		$d['extcredits'.$csetting['moneytype']] = 0-$vo['amount'];
		updatemembercount($vo['uid'],$d,false,'cash','0','','���ֿ۷�','���ֿ۷�');

		DB::insert('eacpay_order', $vo, true);
		$addressVo = DB::fetch_first('select * from '.DB::table("eacpay_address").' where uid = '.$uid);
		if(!$addressVo){
			DB::insert('eacpay_address', array(
				'uid'	=>	$uid,
				'address'	=>	$cash_address
			));
		}
		showmessage('����ɹ�,�ȴ����',"index.php");
	}
}elseif($action == 'log'){
    $field = "o.uid,o.amount,o.create_time,o.address,o.type,o.status,o.order_id,o.eac,o.real_eac";
    $sql=" from ".DB::table("eacpay_order")." AS o where o.`type`='cash'";
	$sql.=' and o.uid='.$uid;
	$sql.=" and o.status<>'wait'";
	
    $vo = DB::fetch_first('select count(o.order_id) as count '.$sql);
    $totalCount = $vo['count'];
    $page = intval($_REQUEST['page']);
    $page = $page<1 ? 1 : $page;
    $pagesize = 10;

    $orderlist = DB::fetch_all('select '.$field.$sql.' order by o.`create_time` desc limit 0,10');
	$statusArr=array(
		'reject'	=>	'�˻�',
		'wait'	=>	'�ȴ�֧��',
		'payed'	=>	'ϵͳȷ����',
		'complete'	=>	'�ɹ�',
	);
	include template('cc_cansnow_eacpay:cash_log');
}else{	
    $field = "o.uid,o.amount,o.create_time,o.address,o.type,o.status,o.order_id,o.eac,o.real_eac";
    $sql=" from ".DB::table("eacpay_order")." AS o where o.`type`='cash'";
	$sql.=' and o.uid='.$uid;
	$sql.=" and o.status<>'wait'";
    $orderlist = DB::fetch_all('select '.$field.$sql.' order by o.`create_time` desc limit 0,10');
	$statusArr=array(
		'reject'	=>	'�˻�',
		'wait'	=>	'�ȴ�֧��',
		'payed'	=>	'ϵͳȷ����',
		'complete'	=>	'�ɹ�',
	);

	$usermoney = DB::fetch_first("SELECT * FROM ".DB::table('common_member_count')." WHERE uid=$uid");
	$addressVo = DB::fetch_first('select * from '.DB::table("eacpay_address").' where uid = '.$uid);
	if(!$addressVo){
		$cash_address = '';
	}else{
		$cash_address = $addressVo['address'];
	}
	$exchangeData = getExchange();

}
?>