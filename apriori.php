<?php
error_reporting(0);

echo "Choose database to process \n";
echo "press 1 for Transactional Database 1 \n";
echo "press 2 for Transactional Database 2 \n";
echo "press 3 for Transactional Database 3 \n";
echo "press 4 for Transactional Database 4 \n";
echo "press 5 for Transactional Database 5 \n";
$handle = fopen ("php://stdin","r");
$line = fgets($handle);
echo "Enter minimum support value :: \n";
$sup=fgets($handle);
echo "Enter minimum confidence value :: \n";
$conf=fgets($handle);
fclose($handle);
echo "\n"; 
echo "Thank you, continuing...\n";

switch(trim($line)){
	case 1:
	$filename="data1.txt";break;
	case 2:
	$filename="data2.txt";break;
	case 3:
	$filename="data3.txt";break;
	case 4:
	$filename="data4.txt";break;
	case 5:	
	$filename="data5.txt";break;
	default:
	echo "Press valid input";
	break;

}



$data_from_file=file($filename);
$number_of_frequenr_set_value=0;
$min_support=$sup;
$min_confidence=$conf;
$unique_item_set=array();
$transactionArr=array();
$not_satisfying_item=array();
$unique_item=array();
foreach($data_from_file as $key=>$value)
{
	
	$buffer=explode(",",$value);
	$itemsArr=explode(" ",$buffer[1]);
	$transactionArr[$buffer[0]]=array();
	foreach($itemsArr as $val)
	{
		array_push($transactionArr[$buffer[0]],trim($val));
		
		if(!in_array(trim($val),$unique_item))
		{
			array_push($unique_item,trim($val));
			$unique_item_set[trim($val)]=0;
		}
		
	}
	
}
function item_set_scanner($item_set)
{	
	global $transactionArr;
	$Temp=array();
	
	foreach($item_set as $item => $val) 
	{
		$searchThis=array();
		$Temp[$item]=0;
		if(preg_match("/\|/",$item))
		{
			$searchThis=explode("|",$item);
		}else{
			array_push($searchThis,$item);
		
		}
		foreach($transactionArr as $itemlist)
		{
			
			 $containsSearch = count(array_intersect($searchThis, $itemlist)) == count($searchThis);
			
			if($containsSearch)
			{
				$Temp[$item]++;
				
			}	
		}
	}
	
	return $Temp;
}
function pruning($Temp)
{
	global $transactionArr,$not_satisfying_item,$min_support;
 	$min_sup=ceil(count($transactionArr)*$min_support/100);
	foreach($Temp as $key=>$val)
	{
		if($val < $min_sup)
		{
			unset($Temp[$key]);
			if(preg_match("/\|/",$key))
			{
				$not_satisfying_item[$key]=explode("|",$key);
			}else{
				$not_satisfying_item[$key]=array($key);
			}
			
		}
	}
	return $Temp;
}

function develope_itemset($prev_itemset)
{
	global $not_satisfying_item;
	$Temp=array();
	$valid_items=array();
	foreach($prev_itemset as $k=>$v)
	{	
		if(preg_match("/\|/",$k)){$flag=1;}else{$flag=0;}
		array_push($valid_items,$k);
		
	}
	
	for($j=0;$j < count($valid_items) ; $j++)
	{
	for($i=$j+1;$i < count($valid_items) ; $i++)
		{
			
			if($flag)
			{
			 	$part1=explode("|",$valid_items[$j]);$part2=explode("|",$valid_items[$i]);
				$keyVal=implode("|",array_unique(array_merge($part1, $part2)));
				
				if( empty( $Temp ) ){ 
					$Temp[$keyVal]=array_unique(array_merge($part1, $part2));
					
				}else{
					foreach($Temp as $key => $value)
					{
						if(count($value) >= count(array_unique(array_merge($part1, $part2)))){
							$containsSearch = count(array_intersect($value, array_unique(array_merge($part1, $part2)))) == count($value);}
						else
						{
							$containsSearch = count(array_intersect($value, array_unique(array_merge($part1, $part2)))) == count(array_unique(array_merge($part1, $part2)));
						}
						
						if($containsSearch != 1)
						{	
							$Temp[$keyVal]=array_unique(array_merge($part1, $part2));
						}else{unset($Temp[$key]);
						$Temp[$keyVal]=array_unique(array_merge($part1, $part2));
						}
					}
					
				}
			}else{
				$keyVal=$valid_items[$j]."|".$valid_items[$i];
				$Temp[$keyVal]=0;
			}
			
			
		}
	}
	
	foreach($not_satisfying_item as $keyval=>$valVal)
	{
		if(preg_match("/\|/",$keyval))
		{
			foreach($Temp as $tempkey => $tempval)
			{
				$check=count(array_intersect($valVal, $tempval)) == count($valVal);
				if($check)
				{
					unset($Temp[$tempkey]);
				}
			}
		}
		else
		{	
		foreach($Temp as $tempkey=>$tempval)
			{
			if(in_array($keyval,$tempval))
				{
					unset($Temp[$tempkey]);
				}
			}
		}	
	}
				
	
	
	return $Temp;
}

