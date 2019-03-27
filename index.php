<?php

?><style type="text/css">
	table.zip{font-size:8px;border-spacing:0;table-layout: fixed;}
	.zip th{margin:0;background:#ddd;text-align:center;vertical-align:middle;}
	.zip td{margin:0;width:20px;border:none;text-align:center;vertical-align:middle;padding:4px;}
	.zip td.w{}
	.zip td.o{background:#ddd;}
	
	.viet{border-spacing:0;font-size:14px;}
	.viet td{text-align:center;vertical-align:middle;}
	.viet .dat{border:solid 1px rgba(128,128,128,.2);width:1em;height:1em;}
	.viet .bb{border-bottom:solid 1px #000;}
	.viet .br{border-right:solid 1px #000;}
	
	.ntab{border:solid 1px #000;border-spacing:0;border-left:none;border-top:none;}
	.ntab td{border:solid 1px #000;border-right:none;border-bottom:none;}
</style>
<script type="text/javascript">
function download(filename, text) {
  var element = document.createElement('a');
  element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(text));
  element.setAttribute('download', filename);

  element.style.display = 'none';
  document.body.appendChild(element);

  element.click();

  document.body.removeChild(element);
}
</script>
<h1>Расчёт диаграмм Вейча</h1>
<?php



$time = microtime(true);


$spe_chars = array(
	'!'=>'!',
	'+'=>'+',
	'*'=>'*',
);
$spe_chars = array(
	'!'=>'~',
	'+'=>' | ',
	'*'=>' & ',
);



$s_values = isset($_REQUEST['values']) ? $_REQUEST['values'] : '0 0
0 1
1 0
1 1';
$s_o_values = isset($_REQUEST['o_values']) ? $_REQUEST['o_values'] : '1 0 1
1 1 1
1 1 0
0 0 1';
$s_names = isset($_REQUEST['names']) ? $_REQUEST['names'] : 'q1/q2';
// $number_col = isset($_REQUEST['number_col']) ? $_REQUEST['number_col'] : '2';
// $number_di = isset($_REQUEST['number_di']) ? $_REQUEST['number_di'] : '3';
?>
<form method=post>
Названия колонок через "/"<br>
<textarea name="names"><? echo $s_names;?></textarea><br>
Входные значения<br>
<textarea name="values"><? echo $s_values;?></textarea><br>
Выходные значения<br>
<textarea name="o_values"><? echo $s_o_values;?></textarea><br>
* значения можно задавать с любыми отступами или без них<br>
<input type="submit" value="Расчитать"/>
</form>
<?

$names = explode("/",$s_names);
foreach($names as $k=>$r) $names[$k] = trim($r);
$values = explode("\n",$s_values);
$o_values = explode("\n",$s_o_values);
if(count($values) != count($o_values)) echo '<h2>Не совпадают количества строк</h2>';
$vals = array();
foreach($values as $k=>$row){
	$vals[$k] = array(preg_replace("/\s/","",$values[$k]), preg_replace("/\s/","",$o_values[$k]));
}

$number_col = mb_strlen($vals[0][0]);
$number_di = mb_strlen($vals[0][1]);
$err = false;
if($number_col > 10) {
	echo 'Слишком много вычислений';
	$err = true;
}
if(count($names)!=$number_col){
	echo 'Не хватает названий столбцов';
	
	$err = true;
}
if(!$err){
$vals_key = array();
foreach($vals as $k=>$row){
	$vals_key[$row[0]] = $k;
}
// echo'<pre>';var_dump($number_col);die();
for($id=0; $id<$number_di; $id++){
	
	
	$data = array('list'=>array(), 'code_name'=>$names);
	$len = $number_col;
	$count = bindec(str_repeat('1',$len));
	// echo'<pre>';var_dump($count, $len);die();
	
	$i=0;
	while($count>=$i){
		$code1 = sprintf("%0".$len."d",decbin($i));
		
		$v = '-';
		if(isset($vals_key[$code1])){
			$v = $vals[$vals_key[$code1]][1][$id];
		}
		
		$data['list'][] = array(
			'code'=>$code1,
			'value'=>$v,
		);
		$i++;
	}
	// echo'<pre>';var_dump($data);die();
	echo '<h2>Диаграмма '.($id+1).'</h2>';
	$form = make_veit_mas($data, 'd'.($id+1));
	
}

}
/*
module dop2(input q1,q2,q3,q4,q5,
				output y1,y2);

assign y1 =  ~(~(q3 & ~q2 & q1) & ~(~q5 & ~q4 & ~q2 & q1) & ~(q5 & q2 & q1) & ~(q4 & ~q3 & ~q2 & ~q1) & ~(~q5 & ~q4 & q3 & q2 & ~q1));
assign y2 = ~(~(q5 & ~q3 & ~q2) & ~(q5 & q3 & ~q1) & ~(q3 & q2 & q1) & ~(~q5 & ~q4 & ~q3 & q2) & ~(q4 & q3 & ~q2 & ~q1));
				
endmodule 
*/





// make_viet_input_data(0);
// $hard1 = make_veit_mas(1,'q', 'y1');



// echo microtime(true)-$time;
// ==========================



function make_viet_input_data($n,$vals,$names){
	
	$vals_key = array();
	
	foreach($vals as $k=>$row){
		$vals_key[$row[0]] = $k;
	}
	
	global $var_mas;
	
	$len = $var_mas['ta']['code_read_len']+$var_mas['ta']['code_sost_len'];
	$count = bindec(str_repeat('1',$len));
	
	$i=0;
	while($count>=$i){
		$code1 = sprintf("%0".$len."d",decbin($i));
		$data['list'][] = array(
			'code'=>$code1,
			'value'=>'-',
		);
		$i++;
	}
	
	$data = array(
		'list'=>array()
	);
	
	$i=0;
	foreach($var_mas['ta']['code_read'] as $key=>$row){
		foreach($var_mas['ta']['code_sost'] as $k=>$r){
			$sost = $var_mas['sost'][0]['list'][$r['sost_id']];
			$out_sost_id = $sost["mov"][$row['name']]['to'];
			$count_code_value = false;
			if ($out_sost_id!==false){
				if($out_sost_id<2) $out_sost_id+=2;
				$out_code = getCodeBySostId($out_sost_id);
				$count_code_value = $out_code['value'];
				$value = $count_code_value[$n];
			}
			else $value='-';
			
			$code = $row['value'].$r['value'];
			if(0) if($code=='1011001'){
				var_dump($n);
				var_dump($code);
				var_dump($r['sost_id']);
				var_dump($value);
				var_dump($sost);
				var_dump($out_sost_id);
				var_dump(getCodeBySostId($out_sost_id));
				die();
			}
			$ti = 0;
			while(bindec($code)-0>$i){
				$code1 = sprintf("%0".$len."d",decbin($i));
				$data['list'][] = array(
					'code'=>$code1,
					'value'=>'-',
				);
				$ti++;
				// if($ti>50) {var_dump($i,$code, bindec($code));die();}
				$i++;
			}
			// if($code=='1000101') {var_dump($code, $out_sost_id, $value, $count_code_value); die();}
			// var_dump($i, decbin($code));die();
			$data['list'][] = array(
				'code'=>$code,
				'value'=>$value,
			);
			$i++;
		}
	}
	// var_dump($i, $count);die();
	while($count>=$i){
		$code1 = sprintf("%0".$len."d",decbin($i));
		$data['list'][] = array(
			'code'=>$code1,
			'value'=>'-',
		);
		$i++;
	}
	
	if(0)foreach($data['list'] as $r) echo $r['code'].' '.$r['value'].'<br>';
	
	$data['code_name'] = $names;
	
	return $data;
	
}
function make_viet_output_data($n){
	
	global $var_mas;
	
	$len = $var_mas['ta']['code_sost_len'];
	$count = bindec(str_repeat('1',$len));
	
	$data = array(
		'list'=>array()
	);
	
	for($i=0;$i<=$count;$i++){
		$code = sprintf("%0".$len."d",decbin($i));
		$sost_id = getCodeByValue('code_sost',$code);
		$sost_id = $sost_id['sost_id'];
		if(!$sost_id) $value = '-';
		else{
			$value = $var_mas['sost'][0]['list'][$sost_id]['write'];
			$value = getCodeValueByName('code_write', $value);
			$value = $value[$n];
		}
		
		$data['list'][$i] = array(
			'code'=>$code,
			'value'=>$value,
		);
	}
	
	$data['code_name'] = array();
	for($i=$len; $i>0; $i--){
		$data['code_name'][] = 'q'.($i);
	}
	
	return $data;
	
}

function make_veit_mas($data, $func=false){
	
	global $var_mas;
	
	$len = strlen($data['list'][0]['code']);
	
	
	
	$data_value_keys = array();
	foreach($data['list'] as $k=>$r){
		if($r['value']!='-'){
			if(!isset($data_value_keys[$r['value']])) $data_value_keys[$r['value']] = array();
			$data_value_keys[$r['value']][$k] = true;
		}
	}
	
	$viet_var_mask = make_viet_var_mask($len);
	
	$viet_find_vars = array('0'=>array(), '1'=>array());
	
	foreach($viet_var_mask as $mask){
		$find = viet_mask_test($data, $mask);
		if(isset($find['0']) && isset($find['1'])){}
		else{
			$f = isset($find['0']) ? '0' : '1';
			$find[$f]['mask'] = $find['mask'];
			$viet_find_vars[$f][] = $find[$f];
		}
	}
	foreach($viet_find_vars as $k=>$r){
	uasort($viet_find_vars[$k], function($f11,$f22){
		// var_dump($f11, $f22);die();
		if($f11['count']>$f22['count']) return -1;
		if($f11['count']<$f22['count']) return 1;
		
		$f1 = $f11['mask'];
		$f2 = $f22['mask'];
		
		$f1_d = substr_count($f1, '.');
		$f2_d = substr_count($f2, '.');
		if($f1_d>$f2_d) return -1;
		if($f1_d<$f2_d) return 1;
		
		return bindec($f1)>bindec($f2) ? 1 : -1;
		
	});
	}
	//Выбираем подходящие маски
	foreach($viet_find_vars as $symb=>$viet_find)
	foreach($viet_find as $key=>$row){
		foreach($viet_find as $k=>$r){
			if($key < $k){
				if(preg_match('/'.$row['mask'].'/', $r['mask'])) unset($viet_find_vars[$symb][$k]);
				// if(preg_match('/'.$row['mask'].'/', $r['mask'])) 
					// echo $row['mask']."\t".$r['mask']."\t".(preg_match('/'.$row['mask'].'/', $r['mask'])?1:0)."<br>";
			}
		}
	}
	//Убираем подмаски
	
	foreach($viet_find_vars as $symb=>$viet_find){
		$data_value_keys_copy = $data_value_keys;
		foreach($viet_find as $key=>$row){
			if (is_array($row['row'])) foreach($row['row'] as $mask_key){
				if(isset($data_value_keys_copy[$symb][$mask_key])){
					unset($data_value_keys_copy[$symb][$mask_key]);
					if(!isset($viet_find_vars[$symb][$key]['pcount'])) $viet_find_vars[$symb][$key]['pcount']=0;
					$viet_find_vars[$symb][$key]['pcount']++;
				}
			}
			if(!$viet_find_vars[$symb][$key]['pcount']){
				unset($viet_find_vars[$symb][$key]);
			}
		}
		if(count($data_value_keys_copy[$symb])){
			echo '<h2>We have a problems...</h2>';
			var_dump($data_value_keys_copy[$symb]);
		}
	}
	$count = 0;
	// echo '<pre>';
	//Убираем маски, которые можно убрать
	do{
		$delete = false;
		foreach($viet_find_vars as $symb=>$viet_find){
			$prev_del = false;
			foreach($viet_find as $key1=>$row1){
				$data_value_keys_copy = $data_value_keys;
				foreach($viet_find as $key=>$row){
					if($key!=$key1)
					if (is_array($row['row'])) foreach($row['row'] as $mask_key){
						if(isset($data_value_keys_copy[$symb][$mask_key])){
							unset($data_value_keys_copy[$symb][$mask_key]);
						}
					}
				}
				if(count($data_value_keys_copy[$symb])){
					
				}
				else{
					// echo $key1.' - can delete '.$row1['mask'].' '.$symb.'<br>';
					if($prev_del){
						if(substr_count($row1['mask'], '.') < substr_count($viet_find[$prev_del]['mask'], '.')){
							$prev_del = $key1;
						}
					}else $prev_del = $key1;
					
				}
			}
			
			if($prev_del){
				// echo $prev_del.' - delete '.$viet_find[$prev_del]['mask'].' '.$symb.'<br>';
				unset($viet_find_vars[$symb][$prev_del]);
				$delete = true;
			}
		}
		$count++;
	}
	while($delete && $count<20);
	
	// foreach($viet_find_vars[0] as $row){
		// echo $row['mask']."\t".$row['count']."\t".$row['pcount']."\t".implode(',',$row['row']).'<br>';
	// }
	viet_show($data);
	$hard0 = viet_make_form($data, $viet_find_vars, 0, $func);
	$hard1 = viet_make_form($data, $viet_find_vars, 1, $func);
	
	$viet_optim_sumb = '';
	if($hard0>$hard1) {
		$viet_optim_sumb = 1;
	}
	else{
		$viet_optim_sumb = 0;
	}
	if($hard0==0 || $hard1==0) return false;
	
	echo '<p>Выбираем минимизацию по '.$viet_optim_sumb.', переводим в базис Шеффера:</p>';
	$viet_optim = $viet_find_vars[$viet_optim_sumb];
	$result_form = viet_make_form_sheffer($data, $viet_optim, $viet_optim_sumb, $func);
	echo '<p>'.$result_form.'</p>';
	return $result_form;
	// echo '<pre>';var_dump(viet_get_line_mask(4));
	// echo '<pre>';var_dump(($data));
	
}

function viet_make_form_sheffer($data,$viet_optim,$symb=0,$func){
	$viet_find = $viet_optim;
	
	$text = '';
	global $spe_chars;

	$hard = 0;
	foreach($viet_optim as $row){
		$mask = $row['mask'];
		$mask_len = strlen($mask);
		// var_dump($mask_len);die();
		$text_temp = '';
		for($n=0; $n<$mask_len; $n++){
			$s = $mask[$n];
			$name = $data['code_name'][$n];
			if($s!='.'){
				if($symb=='1') {
					
					 $add = '';
					 
					 $add.= ($text_temp ? $spe_chars['*'] : '');
					 $add.= ($s=='1'?'':$spe_chars['!']).$name;
					 
					 $text_temp.= $add;
					 
				}
				else {
					$text_temp.= ($text_temp ? $spe_chars['*'] : '').($s=='1'?'':$spe_chars['!']).$name;
				}
				$hard++;
			}
		}
		if($text_temp) {
			if($symb=='1') 
				 $text.= ($text ? $spe_chars['*'] : '').$spe_chars['!'].'('.$text_temp.')';
			else $text.= ($text ? $spe_chars['*'] : '').$spe_chars['!'].'('.$text_temp.')';
			$hard++;
		}
	}
	// echo $spe_chars['!'];die();
	if($symb=='1') 
		 $text= $spe_chars['!'].'('.$text.')';
	else $text = $spe_chars['!'].'('.$spe_chars['!'].'('.$text.')'.')';
	
	test_viet_make_form($data, $text);
	
	$return = $func.' = '.$text.';';
	// echo $return;
	return $return;
	// return $hard;
}

function test_viet_make_form($data, $form){
	// var_dump($data);die();
	global $spe_chars;
	$test_ok = true;
	foreach($data['list'] as $key=>$row){
		$rep = array(
			$spe_chars['!']=>'!',
			$spe_chars['*']=>'&',
			$spe_chars['+']=>'|',
			);
		
		foreach($data['code_name'] as $k=>$name){
			$rep[$name] = $row['code'][$k];
		
		}
		// var_dump($rep);
		$form_rep = str_replace(array_keys($rep), array_values($rep), $form);
		$ret = eval('return '.$form_rep.';');
		// var_dump($row['code'],$form_rep,$ret,'<br>');
		if($ret != $row['value'] &&  $row['value']!='-') {
			$test_ok = false;
			echo "<p>$ret!=$row[value] ($row[code])</p>";
		}
	}
	if(!$test_ok) echo '<h2>Хьюстон, у нас проблемы</h2>';
}

function viet_make_form($data,$viet_find_vars,$symb=0,$func){
	$viet_find = $viet_find_vars[$symb];
	
	$text = '';
	
	global $spe_chars;
	$hard = 0;
	foreach($viet_find_vars[$symb] as $row){
		$mask = $row['mask'];
		$mask_len = strlen($mask);
		// var_dump($mask_len);die();
		$text_temp = '';
		for($n=0; $n<$mask_len; $n++){
			$s = $mask[$n];
			$name = $data['code_name'][$n];
			if($s!='.'){
				if($symb=='1') 
					 $text_temp.= ($text_temp ? $spe_chars['*'] : '').($s=='1'?'':$spe_chars['!']).$name;
				else $text_temp.= ($text_temp ? $spe_chars['+'] : '').($s=='0'?'':$spe_chars['!']).$name;
				$hard++;
			}
		}
		if($text_temp) {
			if($symb=='1') 
				 $text.= ($text ? $spe_chars['+'] : '').$text_temp;
			else $text.= ($text ? $spe_chars['*'] : '').'('.$text_temp.')';
			$hard++;
		}
	}
	$return = '<p>Минимизация по '.$symb.'</p>'.
	'<p>'.$func.' = '.$text.'</p>'.
	'<p>Сложность: '.$hard.'</p>';
	echo $return;
	return $hard;
}

function viet_show($data){
	
	$len = count($data['code_name']);
	$x_len = ceil($len/2);
	$y_len = floor($len/2);
	$x = pow(2,$x_len);
	$y = pow(2,$y_len);
	
	$data_keys = array();
	foreach($data['list'] as $k=>$r){
		$data_keys[$r['code']] = $r['value'];
	}
	
	
	$x_mask = viet_get_line_mask($x_len);
	$y_mask = viet_get_line_mask($y_len);
	// var_dump($x,$y);
	$table = array();
	echo '<table class="viet">';
	
	for($j1=0; $j1<$x_len; $j1++){
		echo '<tr><td colspan="'.($y_len+1).'">';
		$s = false;
		$s_count = 0;
		for($j=0; $j<$x; $j++){
			if($s===false || $s!= $x_mask[$j][$j1]){
				if($s!==false){
					echo '<td colspan="'.$s_count.'" class="'.($s=='1' ? ' bb ' : '').'">';
					if($s=='1') echo $data['code_name'][$y_len+$j1];
				}
				$s = $x_mask[$j][$j1];
				$s_count = 0;
			}
			$s_count++;
		}
		echo '<td colspan="'.$s_count.'" class="'.($s=='1' ? ' bb ' : '').'">';
		if($s=='1') echo $data['code_name'][$y_len+$j1];
	}
	
	
	echo '<tr><td>';
	$s = array();
	$s_count = array();
	for($i=0; $i<$y; $i++){
		
		echo '<tr>';
		//echo '<td colspan="'.($y_len+1).'">';
		for($i1=0; $i1<$y_len; $i1++){
			
			if(!isset($s[$i1])) $s[$i1] = false;
			
		
			
			if($s[$i1]===false || $s[$i1]!= $y_mask[$i][$i1]){
				
				$s[$i1] = $y_mask[$i][$i1];
				$count = 0;
				for($i2=$i; $i2<$y; $i2++){
					if($s[$i1]!=$y_mask[$i2][$i1]){
						break;
					}
					$count++;
				}
				
				
					echo '<td rowspan="'.$count.'" class="'.($s[$i1]=='1' ? ' br ' : '').'">';
					if($s[$i1]=='1') echo $data['code_name'][$i1];
				
			}
			
			
			// echo '<td>';
			// echo $data['code_name'][$i1];
		}
		

		//END
		echo '<td>';
		for($j=0; $j<$x; $j++){
			echo '<td class="dat">'.$data_keys[$y_mask[$i].$x_mask[$j]];
		}
	}
	echo '</table>';
	// die();
}

function viet_get_line_mask($len, $revers=false){
	$rows = array();
	$num_rows = pow(2,$len);
	
	for($i=$len-1; $i>=0; $i--){
		
		$flag = 1;
		$flag_count = 0;
		$flag_max = $i==$len-1 ? $num_rows/2 : pow(2, $i+1);
		
		for($r=0; $r<$num_rows; $r++){
			if(!isset($rows[$r])) $rows[$r]='';
			
			if ($r<pow(2, $i)) $rows[$r] .= '0';
			else {
				$rows[$r] .= $flag;
				$flag_count++;
				if($flag_count==$flag_max){
					$flag = $flag?0:1;
					$flag_count=0;
				}
				
			}
		}
	}
	
	if($revers) foreach($rows as $k=>$r){
		$rows[$k] = strrev($r);
	}
	
	return $rows;
	
}

function viet_mask_test(&$data,$mask){
	
	$ret = array('mask'=>$mask);
	
	foreach($data['list'] as $k=>$row){
		if(preg_match('/'.$mask.'/',$row['code'])){
			if(!isset($ret[$row['value']])) $ret[$row['value']] = array('count'=>0, 'row'=>array());
			$ret[$row['value']]['count']++;
			$ret[$row['value']]['row'][] = $k;
		}
	}
	
	return $ret;
}

function make_viet_var_mask($len=5,$lvl=0,$inp=false){
	
	$chars = array(0,1,'.');
	
	if($inp==false) {
		$ret = make_viet_var_mask($len, $lvl+1, $chars);
		
		uasort($ret, function($f1,$f2){
			
			$f1_d = substr_count($f1, '.');
			$f2_d = substr_count($f2, '.');
			if($f1_d>$f2_d) return -1;
			if($f1_d<$f2_d) return 1;
			
			return bindec($f1)>bindec($f2) ? 1 : -1;
			
		});
		
		return $ret;
		
	}
	else{
		$ret = array();
		foreach($inp as $row){
			foreach($chars as $s){
				$ret[] = $row.$s;
			}
		}
		if ($len==$lvl+1) return $ret;
		else return make_viet_var_mask($len, $lvl+1, $ret);
	}
	
}
