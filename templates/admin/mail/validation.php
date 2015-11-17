<?php
return array(
	array(
		'target'  => 'お名前',
		'noempty' => true,
	),
	array(
		'target'  => 'メールアドレス',
		'noempty' => true,
		'mail'    => true,
	),
	array(
		'target' => 'メールアドレス確認',
		'eq'     => array(
			'target' => 'メールアドレス'
		),
	),
	array(
		'target'  => '内容',
		'noempty' => true,
	),
);