function generate_Association_Rules($k)
{
	
	global $min_confidence;
		if($k==3){
		echo "Association Rules for 3 frequent item set"."\n\n\n";
		global ${"L".$k};
			global ${"L".($k-1)};
			global ${"L".($k-2)};
			foreach(${"L".$k} as $keyVal => $valVal)
			{
				$item_setArr=explode("|",$keyVal);
				for($i=0;$i< count($item_setArr) ;$i++)
				{
					
					echo " {".$item_setArr[$i]."} => "."{".$item_setArr[($i+1)%count($item_setArr)].",".$item_setArr[($i+2)%count($item_setArr)]."}  \t\t";
					echo $valVal ."  , ".${"L".($k-2)}[$item_setArr[$i]]." , ";
					$valculate= ($valVal / ${"L".($k-2)}[$item_setArr[$i]])*100;
					echo $valculate. "  ";
					if($valculate > $min_confidence )
					{
						echo "True , It is an association rule \n\n";
					}else{
						echo "False\n\n";
					}
					
					
					echo " {".$item_setArr[$i]." , ".$item_setArr[($i+1)%count($item_setArr)]."} => {".$item_setArr[($i+2)%count($item_setArr)]."}  \t\t";
					$denominator=0;
					$first=$item_setArr[$i];
					$last=$item_setArr[($i+1)%count($item_setArr)];
					if(${"L".($k-1)}[$first."|".$last])
					{
					 	$denominator=${"L".($k-1)}[$first."|".$last];
					}else{
					 	$denominator=${"L".($k-1)}[$last."|".$first];
					}
					
					
					echo $valVal ."  , ".$denominator." , ";
					$valculate= ($valVal / $denominator)*100;
					echo $valculate. "  ";
					if($valculate > $min_confidence )
					{
						echo "True , It is an association rule \n\n";
					}else{
						echo "False\n\n";
					}
					
					 
				}	
			}
					
		
		}
		if($k==2)
		{
		
			global ${"L".$k};
			global ${"L".($k-1)};
	
			echo "Association Rules for 2 frequent item set"."\n\n\n";
			foreach(${"L".$k} as $keyVal => $valVal)
			{
				$item_setArr=explode("|",$keyVal);
				
				
				for($i=0;$i< count($item_setArr) ;$i++)
				{
				echo "{".$item_setArr[$i] ."}  =>  {".  $item_setArr[($i+1)%count($item_setArr)]."}\t\t";
				
				echo  $valVal." , ".${"L".($k-1)}[$item_setArr[$i]]."  , ";
				$valculate= ($valVal / ${"L".($k-1)}[$item_setArr[$i]])*100;
				echo $valculate ."  ";
				if($valculate > $min_confidence )
				{
					echo "True , It is an association rule \n\n";
				}else{
					echo "False\n\n";
				}
				
				}	
			}
			
		}
	
			
}
$i=1;
 echo "Following are intermediate steps ::   \n\n \n";
while($i)
{	
	
	if($i==1)
	{
		${"C".$i}=item_set_scanner($unique_item_set);
		echo "The Value of C".$i." :: \n\n\n";	
		print_r(${"C".$i});
	}else{
		${"C".$i}=item_set_scanner(${"C".$i});
		echo "The Value of C".$i." ::\n \n\n";
		print_r(${"C".$i});
	}
	
	echo "The Value of L".$i." ::\n\n \n";
	${"L".$i}=pruning(${"C".$i});
	print_r(${"L".$i});
	
	if(count(${"L".$i}) < 2)
	{
		$number_of_frequenr_set_value=$i;
		$i=0;
	}
	else
	{	
		$j=$i+1;
		${"C".$j}=develope_itemset(${"L".$i});
			foreach(${"C".$j} as $k=>$v)
			{
				${"C".$j}[$k]=0;
			}
			echo "The Value of C".$j." :: \n\n\n";
		print_r(${"C".$j});
		$i++;
	}
	
}
while($number_of_frequenr_set_value > 1)
{
	generate_Association_Rules($number_of_frequenr_set_value -1);
	generate_Association_Rules($number_of_frequenr_set_value );
	exit;

}


?> 